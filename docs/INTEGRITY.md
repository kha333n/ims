# File Integrity System

## Overview

The integrity system ensures that critical files have not been modified after the application was built. It operates at three levels:

1. **Extension level (MINIT/RINIT)**: SHA-256 hashes compiled into `php_ims.dll`
2. **Circular PHP checks**: PHP files verify each other's hashes
3. **Vendor patches**: Patched files are included in the integrity hash map

---

## Critical File Categories

### Category A: App Code (~100-150 files)

Files written by us that control application behavior. These never change at runtime.

```
app/Http/Middleware/*.php          - All middleware including SubscriptionGate
app/Services/*.php                 - Business logic services
app/Models/*.php                   - Eloquent models
app/Livewire/**/*.php              - All Livewire components
app/Providers/*.php                - Service providers (including hidden checks)
bootstrap/app.php                  - App bootstrap
bootstrap/providers.php            - Provider registration
config/*.php                       - All config files
routes/web.php                     - Web routes
routes/console.php                 - Console routes
resources/views/layouts/*.blade.php - Main layout templates
```

### Category B: Patched Vendor Files (~25-30 files)

Vendor files modified by build-time patches. These are the most critical to protect because they contain the disguised extension function calls.

```
vendor/laravel/framework/src/Illuminate/Foundation/Application.php
vendor/laravel/framework/src/Illuminate/Routing/Router.php
vendor/laravel/framework/src/Illuminate/Database/Connection.php
vendor/laravel/framework/src/Illuminate/Database/Connectors/SQLiteConnector.php
vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php
vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php
vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
vendor/laravel/framework/src/Illuminate/View/Factory.php
vendor/laravel/framework/src/Illuminate/View/Compilers/BladeCompiler.php
vendor/laravel/framework/src/Illuminate/Auth/SessionGuard.php
vendor/laravel/framework/src/Illuminate/Session/Store.php
vendor/laravel/framework/src/Illuminate/Cookie/CookieJar.php
vendor/laravel/framework/src/Illuminate/Encryption/Encrypter.php
vendor/laravel/framework/src/Illuminate/Hashing/BcryptHasher.php
vendor/laravel/framework/src/Illuminate/Foundation/ProviderRepository.php
vendor/symfony/http-kernel/HttpKernel.php
vendor/symfony/http-foundation/Response.php
vendor/symfony/routing/Router.php
vendor/symfony/console/Application.php
vendor/symfony/error-handler/ErrorHandler.php
vendor/livewire/livewire/src/Mechanisms/HandleRequests/HandleRequests.php
vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php
vendor/nesbot/carbon/src/Carbon/Carbon.php
vendor/monolog/monolog/src/Monolog/Logger.php
vendor/psr/log/src/AbstractLogger.php
vendor/composer/autoload_real.php
vendor/composer/ClassLoader.php
```

### Category C: Critical Unpatched Vendor Files (~20-30 files)

Files we didn't patch but want to protect from modification (e.g., someone swapping classes).

```
vendor/autoload.php
vendor/composer/autoload_classmap.php
vendor/composer/autoload_files.php
vendor/composer/autoload_namespaces.php
vendor/composer/autoload_psr4.php
vendor/composer/autoload_static.php
vendor/laravel/framework/src/Illuminate/Auth/AuthManager.php
vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php
vendor/laravel/framework/src/Illuminate/Encryption/EncryptionServiceProvider.php
vendor/laravel/framework/src/Illuminate/Database/DatabaseServiceProvider.php
```

### Category D: Extension and Runtime (~3 files)

```
php/ext/php_ims.dll               - The extension itself
php/php.ini                        - PHP configuration (loads extension)
php/php.exe                        - PHP binary
```

### Files NEVER Hashed (change at runtime)

```
storage/**                         - Logs, cache, sessions, compiled views
bootstrap/cache/**                 - Compiled config, routes, services
.env                               - User-editable environment
database/*.sqlite                  - Database files (content changes)
public/build/**                    - Vite build artifacts
public/hot                         - Vite dev server flag
```

---

## Extension-Level Integrity (Primary)

### Hash Generation (Build Time)

`build/scripts/generate_hashes.php` scans all critical files and generates a C header:

```c
// build/extension/src/integrity_hashes.h
// AUTO-GENERATED at build time. DO NOT EDIT.

#ifndef INTEGRITY_HASHES_H
#define INTEGRITY_HASHES_H

typedef struct {
    const char* path;    // relative path from app root
    const char* hash;    // SHA-256 hex string (64 chars)
} integrity_entry_t;

static const integrity_entry_t INTEGRITY_MAP[] = {
    {"app/Http/Middleware/SubscriptionGate.php",
     "a1b2c3d4e5f6789012345678901234567890123456789012345678901234abcd"},
    {"vendor/laravel/framework/src/Illuminate/Foundation/Application.php",
     "b2c3d4e5f67890123456789012345678901234567890123456789012345678ef"},
    // ... ~250 entries
};

static const size_t INTEGRITY_MAP_SIZE = 
    sizeof(INTEGRITY_MAP) / sizeof(INTEGRITY_MAP[0]);

#endif
```

