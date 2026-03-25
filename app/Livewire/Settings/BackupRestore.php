<?php

namespace App\Livewire\Settings;

use App\Services\BackupService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BackupRestore extends Component
{
    public ?array $actionSummary = null;

    public bool $showRestoreConfirm = false;

    public ?string $restorePath = null;

    public bool $showDeleteConfirm = false;

    public ?string $deletePath = null;

    public function createBackup(): void
    {
        try {
            $service = app(BackupService::class);
            $path = $service->createLocalBackup();
            $filename = basename($path);
            $size = number_format(filesize($path) / 1024, 1);

            $this->actionSummary = [
                'type' => 'success',
                'title' => 'Backup Created',
                'message' => "Encrypted backup saved: {$filename} ({$size} KB)",
            ];
        } catch (\Throwable $e) {
            $this->actionSummary = [
                'type' => 'error',
                'title' => 'Backup Failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function confirmRestore(string $path): void
    {
        $this->restorePath = $path;
        $this->showRestoreConfirm = true;
    }

    public function restore(): void
    {
        if (! $this->restorePath) {
            return;
        }

        try {
            $service = app(BackupService::class);
            $service->restoreFromFile($this->restorePath);

            $this->showRestoreConfirm = false;
            $this->restorePath = null;

            $this->actionSummary = [
                'type' => 'success',
                'title' => 'Database Restored',
                'message' => 'Database and files restored successfully. Please restart the application.',
            ];
        } catch (\Throwable $e) {
            $this->showRestoreConfirm = false;
            $this->restorePath = null;

            $this->actionSummary = [
                'type' => 'error',
                'title' => 'Restore Failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function confirmDeleteBackup(string $path): void
    {
        $this->deletePath = $path;
        $this->showDeleteConfirm = true;
    }

    public function cancelDeleteBackup(): void
    {
        $this->showDeleteConfirm = false;
        $this->deletePath = null;
    }

    public function deleteBackup(): void
    {
        $this->showDeleteConfirm = false;

        if ($this->deletePath && file_exists($this->deletePath)) {
            $filename = basename($this->deletePath);
            unlink($this->deletePath);
            $this->actionSummary = [
                'type' => 'success',
                'title' => 'Backup Deleted',
                'message' => $filename.' has been deleted.',
            ];
        }

        $this->deletePath = null;
    }

    public function render()
    {
        $service = app(BackupService::class);
        $isOverdue = $service->shouldWarn();

        return view('livewire.settings.backup-restore', [
            'backups' => $service->getLocalBackups(),
            'lastBackup' => $service->getLastBackupTime(),
            'isOverdue' => $isOverdue,
        ]);
    }
}
