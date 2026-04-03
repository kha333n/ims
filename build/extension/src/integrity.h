/**
 * integrity.h -- File integrity verification
 *
 * Uses SHA-256 hashes compiled into integrity_hashes.h
 * to verify critical files have not been modified.
 */

#ifndef IMS_INTEGRITY_H
#define IMS_INTEGRITY_H

#include "php_ims.h"

/**
 * Full integrity check of all files in the hash map.
 * Called once during MINIT (module initialization).
 *
 * @param base_path  Application root directory
 * @return 1 if all files match, 0 if any tampered
 */
int ims_integrity_full_check(const char *base_path);

/**
 * Spot-check a random subset of files.
 * Called during RINIT (every request).
 *
 * @param base_path  Application root directory
 * @param count      Number of random files to check
 * @return 1 if all checked files match, 0 if any tampered
 */
int ims_integrity_spot_check(const char *base_path, int count);

/**
 * Get the total number of files in the integrity map.
 */
size_t ims_integrity_get_file_count(void);

#endif /* IMS_INTEGRITY_H */
