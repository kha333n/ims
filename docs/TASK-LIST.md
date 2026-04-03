# Development Task List

## Phase 1: Foundation & App Flow Changes

Prepare the Laravel app for the new license-first flow and extension integration.

### Setup
- [ ] Create `build/` directory structure (extension, patches, scripts, keys)
- [ ] Create `docs/` with all documentation files
- [ ] Lock all patchable packages to exact versions in `composer.json`
- [ ] Commit `composer.lock` with exact versions
- [ ] Add `build/extension/deps/`, `build/keys/server_private.pem`, `build/extension/src/integrity_hashes.h` to `.gitignore`

### App Flow (License-First)
- [ ] Change session driver from `database` to `file` in `config/session.php`
- [ ] Create `resources/views/layouts/minimal.blade.php` (logo + single content area, no nav/sidebar, no DB queries)
- [ ] Create `app/Livewire/License/LicenseActivation.php` component (no DB dependency)
- [ ] Create `resources/views/livewire/license/license-activation.blade.php`
- [ ] Create `app/Livewire/License/LicenseExpired.php` component (revalidate / new key with warning)
- [ ] Create `resources/views/livewire/license/license-expired.blade.php`
- [ ] Implement app state detection logic (`getAppState()` -- no DB needed for license check)
- [ ] Create middleware or ServiceProvider that routes to correct screen based on app state
- [ ] Update `routes/web.php` for license routes (accessible without DB)
- [ ] Test: fresh install shows license screen, not setup
- [ ] Test: after activation, shows setup wizard
- [ ] Test: after setup + login, shows dashboard
- [ ] Test: expired state shows reactivation screen

### Remove License from Database
- [ ] Remove license storage from `Setting` model / settings table
- [ ] Remove `LicenseManager` DB read/write methods (keep as visible decoy, reads from extension)
- [ ] Update `LicenseManager` to call `ims_*` extension functions (with fallback for dev mode)
- [ ] Ensure license activation/validation works without DB existing

---

## Phase 2: C Extension Core

Build the extension skeleton and core license functionality.

### Project Setup
- [ ] Create `build/extension/CMakeLists.txt` with PHP extension build config
- [ ] Create `build/extension/src/php_ims.h` (module definition, globals)
- [ ] Create `build/extension/src/php_ims.c` (MINIT, MSHUTDOWN, RINIT, RSHUTDOWN, module entry)
- [ ] Download and place PHP dev headers in `build/extension/deps/`
- [ ] Test: extension compiles and loads in PHP (`php -m | grep ims`)

### Hardware Fingerprint
- [ ] Create `build/extension/src/hardware.h` and `hardware.c`
- [ ] Implement: read CPU ID (CPUID instruction or WMI)
- [ ] Implement: read disk serial number
- [ ] Implement: read primary MAC address
- [ ] Implement: read Windows Machine GUID from registry
- [ ] Implement: combine into single hardware_id hash
- [ ] Test: hardware_id is stable across reboots, unique per machine

### DPAPI License Storage
- [ ] Create `build/extension/src/license.h` and `license.c`
- [ ] Implement: `CryptProtectData()` / `CryptUnprotectData()` wrapper
- [ ] Implement: store license struct in registry (`HKCU\Software\Microsoft\Windows\IMS\RuntimeCache`)
- [ ] Implement: backup store in AppData (`%APPDATA%\Microsoft\CLR\cache\ims_runtime.dat`)
- [ ] Implement: read license (try registry first, then AppData)
- [ ] Implement: write license (both locations)
- [ ] Implement: clear license (both locations)
- [ ] Test: store and retrieve license data, survives app restart

### Crypto
- [ ] Create `build/extension/src/crypto.h` and `crypto.c`
- [ ] Integrate libsodium (static link)
- [ ] Implement: Ed25519 signature verification
- [ ] Implement: PBKDF2-SHA256 key derivation (for DB key)
- [ ] Implement: SHA-256 file hashing
- [ ] Implement: sealed box encryption (for sending DB key to server)
- [ ] Test: verify known Ed25519 signatures, derive known keys