This header is compiled INTO the DLL. The hashes exist only as data in the binary -- not in any file that can be edited.

### Boot Check (MINIT)

On PHP startup, the extension checks ALL files:

```c
// In PHP_MINIT_FUNCTION(ims):
for (size_t i = 0; i < INTEGRITY_MAP_SIZE; i++) {
    char full_path[MAX_PATH];
    snprintf(full_path, sizeof(full_path), "%s/%s", 
             app_base_path, INTEGRITY_MAP[i].path);
    
    char computed_hash[65];
    sha256_file(full_path, computed_hash);
    
    if (strcmp(computed_hash, INTEGRITY_MAP[i].hash) != 0) {
        IMS_G(tampered) = 1;  // set global flag
        // Don't break -- check all files to avoid revealing which one
    }
}
```

### Request Spot-Check (RINIT)

Every request, check 3-5 random files:

```c
// In PHP_RINIT_FUNCTION(ims):
if (IMS_G(tampered)) {
    return FAILURE;  // PHP aborts the request
}

// Spot-check random files
srand(time(NULL) ^ getpid());
for (int i = 0; i < 5; i++) {
    size_t idx = rand() % INTEGRITY_MAP_SIZE;
    
    char full_path[MAX_PATH];
    snprintf(full_path, sizeof(full_path), "%s/%s",
             app_base_path, INTEGRITY_MAP[idx].path);
    
    char computed_hash[65];
    sha256_file(full_path, computed_hash);
    
    if (strcmp(computed_hash, INTEGRITY_MAP[idx].hash) != 0) {
        IMS_G(tampered) = 1;
        return FAILURE;
    }
}
```

### Tampered Flag Behavior

- Once `tampered = true`, it stays true for the lifetime of the PHP process
- Only way to clear: restart the app (which triggers a full MINIT re-check)
- If tampered: all disguised functions return invalid values
- If tampered: DB key is cleared from memory
- If tampered: RINIT returns FAILURE (PHP aborts every request)

---

## Circular PHP Checks (Secondary)

PHP files verify each other as a backup layer. These run within the PHP application, not the extension.

### How It Works

Each critical PHP file contains a hash of 1-2 other files:

```
SubscriptionGate.php  --checks-->  LicenseManager.php
LicenseManager.php    --checks-->  IntegrityChecker.php (the PHP one)
IntegrityChecker.php  --checks-->  AppServiceProvider.php
AppServiceProvider.php --checks--> SubscriptionGate.php (closes the loop)
```

### Implementation

```php
// In SubscriptionGate.php (hidden in constructor or handle method):
private const PEER_HASH = 'a1b2c3d4...'; // hash of LicenseManager.php
private const PEER_FILE = 'app/Services/LicenseManager.php';

// Checked occasionally (not every request -- performance):
if (mt_rand(1, 10) === 1) { // 10% of requests
    $hash = hash_file('sha256', base_path(self::PEER_FILE));
    if ($hash !== self::PEER_HASH) {
        abort(500); // generic error, no useful message
    }
}
```

### Limitations

- These are in PHP code, so they CAN be found and edited
- But the extension's integrity check will detect that edit
- They serve as a second layer -- if extension check is somehow bypassed

---

## Vendor Patch Integrity

All patched vendor files are included in Category B of the integrity hash map. This means:

1. Patches are applied at build time (to the `dist/` output)
2. Hash generator runs AFTER patches (captures patched state)
3. Extension verifies patched files match expected hashes
4. If someone reverts a vendor file to its original (unpatched) state:
   - Hash won't match (because extension expects the patched version)
   - Extension sets tampered flag
   - App blocks

This creates a tight coupling: the extension expects EXACTLY the files it was built with, patches included.

---

## What Happens When Integrity Fails

### At Boot (MINIT Failure)

```
Extension detects tampered files during MINIT
  -> Sets tampered = true
  -> Every RINIT returns FAILURE
  -> PHP aborts every request with a generic error
  -> App shows white screen / 500 error
  -> No useful error message (attacker doesn't know which file)
```

### During Request (RINIT Spot-Check)

```
Extension detects tampered file during random spot-check
  -> Sets tampered = true
  -> Returns FAILURE for current request
  -> All subsequent requests also fail
  -> App suddenly stops working (from attacker's perspective: randomly)
```

### Via Disguised Functions

```
Disguised function called by vendor patch
  -> Function checks tampered flag internally
  -> Returns invalid value (empty string, 0, false)
  -> Vendor patch uses invalid value
  -> Downstream failure (wrong DB key, broken pipeline, etc.)
  -> Error appears to come from a completely different place
```

---

## Updating Integrity After App Update

When releasing a new version:

1. Code changes are made in source repo
2. Build pipeline runs
3. Vendor patches applied to `dist/`
4. `generate_hashes.php` scans ALL critical files in `dist/`
5. Generates new `integrity_hashes.h` with updated hashes
6. Extension recompiled with new hashes
7. New version ships: new code + new extension (hashes match)

The extension and the app files are ALWAYS in sync because they're built together.
