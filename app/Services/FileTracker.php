<?php

namespace App\Services;

use App\Models\FileChangeLog;

/**
 * Tracks file changes in storage directories for incremental backup.
 * Call logChange() whenever a file is created/modified/deleted.
 * Call scanForChanges() periodically to catch any missed changes.
 */
class FileTracker
{
    /** Directories to track (relative to storage/app) */
    private const TRACKED_DIRS = [
        'product-images',
    ];

    /**
     * Log a single file change. Call this from your code when saving files.
     */
    public function logChange(string $relativePath, string $action = 'modified'): void
    {
        $fullPath = storage_path('app'.DIRECTORY_SEPARATOR.$relativePath);
        $hash = null;
        $size = null;

        if ($action !== 'deleted' && file_exists($fullPath)) {
            $hash = hash_file('sha256', $fullPath);
            $size = filesize($fullPath);
        }

        FileChangeLog::create([
            'relative_path' => $this->normalizePath($relativePath),
            'action' => $action,
            'file_size' => $size,
            'file_hash' => $hash,
            'backed_up' => false,
        ]);
    }

    /**
     * Scan tracked directories for changes since last scan.
     * Compares filesystem state against the last known hash in the log.
     * Returns count of new changes detected.
     */
    public function scanForChanges(): int
    {
        $changes = 0;
        $storageDir = storage_path('app');

        // Build a map of last known state from the log
        $knownFiles = FileChangeLog::query()
            ->selectRaw('relative_path, MAX(id) as latest_id')
            ->groupBy('relative_path')
            ->pluck('latest_id', 'relative_path');

        $knownState = [];
        if ($knownFiles->isNotEmpty()) {
            $latestEntries = FileChangeLog::whereIn('id', $knownFiles->values())->get();
            foreach ($latestEntries as $entry) {
                $knownState[$entry->relative_path] = [
                    'action' => $entry->action,
                    'hash' => $entry->file_hash,
                ];
            }
        }

        // Scan filesystem
        $currentFiles = [];
        foreach (self::TRACKED_DIRS as $subDir) {
            $dir = $storageDir.DIRECTORY_SEPARATOR.$subDir;
            if (! is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $relativePath = $this->normalizePath(
                    str_replace($storageDir.DIRECTORY_SEPARATOR, '', $file->getPathname())
                );

                $currentHash = hash_file('sha256', $file->getPathname());
                $currentFiles[$relativePath] = $currentHash;

                $known = $knownState[$relativePath] ?? null;

                if (! $known) {
                    // New file
                    $this->logChange($relativePath, 'created');
                    $changes++;
                } elseif ($known['action'] !== 'deleted' && $known['hash'] !== $currentHash) {
                    // Modified file
                    $this->logChange($relativePath, 'modified');
                    $changes++;
                }
            }
        }

        // Check for deleted files
        foreach ($knownState as $path => $state) {
            if ($state['action'] === 'deleted') {
                continue;
            }

            // Only check files in tracked dirs
            $inTrackedDir = false;
            foreach (self::TRACKED_DIRS as $dir) {
                if (str_starts_with($path, $dir.'/')) {
                    $inTrackedDir = true;
                    break;
                }
            }

            if ($inTrackedDir && ! isset($currentFiles[$path])) {
                $this->logChange($path, 'deleted');
                $changes++;
            }
        }

        return $changes;
    }

    /**
     * Get files that have changed since last backup.
     * Returns array of [relative_path => action].
     */
    public function getUnbackedChanges(): array
    {
        return FileChangeLog::notBackedUp()
            ->orderBy('id')
            ->get()
            ->groupBy('relative_path')
            ->map(fn ($entries) => $entries->last()->action)
            ->toArray();
    }

    /**
     * Mark all current changes as backed up.
     */
    public function markAllBackedUp(): void
    {
        FileChangeLog::notBackedUp()->update(['backed_up' => true]);
    }

    /**
     * Get count of pending changes.
     */
    public function pendingCount(): int
    {
        return FileChangeLog::notBackedUp()->count();
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
