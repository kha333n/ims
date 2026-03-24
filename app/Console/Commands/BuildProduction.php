<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BuildProduction extends Command
{
    protected $signature = 'build:production
        {--skip-build : Only prepare staging dir, do not run native:build}
        {--skip-tests : Skip running test suite}
        {--debug : Enable APP_DEBUG=true in the build for troubleshooting}';

    protected $description = 'Full production build pipeline: copy → obfuscate → integrity → clean → build Win64. Does NOT modify project files.';

    private string $stagingDir;

    public function handle(): int
    {
        $startTime = microtime(true);
        $this->stagingDir = base_path('build/.build-staging');

        $this->info('=== IMS Production Build ===');
        $this->info('All changes happen in build/.build-staging/ — your project files are untouched.');
        $this->newLine();

        // Step 1: Run tests on the original source
        if (! $this->option('skip-tests')) {
            $this->step('1/8', 'Running test suite');
            $testEnv = 'APP_ENV=testing';
            if (DIRECTORY_SEPARATOR === '\\') {
                $testEnv = 'set APP_ENV=testing&&';
            } else {
                $testEnv = 'APP_ENV=testing';
            }
            if ($this->runProcess($testEnv.' php artisan test --compact') !== 0) {
                $this->error('Tests failed! Fix tests before building.');

                return self::FAILURE;
            }
        } else {
            $this->step('1/8', 'Skipping tests (--skip-tests)');
        }

        // Step 2: Build frontend assets in the project (they'll be copied)
        $this->step('2/8', 'Building frontend assets');
        $this->runProcess('npm run build');

        // Step 3: Copy project to staging directory
        $this->step('3/8', 'Creating staging copy');
        $this->createStagingCopy();

        // Step 4: Obfuscation disabled — integrity check is the protection layer
        $this->step('4/8', 'Skipping obfuscation (integrity check enforced instead)');

        // Step 5: Remove dev files from staging
        $this->step('5/8', 'Cleaning non-production files');
        $this->cleanStaging();

        // Step 6: Dev license keys kept until license server is built
        $this->step('6/8', 'Dev license keys: KEPT (no license server yet)');

        // Step 7: Generate integrity manifest LAST (on final files)
        $this->step('7/8', 'Generating integrity manifest');
        $this->generateIntegrityInStaging();
        // TODO: When license server is ready, uncomment:
        // $this->removeDevLicenses();

        // Step 8: Build from staging directory
        if (! $this->option('skip-build')) {
            $this->step('8/8', 'Building Windows x64 installer from staging');
            $buildResult = $this->runProcess(
                'cd '.escapeshellarg($this->stagingDir).' && php artisan native:build win'
            );

            if ($buildResult !== 0) {
                $this->error('native:build failed!');

                return self::FAILURE;
            }

            // Copy dist/ back to project root
            $stagingDist = $this->stagingDir.'/dist';
            $projectDist = base_path('dist');
            if (is_dir($stagingDist)) {
                if (! is_dir($projectDist)) {
                    mkdir($projectDist, 0755, true);
                }
                $this->copyDirectory($stagingDist, $projectDist);
                $this->line('  Installer copied to dist/');
            }
        } else {
            $this->step('8/8', 'Skipping native:build (--skip-build)');
            $this->info("Staging directory ready at: {$this->stagingDir}");
        }

        $elapsed = round(microtime(true) - $startTime, 1);
        $this->newLine();
        $this->info("=== Build complete in {$elapsed}s ===");
        $this->info('Your project files remain unchanged.');

        return self::SUCCESS;
    }

    private function step(string $number, string $message): void
    {
        $this->newLine();
        $this->info("[{$number}] {$message}");
        $this->line(str_repeat('-', 50));
    }

    private function createStagingCopy(): void
    {
        // Clean previous staging
        if (is_dir($this->stagingDir)) {
            $this->line('  Removing previous staging dir...');
            $this->deleteDirectory($this->stagingDir);
        }

        $this->line('  Copying project to staging...');

        $excludeDirs = [
            '.git',
            'node_modules',
            'build/.build-staging',
            'build/yakpro-po/vendor',
            'dist',
            '.idea',
            '.vscode',
            '.fleet',
            '.nova',
            '.zed',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
        ];

        $excludeFiles = [
            '.env',
            '.env.backup',
            '.phpunit.result.cache',
        ];

        $source = base_path();

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $count = 0;
        foreach ($iterator as $item) {
            $relativePath = str_replace($source.DIRECTORY_SEPARATOR, '', $item->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);

            // Check excludes
            $skip = false;
            foreach ($excludeDirs as $exc) {
                if (str_starts_with($relativePath, $exc.'/') || $relativePath === $exc) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }

            if ($item->isFile() && in_array(basename($relativePath), $excludeFiles)) {
                continue;
            }

            $target = $this->stagingDir.DIRECTORY_SEPARATOR.$relativePath;

            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } elseif ($item->isFile()) {
                $targetDir = dirname($target);
                if (! is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                copy($item->getPathname(), $target);
                $count++;
            }
        }

        // Create production .env
        $sentryDsn = config('ims.sentry_dsn', '');
        $envContent = "APP_NAME=\"Installment Management System\"\n"
            ."APP_ENV=production\n"
            .'APP_KEY='.config('app.key')."\n"
            .'APP_DEBUG='.($this->option('debug') ? 'true' : 'false')."\n"
            ."APP_URL=http://localhost\n"
            ."LOG_CHANNEL=single\n"
            ."DB_CONNECTION=sqlite\n"
            ."SENTRY_LARAVEL_DSN={$sentryDsn}\n"
            ."SENTRY_ENABLE_LOGS=true\n"
            ."LOG_CHANNEL=stack\n"
            ."LOG_STACK=single,sentry_logs\n";

        file_put_contents($this->stagingDir.'/.env', $envContent);

        // Ensure required storage dirs exist
        foreach (['app', 'framework/cache', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
            $path = $this->stagingDir.'/storage/'.$dir;
            if (! is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        $this->line("  Copied {$count} files to staging.");
    }

    private function obfuscateStaging(): void
    {
        $yakpro = base_path('build/yakpro-po/vendor/pmdunggh/yakpro-po/yakpro-po.php');
        $config = base_path('build/yakpro.cnf');

        if (! file_exists($yakpro)) {
            $this->warn('yakpro-po not installed. Run: cd build/yakpro-po && composer install');

            return;
        }

        $appDir = $this->stagingDir.'/app';
        $tempOutput = $this->stagingDir.'/.obfuscated-app';

        $cmd = sprintf(
            'php %s --config-file %s -o %s %s 2>&1',
            escapeshellarg($yakpro),
            escapeshellarg($config),
            escapeshellarg($tempOutput),
            escapeshellarg($appDir)
        );

        $result = $this->runProcess($cmd);

        if ($result === 0 && is_dir($tempOutput)) {
            $this->deleteDirectory($appDir);
            rename($tempOutput, $appDir);
            $this->line('  PHP source obfuscated.');
        } else {
            $this->warn('  Obfuscation had issues. Continuing with original source.');
            if (is_dir($tempOutput)) {
                $this->deleteDirectory($tempOutput);
            }
        }
    }

    private function generateIntegrityInStaging(): void
    {
        // Only hash the critical security files — must match IntegrityChecker::CRITICAL_FILES
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
            $fullPath = $this->stagingDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (file_exists($fullPath)) {
                $hashes[$relativePath] = hash_file('sha256', $fullPath);
            }
        }

        ksort($hashes);
        $data = json_encode($hashes);
        $signature = hash_hmac('sha256', $data, config('ims.app_secret'));

        file_put_contents($this->stagingDir.'/integrity.bin', $signature."\n".$data);
        $this->line('  Integrity manifest generated ('.count($hashes).' critical files hashed).');
    }

    private function cleanStaging(): void
    {
        $filesToRemove = [
            '*.md',
            'CLAUDE.md',
            '06-task-list.md',
            '.env.example',
            'phpunit.xml',
            '.editorconfig',
            '.gitattributes',
            '.gitignore',
            'integrity.bin.example',
        ];

        $dirsToRemove = [
            'tests',
            'build',
            '.github',
            '.claude',
            'database/seeders', // Demo data seeders
        ];

        $count = 0;

        foreach ($filesToRemove as $pattern) {
            foreach (glob($this->stagingDir.'/'.$pattern) as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $count++;
                }
            }
        }

        foreach ($dirsToRemove as $dir) {
            $path = $this->stagingDir.'/'.$dir;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
                $count++;
            }
        }

        $this->line("  Removed {$count} non-production items.");
    }

    private function removeDevLicenses(): void
    {
        $file = $this->stagingDir.'/app/Services/LicenseManager.php';
        if (! file_exists($file)) {
            return;
        }

        $content = file_get_contents($file);

        // Remove the DEV_LICENSES constant and the dev key check block
        $content = preg_replace(
            '/\s*\/\*\*\s*\* Dev\/test license.*?private const DEV_LICENSES\s*=\s*\[.*?\];\s*/s',
            "\n",
            $content
        );

        // Remove the dev license activation block in activate()
        $content = preg_replace(
            '/\s*\/\/ Check for dev\/test licenses.*?return \[\'success\'.*?\'days\'\];\s*\}\s*/s',
            "\n",
            $content
        );

        file_put_contents($file, $content);
        $this->line('  Dev license keys removed.');
    }

    private function runProcess(string $command): int
    {
        $exitCode = 0;
        passthru($command, $exitCode);

        return $exitCode;
    }

    private function copyDirectory(string $source, string $dest): void
    {
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $dest.DIRECTORY_SEPARATOR.$iterator->getSubPathname();
            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }
}