### Network (License Server Communication)
- [ ] Create `build/extension/src/network.h` and `network.c`
- [ ] Integrate libcurl (static link)
- [ ] Hardcode license server URL as C constant
- [ ] Implement: POST request with JSON body
- [ ] Implement: parse JSON response
- [ ] Implement: nonce generation and verification
- [ ] Implement: timestamp freshness check (within 300 seconds)
- [ ] Implement: Ed25519 signature verification on responses
- [ ] Test: successful and failed server communication

### Public API Functions (Group F)
- [ ] Implement `ims_activate_license(string $key): array`
- [ ] Implement `ims_deactivate_license(): array`
- [ ] Implement `ims_get_license_status(): array`
- [ ] Implement `ims_verify_online(): bool`
- [ ] Implement `ims_update_license(string $key): array`
- [ ] Implement `ims_remove_license(): array`
- [ ] Test: full activation -> status -> verify -> deactivate cycle

---

## Phase 3: Extension Security Functions

### File Integrity
- [ ] Create `build/extension/src/integrity.h` and `integrity.c`
- [ ] Implement: read integrity_hashes.h at compile time (static array)
- [ ] Implement: full file check (all files, used in MINIT)
- [ ] Implement: spot check (3-5 random files, used in RINIT)
- [ ] Implement: tampered flag (sticky, clears only on restart)
- [ ] Wire into MINIT: full check on boot
- [ ] Wire into RINIT: spot check per request, abort if tampered
- [ ] Test: clean files pass, modified file triggers tampered flag

### DB Key Derivation
- [ ] Implement: derive DB key from license_key + hardware_id + app_secret
- [ ] Implement: hold key in module globals (zeroed on shutdown)
- [ ] Implement: clear key when license invalid or tampered
- [ ] Test: same inputs produce same key, different inputs produce different key

### Disguised Functions (Groups A-E)
- [ ] Create `build/extension/src/functions.h` and `functions.c`
- [ ] Implement Group A: 14 license check functions (return encoded status)
- [ ] Implement Group B: 11 integrity check functions (verify files, return bool)
- [ ] Implement Group C: 5 DB key retrieval functions (return key material)
- [ ] Implement Group D: 7 challenge-response functions (HMAC with compiled secret)
- [ ] Implement Group E: 10 decoy functions (return plausible dummy values)
- [ ] Register all 55+ functions in module entry
- [ ] Test: each function returns expected values when licensed/unlicensed

### RINIT Integration
- [ ] Wire RINIT to: check tampered flag -> abort if true
- [ ] Wire RINIT to: check license validity -> clear DB key if false
- [ ] Wire RINIT to: check online verification deadline (7 days) -> block if stale
- [ ] Wire RINIT to: spot-check random files
- [ ] Test: RINIT blocks requests when tampered / unlicensed / stale

---

## Phase 4: Database Encryption

### SQLCipher Build
- [ ] Download SQLCipher amalgamation source
- [ ] Download OpenSSL static libraries (Windows x64)
- [ ] Create `build/scripts/compile_sqlcipher.bat`
- [ ] Compile custom `pdo_sqlite.dll` with SQLCipher support
- [ ] Test: custom pdo_sqlite.dll loads in PHP
- [ ] Test: `PRAGMA key` works, creates encrypted DB, rejects wrong key

### Laravel Integration
- [ ] Update `config/database.php`: single `sqlite` connection, no key in config
- [ ] Create vendor patch for `SQLiteConnector.php` that gets key from extension
- [ ] Test: app creates encrypted DB on first activation
- [ ] Test: app opens encrypted DB on subsequent launches
- [ ] Test: wrong key / no key -> "file is not a database" error
- [ ] Test: removing extension -> undefined function -> crash

### DB Creation Flow
- [ ] Implement: detect "no DB file" state after license activation
- [ ] Implement: create encrypted DB, run migrations
- [ ] Implement: transition to setup wizard after DB creation
- [ ] Test: full flow: license -> DB created -> setup -> login -> dashboard

---

## Phase 5: Vendor Patches

