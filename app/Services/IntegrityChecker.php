<?php

namespace App\Services;

/**
 * Anti-tampering system. At build time, generates a hash manifest of all
 * critical PHP files. At runtime, verifies the manifest on each request.
 * If files have been modified, the app refuses to start.
 *
 * Usage:
 *   Build time:  php artisan integrity:generate
 *   Runtime:     Auto-checked via middleware or service provider
 */
class IntegrityChecker
{
    private const MANIFEST_FILE = 'integrity.bin';

    /**
     * Generate the integrity manifest (run at build time).
     * Hashes all PHP files in critical directories.
     */
    public function generateManifest(): string
    {
        $hashes = [];

        $dirs = [
            app_path('Services'),
            app_path('Http/Middleware'),
            app_path('Models'),
            app_path('Livewire'),
        ];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $relativePath = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $relativePath = str_replace('\\', '/', $relativePath);
                    $hashes[$relativePath] = hash_file('sha256', $file->getPathname());
                }
            }
        }

        ksort($hashes);

        // Sign the manifest with app secret
        $data = json_encode($hashes);
        $signature = hash_hmac('sha256', $data, config('ims.app_secret'));

        // Store as binary: signature(64 hex) + \n + json
        $manifest = $signature."\n".$data;

        $path = $this->getManifestPath();
        file_put_contents($path, $manifest);

        return $path;
    }

    /**
     * Verify file integrity against the manifest.
     * Returns true if all files match or no manifest exists (dev mode).
     */
    public function verify(): bool
    {
        $path = $this->getManifestPath();

        // No manifest = dev mode, skip check
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

        // Verify manifest signature
        $expectedSignature = hash_hmac('sha256', $data, config('ims.app_secret'));
        if (! hash_equals($expectedSignature, $storedSignature)) {
            return false; // Manifest itself was tampered
        }

        $hashes = json_decode($data, true);
        if (! is_array($hashes)) {
            return false;
        }

        // Verify each file
        foreach ($hashes as $relativePath => $expectedHash) {
            $fullPath = base_path($relativePath);

            if (! file_exists($fullPath)) {
                return false; // File was deleted
            }

            $actualHash = hash_file('sha256', $fullPath);
            if (! hash_equals($expectedHash, $actualHash)) {
                return false; // File was modified
            }
        }

        return true;
    }

    /**
     * Get list of tampered files (for diagnostics).
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
