<?php

namespace Tests\Unit\Helpers;

use App\Helpers\VersionHelper;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test suite for VersionHelper.
 */
class VersionHelperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // تنظيف الـ cache للاختبارات
        Cache::flush();
    }

    /**
     * Test getCurrentVersion.
     */
    public function test_get_current_version(): void
    {
        Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Test Site',
            'version' => '1.2.3',
        ]);

        $version = VersionHelper::getCurrentVersion();

        $this->assertEquals('1.2.3', $version);
    }

    /**
     * Test getCurrentVersion with no settings.
     */
    public function test_get_current_version_with_no_settings(): void
    {
        $version = VersionHelper::getCurrentVersion();

        $this->assertEquals('1.0.1', $version);
    }

    /**
     * Test getLatestVersion with existing file.
     */
    public function test_get_latest_version_with_existing_file(): void
    {
        $versionData = [
            'current_version' => '2.0.0',
            'changelog' => [],
        ];

        file_put_contents(storage_path('version.json'), json_encode($versionData));

        $version = VersionHelper::getLatestVersion();

        $this->assertEquals('2.0.0', $version);

        // Clean up
        unlink(storage_path('version.json'));
    }

    /**
     * Test getLatestVersion with non-existing file.
     */
    public function test_get_latest_version_with_non_existing_file(): void
    {
        $version = VersionHelper::getLatestVersion();

        $this->assertEquals('1.0.1', $version);
    }

    /**
     * Test isUpdateAvailable with update available.
     */
    public function test_is_update_available_with_update_available(): void
    {
        Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Test Site',
            'version' => '1.0.0',
        ]);

        $versionData = [
            'current_version' => '2.0.0',
            'changelog' => [],
        ];

        file_put_contents(storage_path('version.json'), json_encode($versionData));

        $isAvailable = VersionHelper::isUpdateAvailable();

        $this->assertTrue($isAvailable);

        // Clean up
        unlink(storage_path('version.json'));
    }

    /**
     * Test isUpdateAvailable with no update available.
     */
    public function test_is_update_available_with_no_update_available(): void
    {
        Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Test Site',
            'version' => '2.0.0',
        ]);

        $versionData = [
            'current_version' => '1.0.0',
            'changelog' => [],
        ];

        file_put_contents(storage_path('version.json'), json_encode($versionData));

        $isAvailable = VersionHelper::isUpdateAvailable();

        $this->assertFalse($isAvailable);

        // Clean up
        unlink(storage_path('version.json'));
    }

    /**
     * Test compareVersions.
     */
    public function test_compare_versions(): void
    {
        $this->assertEquals(1, VersionHelper::compareVersions('2.0.0', '1.0.0'));
        $this->assertEquals(-1, VersionHelper::compareVersions('1.0.0', '2.0.0'));
        $this->assertEquals(0, VersionHelper::compareVersions('1.0.0', '1.0.0'));
    }

    /**
     * Test updateVersion with valid version.
     */
    public function test_update_version_with_valid_version(): void
    {
        Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Test Site',
            'version' => '1.0.0',
        ]);

        $result = VersionHelper::updateVersion('1.1.0');

        $this->assertTrue($result);

        $setting = Setting::where('key', 'site_name')->first();
        $this->assertEquals('1.1.0', $setting->version);
    }

    /**
     * Test updateVersion with invalid version format.
     */
    public function test_update_version_with_invalid_version_format(): void
    {
        $result = VersionHelper::updateVersion('invalid-version');

        $this->assertFalse($result);
    }

    /**
     * Test updateVersion with older version.
     */
    public function test_update_version_with_older_version(): void
    {
        Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Test Site',
            'version' => '2.0.0',
        ]);

        $result = VersionHelper::updateVersion('1.0.0');

        $this->assertFalse($result);
    }

    /**
     * Test isValidVersion.
     */
    public function test_is_valid_version(): void
    {
        $this->assertTrue(VersionHelper::isValidVersion('1.0.0'));
        $this->assertTrue(VersionHelper::isValidVersion('10.25.100'));
        $this->assertFalse(VersionHelper::isValidVersion('1.0'));
        $this->assertFalse(VersionHelper::isValidVersion('1.0.0.0'));
        $this->assertFalse(VersionHelper::isValidVersion('invalid'));
    }

    /**
     * Test getVersionStatus.
     */
    public function test_get_version_status(): void
    {
        Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Test Site',
            'version' => '1.0.0',
        ]);

        $versionData = [
            'current_version' => '2.0.0',
            'changelog' => [],
        ];

        file_put_contents(storage_path('version.json'), json_encode($versionData));

        $status = VersionHelper::getVersionStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('current_version', $status);
        $this->assertArrayHasKey('latest_version', $status);
        $this->assertArrayHasKey('is_update_available', $status);
        $this->assertArrayHasKey('status', $status);
        $this->assertArrayHasKey('last_checked', $status);

        $this->assertEquals('1.0.0', $status['current_version']);
        $this->assertEquals('2.0.0', $status['latest_version']);
        $this->assertTrue($status['is_update_available']);
        $this->assertEquals('update_available', $status['status']);

        // Clean up
        unlink(storage_path('version.json'));
    }

    /**
     * Test canUpdateToVersion.
     */
    public function test_can_update_to_version(): void
    {
        Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Test Site',
            'version' => '1.0.0',
        ]);

        $this->assertTrue(VersionHelper::canUpdateToVersion('1.1.0'));
        $this->assertTrue(VersionHelper::canUpdateToVersion('2.0.0'));
        $this->assertFalse(VersionHelper::canUpdateToVersion('1.0.0'));
        $this->assertFalse(VersionHelper::canUpdateToVersion('0.9.0'));
        $this->assertFalse(VersionHelper::canUpdateToVersion('invalid'));
    }

    /**
     * Test getCurrentVersionFromDatabase.
     */
    public function test_get_current_version_from_database(): void
    {
        Setting::factory()->create([
            'key' => 'current_version',
            'value' => '1.5.0',
        ]);

        $version = VersionHelper::getCurrentVersionFromDatabase();

        $this->assertEquals('1.5.0', $version);
    }

    /**
     * Test getCurrentVersionFromDatabase with no setting.
     */
    public function test_get_current_version_from_database_with_no_setting(): void
    {
        $version = VersionHelper::getCurrentVersionFromDatabase();

        $this->assertEquals('1.0.0', $version);
    }

    /**
     * Test updateCurrentVersionInDatabase.
     */
    public function test_update_current_version_in_database(): void
    {
        $result = VersionHelper::updateCurrentVersionInDatabase('1.2.0');

        $this->assertTrue($result);

        $setting = Setting::where('key', 'current_version')->first();
        $this->assertNotNull($setting);
        $this->assertEquals('1.2.0', $setting->value);
    }

    /**
     * Test updateCurrentVersionInDatabase with invalid version.
     */
    public function test_update_current_version_in_database_with_invalid_version(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        VersionHelper::updateCurrentVersionInDatabase('invalid-version');
    }

    /**
     * Test updateCurrentVersionInDatabase with older version.
     */
    public function test_update_current_version_in_database_with_older_version(): void
    {
        Setting::factory()->create([
            'key' => 'current_version',
            'value' => '2.0.0',
        ]);

        $this->expectException(\InvalidArgumentException::class);

        VersionHelper::updateCurrentVersionInDatabase('1.0.0');
    }

    /**
     * Test getVersionHistory.
     */
    public function test_get_version_history(): void
    {
        Setting::factory()->create([
            'key' => 'version_1.0.0',
            'value' => 'Initial release',
        ]);
        Setting::factory()->create([
            'key' => 'version_1.1.0',
            'value' => 'Bug fixes',
        ]);

        $history = VersionHelper::getVersionHistory();

        $this->assertIsArray($history);
        $this->assertCount(2, $history);

        $versions = array_column($history, 'version');
        $this->assertContains('1.0.0', $versions);
        $this->assertContains('1.1.0', $versions);
    }

    /**
     * Test recordVersionUpdate.
     */
    public function test_record_version_update(): void
    {
        $result = VersionHelper::recordVersionUpdate('1.2.0', 'Test update');

        $this->assertTrue($result);

        $setting = Setting::where('key', 'version_1.2.0')->first();
        $this->assertNotNull($setting);
        $this->assertEquals('Test update', $setting->value);
    }

    /**
     * Test getVersionInfo.
     */
    public function test_get_version_info(): void
    {
        $versionData = [
            'current_version' => '1.0.0',
            'changelog' => [
                '1.0.0' => ['Added new features', 'Fixed bugs'],
            ],
        ];

        file_put_contents(storage_path('version.json'), json_encode($versionData));

        $info = VersionHelper::getVersionInfo('1.0.0');

        $this->assertIsArray($info);
        $this->assertContains('Added new features', $info);
        $this->assertContains('Fixed bugs', $info);

        // Clean up
        unlink(storage_path('version.json'));
    }

    /**
     * Test getUpdateInstructions.
     */
    public function test_get_update_instructions(): void
    {
        $versionData = [
            'current_version' => '1.0.0',
            'update_instructions' => [
                '1.1.0' => ['Backup database', 'Run migrations'],
            ],
        ];

        file_put_contents(storage_path('version.json'), json_encode($versionData));

        $instructions = VersionHelper::getUpdateInstructions('1.1.0');

        $this->assertIsArray($instructions);
        $this->assertContains('Backup database', $instructions);
        $this->assertContains('Run migrations', $instructions);

        // Clean up
        unlink(storage_path('version.json'));
    }
}
