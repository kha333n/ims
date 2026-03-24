<?php

namespace App\Services;

/**
 * Encrypts the SQLite database file at rest.
 * The DB is decrypted to a temp file on app boot and re-encrypted on shutdown.
 * Key is hardware-bound — the encrypted DB can only be opened on this machine.
 */
class DatabaseEncryption
{
    private const MAGIC = "\xD8\x53\x4C\xE9"; // Custom magic — not recognizable

    private const VERSION = 1;

    private ?string $decryptedPath = null;

    public function __construct(
        private HardwareFingerprint $fingerprint,
    ) {}

    /**
     * Get the path to use for the database connection.
     * If the stored DB is encrypted, decrypt it to a temp location.
     * If it's a plain SQLite file, return as-is (first run / dev mode).
     */
    public function getDecryptedPath(string $encryptedPath): string
    {
        if ($this->decryptedPath && file_exists($this->decryptedPath)) {
            return $this->decryptedPath;
        }

        if (! file_exists($encryptedPath)) {
            // No DB yet — return the path as-is, it'll be created by migrations
            return $encryptedPath;
        }

        $contents = file_get_contents($encryptedPath);

        // Check if it's already a plain SQLite file (dev mode or first run)
        if (str_starts_with($contents, "SQLite format 3\0")) {
            return $encryptedPath;
        }

        // Check if it's our encrypted format
        if (str_starts_with($contents, self::MAGIC)) {
            $decrypted = $this->decrypt($contents);

            // Write to temp file
            $this->decryptedPath = tempnam(sys_get_temp_dir(), 'ims_db_');
            file_put_contents($this->decryptedPath, $decrypted);

            return $this->decryptedPath;
        }

        // Unknown format — treat as plain
        return $encryptedPath;
    }

    /**
     * Re-encrypt the database back to the storage path.
     * Called on app shutdown / after significant operations.
     */
    public function encryptAndSave(string $sourcePath, string $targetPath): void
    {
        if (! file_exists($sourcePath)) {
            return;
        }

        $plainData = file_get_contents($sourcePath);

        // Only encrypt if it's actual SQLite data
        if (! str_starts_with($plainData, "SQLite format 3\0")) {
            return;
        }

        $encrypted = $this->encrypt($plainData);
        file_put_contents($targetPath, $encrypted);
    }

    /**
     * Encrypt a plain SQLite file in-place.
     */
    public function encryptInPlace(string $dbPath): void
    {
        $this->encryptAndSave($dbPath, $dbPath);
    }

    /**
     * Clean up temp decrypted file.
     */
    public function cleanup(): void
    {
        if ($this->decryptedPath && file_exists($this->decryptedPath)) {
            // Overwrite with zeros before deleting (secure delete)
            $size = filesize($this->decryptedPath);
            file_put_contents($this->decryptedPath, str_repeat("\0", $size));
            unlink($this->decryptedPath);
            $this->decryptedPath = null;
        }
    }

    // ── Encryption ──────────────────────────────────────────

    private function encrypt(string $data): string
    {
        $salt = random_bytes(16);
        $key = $this->deriveKey($salt);
        $iv = random_bytes(12);
        $tag = '';

        $compressed = gzdeflate($data, 6);
        $ciphertext = openssl_encrypt($compressed, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            throw new \RuntimeException('Database encryption failed');
        }

        // Format: magic(4) + version(1) + salt(16) + iv(12) + tag(16) + ciphertext
        return self::MAGIC.pack('C', self::VERSION).$salt.$iv.$tag.$ciphertext;
    }

    private function decrypt(string $raw): string
    {
        if (strlen($raw) < 49) {
            throw new \RuntimeException('Encrypted DB file too short');
        }

        $magic = substr($raw, 0, 4);
        if ($magic !== self::MAGIC) {
            throw new \RuntimeException('Not an encrypted IMS database');
        }

        $version = ord($raw[4]);
        if ($version !== self::VERSION) {
            throw new \RuntimeException("Unsupported DB encryption version: {$version}");
        }

        $salt = substr($raw, 5, 16);
        $iv = substr($raw, 21, 12);
        $tag = substr($raw, 33, 16);
        $ciphertext = substr($raw, 49);

        $key = $this->deriveKey($salt);

        $compressed = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($compressed === false) {
            throw new \RuntimeException('Database decryption failed. Hardware mismatch?');
        }

        $data = gzinflate($compressed);
        if ($data === false) {
            throw new \RuntimeException('Database decompression failed');
        }

        return $data;
    }

    private function deriveKey(string $salt): string
    {
        // Hardware-bound: only this machine can decrypt
        $hardwareId = $this->fingerprint->generate();
        $secret = config('ims.app_secret');

        return hash_pbkdf2('sha256', $hardwareId.'|'.$secret, $salt, 100000, 32, true);
    }
}
