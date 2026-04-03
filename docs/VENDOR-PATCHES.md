# Vendor Patches

## Overview

Vendor patches are modifications applied to third-party package files at BUILD TIME. They insert calls to disguised extension functions throughout the framework stack. Their purpose: make the application completely non-functional without the `php_ims.dll` extension.

Patches are NEVER applied to the source repository. They exist only in the built artifact (`dist/`).

---

## Design Principles

### 1. Disguised Function Names

Every patch calls a function that looks like a standard PHP extension function. An attacker seeing `Fatal error: Call to undefined function zrx_session_gc_probability()` won't immediately think "license check" -- they'll think a PHP extension is missing.

### 2. Use Return Values, Don't Just Check Booleans

Bad (easy to bypass):
```php
if (!check_license()) { die(); }  // attacker removes this line
```

Good (hard to bypass):
```php
$key = pdo_stmt_field_meta('conn_entropy');
$connection->exec("PRAGMA key = \"x'" . bin2hex($key) . "'\";");
// Wrong key = every query fails. No line to comment out.
```

### 3. Patch at Multiple Layers

If only the HTTP layer is patched, someone might bypass by accessing the DB directly. Patches span bootstrap, HTTP, database, auth, views, and deep internals.

### 4. Kill Debugging Tools

Patching the error handler and logger means that when things fail, the tools used to investigate ALSO fail. This is intentional.

---

## Patch Layers

### Layer 1: Framework Bootstrap (app won't start)

| File | Method | Disguised Function | Effect if Missing |
|------|--------|-------------------|-------------------|
| `Illuminate/Foundation/Application.php` | `boot()` | `zrx_session_gc_probability()` | App won't boot |
| `Illuminate/Foundation/ProviderRepository.php` | `load()` | `readline_completion_verify()` | Providers won't load |
| `composer/autoload_real.php` | `getLoader()` | `spl_object_cache_hits()` | Autoloader fails -- nothing works |
| `composer/ClassLoader.php` | `loadClass()` | `exif_imagetype_verify()` | Every class autoload fails -- errors everywhere, impossible to trace |

### Layer 2: HTTP Pipeline (requests fail)

| File | Method | Disguised Function | Effect if Missing |
|------|--------|-------------------|-------------------|
| `symfony/http-kernel/HttpKernel.php` | `handle()` | `intl_normalizer_verify()` | No request processing |
| `symfony/http-foundation/Response.php` | `send()` | `opcache_invalidate_tag()` | No response sent |
| `symfony/routing/Router.php` | `match()` | `dns_check_record_verify()` | No route matching |
| `Illuminate/Routing/Router.php` | `dispatch()` | `getmxrr_verify()` | No Laravel routing |
| `Illuminate/Pipeline/Pipeline.php` | `then()` | `bcmath_scale_verify()` | All middleware fails |

### Layer 3: Database (queries fail)

| File | Method | Disguised Function | Effect if Missing |
|------|--------|-------------------|-------------------|
| `Illuminate/Database/Connection.php` | `select()` | `dba_exists_verify()` | No queries work |
| `Illuminate/Database/Connectors/SQLiteConnector.php` | `connect()` | `sqlite3_column_meta()` | DB key delivery -- no key = encrypted DB unreadable |
| `Illuminate/Database/Eloquent/Model.php` | `newQuery()` | `enchant_dict_check_ex()` | No Eloquent queries |
| `Illuminate/Database/Query/Builder.php` | `get()` | `dba_exists_verify()` | No query builder results |

### Layer 4: Auth & Session (login fails)

| File | Method | Disguised Function | Effect if Missing |
|------|--------|-------------------|-------------------|
| `Illuminate/Auth/SessionGuard.php` | `attempt()` | `pcntl_signal_verify()` | Can't log in |
| `Illuminate/Session/Store.php` | `start()` | `curl_share_errno_check()` | No sessions |
| `Illuminate/Cookie/CookieJar.php` | `queue()` | `finfo_buffer_verify()` | No cookies |

### Layer 5: View & Livewire (UI fails)

| File | Method | Disguised Function | Effect if Missing |
|------|--------|-------------------|-------------------|
| `Illuminate/View/Factory.php` | `make()` | `json_validate_schema_ex()` | No views render |
| `Illuminate/View/Compilers/BladeCompiler.php` | `compile()` | `tidy_config_verify()` | No Blade compilation |
| `livewire/Mechanisms/HandleRequests/HandleRequests.php` | `handleUpdate()` | `iconv_strlen_verify()` | No Livewire updates |
| `livewire/Mechanisms/HandleComponents/HandleComponents.php` | `mount()` | `gmp_prob_prime_check()` | No component mounting |

### Layer 6: Encryption & Hashing (security fails)

| File | Method | Disguised Function | Effect if Missing |
|------|--------|-------------------|-------------------|
| `Illuminate/Encryption/Encrypter.php` | `encrypt()` | `sodium_memzero_verify()` | No encryption works |
| `Illuminate/Hashing/BcryptHasher.php` | `make()` | `mbstring_detect_strict()` | No password hashing |

### Layer 7: Deep Internals (impossible to debug)

