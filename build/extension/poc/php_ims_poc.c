/**
 * php_ims_poc.c -- Proof of Concept IMS Extension
 *
 * Minimal extension to validate:
 * 1. Extension can be compiled and loaded by NativePHP's PHP
 * 2. PHP functions can be called from Laravel code
 * 3. File integrity checking works (1 file)
 * 4. Removing extension crashes the app (via vendor patch)
 * 5. RINIT can block requests
 *
 * NO external dependencies (no libcurl, no libsodium, no DPAPI).
 * Just PHP headers + Windows API for SHA-256.
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "zend_exceptions.h"

#include <windows.h>
#include <bcrypt.h>  /* Windows CNG for SHA-256 */

#pragma comment(lib, "bcrypt.lib")

/* ── Extension metadata ─────────────────────────────────────── */

#define PHP_IMS_POC_EXTNAME  "ims"
#define PHP_IMS_POC_VERSION  "0.1.0-poc"

/* ── Module Globals ─────────────────────────────────────────── */

ZEND_BEGIN_MODULE_GLOBALS(ims)
    zend_bool licensed;
    zend_bool tampered;
    char base_path[MAX_PATH];

    /* Hardcoded integrity check: one file, one hash */
    char watched_file[512];       /* relative path */
    char watched_hash[65];        /* expected SHA-256 hex */
    zend_bool integrity_enabled;  /* whether we have a file to watch */
ZEND_END_MODULE_GLOBALS(ims)

ZEND_DECLARE_MODULE_GLOBALS(ims)
#define IMS_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(ims, v)

/* ── SHA-256 using Windows BCrypt API ───────────────────────── */

static int sha256_file(const char *filepath, char *out_hex)
{
    BCRYPT_ALG_HANDLE hAlg = NULL;
    BCRYPT_HASH_HANDLE hHash = NULL;
    NTSTATUS status;
    DWORD hashObjSize = 0, hashSize = 0, cbData = 0;
    PBYTE hashObject = NULL;
    BYTE hash[32];
    FILE *fp;
    BYTE buf[8192];
    size_t n;
    int result = -1;

    /* Open algorithm */
    status = BCryptOpenAlgorithmProvider(&hAlg, BCRYPT_SHA256_ALGORITHM, NULL, 0);
    if (!BCRYPT_SUCCESS(status)) goto cleanup;

    /* Get hash object size */
    BCryptGetProperty(hAlg, BCRYPT_OBJECT_LENGTH, (PBYTE)&hashObjSize, sizeof(DWORD), &cbData, 0);
    BCryptGetProperty(hAlg, BCRYPT_HASH_LENGTH, (PBYTE)&hashSize, sizeof(DWORD), &cbData, 0);

    hashObject = (PBYTE)malloc(hashObjSize);
    if (!hashObject) goto cleanup;

    /* Create hash */
    status = BCryptCreateHash(hAlg, &hHash, hashObject, hashObjSize, NULL, 0, 0);
    if (!BCRYPT_SUCCESS(status)) goto cleanup;

    /* Read file and hash */
    fp = fopen(filepath, "rb");
    if (!fp) goto cleanup;

    while ((n = fread(buf, 1, sizeof(buf), fp)) > 0) {
        BCryptHashData(hHash, buf, (ULONG)n, 0);
    }
    fclose(fp);

    /* Finalize */
    status = BCryptFinishHash(hHash, hash, hashSize, 0);
    if (!BCRYPT_SUCCESS(status)) goto cleanup;

    /* Convert to hex */
    for (DWORD i = 0; i < hashSize; i++) {
        sprintf(out_hex + (i * 2), "%02x", hash[i]);
    }
    out_hex[64] = '\0';
    result = 0;

cleanup:
    if (hHash) BCryptDestroyHash(hHash);
    if (hAlg) BCryptCloseAlgorithmProvider(hAlg, 0);
    free(hashObject);
    return result;
}

/* ── Lifecycle Hooks ────────────────────────────────────────── */

static PHP_GINIT_FUNCTION(ims)
{
    memset(ims_globals, 0, sizeof(zend_ims_globals));
}

PHP_MINIT_FUNCTION(ims)
{
    /* Set base path from current working directory */
    if (GetCurrentDirectoryA(MAX_PATH, IMS_G(base_path)) == 0) {
        IMS_G(base_path)[0] = '\0';
    }

    /* Hardcoded license: always valid in POC */
    IMS_G(licensed) = 1;
    IMS_G(tampered) = 0;

    /* If a watched file + hash is configured (set via ini or hardcoded),
       do an initial integrity check */
    if (IMS_G(integrity_enabled) && IMS_G(watched_file)[0] != '\0') {
        char full_path[MAX_PATH];
        snprintf(full_path, sizeof(full_path), "%s\\%s", IMS_G(base_path), IMS_G(watched_file));

        char computed[65];
        if (sha256_file(full_path, computed) == 0) {
            if (strcmp(computed, IMS_G(watched_hash)) != 0) {
                IMS_G(tampered) = 1;
                php_error_docref(NULL, E_WARNING,
                    "IMS: Integrity check failed for %s", IMS_G(watched_file));
            }
        }
        /* If file doesn't exist, skip (dev mode) */
    }

    return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(ims)
{
    return SUCCESS;
}

PHP_RINIT_FUNCTION(ims)
{
    /* If tampered, block all requests */
    if (IMS_G(tampered)) {
        php_error_docref(NULL, E_ERROR,
            "Application integrity check failed. Cannot continue.");
        return FAILURE;
    }
    return SUCCESS;
}

PHP_RSHUTDOWN_FUNCTION(ims)
{
    return SUCCESS;
}

PHP_MINFO_FUNCTION(ims)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "IMS Extension (POC)", "enabled");
    php_info_print_table_row(2, "Version", PHP_IMS_POC_VERSION);
    php_info_print_table_row(2, "License", IMS_G(licensed) ? "Valid (hardcoded)" : "Invalid");
    php_info_print_table_row(2, "Integrity", IMS_G(tampered) ? "FAILED" : "OK");
    php_info_print_table_row(2, "Base Path", IMS_G(base_path));
    php_info_print_table_end();
}

