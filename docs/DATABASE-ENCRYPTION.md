# Database Encryption

## Overview

All business data is stored in a single SQLite database encrypted with SQLCipher. The encryption key is derived from the license key and hardware ID, and is only available through the `php_ims.dll` extension when the license is valid. Without a valid license, the database file is unreadable.

---

## Encryption Method: SQLCipher

SQLCipher is a fork of SQLite that adds transparent 256-bit AES encryption. It's compiled into a custom `pdo_sqlite.dll` that ships with the application, replacing NativePHP's default SQLite driver.

### How It Works

```
Normal SQLite:   PHP -> pdo_sqlite.dll -> file read/write (plaintext)
With SQLCipher:  PHP -> pdo_sqlite_cipher.dll -> encrypt/decrypt -> file read/write

The file on disk is always encrypted. Decryption happens in memory per-page.
AES-256-CBC with HMAC-SHA512 authentication per page.
Each page (4096 bytes) is independently encrypted.
```

### Performance

SQLCipher uses hardware AES-NI instructions on modern CPUs. Typical overhead: 5-15% on queries. For a desktop app with local SQLite, this is imperceptible.

---

## Key Derivation

```
DB_KEY = PBKDF2-SHA256(
    password:   license_key,
    salt:       SHA256(hardware_id + app_secret),
    iterations: 100000,
    key_length: 32 bytes (256 bits)
)

Where:
  license_key  = the activated license string (e.g., "IMS-PROD-A1B2-C3D4")
  hardware_id  = hardware fingerprint from extension (CPU + disk + MAC + machine GUID)
  app_secret   = compiled into extension, never in PHP config
```

### Why This Combination

- **license_key**: Ties data to the specific license. Different license = different key = different data.
- **hardware_id**: Ties data to the specific machine. Same license on different machine = different key.
- **app_secret**: Prevents brute-force if license key format is known. Compiled into DLL, not in PHP.

### Key Storage

The DB key is NEVER stored on disk. It's derived at runtime:

1. Extension reads license from DPAPI
2. Extension reads hardware fingerprint
3. Extension derives key using compiled-in app_secret
4. Key held in extension's C memory during PHP process lifetime
5. Key zeroed on process shutdown (MSHUTDOWN)

---

## Database Lifecycle

### Creation (First Activation)

```
1. User activates license
2. Extension stores license in DPAPI, derives DB_KEY
3. App detects: no ims.sqlite file exists
4. App creates new SQLite connection
5. Vendor patch in SQLiteConnector gets DB_KEY from extension
6. Connection runs: PRAGMA key = 'x''<hex-encoded-DB_KEY>''';
7. SQLCipher creates encrypted database file
8. Laravel runs all migrations on the encrypted DB
9. Setup wizard populates initial data
10. Database is encrypted from birth -- never exists unencrypted
```

### Normal Access

```
1. PHP starts, extension MINIT: derive DB_KEY from DPAPI license
2. Request arrives, extension RINIT: verify license still valid
3. Laravel opens SQLite connection
4. Vendor patch calls sqlite3_column_meta('conn_entropy')
   -> Extension returns hex-encoded DB_KEY
5. Connector runs: PRAGMA key = '<key>';
6. All queries work normally (SQLCipher decrypts in memory)
7. Request ends, connection closes
```

### Access Denied (No License / Expired)

```
1. Extension RINIT: license invalid or expired
2. Extension clears DB_KEY from internal storage
3. Laravel opens SQLite connection
4. Vendor patch calls sqlite3_column_meta('conn_entropy')
   -> Extension returns empty string (no valid key)
5. Connector runs: PRAGMA key = '';  (or skips)
6. First query: "Error: file is not a database"
7. App catches error, redirects to license/reactivation screen
```

---

## Key Change Behavior

When a user enters a NEW license key (different from the original):

```
Old key: IMS-PROD-A1B2-C3D4  ->  DB_KEY_OLD = PBKDF2(old_key, hw+secret)
New key: IMS-PROD-E5F6-G7H8  ->  DB_KEY_NEW = PBKDF2(new_key, hw+secret)

DB_KEY_OLD != DB_KEY_NEW

Result: Old database cannot be decrypted with new key.
```

### User Flow for Key Change

