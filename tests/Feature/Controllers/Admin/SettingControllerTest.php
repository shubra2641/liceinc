<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * Test suite for SettingController.
 *
 * Tests all settings management functionality including:
 * - Settings display and management
 * - Settings update with validation
 * - File uploads handling
 * - API testing functionality
 * - Envato integration guide
 * - Error handling and logging
 */
class SettingControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected User $customer;

    protected Setting $settings;

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

        // Create test settings
        $this->settings = Setting::factory()->create([
            'site_name' => 'Test Site',
            'site_description' => 'Test Description',
            'support_email' => 'support@test.com',
            'timezone' => 'UTC',
            'maintenance_mode' => false,
        ]);

        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test admin can access settings index.
     */
    public function test_admin_can_access_settings_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.index');
        $response->assertViewHas(['settings', 'settingsArray', 'currentTimezone']);
    }

    /**
     * Test customer cannot access settings index.
     */
    public function test_customer_cannot_access_settings_index(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }

    /**
     * Test settings index creates default settings if none exist.
     */
    public function test_settings_index_creates_default_settings_if_none_exist(): void
    {
        // Delete existing settings
        Setting::truncate();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.settings.index'));

        $response->assertStatus(200);

        // Check that default settings were created
        $this->assertDatabaseHas('settings', [
            'site_name' => 'Lic',
            'timezone' => 'UTC',
            'maintenance_mode' => false,
        ]);
    }

    /**
     * Test admin can update settings with valid data.
     */
    public function test_admin_can_update_settings_with_valid_data(): void
    {
        $settingsData = [
            'site_name' => 'Updated Site Name',
            'site_description' => 'Updated site description',
            'support_email' => 'updated@test.com',
            'avg_response_time' => 48,
            'support_phone' => '+1234567890',
            'timezone' => 'America/New_York',
            'maintenance_mode' => true,
            'envato_auth_enabled' => true,
            'envato_oauth_enabled' => false,
            'license_max_attempts' => 10,
            'license_lockout_minutes' => 30,
            'preloader_enabled' => true,
            'preloader_type' => 'dots',
            'preloader_color' => '#ff0000',
            'preloader_background_color' => '#ffffff',
            'preloader_duration' => 1000,
            'logo_show_text' => true,
            'logo_text' => 'Updated Logo Text',
            'logo_text_color' => '#000000',
            'logo_text_font_size' => '28px',
            'enable_captcha' => true,
            'enable_human_question' => false,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Settings updated successfully.');

        $this->assertDatabaseHas('settings', [
            'site_name' => 'Updated Site Name',
            'site_description' => 'Updated site description',
            'support_email' => 'updated@test.com',
            'avg_response_time' => 48,
            'support_phone' => '+1234567890',
            'timezone' => 'America/New_York',
            'maintenance_mode' => true,
        ]);
    }

    /**
     * Test settings update with file uploads.
     */
    public function test_settings_update_with_file_uploads(): void
    {
        $logoFile = UploadedFile::fake()->image('logo.jpg', 200, 100);
        $ogImageFile = UploadedFile::fake()->image('og-image.jpg', 1200, 630);
        $darkLogoFile = UploadedFile::fake()->image('dark-logo.jpg', 200, 100);

        $settingsData = [
            'site_name' => 'Test Site',
            'site_logo' => $logoFile,
            'seo_og_image' => $ogImageFile,
            'site_logo_dark' => $darkLogoFile,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Settings updated successfully.');

        // Check that files were stored
        Storage::disk('public')->assertExists('logos/'.$logoFile->hashName());
        Storage::disk('public')->assertExists('seo/'.$ogImageFile->hashName());
        Storage::disk('public')->assertExists('logos/'.$darkLogoFile->hashName());
    }

    /**
     * Test settings update fails with invalid data.
     */
    public function test_settings_update_fails_with_invalid_data(): void
    {
        $invalidData = [
            'site_name' => '', // Required field missing
            'support_email' => 'invalid-email', // Invalid email
            'avg_response_time' => 'not-a-number', // Invalid number
            'preloader_duration' => 100, // Too small
            'logo_width' => 10, // Too small
            'logo_height' => 5, // Too small
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $invalidData);

        $response->assertSessionHasErrors(['site_name', 'support_email', 'avg_response_time', 'preloader_duration', 'logo_width', 'logo_height']);
    }

    /**
     * Test settings update with human questions.
     */
    public function test_settings_update_with_human_questions(): void
    {
        $humanQuestions = [
            [
                'question' => 'What is 2 + 2?',
                'answer' => '4',
            ],
            [
                'question' => 'What color is the sky?',
                'answer' => 'blue',
            ],
        ];

        $settingsData = [
            'site_name' => 'Test Site',
            'enable_human_question' => true,
            'human_questions' => $humanQuestions,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Settings updated successfully.');

        $this->assertDatabaseHas('settings', [
            'site_name' => 'Test Site',
            'enable_human_question' => true,
        ]);
    }

    /**
     * Test settings update with invalid human questions.
     */
    public function test_settings_update_with_invalid_human_questions(): void
    {
        $invalidHumanQuestions = [
            [
                'question' => '', // Empty question
                'answer' => '4',
            ],
            [
                'question' => 'What color is the sky?',
                'answer' => '', // Empty answer
            ],
        ];

        $settingsData = [
            'site_name' => 'Test Site',
            'enable_human_question' => true,
            'human_questions' => $invalidHumanQuestions,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertSessionHasErrors(['human_questions.*.question', 'human_questions.*.answer']);
    }

    /**
     * Test API token auto-generation.
     */
    public function test_api_token_auto_generation(): void
    {
        $settingsData = [
            'site_name' => 'Test Site',
            'license_api_token' => '', // Empty token
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check that a new token was generated
        $updatedSettings = Setting::first();
        $this->assertNotNull($updatedSettings->license_api_token);
        $this->assertGreaterThanOrEqual(32, strlen($updatedSettings->license_api_token));
    }

    /**
     * Test admin can test API connection.
     */
    public function test_admin_can_test_api_connection(): void
    {
        $apiData = [
            'token' => 'test-token-1234567890',
        ];

        // Mock the EnvatoService
        $envatoService = Mockery::mock('App\Services\EnvatoService');
        $envatoService->shouldReceive('testToken')
            ->with('test-token-1234567890')
            ->andReturn(true);

        $this->app->instance('App\Services\EnvatoService', $envatoService);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.settings.test-api'), $apiData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'API connection successful! Your token is valid.',
        ]);
    }

    /**
     * Test API test fails with invalid token.
     */
    public function test_api_test_fails_with_invalid_token(): void
    {
        $apiData = [
            'token' => 'invalid-token',
        ];

        // Mock the EnvatoService
        $envatoService = Mockery::mock('App\Services\EnvatoService');
        $envatoService->shouldReceive('testToken')
            ->with('invalid-token')
            ->andReturn(false);

        $this->app->instance('App\Services\EnvatoService', $envatoService);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.settings.test-api'), $apiData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid token or API connection failed. Please check your token and try again.',
        ]);
    }

    /**
     * Test API test fails with invalid input.
     */
    public function test_api_test_fails_with_invalid_input(): void
    {
        $apiData = [
            'token' => 'short', // Too short
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.settings.test-api'), $apiData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['token']);
    }

    /**
     * Test admin can access Envato guide.
     */
    public function test_admin_can_access_envato_guide(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.settings.envato-guide'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.envato-guide');
    }

    /**
     * Test customer cannot access Envato guide.
     */
    public function test_customer_cannot_access_envato_guide(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.settings.envato-guide'));

        $response->assertStatus(403);
    }

    /**
     * Test unauthorized access attempts.
     */
    public function test_unauthorized_access_returns_403(): void
    {
        $routes = [
            'admin.settings.index',
            'admin.settings.update',
            'admin.settings.test-api',
            'admin.settings.envato-guide',
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
        $response = $this->get(route('admin.settings.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test database transaction rollback on error.
     */
    public function test_database_transaction_rollback_on_error(): void
    {
        // Mock database error
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // This should trigger an error and rollback
        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Test Site',
            ]);

        // Should handle error gracefully
        $response->assertRedirect();
    }

    /**
     * Test validation error messages.
     */
    public function test_validation_error_messages(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), [
                'site_name' => '',
                'support_email' => 'invalid-email',
                'avg_response_time' => 'not-a-number',
            ]);

        $response->assertSessionHasErrors(['site_name', 'support_email', 'avg_response_time']);

        $errors = $response->session()->get('errors')->getBag('default');
        $this->assertTrue($errors->has('site_name'));
        $this->assertTrue($errors->has('support_email'));
        $this->assertTrue($errors->has('avg_response_time'));
    }

    /**
     * Test boolean field handling.
     */
    public function test_boolean_field_handling(): void
    {
        $settingsData = [
            'site_name' => 'Test Site',
            'maintenance_mode' => '1', // String boolean
            'envato_auth_enabled' => '0', // String boolean
            'preloader_enabled' => 'true', // String boolean
            'logo_show_text' => 'false', // String boolean
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Settings updated successfully.');

        $this->assertDatabaseHas('settings', [
            'site_name' => 'Test Site',
            'maintenance_mode' => true,
            'envato_auth_enabled' => false,
            'preloader_enabled' => true,
            'logo_show_text' => false,
        ]);
    }

    /**
     * Test license settings validation.
     */
    public function test_license_settings_validation(): void
    {
        $settingsData = [
            'site_name' => 'Test Site',
            'license_max_attempts' => 15,
            'license_lockout_minutes' => 45,
            'license_max_domains' => 50,
            'license_default_duration' => 365,
            'license_support_duration' => 180,
            'license_renewal_reminder' => 30,
            'license_expiration_grace' => 7,
            'license_grace_period' => 3,
            'license_domain_cooldown' => 24,
            'license_cache_duration' => 60,
            'license_batch_size' => 100,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Settings updated successfully.');

        $this->assertDatabaseHas('settings', [
            'site_name' => 'Test Site',
            'license_max_attempts' => 15,
            'license_lockout_minutes' => 45,
            'license_max_domains' => 50,
            'license_default_duration' => 365,
            'license_support_duration' => 180,
            'license_renewal_reminder' => 30,
            'license_expiration_grace' => 7,
            'license_grace_period' => 3,
            'license_domain_cooldown' => 24,
            'license_cache_duration' => 60,
            'license_batch_size' => 100,
        ]);
    }

    /**
     * Test license settings validation with invalid values.
     */
    public function test_license_settings_validation_with_invalid_values(): void
    {
        $settingsData = [
            'site_name' => 'Test Site',
            'license_max_attempts' => 0, // Too small
            'license_lockout_minutes' => 0, // Too small
            'license_max_domains' => 0, // Too small
            'license_default_duration' => 0, // Too small
            'license_support_duration' => 0, // Too small
            'license_renewal_reminder' => 0, // Too small
            'license_expiration_grace' => -1, // Too small
            'license_grace_period' => -1, // Too small
            'license_domain_cooldown' => 0, // Too small
            'license_cache_duration' => 0, // Too small
            'license_batch_size' => 0, // Too small
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertSessionHasErrors([
            'license_max_attempts',
            'license_lockout_minutes',
            'license_max_domains',
            'license_default_duration',
            'license_support_duration',
            'license_renewal_reminder',
            'license_expiration_grace',
            'license_grace_period',
            'license_domain_cooldown',
            'license_cache_duration',
            'license_batch_size',
        ]);
    }

    /**
     * Test preloader settings validation.
     */
    public function test_preloader_settings_validation(): void
    {
        $settingsData = [
            'site_name' => 'Test Site',
            'preloader_enabled' => true,
            'preloader_type' => 'spinner',
            'preloader_color' => '#ff0000',
            'preloader_background_color' => '#ffffff',
            'preloader_duration' => 1000,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Settings updated successfully.');

        $this->assertDatabaseHas('settings', [
            'site_name' => 'Test Site',
            'preloader_enabled' => true,
            'preloader_type' => 'spinner',
            'preloader_color' => '#ff0000',
            'preloader_background_color' => '#ffffff',
            'preloader_duration' => 1000,
        ]);
    }

    /**
     * Test logo settings validation.
     */
    public function test_logo_settings_validation(): void
    {
        $settingsData = [
            'site_name' => 'Test Site',
            'logo_width' => 200,
            'logo_height' => 80,
            'logo_show_text' => true,
            'logo_text' => 'Test Logo Text',
            'logo_text_color' => '#000000',
            'logo_text_font_size' => '24px',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Settings updated successfully.');

        $this->assertDatabaseHas('settings', [
            'site_name' => 'Test Site',
            'logo_width' => 200,
            'logo_height' => 80,
            'logo_show_text' => true,
            'logo_text' => 'Test Logo Text',
            'logo_text_color' => '#000000',
            'logo_text_font_size' => '24px',
        ]);
    }

    /**
     * Test captcha settings validation.
     */
    public function test_captcha_settings_validation(): void
    {
        $settingsData = [
            'site_name' => 'Test Site',
            'enable_captcha' => true,
            'captcha_site_key' => 'test-site-key',
            'captcha_secret_key' => 'test-secret-key',
            'enable_human_question' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $settingsData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Settings updated successfully.');

        $this->assertDatabaseHas('settings', [
            'site_name' => 'Test Site',
            'enable_captcha' => true,
            'captcha_site_key' => 'test-site-key',
            'captcha_secret_key' => 'test-secret-key',
            'enable_human_question' => true,
        ]);
    }
}
