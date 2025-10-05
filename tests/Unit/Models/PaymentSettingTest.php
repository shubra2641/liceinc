<?php

namespace Tests\Unit\Models;

use App\Models\PaymentSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for PaymentSetting model.
 */
class PaymentSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
    }

    /**
     * Test payment setting creation.
     */
    public function test_can_create_payment_setting(): void
    {
        $setting = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => true,
            'is_sandbox' => false,
            'credentials' => [
                'public_key' => 'pk_test_123',
                'secret_key' => 'sk_test_123',
            ],
            'webhook_url' => 'https://example.com/webhook',
        ]);

        $this->assertInstanceOf(PaymentSetting::class, $setting);
        $this->assertEquals('stripe', $setting->gateway);
        $this->assertTrue($setting->is_enabled);
        $this->assertFalse($setting->is_sandbox);
        $this->assertEquals([
            'public_key' => 'pk_test_123',
            'secret_key' => 'sk_test_123',
        ], $setting->credentials);
        $this->assertEquals('https://example.com/webhook', $setting->webhook_url);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Payment gateway setting created') &&
                   $context['gateway'] === 'stripe';
        });
    }

    /**
     * Test status check methods.
     */
    public function test_status_check_methods(): void
    {
        $enabledSetting = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => true,
            'is_sandbox' => false,
        ]);

        $disabledSetting = PaymentSetting::create([
            'gateway' => 'paypal',
            'is_enabled' => false,
            'is_sandbox' => true,
        ]);

        $this->assertTrue($enabledSetting->isEnabled());
        $this->assertFalse($enabledSetting->isSandbox());
        $this->assertTrue($enabledSetting->isProduction());

        $this->assertFalse($disabledSetting->isEnabled());
        $this->assertTrue($disabledSetting->isSandbox());
        $this->assertFalse($disabledSetting->isProduction());
    }

    /**
     * Test status badge class attribute.
     */
    public function test_status_badge_class_attribute(): void
    {
        $enabledProduction = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => true,
            'is_sandbox' => false,
        ]);

        $enabledSandbox = PaymentSetting::create([
            'gateway' => 'paypal',
            'is_enabled' => true,
            'is_sandbox' => true,
        ]);

        $disabled = PaymentSetting::create([
            'gateway' => 'square',
            'is_enabled' => false,
            'is_sandbox' => false,
        ]);

        $this->assertEquals('badge-success', $enabledProduction->status_badge_class);
        $this->assertEquals('badge-warning', $enabledSandbox->status_badge_class);
        $this->assertEquals('badge-secondary', $disabled->status_badge_class);
    }

    /**
     * Test status label attribute.
     */
    public function test_status_label_attribute(): void
    {
        $enabledProduction = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => true,
            'is_sandbox' => false,
        ]);

        $enabledSandbox = PaymentSetting::create([
            'gateway' => 'paypal',
            'is_enabled' => true,
            'is_sandbox' => true,
        ]);

        $disabled = PaymentSetting::create([
            'gateway' => 'square',
            'is_enabled' => false,
            'is_sandbox' => false,
        ]);

        $this->assertEquals('Production', $enabledProduction->status_label);
        $this->assertEquals('Sandbox', $enabledSandbox->status_label);
        $this->assertEquals('Disabled', $disabled->status_label);
    }

    /**
     * Test masked credentials attribute.
     */
    public function test_masked_credentials_attribute(): void
    {
        $setting = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => true,
            'credentials' => [
                'public_key' => 'pk_test_123456789',
                'secret_key' => 'sk_test_987654321',
                'short' => 'abc',
            ],
        ]);

        $masked = $setting->masked_credentials;

        $this->assertEquals('pk_t****6789', $masked['public_key']);
        $this->assertEquals('sk_t****4321', $masked['secret_key']);
        $this->assertEquals('****', $masked['short']);
    }

    /**
     * Test enable method.
     */
    public function test_enable_method(): void
    {
        $setting = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => false,
        ]);

        $result = $setting->enable();

        $this->assertTrue($result);
        $this->assertTrue($setting->fresh()->is_enabled);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Payment gateway enabled') &&
                   $context['gateway'] === 'stripe';
        });
    }

    /**
     * Test disable method.
     */
    public function test_disable_method(): void
    {
        $setting = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => true,
        ]);

        $result = $setting->disable();

        $this->assertTrue($result);
        $this->assertFalse($setting->fresh()->is_enabled);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Payment gateway disabled') &&
                   $context['gateway'] === 'stripe';
        });
    }

    /**
     * Test switch to sandbox method.
     */
    public function test_switch_to_sandbox_method(): void
    {
        $setting = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_sandbox' => false,
        ]);

        $result = $setting->switchToSandbox();

        $this->assertTrue($result);
        $this->assertTrue($setting->fresh()->is_sandbox);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Payment gateway switched to sandbox mode') &&
                   $context['gateway'] === 'stripe';
        });
    }

    /**
     * Test switch to production method.
     */
    public function test_switch_to_production_method(): void
    {
        $setting = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_sandbox' => true,
        ]);

        $result = $setting->switchToProduction();

        $this->assertTrue($result);
        $this->assertFalse($setting->fresh()->is_sandbox);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Payment gateway switched to production mode') &&
                   $context['gateway'] === 'stripe';
        });
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => true,
            'is_sandbox' => false,
        ]);

        PaymentSetting::create([
            'gateway' => 'paypal',
            'is_enabled' => true,
            'is_sandbox' => true,
        ]);

        PaymentSetting::create([
            'gateway' => 'square',
            'is_enabled' => false,
            'is_sandbox' => false,
        ]);

        $this->assertCount(2, PaymentSetting::enabled()->get());
        $this->assertCount(1, PaymentSetting::sandbox()->get());
        $this->assertCount(2, PaymentSetting::production()->get());
        $this->assertCount(1, PaymentSetting::byGateway('stripe')->get());
    }

    /**
     * Test static methods.
     */
    public function test_static_methods(): void
    {
        PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => true,
            'is_sandbox' => false,
            'credentials' => ['key' => 'value'],
        ]);

        PaymentSetting::create([
            'gateway' => 'paypal',
            'is_enabled' => true,
            'is_sandbox' => true,
            'credentials' => ['key' => 'value'],
        ]);

        PaymentSetting::create([
            'gateway' => 'square',
            'is_enabled' => false,
            'is_sandbox' => false,
        ]);

        $this->assertInstanceOf(PaymentSetting::class, PaymentSetting::getByGateway('stripe'));
        $this->assertNull(PaymentSetting::getByGateway('nonexistent'));

        $this->assertTrue(PaymentSetting::isGatewayEnabled('stripe'));
        $this->assertFalse(PaymentSetting::isGatewayEnabled('square'));
        $this->assertFalse(PaymentSetting::isGatewayEnabled('nonexistent'));

        $enabledGateways = PaymentSetting::getEnabledGateways();
        $this->assertContains('stripe', $enabledGateways);
        $this->assertContains('paypal', $enabledGateways);
        $this->assertNotContains('square', $enabledGateways);

        $productionGateways = PaymentSetting::getProductionGateways();
        $this->assertContains('stripe', $productionGateways);
        $this->assertNotContains('paypal', $productionGateways);

        $sandboxGateways = PaymentSetting::getSandboxGateways();
        $this->assertContains('paypal', $sandboxGateways);
        $this->assertNotContains('stripe', $sandboxGateways);

        $credentials = PaymentSetting::getCredentials('stripe');
        $this->assertEquals(['key' => 'value'], $credentials);

        $emptyCredentials = PaymentSetting::getCredentials('nonexistent');
        $this->assertEquals([], $emptyCredentials);
    }

    /**
     * Test statistics.
     */
    public function test_statistics(): void
    {
        PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => true,
            'is_sandbox' => false,
        ]);

        PaymentSetting::create([
            'gateway' => 'paypal',
            'is_enabled' => true,
            'is_sandbox' => true,
        ]);

        PaymentSetting::create([
            'gateway' => 'square',
            'is_enabled' => false,
            'is_sandbox' => false,
        ]);

        $statistics = PaymentSetting::getStatistics();

        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('enabled', $statistics);
        $this->assertArrayHasKey('disabled', $statistics);
        $this->assertArrayHasKey('sandbox', $statistics);
        $this->assertArrayHasKey('production', $statistics);
        $this->assertArrayHasKey('by_gateway', $statistics);

        $this->assertEquals(3, $statistics['total']);
        $this->assertEquals(2, $statistics['enabled']);
        $this->assertEquals(1, $statistics['disabled']);
        $this->assertEquals(1, $statistics['sandbox']);
        $this->assertEquals(2, $statistics['production']);
    }

    /**
     * Test configuration validation.
     */
    public function test_configuration_validation(): void
    {
        $validSetting = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => true,
            'credentials' => ['key' => 'value'],
            'webhook_url' => 'https://example.com/webhook',
        ]);

        $invalidSetting = PaymentSetting::create([
            'gateway' => '',
            'is_enabled' => true,
            'credentials' => [],
            'webhook_url' => 'invalid-url',
        ]);

        $this->assertTrue($validSetting->isValidConfiguration());
        $this->assertEmpty($validSetting->validateConfiguration());

        $this->assertFalse($invalidSetting->isValidConfiguration());
        $errors = $invalidSetting->validateConfiguration();
        $this->assertContains('Gateway name is required', $errors);
        $this->assertContains('Credentials are required for enabled gateways', $errors);
        $this->assertContains('Invalid webhook URL format', $errors);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $setting = PaymentSetting::create([
            'gateway' => 'stripe',
            'is_enabled' => '1',
            'is_sandbox' => '0',
            'credentials' => ['key' => 'value'],
        ]);

        $this->assertIsBool($setting->is_enabled);
        $this->assertIsBool($setting->is_sandbox);
        $this->assertIsArray($setting->credentials);
    }
}
