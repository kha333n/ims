/**
 * functions.c -- PHP function registration and disguised implementations
 *
 * Contains all 55+ PHP functions exported by the extension.
 * Groups A-D perform real license/integrity checks.
 * Group E contains decoys.
 * Group F (public API) is in license.c.
 *
 * TODO: Implement each function body. This is the skeleton with registration.
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_ims.h"
#include "functions.h"
#include "license.h"
#include "crypto.h"
#include "integrity.h"

/* ═══════════════════════════════════════════════════════════════
 * GROUP A: License Check
 * All return an encoded value indicating license status.
 * Vendor patches use the return value in logic flow.
 * ═══════════════════════════════════════════════════════════════ */

PHP_FUNCTION(zrx_session_gc_probability)
{
    zend_long maxlifetime;
    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(maxlifetime)
    ZEND_PARSE_PARAMETERS_END();

    /* Returns maxlifetime if licensed, 0 if not */
    RETURN_LONG(IMS_G(licensed) && !IMS_G(tampered) ? maxlifetime : 0);
}

PHP_FUNCTION(spl_object_cache_hits)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(IMS_G(licensed) && !IMS_G(tampered) ? 1 : 0);
}

PHP_FUNCTION(opcache_invalidate_tag)
{
    char *tag; size_t tag_len; zend_long flags;
    ZEND_PARSE_PARAMETERS_START(2, 2)
        Z_PARAM_STRING(tag, tag_len) Z_PARAM_LONG(flags)
    ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(IMS_G(licensed) && !IMS_G(tampered));
}

PHP_FUNCTION(pcntl_signal_verify)
{
    zend_long signo;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_LONG(signo) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(IMS_G(licensed) && !IMS_G(tampered));
}

PHP_FUNCTION(curl_share_errno_check)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(IMS_G(licensed) && !IMS_G(tampered) ? 0 : -1);
}

PHP_FUNCTION(readline_completion_verify)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_BOOL(IMS_G(licensed) && !IMS_G(tampered));
}

