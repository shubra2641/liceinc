<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\License;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseServerService;
use App\Services\UpdatePackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * Test suite for UpdateController.
 *
 * Tests all update management functionality including:
 * - Manual system updates and rollbacks
 * - Automatic update checking and installation
 * - Update package uploads
 * - Backup creation and management
 * - Version history and comparison
 * - Error handling and validation
 */
class UpdateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected User $customer;

    protected Product $product;

    protected License $license;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);
        $this->admin->assignRole('admin');

        $this->customer = User::factory()->create([
            'email' => 'customer@test.com',
        ]);
        $this->customer->assignRole('customer');

        // Create test product and license
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'version' => '1.0.0',
        ]);

        $this->license = License::factory()->create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'license_key' => 'test-license-key-12345',
            'purchase_code' => 'test-purchase-code-67890',
        ]);

        // Set up test settings
        Setting::set('system.version', '1.0.0');
        Setting::set('system.auto_update_enabled', true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test update index page access.
     */
    public function test_admin_can_access_update_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.updates.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.updates.index');
        $response->assertViewHas(['currentVersion', 'latestVersion', 'backups']);
    }

    /**
     * Test customer cannot access update index.
     */
    public function test_customer_cannot_access_update_index(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.updates.index'));

        $response->assertStatus(403);
    }

    /**
     * Test manual system update with valid data.
     */
    public function test_admin_can_perform_manual_update(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.update'), [
                'version' => '1.0.1',
                'confirm' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'System update completed successfully',
        ]);
    }

    /**
     * Test manual update with invalid version.
     */
    public function test_manual_update_fails_with_invalid_version(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.update'), [
                'version' => 'invalid-version',
                'confirm' => true,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['version']);
    }

    /**
     * Test manual update without confirmation.
     */
    public function test_manual_update_fails_without_confirmation(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.update'), [
                'version' => '1.0.1',
                'confirm' => false,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['confirm']);
    }

    /**
     * Test system rollback functionality.
     */
    public function test_admin_can_perform_rollback(): void
    {
        // Create a mock backup file
        Storage::fake('local');
        $backupFile = 'backups/backup_2024-01-01_12-00-00.zip';
        Storage::put($backupFile, 'mock backup content');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.rollback'), [
                'backup_file' => $backupFile,
                'confirm' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'System rollback completed successfully',
        ]);
    }

    /**
     * Test rollback with invalid backup file.
     */
    public function test_rollback_fails_with_invalid_backup(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.rollback'), [
                'backup_file' => 'nonexistent-backup.zip',
                'confirm' => true,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['backup_file']);
    }

    /**
     * Test update package upload.
     */
    public function test_admin_can_upload_update_package(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('update.zip', 1024, 'application/zip');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.upload-package'), [
                'update_package' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Update package uploaded successfully',
        ]);

        // Verify file was stored
        Storage::assertExists('product-updates/'.$file->hashName());
    }

    /**
     * Test upload with invalid file type.
     */
    public function test_upload_fails_with_invalid_file_type(): void
    {
        $file = UploadedFile::fake()->create('update.txt', 1024, 'text/plain');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.upload-package'), [
                'update_package' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['update_package']);
    }

    /**
     * Test upload with oversized file.
     */
    public function test_upload_fails_with_oversized_file(): void
    {
        $file = UploadedFile::fake()->create('update.zip', 60000, 'application/zip'); // 60MB

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.upload-package'), [
                'update_package' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['update_package']);
    }

    /**
     * Test automatic update check.
     */
    public function test_admin_can_check_auto_updates(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.check-auto-updates'), [
                'license_key' => $this->license->license_key,
                'product_slug' => $this->product->slug,
                'domain' => 'example.com',
                'version' => '1.0.0',
                'confirm' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'current_version',
                'latest_version',
                'update_available',
                'update_info',
            ],
        ]);
    }

    /**
     * Test auto update check with invalid license.
     */
    public function test_auto_update_check_fails_with_invalid_license(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.check-auto-updates'), [
                'license_key' => 'invalid-license',
                'product_slug' => $this->product->slug,
                'domain' => 'example.com',
                'version' => '1.0.0',
                'confirm' => true,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['license_key']);
    }

    /**
     * Test automatic update installation.
     */
    public function test_admin_can_install_auto_update(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.install-auto-update'), [
                'license_key' => $this->license->license_key,
                'product_slug' => $this->product->slug,
                'domain' => 'example.com',
                'version' => '1.0.1',
                'confirm' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Auto update installed successfully',
        ]);
    }

    /**
     * Test auto update installation with invalid version.
     */
    public function test_auto_update_installation_fails_with_invalid_version(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.install-auto-update'), [
                'license_key' => $this->license->license_key,
                'product_slug' => $this->product->slug,
                'domain' => 'example.com',
                'version' => '0.9.0', // Downgrade not allowed
                'confirm' => true,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['version']);
    }

    /**
     * Test get backups endpoint.
     */
    public function test_admin_can_get_backups(): void
    {
        // Create mock backup files
        Storage::fake('local');
        Storage::put('backups/backup_2024-01-01_12-00-00.zip', 'backup content 1');
        Storage::put('backups/backup_2024-01-02_12-00-00.zip', 'backup content 2');

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.updates.backups'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'filename',
                    'size',
                    'formatted_size',
                    'created_at',
                ],
            ],
        ]);
    }

    /**
     * Test version history from central server.
     */
    public function test_admin_can_get_version_history(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.updates.version-history'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'versions' => [
                    '*' => [
                        'version',
                        'release_date',
                        'changelog',
                        'is_major',
                        'is_required',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test latest version check from central server.
     */
    public function test_admin_can_get_latest_version(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.updates.latest-version'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'latest_version',
                'current_version',
                'update_available',
                'update_info',
            ],
        ]);
    }

    /**
     * Test error handling for service failures.
     */
    public function test_handles_service_failures_gracefully(): void
    {
        // Mock service failure
        $this->mock(LicenseServerService::class, function ($mock) {
            $mock->shouldReceive('checkForUpdates')
                ->andThrow(new \Exception('Service unavailable'));
        });

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.check-auto-updates'), [
                'license_key' => $this->license->license_key,
                'product_slug' => $this->product->slug,
                'domain' => 'example.com',
                'version' => '1.0.0',
                'confirm' => true,
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred while checking for updates',
        ]);
    }

    /**
     * Test cache management.
     */
    public function test_update_operations_clear_appropriate_caches(): void
    {
        // Set some cache values
        Cache::put('version_check_cache', 'cached_data', 3600);
        Cache::put('update_info_cache', 'cached_info', 3600);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.update'), [
                'version' => '1.0.1',
                'confirm' => true,
            ]);

        $response->assertStatus(200);

        // Verify caches were cleared
        $this->assertFalse(Cache::has('version_check_cache'));
        $this->assertFalse(Cache::has('update_info_cache'));
    }

    /**
     * Test backup creation before updates.
     */
    public function test_creates_backup_before_update(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.update'), [
                'version' => '1.0.1',
                'confirm' => true,
            ]);

        $response->assertStatus(200);

        // Verify backup was created
        $backups = Storage::files('backups');
        $this->assertNotEmpty($backups);
        $this->assertStringContains('backup_', $backups[0]);
    }

    /**
     * Test file size formatting helper.
     */
    public function test_file_size_formatting(): void
    {
        $controller = new \App\Http\Controllers\Admin\UpdateController(
            app(LicenseServerService::class),
            app(UpdatePackageService::class),
        );

        // Use reflection to test private method
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('formatFileSize');
        $method->setAccessible(true);

        $this->assertEquals('1.00 KB', $method->invoke($controller, 1024));
        $this->assertEquals('1.00 MB', $method->invoke($controller, 1024 * 1024));
        $this->assertEquals('1.00 GB', $method->invoke($controller, 1024 * 1024 * 1024));
    }

    /**
     * Test validation error messages.
     */
    public function test_validation_error_messages(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.updates.update'), [
                'version' => '',
                'confirm' => false,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['version', 'confirm']);

        $errors = $response->json('errors');
        $this->assertArrayHasKey('version', $errors);
        $this->assertArrayHasKey('confirm', $errors);
    }

    /**
     * Test unauthorized access attempts.
     */
    public function test_unauthorized_access_returns_403(): void
    {
        $routes = [
            'admin.updates.index',
            'admin.updates.update',
            'admin.updates.rollback',
            'admin.updates.upload-package',
            'admin.updates.check-auto-updates',
            'admin.updates.install-auto-update',
            'admin.updates.backups',
            'admin.updates.version-history',
            'admin.updates.latest-version',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->customer)
                ->get(route($route));

            $response->assertStatus(403);
        }
    }

    /**
     * Test guest access attempts.
     */
    public function test_guest_access_redirects_to_login(): void
    {
        $response = $this->get(route('admin.updates.index'));
        $response->assertRedirect(route('login'));
    }
}
