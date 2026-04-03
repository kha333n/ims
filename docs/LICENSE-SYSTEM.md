# License System

## Overview

The license system is the gatekeeper for the entire application. It operates independently of the database -- license data lives in the `php_ims.dll` extension and Windows DPAPI storage only. Without a valid license, the app cannot function: the extension withholds the database encryption key, and vendor patches prevent the app from booting without the extension.

---

## License Lifecycle

```
  [Not Activated] --activate--> [Active] --expires--> [Expired]
                                   |                      |
                                   |   <--revalidate------+
                                   |                      |
                                   |   --new key--------> [Key Changed]
                                   |                      |
                                   |                      v
                                   |              [Warning: data loss]
                                   |                      |
                                   +--deactivate--> [Not Activated]
```

---

## License Storage

License data is stored ONLY in the extension layer. Never in the database, never in PHP config files.

### Primary: Windows DPAPI (Registry)

```
Location: HKCU\Software\Microsoft\Windows\IMS\RuntimeCache
Format:   CryptProtectData() encrypted blob
Contents: license key, hardware_id, expires_at, last_verified_online, flags
```

DPAPI encryption is tied to the Windows user account. Cannot be decrypted by a different user or on a different machine.

### Backup: AppData File

```
Location: %APPDATA%\Microsoft\CLR\cache\ims_runtime.dat
Format:   Same DPAPI-encrypted blob
Purpose:  Redundancy in case registry is cleared
```

### NOT Stored In

- Database (DB requires license to access -- circular dependency)
- .env file (user-editable)
- Laravel config (user-editable)
- PHP session (ephemeral)

---

## Server Communication Protocol

### Base URL

Hardcoded in the extension (compiled C code):

```c
#define LICENSE_SERVER_URL "https://license.yourdomain.com"
```

Cannot be changed by modifying PHP config or .env files.

### Authentication: Ed25519 Signatures

Every server response is signed with the server's Ed25519 private key. The extension has the public key compiled in and verifies every response.

```
Extension has: SERVER_PUBLIC_KEY (32 bytes, in server_pubkey.h)
Server has:    SERVER_PRIVATE_KEY (64 bytes, in server's environment)
```

### Request Format

All requests include:

```json
{
    "key": "IMS-PROD-A1B2-C3D4",
    "hardware_id": "hw_abc123...",
    "nonce": "random_32_byte_hex",
    "timestamp": 1743724800,
    "extension_version": "1.0.0"
}
```

### Response Format

All responses include:

```json
{
    "status": "valid",
    "data": {
        "expires_at": "2027-01-15",
        "customer_name": "Shop Name",
        "features": ["standard"]
    },
    "nonce": "same_nonce_from_request",
    "timestamp": 1743724805,
    "signature": "base64_ed25519_signature_of_canonical_json"
}
```

### Signature Verification

```c
// Extension verifies:
// 1. Signature is valid Ed25519 over canonical response JSON
// 2. Nonce matches what we sent (anti-replay)
// 3. Timestamp within 300 seconds of local time (anti-stale)
// 4. Status field matches expected values
```

### Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/v1/activate` | POST | Activate a license key on this hardware |
| `/api/v1/deactivate` | POST | Release hardware slot |
| `/api/v1/validate` | POST | Verify license is still valid |
| `/api/v1/backup/init` | POST | Get upload URL for backup |
| `/api/v1/backup/confirm` | POST | Confirm backup upload complete |
| `/api/v1/backup/list` | GET | List available backups |
| `/api/v1/backup/restore` | POST | Request restore (same or new device) |

---

## Online Verification Schedule

### Daily Soft Check (App Level)

- Runs on every app boot AND once daily via Laravel scheduler
- Calls `ims_verify_online()` from PHP
- Non-blocking: if offline, silently returns false
- If successful: updates `last_verified_online` timestamp in DPAPI
- If offline: shows warning banner after 4+ days
  - "Last verified X days ago. Please connect to internet within Y days."

### 7-Day Hard Check (Extension Level)

- Checked in RINIT on every request
- If `last_verified_online` is more than 7 days ago:
  - Extension sets `licensed = false`
  - Extension clears DB key from memory
  - All vendor patches fail (no valid license)
  - App shows: "License verification required. Please connect to internet."
