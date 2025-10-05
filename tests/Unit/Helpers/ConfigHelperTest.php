<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ConfigHelper;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for ConfigHelper.
 */
class ConfigHelperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Cache and Log are already configured for testing
    }

    /**
     * Test getSetting with valid key.
     */
    public function test_get_setting_with_valid_key(): void
    {
        Setting::factory()->create([
            'key' => 'test_setting',
            'value' => 'test_value',
        ]);

        $result = ConfigHelper::getSetting('test_setting', 'default');

        $this->assertEquals('test_value', $result);
    }

    /**
     * Test getSetting with invalid key.
     */
    public function test_get_setting_with_invalid_key(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ConfigHelper::getSetting('invalid@key!', 'default');
    }

    /**
     * Test getSetting with non-existent key.
     */
    public function test_get_setting_with_non_existent_key(): void
    {
        $result = ConfigHelper::getSetting('non_existent', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    /**
     * Test getSetting with config fallback.
     */
    public function test_get_setting_with_config_fallback(): void
    {
        config(['test.config' => 'config_value']);

        $result = ConfigHelper::getSetting('non_existent', 'default', 'test.config');

        $this->assertEquals('config_value', $result);
    }

    /**
     * Test getSettings with multiple keys.
     */
    public function test_get_settings_with_multiple_keys(): void
    {
        Setting::factory()->create([
            'key' => 'setting1',
            'value' => 'value1',
        ]);
        Setting::factory()->create([
            'key' => 'setting2',
            'value' => 'value2',
        ]);

        $result = ConfigHelper::getSettings(['setting1', 'setting2']);

        $this->assertArrayHasKey('setting1', $result);
        $this->assertArrayHasKey('setting2', $result);
        $this->assertEquals('value1', $result['setting1']);
        $this->assertEquals('value2', $result['setting2']);
    }

    /**
     * Test getTypedSetting with string type.
     */
    public function test_get_typed_setting_with_string_type(): void
    {
        Setting::factory()->create([
            'key' => 'string_setting',
            'value' => '123',
        ]);

        $result = ConfigHelper::getTypedSetting('string_setting', 'string', 'default');

        $this->assertIsString($result);
        $this->assertEquals('123', $result);
    }

    /**
     * Test getTypedSetting with integer type.
     */
    public function test_get_typed_setting_with_integer_type(): void
    {
        Setting::factory()->create([
            'key' => 'int_setting',
            'value' => '123',
        ]);

        $result = ConfigHelper::getTypedSetting('int_setting', 'int', 0);

        $this->assertIsInt($result);
        $this->assertEquals(123, $result);
    }

    /**
     * Test getTypedSetting with boolean type.
     */
    public function test_get_typed_setting_with_boolean_type(): void
    {
        Setting::factory()->create([
            'key' => 'bool_setting',
            'value' => 'true',
        ]);

        $result = ConfigHelper::getTypedSetting('bool_setting', 'bool', false);

        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    /**
     * Test hasSetting with existing setting.
     */
    public function test_has_setting_with_existing_setting(): void
    {
        Setting::factory()->create([
            'key' => 'existing_setting',
            'value' => 'some_value',
        ]);

        $result = ConfigHelper::hasSetting('existing_setting');

        $this->assertTrue($result);
    }

    /**
     * Test hasSetting with non-existing setting.
     */
    public function test_has_setting_with_non_existing_setting(): void
    {
        $result = ConfigHelper::hasSetting('non_existing_setting');

        $this->assertFalse($result);
    }

    /**
     * Test clearSettingCache.
     */
    public function test_clear_setting_cache(): void
    {
        ConfigHelper::clearSettingCache('test_key');

        // Should not throw any exceptions
        $this->assertTrue(true);
    }

    /**
     * Test clearAllCache.
     */
    public function test_clear_all_cache(): void
    {
        ConfigHelper::clearAllCache();

        // Should not throw any exceptions
        $this->assertTrue(true);
    }

    /**
     * Test getCacheStats.
     */
    public function test_get_cache_stats(): void
    {
        $stats = ConfigHelper::getCacheStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('cache_prefix', $stats);
        $this->assertArrayHasKey('cache_ttl', $stats);
        $this->assertArrayHasKey('cache_tag', $stats);
    }

    /**
     * Test warmUpCache.
     */
    public function test_warm_up_cache(): void
    {
        ConfigHelper::warmUpCache(['test_key']);

        // Should not throw any exceptions
        $this->assertTrue(true);
    }

    /**
     * Test getLicenseSettings.
     */
    public function test_get_license_settings(): void
    {
        $settings = ConfigHelper::getLicenseSettings();

        $this->assertIsArray($settings);
        // Should return array even if no settings exist
    }

    /**
     * Test getEnvatoSettings.
     */
    public function test_get_envato_settings(): void
    {
        $settings = ConfigHelper::getEnvatoSettings();

        $this->assertIsArray($settings);
        // Should return array even if no settings exist
    }

    /**
     * Test getSettingsWithEnvFallback.
     */
    public function test_get_settings_with_env_fallback(): void
    {
        putenv('TEST_ENV_VAR=env_value');

        $result = ConfigHelper::getSettingsWithEnvFallback(
            ['non_existent'],
            ['non_existent' => 'TEST_ENV_VAR'],
        );

        $this->assertArrayHasKey('non_existent', $result);
        $this->assertEquals('env_value', $result['non_existent']);
    }
}
