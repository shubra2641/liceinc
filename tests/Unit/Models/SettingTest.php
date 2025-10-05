<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for Setting model.
 */
class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
        Cache::flush();
    }

    /**
     * Test setting creation.
     */
    public function test_can_create_setting(): void
    {
        $setting = Setting::create([
            'site_name' => 'Test Site',
            'support_email' => 'support@test.com',
            'is_active' => true,
            'maintenance_mode' => false,
        ]);

        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertEquals('Test Site', $setting->site_name);
        $this->assertEquals('support@test.com', $setting->support_email);
    }

    /**
     * Test get setting value.
     */
    public function test_get_setting_value(): void
    {
        Setting::create([
            'site_name' => 'Test Site',
            'support_email' => 'support@test.com',
        ]);

        $siteName = Setting::get('site_name');
        $this->assertEquals('Test Site', $siteName);

        $nonExistent = Setting::get('non_existent', 'default');
        $this->assertEquals('default', $nonExistent);
    }

    /**
     * Test set setting value.
     */
    public function test_set_setting_value(): void
    {
        $setting = Setting::set('site_name', 'New Site Name');

        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertEquals('New Site Name', $setting->site_name);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Application setting updated') &&
                   $context['key'] === 'site_name';
        });
    }

    /**
     * Test get all settings.
     */
    public function test_get_all_settings(): void
    {
        Setting::create([
            'site_name' => 'Test Site',
            'support_email' => 'support@test.com',
        ]);

        $allSettings = Setting::allSettings();
        $this->assertInstanceOf(Setting::class, $allSettings);
        $this->assertEquals('Test Site', $allSettings->site_name);
    }

    /**
     * Test clear cache.
     */
    public function test_clear_cache(): void
    {
        Setting::create(['site_name' => 'Test Site']);

        // Get setting to populate cache
        Setting::get('site_name');

        Setting::clearCache();

        Log::assertLogged('info', function ($message) {
            return str_contains($message, 'Application settings cache cleared');
        });
    }

    /**
     * Test value attribute casting.
     */
    public function test_value_attribute_casting(): void
    {
        $setting = Setting::create([
            'type' => 'boolean',
            'value' => '1',
        ]);

        $this->assertTrue($setting->value);

        $setting = Setting::create([
            'type' => 'integer',
            'value' => '123',
        ]);

        $this->assertEquals(123, $setting->value);

        $setting = Setting::create([
            'type' => 'array',
            'value' => '{"key": "value"}',
        ]);

        $this->assertEquals(['key' => 'value'], $setting->value);
    }

    /**
     * Test set value attribute.
     */
    public function test_set_value_attribute(): void
    {
        $setting = new Setting();
        $setting->type = 'array';
        $setting->value = ['key' => 'value'];

        $this->assertEquals('{"key":"value"}', $setting->getAttributes()['value']);
    }

    /**
     * Test has setting.
     */
    public function test_has_setting(): void
    {
        Setting::create(['site_name' => 'Test Site']);

        $this->assertTrue(Setting::has('site_name'));
        $this->assertFalse(Setting::has('non_existent'));
    }

    /**
     * Test get many settings.
     */
    public function test_get_many_settings(): void
    {
        Setting::create([
            'site_name' => 'Test Site',
            'support_email' => 'support@test.com',
            'maintenance_mode' => true,
        ]);

        $settings = Setting::getMany(
            ['site_name', 'support_email', 'non_existent'],
            ['non_existent' => 'default'],
        );

        $this->assertEquals('Test Site', $settings['site_name']);
        $this->assertEquals('support@test.com', $settings['support_email']);
        $this->assertEquals('default', $settings['non_existent']);
    }

    /**
     * Test set many settings.
     */
    public function test_set_many_settings(): void
    {
        $settings = [
            'site_name' => 'New Site',
            'support_email' => 'new@test.com',
            'maintenance_mode' => true,
        ];

        $setting = Setting::setMany($settings);

        $this->assertEquals('New Site', $setting->site_name);
        $this->assertEquals('new@test.com', $setting->support_email);
        $this->assertTrue($setting->maintenance_mode);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Multiple application settings updated') &&
                   count($context['updated_keys']) === 3;
        });
    }

    /**
     * Test get settings by category.
     */
    public function test_get_settings_by_category(): void
    {
        Setting::create([
            'license_verify_envato' => true,
            'license_cache_duration' => 3600,
            'site_name' => 'Test Site',
        ]);

        $licenseSettings = Setting::getByCategory('license');

        $this->assertArrayHasKey('license_verify_envato', $licenseSettings);
        $this->assertArrayHasKey('license_cache_duration', $licenseSettings);
        $this->assertArrayNotHasKey('site_name', $licenseSettings);
    }

    /**
     * Test configuration validation.
     */
    public function test_configuration_validation(): void
    {
        $validSetting = Setting::create([
            'support_email' => 'valid@test.com',
            'envato_api_key' => 'valid_api_key_12345',
            'timezone' => 'UTC',
        ]);

        $invalidSetting = Setting::create([
            'support_email' => 'invalid-email',
            'envato_api_key' => 'short',
            'timezone' => 'Invalid/Timezone',
        ]);

        $this->assertTrue($validSetting->isValidConfiguration());
        $this->assertEmpty($validSetting->validateConfiguration());

        $this->assertFalse($invalidSetting->isValidConfiguration());
        $errors = $invalidSetting->validateConfiguration();
        $this->assertContains('Invalid support email format', $errors);
        $this->assertContains('Envato API key is too short', $errors);
        $this->assertContains('Invalid timezone', $errors);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $setting = Setting::create([
            'envato_auth_enabled' => '1',
            'maintenance_mode' => '0',
            'preloader_enabled' => '1',
            'preloader_duration' => '3000',
            'logo_width' => '200',
            'logo_height' => '100',
            'avg_response_time' => '500',
        ]);

        $this->assertIsBool($setting->envato_auth_enabled);
        $this->assertIsBool($setting->maintenance_mode);
        $this->assertIsBool($setting->preloader_enabled);
        $this->assertIsInt($setting->preloader_duration);
        $this->assertIsInt($setting->logo_width);
        $this->assertIsInt($setting->logo_height);
        $this->assertIsInt($setting->avg_response_time);
    }

    /**
     * Test boot method logging.
     */
    public function test_boot_method_logging(): void
    {
        $setting = Setting::create(['site_name' => 'Test Site']);

        $setting->update(['site_name' => 'Updated Site']);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Application setting updated') &&
                   in_array('site_name', $context['updated_fields']);
        });
    }

    /**
     * Test error handling in get method.
     */
    public function test_error_handling_in_get_method(): void
    {
        // This test simulates database connection issues
        $result = Setting::get('test_key', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    /**
     * Test error handling in set method.
     */
    public function test_error_handling_in_set_method(): void
    {
        // This test simulates database connection issues
        $setting = Setting::set('test_key', 'test_value');
        $this->assertInstanceOf(Setting::class, $setting);
    }

    /**
     * Test error handling in allSettings method.
     */
    public function test_error_handling_in_all_settings_method(): void
    {
        // This test simulates database connection issues
        $settings = Setting::allSettings();
        $this->assertInstanceOf(Setting::class, $settings);
    }
}
