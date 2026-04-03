# PHP Extension: php_ims.dll

## Overview

`php_ims.dll` is a custom PHP extension written in C that serves as the core security enforcement layer. Because it's compiled machine code, it cannot be read or casually edited like PHP files. It handles:

- License storage and validation (Windows DPAPI)
- Hardware fingerprinting
- File integrity verification (SHA-256 hashes compiled in)
- Database encryption key derivation and delivery
- License server communication (Ed25519 signed)
- 55+ disguised PHP functions called by vendor patches

---

## Source File Map

```
build/extension/
+-- CMakeLists.txt              Build configuration (CMake)
+-- src/
|   +-- php_ims.c               Module entry point, MINIT/MSHUTDOWN/RINIT/RSHUTDOWN
|   +-- php_ims.h               Main header, module globals
|   +-- license.c               DPAPI storage, activate, deactivate, validate, status
|   +-- license.h
|   +-- integrity.c             File hash checking, spot-check logic
|   +-- integrity.h
|   +-- integrity_hashes.h      AUTO-GENERATED: hash map of ~200-250 critical files
|   +-- crypto.c                PBKDF2 key derivation, HMAC, Ed25519 verify
|   +-- crypto.h
|   +-- network.c               HTTPS to license server via libcurl
|   +-- network.h
|   +-- hardware.c              Hardware fingerprint (CPU ID, disk serial, MAC, machine GUID)
|   +-- hardware.h
|   +-- functions.c             55+ disguised PHP function exports
|   +-- functions.h
|   +-- server_pubkey.h         Ed25519 public key for signature verification
+-- deps/                       Vendored C libraries (downloaded by build script)
|   +-- curl/                   libcurl (static link, for HTTPS)
|   +-- sodium/                 libsodium (Ed25519, encryption primitives)
|   +-- sqlite3/                SQLCipher source (for custom pdo_sqlite.dll)
+-- tests/                      C unit tests
```

---

## PHP Lifecycle Hooks

### MINIT (Module Init) -- Once when PHP starts

```c
PHP_MINIT_FUNCTION(ims)
{
    // 1. Initialize libsodium
    // 2. Initialize libcurl
    // 3. Register module globals
    // 4. Register all 55+ PHP functions
    // 5. Verify php.exe integrity (hash of the binary itself)
    // 6. Full integrity check of ALL critical files (one-time on boot)
    //    - Read integrity_hashes.h compiled-in hash map
    //    - Hash each file, compare
    //    - If ANY mismatch: set global tampered=true flag
    // 7. Read license from DPAPI
    //    - If valid: derive DB key, store in module globals
    //    - If expired/missing: set global licensed=false
    return SUCCESS;
}
```

### RINIT (Request Init) -- Every HTTP request, before PHP code runs

```c
PHP_RINIT_FUNCTION(ims)
{
    // 1. Check tampered flag (set during MINIT)
    //    If tampered: return FAILURE -> PHP aborts request
    //
    // 2. Check licensed flag
    //    If not licensed: clear DB key from request globals
    //
    // 3. Spot-check 3-5 random files from hash map
    //    Pick random indices, verify hashes
    //    If any fail: set tampered=true, return FAILURE
    //
    // 4. Check online verification deadline
    //    If last_verified_online > 7 days: set licensed=false, clear DB key
    //
    // 5. If all checks pass: copy DB key to request-scoped storage
    return SUCCESS;
}
```

### RSHUTDOWN (Request Shutdown) -- After every request

```c
PHP_RSHUTDOWN_FUNCTION(ims)
{
    // 1. Zero out request-scoped DB key from memory
    //    (sodium_memzero equivalent)
    return SUCCESS;
}
```

### MSHUTDOWN (Module Shutdown) -- When PHP process exits

```c
PHP_MSHUTDOWN_FUNCTION(ims)
{
    // 1. Zero out all sensitive data (license, DB key, etc.)
    // 2. Cleanup libcurl
    // 3. Cleanup libsodium
    return SUCCESS;
}
```

