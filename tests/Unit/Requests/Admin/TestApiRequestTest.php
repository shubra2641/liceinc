<?php

namespace Tests\Unit\Requests\Admin;

use App\Http\Requests\Admin\TestApiRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test suite for TestApiRequest.
 *
 * Tests validation rules, authorization, and data preparation
 * for API testing requests.
 */
class TestApiRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->customer = User::factory()->create();
        $this->customer->assignRole('customer');
    }

    /**
     * Test admin can authorize request.
     */
    public function test_admin_can_authorize_request(): void
    {
        $request = new TestApiRequest();
        $request->setUserResolver(fn () => $this->admin);

        $this->assertTrue($request->authorize());
    }

    /**
     * Test customer cannot authorize request.
     */
    public function test_customer_cannot_authorize_request(): void
    {
        $request = new TestApiRequest();
        $request->setUserResolver(fn () => $this->customer);

        $this->assertFalse($request->authorize());
    }

    /**
     * Test guest cannot authorize request.
     */
    public function test_guest_cannot_authorize_request(): void
    {
        $request = new TestApiRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    /**
     * Test validation rules.
     */
    public function test_validation_rules(): void
    {
        $request = new TestApiRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('token', $rules);
        $this->assertContains('required', $rules['token']);
        $this->assertContains('string', $rules['token']);
        $this->assertContains('min:10', $rules['token']);
    }

    /**
     * Test custom error messages.
     */
    public function test_custom_error_messages(): void
    {
        $request = new TestApiRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('token.required', $messages);
        $this->assertEquals('API token is required for testing.', $messages['token.required']);

        $this->assertArrayHasKey('token.string', $messages);
        $this->assertEquals('API token must be a string.', $messages['token.string']);

        $this->assertArrayHasKey('token.min', $messages);
        $this->assertEquals('API token must be at least 10 characters long.', $messages['token.min']);
    }

    /**
     * Test custom attributes.
     */
    public function test_custom_attributes(): void
    {
        $request = new TestApiRequest();
        $attributes = $request->attributes();

        $this->assertArrayHasKey('token', $attributes);
        $this->assertEquals('API Token', $attributes['token']);
    }

    /**
     * Test data preparation trims token.
     */
    public function test_data_preparation_trims_token(): void
    {
        $request = new TestApiRequest();
        $request->merge([
            'token' => '  test-token-1234567890  ',
        ]);

        $request->prepareForValidation();

        $this->assertEquals('test-token-1234567890', $request->input('token'));
    }

    /**
     * Test validation with valid data.
     */
    public function test_validation_with_valid_data(): void
    {
        $request = new TestApiRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [
            'token' => 'test-token-1234567890',
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertTrue($validator->passes());
    }

    /**
     * Test validation with invalid data.
     */
    public function test_validation_with_invalid_data(): void
    {
        $request = new TestApiRequest();
        $request->setUserResolver(fn () => $this->admin);

        $invalidData = [
            'token' => 'short', // Too short
        ];

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertFalse($validator->passes());

        $errors = $validator->errors();
        $this->assertTrue($errors->has('token'));
    }

    /**
     * Test validation with missing token.
     */
    public function test_validation_with_missing_token(): void
    {
        $request = new TestApiRequest();
        $request->setUserResolver(fn () => $this->admin);

        $invalidData = [
            // No token provided
        ];

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertFalse($validator->passes());

        $errors = $validator->errors();
        $this->assertTrue($errors->has('token'));
    }

    /**
     * Test validation with non-string token.
     */
    public function test_validation_with_non_string_token(): void
    {
        $request = new TestApiRequest();
        $request->setUserResolver(fn () => $this->admin);

        $invalidData = [
            'token' => 1234567890, // Not a string
        ];

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertFalse($validator->passes());

        $errors = $validator->errors();
        $this->assertTrue($errors->has('token'));
    }

    /**
     * Test validation with exactly minimum length token.
     */
    public function test_validation_with_exactly_minimum_length_token(): void
    {
        $request = new TestApiRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [
            'token' => '1234567890', // Exactly 10 characters
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertTrue($validator->passes());
    }

    /**
     * Test validation with long token.
     */
    public function test_validation_with_long_token(): void
    {
        $request = new TestApiRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [
            'token' => str_repeat('a', 100), // 100 characters
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertTrue($validator->passes());
    }
}