- Once online: `ims_verify_online()` succeeds -> instant resume, no restart needed

### Timeline

```
Day 0: Last successful online verification
Day 1-3: Normal operation, daily soft checks (may fail silently)
Day 4: Warning banner appears: "Please connect to internet. 3 days remaining."
Day 5: Warning: "2 days remaining"
Day 6: Warning: "1 day remaining. App will lock tomorrow."
Day 7: HARD BLOCK. Extension refuses DB key. App locked.
        Shows revalidation screen. Must connect to internet.
Day 7+: Connect to internet -> ims_verify_online() -> immediate unlock
```

---

## Expiry Behavior

When a license expires (server says expired, or `expires_at` date passed):

### Immediate Full Stop

```
1. Extension RINIT detects: now() > expires_at
2. Extension sets licensed = false
3. Extension clears DB key
4. App cannot access any business data
5. App shows EXPIRY SCREEN:
   
   "Your license has expired.
   
    [Revalidate] - Check with server (maybe renewed on server side)
    [Enter New Key] - Activate a different license key
    
    WARNING: Entering a new key will change your data encryption.
    Your existing data will not be accessible with a new key unless
    you contact support for data migration."
```

### No Grace Period

- No read-only mode
- No limited access
- No "30 more days"
- Expired = fully stopped, immediately
- The only way forward: revalidate (if renewed server-side) or new key

### Revalidation

```
1. User clicks "Revalidate"
2. App calls ims_verify_online()
3. Extension contacts server with current license key
4. If server says "valid" (e.g., customer renewed via web portal):
   - Extension updates expires_at in DPAPI
   - DB key restored to memory
   - App resumes immediately
5. If server says "expired":
   - Still locked
   - User must enter new key or contact support
```

---

## New Key Flow (Different Key)

When a user enters a key that is DIFFERENT from their current key:

```
1. User enters new key on expiry/reactivation screen
2. App calls ims_update_license(new_key)
3. Extension validates new key with server
4. Extension detects: new_key != old_key stored in DPAPI
5. Extension returns: {success: true, key_changed: true}
6. App shows WARNING DIALOG:
   
   "WARNING: License Key Change
   
    Your new license key is different from the previous one.
    Your existing data was encrypted with the old key and 
    CANNOT be accessed with the new key.
    
    Options:
    [A] Contact support to transfer data (recommended)
        Your data can be migrated to the new key via our support team.
    
    [B] Start fresh (old data will remain on disk but inaccessible)
        A new empty database will be created.
    
    [C] Cancel (keep trying with current/expired key)"

7. If user chooses B:
   - Old DB renamed to ims_old_<timestamp>.sqlite
   - Extension stores new key in DPAPI
   - New DB created with new encryption key
   - Fresh migrations run
   - Setup wizard starts over

8. If user chooses A:
   - App initiates backup to server (if old key still allows)
   - Or user contacts support with license details
   - Support re-encrypts backup for new key (see DEVICE-TRANSFER.md)
```

---

## Dev/Test Licenses

For development and testing, the extension supports special test keys that activate locally without a server. These are compiled into the extension with a `#ifdef DEV_BUILD` guard and are NOT present in production builds.

```c
#ifdef DEV_BUILD
static const dev_license_t DEV_LICENSES[] = {
    {"IMS-TEST-0001-DEV1", "Test Shop", 365},
    {"IMS-TEST-0002-DEV2", "Demo Shop", 30},
    {"IMS-TEST-0003-DEMO", "Trial", 7},
};
#endif
```

Production builds are compiled WITHOUT the `DEV_BUILD` flag, so these keys do not exist in the shipped extension.

---

## Security Properties

| Property | How Enforced |
|----------|-------------|
| License cannot be faked in PHP | Extension validates with server, signature verified |
| License cannot be set without server | Extension's activate function contacts server |
| License server cannot be faked | Ed25519 signature -- private key only on real server |
| License removal breaks app | No license -> no DB key -> encrypted DB inaccessible |
| License copying to another machine | Hardware ID in DPAPI + key derivation -> different machine = different key |
| Expired license = full stop | Extension RINIT blocks, no grace period |
| Offline bypass | Hard block after 7 days, enforced in compiled C |
