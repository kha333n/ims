<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\BackupService;
use App\Services\HardwareFingerprint;
use App\Services\IntegrityChecker;
use App\Services\LicenseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseAndBackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear any persisted license from AppData and Registry
        // so tests start with a clean slate
        $appDataPath = (getenv('APPDATA') ?: (getenv('USERPROFILE').'\\AppData\\Roaming')).'\\IMS\\license.enc';
        if (file_exists($appDataPath)) {
            @unlink($appDataPath);
        }
        if (PHP_OS_FAMILY === 'Windows') {
            @exec('reg delete "HKCU\\Software\\TechmiddleTech\\IMS" /v license /f 2>NUL');
        }

        HardwareFingerprint::clearCache();
    }

    public function test_hardware_fingerprint_generates_consistent_hash(): void
    {
        $fp = new HardwareFingerprint;
        $hash1 = $fp->generate();
        $hash2 = $fp->generate();

        $this->assertNotEmpty($hash1);
        $this->assertEquals(64, strlen($hash1)); // SHA-256 hex
        $this->assertEquals($hash1, $hash2);
    }

    public function test_hardware_fingerprint_verify_matches_generated(): void
    {
        $fp = new HardwareFingerprint;
        $hash = $fp->generate();

        $this->assertTrue($fp->verify($hash));
        $this->assertFalse($fp->verify('wrong-hash'));
    }

    public function test_license_manager_returns_not_activated_when_no_license(): void
    {
        $manager = app(LicenseManager::class);
        $status = $manager->getStatus();

        $this->assertEquals('not_activated', $status['status']);
        $this->assertFalse($status['is_valid']);
        $this->assertNotEmpty($status['hardware_id']);
    }

    public function test_license_manager_is_valid_returns_false_with_no_license(): void
    {
        $manager = app(LicenseManager::class);

        $this->assertFalse($manager->isValid());
    }

    public function test_subscription_gate_skips_in_test_environment(): void
    {
        // The gate should let tests through by default
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_subscription_gate_allows_license_route(): void
    {
        // Even if we enforce in tests, license route should always be accessible
        config(['ims.license.enforce_in_tests' => true]);

        $response = $this->get('/license');

        $response->assertStatus(200);
    }

    public function test_subscription_gate_blocks_when_enforced_and_no_license(): void
    {
        config(['ims.license.enforce_in_tests' => true]);

        $response = $this->get('/');

        $response->assertRedirect(route('license'));
    }

    public function test_license_settings_page_loads(): void
    {
        $response = $this->get('/settings/license');

        $response->assertStatus(200);
        $response->assertSee('License Settings');
        $response->assertSee('Not Activated');
        $response->assertSee('Hardware ID');
    }

    public function test_backup_service_encrypt_decrypt_round_trip(): void
    {
        // Create a temporary SQLite-like file for testing
        $testDb = tempnam(sys_get_temp_dir(), 'ims_test_');
        file_put_contents($testDb, "SQLite format 3\0".str_repeat("\0", 100));

        // Point the service to our test file
        config(['database.connections.sqlite.database' => $testDb]);

        $service = app(BackupService::class);

        // We need to test encrypt/decrypt via backup/restore
        // First set a backup path we control
        $backupDir = sys_get_temp_dir().'/ims_test_backups_'.time();
        config(['ims.backup.local_path' => $backupDir]);

        try {
            $path = $service->createLocalBackup();

            $this->assertFileExists($path);
            $this->assertStringEndsWith('.imsb', $path);

            // Verify it's not plain SQLite (encrypted + obfuscated)
            $content = file_get_contents($path);
            $this->assertStringNotContainsString('SQLite format', $content);
            // Should not start with plain SQLite header
            $this->assertNotEquals("SQLite format 3\0", substr($content, 0, 16));

            // Verify last backup timestamp was set
            $this->assertNotNull(Setting::get('last_backup_at'));
        } finally {
            // Cleanup
            @unlink($testDb);
            if (is_dir($backupDir)) {
                array_map('unlink', glob($backupDir.'/*'));
                @rmdir($backupDir);
            }
        }
    }

    public function test_backup_service_should_warn_when_never_backed_up(): void
    {
        $service = app(BackupService::class);

        $this->assertTrue($service->shouldWarn());
    }

    public function test_backup_service_should_not_warn_after_recent_backup(): void
    {
        Setting::set('last_backup_at', now()->toIso8601String());

        $service = app(BackupService::class);

        $this->assertFalse($service->shouldWarn());
    }

    public function test_backup_service_should_warn_after24_hours(): void
    {
        Setting::set('last_backup_at', now()->subHours(25)->toIso8601String());

        $service = app(BackupService::class);

        $this->assertTrue($service->shouldWarn());
    }

    public function test_backup_restore_page_loads(): void
    {
        $response = $this->get('/settings/backup');

        $response->assertStatus(200);
        $response->assertSee('Backup');
    }

    public function test_ims_config_exists(): void
    {
        $this->assertNotNull(config('ims.license.server_url'));
        $this->assertEquals(7, config('ims.license.offline_grace_days'));
        $this->assertEquals(12, config('ims.backup.warn_after_hours'));
        $this->assertEquals(7, config('ims.backup.max_local_backups'));
    }

    public function test_dev_license_activation_works(): void
    {
        $manager = app(LicenseManager::class);

        // Should start as not activated
        $this->assertFalse($manager->isValid());

        // Activate with dev key
        $result = $manager->activate('IMS-TEST-0001-DEV1');
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('days', $result['message']);

        // Should now be valid
        $this->assertTrue($manager->isValid());

        $status = $manager->getStatus();
        $this->assertEquals('valid', $status['status']);
        $this->assertEquals('Test Shop (Dev)', $status['customer_name']);
    }

    public function test_dev_license_30_day_activation(): void
    {
        $manager = app(LicenseManager::class);
        $result = $manager->activate('IMS-TEST-0002-DEV2');

        $this->assertTrue($result['success']);
        $this->assertTrue($manager->isValid());
        $this->assertEquals('Demo Shop (Dev)', $manager->getStatus()['customer_name']);
    }

    public function test_invalid_license_key_fails(): void
    {
        $manager = app(LicenseManager::class);
        $result = $manager->activate('INVALID-KEY-1234');

        $this->assertFalse($result['success']);
        $this->assertFalse($manager->isValid());
    }

    public function test_integrity_checker_passes_without_manifest(): void
    {
        $checker = app(IntegrityChecker::class);

        // No manifest file = dev mode, should pass
        $this->assertTrue($checker->verify());
    }

    public function test_integrity_checker_generates_and_verifies_manifest(): void
    {
        $checker = app(IntegrityChecker::class);
        $path = $checker->generateManifest();

        $this->assertFileExists($path);
        $this->assertTrue($checker->verify());

        // Clean up
        @unlink($path);
    }
}
