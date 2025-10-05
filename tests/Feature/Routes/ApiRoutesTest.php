<?php

namespace Tests\Feature\Routes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * API Routes Feature Test.
 */
class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $adminUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->regularUser = User::factory()->create([
            'role' => 'user',
        ]);
    }

    /**
     * Test authenticated user endpoint.
     */
    public function test_authenticated_user_endpoint(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/user');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * Test license file content API with valid language.
     */
    public function test_license_file_content_api_with_valid_language(): void
    {
        $response = $this->getJson('/api/programming-languages/license-file/php');
        $response->assertStatus(200);
    }

    /**
     * Test license file content API with invalid language.
     */
    public function test_license_file_content_api_with_invalid_language(): void
    {
        $response = $this->getJson('/api/programming-languages/license-file/invalid-language!');
        $response->assertStatus(404);
    }

    /**
     * Test license verification endpoints with rate limiting.
     */
    public function test_license_verification_endpoints_with_rate_limiting(): void
    {
        // Test license verify with rate limiting
        for ($i = 0; $i < 32; $i++) {
            $response = $this->postJson('/api/license/verify', [
                'license_key' => 'test-license-key',
                'domain' => 'example.com',
            ]);

            if ($i < 30) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }

        // Test license register with rate limiting
        for ($i = 0; $i < 12; $i++) {
            $response = $this->postJson('/api/license/register', [
                'license_key' => 'test-license-key',
                'domain' => 'example.com',
            ]);

            if ($i < 10) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }

        // Test license status with rate limiting
        for ($i = 0; $i < 22; $i++) {
            $response = $this->postJson('/api/license/status', [
                'license_key' => 'test-license-key',
                'domain' => 'example.com',
            ]);

            if ($i < 20) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test KB serial verification endpoints with validation.
     */
    public function test_kb_serial_verification_endpoints_with_validation(): void
    {
        // Test with valid slug
        $response = $this->getJson('/api/kb/article/valid-slug/requirements');
        $response->assertStatus(200);

        $response = $this->postJson('/api/kb/article/valid-slug/verify', [
            'serial' => 'test-serial',
        ]);
        $response->assertStatus(200);

        $response = $this->getJson('/api/kb/category/valid-category/requirements');
        $response->assertStatus(200);

        // Test with invalid slug
        $response = $this->getJson('/api/kb/article/Invalid_Slug!/requirements');
        $response->assertStatus(404);

        $response = $this->postJson('/api/kb/article/Invalid_Slug!/verify', [
            'serial' => 'test-serial',
        ]);
        $response->assertStatus(404);

        $response = $this->getJson('/api/kb/category/Invalid_Category!/requirements');
        $response->assertStatus(404);
    }

    /**
     * Test KB article verification with rate limiting.
     */
    public function test_kb_article_verification_with_rate_limiting(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $response = $this->postJson('/api/kb/article/valid-slug/verify', [
                'serial' => 'test-serial',
            ]);

            if ($i < 10) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test authenticated API resource routes.
     */
    public function test_authenticated_api_resource_routes(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/licenses');
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/products');
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/users');
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/tickets');
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/kb/articles');
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/kb/categories');
        $response->assertStatus(200);
    }

    /**
     * Test public API resource routes.
     */
    public function test_public_api_resource_routes(): void
    {
        $response = $this->getJson('/api/licenses');
        $response->assertStatus(200);

        $response = $this->getJson('/api/products');
        $response->assertStatus(200);

        $response = $this->getJson('/api/users');
        $response->assertStatus(200);

        $response = $this->getJson('/api/tickets');
        $response->assertStatus(200);

        $response = $this->getJson('/api/kb/articles');
        $response->assertStatus(200);

        $response = $this->getJson('/api/kb/categories');
        $response->assertStatus(200);
    }

    /**
     * Test product lookup by purchase code.
     */
    public function test_product_lookup_by_purchase_code(): void
    {
        $response = $this->postJson('/api/product/lookup', [
            'purchase_code' => 'test-purchase-code',
        ]);
        $response->assertStatus(200);
    }

    /**
     * Test product updates API with rate limiting.
     */
    public function test_product_updates_api_with_rate_limiting(): void
    {
        // Test check updates with rate limiting
        for ($i = 0; $i < 12; $i++) {
            $response = $this->postJson('/api/product-updates/check', [
                'product_id' => 1,
                'current_version' => '1.0.0',
            ]);

            if ($i < 10) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }

        // Test get latest version with rate limiting
        for ($i = 0; $i < 22; $i++) {
            $response = $this->postJson('/api/product-updates/latest', [
                'product_id' => 1,
            ]);

            if ($i < 20) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }

        // Test get changelog with rate limiting
        for ($i = 0; $i < 12; $i++) {
            $response = $this->postJson('/api/product-updates/changelog', [
                'product_id' => 1,
                'version' => '1.0.1',
            ]);

            if ($i < 10) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test product updates download with validation.
     */
    public function test_product_updates_download_with_validation(): void
    {
        // Test with valid product ID and version
        $response = $this->getJson('/api/product-updates/download/1/1.0.1');
        $response->assertStatus(200);

        // Test with invalid product ID
        $response = $this->getJson('/api/product-updates/download/invalid/1.0.1');
        $response->assertStatus(404);

        // Test with invalid version
        $response = $this->getJson('/api/product-updates/download/1/invalid-version');
        $response->assertStatus(404);
    }

    /**
     * Test product updates download with rate limiting.
     */
    public function test_product_updates_download_with_rate_limiting(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $response = $this->getJson('/api/product-updates/download/1/1.0.1');

            if ($i < 5) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test license server API with rate limiting.
     */
    public function test_license_server_api_with_rate_limiting(): void
    {
        // Test check updates with rate limiting
        for ($i = 0; $i < 12; $i++) {
            $response = $this->postJson('/api/license/check-updates', [
                'license_key' => 'test-license',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'current_version' => '1.0.0',
            ]);

            if ($i < 10) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }

        // Test version history with rate limiting
        for ($i = 0; $i < 22; $i++) {
            $response = $this->postJson('/api/license/version-history', [
                'product_slug' => 'test-product',
            ]);

            if ($i < 20) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }

        // Test latest version with rate limiting
        for ($i = 0; $i < 22; $i++) {
            $response = $this->postJson('/api/license/latest-version', [
                'product_slug' => 'test-product',
            ]);

            if ($i < 20) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }

        // Test update info with rate limiting
        for ($i = 0; $i < 12; $i++) {
            $response = $this->postJson('/api/license/update-info', [
                'product_slug' => 'test-product',
                'version' => '1.0.1',
            ]);

            if ($i < 10) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }

        // Test get products with rate limiting
        for ($i = 0; $i < 32; $i++) {
            $response = $this->getJson('/api/license/products');

            if ($i < 30) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test license server download with validation.
     */
    public function test_license_server_download_with_validation(): void
    {
        // Test with valid license key and version
        $response = $this->getJson('/api/license/download-update/valid-license-key/1.0.1');
        $response->assertStatus(200);

        // Test with invalid license key
        $response = $this->getJson('/api/license/download-update/Invalid_Key!/1.0.1');
        $response->assertStatus(404);

        // Test with invalid version
        $response = $this->getJson('/api/license/download-update/valid-license-key/invalid-version');
        $response->assertStatus(404);
    }

    /**
     * Test license server download with rate limiting.
     */
    public function test_license_server_download_with_rate_limiting(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $response = $this->getJson('/api/license/download-update/valid-license-key/1.0.1');

            if ($i < 5) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test admin API routes with authentication.
     */
    public function test_admin_api_routes_with_authentication(): void
    {
        // Test with admin user
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/admin/user-licenses/1');
        $response->assertStatus(200);

        // Test with regular user (should be forbidden)
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/admin/user-licenses/1');
        $response->assertStatus(403);

        // Test without authentication
        $response = $this->getJson('/api/admin/user-licenses/1');
        $response->assertStatus(401);
    }

    /**
     * Test admin API routes with validation.
     */
    public function test_admin_api_routes_with_validation(): void
    {
        // Test with valid user ID
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/admin/user-licenses/1');
        $response->assertStatus(200);

        // Test with invalid user ID
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/admin/user-licenses/invalid');
        $response->assertStatus(404);
    }

    /**
     * Test API routes with different HTTP methods.
     */
    public function test_api_routes_with_different_http_methods(): void
    {
        // Test GET requests
        $response = $this->getJson('/api/license/products');
        $response->assertStatus(200);

        // Test POST requests
        $response = $this->postJson('/api/license/verify', [
            'license_key' => 'test-license',
            'domain' => 'example.com',
        ]);
        $response->assertStatus(200);

        // Test PUT requests (authenticated)
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson('/api/licenses/1', [
                'status' => 'active',
            ]);
        $response->assertStatus(200);

        // Test DELETE requests (authenticated)
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson('/api/licenses/1');
        $response->assertStatus(200);
    }

    /**
     * Test API error handling.
     */
    public function test_api_error_handling(): void
    {
        // Test 404 for non-existent routes
        $response = $this->getJson('/api/non-existent-route');
        $response->assertStatus(404);

        // Test 405 for method not allowed
        $response = $this->putJson('/api/license/products');
        $response->assertStatus(405);

        // Test 422 for validation errors
        $response = $this->postJson('/api/license/verify', []);
        $response->assertStatus(422);
    }

    /**
     * Test API response format consistency.
     */
    public function test_api_response_format_consistency(): void
    {
        $response = $this->getJson('/api/license/products');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');

        $response = $this->postJson('/api/license/verify', [
            'license_key' => 'test-license',
            'domain' => 'example.com',
        ]);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    /**
     * Test API with large payloads.
     */
    public function test_api_with_large_payloads(): void
    {
        $largeData = [
            'license_key' => str_repeat('a', 1000),
            'domain' => 'example.com',
            'metadata' => str_repeat('b', 5000),
        ];

        $response = $this->postJson('/api/license/verify', $largeData);
        $response->assertStatus(200);
    }

    /**
     * Test API with special characters.
     */
    public function test_api_with_special_characters(): void
    {
        $specialData = [
            'license_key' => 'test-license-with-special-chars-!@#$%^&*()',
            'domain' => 'example.com',
            'metadata' => 'Special chars: àáâãäåæçèéêë',
        ];

        $response = $this->postJson('/api/license/verify', $specialData);
        $response->assertStatus(200);
    }
}
