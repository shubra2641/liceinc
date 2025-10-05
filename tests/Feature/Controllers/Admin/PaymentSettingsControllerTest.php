<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for PaymentSettingsController.
 *
 * This test suite covers all payment settings operations, connection testing,
 * status management, and error handling for payment gateways.
 */
class PaymentSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create();
    }

    /** @test */
    public function admin_can_view_payment_settings()
    {
        PaymentSetting::factory()->create(['gateway' => 'paypal']);
        PaymentSetting::factory()->create(['gateway' => 'stripe']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.payment-settings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.payment-settings.index');
        $response->assertViewHas(['paypalSettings', 'stripeSettings']);
    }

    /** @test */
    public function non_admin_cannot_view_payment_settings()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.payment-settings.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_paypal_settings()
    {
        $paypalSettings = PaymentSetting::factory()->create(['gateway' => 'paypal']);

        $updateData = [
            'gateway' => 'paypal',
            'is_enabled' => true,
            'is_sandbox' => true,
            'credentials' => [
                'client_id' => 'test_client_id',
                'client_secret' => 'test_client_secret',
            ],
            'webhook_url' => 'https://example.com/webhook',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => trans('app.Payment settings updated successfully'),
        ]);

        $paypalSettings->refresh();
        $this->assertTrue($paypalSettings->is_enabled);
        $this->assertTrue($paypalSettings->is_sandbox);
        $this->assertEquals('https://example.com/webhook', $paypalSettings->webhook_url);
    }

    /** @test */
    public function admin_can_update_stripe_settings()
    {
        $stripeSettings = PaymentSetting::factory()->create(['gateway' => 'stripe']);

        $updateData = [
            'gateway' => 'stripe',
            'is_enabled' => true,
            'is_sandbox' => false,
            'credentials' => [
                'publishable_key' => 'pk_test_123',
                'secret_key' => 'sk_test_123',
            ],
            'webhook_url' => 'https://example.com/stripe-webhook',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => trans('app.Payment settings updated successfully'),
        ]);

        $stripeSettings->refresh();
        $this->assertTrue($stripeSettings->is_enabled);
        $this->assertFalse($stripeSettings->is_sandbox);
        $this->assertEquals('https://example.com/stripe-webhook', $stripeSettings->webhook_url);
    }

    /** @test */
    public function payment_settings_update_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gateway', 'credentials']);
    }

    /** @test */
    public function payment_settings_update_validates_gateway_type()
    {
        $updateData = [
            'gateway' => 'invalid_gateway',
            'credentials' => [],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gateway']);
    }

    /** @test */
    public function payment_settings_update_validates_paypal_credentials()
    {
        $updateData = [
            'gateway' => 'paypal',
            'credentials' => [
                'client_id' => '', // Empty client ID
                'client_secret' => 'test_secret',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['credentials.client_id']);
    }

    /** @test */
    public function payment_settings_update_validates_stripe_credentials()
    {
        $updateData = [
            'gateway' => 'stripe',
            'credentials' => [
                'publishable_key' => 'pk_test_123',
                'secret_key' => '', // Empty secret key
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['credentials.secret_key']);
    }

    /** @test */
    public function payment_settings_update_validates_webhook_url()
    {
        $updateData = [
            'gateway' => 'paypal',
            'credentials' => [
                'client_id' => 'test_client_id',
                'client_secret' => 'test_client_secret',
            ],
            'webhook_url' => 'invalid-url',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['webhook_url']);
    }

    /** @test */
    public function payment_settings_update_handles_nonexistent_gateway()
    {
        $updateData = [
            'gateway' => 'paypal',
            'credentials' => [
                'client_id' => 'test_client_id',
                'client_secret' => 'test_client_secret',
            ],
        ];

        // Don't create the payment setting
        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), $updateData);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => trans('app.Payment gateway not found'),
        ]);
    }

    /** @test */
    public function admin_can_test_paypal_connection()
    {
        $testData = [
            'gateway' => 'paypal',
            'credentials' => [
                'client_id' => 'test_client_id',
                'client_secret' => 'test_client_secret',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-settings.test-connection'), $testData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    /** @test */
    public function admin_can_test_stripe_connection()
    {
        $testData = [
            'gateway' => 'stripe',
            'credentials' => [
                'publishable_key' => 'pk_test_123',
                'secret_key' => 'sk_test_123',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-settings.test-connection'), $testData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    /** @test */
    public function connection_test_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-settings.test-connection'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gateway', 'credentials']);
    }

    /** @test */
    public function connection_test_validates_gateway_type()
    {
        $testData = [
            'gateway' => 'invalid_gateway',
            'credentials' => [],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-settings.test-connection'), $testData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gateway']);
    }

    /** @test */
    public function connection_test_handles_unsupported_gateway()
    {
        $testData = [
            'gateway' => 'unsupported',
            'credentials' => [],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-settings.test-connection'), $testData);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => trans('app.Unsupported payment gateway'),
        ]);
    }

    /** @test */
    public function admin_can_get_payment_gateway_status()
    {
        $paypalSettings = PaymentSetting::factory()->create([
            'gateway' => 'paypal',
            'is_enabled' => true,
            'is_sandbox' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.payment-settings.status', 'paypal'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => [
                'gateway' => 'paypal',
                'is_enabled' => true,
                'is_sandbox' => true,
                'has_credentials' => true,
            ],
        ]);
    }

    /** @test */
    public function get_status_handles_invalid_gateway()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payment-settings.status', 'invalid'));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid payment gateway',
        ]);
    }

    /** @test */
    public function get_status_handles_nonexistent_gateway()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payment-settings.status', 'paypal'));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Payment gateway not found',
        ]);
    }

    /** @test */
    public function admin_can_toggle_payment_gateway_status()
    {
        $paypalSettings = PaymentSetting::factory()->create([
            'gateway' => 'paypal',
            'is_enabled' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-settings.toggle-status', 'paypal'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => trans('app.Payment gateway status updated successfully'),
            'is_enabled' => true,
        ]);

        $paypalSettings->refresh();
        $this->assertTrue($paypalSettings->is_enabled);
    }

    /** @test */
    public function toggle_status_handles_invalid_gateway()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-settings.toggle-status', 'invalid'));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid payment gateway',
        ]);
    }

    /** @test */
    public function toggle_status_handles_nonexistent_gateway()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-settings.toggle-status', 'paypal'));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Payment gateway not found',
        ]);
    }

    /** @test */
    public function non_admin_cannot_access_payment_settings_management()
    {
        $routes = [
            'admin.payment-settings.update',
            'admin.payment-settings.test-connection',
            'admin.payment-settings.status',
            'admin.payment-settings.toggle-status',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)
                ->put(route($route, 'paypal'));

            $response->assertStatus(403);
        }
    }

    /** @test */
    public function guest_cannot_access_payment_settings()
    {
        $response = $this->get(route('admin.payment-settings.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function payment_settings_update_handles_database_errors_gracefully()
    {
        $paypalSettings = PaymentSetting::factory()->create(['gateway' => 'paypal']);

        // Mock database error by using invalid data
        $updateData = [
            'gateway' => 'paypal',
            'credentials' => [
                'client_id' => str_repeat('a', 300), // Exceeds database limit
                'client_secret' => 'test_secret',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), $updateData);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => trans('app.Failed to update payment settings'),
        ]);
    }

    /** @test */
    public function connection_test_handles_external_api_errors_gracefully()
    {
        $testData = [
            'gateway' => 'paypal',
            'credentials' => [
                'client_id' => 'invalid_client_id',
                'client_secret' => 'invalid_client_secret',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-settings.test-connection'), $testData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    /** @test */
    public function payment_settings_update_validates_boolean_fields()
    {
        $paypalSettings = PaymentSetting::factory()->create(['gateway' => 'paypal']);

        $updateData = [
            'gateway' => 'paypal',
            'is_enabled' => 'invalid_boolean',
            'is_sandbox' => 'invalid_boolean',
            'credentials' => [
                'client_id' => 'test_client_id',
                'client_secret' => 'test_client_secret',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['is_enabled', 'is_sandbox']);
    }

    /** @test */
    public function payment_settings_update_validates_credentials_array()
    {
        $updateData = [
            'gateway' => 'paypal',
            'credentials' => 'not_an_array',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-settings.update'), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['credentials']);
    }

    /** @test */
    public function connection_test_validates_credentials_array()
    {
        $testData = [
            'gateway' => 'paypal',
            'credentials' => 'not_an_array',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-settings.test-connection'), $testData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['credentials']);
    }
}
