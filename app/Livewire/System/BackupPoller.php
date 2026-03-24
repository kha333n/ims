<?php

namespace App\Livewire\System;

use App\Services\BackupService;
use App\Services\FileTracker;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * Hidden component that polls every 5 minutes.
 * Checks if auto-backup is due (>12h since last) and runs it silently.
 */
class BackupPoller extends Component
{
    public function checkBackup(): void
    {
        try {
            $backup = app(BackupService::class);

            if (! $backup->shouldWarn()) {
                return;
            }

            $tracker = app(FileTracker::class);
            $tracker->scanForChanges();

            $backup->createLocalBackup();
            $tracker->markAllBackedUp();

            Log::info('Auto-backup created via poller.');
        } catch (\Throwable $e) {
            Log::warning('Auto-backup failed: '.$e->getMessage());
        }
    }

    public function render(): string
    {
        return <<<'HTML'
        <div wire:poll.300s="checkBackup" class="hidden"></div>
        HTML;
    }
}
