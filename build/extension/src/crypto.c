/**
 * crypto.c -- Cryptographic operations
 *
 * TODO: Implement all functions. This is the skeleton.
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_ims.h"
#include "crypto.h"

#include <sodium.h>
#include <string.h>
#include <stdio.h>

int ims_crypto_init(void)
{
    if (sodium_init() < 0) {
        return FAILURE;
    }
    return SUCCESS;
}

void ims_crypto_shutdown(void)
{
    /* libsodium has no global cleanup */
}

int ims_crypto_derive_db_key(
    const char *license_key,
    const char *hardware_id,
    unsigned char *out_key)
{
    /* TODO:
     * 1. Compute salt = SHA256(hardware_id + IMS_APP_SECRET)
     * 2. out_key = PBKDF2-SHA256(license_key, salt, IMS_PBKDF2_ITERATIONS, 32)
     * 3. Return SUCCESS
     *
     * Use libsodium's crypto_pwhash or manual PBKDF2 with OpenSSL
     */
    memset(out_key, 0, 32);
    return FAILURE;
}

int ims_crypto_derive_challenge_secret(unsigned char *out_secret)
{
    /* TODO:
     * Derive from IMS_APP_SECRET + "challenge" salt
     * This secret is used by Group D functions for HMAC
     */
    memset(out_secret, 0, 32);
    return FAILURE;
}

int ims_crypto_hmac(
    const unsigned char *key, size_t key_len,
    const unsigned char *data, size_t data_len,
    unsigned char *out_mac)
{
    /* TODO:
     * Compute HMAC-SHA256(key, data) -> out_mac (32 bytes)
     * Use crypto_auth_hmacsha256 from libsodium
     */
    memset(out_mac, 0, 32);
    return FAILURE;
}

int ims_crypto_sha256_file(const char *filepath, char *out_hex)
{
    /* TODO:
     * 1. Open file
     * 2. Read in chunks, feed to crypto_hash_sha256
     * 3. Convert 32-byte hash to 64-char hex string
     * 4. Write to out_hex (must be at least 65 bytes)
     */

    FILE *fp = fopen(filepath, "rb");
    if (!fp) {
        return -1;
    }

    crypto_hash_sha256_state state;
    crypto_hash_sha256_init(&state);

    unsigned char buf[8192];
    size_t n;
    while ((n = fread(buf, 1, sizeof(buf), fp)) > 0) {
        crypto_hash_sha256_update(&state, buf, n);
    }
    fclose(fp);

    unsigned char hash[crypto_hash_sha256_BYTES];
    crypto_hash_sha256_final(&state, hash);

    /* Convert to hex */
    for (size_t i = 0; i < crypto_hash_sha256_BYTES; i++) {
        sprintf(out_hex + (i * 2), "%02x", hash[i]);
    }
    out_hex[64] = '\0';

    return 0;
}

int ims_crypto_ed25519_verify(
    const unsigned char *message, size_t msg_len,
    const unsigned char *signature,
    const unsigned char *pubkey)
{
    return crypto_sign_verify_detached(signature, message, msg_len, pubkey) == 0 ? 1 : 0;
}

int ims_crypto_seal(
    const unsigned char *plaintext, size_t plain_len,
    const unsigned char *recipient_pubkey,
    unsigned char *ciphertext, size_t *cipher_len)
{
    /* Sealed box: encrypt with recipient's public key */
    *cipher_len = plain_len + crypto_box_SEALBYTES;
    return crypto_box_seal(ciphertext, plaintext, plain_len, recipient_pubkey) == 0
        ? SUCCESS : FAILURE;
}

void ims_crypto_memzero(void *ptr, size_t len)
{
    sodium_memzero(ptr, len);
}
