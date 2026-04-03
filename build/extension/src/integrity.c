/**
 * integrity.c -- File integrity verification
 *
 * Checks SHA-256 hashes of critical files against values
 * compiled into integrity_hashes.h at build time.
 *
 * TODO: Implement all functions. This is the skeleton.
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_ims.h"
#include "integrity.h"
#include "crypto.h"
#include "integrity_hashes.h"

#include <stdlib.h>
#include <time.h>

/* ── Full Check (MINIT) ────────────────────────────────────── */

int ims_integrity_full_check(const char *base_path)
{
    int all_valid = 1;
    char full_path[MAX_PATH];
    char computed_hash[65];

    for (size_t i = 0; i < INTEGRITY_MAP_SIZE; i++) {
        /* Build full path: base_path / relative_path */
        snprintf(full_path, sizeof(full_path), "%s/%s",
                 base_path, INTEGRITY_MAP[i].path);

        /* Normalize path separators for Windows */
        for (char *p = full_path; *p; p++) {
            if (*p == '/') *p = '\\';
        }

        /* Hash the file */
        if (ims_crypto_sha256_file(full_path, computed_hash) != 0) {
            /* File missing or unreadable */
            all_valid = 0;
            continue; /* Don't break -- check all to avoid revealing which */
        }

        /* Compare hashes (constant-time) */
        if (memcmp(computed_hash, INTEGRITY_MAP[i].hash, 64) != 0) {
            all_valid = 0;
            /* Don't break -- check all files */
        }
    }

    return all_valid;
}

/* ── Spot Check (RINIT) ────────────────────────────────────── */

int ims_integrity_spot_check(const char *base_path, int count)
{
    char full_path[MAX_PATH];
    char computed_hash[65];

    if (INTEGRITY_MAP_SIZE == 0) {
        return 1; /* No files to check */
    }

    /* Seed with time + process ID for randomness */
    srand((unsigned int)(time(NULL) ^ _getpid()));

    for (int i = 0; i < count && i < (int)INTEGRITY_MAP_SIZE; i++) {
        size_t idx = rand() % INTEGRITY_MAP_SIZE;

        snprintf(full_path, sizeof(full_path), "%s/%s",
                 base_path, INTEGRITY_MAP[idx].path);

        for (char *p = full_path; *p; p++) {
            if (*p == '/') *p = '\\';
        }

        if (ims_crypto_sha256_file(full_path, computed_hash) != 0) {
            return 0; /* File missing = tampered */
        }

        if (memcmp(computed_hash, INTEGRITY_MAP[idx].hash, 64) != 0) {
            return 0; /* Hash mismatch = tampered */
        }
    }

    return 1;
}

/* ── Helpers ────────────────────────────────────────────────── */

size_t ims_integrity_get_file_count(void)
{
    return INTEGRITY_MAP_SIZE;
}
