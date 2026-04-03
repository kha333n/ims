# Build Pipeline

## Overview

The build pipeline transforms the source repository into a hardened, distributable Windows installer. It orchestrates NativePHP's Electron build, vendor patching, hash generation, extension compilation, and final packaging.

---

## Prerequisites

### Development Machine

- **Windows 10/11** (build target is Windows only)
- **PHP 8.4** (matching NativePHP's bundled version)
- **Composer 2.x**
- **Node.js 20+** and npm
- **Visual Studio 2022 Build Tools** (MSVC v143 toolchain)
  - Workload: "Desktop development with C++"
  - Individual components: CMake tools, Windows SDK
- **CMake 3.20+**
- **Git**

### Dependencies (downloaded by setup script)

- **PHP development headers** (php-dev package matching NativePHP's PHP version)
- **libcurl** (static build for Windows x64)
- **libsodium** (static build for Windows x64)
- **SQLCipher amalgamation** (sqlite3.c with encryption support)
- **OpenSSL** (required by SQLCipher)

### One-Time Setup

```bash
# 1. Install Visual Studio Build Tools
# Download from: https://visualstudio.microsoft.com/visual-cpp-build-tools/

# 2. Download C dependencies into build/extension/deps/
cd build/extension/deps
# curl, sodium, sqlite3/sqlcipher -- see setup script

# 3. Generate Ed25519 key pair for license server
php build/scripts/generate_server_keys.php
# Outputs:
#   build/keys/server_private.pem  (KEEP SECRET, deploy to license server)
#   build/keys/server_public.pem   (compiled into extension)
#   build/extension/src/server_pubkey.h  (auto-generated)
```

---

## Build Process

### Master Build Script

```bash
# build/scripts/build.sh
# Usage: bash build/scripts/build.sh [version]
# Example: bash build/scripts/build.sh 1.2.0
```

### Step-by-Step

```
STEP 1: Prepare Laravel App
  $ composer install --no-dev --optimize-autoloader
  $ npm ci && npm run build
  $ php artisan config:clear
  $ php artisan route:clear
  $ php artisan view:clear

STEP 2: NativePHP Build
  $ php artisan native:build win
  Output: dist/win-unpacked/
  
  At this point:
  - Electron runtime is packaged
  - PHP binary is in dist/.../resources/php/
  - Laravel app is in dist/.../resources/app/
  - All PHP files are plain text in app.asar.unpacked

STEP 3: Apply Vendor Patches
  $ php build/scripts/harden.php
  
  - Reads patch definitions from build/patches/
  - Applies each patch to the corresponding vendor file in dist/
  - Verifies each patch applied correctly
  - Logs all patches applied
  
  After this step: 25+ vendor files are modified with disguised function calls

STEP 4: Generate File Hashes
  $ php build/scripts/generate_hashes.php
  
  - Scans all critical files in dist/ (Categories A, B, C, D)
  - Categories B files are already patched (hashes capture patched state)
  - Generates build/extension/src/integrity_hashes.h
  - Reports total files hashed and any missing files

STEP 5: Compile Extension
  $ build/scripts/compile_extension.bat
  
  - Uses Visual Studio Build Tools (MSVC)
  - Compiles all .c files in build/extension/src/
  - Links libcurl, libsodium statically
  - Output: build/extension/out/Release/php_ims.dll
  
  The DLL now contains:
  - All 55+ disguised functions
  - SHA-256 hashes of 200+ files (from integrity_hashes.h)
  - Ed25519 public key (from server_pubkey.h)
  - License server URL (hardcoded)
  - App secret (for key derivation)

STEP 6: Compile Custom pdo_sqlite.dll (SQLCipher)
  $ build/scripts/compile_sqlcipher.bat
  
  - Compiles SQLCipher amalgamation with OpenSSL
  - Builds pdo_sqlite.dll linked against SQLCipher
  - Output: custom pdo_sqlite.dll with encryption support

STEP 7: Install Extension into Build
  - Copy php_ims.dll to dist/.../resources/php/ext/
  - Copy custom pdo_sqlite.dll to dist/.../resources/php/ext/ (replaces default)
  - Update php.ini in dist/.../resources/php/:
    extension=ext/php_ims.dll
    ; pdo_sqlite already loaded by default, our DLL replaces it

STEP 8: Final Hash Update
  $ php build/scripts/generate_hashes.php --update-dll-hash
  
  - Now that php_ims.dll exists, compute its hash
  - Update integrity_hashes.h with the DLL's own hash
  - Recompile extension one more time (it now knows its own hash)
  
  NOTE: This creates a chicken-and-egg problem. Solution:
  - First compile: DLL hash entry is "0000..." (placeholder)
  - Generate hash of first-compile DLL
  - Second compile: DLL hash entry is the real hash
  - The second DLL has a DIFFERENT hash (because it contains different data)
  - So we skip self-hash verification in the extension
  - Extension verifies all OTHER files, not itself
  - php.ini hash covers the extension loading config

STEP 9: Package Installer
  - electron-builder packages dist/ into NSIS installer
  - Output: dist/Installment-Management-System-Setup-X.Y.Z.exe
  - Sign the installer with code signing certificate (if available)

STEP 10: Verify Build
  $ php build/scripts/verify_build.php
  
  - Installs to a temp directory
  - Checks: extension loads, all functions exist
  - Checks: vendor patches present
  - Checks: DB encryption works
  - Checks: integrity verification passes
  - Reports: PASS/FAIL for each check
```

---

## Build Script Details

### build/scripts/harden.php

```
Purpose: Apply vendor patches to dist/ build output
Input:   build/patches/*.patch.php + dist/ directory
Output:  Modified vendor files in dist/

Process:
1. Load patch manifest (list of all patch files)
2. For each patch:
   a. Locate target file in dist/
   b. Read file content
   c. Find insertion anchor (method signature)
   d. Insert patch code at specified position
   e. Verify insertion succeeded
3. Run verify_patches.php to confirm all patches present
4. Log summary: X patches applied, 0 failures
```

### build/scripts/generate_hashes.php

```
Purpose: Scan critical files and generate C header with SHA-256 hashes
Input:   dist/ directory + file category definitions
Output:  build/extension/src/integrity_hashes.h

Process:
1. Define file categories (A: app, B: patched vendor, C: critical vendor, D: runtime)
2. Enumerate all files in each category
3. Compute SHA-256 of each file
4. Generate C header with static array of {path, hash} structs
5. Write to integrity_hashes.h
6. Report: X files hashed, Y missing (should be 0)
```

### build/scripts/compile_extension.bat

```
Purpose: Compile php_ims.dll from C source
Input:   build/extension/src/*.c + deps/
Output:  build/extension/out/Release/php_ims.dll

Process:
1. Set up Visual Studio environment (vcvarsall.bat x64)
2. Run CMake configure
3. Run CMake build (Release mode)
4. Verify output DLL exists and is valid PE binary
```

---

## Directory Structure After Build

```
dist/win-unpacked/
+-- resources/
|   +-- app.asar                    (Electron JS, packed)
|   +-- app.asar.unpacked/
|   |   +-- resources/
|   |       +-- app/                (Laravel application)
|   |       |   +-- app/            (PHP app code -- hashed)
|   |       |   +-- vendor/         (patched + hashed)
|   |       |   +-- config/         (hashed)
|   |       |   +-- routes/         (hashed)
|   |       |   +-- bootstrap/      (hashed)
|   |       |   +-- ...
|   |       +-- php/
|   |       |   +-- php.exe         (PHP binary -- hashed)
|   |       |   +-- php.ini         (loads extension -- hashed)
|   |       |   +-- ext/
|   |       |       +-- php_ims.dll (our extension)
|   |       |       +-- pdo_sqlite.dll (SQLCipher-enabled)
|   |       |       +-- ... (other PHP extensions)
|   |       +-- icon.png
|   |       +-- cacert.pem
|   +-- elevate.exe
+-- Installment Management System.exe
+-- ... (Electron/Chromium binaries)
```

---

## Versioning

### Version String

Format: `MAJOR.MINOR.PATCH` (e.g., `1.2.0`)

Set in:
- `config/nativephp.php` -> `version`
- `build/extension/src/php_ims.h` -> `PHP_IMS_VERSION`
- `package.json` -> `version`

All three must match. The build script verifies this.

### Release Checklist

```
[ ] All tests pass (php artisan test)
[ ] Version updated in all 3 locations
[ ] composer.json has exact version locks
[ ] Build completes without errors
[ ] verify_build.php passes all checks
[ ] Installer tested on clean Windows machine
[ ] License activation tested end-to-end
[ ] Extension loads and all functions work
[ ] DB encryption creates and reads correctly
[ ] Integrity check passes on clean install
[ ] Integrity check fails when file modified
```

---

## Troubleshooting

### Extension won't compile

- Ensure Visual Studio Build Tools are installed with C++ workload
- Verify PHP dev headers match NativePHP's PHP version
- Check CMake can find deps (curl, sodium)

### Patches fail to apply

- Vendor package version may have changed
- Run `composer install` (not `update`) to restore locked versions
- Check if the anchor text (method signature) changed in the new version

### Hash mismatch after build

- NativePHP may modify files during its build process
- Run hash generation AFTER NativePHP build, not before
- Check for line ending issues (CRLF vs LF)

### Self-hash chicken-and-egg

- Extension does NOT verify its own hash
- It verifies all other files including php.ini (which references the extension)
- If someone replaces the DLL, it would need to contain the correct hashes of all other files -- practically impossible without our build pipeline
