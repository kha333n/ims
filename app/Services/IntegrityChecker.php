<?php

namespace App\Services;

/**
 * Anti-tampering for critical security files only.
 * Only checks files that enforce licensing, payments, and access control.
 * NativePHP's build process modifies other files, so we can't hash everything.
 */
class IntegrityChecker
{
    private const MANIFEST_FILE = 'integrity.bin';

    /**
     * Only these specific files are integrity-checked.
     * These are the files someone would modify to bypass licensing/payments.
     */
    private const CRITICAL_FILES = [
        'app/Http/Middleware/SubscriptionGate.php',
        'app/Services/LicenseManager.php',
        'app/Services/HardwareFingerprint.php',
        'app/Services/IntegrityChecker.php',
        'app/Services/BackupService.php',
        'app/Services/DatabaseEncryption.php',
        'app/Livewire/Settings/LicenseSettings.php',
        'bootstrap/app.php',
    ];

    /**
     * Generate the integrity manifest (run at build time, AFTER NativePHP modifications).
     */
    public function generateManifest(): string
    {
        $hashes = [];

        foreach (self::CRITICAL_FILES as $relativePath) {
            $fullPath = base_path($relativePath);
            if (file_exists($fullPath)) {
                $hashes[$relativePath] = hash_file('sha256', $fullPath);
            }
        }

        ksort($hashes);

        $data = json_encode($hashes);
        $signature = hash_hmac('sha256', $data, config('ims.app_secret'));
        $manifest = $signature."\n".$data;

        $path = $this->getManifestPath();
        file_put_contents($path, $manifest);

        return $path;
    }

    /**
     * Verify critical file integrity.
     * Returns true if all critical files match or no manifest exists (dev mode).
     */
    public function verify(): bool
    {
        $path = $this->getManifestPath();

        // No manifest = dev mode, skip
        if (! file_exists($path)) {
            return true;
        }

        $contents = file_get_contents($path);
        $newlinePos = strpos($contents, "\n");

        if ($newlinePos === false) {
            return false;
        }

        $storedSignature = substr($contents, 0, $newlinePos);
        $data = substr($contents, $newlinePos + 1);

        $expectedSignature = hash_hmac('sha256', $data, config('ims.app_secret'));
        if (! hash_equals($expectedSignature, $storedSignature)) {
            return false;
        }

        $hashes = json_decode($data, true);
        if (! is_array($hashes)) {
            return false;
        }

        foreach ($hashes as $relativePath => $expectedHash) {
            $fullPath = base_path($relativePath);

            if (! file_exists($fullPath)) {
                return false;
            }

            if (! hash_equals($expectedHash, hash_file('sha256', $fullPath))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get list of tampered files (diagnostics).
     */
    public function getTamperedFiles(): array
    {
        $path = $this->getManifestPath();
        if (! file_exists($path)) {
            return [];
        }

        $contents = file_get_contents($path);
        $data = substr($contents, strpos($contents, "\n") + 1);
        $hashes = json_decode($data, true) ?? [];

        $tampered = [];
        foreach ($hashes as $relativePath => $expectedHash) {
            $fullPath = base_path($relativePath);
            if (! file_exists($fullPath)) {
                $tampered[] = ['file' => $relativePath, 'reason' => 'deleted'];
            } elseif (hash_file('sha256', $fullPath) !== $expectedHash) {
                $tampered[] = ['file' => $relativePath, 'reason' => 'modified'];
            }
        }

        return $tampered;
    }

    private function getManifestPath(): string
    {
        return base_path(self::MANIFEST_FILE);
    }
}
