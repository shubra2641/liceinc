<?php

namespace Tests\Feature\Console;

use App\Models\License;
use App\Models\LicenseLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Test suite for SecurityAuditCommand.
 */
class SecurityAuditCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test environment
        Config::set('app.debug', false);
        Config::set('app.key', 'base64:test-key-for-testing-purposes');
        Config::set('app.env', 'testing');
    }

    /**
     * Test command runs successfully.
     */
    public function test_command_runs_successfully(): void
    {
        $exitCode = Artisan::call('security:audit');

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Security audit completed', Artisan::output());
    }

    /**
     * Test command generates report when requested.
     */
    public function test_command_generates_report(): void
    {
        $exitCode = Artisan::call('security:audit', ['--report' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Security report saved to', Artisan::output());

        // Check if report file was created
        $reportFiles = File::glob(storage_path('logs/security-audit-*.json'));
        $this->assertNotEmpty($reportFiles);
    }

    /**
     * Test command detects debug mode in production.
     */
    public function test_command_detects_debug_mode(): void
    {
        Config::set('app.debug', true);
        Config::set('app.env', 'production');

        $exitCode = Artisan::call('security:audit');

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Application is running in debug mode', Artisan::output());
    }

    /**
     * Test command detects missing encryption key.
     */
    public function test_command_detects_missing_encryption_key(): void
    {
        Config::set('app.key', '');

        $exitCode = Artisan::call('security:audit');

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Application encryption key is not set', Artisan::output());
    }

    /**
     * Test command detects database security issues.
     */
    public function test_command_detects_database_security_issues(): void
    {
        // Create user with default password
        User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $exitCode = Artisan::call('security:audit');

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('users with default passwords', Artisan::output());
    }

    /**
     * Test command detects orphaned licenses.
     */
    public function test_command_detects_orphaned_licenses(): void
    {
        License::factory()->create(['user_id' => null]);

        $exitCode = Artisan::call('security:audit');

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('orphaned licenses', Artisan::output());
    }

    /**
     * Test command detects expired active licenses.
     */
    public function test_command_detects_expired_active_licenses(): void
    {
        License::factory()->create([
            'status' => 'active',
            'license_expires_at' => now()->subDays(1),
        ]);

        $exitCode = Artisan::call('security:audit');

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('expired but active licenses', Artisan::output());
    }

    /**
     * Test command detects duplicate license keys.
     */
    public function test_command_detects_duplicate_license_keys(): void
    {
        $duplicateKey = 'TEST-LICENSE-KEY-123';

        License::factory()->create(['license_key' => $duplicateKey]);
        License::factory()->create(['license_key' => $duplicateKey]);

        $exitCode = Artisan::call('security:audit');

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('duplicate license keys', Artisan::output());
    }

    /**
     * Test command detects suspicious license activity.
     */
    public function test_command_detects_suspicious_license_activity(): void
    {
        $suspiciousIp = '192.168.1.100';

        // Create multiple license logs from same IP
        for ($i = 0; $i < 101; $i++) {
            LicenseLog::factory()->create([
                'ip_address' => $suspiciousIp,
                'action' => 'verification',
                'created_at' => now()->subDays(rand(1, 7)),
            ]);
        }

        $exitCode = Artisan::call('security:audit');

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('high verification activity', Artisan::output());
    }

    /**
     * Test command handles file permission checks.
     */
    public function test_command_handles_file_permission_checks(): void
    {
        $exitCode = Artisan::call('security:audit');

        $this->assertEquals(0, $exitCode);
        // File permission checks should run without errors
        $this->assertStringContainsString('Checking file permissions', Artisan::output());
    }

    /**
     * Test command summary display.
     */
    public function test_command_displays_summary(): void
    {
        $exitCode = Artisan::call('security:audit');

        $this->assertEquals(0, $exitCode);
        $output = Artisan::output();

        $this->assertStringContainsString('Security audit completed', $output);
        $this->assertStringContainsString('Severity', $output);
        $this->assertStringContainsString('Count', $output);
    }

    /**
     * Test command with fix option.
     */
    public function test_command_with_fix_option(): void
    {
        $exitCode = Artisan::call('security:audit', ['--fix' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Attempting to fix security issues', Artisan::output());
    }

    /**
     * Test command with email option.
     */
    public function test_command_with_email_option(): void
    {
        $exitCode = Artisan::call('security:audit', ['--email' => 'test@example.com']);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Sending security report to: test@example.com', Artisan::output());
    }
}