---

## Public API Functions (Group F)

These are called from app-level PHP code. They have obvious `ims_` prefixed names because they're part of the visible license UI (the decoy layer users are meant to find).

### `ims_activate_license(string $key): array`

Activates a license key on this machine.

1. Generates hardware fingerprint
2. Sends HTTPS POST to license server: `{key, hardware_id, nonce}`
3. Verifies Ed25519 signature on response
4. If valid: stores license in DPAPI, derives DB key
5. Returns: `['success' => bool, 'message' => string, 'expires_at' => string|null]`

### `ims_deactivate_license(): array`

Deactivates the current license (frees the hardware slot on the server).

1. Reads current license from DPAPI
2. Sends deactivation request to server
3. If successful: clears DPAPI, clears DB key from memory
4. Returns: `['success' => bool, 'message' => string]`

### `ims_get_license_status(): array`

Returns the current license state. No server call, reads from DPAPI only.

```php
[
    'valid'              => bool,    // true if licensed and not expired
    'status'             => string,  // 'valid', 'expired', 'not_activated', 'offline_expired'
    'key_masked'         => string,  // 'IMS-****-****-XXXX'
    'hardware_id'        => string,  // current machine fingerprint
    'expires_at'         => string,  // ISO date
    'days_until_expiry'  => int,
    'last_verified'      => string,  // ISO datetime of last server check
    'days_since_verified'=> int,
    'needs_online_check' => bool,    // true if > 5 days since last verify
]
```

### `ims_verify_online(): bool`

Attempts to verify the license with the server. Non-blocking conceptually (but the HTTP call itself blocks briefly). Returns true if verified, false if offline or invalid.

1. Reads license key from DPAPI
2. Sends validation request to server
3. If valid: updates `last_verified_online` in DPAPI, returns true
4. If offline: returns false (does NOT invalidate license)
5. If server says expired/revoked: updates DPAPI, sets licensed=false

### `ims_update_license(string $key): array`

Updates to a new license key. Used for renewals or key changes.

1. Validates new key with server
2. If valid: replaces old license in DPAPI
3. **Critical**: If new key is different from old key, DB key changes
   - Old DB becomes inaccessible with new key
   - Extension returns a warning flag so app can warn user
4. Returns: `['success' => bool, 'message' => string, 'key_changed' => bool]`

### `ims_remove_license(): array`

Removes license from local storage. Does NOT deactivate on server.
Used for cleanup or troubleshooting.

---

## Disguised Function Groups

All functions below perform license and/or integrity checks internally. They are called from vendor patches and hidden PHP enforcement points. Their names are designed to look like standard PHP extension functions.

### Group A: License Check (return encoded status as int/bool)

These return an integer or boolean that encodes the license status. Vendor patches use the return value in conditional logic.

| Function | Signature | Looks Like |
|----------|-----------|------------|
| `zrx_session_gc_probability` | `(int $maxlifetime): int` | Session GC config |
| `spl_object_cache_hits` | `(): int` | SPL internals |
| `opcache_invalidate_tag` | `(string $tag, int $flags): bool` | OPcache |
| `pcntl_signal_verify` | `(int $signo): bool` | PCNTL signals |
| `curl_share_errno_check` | `(): int` | cURL errors |
| `readline_completion_verify` | `(): bool` | Readline |
| `xmlrpc_type_validate` | `(string $type): int` | XML-RPC |
| `finfo_buffer_verify` | `(string $buf): bool` | Fileinfo |
| `iconv_strlen_verify` | `(string $str, string $enc): int` | iconv |
| `gmp_prob_prime_check` | `(int $rep): int` | GMP math |
| `bcmath_scale_verify` | `(int $scale): bool` | BCMath |
| `exif_imagetype_verify` | `(string $fn): int` | EXIF |
| `getmxrr_verify` | `(string $host): bool` | DNS |
| `dns_check_record_verify` | `(string $host, string $type): bool` | DNS |

