/**
 * license.h -- License management via Windows DPAPI
 */

#ifndef IMS_LICENSE_H
#define IMS_LICENSE_H

#include "php_ims.h"

/* Read license from DPAPI storage into module globals */
int ims_license_read_from_dpapi(void);

/* Write current license data from globals to DPAPI */
int ims_license_write_to_dpapi(void);

/* Clear license from all storage locations */
int ims_license_clear(void);

/* Calculate days since last online verification */
int ims_license_days_since_online_verify(void);

/* Check if license is expired based on expires_at */
zend_bool ims_license_is_expired(void);

/* Update last_verified_online timestamp to now */
int ims_license_update_verified_timestamp(void);

/* ── PHP Function Implementations ───────────────────────────── */

PHP_FUNCTION(ims_activate_license);
PHP_FUNCTION(ims_deactivate_license);
PHP_FUNCTION(ims_get_license_status);
PHP_FUNCTION(ims_verify_online);
PHP_FUNCTION(ims_update_license);
PHP_FUNCTION(ims_remove_license);

#endif /* IMS_LICENSE_H */
