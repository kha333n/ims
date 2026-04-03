# System Architecture

## Overview

The Installment Management System (IMS) uses a multi-layered security architecture to enforce licensing on a desktop application built with Laravel + NativePHP. Because NativePHP unpacks all PHP files to `app.asar.unpacked/`, source code is exposed on disk. The security model assumes **all PHP code is readable and editable** by the end user and builds protection accordingly.

---

## Layered Security Model

```
+---------------------------------------------------------------+
|                     VISIBLE LAYER (Decoy)                     |
|  App-level license UI, SubscriptionGate middleware,           |
|  LicenseManager service -- works correctly, is the            |
|  "obvious" system attackers will focus on                     |
+---------------------------------------------------------------+
|                    HIDDEN PHP LAYER                            |
|  3-4 disguised checks in ServiceProviders,                    |
|  Livewire base class, View Composer, Eloquent scope           |
|  -- calls extension functions by disguised names              |
+---------------------------------------------------------------+
|                  VENDOR PATCH LAYER                            |
|  25+ vendor files call extension functions                    |
|  -- app won't boot without extension                          |
|  -- function names look like PHP internals                    |
|  -- patching error handler + logger = impossible to debug     |
+---------------------------------------------------------------+
|                php_ims.dll EXTENSION                           |
|  +-- License: store/validate/activate via DPAPI               |
|  +-- Integrity: file hash checks on RINIT                     |
|  +-- DB Key: returns decryption key only if valid             |
|  +-- Server: Ed25519-signed communication                     |
|  +-- 55+ disguised functions (same checks, different names)   |
+---------------------------------------------------------------+
|                 ENCRYPTED SQLite DB                            |
|  SQLCipher -- entire DB encrypted                             |
|  Key = f(license_key, hardware_id, app_secret)                |
|  No valid license = no data = app is useless                  |
+---------------------------------------------------------------+
```

---

## Component Map

| Component | Language | Location | Purpose |
|-----------|----------|----------|---------|
| `php_ims.dll` | C | `build/extension/` | Core license enforcement, integrity, DB key |
| Vendor patches | PHP (applied at build time) | `build/patches/` | Make app unbootable without extension |
| Build scripts | PHP + Bash | `build/scripts/` | Orchestrate hash generation, patching, compilation |
| Hidden PHP checks | PHP | `app/Providers/`, `app/Livewire/`, etc. | App-level enforcement backup |
| Visible license UI | PHP/Livewire | `app/Livewire/License/` | User-facing activation, the "obvious" layer |
| License server | PHP (separate project) | External server | Validates keys, stores backups, handles transfers |
| Encrypted DB | SQLite + SQLCipher | `storage/app/ims.sqlite` | All business data, encrypted at rest |

---

## App States

The application operates in one of four states. State is determined by the extension and does NOT depend on the database.

```
+---------------+          +----------------+          +-----------+
|  NO_LICENSE   | -------> |  NEEDS_SETUP   | -------> |   READY   |
|               |  activate|                |  setup   |           |
| - Fresh install          | - License valid           | - Full    |
| - Expired license        | - DB created              |   access  |
| - Key revoked            | - No admin user           |           |
|                          | - Runs migrations         |           |
| Shows: license           | Shows: setup              | Shows:    |
| activation screen        | wizard                    | dashboard |
+---------------+          +----------------+          +-----------+
       ^                                                     |
       |                                                     |
       |                   +-------------+                   |
       +------------------ |   EXPIRED   | <-----------------+
             revalidate    |             |    license expires
             or new key    | - Extension |    or 7 days offline
                           |   blocks    |
                           | - No DB key |
                           | Shows:      |
                           | reactivation|
                           | screen      |
                           +-------------+
```

### State Detection (No DB Required)

