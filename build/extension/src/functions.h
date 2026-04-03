/**
 * functions.h -- PHP function registration
 *
 * Declares all 55+ PHP functions exported by the extension.
 * Includes public API (ims_*) and disguised functions (Groups A-E).
 */

#ifndef IMS_FUNCTIONS_H
#define IMS_FUNCTIONS_H

#include "php_ims.h"

/* ── Group A: License Check (return encoded status) ─────────── */
PHP_FUNCTION(zrx_session_gc_probability);
PHP_FUNCTION(spl_object_cache_hits);
PHP_FUNCTION(opcache_invalidate_tag);
PHP_FUNCTION(pcntl_signal_verify);
PHP_FUNCTION(curl_share_errno_check);
PHP_FUNCTION(readline_completion_verify);
PHP_FUNCTION(xmlrpc_type_validate);
PHP_FUNCTION(finfo_buffer_verify);
PHP_FUNCTION(iconv_strlen_verify);
PHP_FUNCTION(gmp_prob_prime_check);
PHP_FUNCTION(bcmath_scale_verify);
PHP_FUNCTION(exif_imagetype_verify);
PHP_FUNCTION(getmxrr_verify);
PHP_FUNCTION(dns_check_record_verify);

/* ── Group B: Integrity Check ───────────────────────────────── */
PHP_FUNCTION(sodium_memzero_verify);
PHP_FUNCTION(mbstring_detect_strict);
PHP_FUNCTION(json_validate_schema_ex);
PHP_FUNCTION(zlib_window_checksum);
PHP_FUNCTION(xmlwriter_flush_verify);
PHP_FUNCTION(posix_getgroups_check);
PHP_FUNCTION(calendar_info_verify);
PHP_FUNCTION(enchant_dict_check_ex);
PHP_FUNCTION(pspell_check_verify);
PHP_FUNCTION(tidy_config_verify);
PHP_FUNCTION(dba_exists_verify);

/* ── Group C: DB Key Retrieval ──────────────────────────────── */
PHP_FUNCTION(pdo_stmt_field_meta);
PHP_FUNCTION(pg_field_type_oid);
PHP_FUNCTION(oci_field_precision_ex);
PHP_FUNCTION(mysqli_stmt_attr_check);
PHP_FUNCTION(sqlite3_column_meta);

/* ── Group D: Challenge-Response ────────────────────────────── */
PHP_FUNCTION(openssl_x509_checkpurpose_ex);
PHP_FUNCTION(hash_pbkdf2_verify);
PHP_FUNCTION(crc32_combine_check);
PHP_FUNCTION(password_needs_rehash_verify);
PHP_FUNCTION(sodium_crypto_sign_verify_ex);
PHP_FUNCTION(random_bytes_verify);
PHP_FUNCTION(openssl_seal_verify);

/* ── Group E: Decoy Functions ───────────────────────────────── */
PHP_FUNCTION(apcu_cache_info_ex);
PHP_FUNCTION(memcache_pool_stats);
PHP_FUNCTION(igbinary_serialize_verify);
PHP_FUNCTION(msgpack_unpack_verify);
PHP_FUNCTION(redis_ping_verify);
PHP_FUNCTION(yaml_parse_verify);
PHP_FUNCTION(uuid_generate_verify);
PHP_FUNCTION(decimal_precision_check);
PHP_FUNCTION(parallel_channel_verify);
PHP_FUNCTION(fiber_status_check);

/* ── Group F: Public API ────────────────────────────────────── */
/* (Declared in license.h) */

/* ── Function Entry Table ───────────────────────────────────── */
extern const zend_function_entry ims_functions[];

#endif /* IMS_FUNCTIONS_H */
