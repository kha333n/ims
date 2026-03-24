<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use App\Services\FileTracker;
use Illuminate\Console\Command;

class AutoBackup extends Command
{
    protected $signature = 'backup:auto {--scan-only : Only scan for file changes, do not create backup}';

    protected $description = 'Auto-backup if more than 12 hours since last backup';

    public function handle(BackupService $backup, FileTracker $tracker): int
    {
        // Scan for file changes first
        $changes = $tracker->scanForChanges();
        if ($changes > 0) {
            $this->line("Detected {$changes} file changes.");
        }

        if ($this->option('scan-only')) {
            $this->info("File scan complete. {$changes} changes detected.");

            return self::SUCCESS;
        }

        if (! $backup->shouldWarn()) {
            $this->info('Backup is up to date. Skipping.');

            return self::SUCCESS;
        }

        $this->info('Backup overdue. Creating auto-backup...');

        try {
            $path = $backup->createLocalBackup();
            $tracker->markAllBackedUp();

            $size = number_format(filesize($path) / 1024, 1);
            $this->info('Auto-backup created: '.basename($path)." ({$size} KB)");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Auto-backup failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
