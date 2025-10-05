<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ConfigHelper;
use App\Helpers\EnvatoHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for EnvatoHelper.
 */
class EnvatoHelperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::fake();
        // Log is already configured for testing
        Http::fake();
    }

    /**
     * Test isEnvatoConfigured with valid settings.
     */
    public function test_is_envato_configured_with_valid_settings(): void
    {
        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andReturn([
                    'envato_personal_token' => 'valid_token_123',
                    'envato_client_id' => 'client_id_123',
                    'envato_client_secret' => 'client_secret_123',
                ]);
        });

        $result = EnvatoHelper::isEnvatoConfigured();

        $this->assertTrue($result);
    }

    /**
     * Test isEnvatoConfigured with missing settings.
     */
    public function test_is_envato_configured_with_missing_settings(): void
    {
        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andReturn(null);
        });

        $result = EnvatoHelper::isEnvatoConfigured();

        $this->assertFalse($result);
    }

    /**
     * Test isEnvatoConfigured with incomplete settings.
     */
    public function test_is_envato_configured_with_incomplete_settings(): void
    {
        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andReturn([
                    'envato_personal_token' => 'valid_token_123',
                    'envato_client_id' => 'client_id_123',
                    // Missing client_secret
                ]);
        });

        $result = EnvatoHelper::isEnvatoConfigured();

        $this->assertFalse($result);
    }

    /**
     * Test getEnvatoSettings with caching.
     */
    public function test_get_envato_settings_with_caching(): void
    {
        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andReturn([
                    'envato_personal_token' => 'token_123',
                    'envato_client_id' => 'client_123',
                    'envato_client_secret' => 'secret_123',
                ]);
        });

        $result = EnvatoHelper::getEnvatoSettings(true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('envato_personal_token', $result);
    }

    /**
     * Test getEnvatoSettings without caching.
     */
    public function test_get_envato_settings_without_caching(): void
    {
        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andReturn([
                    'envato_personal_token' => 'token_123',
                    'envato_client_id' => 'client_123',
                    'envato_client_secret' => 'secret_123',
                ]);
        });

        $result = EnvatoHelper::getEnvatoSettings(false);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('envato_personal_token', $result);
    }

    /**
     * Test testApiConnection with successful response.
     */
    public function test_test_api_connection_with_successful_response(): void
    {
        Http::fake([
            'api.envato.com/*' => Http::response(['total_items' => 1000], 200),
        ]);

        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andReturn([
                    'envato_personal_token' => 'valid_token_123',
                    'envato_client_id' => 'client_id_123',
                    'envato_client_secret' => 'client_secret_123',
                ]);
        });

        $result = EnvatoHelper::testApiConnection();

        $this->assertTrue($result);
    }

    /**
     * Test testApiConnection with failed response.
     */
    public function test_test_api_connection_with_failed_response(): void
    {
        Http::fake([
            'api.envato.com/*' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andReturn([
                    'envato_personal_token' => 'invalid_token',
                    'envato_client_id' => 'client_id_123',
                    'envato_client_secret' => 'client_secret_123',
                ]);
        });

        $result = EnvatoHelper::testApiConnection();

        $this->assertFalse($result);
    }

    /**
     * Test validatePurchaseCode with valid code.
     */
    public function test_validate_purchase_code_with_valid_code(): void
    {
        Http::fake([
            'api.envato.com/*' => Http::response([
                'item' => ['id' => '12345', 'name' => 'Test Item'],
                'buyer' => 'test_buyer',
                'sold_at' => '2023-01-01',
                'license' => 'regular',
            ], 200),
        ]);

        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andReturn([
                    'envato_personal_token' => 'valid_token_123',
                    'envato_client_id' => 'client_id_123',
                    'envato_client_secret' => 'client_secret_123',
                ]);
        });

        $result = EnvatoHelper::validatePurchaseCode('12345678-1234-1234-1234-123456789012', '12345');

        $this->assertTrue($result['valid']);
        $this->assertEquals('VALID', $result['code']);
    }

    /**
     * Test validatePurchaseCode with invalid format.
     */
    public function test_validate_purchase_code_with_invalid_format(): void
    {
        $result = EnvatoHelper::validatePurchaseCode('invalid_format', '12345');

        $this->assertFalse($result['valid']);
        $this->assertEquals('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test validatePurchaseCode with item mismatch.
     */
    public function test_validate_purchase_code_with_item_mismatch(): void
    {
        Http::fake([
            'api.envato.com/*' => Http::response([
                'item' => ['id' => '99999', 'name' => 'Different Item'],
                'buyer' => 'test_buyer',
                'sold_at' => '2023-01-01',
                'license' => 'regular',
            ], 200),
        ]);

        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andReturn([
                    'envato_personal_token' => 'valid_token_123',
                    'envato_client_id' => 'client_id_123',
                    'envato_client_secret' => 'client_secret_123',
                ]);
        });

        $result = EnvatoHelper::validatePurchaseCode('12345678-1234-1234-1234-123456789012', '12345');

        $this->assertFalse($result['valid']);
        $this->assertEquals('ITEM_MISMATCH', $result['code']);
    }

    /**
     * Test clearCache.
     */
    public function test_clear_cache(): void
    {
        EnvatoHelper::clearCache();

        // Should not throw any exceptions
        $this->assertTrue(true);
    }

    /**
     * Test getRateLimitStatus.
     */
    public function test_get_rate_limit_status(): void
    {
        $status = EnvatoHelper::getRateLimitStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('requests_per_hour', $status);
        $this->assertArrayHasKey('window_seconds', $status);
        $this->assertArrayHasKey('cache_prefix', $status);
        $this->assertArrayHasKey('cache_ttl', $status);
    }

    /**
     * Test getHealthStatus.
     */
    public function test_get_health_status(): void
    {
        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andReturn([
                    'envato_personal_token' => 'valid_token_123',
                    'envato_client_id' => 'client_id_123',
                    'envato_client_secret' => 'client_secret_123',
                ]);
        });

        Http::fake([
            'api.envato.com/*' => Http::response(['total_items' => 1000], 200),
        ]);

        $status = EnvatoHelper::getHealthStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('configured', $status);
        $this->assertArrayHasKey('api_accessible', $status);
        $this->assertArrayHasKey('cache_working', $status);
        $this->assertArrayHasKey('last_test', $status);
        $this->assertArrayHasKey('errors', $status);
    }

    /**
     * Test exception handling in isEnvatoConfigured.
     */
    public function test_exception_handling_in_is_envato_configured(): void
    {
        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andThrow(new \Exception('Database error'));
        });

        $result = EnvatoHelper::isEnvatoConfigured();

        $this->assertFalse($result);
    }

    /**
     * Test exception handling in getEnvatoSettings.
     */
    public function test_exception_handling_in_get_envato_settings(): void
    {
        $this->mock(ConfigHelper::class, function ($mock) {
            $mock->shouldReceive('getEnvatoSettings')
                ->andThrow(new \Exception('Database error'));
        });

        $result = EnvatoHelper::getEnvatoSettings();

        $this->assertNull($result);
    }
}
