<?php

namespace Tests\Feature\Api;

use App\Models\License;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EnhancedLicenseApiControllerTest extends TestCase
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
            'license_type' => 'regular',
            'support_days' => 365,
        ]);

        // Create test license
        $this->license = License::factory()->create([
            'product_id' => $this->product->id,
            'purchase_code' => 'TEST-PURCHASE-CODE-123',
            'license_key' => 'ABCD-1234-EFGH-5678',
            'license_type' => 'regular',
            'status' => 'active',
            'license_expires_at' => now()->addYear(),
            'support_expires_at' => now()->addYear(),
        ]);

        // Set API token
        $this->apiToken = 'test-api-token-123';
        config(['app.license_api_token' => $this->apiToken]);

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
        ])->postJson('/api/enhanced-license/verify', [
            'purchase_code' => 'TEST-PURCHASE-CODE-123',
            'product_slug' => 'test-product',
            'domain' => 'example.com',
            'client_info' => [
                'version' => '1.0.0',
                'platform' => 'wordpress',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'License verified successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'license_id',
                    'license_type',
                    'expires_at',
                    'support_expires_at',
                    'status',
                    'verification_method',
                    'performance' => [
                        'processing_time_ms',
                        'cached',
                        'timestamp',
                    ],
                ],
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
        ])->postJson('/api/enhanced-license/verify', [
            'purchase_code' => 'INVALID-PURCHASE-CODE',
            'product_slug' => 'test-product',
            'domain' => 'example.com',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'License not found',
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
        ])->postJson('/api/enhanced-license/verify', [
            'purchase_code' => 'TEST-PURCHASE-CODE-123',
            'product_slug' => 'non-existent-product',
            'domain' => 'example.com',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Product not found',
            ]);
    }

    /**
     * Test license verification without authorization.
     */
    public function test_verify_license_without_authorization()
    {
        $response = $this->postJson('/api/enhanced-license/verify', [
            'purchase_code' => 'TEST-PURCHASE-CODE-123',
            'product_slug' => 'test-product',
            'domain' => 'example.com',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
    }

    /**
     * Test license verification with invalid authorization token.
     */
    public function test_verify_license_with_invalid_authorization()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json',
        ])->postJson('/api/enhanced-license/verify', [
            'purchase_code' => 'TEST-PURCHASE-CODE-123',
            'product_slug' => 'test-product',
            'domain' => 'example.com',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized',
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
        ])->postJson('/api/enhanced-license/register', [
            'purchase_code' => 'NEW-PURCHASE-CODE-456',
            'product_slug' => 'test-product',
            'domain' => 'newdomain.com',
            'envato_data' => [
                'item' => [
                    'id' => 12345,
                ],
                'supported_until' => now()->addYear()->toDateString(),
            ],
            'customer_info' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'country' => 'US',
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
                'data' => [
                    'license_id',
                    'status',
                ],
            ]);

        // Verify license was created
        $this->assertDatabaseHas('licenses', [
            'purchase_code' => 'NEW-PURCHASE-CODE-456',
            'product_id' => $this->product->id,
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
        ])->postJson('/api/enhanced-license/register', [
            'purchase_code' => 'TEST-PURCHASE-CODE-123',
            'product_slug' => 'test-product',
            'domain' => 'example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'License already exists',
            ])
            ->assertJson([
                'data' => [
                    'status' => 'already_exists',
                ],
            ]);
    }

    /**
     * Test get license status with valid data.
     */
    public function test_get_license_status_with_valid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/enhanced-license/status', [
            'license_key' => 'ABCD-1234-EFGH-5678',
            'product_slug' => 'test-product',
            'include_domains' => true,
            'include_history' => true,
            'include_metrics' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'License status retrieved',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'license_id',
                    'type',
                    'expires_at',
                    'support_expires_at',
                    'status',
                    'is_active',
                ],
            ]);
    }

    /**
     * Test get license status with invalid license key.
     */
    public function test_get_license_status_with_invalid_license_key()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/enhanced-license/status', [
            'license_key' => 'INVALID-LICENSE-KEY',
            'product_slug' => 'test-product',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'License not found',
            ]);
    }

    /**
     * Test get statistics.
     */
    public function test_get_statistics()
    {
        // Create additional test data
        License::factory()->count(5)->create([
            'product_id' => $this->product->id,
            'status' => 'active',
        ]);

        License::factory()->count(2)->create([
            'product_id' => $this->product->id,
            'status' => 'suspended',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->getJson('/api/enhanced-license/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'period',
                'license_metrics' => [
                    'total_licenses',
                    'active_licenses',
                    'expired_licenses',
                    'suspended_licenses',
                    'licenses_by_type',
                ],
                'verification_metrics' => [
                    'total_verifications',
                    'successful_verifications',
                    'failed_verifications',
                    'verification_sources',
                ],
                'performance_metrics' => [
                    'average_response_time',
                    'cache_hit_rate',
                    'api_uptime',
                    'error_rate',
                    'throughput_per_minute',
                ],
                'security_metrics' => [
                    'rate_limited_requests',
                    'suspicious_activities',
                    'blocked_ips',
                ],
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
            'purchase_code' => 'BULK-PURCHASE-CODE-2',
            'license_key' => 'BULK-1234-EFGH-5678',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/enhanced-license/bulk-verify', [
            'licenses' => [
                [
                    'purchase_code' => 'TEST-PURCHASE-CODE-123',
                    'product_slug' => 'test-product',
                    'domain' => 'example.com',
                ],
                [
                    'purchase_code' => 'BULK-PURCHASE-CODE-2',
                    'product_slug' => 'test-product',
                    'domain' => 'bulkdomain.com',
                ],
                [
                    'purchase_code' => 'INVALID-PURCHASE-CODE',
                    'product_slug' => 'test-product',
                    'domain' => 'invaliddomain.com',
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
                'performance' => [
                    'processing_time_ms',
                    'licenses_per_second',
                ],
                'processed_at',
            ]);

        // Verify results structure
        $data = $response->json('results');
        $this->assertCount(3, $data);
        $this->assertArrayHasKey('index', $data[0]);
        $this->assertArrayHasKey('success', $data[0]);
    }

    /**
     * Test bulk verification with too many licenses.
     */
    public function test_bulk_verify_with_too_many_licenses()
    {
        $licenses = [];
        for ($i = 0; $i < 101; $i++) {
            $licenses[] = [
                'purchase_code' => "BULK-PURCHASE-CODE-{$i}",
                'product_slug' => 'test-product',
                'domain' => "domain{$i}.com",
            ];
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/enhanced-license/bulk-verify', [
            'licenses' => $licenses,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
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
        ])->postJson('/api/enhanced-license/verify', [
            'purchase_code' => 'TEST-PURCHASE-CODE-123',
            'product_slug' => 'test-product',
            'domain' => 'example.com',
        ]);

        $response1->assertStatus(200);

        // Second request should use cache
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/enhanced-license/verify', [
            'purchase_code' => 'TEST-PURCHASE-CODE-123',
            'product_slug' => 'test-product',
            'domain' => 'example.com',
        ]);

        $response2->assertStatus(200);
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test statistics caching.
     */
    public function test_statistics_caching()
    {
        // First request
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->getJson('/api/enhanced-license/statistics');

        $response1->assertStatus(200);

        // Second request should use cache
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->getJson('/api/enhanced-license/statistics');

        $response2->assertStatus(200);
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test force refresh statistics.
     */
    public function test_force_refresh_statistics()
    {
        // First request
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->getJson('/api/enhanced-license/statistics');

        $response1->assertStatus(200);

        // Force refresh request
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->getJson('/api/enhanced-license/statistics?force_refresh=1');

        $response2->assertStatus(200);

        // Both should be successful but may have different timestamps
        $this->assertTrue($response1->json('success'));
        $this->assertTrue($response2->json('success'));
    }

    /**
     * Test validation errors.
     */
    public function test_validation_errors()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/enhanced-license/verify', [
            'purchase_code' => '', // Invalid empty
            'product_slug' => 'test-product',
            'domain' => 'example.com',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
                'error_code' => 'VALIDATION_ERROR',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
                'error_code',
            ]);
    }

    /**
     * Test security validation.
     */
    public function test_security_validation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/enhanced-license/verify', [
            'purchase_code' => '<script>alert("xss")</script>',
            'product_slug' => 'test-product',
            'domain' => 'example.com',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Purchase code contains invalid characters',
                'error_code' => 'INVALID_PURCHASE_CODE_CHARACTERS',
            ]);
    }

    /**
     * Test SQL injection prevention.
     */
    public function test_sql_injection_prevention()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/enhanced-license/verify', [
            'purchase_code' => "'; DROP TABLE licenses; --",
            'product_slug' => 'test-product',
            'domain' => 'example.com',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid purchase code format',
                'error_code' => 'INVALID_PURCHASE_CODE_FORMAT',
            ]);
    }

    /**
     * Test domain registration for license.
     */
    public function test_domain_registration_for_license()
    {
        // Create license without domains
        $license = License::factory()->create([
            'product_id' => $this->product->id,
            'purchase_code' => 'DOMAIN-TEST-CODE',
            'license_key' => 'DOMAIN-TEST-KEY',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ])->postJson('/api/enhanced-license/verify', [
            'purchase_code' => 'DOMAIN-TEST-CODE',
            'product_slug' => 'test-product',
            'domain' => 'newdomain.com',
        ]);

        $response->assertStatus(200);

        // Verify domain was registered
        $this->assertDatabaseHas('license_domains', [
            'license_id' => $license->id,
            'domain' => 'newdomain.com',
            'status' => 'active',
        ]);
    }

    /**
     * Test rate limiting.
     */
    public function test_rate_limiting()
    {
        // Make multiple requests to trigger rate limiting
        for ($i = 0; $i < 20; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->apiToken,
                'Accept' => 'application/json',
            ])->postJson('/api/enhanced-license/verify', [
                'purchase_code' => 'TEST-PURCHASE-CODE-123',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
            ]);

            if ($i >= 12) { // After 12 requests, should be rate limited
                $response->assertStatus(429)
                    ->assertJson([
                        'success' => false,
                        'message' => 'Too many verification attempts. Please try again later.',
                    ]);
            }
        }
    }

    /**
     * Test error handling.
     */
    public function test_error_handling()
    {
        // Test with malformed JSON
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->post('/api/enhanced-license/verify', 'invalid json');

        $response->assertStatus(400);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
