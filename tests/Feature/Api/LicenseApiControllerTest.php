<?php

namespace Tests\Feature\Api;

use App\Models\License;
use App\Models\LicenseLog;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LicenseApiControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $product;

    protected $license;

    protected $apiToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'envato_item_id' => 12345,
            'license_type' => 'single',
            'support_days' => 365,
        ]);

        // Create test license
        $this->license = License::factory()->create([
            'product_id' => $this->product->id,
            'purchase_code' => 'test-purchase-code-123',
            'license_key' => 'TEST-LICENSE-KEY-123',
            'license_type' => 'single',
            'max_domains' => 1,
            'status' => 'active',
            'support_expires_at' => now()->addDays(365),
            'license_expires_at' => null,
        ]);

        // Set API token
        $this->apiToken = 'test-api-token-123';
        Setting::create([
            'key' => 'license_api_token',
            'value' => $this->apiToken,
        ]);

        // Clear cache
        Cache::flush();
    }

    /**
     * Test license verification with valid data.
     */
    public function test_verify_license_with_valid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'message' => 'License verified successfully',
            ])
            ->assertJsonStructure([
                'valid',
                'message',
                'data' => [
                    'license_id',
                    'license_type',
                    'max_domains',
                    'current_domains',
                    'remaining_domains',
                    'expires_at',
                    'support_expires_at',
                    'status',
                    'verification_method',
                    'envato_valid',
                    'database_valid',
                ],
            ]);
    }

    /**
     * Test license verification without authorization.
     */
    public function test_verify_license_without_authorization()
    {
        $response = $this->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'valid' => false,
                'message' => 'Unauthorized',
                'error_code' => 'UNAUTHORIZED',
            ]);
    }

    /**
     * Test license verification with invalid product.
     */
    public function test_verify_license_with_invalid_product()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => 'non-existent-product',
            'domain' => 'example.com',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'valid' => false,
                'message' => 'Product not found',
                'error_code' => 'PRODUCT_NOT_FOUND',
            ]);
    }

    /**
     * Test license verification with invalid purchase code.
     */
    public function test_verify_license_with_invalid_purchase_code()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/license/verify', [
            'purchase_code' => 'invalid-purchase-code',
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'valid' => false,
                'message' => 'License not found',
                'error_code' => 'LICENSE_NOT_FOUND',
            ]);
    }

    /**
     * Test license verification with expired license.
     */
    public function test_verify_license_with_expired_license()
    {
        // Create expired license
        $expiredLicense = License::factory()->create([
            'product_id' => $this->product->id,
            'purchase_code' => 'expired-purchase-code',
            'license_type' => 'single',
            'max_domains' => 1,
            'status' => 'active',
            'license_expires_at' => now()->subDays(1), // Expired yesterday
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/license/verify', [
            'purchase_code' => $expiredLicense->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'valid' => false,
                'message' => 'License has expired',
                'error_code' => 'LICENSE_EXPIRED',
            ]);
    }

    /**
     * Test license verification with suspended license.
     */
    public function test_verify_license_with_suspended_license()
    {
        // Create suspended license
        $suspendedLicense = License::factory()->create([
            'product_id' => $this->product->id,
            'purchase_code' => 'suspended-purchase-code',
            'license_type' => 'single',
            'max_domains' => 1,
            'status' => 'suspended',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/license/verify', [
            'purchase_code' => $suspendedLicense->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'valid' => false,
                'message' => 'License is suspended',
                'error_code' => 'LICENSE_SUSPENDED',
            ]);
    }

    /**
     * Test license registration with valid data.
     */
    public function test_register_license_with_valid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/license/register', [
            'purchase_code' => 'new-purchase-code-123',
            'product_slug' => $this->product->slug,
            'domain' => 'newdomain.com',
            'envato_data' => [
                'item' => [
                    'id' => $this->product->envato_item_id,
                ],
                'supported_until' => now()->addDays(365)->toDateString(),
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'License registered successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'license_id',
            ]);

        // Verify license was created
        $this->assertDatabaseHas('licenses', [
            'purchase_code' => 'new-purchase-code-123',
            'product_id' => $this->product->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test license registration without authorization.
     */
    public function test_register_license_without_authorization()
    {
        $response = $this->postJson('/api/license/register', [
            'purchase_code' => 'new-purchase-code-123',
            'product_slug' => $this->product->slug,
            'domain' => 'newdomain.com',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
    }

    /**
     * Test license registration with existing license.
     */
    public function test_register_license_with_existing_license()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/license/register', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'License already exists',
            ]);
    }

    /**
     * Test license status check with valid data.
     */
    public function test_get_license_status_with_valid_data()
    {
        $response = $this->getJson('/api/license/status?'.http_build_query([
            'license_key' => $this->license->license_key,
            'product_slug' => $this->product->slug,
        ]));

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
            ])
            ->assertJsonStructure([
                'valid',
                'license' => [
                    'id',
                    'type',
                    'expires_at',
                    'support_expires_at',
                    'status',
                ],
                'product' => [
                    'name',
                    'version',
                ],
            ]);
    }

    /**
     * Test license status check with invalid license key.
     */
    public function test_get_license_status_with_invalid_license_key()
    {
        $response = $this->getJson('/api/license/status?'.http_build_query([
            'license_key' => 'invalid-license-key',
            'product_slug' => $this->product->slug,
        ]));

        $response->assertStatus(404)
            ->assertJson([
                'valid' => false,
                'message' => 'License not found',
            ]);
    }

    /**
     * Test license status check with invalid product.
     */
    public function test_get_license_status_with_invalid_product()
    {
        $response = $this->getJson('/api/license/status?'.http_build_query([
            'license_key' => $this->license->license_key,
            'product_slug' => 'non-existent-product',
        ]));

        $response->assertStatus(404)
            ->assertJson([
                'valid' => false,
                'message' => 'Product not found',
            ]);
    }

    /**
     * Test get statistics endpoint.
     */
    public function test_get_statistics()
    {
        // Create some license logs
        LicenseLog::factory()->count(5)->create([
            'status' => 'success',
        ]);

        LicenseLog::factory()->count(2)->create([
            'status' => 'failed',
        ]);

        $response = $this->getJson('/api/license/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'period',
                'verification_counts',
                'source_distribution',
                'license_types',
                'performance_metrics',
                'recent_activity',
                'generated_at',
            ]);
    }

    /**
     * Test bulk license verification.
     */
    public function test_bulk_verify_licenses()
    {
        // Create additional licenses for bulk testing
        $license2 = License::factory()->create([
            'product_id' => $this->product->id,
            'purchase_code' => 'bulk-purchase-code-2',
            'license_type' => 'single',
            'max_domains' => 1,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/license/bulk-verify', [
            'licenses' => [
                [
                    'purchase_code' => $this->license->purchase_code,
                    'product_slug' => $this->product->slug,
                    'domain' => 'example1.com',
                ],
                [
                    'purchase_code' => $license2->purchase_code,
                    'product_slug' => $this->product->slug,
                    'domain' => 'example2.com',
                ],
                [
                    'purchase_code' => 'invalid-purchase-code',
                    'product_slug' => $this->product->slug,
                    'domain' => 'example3.com',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'batch_id',
                'total_licenses',
                'processed_licenses',
                'results',
                'processed_at',
            ]);

        $responseData = $response->json();
        $this->assertEquals(3, $responseData['total_licenses']);
        $this->assertEquals(3, $responseData['processed_licenses']);
        $this->assertCount(3, $responseData['results']);
    }

    /**
     * Test bulk verification with invalid data.
     */
    public function test_bulk_verify_with_invalid_data()
    {
        $response = $this->postJson('/api/license/bulk-verify', [
            'licenses' => [
                [
                    'purchase_code' => '', // Invalid empty purchase code
                    'product_slug' => $this->product->slug,
                    'domain' => 'example.com',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * Test caching functionality.
     */
    public function test_license_verification_caching()
    {
        // First request
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response1->assertStatus(200);

        // Second request should use cache
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/license/verify', [
            'purchase_code' => $this->license->purchase_code,
            'product_slug' => $this->product->slug,
            'domain' => 'example.com',
        ]);

        $response2->assertStatus(200);
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test rate limiting.
     */
    public function test_rate_limiting()
    {
        // Make multiple requests to trigger rate limiting
        for ($i = 0; $i < 15; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->apiToken,
                'Accept' => 'application/json',
            ])->postJson('/api/license/verify', [
                'purchase_code' => $this->license->purchase_code,
                'product_slug' => $this->product->slug,
                'domain' => 'example.com',
            ]);

            if ($i >= 10) { // After 10 requests, should be rate limited
                $response->assertStatus(429)
                    ->assertJson([
                        'success' => false,
                        'message' => 'Too many requests. Please try again later.',
                    ]);
            }
        }
    }

    /**
     * Test validation errors.
     */
    public function test_validation_errors()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/license/verify', [
            'purchase_code' => '', // Invalid empty
            'product_slug' => 'invalid@slug', // Invalid characters
            'domain' => 'invalid-domain', // Invalid format
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'message' => 'Validation failed',
                'error_code' => 'VALIDATION_ERROR',
            ])
            ->assertJsonStructure([
                'valid',
                'message',
                'errors',
                'error_code',
            ]);
    }

    /**
     * Test security event logging.
     */
    public function test_security_event_logging()
    {
        Log::shouldReceive('error')
            ->once()
            ->with('License API security event', \Mockery::type('array'));

        // Trigger rate limiting to test security logging
        for ($i = 0; $i < 15; $i++) {
            $this->withHeaders([
                'Authorization' => 'Bearer '.$this->apiToken,
                'Accept' => 'application/json',
            ])->postJson('/api/license/verify', [
                'purchase_code' => $this->license->purchase_code,
                'product_slug' => $this->product->slug,
                'domain' => 'example.com',
            ]);
        }
    }

    /**
     * Test error handling.
     */
    public function test_error_handling()
    {
        // Test with malformed JSON
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('/api/license/verify', 'invalid json');

        $response->assertStatus(400);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
