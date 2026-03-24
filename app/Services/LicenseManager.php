<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseManager
{
    private const APPDATA_DIR = 'IMS';

    private const LICENSE_FILE = 'license.enc';

    private const REGISTRY_PATH = 'HKCU\\Software\\TechmiddleTech\\IMS';

    private ?array $licenseData = null;

    public function __construct(
        private HardwareFingerprint $fingerprint,
    ) {}

    /**
     * Check if the current license is valid (cached for request lifecycle).
     */
    public function isValid(): bool
    {
        $data = $this->getLicenseData();

        if (! $data) {
            return false;
        }

        // Hardware check
        if (! $this->fingerprint->verify($data['hardware_id'] ?? '')) {
            return false;
        }

        // Expiry check
        if (now()->isAfter($data['expires_at'] ?? '2000-01-01')) {
            return false;
        }

        // Offline grace period
        $lastOnline = $data['last_verified_online'] ?? null;
        $graceDays = config('ims.license.offline_grace_days', 7);

        if ($lastOnline && now()->diffInDays($lastOnline) > $graceDays) {
            // Try to verify online
            if ($this->verifyOnline($data['key'])) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Dev/test license keys that activate locally without a server.
     * Remove these in production builds.
     */
    private const DEV_LICENSES = [
        'IMS-TEST-0001-DEV1' => ['customer_name' => 'Test Shop (Dev)', 'days' => 365],
        'IMS-TEST-0002-DEV2' => ['customer_name' => 'Demo Shop (Dev)', 'days' => 30],
        'IMS-TEST-0003-DEMO' => ['customer_name' => 'Trial License', 'days' => 7],
    ];

    /**
     * Activate a license key on this machine.
     */
    public function activate(string $key): array
    {
        $hardwareId = $this->fingerprint->generate();

        // Check for dev/test licenses (local activation, no server needed)
        if (isset(self::DEV_LICENSES[$key])) {
            // Check if this key was previously activated on this hardware
            $previousActivation = $this->getPreviousDevActivation($key, $hardwareId);

            if ($previousActivation) {
                // Key was used before — use original expiry, don't grant fresh days
                if (now()->isAfter($previousActivation['expires_at'])) {
                    return ['success' => false, 'message' => 'This license key has expired and cannot be reactivated.'];
                }

                // Still valid — restore with original expiry
                $licenseData = $previousActivation;
                $licenseData['last_verified_online'] = now()->toIso8601String();
            } else {
                // First-time activation
                $dev = self::DEV_LICENSES[$key];
                $licenseData = [
                    'key' => $key,
                    'hardware_id' => $hardwareId,
                    'customer_name' => $dev['customer_name'],
                    'activated_at' => now()->toIso8601String(),
                    'expires_at' => now()->addDays($dev['days'])->toDateString(),
                    'last_verified_online' => now()->toIso8601String(),
                ];

                // Store permanent activation record in AppData (survives uninstall)
                $this->storeDevActivation($key, $hardwareId, $licenseData);
            }

            $this->storeLicense($licenseData);
            $this->licenseData = $licenseData;

            $daysLeft = (int) now()->diffInDays($licenseData['expires_at'], false);

            return ['success' => true, 'message' => "Dev license activated — expires in {$daysLeft} days"];
        }

        $serverUrl = config('ims.license.server_url');

        try {
            $response = Http::timeout(15)->post("{$serverUrl}/api/v1/activate", [
                'key' => $key,
                'hardware_id' => $hardwareId,
            ]);

            if (! $response->successful()) {
                $message = $response->json('message', 'Activation failed');

                return ['success' => false, 'message' => $message];
            }

            $body = $response->json();

            // Verify server signature
            if (! $this->verifySignature($body['license'] ?? [], $body['signature'] ?? '')) {
                return ['success' => false, 'message' => 'Invalid server response signature'];
            }

            $licenseData = [
                'key' => $body['license']['key'],
                'hardware_id' => $hardwareId,
                'customer_name' => $body['license']['customer_name'] ?? '',
                'activated_at' => now()->toIso8601String(),
                'expires_at' => $body['license']['expires_at'],
                'last_verified_online' => now()->toIso8601String(),
            ];

            $this->storeLicense($licenseData);
            $this->licenseData = $licenseData;

            return ['success' => true, 'message' => 'License activated successfully'];
        } catch (\Throwable $e) {
            Log::warning('License activation failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Could not connect to license server. Check your internet connection.'];
        }
    }

    /**
     * Deactivate the current license (frees hardware slot).
     */
    public function deactivate(): array
    {
        $data = $this->getLicenseData();
        if (! $data) {
            return ['success' => false, 'message' => 'No active license found'];
        }

        $serverUrl = config('ims.license.server_url');

        try {
            $response = Http::timeout(15)->post("{$serverUrl}/api/v1/deactivate", [
                'key' => $data['key'],
                'hardware_id' => $data['hardware_id'],
            ]);

            if ($response->successful()) {
                $this->clearLicense();
                $this->licenseData = null;

                return ['success' => true, 'message' => 'License deactivated. You can now activate on another machine.'];
            }

            return ['success' => false, 'message' => $response->json('message', 'Deactivation failed')];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Could not connect to server. Try again when online.'];
        }
    }

    /**
     * Verify license with server (when online).
     */
    public function verifyOnline(?string $key = null): bool
    {
        $data = $this->getLicenseData();
        $key = $key ?? ($data['key'] ?? null);

        if (! $key) {
            return false;
        }

        $serverUrl = config('ims.license.server_url');

        try {
            $response = Http::timeout(10)->post("{$serverUrl}/api/v1/validate", [
                'key' => $key,
                'hardware_id' => $this->fingerprint->generate(),
            ]);

            if ($response->successful() && $response->json('status') === 'valid') {
                // Update last verified timestamp
                if ($data) {
                    $data['last_verified_online'] = now()->toIso8601String();
                    $data['expires_at'] = $response->json('license.expires_at', $data['expires_at']);
                    $this->storeLicense($data);
                    $this->licenseData = $data;
                }

                return true;
            }
        } catch (\Throwable) {
            // Can't reach server — not a failure, just can't verify
        }

        return false;
    }

    /**
     * Get current license data (tries all 3 storage locations).
     */
    public function getLicenseData(): ?array
    {
        if ($this->licenseData !== null) {
            return $this->licenseData;
        }

        // Try each storage location in priority order
        $data = $this->readFromAppData()
            ?? $this->readFromRegistry()
            ?? $this->readFromDatabase();

        if ($data) {
            $this->licenseData = $data;
            // Sync to any missing locations
            $this->storeLicense($data);
        }

        return $data;
    }

    /**
     * Get the license status summary for display.
     */
    public function getStatus(): array
    {
        $data = $this->getLicenseData();

        if (! $data) {
            return [
                'status' => 'not_activated',
                'label' => 'Not Activated',
                'color' => 'red',
                'key' => null,
                'hardware_id' => $this->fingerprint->generate(),
                'customer_name' => null,
                'expires_at' => null,
                'last_verified' => null,
                'is_valid' => false,
            ];
        }

        $isExpired = now()->isAfter($data['expires_at'] ?? '2000-01-01');
        $hardwareMatch = $this->fingerprint->verify($data['hardware_id'] ?? '');

        $status = 'valid';
        $label = 'Valid';
        $color = 'green';

        if ($isExpired) {
            $status = 'expired';
            $label = 'Expired';
            $color = 'red';
        } elseif (! $hardwareMatch) {
            $status = 'hardware_mismatch';
            $label = 'Hardware Mismatch';
            $color = 'red';
        } else {
            $lastOnline = $data['last_verified_online'] ?? null;
            $graceDays = config('ims.license.offline_grace_days', 7);
            if ($lastOnline && now()->diffInDays($lastOnline) > $graceDays) {
                $status = 'offline_expired';
                $label = 'Offline Too Long';
                $color = 'orange';
            }
        }

        return [
            'status' => $status,
            'label' => $label,
            'color' => $color,
            'key' => $this->maskKey($data['key'] ?? ''),
            'key_raw' => $data['key'] ?? '',
            'hardware_id' => $this->fingerprint->generate(),
            'customer_name' => $data['customer_name'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'last_verified' => $data['last_verified_online'] ?? null,
            'is_valid' => $status === 'valid',
        ];
    }

    // ── Encryption ──────────────────────────────────────────

    private function encrypt(array $data): string
    {
        $json = json_encode($data);
        $key = $this->deriveKey();
        $iv = random_bytes(12);
        $tag = '';

        $encrypted = openssl_encrypt($json, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        return base64_encode($iv.$tag.$encrypted);
    }

    private function decrypt(string $encoded): ?array
    {
        try {
            $raw = base64_decode($encoded, true);
            if ($raw === false || strlen($raw) < 28) {
                return null;
            }

            $iv = substr($raw, 0, 12);
            $tag = substr($raw, 12, 16);
            $ciphertext = substr($raw, 28);
            $key = $this->deriveKey();

            $json = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

            if ($json === false) {
                return null;
            }

            return json_decode($json, true);
        } catch (\Throwable) {
            return null;
        }
    }

    private function deriveKey(): string
    {
        $secret = config('ims.app_secret');

        return hash_pbkdf2('sha256', $secret, 'ims-license-storage', 10000, 32, true);
    }

    // ── Storage: AppData ────────────────────────────────────

    private function getAppDataPath(): string
    {
        $appData = getenv('APPDATA') ?: (getenv('USERPROFILE').'\\AppData\\Roaming');

        return $appData.'\\'.self::APPDATA_DIR;
    }

    private function readFromAppData(): ?array
    {
        $path = $this->getAppDataPath().'\\'.self::LICENSE_FILE;

        if (! file_exists($path)) {
            return null;
        }

        return $this->decrypt(file_get_contents($path));
    }

    private function writeToAppData(string $encrypted): void
    {
        $dir = $this->getAppDataPath();
        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        file_put_contents($dir.'\\'.self::LICENSE_FILE, $encrypted);
    }

    // ── Storage: Windows Registry ───────────────────────────

    private function readFromRegistry(): ?array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return null;
        }

        try {
            $output = [];
            exec('reg query "'.self::REGISTRY_PATH.'" /v license 2>NUL', $output, $code);

            if ($code !== 0) {
                return null;
            }

            foreach ($output as $line) {
                if (str_contains($line, 'license') && str_contains($line, 'REG_SZ')) {
                    $parts = preg_split('/\s+/', trim($line), 3);
                    $value = $parts[2] ?? '';

                    return $this->decrypt($value);
                }
            }
        } catch (\Throwable) {
        }

        return null;
    }

    private function writeToRegistry(string $encrypted): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        try {
            exec('reg add "'.self::REGISTRY_PATH.'" /v license /t REG_SZ /d "'.$encrypted.'" /f 2>NUL');
        } catch (\Throwable) {
        }
    }

    // ── Storage: SQLite Settings ────────────────────────────

    private function readFromDatabase(): ?array
    {
        try {
            $value = Setting::get('license_data');

            return $value ? $this->decrypt($value) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function writeToDatabase(string $encrypted): void
    {
        try {
            Setting::set('license_data', $encrypted);
        } catch (\Throwable) {
        }
    }

    // ── Helpers ─────────────────────────────────────────────

    private function storeLicense(array $data): void
    {
        $encrypted = $this->encrypt($data);
        $this->writeToAppData($encrypted);
        $this->writeToRegistry($encrypted);
        $this->writeToDatabase($encrypted);
    }

    private function clearLicense(): void
    {
        // AppData
        $path = $this->getAppDataPath().'\\'.self::LICENSE_FILE;
        if (file_exists($path)) {
            unlink($path);
        }

        // Registry
        if (PHP_OS_FAMILY === 'Windows') {
            exec('reg delete "'.self::REGISTRY_PATH.'" /v license /f 2>NUL');
        }

        // Database
        try {
            Setting::set('license_data', null);
        } catch (\Throwable) {
        }
    }

    private function verifySignature(array $license, string $signature): string|bool
    {
        $secret = config('ims.app_secret');
        $expected = hash_hmac('sha256', json_encode($license), $secret);

        return hash_equals($expected, $signature);
    }

    private function maskKey(string $key): string
    {
        if (strlen($key) <= 8) {
            return $key;
        }

        return substr($key, 0, 4).'••••'.substr($key, -4);
    }

    // ── Dev License Activation Persistence ──────────────────
    // Stored in AppData as a separate file that survives uninstall.
    // Prevents reuse of expired test keys by reinstalling.

    private const DEV_ACTIVATIONS_FILE = 'dev_activations.enc';

    /**
     * Check if a dev key was previously activated on this hardware.
     */
    private function getPreviousDevActivation(string $key, string $hardwareId): ?array
    {
        $activations = $this->readDevActivations();

        $lookupKey = hash('sha256', $key.'|'.$hardwareId);

        return $activations[$lookupKey] ?? null;
    }

    /**
     * Store a permanent record that this dev key was activated.
     */
    private function storeDevActivation(string $key, string $hardwareId, array $licenseData): void
    {
        $activations = $this->readDevActivations();

        $lookupKey = hash('sha256', $key.'|'.$hardwareId);
        $activations[$lookupKey] = $licenseData;

        $this->writeDevActivations($activations);

        // Also store in Registry as a backup
        $this->writeDevActivationsToRegistry($activations);
    }

    private function readDevActivations(): array
    {
        // Try AppData first
        $path = $this->getAppDataPath().'\\'.self::DEV_ACTIVATIONS_FILE;
        if (file_exists($path)) {
            $data = $this->decrypt(file_get_contents($path));
            if (is_array($data)) {
                return $data;
            }
        }

        // Try Registry
        if (PHP_OS_FAMILY === 'Windows') {
            try {
                $output = [];
                exec('reg query "'.self::REGISTRY_PATH.'" /v dev_activations 2>NUL', $output, $code);
                if ($code === 0) {
                    foreach ($output as $line) {
                        if (str_contains($line, 'dev_activations') && str_contains($line, 'REG_SZ')) {
                            $parts = preg_split('/\s+/', trim($line), 3);
                            $data = $this->decrypt($parts[2] ?? '');
                            if (is_array($data)) {
                                return $data;
                            }
                        }
                    }
                }
            } catch (\Throwable) {
            }
        }

        return [];
    }

    private function writeDevActivations(array $activations): void
    {
        $dir = $this->getAppDataPath();
        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        file_put_contents(
            $dir.'\\'.self::DEV_ACTIVATIONS_FILE,
            $this->encrypt($activations)
        );
    }

    private function writeDevActivationsToRegistry(array $activations): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        try {
            $encrypted = $this->encrypt($activations);
            exec('reg add "'.self::REGISTRY_PATH.'" /v dev_activations /t REG_SZ /d "'.$encrypted.'" /f 2>NUL');
        } catch (\Throwable) {
        }
    }
}
