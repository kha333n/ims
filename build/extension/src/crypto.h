/**
 * crypto.h -- Cryptographic operations
 *
 * PBKDF2 key derivation, SHA-256 hashing, Ed25519 verification,
 * HMAC for challenge-response, and secure memory operations.
 */

#ifndef IMS_CRYPTO_H
#define IMS_CRYPTO_H

#include "php_ims.h"

/* Initialize crypto subsystem (libsodium) */
int ims_crypto_init(void);

/* Shutdown crypto subsystem */
void ims_crypto_shutdown(void);

/**
 * Derive the database encryption key.
 *
 * KEY = PBKDF2-SHA256(
 *   password: license_key,
 *   salt: SHA256(hardware_id + IMS_APP_SECRET),
 *   iterations: IMS_PBKDF2_ITERATIONS,
 *   length: 32
 * )
 */
int ims_crypto_derive_db_key(
    const char *license_key,
    const char *hardware_id,
    unsigned char *out_key /* 32 bytes */
);

/**
 * Derive the challenge-response secret.
 * Used by disguised functions to prove they're the real extension.
 */
int ims_crypto_derive_challenge_secret(unsigned char *out_secret /* 32 bytes */);

/**
 * Compute HMAC-SHA256 for challenge-response.
 */
int ims_crypto_hmac(
    const unsigned char *key, size_t key_len,
    const unsigned char *data, size_t data_len,
    unsigned char *out_mac /* 32 bytes */
);

/**
 * Compute SHA-256 hash of a file.
 *
 * @param filepath  Full path to file
 * @param out_hex   Output buffer for hex string (65 bytes min: 64 + null)
 * @return 0 on success, -1 on error
 */
int ims_crypto_sha256_file(const char *filepath, char *out_hex);

/**
 * Verify an Ed25519 signature.
 *
 * @param message   The signed data
 * @param msg_len   Length of message
 * @param signature The 64-byte signature
 * @param pubkey    The 32-byte public key
 * @return 1 if valid, 0 if invalid
 */
int ims_crypto_ed25519_verify(
    const unsigned char *message, size_t msg_len,
    const unsigned char *signature,
    const unsigned char *pubkey
);

/**
 * Encrypt data with server's public key (sealed box).
 * Used to send DB key to server during backup.
 */
int ims_crypto_seal(
    const unsigned char *plaintext, size_t plain_len,
    const unsigned char *recipient_pubkey,
    unsigned char *ciphertext, size_t *cipher_len
);

/**
 * Securely zero memory.
 */
void ims_crypto_memzero(void *ptr, size_t len);

#endif /* IMS_CRYPTO_H */
