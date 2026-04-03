/**
 * php_ims.c -- IMS PHP Extension Entry Point
 *
 * Module initialization, request lifecycle, and function registration.
 * This is the main entry point for the php_ims.dll extension.
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_ims.h"
#include "license.h"
#include "integrity.h"
#include "crypto.h"
#include "network.h"
#include "hardware.h"
#include "functions.h"

ZEND_DECLARE_MODULE_GLOBALS(ims)

/* ── Module Globals Initialization ──────────────────────────── */

static PHP_GINIT_FUNCTION(ims)
{
    memset(ims_globals, 0, sizeof(zend_ims_globals));
}

/* ── MINIT: Module Initialization (once, when PHP starts) ───── */

PHP_MINIT_FUNCTION(ims)
{
    /* 1. Initialize crypto libraries */
    if (ims_crypto_init() != SUCCESS) {
        php_error_docref(NULL, E_ERROR, "Failed to initialize crypto subsystem");
        return FAILURE;
    }

    /* 2. Initialize network (libcurl) */
    if (ims_network_init() != SUCCESS) {
        php_error_docref(NULL, E_ERROR, "Failed to initialize network subsystem");
        return FAILURE;
    }

    /* 3. Determine app base path */
    /* NativePHP sets the working directory to the app root */
    if (VCWD_GETCWD(IMS_G(base_path), MAX_PATH) == NULL) {
        php_error_docref(NULL, E_ERROR, "Failed to determine base path");
        return FAILURE;
    }

    /* 4. Generate hardware fingerprint (cached for process lifetime) */
    if (ims_hardware_generate(IMS_G(hardware_id), sizeof(IMS_G(hardware_id))) != SUCCESS) {
        /* Non-fatal: hardware ID will be empty, license checks will fail */
        IMS_G(hardware_id)[0] = '\0';
    }

    /* 5. Read license from DPAPI */
    ims_license_read_from_dpapi();

    /* 6. Derive challenge-response secret */
    ims_crypto_derive_challenge_secret(IMS_G(challenge_secret));

    /* 7. Full integrity check of all files */
    if (!ims_integrity_full_check(IMS_G(base_path))) {
        IMS_G(tampered) = 1;
        /* Don't return FAILURE here -- let vendor patches handle it
           so the error is harder to trace */
    }

    /* 8. If licensed, derive DB key */
    if (IMS_G(licensed) && !IMS_G(tampered)) {
        ims_crypto_derive_db_key(
            IMS_G(license_key),
            IMS_G(hardware_id),
            IMS_G(db_key)
        );
        IMS_G(db_key_valid) = 1;
    }

    return SUCCESS;
}

/* ── MSHUTDOWN: Module Shutdown (when PHP process exits) ────── */

PHP_MSHUTDOWN_FUNCTION(ims)
{
    /* Zero out all sensitive data */
    ims_crypto_memzero(IMS_G(db_key), sizeof(IMS_G(db_key)));
    ims_crypto_memzero(IMS_G(license_key), sizeof(IMS_G(license_key)));
    ims_crypto_memzero(IMS_G(challenge_secret), sizeof(IMS_G(challenge_secret)));
    ims_crypto_memzero(IMS_G(hardware_id), sizeof(IMS_G(hardware_id)));

    IMS_G(db_key_valid) = 0;
    IMS_G(licensed) = 0;

    /* Cleanup subsystems */
    ims_network_shutdown();
    ims_crypto_shutdown();

    return SUCCESS;
}

/* ── RINIT: Request Initialization (every HTTP request) ─────── */

PHP_RINIT_FUNCTION(ims)
{
    /* 1. If tampered flag was set during MINIT, block all requests */
    if (IMS_G(tampered)) {
        return FAILURE; /* PHP aborts the request */
    }

    /* 2. Spot-check random files */
    if (!ims_integrity_spot_check(IMS_G(base_path), IMS_SPOT_CHECK_COUNT)) {
        IMS_G(tampered) = 1;
        IMS_G(db_key_valid) = 0;
        ims_crypto_memzero(IMS_G(db_key), sizeof(IMS_G(db_key)));
        return FAILURE;
    }

    /* 3. Check online verification deadline */
    if (IMS_G(licensed)) {
        int days_since = ims_license_days_since_online_verify();
        if (days_since > IMS_OFFLINE_GRACE_DAYS) {
            /* Hard block: too long since last online verification */
            IMS_G(licensed) = 0;
            IMS_G(db_key_valid) = 0;
            ims_crypto_memzero(IMS_G(db_key), sizeof(IMS_G(db_key)));
            IMS_G(needs_online_verify) = 1;
        }
    }

    return SUCCESS;
}

/* ── RSHUTDOWN: Request Shutdown (after every request) ──────── */

PHP_RSHUTDOWN_FUNCTION(ims)
{
    /* Nothing to clean per-request; DB key persists across requests
       in the module globals (process-scoped). */
    return SUCCESS;
}

/* ── MINFO: phpinfo() output ────────────────────────────────── */

PHP_MINFO_FUNCTION(ims)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "IMS Extension", "enabled");
    php_info_print_table_row(2, "Version", PHP_IMS_VERSION);
    php_info_print_table_row(2, "License Status",
        IMS_G(licensed) ? "Valid" : "Not Activated");
    php_info_print_table_row(2, "Integrity",
        IMS_G(tampered) ? "FAILED" : "OK");
    php_info_print_table_end();
}

/* ── Function Entries ───────────────────────────────────────── */

/* All function registrations are defined in functions.c / functions.h */
extern const zend_function_entry ims_functions[];

/* ── Module Entry ───────────────────────────────────────────── */

zend_module_entry ims_module_entry = {
    STANDARD_MODULE_HEADER,
    PHP_IMS_EXTNAME,
    ims_functions,          /* function entries (from functions.c) */
    PHP_MINIT(ims),
    PHP_MSHUTDOWN(ims),
    PHP_RINIT(ims),
    PHP_RSHUTDOWN(ims),
    PHP_MINFO(ims),
    PHP_IMS_VERSION,
    PHP_MODULE_GLOBALS(ims),
    PHP_GINIT(ims),
    NULL, /* GSHUTDOWN */
    NULL, /* post deactivate */
    STANDARD_MODULE_PROPERTIES_EX
};

#ifdef COMPILE_DL_IMS
ZEND_GET_MODULE(ims)
#endif