/* ── PHP Functions ──────────────────────────────────────────── */

/* POC: ims_get_license_status() -- returns hardcoded valid status */
PHP_FUNCTION(ims_get_license_status)
{
    ZEND_PARSE_PARAMETERS_NONE();

    array_init(return_value);
    add_assoc_bool(return_value, "valid", IMS_G(licensed) && !IMS_G(tampered));
    add_assoc_string(return_value, "status",
        IMS_G(tampered) ? "tampered" :
        (IMS_G(licensed) ? "valid" : "not_activated"));
    add_assoc_string(return_value, "version", PHP_IMS_POC_VERSION);
}

/* POC: ims_check_integrity(string $filepath) -- checks one file */
PHP_FUNCTION(ims_check_integrity)
{
    char *filepath;
    size_t filepath_len;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STRING(filepath, filepath_len)
    ZEND_PARSE_PARAMETERS_END();

    char full_path[MAX_PATH];
    snprintf(full_path, sizeof(full_path), "%s\\%s", IMS_G(base_path), filepath);

    char computed[65];
    if (sha256_file(full_path, computed) != 0) {
        RETURN_FALSE;
    }

    /* Return the hash so we can use it for setting up integrity */
    RETURN_STRING(computed);
}

/* POC: ims_set_watch(string $filepath, string $expected_hash) */
PHP_FUNCTION(ims_set_watch)
{
    char *filepath, *hash;
    size_t filepath_len, hash_len;

    ZEND_PARSE_PARAMETERS_START(2, 2)
        Z_PARAM_STRING(filepath, filepath_len)
        Z_PARAM_STRING(hash, hash_len)
    ZEND_PARSE_PARAMETERS_END();

    if (hash_len != 64) {
        RETURN_FALSE;
    }

    strncpy(IMS_G(watched_file), filepath, sizeof(IMS_G(watched_file)) - 1);
    strncpy(IMS_G(watched_hash), hash, sizeof(IMS_G(watched_hash)) - 1);
    IMS_G(integrity_enabled) = 1;

    RETURN_TRUE;
}

/* POC disguised function: the one vendor patch will call */
PHP_FUNCTION(zrx_session_gc_probability)
{
    zend_long maxlifetime;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(maxlifetime)
    ZEND_PARSE_PARAMETERS_END();

    /* Returns maxlifetime if licensed+intact, 0 if not */
    RETURN_LONG(IMS_G(licensed) && !IMS_G(tampered) ? maxlifetime : 0);
}

/* ── Arginfo Definitions ────────────────────────────────────── */

ZEND_BEGIN_ARG_INFO_EX(arginfo_ims_get_license_status, 0, 0, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_ims_check_integrity, 0, 0, 1)
    ZEND_ARG_INFO(0, filepath)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_ims_set_watch, 0, 0, 2)
    ZEND_ARG_INFO(0, filepath)
    ZEND_ARG_INFO(0, expected_hash)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_zrx_session_gc_probability, 0, 0, 1)
    ZEND_ARG_INFO(0, maxlifetime)
ZEND_END_ARG_INFO()

/* ── Function Registration ──────────────────────────────────── */

static const zend_function_entry ims_functions[] = {
    PHP_FE(ims_get_license_status, arginfo_ims_get_license_status)
    PHP_FE(ims_check_integrity, arginfo_ims_check_integrity)
    PHP_FE(ims_set_watch, arginfo_ims_set_watch)
    PHP_FE(zrx_session_gc_probability, arginfo_zrx_session_gc_probability)
    PHP_FE_END
};

/* ── Module Entry ───────────────────────────────────────────── */

zend_module_entry ims_module_entry = {
    STANDARD_MODULE_HEADER,
    PHP_IMS_POC_EXTNAME,
    ims_functions,
    PHP_MINIT(ims),
    PHP_MSHUTDOWN(ims),
    PHP_RINIT(ims),
    PHP_RSHUTDOWN(ims),
    PHP_MINFO(ims),
    PHP_IMS_POC_VERSION,
    PHP_MODULE_GLOBALS(ims),
    PHP_GINIT(ims),
    NULL,
    NULL,
    STANDARD_MODULE_PROPERTIES_EX
};

#ifdef COMPILE_DL_IMS
ZEND_GET_MODULE(ims)
#endif