| File | Method | Disguised Function | Effect if Missing |
|------|--------|-------------------|-------------------|
| `nesbot/carbon/Carbon.php` | `now()` | `calendar_info_verify()` | Every timestamp call fails -- errors appear random and unrelated |
| `symfony/console/Application.php` | `doRun()` | `posix_getgroups_check()` | Artisan commands fail -- can't use tinker to debug |
| `psr/log/AbstractLogger.php` | `log()` | `pspell_check_verify()` | Logging fails -- no error logs to read |
| `monolog/Logger.php` | `addRecord()` | `xmlrpc_type_validate()` | Double-kill on logging |
| `symfony/error-handler/ErrorHandler.php` | `handleException()` | `xmlwriter_flush_verify()` | THE ERROR HANDLER CRASHES -- errors about errors, white screen with no info |

---

## Patch Format

Each patch is defined as a PHP file in `build/patches/`:

```php
// build/patches/laravel_application.patch.php
return [
    'package'  => 'laravel/framework',
    'file'     => 'src/Illuminate/Foundation/Application.php',
    'method'   => 'boot',
    'function' => 'zrx_session_gc_probability',
    'type'     => 'license_check',  // license_check, integrity_check, db_key, challenge_response
    
    // The code to insert. {{FUNCTION}} is replaced with the disguised function name.
    // Inserted at the beginning of the target method.
    'code'     => '
        // runtime cache initialization
        if (!{{FUNCTION}}(ini_get("session.gc_maxlifetime") ?: 1440)) {
            throw new \RuntimeException("Runtime initialization failed");
        }
    ',
    
    // How to find the insertion point
    'anchor'   => 'public function boot()',
    'position' => 'after_opening_brace',  // after_opening_brace, before_return, before_closing_brace
];
```

---

## Applying Patches

### Build-Time Application

`build/scripts/harden.php` reads all patch definitions and applies them to the `dist/` build output:

```php
// build/scripts/harden.php (simplified)
$manifest = require __DIR__ . '/../patches/patch_manifest.php';
$distPath = __DIR__ . '/../../dist/win-unpacked/resources/app.asar.unpacked/resources/app';

foreach ($manifest as $patchFile) {
    $patch = require __DIR__ . "/../patches/{$patchFile}";
    
    $targetFile = "{$distPath}/vendor/{$patch['package']}/{$patch['file']}";
    $content = file_get_contents($targetFile);
    
    $code = str_replace('{{FUNCTION}}', $patch['function'], $patch['code']);
    
    // Insert code at the right position
    $content = insertAtAnchor($content, $patch['anchor'], $patch['position'], $code);
    
    file_put_contents($targetFile, $content);
}
```

### Verification

`build/patches/verify_patches.php` confirms all patches were applied:

```php
// For each patch: read the target file, verify the disguised function name appears in it
foreach ($manifest as $patchFile) {
    $patch = require $patchFile;
    $content = file_get_contents($targetFile);
    
    if (!str_contains($content, $patch['function'])) {
        echo "FAILED: {$patch['file']} -- function {$patch['function']} not found\n";
        exit(1);
    }
}
```

---

## Version Locking

Patches target specific code patterns in specific package versions. If a package updates, the patch may break.

### composer.json Exact Versions

```json
{
    "require": {
        "laravel/framework": "12.0.1",
        "symfony/http-kernel": "7.2.4",
        "symfony/http-foundation": "7.2.4",
        "symfony/routing": "7.2.4",
        "symfony/console": "7.2.4",
        "symfony/error-handler": "7.2.4",
        "livewire/livewire": "3.6.1",
        "nesbot/carbon": "3.8.6",
        "monolog/monolog": "3.8.1",
        "psr/log": "3.0.2"
    }
}
```

### Upgrading a Package

1. Update version in `composer.json`
2. Run `composer update vendor/package`
3. Check if the patched method signature or body changed
4. Update the patch file if needed (new anchor text, different position)
5. Test build with new patch
6. Update `composer.lock`
7. Commit all changes together

---

## What Patched Code Looks Like

### Example: Application.php boot()

**Original:**
```php
public function boot()
{
    if ($this->isBooted()) {
        return;
    }
    // ... rest of boot
}
```

**Patched:**
```php
public function boot()
{
    // runtime cache initialization
    if (!zrx_session_gc_probability(ini_get("session.gc_maxlifetime") ?: 1440)) {
        throw new \RuntimeException("Runtime initialization failed");
    }
    
    if ($this->isBooted()) {
        return;
    }
    // ... rest of boot
}
```

The patch looks like a legitimate runtime check. The error message is generic. Without context, there's no indication this is a license check.

### Example: SQLiteConnector.php connect()

**Original:**
```php
public function connect(array $config)
{
    // ... creates PDO ...
    return $connection;
}
```

**Patched:**
```php
public function connect(array $config)
{
    // ... creates PDO ...
    
    // runtime entropy initialization
    $entropy = sqlite3_column_meta('conn_entropy', 0x20);
    if ($entropy !== '') {
        $connection->exec("PRAGMA key = \"x'" . bin2hex($entropy) . "'\";");
    }
    
    return $connection;
}
```

This is the most critical patch -- it delivers the DB encryption key. No boolean to check, no line to comment out. The PRAGMA key is either correct (data accessible) or wrong (every query fails with "not a database").