### Group B: Integrity Check (verify file hashes, return bool)

These trigger file integrity checks and return whether files are intact.

| Function | Signature | Looks Like |
|----------|-----------|------------|
| `sodium_memzero_verify` | `(string $buf): bool` | Sodium crypto |
| `mbstring_detect_strict` | `(string $enc, int $mode): string` | mbstring |
| `json_validate_schema_ex` | `(string $schema): bool` | JSON validation |
| `zlib_window_checksum` | `(string $data, int $level): string` | zlib |
| `xmlwriter_flush_verify` | `(): int` | XMLWriter |
| `posix_getgroups_check` | `(): array` | POSIX |
| `calendar_info_verify` | `(int $cal): array` | Calendar |
| `enchant_dict_check_ex` | `(string $word): bool` | Enchant spelling |
| `pspell_check_verify` | `(string $word): bool` | PSpell |
| `tidy_config_verify` | `(string $opt): bool` | Tidy HTML |
| `dba_exists_verify` | `(string $key): bool` | DBA |

### Group C: DB Key Retrieval (return encryption material as string)

These return the database encryption key (or a derivative). Vendor patches in the database layer use the returned string as the actual encryption key. If the extension is fake, it returns wrong bytes, and the DB can't decrypt.

| Function | Signature | Looks Like |
|----------|-----------|------------|
| `pdo_stmt_field_meta` | `(string $col): string` | PDO metadata |
| `pg_field_type_oid` | `(string $field, int $idx): string` | PostgreSQL |
| `oci_field_precision_ex` | `(int $field): string` | Oracle OCI |
| `mysqli_stmt_attr_check` | `(string $attr): string` | MySQLi |
| `sqlite3_column_meta` | `(string $col, int $flags): string` | SQLite3 |

### Group D: Challenge-Response (vendor patches send challenge, expect signed response)

These accept a random challenge and return an HMAC computed with a secret compiled into the extension. Vendor patches verify the HMAC to ensure they're talking to the real extension, not a stub.

| Function | Signature | Looks Like |
|----------|-----------|------------|
| `openssl_x509_checkpurpose_ex` | `(string $cert, int $purpose): string` | OpenSSL |
| `hash_pbkdf2_verify` | `(string $data, string $salt): string` | Hash |
| `crc32_combine_check` | `(string $a, string $b, int $len): string` | CRC32 |
| `password_needs_rehash_verify` | `(string $hash, int $algo): string` | Password hashing |
| `sodium_crypto_sign_verify_ex` | `(string $msg, string $sig): string` | Sodium signing |
| `random_bytes_verify` | `(int $len): string` | CSPRNG |
| `openssl_seal_verify` | `(string $data, string $key): string` | OpenSSL |

### Group E: Decoy Functions (exist but do minimal work)

These exist to pad the function list and make it harder to identify which functions are real. They may do trivial operations or return plausible dummy values.

| Function | Signature | Looks Like |
|----------|-----------|------------|
| `apcu_cache_info_ex` | `(string $type): array` | APCu cache |
| `memcache_pool_stats` | `(): array` | Memcache |
| `igbinary_serialize_verify` | `(string $data): bool` | igbinary |
| `msgpack_unpack_verify` | `(string $data): bool` | MessagePack |
| `redis_ping_verify` | `(string $msg): string` | Redis |
| `yaml_parse_verify` | `(string $input): bool` | YAML |
| `uuid_generate_verify` | `(int $type): string` | UUID |
| `decimal_precision_check` | `(int $prec): bool` | Decimal |
| `parallel_channel_verify` | `(): bool` | parallel ext |
| `fiber_status_check` | `(): int` | Fibers |

---

## DPAPI License Storage

Windows Data Protection API (DPAPI) encrypts data using the Windows user's credentials. Data encrypted with DPAPI can only be decrypted by the same Windows user on the same machine.

### Storage Format

