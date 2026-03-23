<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RestoreElectronPackageJson extends Command
{
    protected $signature = 'nativephp:restore-package-json';

    protected $description = 'Restore the NativePHP electron package.json after a build';

    public function handle(): void
    {
        $packageJsonPath = base_path('vendor/nativephp/electron/resources/js/package.json');
        $backupPath = base_path('vendor/nativephp/electron/resources/js/package.json.bak');

        if (file_exists($backupPath)) {
            copy($backupPath, $packageJsonPath);
            $this->info('package.json restored from backup.');
        } else {
            $this->warn('No backup found at package.json.bak — skipping restore.');
        }
    }
}
