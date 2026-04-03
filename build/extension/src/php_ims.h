/**
 * php_ims.h -- IMS PHP Extension Header
 *
 * Core header for the Installment Management System PHP extension.
 * Defines module globals, lifecycle hooks, and shared constants.
 */

#ifndef PHP_IMS_H
#define PHP_IMS_H

#include "php.h"
#include "ext/standard/info.h"

#define PHP_IMS_EXTNAME  "ims"
#define PHP_IMS_VERSION  "1.0.0"

/* ── Module Globals ─────────────────────────────────────────── */

ZEND_BEGIN_MODULE_GLOBALS(ims)
    /* License state */
    zend_bool   licensed;              /* true if license is valid */
    zend_bool   tampered;              /* true if file integrity failed */
    zend_bool   needs_online_verify;   /* true if > 7 days since last check */

    /* DB encryption key (32 bytes, zeroed when invalid) */
    unsigned char db_key[32];
    zend_bool     db_key_valid;

    /* Hardware fingerprint (cached) */
    char hardware_id[129];             /* 128 hex chars + null */

    /* License data (from DPAPI) */
    char license_key[65];              /* max 64 chars + null */
    char expires_at[33];               /* ISO date + null */
    char last_verified_online[33];     /* ISO datetime + null */
    char customer_name[257];           /* max 256 chars + null */

    /* App base path (set during MINIT) */
    char base_path[MAX_PATH];

    /* Internal secret for challenge-response HMAC */
    unsigned char challenge_secret[32];

#ZEND_END_MODULE_GLOBALS(ims)

ZEND_EXTERN_MODULE_GLOBALS(ims)
#define IMS_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(ims, v)

/* ── Lifecycle Hooks ────────────────────────────────────────── */

PHP_MINIT_FUNCTION(ims);
PHP_MSHUTDOWN_FUNCTION(ims);
PHP_RINIT_FUNCTION(ims);
PHP_RSHUTDOWN_FUNCTION(ims);
PHP_MINFO_FUNCTION(ims);

/* ── Constants ──────────────────────────────────────────────── */

/* License server URL (hardcoded, cannot be overridden) */
#define IMS_LICENSE_SERVER_URL "https://license.yourdomain.com"

/* Offline grace period in days */
#define IMS_OFFLINE_GRACE_DAYS 7

/* Number of files to spot-check per request */
#define IMS_SPOT_CHECK_COUNT 5

/* App secret for key derivation (replace with real secret before production) */
#define IMS_APP_SECRET "REPLACE_WITH_64_CHAR_HEX_SECRET_BEFORE_PRODUCTION_BUILD_00000000"

/* DPAPI registry path (intentionally looks like Windows internal) */
#define IMS_REGISTRY_PATH "Software\\Microsoft\\Windows\\IMS\\RuntimeCache"
#define IMS_REGISTRY_VALUE "CacheData"
#define IMS_REGISTRY_BACKUP_VALUE "CacheDataBackup"

/* AppData backup path */
#define IMS_APPDATA_SUBDIR "Microsoft\\CLR\\cache"
#define IMS_APPDATA_FILENAME "ims_runtime.dat"

/* PBKDF2 iterations for DB key derivation */
#define IMS_PBKDF2_ITERATIONS 100000

#endif /* PHP_IMS_H */
