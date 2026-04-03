/**
 * network.h -- License server communication
 *
 * HTTPS communication with the license server using libcurl.
 * Server URL is hardcoded. Responses are Ed25519 signed.
 */

#ifndef IMS_NETWORK_H
#define IMS_NETWORK_H

#include "php_ims.h"

/* Initialize network subsystem (libcurl) */
int ims_network_init(void);

/* Shutdown network subsystem */
void ims_network_shutdown(void);

/**
 * Activate a license key with the server.
 *
 * @param key          License key string
 * @param hardware_id  Hardware fingerprint
 * @param out_expires  Buffer for expires_at date (33 bytes)
 * @param out_customer Buffer for customer name (257 bytes)
 * @param out_message  Buffer for server message (512 bytes)
 * @return 1 on success, 0 on failure
 */
int ims_network_activate(
    const char *key,
    const char *hardware_id,
    char *out_expires,
    char *out_customer,
    char *out_message
);

/**
 * Deactivate a license key (release hardware slot).
 */
int ims_network_deactivate(
    const char *key,
    const char *hardware_id,
    char *out_message
);

/**
 * Validate a license with the server (periodic check).
 *
 * @param key          License key
 * @param hardware_id  Hardware fingerprint
 * @param out_expires  Updated expires_at (may change if renewed)
 * @return 1 if valid, 0 if invalid/unreachable
 */
int ims_network_validate(
    const char *key,
    const char *hardware_id,
    char *out_expires
);

#endif /* IMS_NETWORK_H */
