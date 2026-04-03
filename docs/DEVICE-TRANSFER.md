# Device Transfer & Backup/Restore

## Overview

Because the database is encrypted with a key derived from the license key AND hardware ID, moving to a new device requires server-assisted re-encryption. The license server acts as an escrow for the encryption key, enabling device transfers without exposing plaintext data.

---

## Backup Flow

### When User Clicks "Backup Now"

```
+-----------+                    +-----------------+               +---------+
|   APP     |                    | LICENSE SERVER   |               | S3/DO   |
|  (Local)  |                    | (Your API)      |               | Storage |
+-----------+                    +-----------------+               +---------+
     |                                  |                               |
     | 1. Read encrypted ims.sqlite     |                               |
     |    (already encrypted by         |                               |
     |     SQLCipher, no extra          |                               |
     |     encryption needed)           |                               |
     |                                  |                               |
     | 2. POST /api/v1/backup/init      |                               |
     |    {license_key, hardware_id,    |                               |
     |     file_size, checksum_sha256,  |                               |
     |     db_key_encrypted}            |                               |
     | -------------------------------->|                               |
     |                                  |                               |
     |    db_key_encrypted =            |                               |
     |    AES-256-GCM(                  |                               |
     |      plaintext: DB_KEY,          |                               |
     |      key: SERVER_PUBLIC_KEY      |                               |
     |    )                             |                               |
     |    (Extension encrypts DB key    |                               |
     |     with server's public key     |                               |
     |     before sending)              |                               |
     |                                  |                               |
     | 3. Server returns                |                               |
     |    {backup_id, upload_url,       |                               |
     |     upload_headers}              |                               |
     | <--------------------------------|                               |
     |                                  |                               |
     | 4. Upload encrypted .sqlite      |                               |
     |    directly to S3/DO             |                               |
     | ---------------------------------------------------------------->|
     |                                  |                               |
     | 5. POST /api/v1/backup/confirm   |                               |
     |    {backup_id, checksum}         |                               |
     | -------------------------------->|                               |
     |                                  |                               |
     |    Server stores metadata:       |                               |
     |    {backup_id, license_key,      |                               |
     |     hardware_id,                 |                               |
     |     db_key_encrypted,            |                               |
     |     s3_path, file_size,          |                               |
     |     checksum, created_at}        |                               |
     |                                  |                               |
     | 6. {success: true}               |                               |
     | <--------------------------------|                               |
```

### DB Key Escrow

The DB_KEY is encrypted by the extension using the server's public key before transmission:

```
Extension does:
  1. db_key = current DB encryption key (32 bytes)
  2. nonce = random 24 bytes
  3. encrypted = crypto_box_seal(db_key, server_public_key)
     (libsodium sealed box -- only server's private key can decrypt)
  4. Send encrypted blob to server

Server does:
  1. Receives encrypted blob
  2. db_key = crypto_box_seal_open(encrypted, server_private_key)
  3. Stores: AES-256-GCM(db_key, SERVER_MASTER_KEY) as db_key_encrypted
```

The DB_KEY never travels in plaintext. The extension encrypts with the server's public key (compiled in). The server re-encrypts with its own master key for storage.

---

## Restore on Same Device

Simple case: same hardware, same license.

```
1. User clicks "Restore from Backup"
2. App calls GET /api/v1/backup/list {license_key, hardware_id}
3. Server returns list:
   [
     {backup_id: "bk_001", created_at: "2026-04-01", size: "15MB"},
     {backup_id: "bk_002", created_at: "2026-03-15", size: "14MB"},
   ]
4. User selects a backup
5. App calls POST /api/v1/backup/restore {backup_id, license_key, hardware_id}
6. Server verifies: same license + same hardware -> no re-encryption needed
7. Server returns: {download_url: "https://s3.../bk_001.sqlite"}
8. App downloads to: storage/app/ims_restore_temp.sqlite

SAFETY PROTOCOL:
9.  Verify download:
    - File size matches expected
    - SHA-256 checksum matches
    - SQLite header valid (first 16 bytes)
    - Try: PRAGMA key + SELECT count(*) FROM users
    - If ANY check fails -> delete temp file, show error, STOP

10. Backup current DB:
    - ims.sqlite -> ims_backup_before_restore_20260403_143022.sqlite

11. Swap:
    - ims_restore_temp.sqlite -> ims.sqlite

12. Verify app works:
    - Open connection with current DB key
    - Run test query
    - If fails -> ROLLBACK:
      - ims.sqlite -> ims_failed_restore_20260403.sqlite
      - ims_backup_before_restore_20260403_143022.sqlite -> ims.sqlite
      - Show: "Restore failed. Original data preserved."

13. Success:
    - Show: "Restored successfully from backup dated [date]"
    - Keep old backup file for 7 days, then auto-delete
    - Log restore event
```