### Patch Framework
- [ ] Create `build/patches/patch_manifest.php` (list of all patch files)
- [ ] Create `build/scripts/harden.php` (reads patches, applies to dist/)
- [ ] Create `build/patches/verify_patches.php` (confirms all patches present)
- [ ] Test: harden.php modifies files correctly, verify confirms

### Layer 1: Bootstrap (4 patches)
- [ ] `laravel_application.patch.php` -- Application::boot()
- [ ] `laravel_provider_repository.patch.php` -- ProviderRepository::load()
- [ ] `composer_autoload_real.patch.php` -- autoload_real::getLoader()
- [ ] `composer_classloader.patch.php` -- ClassLoader::loadClass()

### Layer 2: HTTP Pipeline (5 patches)
- [ ] `symfony_httpkernel.patch.php` -- HttpKernel::handle()
- [ ] `symfony_response.patch.php` -- Response::send()
- [ ] `symfony_router.patch.php` -- Router::match()
- [ ] `laravel_router.patch.php` -- Router::dispatch()
- [ ] `laravel_pipeline.patch.php` -- Pipeline::then()

### Layer 3: Database (4 patches)
- [ ] `laravel_connection.patch.php` -- Connection::select()
- [ ] `laravel_sqlite_connector.patch.php` -- SQLiteConnector::connect() (DB KEY DELIVERY)
- [ ] `laravel_model.patch.php` -- Model::newQuery()
- [ ] `laravel_query_builder.patch.php` -- Builder::get()

### Layer 4: Auth & Session (3 patches)
- [ ] `laravel_session_guard.patch.php` -- SessionGuard::attempt()
- [ ] `laravel_session_store.patch.php` -- Store::start()
- [ ] `laravel_cookie_jar.patch.php` -- CookieJar::queue()

### Layer 5: View & Livewire (4 patches)
- [ ] `laravel_view_factory.patch.php` -- Factory::make()
- [ ] `laravel_blade_compiler.patch.php` -- BladeCompiler::compile()
- [ ] `livewire_handle_requests.patch.php` -- HandleRequests::handleUpdate()
- [ ] `livewire_handle_components.patch.php` -- HandleComponents::mount()

### Layer 6: Encryption & Hashing (2 patches)
- [ ] `laravel_encrypter.patch.php` -- Encrypter::encrypt()
- [ ] `laravel_bcrypt_hasher.patch.php` -- BcryptHasher::make()

### Layer 7: Deep Internals (5 patches)
- [ ] `carbon_carbon.patch.php` -- Carbon::now()
- [ ] `symfony_console.patch.php` -- Application::doRun()
- [ ] `psr_abstract_logger.patch.php` -- AbstractLogger::log()
- [ ] `monolog_logger.patch.php` -- Logger::addRecord()
- [ ] `symfony_error_handler.patch.php` -- ErrorHandler::handleException()

### Testing
- [ ] Test: remove extension from php.ini -> app crashes at Layer 1
- [ ] Test: stub extension (always true) -> DB key wrong -> data inaccessible
- [ ] Test: revert one vendor patch -> extension integrity check catches it
- [ ] Test: all patches present + real extension -> app works normally

---

## Phase 6: Build Pipeline

### Scripts
- [ ] Create `build/scripts/generate_server_keys.php` (one-time Ed25519 key pair)
- [ ] Create `build/scripts/generate_hashes.php` (scan files -> integrity_hashes.h)
- [ ] Create `build/scripts/compile_extension.bat` (CMake + MSVC build)
- [ ] Create `build/scripts/compile_sqlcipher.bat` (custom pdo_sqlite.dll)
- [ ] Create `build/scripts/harden.php` (apply vendor patches)
- [ ] Create `build/scripts/build.sh` (master orchestration)
- [ ] Create `build/scripts/verify_build.php` (post-build validation)

### Integration
- [ ] Wire into NativePHP post-build hook OR standalone build.sh
- [ ] Test: full build from clean source to installer
- [ ] Test: install on clean Windows machine
- [ ] Test: app boots, extension loads, functions work
- [ ] Test: DB encryption works end-to-end
- [ ] Test: integrity check passes on clean install
- [ ] Test: modify a file in installed app -> integrity fails

---

## Phase 7: App-Level Enforcement (Hidden PHP Layer)

