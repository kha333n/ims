<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BuildProduction extends Command
{
    protected $signature = 'build:production
        {--skip-build : Only prepare, do not run native:build}
        {--skip-tests : Skip running test suite}
        {--debug : Enable APP_DEBUG=true in the build}';

    protected $description = 'Build the IMS desktop app. Runs directly without staging copy.';

    private ?string $originalEnv = null;

    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info('=== IMS Production Build ===');
        $this->newLine();

        // Step 1: Run tests
        if (! $this->option('skip-tests')) {
            $this->step('1/6', 'Running test suite');
            if ($this->runProcess($this->envPrefix('APP_ENV=testing').'php artisan test --compact') !== 0) {
                $this->error('Tests failed! Fix tests before building.');

                return self::FAILURE;
            }
        } else {
            $this->step('1/6', 'Skipping tests (--skip-tests)');
        }

        // Step 2: Build frontend assets
        $this->step('2/6', 'Building frontend assets');
        $this->runProcess('npm run build');

        // Step 3: Swap .env for production
        $this->step('3/6', 'Preparing production .env');
        $this->swapEnv();

        // Step 4: Generate integrity manifest
        $this->step('4/6', 'Generating integrity manifest');
        $this->generateIntegrity();

        // Step 5: Build with NativePHP (uses cleanup_exclude_files from config)
        if (! $this->option('skip-build')) {
            $this->step('5/6', 'Building Windows x64 installer');
            $buildResult = $this->runProcess('php artisan native:build win x64 --no-interaction');

            // Always restore .env
            $this->restoreEnv();

            if ($buildResult !== 0) {
                $this->error('native:build failed!');

                return self::FAILURE;
            }
        } else {
            $this->step('5/6', 'Skipping native:build (--skip-build)');
            $this->restoreEnv();
        }

        // Step 6: Clean up integrity.bin from project (only needed in build)
        $this->step('6/6', 'Cleanup');
        @unlink(base_path('integrity.bin'));
        $this->line('  Removed integrity.bin from project root.');

        $elapsed = round(microtime(true) - $startTime, 1);
        $this->newLine();
        $this->info("=== Build complete in {$elapsed}s ===");

        if (is_dir(base_path('dist'))) {
            $this->info('Installer at: dist/');
        }

        return self::SUCCESS;
    }

    private function step(string $number, string $message): void
    {
        $this->newLine();
        $this->info("[{$number}] {$message}");
        $this->line(str_repeat('-', 50));
    }

    private function swapEnv(): void
    {
        $envPath = base_path('.env');
        $this->originalEnv = file_exists($envPath) ? file_get_contents($envPath) : null;

        $sentryDsn = config('ims.sentry_dsn', '');
        $isDebug = $this->option('debug');
        $envContent = "APP_NAME=\"Installment Management System\"\n"
            ."APP_ENV=production\n"
            .'APP_KEY='.config('app.key')."\n"
            .'APP_DEBUG='.($isDebug ? 'true' : 'false')."\n"
            ."APP_URL=http://localhost\n"
            ."DB_CONNECTION=sqlite\n"
            .'IMS_DEMO_SEED='.($isDebug ? 'true' : 'false')."\n"
            ."LOG_CHANNEL=stack\n"
            ."LOG_STACK=single,sentry_logs\n"
            ."SENTRY_LARAVEL_DSN={$sentryDsn}\n"
            ."SENTRY_ENABLE_LOGS=true\n";

        file_put_contents($envPath, $envContent);
        $this->line('  Production .env written (original backed up in memory).');
    }

    private function restoreEnv(): void
    {
        if ($this->originalEnv !== null) {
            file_put_contents(base_path('.env'), $this->originalEnv);
            $this->line('  Original .env restored.');
        }
    }

    private function generateIntegrity(): void
    {
        $criticalFiles = [
            'app/Http/Middleware/SubscriptionGate.php',
            'app/Services/LicenseManager.php',
            'app/Services/HardwareFingerprint.php',
            'app/Services/IntegrityChecker.php',
            'app/Services/BackupService.php',
            'app/Services/DatabaseEncryption.php',
            'app/Livewire/Settings/LicenseSettings.php',
            'bootstrap/app.php',
        ];

        $hashes = [];
        foreach ($criticalFiles as $relativePath) {
            $fullPath = base_path($relativePath);
            if (file_exists($fullPath)) {
                $hashes[$relativePath] = hash_file('sha256', $fullPath);
            }
        }

        ksort($hashes);
        $data = json_encode($hashes);
        $signature = hash_hmac('sha256', $data, config('ims.app_secret'));

        file_put_contents(base_path('integrity.bin'), $signature."\n".$data);
        $this->line('  Integrity manifest generated ('.count($hashes).' files hashed).');
    }

    private function envPrefix(string $envVar): string
    {
        // Windows: use 'set VAR=val&&' syntax; Unix: use 'VAR=val ' prefix
        if (DIRECTORY_SEPARATOR === '\\') {
            return 'set '.$envVar.'&& ';
        }

        return $envVar.' ';
    }

    private function runProcess(string $command): int
    {
        $exitCode = 0;
        passthru($command, $exitCode);

        return $exitCode;
    }
}
