/**
 * license.c -- License management via Windows DPAPI
 *
 * Stores and retrieves license data using Windows Data Protection API.
 * DPAPI encrypts data with the Windows user's credentials, making it
 * inaccessible to other users or on other machines.
 *
 * TODO: Implement all functions. This is the skeleton.
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_ims.h"
#include "license.h"
#include "crypto.h"
#include "network.h"
#include "hardware.h"

#include <windows.h>
#include <wincrypt.h>

/* ── DPAPI Read/Write ───────────────────────────────────────── */

int ims_license_read_from_dpapi(void)
{
    /* TODO:
     * 1. Try registry: HKCU\IMS_REGISTRY_PATH\IMS_REGISTRY_VALUE
     * 2. If not found, try AppData: IMS_APPDATA_SUBDIR\IMS_APPDATA_FILENAME
     * 3. CryptUnprotectData() the blob
     * 4. Parse the decrypted struct
     * 5. Populate IMS_G(license_key), IMS_G(expires_at), etc.
     * 6. Validate: check expiry, check hardware_id matches current
     * 7. Set IMS_G(licensed) = true/false
     */
    IMS_G(licensed) = 0;
    return FAILURE;
}

int ims_license_write_to_dpapi(void)
{
    /* TODO:
     * 1. Serialize license data from IMS_G globals
     * 2. CryptProtectData() the serialized data
     * 3. Write to registry
     * 4. Write to AppData (backup)
     */
    return FAILURE;
}

int ims_license_clear(void)
{
    /* TODO:
     * 1. Delete registry value
     * 2. Delete AppData file
     * 3. Zero out IMS_G globals
     */
    ims_crypto_memzero(IMS_G(license_key), sizeof(IMS_G(license_key)));
    ims_crypto_memzero(IMS_G(db_key), sizeof(IMS_G(db_key)));
    IMS_G(licensed) = 0;
    IMS_G(db_key_valid) = 0;
    return SUCCESS;
}

int ims_license_days_since_online_verify(void)
{
    /* TODO:
     * 1. Parse IMS_G(last_verified_online) as ISO datetime
     * 2. Calculate difference from now() in days
     * 3. Return integer days
     */
    return 999; /* Default: force online check */
}

zend_bool ims_license_is_expired(void)
{
    /* TODO:
     * 1. Parse IMS_G(expires_at) as ISO date
     * 2. Compare with current date
     * 3. Return true if expired
     */
    return 1;
}

int ims_license_update_verified_timestamp(void)
{
    /* TODO:
     * 1. Set IMS_G(last_verified_online) to current ISO datetime
     * 2. Write updated data to DPAPI
     */
    return FAILURE;
}

/* ── PHP Function: ims_activate_license ─────────────────────── */

PHP_FUNCTION(ims_activate_license)
{
    char *key;
    size_t key_len;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STRING(key, key_len)
    ZEND_PARSE_PARAMETERS_END();

    /* TODO:
     * 1. Call ims_network_activate(key, hardware_id, ...)
     * 2. Verify server response signature
     * 3. Store in DPAPI via ims_license_write_to_dpapi()
     * 4. Derive DB key via ims_crypto_derive_db_key()
     * 5. Set IMS_G(licensed) = 1, IMS_G(db_key_valid) = 1
     * 6. Return ['success' => bool, 'message' => string]
     */

    array_init(return_value);
    add_assoc_bool(return_value, "success", 0);
    add_assoc_string(return_value, "message", "Not yet implemented");
}

/* ── PHP Function: ims_deactivate_license ───────────────────── */

PHP_FUNCTION(ims_deactivate_license)
{
    ZEND_PARSE_PARAMETERS_NONE();

    /* TODO:
     * 1. Call ims_network_deactivate(key, hardware_id)
     * 2. Clear DPAPI storage
     * 3. Clear DB key from memory
     */

    array_init(return_value);
    add_assoc_bool(return_value, "success", 0);
    add_assoc_string(return_value, "message", "Not yet implemented");
}

/* ── PHP Function: ims_get_license_status ───────────────────── */

PHP_FUNCTION(ims_get_license_status)
{
    ZEND_PARSE_PARAMETERS_NONE();

    array_init(return_value);
    add_assoc_bool(return_value, "valid", IMS_G(licensed) && !IMS_G(tampered));
    add_assoc_string(return_value, "status",
        !IMS_G(licensed) ? "not_activated" :
        IMS_G(tampered) ? "tampered" :
        IMS_G(needs_online_verify) ? "offline_expired" : "valid"
    );
    add_assoc_string(return_value, "hardware_id", IMS_G(hardware_id));
    add_assoc_string(return_value, "expires_at", IMS_G(expires_at));
    add_assoc_string(return_value, "last_verified", IMS_G(last_verified_online));
    add_assoc_long(return_value, "days_since_verified",
        ims_license_days_since_online_verify());
    add_assoc_bool(return_value, "needs_online_check",
        IMS_G(needs_online_verify));
}

/* ── PHP Function: ims_verify_online ────────────────────────── */

PHP_FUNCTION(ims_verify_online)
{
    ZEND_PARSE_PARAMETERS_NONE();

    /* TODO:
     * 1. Call ims_network_validate(key, hardware_id, out_expires)
     * 2. If valid: update DPAPI timestamp, refresh expires_at
     * 3. If expired/revoked: clear license
     * 4. Return bool
     */

    RETURN_FALSE;
}

/* ── PHP Function: ims_update_license ───────────────────────── */

PHP_FUNCTION(ims_update_license)
{
    char *key;
    size_t key_len;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STRING(key, key_len)
    ZEND_PARSE_PARAMETERS_END();

    /* TODO:
     * 1. Validate new key with server
     * 2. Check if key is different from current
     * 3. If different: set key_changed flag (DB key will change!)
     * 4. Update DPAPI with new key
     * 5. Re-derive DB key
     * 6. Return ['success' => bool, 'message' => string, 'key_changed' => bool]
     */

    array_init(return_value);
    add_assoc_bool(return_value, "success", 0);
    add_assoc_string(return_value, "message", "Not yet implemented");
    add_assoc_bool(return_value, "key_changed", 0);
}

/* ── PHP Function: ims_remove_license ───────────────────────── */

PHP_FUNCTION(ims_remove_license)
{
    ZEND_PARSE_PARAMETERS_NONE();

    /* TODO:
     * 1. Clear DPAPI (without server deactivation)
     * 2. Clear DB key
     * 3. Return ['success' => bool, 'message' => string]
     */

    ims_license_clear();

    array_init(return_value);
    add_assoc_bool(return_value, "success", 1);
    add_assoc_string(return_value, "message", "License removed from local storage");
}