```
1. User enters new key
2. App calls ims_update_license(new_key)
3. Extension validates with server
4. Extension detects: new key != old key
5. Extension returns: {success: true, key_changed: true}
6. App shows WARNING:
   "Your license key has changed. Your existing data was encrypted
    with the old key and will not be accessible with the new key.
    
    Options:
    [A] Contact support to transfer your data (recommended)
    [B] Start fresh with new database (old data lost)
    [C] Cancel and keep current key"
    
7. If user chooses B:
   - Rename old DB: ims.sqlite -> ims_old_<date>.sqlite
   - Extension activates new key
   - App creates new encrypted DB with new key
   - Old DB file remains on disk (encrypted, inaccessible without old key)
   
8. If user chooses A:
   - App initiates backup of current DB to server
   - Server stores encrypted backup + key escrow
   - Server can re-encrypt with new key (see DEVICE-TRANSFER.md)
   - User restores re-encrypted backup
```

---

## SQLCipher Integration

### Custom pdo_sqlite.dll

NativePHP ships a standard `pdo_sqlite.dll`. We replace it with one compiled against SQLCipher:

```
Standard: pdo_sqlite.dll -> uses sqlite3.c (plain)
Ours:     pdo_sqlite.dll -> uses sqlite3.c (SQLCipher patched, with OpenSSL)
```

### How to Build

```bash
# 1. Get SQLCipher source (it's a modified sqlite3.c + sqlite3.h)
git clone https://github.com/nicedrive/sqlcipher-amalgamation.git

# 2. Get PHP source matching NativePHP's PHP version
# 3. Replace ext/pdo_sqlite/sqlite3.c with SQLCipher's
# 4. Compile with OpenSSL flags:
#    SQLITE_HAS_CODEC=1
#    SQLITE_TEMP_STORE=2
# 5. Output: pdo_sqlite.dll (with SQLCipher support)
```

### Alternative: Pre-built SQLCipher

If compiling is complex, use the PHP SQLite3 extension with SQLCipher as a separate DLL:
- Ship `libsqlcipher.dll` alongside PHP
- Configure PDO to use it via `pdo_sqlite.dll` built with SQLCipher

### Vendor Patch in SQLiteConnector

The patch is in `vendor/laravel/framework/src/Illuminate/Database/Connectors/SQLiteConnector.php`:

```php
// Original:
public function connect(array $config)
{
    // ... creates PDO connection ...
    return $connection;
}

// Patched (added after connection creation):
public function connect(array $config)
{
    // ... creates PDO connection ...
    
    // [patch: runtime entropy initialization]
    $entropy = sqlite3_column_meta('conn_entropy', 0x20);
    if ($entropy !== '') {
        $connection->exec("PRAGMA key = \"x'" . bin2hex($entropy) . "'\";");
    }
    
    return $connection;
}
```

This patch:
- Calls a disguised extension function to get the DB key
- Applies it via PRAGMA key (SQLCipher's encryption pragma)
- If extension is missing: `undefined function` fatal error
- If extension returns wrong key: `SQLITE_NOTADB` error
- Looks like standard SQLite metadata initialization

---

## Session Storage

Sessions MUST NOT use the database driver (the DB may be inaccessible during license activation).

```php
// config/session.php
'driver' => env('SESSION_DRIVER', 'file'),
```

File sessions work without any database. Stored in `storage/framework/sessions/`.

---

## What the Encrypted DB Looks Like

### With Valid Key (normal operation)

```sql
sqlite> PRAGMA key = 'correct_key';
sqlite> SELECT * FROM customers LIMIT 1;
1|Muhammad Ali|3520112345678|Rawalpindi|03001234567|2026-01-15
```

### Without Key or Wrong Key

```sql
sqlite> SELECT * FROM customers LIMIT 1;
Error: file is not a database

-- or with wrong key:
sqlite> PRAGMA key = 'wrong_key';
sqlite> SELECT * FROM customers LIMIT 1;
Error: file is not a database
```

### Hex Dump of Encrypted File

```
00000000: 53 51 4c 69 74 65 20 66  6f 72 6d 61 74 20 33 00  SQLite format 3.
                          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                          SQLCipher keeps the header but everything
                          else is AES-256 encrypted gibberish

00000010: a3 7f 1b 4c 9e 22 d1 87  5c 3a f0 6b 8d 2e 44 91  ...L."...:k..D.
00000020: e8 b7 3c 55 0a 19 d6 73  4f 82 a1 c0 5d 6e f3 28  ..<U...sO...]n.(
```

---

## Backup Encryption

When backing up to the server, the file is ALREADY encrypted (SQLCipher). No additional encryption layer needed for the backup file itself. The server stores:

1. The encrypted `.sqlite` file (as-is)
2. The DB_KEY encrypted with the server's master key (key escrow)

See `DEVICE-TRANSFER.md` for the full backup/restore/transfer flow.
