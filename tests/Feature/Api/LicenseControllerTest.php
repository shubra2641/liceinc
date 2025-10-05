<?php

namespace Tests\Feature\Api;

use App\Models\License;
use App\Models\LicenseLog;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * License Controller Test.
 *
 * Comprehensive test suite for LicenseController API endpoints
 * with security, validation, and functionality testing.
 *


 * @author Sekuret Development Team
 */
class LicenseControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected Product $product;

    protected License $license;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'envato_item_id' => '12345678',
        ]);

        // Create test license
        $this->license = License::factory()->create([
            'product_id' => $this->product->id,
            'purchase_code' => 'test-purchase-code-12345',
            'license_key' => 'test-license-key-12345',
            'status' => 'active',
            'license_type' => 'regular',
        ]);
    }

    /**
     * Test license verification with valid local license.
     */
    public function test_verify_license_with_valid_local_license(): void
    {
        $response = $this->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'valid',
                'source',
                'license_type',
                'purchase_code',
                'product' => [
                    'id',
                    'name',
                    'slug',
                    'envato_item_id',
                ],
                'domain_allowed',
                'status',
                'support_expires_at',
                'license_expires_at',
                'verified_at',
            ])
            ->assertJson([
                'valid' => true,
                'source' => 'local',
                'license_type' => 'system_generated',
            ]);
    }

    /**
     * Test license verification with invalid license.
     */
    public function test_verify_license_with_invalid_license(): void
    {
        $response = $this->postJson('/api/license/verify', [
            'purchase_code' => 'invalid-purchase-code',
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'valid' => false,
                'reason' => 'license_not_found',
                'message' => 'License not found in local database or Envato Market',
            ]);
    }

    /**
     * Test license verification with validation errors.
     */
    public function test_verify_license_with_validation_errors(): void
    {
        $response = $this->postJson('/api/license/verify', [
            'purchase_code' => 'short',
            'product_slug' => 'non-existent-product',
            'domain' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    /**
     * Test license verification with invalid domain format.
     */
    public function test_verify_license_with_invalid_domain(): void
    {
        $response = $this->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'invalid-domain-format',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    /**
     * Test generate integration file endpoint.
     */
    public function test_generate_integration_file(): void
    {
        $response = $this->postJson('/api/license/generate-integration-file', [
            'product_slug' => $this->product->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'product' => [
                    'name',
                    'slug',
                ],
                'integration_file',
                'filename',
                'generated_at',
            ])
            ->assertJson([
                'success' => true,
                'filename' => 'license_integration_'.$this->product->slug.'.php',
            ]);

        // Check that integration file contains expected content
        $integrationFile = $response->json('integration_file');
        $this->assertStringContainsString('class LicenseManager', $integrationFile);
        $this->assertStringContainsString($this->product->name, $integrationFile);
        $this->assertStringContainsString($this->product->slug, $integrationFile);
    }

    /**
     * Test generate integration file with non-existent product.
     */
    public function test_generate_integration_file_with_invalid_product(): void
    {
        $response = $this->postJson('/api/license/generate-integration-file', [
            'product_slug' => 'non-existent-product',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    /**
     * Test get statistics endpoint.
     */
    public function test_get_statistics(): void
    {
        // Create some test license logs
        LicenseLog::factory()->create([
            'status' => 'success',
            'response_data' => ['source' => 'local', 'license_type' => 'system_generated'],
        ]);

        LicenseLog::factory()->create([
            'status' => 'failed',
            'response_data' => ['source' => 'envato', 'license_type' => 'envato_market'],
        ]);

        $response = $this->getJson('/api/license/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'period' => [
                    'start',
                    'end',
                ],
                'verification_counts' => [
                    'total_verifications',
                    'successful_verifications',
                    'failed_verifications',
                    'rate_limited_verifications',
                ],
                'source_distribution' => [
                    'local_verifications',
                    'envato_verifications',
                ],
                'license_types' => [
                    'system_generated',
                    'envato_market',
                ],
                'performance_metrics' => [
                    'average_response_time',
                    'cache_hit_rate',
                    'api_uptime',
                    'error_rate',
                ],
                'recent_activity',
                'generated_at',
            ]);
    }

    /**
     * Test bulk license verification.
     */
    public function test_bulk_verify_licenses(): void
    {
        $response = $this->postJson('/api/license/bulk-verify', [
            'licenses' => [
                [
                    'purchase_code' => $this->license->purchase_code,
                    'product_slug' => $this->product->slug,
                    'domain' => 'example.com',
                ],
                [
                    'purchase_code' => 'invalid-purchase-code',
                    'product_slug' => $this->product->slug,
                    'domain' => 'example2.com',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'batch_id',
                'total_licenses',
                'processed_licenses',
                'results' => [
                    '*' => [
                        'index',
                        'purchase_code',
                        'valid',
                        'source',
                        'license_type',
                        'domain_allowed',
                        'status',
                    ],
                ],
                'processed_at',
            ]);

        // Check that we have both successful and failed results
        $results = $response->json('results');
        $this->assertCount(2, $results);
        $this->assertTrue($results[0]['valid']);
        $this->assertFalse($results[1]['valid']);
    }

    /**
     * Test bulk verification with validation errors.
     */
    public function test_bulk_verify_with_validation_errors(): void
    {
        $response = $this->postJson('/api/license/bulk-verify', [
            'licenses' => [
                [
                    'purchase_code' => 'short',
                    'product_slug' => 'non-existent',
                    'domain' => '',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * Test rate limiting.
     */
    public function test_rate_limiting(): void
    {
        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 15; $i++) {
            $response = $this->postJson('/api/license/verify', [
                'purchase_code' => $this->license->purchase_code,
                'product_slug' => $this->product->slug,
                'domain' => 'example.com',
            ]);
        }

        // The 11th request should be rate limited
        $response->assertStatus(429);
    }

    /**
     * Test caching functionality.
     */
    public function test_caching_functionality(): void
    {
        // First request
        $response1 = $this->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response1->assertStatus(200);

        // Second request should be cached
        $response2 = $this->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response2->assertStatus(200);

        // Both responses should be identical
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test security headers.
     */
    public function test_security_headers(): void
    {
        $response = $this->getJson('/api/license/statistics');

        $response->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    /**
     * Test error handling.
     */
    public function test_error_handling(): void
    {
        // Test with non-existent product
        $response = $this->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => 'non-existent-product',
            'domain' => 'example.com',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test license log creation.
     */
    public function test_license_log_creation(): void
    {
        $initialCount = LicenseLog::count();

        $this->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $this->assertEquals($initialCount + 1, LicenseLog::count());

        $log = LicenseLog::latest()->first();
        $this->assertEquals($this->license->id, $log->license_id);
        $this->assertEquals('example.com', $log->domain);
        $this->assertEquals('success', $log->status);
    }

    /**
     * Test purchase code masking in logs.
     */
    public function test_purchase_code_masking_in_logs(): void
    {
        $this->postJson('/api/license/verify', [
            'purchase_code' => 'test-purchase-code-12345',
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $log = LicenseLog::latest()->first();
        $requestData = $log->request_data;

        // Check that purchase code is masked in request data
        $this->assertStringContainsString('***', $requestData['purchase_code']);
        $this->assertStringNotContainsString('test-purchase-code-12345', $requestData['purchase_code']);
    }

    /**
     * Test integration file caching.
     */
    public function test_integration_file_caching(): void
    {
        // First request
        $response1 = $this->postJson('/api/license/generate-integration-file', [
            'product_slug' => $this->product->slug,
        ]);

        $response1->assertStatus(200);

        // Second request should be cached
        $response2 = $this->postJson('/api/license/generate-integration-file', [
            'product_slug' => $this->product->slug,
        ]);

        $response2->assertStatus(200);

        // Both responses should be identical
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test statistics caching.
     */
    public function test_statistics_caching(): void
    {
        // First request
        $response1 = $this->getJson('/api/license/statistics');

        $response1->assertStatus(200);

        // Second request should be cached
        $response2 = $this->getJson('/api/license/statistics');

        $response2->assertStatus(200);

        // Both responses should be identical
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test force refresh parameter.
     */
    public function test_force_refresh_parameter(): void
    {
        // First request
        $response1 = $this->getJson('/api/license/statistics');

        $response1->assertStatus(200);

        // Second request with force refresh
        $response2 = $this->getJson('/api/license/statistics?force_refresh=1');

        $response2->assertStatus(200);

        // Responses should be different due to force refresh
        $this->assertNotEquals($response1->json('generated_at'), $response2->json('generated_at'));
    }
}
