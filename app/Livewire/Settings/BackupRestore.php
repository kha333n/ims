<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\File;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BackupRestore extends Component
{
    public ?array $actionSummary = null;

    public function createBackup(): void
    {
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $dbPath = database_path('nativephp.sqlite');
        if (! File::exists($dbPath)) {
            $dbPath = database_path('database.sqlite');
        }

        if (! File::exists($dbPath)) {
            $this->addError('backup', 'Database file not found.');

            return;
        }

        $filename = 'backup_'.now()->format('Y-m-d_His').'.sqlite';
        File::copy($dbPath, "{$backupDir}/{$filename}");

        $size = number_format(File::size("{$backupDir}/{$filename}") / 1024, 1);
        $this->actionSummary = ['action' => 'Backup Created', 'detail' => "{$filename} ({$size} KB)"];
    }

    public function deleteBackup(string $filename): void
    {
        $path = storage_path("app/backups/{$filename}");
        if (File::exists($path)) {
            File::delete($path);
            $this->actionSummary = ['action' => 'Backup Deleted', 'detail' => $filename];
        }
    }

    public function render()
    {
        $backupDir = storage_path('app/backups');
        $backups = [];
        if (File::isDirectory($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $file) {
                if ($file->getExtension() === 'sqlite') {
                    $backups[] = [
                        'name' => $file->getFilename(),
                        'size' => number_format($file->getSize() / 1024, 1),
                        'date' => date('d/M/Y H:i', $file->getMTime()),
                    ];
                }
            }
            usort($backups, fn ($a, $b) => strcmp($b['name'], $a['name']));
        }

        return view('livewire.settings.backup-restore', ['backups' => $backups]);
    }
}
