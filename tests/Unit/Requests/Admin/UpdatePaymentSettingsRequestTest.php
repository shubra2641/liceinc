<?php

namespace Tests\Unit\Requests\Admin;

use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test suite for UpdatePaymentSettingsRequest.
 *
 * This test suite covers all validation rules, authorization,
 * and data preparation for payment settings update requests.
 */
class UpdatePaymentSettingsRequestTest extends TestCase
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
    public function admin_can_authorize_request()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function non_admin_cannot_authorize_request()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->user);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function guest_cannot_authorize_request()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function validates_required_fields()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validator = Validator::make([], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gateway', $validator->errors()->toArray());
        $this->assertArrayHasKey('credentials', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_gateway_field()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid gateway
        $validData = [
            'gateway' => 'paypal',
            'credentials' => ['client_id' => 'test', 'client_secret' => 'test'],
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid gateway
        $invalidData = [
            'gateway' => 'invalid_gateway',
            'credentials' => ['client_id' => 'test', 'client_secret' => 'test'],
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gateway', $validator->errors()->toArray());

        // Test empty gateway
        $invalidData = [
            'gateway' => '',
            'credentials' => ['client_id' => 'test', 'client_secret' => 'test'],
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gateway', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_credentials_field()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid credentials array
        $validData = [
            'gateway' => 'paypal',
            'credentials' => ['client_id' => 'test', 'client_secret' => 'test'],
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid credentials (not array)
        $invalidData = [
            'gateway' => 'paypal',
            'credentials' => 'not_an_array',
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('credentials', $validator->errors()->toArray());

        // Test empty credentials
        $invalidData = [
            'gateway' => 'paypal',
            'credentials' => [],
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('credentials', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_paypal_credentials()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid PayPal credentials
        $validData = [
            'gateway' => 'paypal',
            'credentials' => [
                'client_id' => 'test_client_id',
                'client_secret' => 'test_client_secret',
            ],
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test missing PayPal client_id
        $invalidData = [
            'gateway' => 'paypal',
            'credentials' => [
                'client_secret' => 'test_client_secret',
            ],
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('credentials.client_id', $validator->errors()->toArray());

        // Test missing PayPal client_secret
        $invalidData = [
            'gateway' => 'paypal',
            'credentials' => [
                'client_id' => 'test_client_id',
            ],
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('credentials.client_secret', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_stripe_credentials()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid Stripe credentials
        $validData = [
            'gateway' => 'stripe',
            'credentials' => [
                'publishable_key' => 'pk_test_123',
                'secret_key' => 'sk_test_123',
            ],
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test missing Stripe publishable_key
        $invalidData = [
            'gateway' => 'stripe',
            'credentials' => [
                'secret_key' => 'sk_test_123',
            ],
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('credentials.publishable_key', $validator->errors()->toArray());

        // Test missing Stripe secret_key
        $invalidData = [
            'gateway' => 'stripe',
            'credentials' => [
                'publishable_key' => 'pk_test_123',
            ],
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('credentials.secret_key', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_boolean_fields()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid boolean values
        $validData = [
            'gateway' => 'paypal',
            'credentials' => ['client_id' => 'test', 'client_secret' => 'test'],
            'is_enabled' => true,
            'is_sandbox' => false,
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid boolean values
        $invalidData = [
            'gateway' => 'paypal',
            'credentials' => ['client_id' => 'test', 'client_secret' => 'test'],
            'is_enabled' => 'invalid',
            'is_sandbox' => 'invalid',
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('is_enabled', $validator->errors()->toArray());
        $this->assertArrayHasKey('is_sandbox', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_webhook_url_field()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid webhook URL
        $validData = [
            'gateway' => 'paypal',
            'credentials' => ['client_id' => 'test', 'client_secret' => 'test'],
            'webhook_url' => 'https://example.com/webhook',
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid webhook URL
        $invalidData = [
            'gateway' => 'paypal',
            'credentials' => ['client_id' => 'test', 'client_secret' => 'test'],
            'webhook_url' => 'invalid-url',
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('webhook_url', $validator->errors()->toArray());

        // Test webhook URL too long
        $invalidData = [
            'gateway' => 'paypal',
            'credentials' => ['client_id' => 'test', 'client_secret' => 'test'],
            'webhook_url' => 'https://example.com/'.str_repeat('a', 1000),
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('webhook_url', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_credentials_string_length()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test credentials too long
        $invalidData = [
            'gateway' => 'paypal',
            'credentials' => [
                'client_id' => str_repeat('a', 256), // Too long
                'client_secret' => 'test_client_secret',
            ],
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('credentials.client_id', $validator->errors()->toArray());
    }

    /** @test */
    public function prepares_data_for_validation()
    {
        $request = new UpdatePaymentSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $data = [
            'gateway' => '  paypal  ',
            'webhook_url' => '  https://example.com/webhook  ',
            'credentials' => [
                'client_id' => '  test_client_id  ',
                'client_secret' => '  test_client_secret  ',
            ],
            'is_enabled' => '1',
            'is_sandbox' => '0',
        ];

        $request->replace($data);
        $request->prepareForValidation();

        $this->assertEquals('paypal', $request->input('gateway'));
        $this->assertEquals('https://example.com/webhook', $request->input('webhook_url'));
        $this->assertEquals('test_client_id', $request->input('credentials.client_id'));
        $this->assertEquals('test_client_secret', $request->input('credentials.client_secret'));
        $this->assertTrue($request->input('is_enabled'));
        $this->assertFalse($request->input('is_sandbox'));
    }

    /** @test */
    public function has_custom_error_messages()
    {
        $request = new UpdatePaymentSettingsRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('gateway.required', $messages);
        $this->assertArrayHasKey('gateway.in', $messages);
        $this->assertArrayHasKey('credentials.required', $messages);
        $this->assertArrayHasKey('credentials.array', $messages);
        $this->assertArrayHasKey('webhook_url.url', $messages);
    }

    /** @test */
    public function has_custom_attributes()
    {
        $request = new UpdatePaymentSettingsRequest();
        $attributes = $request->attributes();

        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('gateway', $attributes);
        $this->assertArrayHasKey('credentials', $attributes);
        $this->assertArrayHasKey('webhook_url', $attributes);
        $this->assertArrayHasKey('credentials.client_id', $attributes);
        $this->assertArrayHasKey('credentials.secret_key', $attributes);
    }
}
