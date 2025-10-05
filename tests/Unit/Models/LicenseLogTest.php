<?php

namespace Tests\Unit\Models;

use App\Models\License;
use App\Models\LicenseLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for LicenseLog model.
 */
class LicenseLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
    }

    /**
     * Test log entry creation.
     */
    public function test_can_create_log_entry(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $log = LicenseLog::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'ip_address' => '192.168.1.1',
            'serial' => 'TEST123',
            'status' => 'success',
            'user_agent' => 'Mozilla/5.0',
            'request_data' => ['action' => 'verify'],
            'response_data' => ['message' => 'Success'],
        ]);

        $this->assertInstanceOf(LicenseLog::class, $log);
        $this->assertEquals($license->id, $log->license_id);
        $this->assertEquals('example.com', $log->domain);
        $this->assertEquals('192.168.1.1', $log->ip_address);
        $this->assertEquals('TEST123', $log->serial);
        $this->assertEquals('success', $log->status);
        $this->assertEquals('Mozilla/5.0', $log->user_agent);
        $this->assertEquals(['action' => 'verify'], $log->request_data);
        $this->assertEquals(['message' => 'Success'], $log->response_data);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'License log entry created') &&
                   $context['license_id'] === $license->id;
        });
    }

    /**
     * Test action attribute.
     */
    public function test_action_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $log = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'success',
            'request_data' => ['action' => 'verify_license'],
        ]);

        $this->assertEquals('verify_license', $log->action);
    }

    /**
     * Test message attribute.
     */
    public function test_message_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $log = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'success',
            'response_data' => ['message' => 'License verified successfully'],
        ]);

        $this->assertEquals('License verified successfully', $log->message);
    }

    /**
     * Test status badge class attribute.
     */
    public function test_status_badge_class_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $successLog = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'success',
        ]);

        $failedLog = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'failed',
        ]);

        $errorLog = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'error',
        ]);

        $this->assertEquals('badge-success', $successLog->status_badge_class);
        $this->assertEquals('badge-danger', $failedLog->status_badge_class);
        $this->assertEquals('badge-warning', $errorLog->status_badge_class);
    }

    /**
     * Test status label attribute.
     */
    public function test_status_label_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $log = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'success',
        ]);

        $this->assertEquals('Success', $log->status_label);
    }

    /**
     * Test status check methods.
     */
    public function test_status_check_methods(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $successLog = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'success',
        ]);

        $failedLog = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'failed',
        ]);

        $errorLog = LicenseLog::create([
            'license_id' => $license->id,
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
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        LicenseLog::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'ip_address' => '192.168.1.1',
            'status' => 'success',
        ]);

        LicenseLog::create([
            'license_id' => $license->id,
            'domain' => 'test.com',
            'ip_address' => '192.168.1.2',
            'status' => 'failed',
        ]);

        LicenseLog::create([
            'license_id' => $license->id,
            'domain' => 'error.com',
            'ip_address' => '192.168.1.3',
            'status' => 'error',
        ]);

        $this->assertCount(1, LicenseLog::successful()->get());
        $this->assertCount(1, LicenseLog::failed()->get());
        $this->assertCount(1, LicenseLog::error()->get());
        $this->assertCount(3, LicenseLog::forLicense($license->id)->get());
        $this->assertCount(1, LicenseLog::forDomain('example.com')->get());
        $this->assertCount(1, LicenseLog::forIp('192.168.1.1')->get());
        $this->assertCount(1, LicenseLog::byStatus('success')->get());
    }

    /**
     * Test relationships.
     */
    public function test_relationships(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $log = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'success',
        ]);

        $this->assertInstanceOf(License::class, $log->license);
        $this->assertEquals($license->id, $log->license->id);
    }

    /**
     * Test static methods.
     */
    public function test_static_methods(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        LicenseLog::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'success',
        ]);

        LicenseLog::create([
            'license_id' => $license->id,
            'domain' => 'test.com',
            'status' => 'failed',
        ]);

        $statistics = LicenseLog::getStatistics();
        $forLicense = LicenseLog::getForLicense($license->id);
        $recentLogs = LicenseLog::getRecentLogs(7);
        $suspiciousActivity = LicenseLog::getSuspiciousActivity(30);

        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('successful', $statistics);
        $this->assertArrayHasKey('failed', $statistics);
        $this->assertArrayHasKey('errors', $statistics);
        $this->assertEquals(2, $statistics['total']);
        $this->assertEquals(1, $statistics['successful']);
        $this->assertEquals(1, $statistics['failed']);

        $this->assertCount(2, $forLicense);
        $this->assertCount(2, $recentLogs);
        $this->assertCount(1, $suspiciousActivity);
    }

    /**
     * Test API analytics methods.
     */
    public function test_api_analytics_methods(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        // Create logs with different dates
        LicenseLog::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'success',
            'created_at' => now()->subDays(1),
        ]);

        LicenseLog::create([
            'license_id' => $license->id,
            'domain' => 'test.com',
            'status' => 'failed',
            'created_at' => now()->subDays(2),
        ]);

        $callsByDate = LicenseLog::getApiCallsByDate(30);
        $statusDistribution = LicenseLog::getApiStatusDistribution(30);
        $topDomains = LicenseLog::getTopDomainsByCalls(10);
        $callsByHour = LicenseLog::getApiCallsByHour();

        $this->assertCount(2, $callsByDate);
        $this->assertCount(2, $statusDistribution);
        $this->assertCount(2, $topDomains);
        $this->assertIsArray($callsByHour);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $log = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'success',
            'request_data' => ['action' => 'verify'],
            'response_data' => ['message' => 'Success'],
        ]);

        $this->assertIsInt($log->license_id);
        $this->assertIsArray($log->request_data);
        $this->assertIsArray($log->response_data);
    }
}
