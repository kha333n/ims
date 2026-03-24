<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Uses %APPDATA%\IMS\ims.sqlite as the database location.
 * This persists across app uninstall/reinstall.
 * In testing, PHPUnit uses :memory: via phpunit.xml — this provider skips.
 */
class DatabaseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // PHPUnit sets DB_DATABASE=:memory: — don't override
        if (app()->environment('testing')) {
            return;
        }

        $persistentConfig = config('database.connections.persistent');
        if (! $persistentConfig) {
            return;
        }

        $dbPath = $persistentConfig['database'];
        $dbDir = dirname($dbPath);

        if (! is_dir($dbDir)) {
            mkdir($dbDir, 0700, true);
        }

        $isNewDb = ! file_exists($dbPath);
        if ($isNewDb) {
            touch($dbPath);
        }

        config(['database.default' => 'persistent']);

        try {
            $this->runMigrationsIfNeeded($isNewDb);
        } catch (\Throwable $e) {
            Log::error('Persistent DB migration failed: '.$e->getMessage());
        }
    }

    private function runMigrationsIfNeeded(bool $isNewDb): void
    {
        if ($isNewDb) {
            Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);

            return;
        }

        try {
            $migrator = app('migrator');
            $ran = $migrator->getRepository()->getRan();
            $allFiles = $migrator->getMigrationFiles(database_path('migrations'));
            $pending = array_diff(array_keys($allFiles), $ran);

            if (! empty($pending)) {
                Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
            }
        } catch (\Throwable) {
            Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
        }
    }
}