---

## Device Transfer (New Device)

### Scenario

User's old device died / was sold / they bought a new PC. They have the same license key but different hardware.

### Step 1: Deactivate Old Device

Three options:

**Option A: User deactivates from old device (if still accessible)**
```
Old device: ims_deactivate_license()
  -> Extension calls server: POST /api/v1/deactivate {key, hardware_id}
  -> Server releases hardware slot
```

**Option B: Admin revokes remotely (if old device is dead)**
```
Admin panel: revoke hardware_id for license key
  -> Server marks old hardware as revoked
  -> Next activation on new hardware is allowed
```

**Option C: Auto-revoke (if old device hasn't contacted server in 30+ days)**
```
Server cron job:
  - Find licenses where last_verified > 30 days
  - Auto-revoke hardware slot
  - Next activation on any hardware is allowed
```

### Step 2: Activate on New Device

```
New device: install app -> license screen
User enters same license key
Extension calls: POST /api/v1/activate {key, new_hardware_id}

Server checks:
  - Key exists and is valid
  - Old hardware is deactivated/revoked
  - Activates for new hardware_id
  - Returns: {status: ok, expires_at, signature}

Extension stores new activation in DPAPI on new device
```

### Step 3: Restore Backup on New Device

```
+-----------+                    +-----------------+               +---------+
|   NEW     |                    | LICENSE SERVER   |               | S3/DO   |
|  DEVICE   |                    | (Your API)      |               | Storage |
+-----------+                    +-----------------+               +---------+
     |                                  |                               |
     | 1. POST /api/v1/backup/restore   |                               |
     |    {backup_id, license_key,      |                               |
     |     new_hardware_id}             |                               |
     | -------------------------------->|                               |
     |                                  |                               |
     |    Server detects:               |                               |
     |    hardware_id CHANGED           |                               |
     |    Need to re-encrypt!           |                               |
     |                                  |                               |
     |    2. Server decrypts DB key     |                               |
     |       from escrow:               |                               |
     |       db_key_old = AES-decrypt(  |                               |
     |         db_key_encrypted,        |                               |
     |         SERVER_MASTER_KEY)       |                               |
     |                                  |                               |
     |                                  | 3. Download encrypted backup  |
     |                                  | <-----------------------------|
     |                                  |                               |
     |    4. Server re-encrypts:        |                               |
     |       a. Open with old key:      |                               |
     |          PRAGMA key = old_key    |                               |
     |       b. Derive new key:         |                               |
     |          new_key = PBKDF2(       |                               |
     |            license_key,          |                               |
     |            new_hw_id + secret)   |                               |
     |       c. Re-key database:        |                               |
     |          PRAGMA rekey = new_key  |                               |
     |       d. Verify: test query      |                               |
     |                                  |                               |
     |                                  | 5. Upload re-encrypted DB     |
     |                                  | ---------------------------->|
     |                                  |                               |
     |    6. Update escrow:             |                               |
     |       db_key_encrypted =         |                               |
     |         AES-encrypt(new_key,     |                               |
     |         SERVER_MASTER_KEY)       |                               |
     |                                  |                               |
     | 7. {download_url: "..."}         |                               |
     | <--------------------------------|                               |
     |                                  |                               |
     | 8. Download re-encrypted DB      |                               |
     | <----------------------------------------------------------------|
     |                                  |                               |
     | 9. Verify + install              |                               |
     |    (same safety protocol         |                               |
     |     as same-device restore)      |                               |
```

### Server-Side Re-Encryption

The server needs the PBKDF2 key derivation logic (same as the extension) to derive the new DB key:

```python
# Server-side (Python/PHP):
def derive_db_key(license_key: str, hardware_id: str, app_secret: str) -> bytes:
    salt = hashlib.sha256((hardware_id + app_secret).encode()).digest()
    return hashlib.pbkdf2_hmac('sha256', license_key.encode(), salt, 100000, dklen=32)

# Re-encryption:
old_key = decrypt_escrow(backup.db_key_encrypted, SERVER_MASTER_KEY)
new_key = derive_db_key(license_key, new_hardware_id, APP_SECRET)

# Open with SQLCipher:
db = sqlite3.connect(backup_file)
db.execute(f"PRAGMA key = \"x'{old_key.hex()}'\"")
db.execute("SELECT count(*) FROM users")  # verify works
db.execute(f"PRAGMA rekey = \"x'{new_key.hex()}'\"")
db.close()

# Now the file is encrypted with new_key
```

The `app_secret` must be shared between the extension and the server. It's:
- Compiled into the extension (C constant)
- Stored in the server's environment variables
- Never in the Laravel app's .env or config

---

## Edge Cases

### Device Dead, No Recent Backup

```
1. User contacts support
2. Support checks server: last backup was 3 months ago
3. Support can still restore that backup to new device
   (server has key escrow, can re-encrypt for new hardware)
4. User loses 3 months of data
5. Recommendation: app shows warning if last backup > 7 days old
   "You haven't backed up in X days. Backup now to protect your data."
```

### License Expired, User Wants Data

```
No grace period. Expired = locked.

Options:
1. Renew license (same key) -> revalidate -> instant access to same data
2. Contact support -> support can provide a time-limited export key
   (future feature: extension accepts a special "export token" from server
    that unlocks DB for 1 hour in read-only mode for CSV export)
3. If license was revoked for fraud -> no access, period
```

### Multiple Backups, Different Keys

```
Scenario: User activated key A, backed up, then changed to key B, backed up.

Server stores:
  Backup 1: encrypted with key_A, escrow has key_A
  Backup 2: encrypted with key_B, escrow has key_B

User can restore either backup. Server uses the correct escrowed key
for whichever backup is selected. Re-encrypts with current device's key.
```

### Backup Corruption

```
1. Download backup from S3
2. Checksum doesn't match -> re-download (up to 3 retries)
3. Still doesn't match -> "Backup appears corrupted. Try another backup."
4. Server keeps multiple backup versions (not just latest)
5. User can select from backup history
```

### Server Unreachable During Restore

```
1. User is offline -> can't restore (needs server for download URL + re-encryption)
2. Show: "Restore requires internet connection. Please connect and try again."
3. Backup download from S3 also requires internet
4. No offline restore possible (by design -- server controls re-encryption)
```

---

## Automatic Backup Schedule

The app should encourage regular backups:

```php
// Check in a ServiceProvider or dashboard component
$lastBackup = Setting::get('last_backup_at');

if (!$lastBackup || now()->diffInDays($lastBackup) > 7) {
    // Show persistent banner:
    // "You haven't backed up in X days. Back up now to protect your data."
    // [Backup Now] button
}

if (!$lastBackup || now()->diffInDays($lastBackup) > 14) {
    // More urgent:
    // "WARNING: No backup in X days. If your device fails, you will lose data."
}
```

---

## Server Database Schema

```sql
-- Licenses
CREATE TABLE licenses (
    id BIGINT PRIMARY KEY,
    key VARCHAR(64) UNIQUE NOT NULL,
    customer_name VARCHAR(255),
    hardware_id VARCHAR(256),        -- current active hardware
    activated_at TIMESTAMP,
    expires_at DATE NOT NULL,
    last_verified_at TIMESTAMP,
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Hardware activation history
CREATE TABLE activations (
    id BIGINT PRIMARY KEY,
    license_id BIGINT REFERENCES licenses(id),
    hardware_id VARCHAR(256) NOT NULL,
    activated_at TIMESTAMP NOT NULL,
    deactivated_at TIMESTAMP,
    deactivation_reason ENUM('user', 'admin', 'auto', 'transfer'),
    ip_address VARCHAR(45),
    created_at TIMESTAMP
);

-- Backups
CREATE TABLE backups (
    id BIGINT PRIMARY KEY,
    backup_id VARCHAR(64) UNIQUE NOT NULL,  -- "bk_abc123"
    license_id BIGINT REFERENCES licenses(id),
    hardware_id VARCHAR(256) NOT NULL,       -- device that created it
    db_key_encrypted BLOB NOT NULL,          -- AES-encrypted DB key
    s3_path VARCHAR(512) NOT NULL,
    file_size BIGINT NOT NULL,
    checksum_sha256 VARCHAR(64) NOT NULL,
    created_at TIMESTAMP NOT NULL
);

-- Transfer/restore history
CREATE TABLE restores (
    id BIGINT PRIMARY KEY,
    backup_id VARCHAR(64) REFERENCES backups(backup_id),
    license_id BIGINT REFERENCES licenses(id),
    source_hardware_id VARCHAR(256),
    target_hardware_id VARCHAR(256),
    re_encrypted BOOLEAN DEFAULT FALSE,
    restored_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45)
);
```
