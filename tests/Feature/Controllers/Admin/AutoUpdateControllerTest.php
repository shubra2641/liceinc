<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\User;
use App\Services\LicenseServerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * AutoUpdateController Feature Test.
 */
class AutoUpdateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $adminUser;

    protected $licenseServerServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        // Mock LicenseServerService
        $this->licenseServerServiceMock = Mockery::mock(LicenseServerService::class);
        $this->app->instance(LicenseServerService::class, $this->licenseServerServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test auto update index page loads successfully.
     */
    public function test_auto_update_index_loads_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.auto-update.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.auto-update.index');
    }

    /**
     * Test auto update index with error handling.
     */
    public function test_auto_update_index_with_error_handling(): void
    {
        // Mock view to throw exception
        $this->mock('view', function ($mock) {
            $mock->shouldReceive('make')->andThrow(new \Exception('View error'));
        });

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.auto-update.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.auto-update.index');
        $response->assertViewHas('error', 'Failed to load auto update page');
    }

    /**
     * Test check updates with valid data.
     */
    public function test_check_updates_with_valid_data(): void
    {
        $this->licenseServerServiceMock
            ->shouldReceive('checkUpdates')
            ->once()
            ->with([
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'current_version' => '1.0.0',
            ])
            ->andReturn([
                'success' => true,
                'data' => [
                    'version' => '1.0.1',
                    'download_url' => 'https://example.com/update.zip',
                    'changelog' => 'Bug fixes and improvements',
                ],
            ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'current_version' => '1.0.0',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'version' => '1.0.1',
                'download_url' => 'https://example.com/update.zip',
                'changelog' => 'Bug fixes and improvements',
            ],
        ]);
    }

    /**
     * Test check updates with invalid license.
     */
    public function test_check_updates_with_invalid_license(): void
    {
        $this->licenseServerServiceMock
            ->shouldReceive('checkUpdates')
            ->once()
            ->andReturn([
                'success' => false,
                'message' => 'Invalid license key',
                'error_code' => 'INVALID_LICENSE',
            ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => 'invalid-license',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'current_version' => '1.0.0',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid license key',
            'error_code' => 'INVALID_LICENSE',
        ]);
    }

    /**
     * Test check updates with validation errors.
     */
    public function test_check_updates_with_validation_errors(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => '', // Invalid: required
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'current_version' => '1.0.0',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['license_key']);
    }

    /**
     * Test check updates with service exception.
     */
    public function test_check_updates_with_service_exception(): void
    {
        $this->licenseServerServiceMock
            ->shouldReceive('checkUpdates')
            ->once()
            ->andThrow(new \Exception('Service unavailable'));

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'current_version' => '1.0.0',
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred while checking for updates: Service unavailable',
            'error_code' => 'SERVER_ERROR',
        ]);
    }

    /**
     * Test install update with valid data.
     */
    public function test_install_update_with_valid_data(): void
    {
        // Mock successful license verification
        $this->licenseServerServiceMock
            ->shouldReceive('checkUpdates')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => ['version' => '1.0.1'],
            ]);

        // Mock successful download
        $this->licenseServerServiceMock
            ->shouldReceive('downloadUpdate')
            ->once()
            ->andReturn([
                'success' => true,
                'content' => 'fake-zip-content',
            ]);

        // Mock Artisan commands
        Artisan::shouldReceive('call')
            ->with('backup:run', Mockery::any())
            ->once();

        // Mock file operations
        Storage::fake('local');
        File::shouldReceive('exists')->andReturn(false);
        File::shouldReceive('makeDirectory')->andReturn(true);
        File::shouldReceive('put')->andReturn(true);
        File::shouldReceive('delete')->andReturn(true);
        File::shouldReceive('deleteDirectory')->andReturn(true);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'version' => '1.0.1',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Update installed successfully',
            'data' => [
                'version' => '1.0.1',
                'installed_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Test install update with license verification failure.
     */
    public function test_install_update_with_license_verification_failure(): void
    {
        $this->licenseServerServiceMock
            ->shouldReceive('checkUpdates')
            ->once()
            ->andReturn([
                'success' => false,
                'message' => 'License verification failed',
                'error_code' => 'LICENSE_INVALID',
            ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'invalid-license',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'version' => '1.0.1',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'License verification failed',
            'error_code' => 'LICENSE_INVALID',
        ]);
    }

    /**
     * Test install update with download failure.
     */
    public function test_install_update_with_download_failure(): void
    {
        // Mock successful license verification
        $this->licenseServerServiceMock
            ->shouldReceive('checkUpdates')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => ['version' => '1.0.1'],
            ]);

        // Mock failed download
        $this->licenseServerServiceMock
            ->shouldReceive('downloadUpdate')
            ->once()
            ->andReturn([
                'success' => false,
                'message' => 'Download failed',
                'error_code' => 'DOWNLOAD_ERROR',
            ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'version' => '1.0.1',
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Download failed',
            'error_code' => 'DOWNLOAD_ERROR',
        ]);
    }

    /**
     * Test install update with validation errors.
     */
    public function test_install_update_with_validation_errors(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => '', // Invalid: required
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'version' => '1.0.1',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['license_key']);
    }

    /**
     * Test install update with service exception.
     */
    public function test_install_update_with_service_exception(): void
    {
        $this->licenseServerServiceMock
            ->shouldReceive('checkUpdates')
            ->once()
            ->andThrow(new \Exception('Service unavailable'));

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'version' => '1.0.1',
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred while installing update: Service unavailable',
            'error_code' => 'SERVER_ERROR',
        ]);
    }

    /**
     * Test install update with file system errors.
     */
    public function test_install_update_with_file_system_errors(): void
    {
        // Mock successful license verification
        $this->licenseServerServiceMock
            ->shouldReceive('checkUpdates')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => ['version' => '1.0.1'],
            ]);

        // Mock successful download
        $this->licenseServerServiceMock
            ->shouldReceive('downloadUpdate')
            ->once()
            ->andReturn([
                'success' => true,
                'content' => 'fake-zip-content',
            ]);

        // Mock file system errors
        File::shouldReceive('exists')->andReturn(false);
        File::shouldReceive('makeDirectory')->andThrow(new \Exception('Permission denied'));

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'version' => '1.0.1',
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred while installing update: Permission denied',
            'error_code' => 'SERVER_ERROR',
        ]);
    }

    /**
     * Test unauthorized access to auto update.
     */
    public function test_unauthorized_access_to_auto_update(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get(route('admin.auto-update.index'));

        $response->assertStatus(403);
    }

    /**
     * Test guest access to auto update.
     */
    public function test_guest_access_to_auto_update(): void
    {
        $response = $this->get(route('admin.auto-update.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test check updates with missing required fields.
     */
    public function test_check_updates_with_missing_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'license_key',
            'product_slug',
            'domain',
            'current_version',
        ]);
    }

    /**
     * Test install update with missing required fields.
     */
    public function test_install_update_with_missing_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'license_key',
            'product_slug',
            'domain',
            'version',
        ]);
    }

    /**
     * Test check updates with invalid version format.
     */
    public function test_check_updates_with_invalid_version_format(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'current_version' => 'invalid-version', // Invalid format
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_version']);
    }

    /**
     * Test install update with invalid version format.
     */
    public function test_install_update_with_invalid_version_format(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'version' => 'invalid-version', // Invalid format
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['version']);
    }

    /**
     * Test check updates with invalid domain format.
     */
    public function test_check_updates_with_invalid_domain_format(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'invalid-domain-format', // Invalid format
                'current_version' => '1.0.0',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['domain']);
    }

    /**
     * Test install update with invalid domain format.
     */
    public function test_install_update_with_invalid_domain_format(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => 'invalid-domain-format', // Invalid format
                'version' => '1.0.1',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['domain']);
    }

    /**
     * Test check updates with invalid product slug format.
     */
    public function test_check_updates_with_invalid_product_slug_format(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'Invalid Product Slug!', // Invalid format
                'domain' => 'example.com',
                'current_version' => '1.0.0',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_slug']);
    }

    /**
     * Test install update with invalid product slug format.
     */
    public function test_install_update_with_invalid_product_slug_format(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'Invalid Product Slug!', // Invalid format
                'domain' => 'example.com',
                'version' => '1.0.1',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_slug']);
    }

    /**
     * Test check updates with license key too short.
     */
    public function test_check_updates_with_license_key_too_short(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => 'short', // Too short
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'current_version' => '1.0.0',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['license_key']);
    }

    /**
     * Test install update with license key too short.
     */
    public function test_install_update_with_license_key_too_short(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'short', // Too short
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'version' => '1.0.1',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['license_key']);
    }

    /**
     * Test check updates with very long license key.
     */
    public function test_check_updates_with_very_long_license_key(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => str_repeat('a', 256), // Too long
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'current_version' => '1.0.0',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['license_key']);
    }

    /**
     * Test install update with very long license key.
     */
    public function test_install_update_with_very_long_license_key(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => str_repeat('a', 256), // Too long
                'product_slug' => 'test-product',
                'domain' => 'example.com',
                'version' => '1.0.1',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['license_key']);
    }

    /**
     * Test check updates with very long product slug.
     */
    public function test_check_updates_with_very_long_product_slug(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => 'test-license-key',
                'product_slug' => str_repeat('a', 256), // Too long
                'domain' => 'example.com',
                'current_version' => '1.0.0',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_slug']);
    }

    /**
     * Test install update with very long product slug.
     */
    public function test_install_update_with_very_long_product_slug(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'test-license-key',
                'product_slug' => str_repeat('a', 256), // Too long
                'domain' => 'example.com',
                'version' => '1.0.1',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_slug']);
    }

    /**
     * Test check updates with very long domain.
     */
    public function test_check_updates_with_very_long_domain(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.check'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => str_repeat('a', 256).'.com', // Too long
                'current_version' => '1.0.0',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['domain']);
    }

    /**
     * Test install update with very long domain.
     */
    public function test_install_update_with_very_long_domain(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.auto-update.install'), [
                'license_key' => 'test-license-key',
                'product_slug' => 'test-product',
                'domain' => str_repeat('a', 256).'.com', // Too long
                'version' => '1.0.1',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['domain']);
    }
}