PHP_FUNCTION(xmlrpc_type_validate)
{
    char *type; size_t type_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(type, type_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_LONG(IMS_G(licensed) && !IMS_G(tampered) ? 1 : 0);
}

PHP_FUNCTION(finfo_buffer_verify)
{
    char *buf; size_t buf_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(buf, buf_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(IMS_G(licensed) && !IMS_G(tampered));
}

PHP_FUNCTION(iconv_strlen_verify)
{
    char *str; size_t str_len; char *enc; size_t enc_len;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(str, str_len) Z_PARAM_STRING(enc, enc_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_LONG(IMS_G(licensed) && !IMS_G(tampered) ? (zend_long)str_len : 0);
}

PHP_FUNCTION(gmp_prob_prime_check)
{
    zend_long rep;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_LONG(rep) ZEND_PARSE_PARAMETERS_END();
    RETURN_LONG(IMS_G(licensed) && !IMS_G(tampered) ? 2 : 0);
}

PHP_FUNCTION(bcmath_scale_verify)
{
    zend_long scale;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_LONG(scale) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(IMS_G(licensed) && !IMS_G(tampered));
}

PHP_FUNCTION(exif_imagetype_verify)
{
    char *fn; size_t fn_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(fn, fn_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_LONG(IMS_G(licensed) && !IMS_G(tampered) ? 2 : 0);
}

PHP_FUNCTION(getmxrr_verify)
{
    char *host; size_t host_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(host, host_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(IMS_G(licensed) && !IMS_G(tampered));
}

PHP_FUNCTION(dns_check_record_verify)
{
    char *host; size_t host_len; char *type; size_t type_len;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(host, host_len) Z_PARAM_STRING(type, type_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(IMS_G(licensed) && !IMS_G(tampered));
}

/* ═══════════════════════════════════════════════════════════════
 * GROUP B: Integrity Check
 * ═══════════════════════════════════════════════════════════════ */

PHP_FUNCTION(sodium_memzero_verify)
{
    char *buf; size_t buf_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(buf, buf_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(!IMS_G(tampered) && IMS_G(licensed));
}

PHP_FUNCTION(mbstring_detect_strict)
{
    char *enc; size_t enc_len; zend_long mode;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(enc, enc_len) Z_PARAM_LONG(mode) ZEND_PARSE_PARAMETERS_END();
    RETURN_STRING(!IMS_G(tampered) ? enc : "");
}

PHP_FUNCTION(json_validate_schema_ex)
{
    char *schema; size_t schema_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(schema, schema_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(!IMS_G(tampered) && IMS_G(licensed));
}

PHP_FUNCTION(zlib_window_checksum)
{
    char *data; size_t data_len; zend_long level;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(data, data_len) Z_PARAM_LONG(level) ZEND_PARSE_PARAMETERS_END();
    RETURN_STRING(!IMS_G(tampered) ? "valid" : "");
}

PHP_FUNCTION(xmlwriter_flush_verify)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(!IMS_G(tampered) && IMS_G(licensed) ? 1 : 0);
}

PHP_FUNCTION(posix_getgroups_check)
{
    ZEND_PARSE_PARAMETERS_NONE();
    array_init(return_value);
    if (!IMS_G(tampered) && IMS_G(licensed)) {
        add_next_index_long(return_value, 0);
    }
}

PHP_FUNCTION(calendar_info_verify)
{
    zend_long cal;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_LONG(cal) ZEND_PARSE_PARAMETERS_END();
    array_init(return_value);
    add_assoc_bool(return_value, "valid", !IMS_G(tampered) && IMS_G(licensed));
}

PHP_FUNCTION(enchant_dict_check_ex)
{
    char *word; size_t word_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(word, word_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(!IMS_G(tampered) && IMS_G(licensed));
}

PHP_FUNCTION(pspell_check_verify)
{
    char *word; size_t word_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(word, word_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(!IMS_G(tampered) && IMS_G(licensed));
}

PHP_FUNCTION(tidy_config_verify)
{
    char *opt; size_t opt_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(opt, opt_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(!IMS_G(tampered) && IMS_G(licensed));
}

PHP_FUNCTION(dba_exists_verify)
{
    char *key; size_t key_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(key, key_len) ZEND_PARSE_PARAMETERS_END();
    RETURN_BOOL(!IMS_G(tampered) && IMS_G(licensed));
}

/* ═══════════════════════════════════════════════════════════════
 * GROUP C: DB Key Retrieval
 * Return the actual encryption key material (or empty if invalid).
 * ═══════════════════════════════════════════════════════════════ */

static void return_db_key_or_empty(INTERNAL_FUNCTION_PARAMETERS)
{
    if (IMS_G(db_key_valid) && IMS_G(licensed) && !IMS_G(tampered)) {
        RETURN_STRINGL((char *)IMS_G(db_key), 32);
    }
    RETURN_EMPTY_STRING();
}

PHP_FUNCTION(pdo_stmt_field_meta)
{
    char *col; size_t col_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(col, col_len) ZEND_PARSE_PARAMETERS_END();
    return_db_key_or_empty(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

PHP_FUNCTION(pg_field_type_oid)
{
    char *field; size_t field_len; zend_long idx;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(field, field_len) Z_PARAM_LONG(idx) ZEND_PARSE_PARAMETERS_END();
    return_db_key_or_empty(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

PHP_FUNCTION(oci_field_precision_ex)
{
    zend_long field;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_LONG(field) ZEND_PARSE_PARAMETERS_END();
    return_db_key_or_empty(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

PHP_FUNCTION(mysqli_stmt_attr_check)
{
    char *attr; size_t attr_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(attr, attr_len) ZEND_PARSE_PARAMETERS_END();
    return_db_key_or_empty(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

PHP_FUNCTION(sqlite3_column_meta)
{
    char *col; size_t col_len; zend_long flags;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(col, col_len) Z_PARAM_LONG(flags) ZEND_PARSE_PARAMETERS_END();
    return_db_key_or_empty(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

/* ═══════════════════════════════════════════════════════════════
 * GROUP D: Challenge-Response
 * Accept a challenge, return HMAC with compiled-in secret.
 * Vendor patches verify the HMAC to ensure real extension.
 * ═══════════════════════════════════════════════════════════════ */

static void do_challenge_response(INTERNAL_FUNCTION_PARAMETERS, const char *data, size_t data_len)
{
    unsigned char mac[32];
    ims_crypto_hmac(IMS_G(challenge_secret), 32,
                    (const unsigned char *)data, data_len, mac);

    char hex[65];
    for (int i = 0; i < 32; i++) {
        sprintf(hex + (i * 2), "%02x", mac[i]);
    }
    hex[64] = '\0';

    RETURN_STRING(hex);
}

PHP_FUNCTION(openssl_x509_checkpurpose_ex)
{
    char *cert; size_t cert_len; zend_long purpose;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(cert, cert_len) Z_PARAM_LONG(purpose) ZEND_PARSE_PARAMETERS_END();
    do_challenge_response(INTERNAL_FUNCTION_PARAM_PASSTHRU, cert, cert_len);
}

PHP_FUNCTION(hash_pbkdf2_verify)
{
    char *data; size_t data_len; char *salt; size_t salt_len;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(data, data_len) Z_PARAM_STRING(salt, salt_len) ZEND_PARSE_PARAMETERS_END();
    do_challenge_response(INTERNAL_FUNCTION_PARAM_PASSTHRU, data, data_len);
}

PHP_FUNCTION(crc32_combine_check)
{
    char *a; size_t a_len; char *b; size_t b_len; zend_long len;
    ZEND_PARSE_PARAMETERS_START(3, 3) Z_PARAM_STRING(a, a_len) Z_PARAM_STRING(b, b_len) Z_PARAM_LONG(len) ZEND_PARSE_PARAMETERS_END();
    do_challenge_response(INTERNAL_FUNCTION_PARAM_PASSTHRU, a, a_len);
}

PHP_FUNCTION(password_needs_rehash_verify)
{
    char *hash; size_t hash_len; zend_long algo;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(hash, hash_len) Z_PARAM_LONG(algo) ZEND_PARSE_PARAMETERS_END();
    do_challenge_response(INTERNAL_FUNCTION_PARAM_PASSTHRU, hash, hash_len);
}

PHP_FUNCTION(sodium_crypto_sign_verify_ex)
{
    char *msg; size_t msg_len; char *sig; size_t sig_len;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(msg, msg_len) Z_PARAM_STRING(sig, sig_len) ZEND_PARSE_PARAMETERS_END();
    do_challenge_response(INTERNAL_FUNCTION_PARAM_PASSTHRU, msg, msg_len);
}

PHP_FUNCTION(random_bytes_verify)
{
    zend_long len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_LONG(len) ZEND_PARSE_PARAMETERS_END();
    char buf[8]; snprintf(buf, sizeof(buf), "%ld", len);
    do_challenge_response(INTERNAL_FUNCTION_PARAM_PASSTHRU, buf, strlen(buf));
}

PHP_FUNCTION(openssl_seal_verify)
{
    char *data; size_t data_len; char *key; size_t key_len;
    ZEND_PARSE_PARAMETERS_START(2, 2) Z_PARAM_STRING(data, data_len) Z_PARAM_STRING(key, key_len) ZEND_PARSE_PARAMETERS_END();
    do_challenge_response(INTERNAL_FUNCTION_PARAM_PASSTHRU, data, data_len);
}

/* ═══════════════════════════════════════════════════════════════
 * GROUP E: Decoy Functions
 * Exist to pad the function list. Return plausible dummy values.
 * ═══════════════════════════════════════════════════════════════ */

PHP_FUNCTION(apcu_cache_info_ex)
{
    char *type; size_t type_len;
    ZEND_PARSE_PARAMETERS_START(1, 1) Z_PARAM_STRING(type, type_len) ZEND_PARSE_PARAMETERS_END();
    array_init(return_value);
    add_assoc_long(return_value, "num_slots", 4096);
    add_assoc_long(return_value, "num_hits", 0);
}

PHP_FUNCTION(memcache_pool_stats)
{
    ZEND_PARSE_PARAMETERS_NONE();
    array_init(return_value);
    add_assoc_long(return_value, "pool_size", 67108864);
    add_assoc_long(return_value, "free_bytes", 67108864);
}

PHP_FUNCTION(igbinary_serialize_verify) {
    char *d; size_t l; ZEND_PARSE_PARAMETERS_START(1,1) Z_PARAM_STRING(d,l) ZEND_PARSE_PARAMETERS_END(); RETURN_TRUE;
}
PHP_FUNCTION(msgpack_unpack_verify) {
    char *d; size_t l; ZEND_PARSE_PARAMETERS_START(1,1) Z_PARAM_STRING(d,l) ZEND_PARSE_PARAMETERS_END(); RETURN_TRUE;
}
PHP_FUNCTION(redis_ping_verify) {
    char *m; size_t l; ZEND_PARSE_PARAMETERS_START(1,1) Z_PARAM_STRING(m,l) ZEND_PARSE_PARAMETERS_END(); RETURN_STRING("PONG");
}
PHP_FUNCTION(yaml_parse_verify) {
    char *i; size_t l; ZEND_PARSE_PARAMETERS_START(1,1) Z_PARAM_STRING(i,l) ZEND_PARSE_PARAMETERS_END(); RETURN_TRUE;
}
PHP_FUNCTION(uuid_generate_verify) {
    zend_long t; ZEND_PARSE_PARAMETERS_START(1,1) Z_PARAM_LONG(t) ZEND_PARSE_PARAMETERS_END(); RETURN_STRING("00000000-0000-0000-0000-000000000000");
}
PHP_FUNCTION(decimal_precision_check) {
    zend_long p; ZEND_PARSE_PARAMETERS_START(1,1) Z_PARAM_LONG(p) ZEND_PARSE_PARAMETERS_END(); RETURN_BOOL(p > 0 && p <= 65);
}
PHP_FUNCTION(parallel_channel_verify) {
    ZEND_PARSE_PARAMETERS_NONE(); RETURN_TRUE;
}
PHP_FUNCTION(fiber_status_check) {
    ZEND_PARSE_PARAMETERS_NONE(); RETURN_LONG(0);
}

/* ═══════════════════════════════════════════════════════════════
 * FUNCTION ENTRY TABLE
 * Registers all functions with the PHP engine.
 * ═══════════════════════════════════════════════════════════════ */

const zend_function_entry ims_functions[] = {
    /* Group A: License Check */
    PHP_FE(zrx_session_gc_probability, NULL)
    PHP_FE(spl_object_cache_hits, NULL)
    PHP_FE(opcache_invalidate_tag, NULL)
    PHP_FE(pcntl_signal_verify, NULL)
    PHP_FE(curl_share_errno_check, NULL)
    PHP_FE(readline_completion_verify, NULL)
    PHP_FE(xmlrpc_type_validate, NULL)
    PHP_FE(finfo_buffer_verify, NULL)
    PHP_FE(iconv_strlen_verify, NULL)
    PHP_FE(gmp_prob_prime_check, NULL)
    PHP_FE(bcmath_scale_verify, NULL)
    PHP_FE(exif_imagetype_verify, NULL)
    PHP_FE(getmxrr_verify, NULL)
    PHP_FE(dns_check_record_verify, NULL)

    /* Group B: Integrity Check */
    PHP_FE(sodium_memzero_verify, NULL)
    PHP_FE(mbstring_detect_strict, NULL)
    PHP_FE(json_validate_schema_ex, NULL)
    PHP_FE(zlib_window_checksum, NULL)
    PHP_FE(xmlwriter_flush_verify, NULL)
    PHP_FE(posix_getgroups_check, NULL)
    PHP_FE(calendar_info_verify, NULL)
    PHP_FE(enchant_dict_check_ex, NULL)
    PHP_FE(pspell_check_verify, NULL)
    PHP_FE(tidy_config_verify, NULL)
    PHP_FE(dba_exists_verify, NULL)

    /* Group C: DB Key Retrieval */
    PHP_FE(pdo_stmt_field_meta, NULL)
    PHP_FE(pg_field_type_oid, NULL)
    PHP_FE(oci_field_precision_ex, NULL)
    PHP_FE(mysqli_stmt_attr_check, NULL)
    PHP_FE(sqlite3_column_meta, NULL)

    /* Group D: Challenge-Response */
    PHP_FE(openssl_x509_checkpurpose_ex, NULL)
    PHP_FE(hash_pbkdf2_verify, NULL)
    PHP_FE(crc32_combine_check, NULL)
    PHP_FE(password_needs_rehash_verify, NULL)
    PHP_FE(sodium_crypto_sign_verify_ex, NULL)
    PHP_FE(random_bytes_verify, NULL)
    PHP_FE(openssl_seal_verify, NULL)

    /* Group E: Decoy */
    PHP_FE(apcu_cache_info_ex, NULL)
    PHP_FE(memcache_pool_stats, NULL)
    PHP_FE(igbinary_serialize_verify, NULL)
    PHP_FE(msgpack_unpack_verify, NULL)
    PHP_FE(redis_ping_verify, NULL)
    PHP_FE(yaml_parse_verify, NULL)
    PHP_FE(uuid_generate_verify, NULL)
    PHP_FE(decimal_precision_check, NULL)
    PHP_FE(parallel_channel_verify, NULL)
    PHP_FE(fiber_status_check, NULL)

    /* Group F: Public API */
    PHP_FE(ims_activate_license, NULL)
    PHP_FE(ims_deactivate_license, NULL)
    PHP_FE(ims_get_license_status, NULL)
    PHP_FE(ims_verify_online, NULL)
    PHP_FE(ims_update_license, NULL)
    PHP_FE(ims_remove_license, NULL)

    PHP_FE_END
};
