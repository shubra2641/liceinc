<?php

namespace Tests\Feature\Api;

use App\Models\License;
use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * License Server Controller Test.
 *
 * Comprehensive test suite for LicenseServerController API endpoints
 * with security, validation, and functionality testing.
 *


 * @author Sekuret Development Team
 */
class LicenseServerControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected Product $product;

    protected License $license;

    protected ProductUpdate $productUpdate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'current_version' => '1.0.0',
        ]);

        // Create test license
        $this->license = License::factory()->create([
            'product_id' => $this->product->id,
            'license_key' => 'test-license-key-12345',
            'purchase_code' => 'test-purchase-code-12345',
            'status' => 'active',
            'license_type' => 'regular',
            'max_domains' => 1,
        ]);

        // Create test product update
        $this->productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
            'version' => '1.1.0',
            'title' => 'Test Update',
            'description' => 'Test update description',
            'is_active' => true,
            'is_major' => false,
            'is_required' => false,
        ]);
    }

    /**
     * Test check updates endpoint with valid data.
     */
    public function test_check_updates_with_valid_data(): void
    {
        $response = $this->postJson('/api/license/check-updates', [
            'license_key' => $this->license->license_key,
            'current_version' => '1.0.0',
            'domain' => 'example.com',
            'product_slug' => $this->product->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_version',
                    'latest_version',
                    'is_update_available',
                    'product' => [
                        'name',
                        'slug',
                    ],
                    'update_info',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'current_version' => '1.0.0',
                    'latest_version' => '1.1.0',
                    'is_update_available' => true,
                ],
            ]);
    }

    /**
     * Test check updates endpoint with invalid license.
     */
    public function test_check_updates_with_invalid_license(): void
    {
        $response = $this->postJson('/api/license/check-updates', [
            'license_key' => 'invalid-license-key',
            'current_version' => '1.0.0',
            'domain' => 'example.com',
            'product_slug' => $this->product->slug,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid or expired license',
                'error_code' => 'INVALID_LICENSE',
            ]);
    }

    /**
     * Test check updates endpoint with validation errors.
     */
    public function test_check_updates_with_validation_errors(): void
    {
        $response = $this->postJson('/api/license/check-updates', [
            'license_key' => 'short',
            'current_version' => '',
            'product_slug' => 'non-existent-product',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * Test get version history endpoint.
     */
    public function test_get_version_history(): void
    {
        $response = $this->postJson('/api/license/version-history', [
            'license_key' => $this->license->license_key,
            'domain' => 'example.com',
            'product_slug' => $this->product->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'product' => [
                        'name',
                        'slug',
                        'current_version',
                    ],
                    'versions' => [
                        '*' => [
                            'version',
                            'title',
                            'description',
                            'changelog',
                            'is_major',
                            'is_required',
                            'released_at',
                            'file_size',
                            'download_url',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test download update endpoint.
     */
    public function test_download_update(): void
    {
        $response = $this->getJson('/api/license/download-update/'.$this->license->license_key.'/1.1.0?product_slug='.$this->product->slug);

        // This will return 404 because we don't have actual files in test
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Update file not available',
                'error_code' => 'FILE_NOT_AVAILABLE',
            ]);
    }

    /**
     * Test get latest version endpoint.
     */
    public function test_get_latest_version(): void
    {
        $response = $this->postJson('/api/license/latest-version', [
            'license_key' => $this->license->license_key,
            'domain' => 'example.com',
            'product_slug' => $this->product->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'product' => [
                        'name',
                        'slug',
                    ],
                    'version',
                    'title',
                    'description',
                    'changelog',
                    'is_major',
                    'is_required',
                    'released_at',
                    'file_size',
                    'download_url',
                ],
            ]);
    }

    /**
     * Test get update info endpoint.
     */
    public function test_get_update_info(): void
    {
        $response = $this->postJson('/api/license/update-info', [
            'product_slug' => $this->product->slug,
            'current_version' => '1.0.0',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'is_update_available',
                    'current_version',
                    'next_version',
                    'message',
                    'update_info',
                ],
            ]);
    }

    /**
     * Test get products endpoint.
     */
    public function test_get_products(): void
    {
        $response = $this->getJson('/api/license/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'products' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'description',
                            'version',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test get license statistics endpoint.
     */
    public function test_get_license_statistics(): void
    {
        $response = $this->postJson('/api/license/statistics', [
            'license_key' => $this->license->license_key,
            'product_slug' => $this->product->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'license_info' => [
                    'license_id',
                    'license_type',
                    'status',
                    'created_at',
                    'expires_at',
                    'max_domains',
                    'active_domains_count',
                ],
                'product_info' => [
                    'name',
                    'slug',
                    'current_version',
                ],
                'update_statistics' => [
                    'total_updates',
                    'available_updates',
                    'latest_version',
                ],
                'domain_statistics' => [
                    'registered_domains',
                    'active_domains',
                    'recent_domains',
                ],
                'usage_statistics' => [
                    'last_verification',
                    'verification_count',
                    'download_count',
                ],
                'generated_at',
            ]);
    }

    /**
     * Test get system health endpoint.
     */
    public function test_get_system_health(): void
    {
        $response = $this->getJson('/api/license/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status',
                'timestamp',
                'checks' => [
                    'database' => [
                        'status',
                        'message',
                    ],
                    'storage' => [
                        'status',
                        'message',
                    ],
                    'cache' => [
                        'status',
                        'message',
                    ],
                    'license_system' => [
                        'status',
                        'message',
                    ],
                ],
                'system_info' => [
                    'php_version',
                    'laravel_version',
                    'server_time',
                    'timezone',
                    'environment',
                ],
            ]);
    }

    /**
     * Test bulk check updates endpoint.
     */
    public function test_bulk_check_updates(): void
    {
        $response = $this->postJson('/api/license/bulk-check-updates', [
            'licenses' => [
                [
                    'license_key' => $this->license->license_key,
                    'current_version' => '1.0.0',
                    'domain' => 'example.com',
                    'product_slug' => $this->product->slug,
                ],
                [
                    'license_key' => 'invalid-license-key',
                    'current_version' => '1.0.0',
                    'domain' => 'example2.com',
                    'product_slug' => $this->product->slug,
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
                        'license_key',
                        'success',
                        'current_version',
                        'latest_version',
                        'is_update_available',
                        'product' => [
                            'name',
                            'slug',
                        ],
                        'update_info',
                    ],
                ],
                'processed_at',
            ]);

        // Check that we have both successful and failed results
        $results = $response->json('results');
        $this->assertCount(2, $results);
        $this->assertTrue($results[0]['success']);
        $this->assertFalse($results[1]['success']);
    }

    /**
     * Test bulk check updates with validation errors.
     */
    public function test_bulk_check_updates_validation_errors(): void
    {
        $response = $this->postJson('/api/license/bulk-check-updates', [
            'licenses' => [
                [
                    'license_key' => 'short',
                    'current_version' => '',
                    'product_slug' => 'non-existent',
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
            $response = $this->postJson('/api/license/check-updates', [
                'license_key' => $this->license->license_key,
                'current_version' => '1.0.0',
                'domain' => 'example.com',
                'product_slug' => $this->product->slug,
            ]);
        }

        // The 11th request should be rate limited
        $response->assertStatus(429);
    }

    /**
     * Test security headers.
     */
    public function test_security_headers(): void
    {
        $response = $this->getJson('/api/license/health');

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
        $response = $this->postJson('/api/license/check-updates', [
            'license_key' => $this->license->license_key,
            'current_version' => '1.0.0',
            'domain' => 'example.com',
            'product_slug' => 'non-existent-product',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test version comparison logic.
     */
    public function test_version_comparison(): void
    {
        // Test with newer version
        $response = $this->postJson('/api/license/check-updates', [
            'license_key' => $this->license->license_key,
            'current_version' => '1.0.0',
            'domain' => 'example.com',
            'product_slug' => $this->product->slug,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'is_update_available' => true,
                ],
            ]);

        // Test with same version
        $response = $this->postJson('/api/license/check-updates', [
            'license_key' => $this->license->license_key,
            'current_version' => '1.1.0',
            'domain' => 'example.com',
            'product_slug' => $this->product->slug,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'is_update_available' => false,
                ],
            ]);
    }
}
