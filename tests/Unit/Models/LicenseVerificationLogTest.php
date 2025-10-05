<?php

namespace Tests\Unit\Models;

use App\Models\LicenseVerificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for LicenseVerificationLog model.
 */
class LicenseVerificationLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
    }

    /**
     * Test verification log creation.
     */
    public function test_can_create_verification_log(): void
    {
        $log = LicenseVerificationLog::create([
            'purchase_code_hash' => 'abc123def456',
            'domain' => 'example.com',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'is_valid' => true,
            'response_message' => 'License verified successfully',
            'response_data' => ['status' => 'valid'],
            'verification_source' => 'api',
            'status' => 'success',
            'verified_at' => now(),
        ]);

        $this->assertInstanceOf(LicenseVerificationLog::class, $log);
        $this->assertEquals('abc123def456', $log->purchase_code_hash);
        $this->assertEquals('example.com', $log->domain);
        $this->assertEquals('192.168.1.1', $log->ip_address);
        $this->assertEquals('Mozilla/5.0', $log->user_agent);
        $this->assertTrue($log->is_valid);
        $this->assertEquals('License verified successfully', $log->response_message);
        $this->assertEquals(['status' => 'valid'], $log->response_data);
        $this->assertEquals('api', $log->verification_source);
        $this->assertEquals('success', $log->status);
        $this->assertNotNull($log->verified_at);
    }

    /**
     * Test failed verification logging.
     */
    public function test_failed_verification_logging(): void
    {
        $log = LicenseVerificationLog::create([
            'purchase_code_hash' => 'invalid123',
            'domain' => 'example.com',
            'ip_address' => '192.168.1.1',
            'is_valid' => false,
            'verification_source' => 'api',
            'status' => 'failed',
            'error_details' => 'Invalid purchase code',
        ]);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'License verification failed') &&
                   $context['domain'] === 'example.com' &&
                   $context['status'] === 'failed';
        });
    }

    /**
     * Test masked purchase code attribute.
     */
    public function test_masked_purchase_code_attribute(): void
    {
        $log = LicenseVerificationLog::create([
            'purchase_code_hash' => 'abcdefghijklmnop',
            'is_valid' => true,
            'verification_source' => 'api',
            'status' => 'success',
        ]);

        $this->assertEquals('abcd****mnop', $log->masked_purchase_code);

        $shortLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'short',
            'is_valid' => true,
            'verification_source' => 'api',
            'status' => 'success',
        ]);

        $this->assertEquals('****', $shortLog->masked_purchase_code);
    }

    /**
     * Test status badge class attribute.
     */
    public function test_status_badge_class_attribute(): void
    {
        $successLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test123',
            'is_valid' => true,
            'verification_source' => 'api',
            'status' => 'success',
        ]);

        $failedLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test456',
            'is_valid' => false,
            'verification_source' => 'api',
            'status' => 'failed',
        ]);

        $errorLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test789',
            'is_valid' => false,
            'verification_source' => 'api',
            'status' => 'error',
        ]);

        $this->assertEquals('badge-success', $successLog->status_badge_class);
        $this->assertEquals('badge-danger', $failedLog->status_badge_class);
        $this->assertEquals('badge-warning', $errorLog->status_badge_class);
    }

    /**
     * Test source badge class attribute.
     */
    public function test_source_badge_class_attribute(): void
    {
        $installLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test123',
            'is_valid' => true,
            'verification_source' => 'install',
            'status' => 'success',
        ]);

        $apiLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test456',
            'is_valid' => true,
            'verification_source' => 'api',
            'status' => 'success',
        ]);

        $adminLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test789',
            'is_valid' => true,
            'verification_source' => 'admin',
            'status' => 'success',
        ]);

        $this->assertEquals('badge-primary', $installLog->source_badge_class);
        $this->assertEquals('badge-info', $apiLog->source_badge_class);
        $this->assertEquals('badge-warning', $adminLog->source_badge_class);
    }

    /**
     * Test status label attribute.
     */
    public function test_status_label_attribute(): void
    {
        $log = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test123',
            'is_valid' => true,
            'verification_source' => 'api',
            'status' => 'success',
        ]);

        $this->assertEquals('Success', $log->status_label);
    }

    /**
     * Test source label attribute.
     */
    public function test_source_label_attribute(): void
    {
        $installLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test123',
            'is_valid' => true,
            'verification_source' => 'install',
            'status' => 'success',
        ]);

        $apiLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test456',
            'is_valid' => true,
            'verification_source' => 'api',
            'status' => 'success',
        ]);

        $adminLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test789',
            'is_valid' => true,
            'verification_source' => 'admin',
            'status' => 'success',
        ]);

        $this->assertEquals('Installation', $installLog->source_label);
        $this->assertEquals('API', $apiLog->source_label);
        $this->assertEquals('Admin Panel', $adminLog->source_label);
    }

    /**
     * Test status check methods.
     */
    public function test_status_check_methods(): void
    {
        $successLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test123',
            'is_valid' => true,
            'verification_source' => 'api',
            'status' => 'success',
        ]);

        $failedLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test456',
            'is_valid' => false,
            'verification_source' => 'api',
            'status' => 'failed',
        ]);

        $errorLog = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test789',
            'is_valid' => false,
            'verification_source' => 'api',
            'status' => 'error',
        ]);

        $this->assertTrue($successLog->isSuccessful());
        $this->assertFalse($successLog->isFailed());
        $this->assertFalse($successLog->isError());

        $this->assertFalse($failedLog->isSuccessful());
        $this->assertTrue($failedLog->isFailed());
        $this->assertFalse($failedLog->isError());

        $this->assertFalse($errorLog->isSuccessful());
        $this->assertFalse($errorLog->isFailed());
        $this->assertTrue($errorLog->isError());
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        LicenseVerificationLog::create([
            'purchase_code_hash' => 'test123',
            'domain' => 'example.com',
            'ip_address' => '192.168.1.1',
            'is_valid' => true,
            'verification_source' => 'api',
            'status' => 'success',
        ]);

        LicenseVerificationLog::create([
            'purchase_code_hash' => 'test456',
            'domain' => 'test.com',
            'ip_address' => '192.168.1.2',
            'is_valid' => false,
            'verification_source' => 'install',
            'status' => 'failed',
        ]);

        LicenseVerificationLog::create([
            'purchase_code_hash' => 'test789',
            'domain' => 'error.com',
            'ip_address' => '192.168.1.3',
            'is_valid' => false,
            'verification_source' => 'admin',
            'status' => 'error',
        ]);

        $this->assertCount(1, LicenseVerificationLog::successful()->get());
        $this->assertCount(1, LicenseVerificationLog::failed()->get());
        $this->assertCount(1, LicenseVerificationLog::error()->get());
        $this->assertCount(1, LicenseVerificationLog::forDomain('example.com')->get());
        $this->assertCount(1, LicenseVerificationLog::forIp('192.168.1.1')->get());
        $this->assertCount(1, LicenseVerificationLog::fromSource('api')->get());
        $this->assertCount(1, LicenseVerificationLog::byStatus('success')->get());
    }

    /**
     * Test static methods.
     */
    public function test_static_methods(): void
    {
        LicenseVerificationLog::create([
            'purchase_code_hash' => 'test123',
            'domain' => 'example.com',
            'ip_address' => '192.168.1.1',
            'is_valid' => true,
            'verification_source' => 'api',
            'status' => 'success',
        ]);

        LicenseVerificationLog::create([
            'purchase_code_hash' => 'test456',
            'domain' => 'test.com',
            'ip_address' => '192.168.1.2',
            'is_valid' => false,
            'verification_source' => 'install',
            'status' => 'failed',
        ]);

        $statistics = LicenseVerificationLog::getStatistics();
        $forDomain = LicenseVerificationLog::getForDomain('example.com');
        $forIp = LicenseVerificationLog::getForIp('192.168.1.1');
        $recentAttempts = LicenseVerificationLog::getRecentAttempts(24);
        $failedAttempts = LicenseVerificationLog::getFailedAttempts(7);
        $suspiciousPatterns = LicenseVerificationLog::getSuspiciousPatterns(30);

        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('successful', $statistics);
        $this->assertArrayHasKey('failed', $statistics);
        $this->assertArrayHasKey('errors', $statistics);
        $this->assertArrayHasKey('by_status', $statistics);
        $this->assertArrayHasKey('by_source', $statistics);
        $this->assertEquals(2, $statistics['total']);
        $this->assertEquals(1, $statistics['successful']);
        $this->assertEquals(1, $statistics['failed']);

        $this->assertCount(1, $forDomain);
        $this->assertCount(1, $forIp);
        $this->assertCount(2, $recentAttempts);
        $this->assertCount(1, $failedAttempts);
        $this->assertArrayHasKey('suspicious_ips', $suspiciousPatterns);
        $this->assertArrayHasKey('suspicious_domains', $suspiciousPatterns);
    }

    /**
     * Test suspicious patterns detection.
     */
    public function test_suspicious_patterns_detection(): void
    {
        // Create multiple failed attempts from same IP
        for ($i = 0; $i < 15; $i++) {
            LicenseVerificationLog::create([
                'purchase_code_hash' => 'test'.$i,
                'domain' => 'example.com',
                'ip_address' => '192.168.1.100',
                'is_valid' => false,
                'verification_source' => 'api',
                'status' => 'failed',
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        // Create multiple failed attempts from same domain
        for ($i = 0; $i < 8; $i++) {
            LicenseVerificationLog::create([
                'purchase_code_hash' => 'test'.$i,
                'domain' => 'suspicious.com',
                'ip_address' => '192.168.1.'.$i,
                'is_valid' => false,
                'verification_source' => 'api',
                'status' => 'failed',
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $suspiciousPatterns = LicenseVerificationLog::getSuspiciousPatterns(30);

        $this->assertCount(1, $suspiciousPatterns['suspicious_ips']);
        $this->assertCount(1, $suspiciousPatterns['suspicious_domains']);
        $this->assertEquals('192.168.1.100', $suspiciousPatterns['suspicious_ips']->first()->ip_address);
        $this->assertEquals('suspicious.com', $suspiciousPatterns['suspicious_domains']->first()->domain);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $log = LicenseVerificationLog::create([
            'purchase_code_hash' => 'test123',
            'is_valid' => '1',
            'verification_source' => 'api',
            'status' => 'success',
            'response_data' => ['status' => 'valid'],
            'verified_at' => now(),
        ]);

        $this->assertIsBool($log->is_valid);
        $this->assertIsArray($log->response_data);
        $this->assertInstanceOf(\Carbon\Carbon::class, $log->verified_at);
    }
}