```php
function getAppState(): string
{
    // 1. Extension loaded?
    if (!function_exists('ims_get_license_status')) {
        return 'broken'; // vendor patches will crash anyway
    }

    // 2. License valid?
    $license = ims_get_license_status();
    if (!$license['valid']) {
        return 'no_license'; // State 1 or 4
    }

    // 3. DB exists?
    if (!file_exists(storage_path('app/ims.sqlite'))) {
        return 'needs_setup'; // State 2
    }

    // 4. Setup complete?
    try {
        $count = DB::select('SELECT COUNT(*) as c FROM users')[0]->c;
        return $count > 0 ? 'ready' : 'needs_setup';
    } catch (\Throwable) {
        return 'needs_setup';
    }
}
```

---

## Security Model: What Protects What

| Threat | Protection |
|--------|------------|
| User edits SubscriptionGate.php to skip license | Vendor patches + extension integrity check detect modification |
| User removes extension from php.ini | Vendor patches call extension functions -- app crashes with undefined function errors |
| User creates fake extension returning true | Challenge-response: vendor patches USE return values, not just check booleans; wrong values = DB key wrong = data inaccessible |
| User bypasses all PHP checks | DB is encrypted -- without valid license key from extension, queries return "file is not a database" |
| User copies DB to another machine | DB key is derived from license_key + hardware_id -- different hardware = different key = can't decrypt |
| User tries to read extension code | It's compiled C (machine code), requires reverse engineering with IDA Pro/Ghidra |
| User modifies vendor files to remove patches | Extension integrity check has hashes of patched vendor files -- modification detected |
| User replaces php.exe with stock PHP | Extension won't load (not in new php.ini), vendor patches crash |
| Attacker tries fake license server | Extension verifies server responses with Ed25519 -- can't forge signatures without private key |

---

## Attack Difficulty Assessment

| Attack | Skill Required | Time | Blocked By |
|--------|---------------|------|------------|
| Edit PHP middleware | Beginner | 1 min | Extension integrity + vendor patches |
| Remove extension from ini | Beginner | 1 min | Vendor patches (undefined function crash) |
| Find and fix all vendor patches | Intermediate | Hours | 25+ patches across 7 layers, disguised names |
| Create stub extension | Advanced | Hours | Challenge-response, DB key derivation |
| Reverse-engineer DLL | Expert | Days | Compiled C, obfuscated logic |
| Full bypass (DLL RE + all patches + DB) | Expert | Days-Weeks | Multiple independent layers |

For a local business installment management app, this level of protection is more than sufficient. The target user base does not include professional reverse engineers.

---

## Data Flow

### Normal Request Flow

```
1. PHP loads php_ims.dll (php.ini)
2. Extension MINIT: one-time initialization
3. Request arrives
4. Extension RINIT:
   a. Read license from DPAPI
   b. Check expiry (hard block if > 7 days since online verify)
   c. Spot-check 3-5 random file hashes
   d. Set internal valid/invalid flag
   e. If valid: derive DB key, hold in memory
5. Composer autoloader loads classes
   -> Vendor patch in ClassLoader calls disguised function
6. Laravel boots Application
   -> Vendor patch in Application::boot() calls disguised function
7. HTTP pipeline processes request
   -> Vendor patches in Router, Pipeline, HttpKernel
8. SQLite connection opens
   -> Vendor patch in SQLiteConnector gets DB key from extension
   -> PRAGMA key applied
9. Eloquent queries run normally (DB decrypted in memory)
10. Response sent
    -> Vendor patch in Response::send() calls disguised function
11. Extension RSHUTDOWN: clear sensitive data from memory
```

### License Activation Flow

```
1. User on license screen (no DB, minimal layout)
2. Enters license key
3. Livewire calls ims_activate_license(key)
4. Extension:
   a. Generates hardware_id
   b. HTTPS POST to license server with {key, hardware_id, nonce}
   c. Server validates key, returns signed response
   d. Extension verifies Ed25519 signature
   e. Stores license in DPAPI
   f. Derives DB key
5. App creates ims.sqlite with PRAGMA key
6. Runs migrations
7. Redirects to setup wizard
```