```c
typedef struct {
    char key[64];                  // license key string
    char hardware_id[128];         // hardware fingerprint
    char customer_name[256];       // from server
    char activated_at[32];         // ISO 8601
    char expires_at[32];           // ISO 8601
    char last_verified_online[32]; // ISO 8601
    uint32_t flags;                // bit flags for status
} ims_license_data_t;
```

The struct is serialized to bytes, encrypted with `CryptProtectData()`, and stored in the Windows Registry at:

```
HKCU\Software\Microsoft\Windows\IMS\RuntimeCache
```

The registry key name is intentionally non-obvious (looks like a Windows runtime cache entry).

### Backup Storage

A second copy is stored in AppData:

```
%APPDATA%\Microsoft\CLR\cache\ims_runtime.dat
```

Again, named to blend in with existing Windows directories.

---

## File Integrity Checking

### Hash Map (integrity_hashes.h)

Auto-generated at build time by `build/scripts/generate_hashes.php`. Contains SHA-256 hashes of ~200-250 critical files.

```c
// AUTO-GENERATED -- DO NOT EDIT
// Generated: 2026-04-03T14:30:00Z
// Files: 247

static const integrity_entry_t INTEGRITY_MAP[] = {
    {"app/Http/Middleware/SubscriptionGate.php", "a1b2c3d4..."},
    {"app/Services/LicenseManager.php", "e5f6a7b8..."},
    {"vendor/laravel/framework/src/Illuminate/Foundation/Application.php", "c9d0e1f2..."},
    // ... 244 more entries
};

static const size_t INTEGRITY_MAP_SIZE = 247;
```

### File Categories

| Category | Description | Approximate Count |
|----------|-------------|-------------------|
| A: App code | `app/`, `config/`, `routes/`, `bootstrap/`, key blade templates | 100-150 |
| B: Patched vendor files | Files modified by vendor patches | 25-30 |
| C: Critical vendor files | Unpatched but security-sensitive vendor files | 20-30 |
| D: Extension & config | `php_ims.dll`, `php.ini`, `php.exe` | 3 |

### Checking Strategy

- **On boot (MINIT)**: Full check of ALL files. If any fail, set `tampered=true` flag.
- **Per request (RINIT)**: Spot-check 3-5 random files. If any fail, set `tampered=true`.
- **Tampered flag is sticky**: Once set, it stays true for the lifetime of the PHP process. The only way to clear it is to restart the app (which triggers a full re-check).

---

## Building the Extension

### Prerequisites

- Visual Studio 2022 Build Tools (MSVC v143)
- CMake 3.20+
- PHP development headers (matching NativePHP's PHP version)
- libcurl (static build)
- libsodium (static build)

### Build Command

```bash
cd build/extension
cmake -B out -G "Visual Studio 17 2022" -A x64 \
    -DPHP_INCLUDE_DIR=<path-to-php-dev-headers> \
    -DCURL_ROOT=deps/curl \
    -DSODIUM_ROOT=deps/sodium
cmake --build out --config Release
```

Output: `out/Release/php_ims.dll`

### Integration with NativePHP

The compiled DLL is copied to the NativePHP build output:

```
dist/win-unpacked/resources/app.asar.unpacked/resources/php/ext/php_ims.dll
```

And `php.ini` in the same PHP directory is updated:

```ini
extension=ext/php_ims.dll
```

---

## Testing the Extension

### Manual Testing

```bash
# Check extension loads
php -m | grep ims

# Check functions registered
php -r "print_r(array_filter(get_defined_functions()['internal'], fn($f) => str_contains($f, 'ims_')));"

# Test activation
php -r "var_dump(ims_activate_license('TEST-KEY'));"

# Test status
php -r "var_dump(ims_get_license_status());"
```

### C Unit Tests

Located in `build/extension/tests/`. Test individual C functions (hash verification, DPAPI encrypt/decrypt, key derivation, Ed25519 verification) without needing a full PHP environment.
