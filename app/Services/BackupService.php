<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BackupService
{
    // Custom file format magic bytes — looks like garbage in hex editors
    private const MAGIC = "\xE1\x4D\x53\xB7"; // Not ASCII — won't show "IMSB"

    private const VERSION = 3;

    // XOR scramble key — obfuscates data before encryption so even
    // partial decryption or memory dumps look like noise
    private const SCRAMBLE_KEY = "\x7A\x3F\xB2\x91\xD4\x58\xE6\x0C\xAA\x1D\x73\xC5\x2E\x86\xF0\x49";

    // Fake file headers injected to confuse file-type detection tools
    private const DECOY_HEADERS = [
        "\x89\x50\x4E\x47", // PNG header
        "\x25\x50\x44\x46", // PDF header
        "\x50\x4B\x03\x04", // ZIP header
    ];

    public function __construct(
        private LicenseManager $licenseManager,
    ) {}

    /**
     * Create an encrypted local backup (DB + storage files).
     */
    public function createLocalBackup(): string
    {
        $payload = $this->buildPayload();
        $packed = $this->packAndProtect($payload);

        $backupDir = $this->getBackupDir();
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0700, true);
        }

        $filename = 'ims_'.date('Y-m-d_His').'.imsb';
        $path = $backupDir.DIRECTORY_SEPARATOR.$filename;

        file_put_contents($path, $packed);

        Setting::set('last_backup_at', now()->toIso8601String());

        // Mark tracked files as backed up
        app(FileTracker::class)->markAllBackedUp();

        $this->rotateBackups();

        return $path;
    }

    /**
     * Restore from a local backup file.
     */
    public function restoreFromFile(string $backupPath): void
    {
        if (! file_exists($backupPath)) {
            throw new \RuntimeException('Backup file not found');
        }

        $packed = file_get_contents($backupPath);
        $payload = $this->unpackAndVerify($packed);

        // Restore database
        $dbData = $payload['db'];
        if (substr($dbData, 0, 16) !== "SQLite format 3\0") {
            throw new \RuntimeException('Decrypted data is not a valid SQLite database');
        }

        $dbPath = $this->getDatabasePath();
        $safetyPath = $dbPath.'.pre-restore.bak';
        if (file_exists($dbPath)) {
            copy($dbPath, $safetyPath);
        }

        file_put_contents($dbPath, $dbData);

        // Restore storage files
        if (! empty($payload['files'])) {
            foreach ($payload['files'] as $relativePath => $content) {
                $fullPath = storage_path('app'.DIRECTORY_SEPARATOR.$relativePath);
                $dir = dirname($fullPath);
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents($fullPath, $content);
            }
        }
    }

    /**
     * Upload backup to cloud. DB is encrypted, files uploaded incrementally.
     */
    public function uploadToCloud(): array
    {
        $licenseData = $this->licenseManager->getLicenseData();
        if (! $licenseData) {
            return ['success' => false, 'message' => 'No active license.'];
        }

        $serverUrl = config('ims.license.server_url');

        try {
            // 1. Upload encrypted DB
            $dbPath = $this->getDatabasePath();
            if (! file_exists($dbPath)) {
                return ['success' => false, 'message' => 'Database not found.'];
            }

            $dbData = file_get_contents($dbPath);
            $encryptedDb = $this->encrypt($dbData);

            $response = Http::timeout(120)
                ->attach('database', $encryptedDb, 'database.enc')
                ->post("{$serverUrl}/api/v1/backup/upload-db", [
                    'key' => $licenseData['key'],
                ]);

            if (! $response->successful()) {
                return ['success' => false, 'message' => 'DB upload failed: '.$response->json('message', 'Unknown error')];
            }

            // 2. Upload new/changed files incrementally
            $lastCloudSync = Setting::get('last_cloud_files_sync');
            $files = $this->collectStorageFiles();
            $newFiles = [];

            foreach ($files as $relativePath => $fullPath) {
                if (! $lastCloudSync || filemtime($fullPath) > strtotime($lastCloudSync)) {
                    $newFiles[$relativePath] = $fullPath;
                }
            }

            if (! empty($newFiles)) {
                $request = Http::timeout(120);
                foreach ($newFiles as $relativePath => $fullPath) {
                    $request = $request->attach(
                        'files[]',
                        file_get_contents($fullPath),
                        $relativePath
                    );
                }

                $fileResponse = $request->post("{$serverUrl}/api/v1/backup/upload-files", [
                    'key' => $licenseData['key'],
                ]);

                if (! $fileResponse->successful()) {
                    return ['success' => true, 'message' => 'DB uploaded, but file sync failed.'];
                }
            }

            Setting::set('last_cloud_backup_at', now()->toIso8601String());
            Setting::set('last_cloud_files_sync', now()->toIso8601String());

            $fileCount = count($newFiles);

            return ['success' => true, 'message' => "Cloud backup complete. DB + {$fileCount} files synced."];
        } catch (\Throwable $e) {
            Log::warning('Cloud backup failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Could not connect to server.'];
        }
    }

    /**
     * List cloud backups.
     */
    public function listCloudBackups(): array
    {
        $licenseData = $this->licenseManager->getLicenseData();
        if (! $licenseData) {
            return [];
        }

        try {
            $response = Http::timeout(15)->get(
                config('ims.license.server_url').'/api/v1/backup/list',
                ['key' => $licenseData['key']]
            );

            return $response->successful() ? $response->json('backups', []) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Download a cloud backup.
     */
    public function downloadCloudBackup(int $id): ?string
    {
        $licenseData = $this->licenseManager->getLicenseData();
        if (! $licenseData) {
            return null;
        }

        try {
            $response = Http::timeout(120)->get(
                config('ims.license.server_url')."/api/v1/backup/download/{$id}",
                ['key' => $licenseData['key']]
            );

            if ($response->successful()) {
                $path = $this->getBackupDir().DIRECTORY_SEPARATOR.'cloud_'.date('Y-m-d_His').'.imsb';
                file_put_contents($path, $response->body());

                return $path;
            }
        } catch (\Throwable) {
        }

        return null;
    }

    public function getLocalBackups(): array
    {
        $dir = $this->getBackupDir();
        if (! is_dir($dir)) {
            return [];
        }

        $files = glob($dir.DIRECTORY_SEPARATOR.'*.imsb');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'path' => $file,
                'filename' => basename($file),
                'size' => filesize($file),
                'size_formatted' => $this->formatFileSize(filesize($file)),
                'created_at' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        usort($backups, fn ($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $backups;
    }

    public function shouldWarn(): bool
    {
        $lastBackup = Setting::get('last_backup_at');

        if (! $lastBackup) {
            return true;
        }

        $hours = config('ims.backup.warn_after_hours', 24);

        return abs(now()->diffInHours(Carbon::parse($lastBackup))) >= $hours;
    }

    public function getLastBackupTime(): ?string
    {
        return Setting::get('last_backup_at');
    }

    // ── Payload Builder ─────────────────────────────────────

    private function buildPayload(): array
    {
        $dbPath = $this->getDatabasePath();
        if (! file_exists($dbPath)) {
            throw new \RuntimeException('Database file not found');
        }

        $payload = [
            'db' => file_get_contents($dbPath),
            'files' => [],
            'created_at' => now()->toIso8601String(),
        ];

        // Collect storage files (product images, etc.)
        foreach ($this->collectStorageFiles() as $relativePath => $fullPath) {
            $payload['files'][$relativePath] = file_get_contents($fullPath);
        }

        return $payload;
    }

    private function collectStorageFiles(): array
    {
        $files = [];
        $storageDir = config('filesystems.disks.persistent.root');
        $scanDirs = ['product-images']; // Add more dirs as needed

        foreach ($scanDirs as $subDir) {
            $dir = $storageDir.DIRECTORY_SEPARATOR.$subDir;
            if (! is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $relativePath = str_replace(
                        $storageDir.DIRECTORY_SEPARATOR,
                        '',
                        $file->getPathname()
                    );
                    // Normalize to forward slashes for cross-platform
                    $files[str_replace('\\', '/', $relativePath)] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    // ── Pack / Unpack with multi-layer protection ───────────

    /**
     * Pack payload into the protected .imsb format:
     * 1. Serialize payload to binary (msgpack-like custom format)
     * 2. Compress with gzip
     * 3. XOR scramble (obfuscation layer — not cryptographic, but makes raw data unreadable)
     * 4. AES-256-GCM encrypt with license-derived key
     * 5. Wrap in custom binary format with decoy headers and junk padding
     */
    private function packAndProtect(array $payload): string
    {
        // Step 1: Serialize
        $serialized = serialize($payload);

        // Step 2: Compress
        $compressed = gzdeflate($serialized, 9);

        // Step 3: XOR scramble
        $scrambled = $this->xorScramble($compressed);

        // Step 4: Encrypt
        $encrypted = $this->encrypt($scrambled);

        // Step 5: Wrap in obfuscated container
        return $this->wrapContainer($encrypted);
    }

    private function unpackAndVerify(string $container): array
    {
        // Step 5: Unwrap container
        $encrypted = $this->unwrapContainer($container);

        // Step 4: Decrypt
        $scrambled = $this->decrypt($encrypted);

        // Step 3: Un-scramble
        $compressed = $this->xorScramble($scrambled); // XOR is symmetric

        // Step 2: Decompress
        $serialized = gzinflate($compressed);
        if ($serialized === false) {
            throw new \RuntimeException('Backup decompression failed');
        }

        // Step 1: Unserialize (safe — only our own data)
        $payload = @unserialize($serialized, ['allowed_classes' => false]);
        if (! is_array($payload) || ! isset($payload['db'])) {
            throw new \RuntimeException('Invalid backup structure');
        }

        return $payload;
    }

    // ── XOR Scramble ────────────────────────────────────────

    private function xorScramble(string $data): string
    {
        $key = self::SCRAMBLE_KEY;
        $keyLen = strlen($key);
        $result = '';

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $result .= $data[$i] ^ $key[$i % $keyLen];
        }

        return $result;
    }

    // ── Container Format ────────────────────────────────────
    // The container wraps encrypted data with junk to confuse analysis:
    // [decoy header (4)] [junk padding (32-64 random)] [magic (4)] [version (1)]
    // [junk length (1)] [encrypted data] [checksum (4)]

    private function wrapContainer(string $encrypted): string
    {
        // Random decoy header — looks like PNG/PDF/ZIP to casual inspection
        $decoy = self::DECOY_HEADERS[array_rand(self::DECOY_HEADERS)];

        // Random junk padding (32-64 bytes)
        $junkLen = random_int(32, 64);
        $junk = random_bytes($junkLen);

        // Build container
        $container = $decoy
            .$junk
            .self::MAGIC
            .pack('C', self::VERSION)
            .pack('C', $junkLen)
            .$encrypted;

        // Append CRC32 checksum of everything before it
        $container .= pack('N', crc32($container));

        return $container;
    }

    private function unwrapContainer(string $container): string
    {
        $len = strlen($container);

        // Minimum: decoy(4) + junk(32) + magic(4) + version(1) + junkLen(1) + some data + checksum(4)
        if ($len < 50) {
            throw new \RuntimeException('Invalid backup file');
        }

        // Verify checksum (last 4 bytes)
        $storedChecksum = unpack('N', substr($container, -4))[1];
        $body = substr($container, 0, -4);
        if (crc32($body) !== $storedChecksum) {
            throw new \RuntimeException('Backup file corrupted or tampered');
        }

        // Skip decoy header (4 bytes)
        $pos = 4;

        // Read past junk — we need to find our magic bytes
        // Scan for magic starting after decoy
        $magicPos = strpos($container, self::MAGIC, $pos);
        if ($magicPos === false) {
            throw new \RuntimeException('Not a valid IMS backup file');
        }

        $pos = $magicPos + 4; // Past magic

        $version = ord($container[$pos]);
        $pos++;

        if ($version !== self::VERSION) {
            throw new \RuntimeException("Unsupported backup version: {$version}");
        }

        $junkLen = ord($container[$pos]);
        $pos++;

        // Encrypted data is between current position and checksum
        $encrypted = substr($container, $pos, $len - $pos - 4);

        return $encrypted;
    }

    // ── AES Encryption (license-key only) ───────────────────

    private function encrypt(string $data): string
    {
        $salt = random_bytes(16);
        $key = $this->deriveKey($salt);
        $iv = random_bytes(12);
        $tag = '';

        $ciphertext = openssl_encrypt($data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }

        // salt(16) + iv(12) + tag(16) + ciphertext
        return $salt.$iv.$tag.$ciphertext;
    }

    private function decrypt(string $raw): string
    {
        if (strlen($raw) < 44) {
            throw new \RuntimeException('Encrypted data too short');
        }

        $salt = substr($raw, 0, 16);
        $iv = substr($raw, 16, 12);
        $tag = substr($raw, 28, 16);
        $ciphertext = substr($raw, 44);

        $key = $this->deriveKey($salt);

        $data = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($data === false) {
            throw new \RuntimeException('Decryption failed. Wrong license or corrupted file.');
        }

        return $data;
    }

    private function deriveKey(string $salt): string
    {
        $licenseData = $this->licenseManager->getLicenseData();
        $licenseKey = $licenseData['key'] ?? 'no-license';

        // License key only — no hardware binding. Backup works on any machine
        // with the same license. The scramble layer provides additional obfuscation.
        return hash_pbkdf2('sha256', $licenseKey, $salt, 200000, 32, true);
    }

    // ── Helpers ──────────────────────────────────────────────

    private function getDatabasePath(): string
    {
        $connection = config('database.default', 'sqlite');

        return config("database.connections.{$connection}.database", database_path('database.sqlite'));
    }

    private function getBackupDir(): string
    {
        $configured = config('ims.backup.local_path');

        if ($configured) {
            return $configured;
        }

        $appData = getenv('APPDATA') ?: (getenv('USERPROFILE').'\\AppData\\Roaming');

        return $appData.'\\IMS\\backups';
    }

    private function getLatestBackupPath(): ?string
    {
        $backups = $this->getLocalBackups();

        return $backups[0]['path'] ?? null;
    }

    private function rotateBackups(): void
    {
        $max = config('ims.backup.max_local_backups', 7);
        $backups = $this->getLocalBackups();

        if (count($backups) <= $max) {
            return;
        }

        foreach (array_slice($backups, $max) as $backup) {
            if (file_exists($backup['path'])) {
                unlink($backup['path']);
            }
        }
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format($bytes / 1024, 0).' KB';
    }
}