### Hidden Enforcement Points
- [ ] Create `AnalyticsServiceProvider` (disguised name) -- calls extension in boot()
- [ ] Add license check in base Livewire component `mount()` method
- [ ] Create View Composer on `layouts.app` -- calls extension
- [ ] Add Eloquent Global Scope on `Customer` model -- blocks queries if invalid
- [ ] Each point calls a DIFFERENT disguised function

### Visible Layer (Decoy)
- [ ] Keep `SubscriptionGate` middleware (with working license checks)
- [ ] Keep `LicenseManager` service (calls extension internally)
- [ ] Keep `IntegrityChecker` service (PHP-level, circular checks)
- [ ] These are the "obvious" layer -- work correctly but are not the only enforcement

### Daily Verification
- [ ] Add `ims_verify_online()` call on every app boot (ServiceProvider, non-blocking)
- [ ] Add Laravel scheduled task: daily online check
- [ ] Implement offline warning banner (shows after 4+ days without verification)
- [ ] Test: offline for 6 days -> warning banner
- [ ] Test: offline for 7+ days -> hard block from extension
- [ ] Test: reconnect -> verify -> instant resume

---

## Phase 8: Backup & Restore

### App-Level
- [ ] Create `app/Livewire/Settings/BackupRestore.php` component
- [ ] Implement "Backup Now" button -- uploads encrypted DB to server
- [ ] Implement "Restore from Backup" -- lists backups, downloads, verifies, swaps
- [ ] Implement rollback safety (keep old DB, rollback on failure)
- [ ] Implement backup age warning banner (> 7 days, > 14 days)
- [ ] Test: backup -> restore on same device
- [ ] Test: restore failure -> rollback preserves original data

### Extension-Level (for device transfer)
- [ ] Implement DB key escrow: encrypt DB key with server's public key before sending
- [ ] Add backup metadata to activation/deactivation flow
- [ ] Test: key escrow encryption/decryption round-trip

### Server-Side (separate project)
- [ ] Create license server API: `/api/v1/activate`
- [ ] Create license server API: `/api/v1/deactivate`
- [ ] Create license server API: `/api/v1/validate`
- [ ] Create license server API: `/api/v1/backup/init`
- [ ] Create license server API: `/api/v1/backup/confirm`
- [ ] Create license server API: `/api/v1/backup/list`
- [ ] Create license server API: `/api/v1/backup/restore` (with re-encryption for device transfer)
- [ ] Create admin panel: manage licenses, revoke hardware, view backups
- [ ] Implement server-side PBKDF2 key derivation (same as extension)
- [ ] Implement server-side SQLCipher re-encryption
- [ ] Test: full device transfer cycle (old -> server -> new)

---

## Phase 9: Testing & Hardening

### Functional Tests
- [ ] Test: fresh install -> license -> setup -> login -> use
- [ ] Test: license expiry -> full stop -> revalidate -> resume
- [ ] Test: key change -> warning -> fresh DB
- [ ] Test: offline 7 days -> block -> reconnect -> resume
- [ ] Test: backup -> restore same device
- [ ] Test: backup -> transfer to new device

### Security Tests
- [ ] Test: edit SubscriptionGate.php -> extension catches, app blocks
- [ ] Test: remove extension from php.ini -> vendor patches crash app
- [ ] Test: create stub extension (all true) -> wrong DB key -> data inaccessible
- [ ] Test: revert vendor patch -> integrity hash mismatch -> app blocks
- [ ] Test: replace php.exe -> extension won't load -> crash
- [ ] Test: fake license server -> Ed25519 verification fails
- [ ] Test: copy DB to another machine -> different hardware_id -> wrong key
- [ ] Test: modify integrity_hashes in DLL -> can't (it's compiled binary)

### Edge Cases
- [ ] Test: DB file deleted -> app detects, offers re-setup
- [ ] Test: DPAPI storage corrupted -> extension reads from backup location
- [ ] Test: server unreachable during activation -> clear error message
- [ ] Test: multiple rapid requests -> RINIT doesn't slow down
- [ ] Test: app update with new extension -> hashes match new files
