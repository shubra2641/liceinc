<?php

namespace Tests\Unit\Requests\Admin;

use App\Http\Requests\Admin\SendTestEmailRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test suite for SendTestEmailRequest.
 *
 * This test suite covers all validation rules, authorization,
 * and data preparation for test email sending requests.
 */
class SendTestEmailRequestTest extends TestCase
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
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function non_admin_cannot_authorize_request()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->user);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function guest_cannot_authorize_request()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function validates_required_fields()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validator = Validator::make([], $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('test_email', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_test_email_field()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid email
        $validData = ['test_email' => 'test@example.com'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid email format
        $invalidData = ['test_email' => 'invalid-email'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('test_email', $validator->errors()->toArray());

        // Test empty email
        $invalidData = ['test_email' => ''];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('test_email', $validator->errors()->toArray());

        // Test email too long
        $invalidData = ['test_email' => str_repeat('a', 250).'@example.com'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('test_email', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_test_data_field()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid test data array
        $validData = [
            'test_email' => 'test@example.com',
            'test_data' => [
                'user_name' => 'Test User',
                'custom_field' => 'Custom Value',
            ],
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid test data (not array)
        $invalidData = [
            'test_email' => 'test@example.com',
            'test_data' => 'not_an_array',
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('test_data', $validator->errors()->toArray());

        // Test test data with invalid values
        $invalidData = [
            'test_email' => 'test@example.com',
            'test_data' => [
                'user_name' => 123, // Invalid: not string
                'custom_field' => null, // Invalid: not string
            ],
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('test_data.0', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_test_data_string_length()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test test data with value too long
        $invalidData = [
            'test_email' => 'test@example.com',
            'test_data' => [
                'user_name' => str_repeat('a', 1001), // Too long
            ],
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('test_data.0', $validator->errors()->toArray());
    }

    /** @test */
    public function allows_null_test_data()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [
            'test_email' => 'test@example.com',
            'test_data' => null,
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function allows_empty_test_data()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [
            'test_email' => 'test@example.com',
            'test_data' => [],
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function prepares_data_for_validation()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        $data = [
            'test_email' => '  TEST@EXAMPLE.COM  ',
            'test_data' => [
                '  user_name  ' => '  Test User  ',
                '  custom_field  ' => '  Custom Value  ',
                '' => '  Empty Key  ',
                '  valid_key  ' => '',
            ],
        ];

        $request->replace($data);
        $request->prepareForValidation();

        $this->assertEquals('test@example.com', $request->input('test_email'));

        $testData = $request->input('test_data');
        $this->assertArrayHasKey('user_name', $testData);
        $this->assertEquals('Test User', $testData['user_name']);
        $this->assertArrayHasKey('custom_field', $testData);
        $this->assertEquals('Custom Value', $testData['custom_field']);
        $this->assertArrayHasKey('valid_key', $testData);
        $this->assertEquals('', $testData['valid_key']);
        $this->assertArrayNotHasKey('', $testData); // Empty keys should be removed
    }

    /** @test */
    public function handles_mixed_test_data_types()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        $data = [
            'test_email' => 'test@example.com',
            'test_data' => [
                'string_value' => 'Valid String',
                'number_value' => 123,
                'boolean_value' => true,
                'null_value' => null,
                'array_value' => ['nested' => 'value'],
            ],
        ];

        $request->replace($data);
        $request->prepareForValidation();

        $testData = $request->input('test_data');
        $this->assertEquals('Valid String', $testData['string_value']);
        $this->assertEquals('', $testData['number_value']); // Non-string values become empty strings
        $this->assertEquals('', $testData['boolean_value']);
        $this->assertEquals('', $testData['null_value']);
        $this->assertEquals('', $testData['array_value']);
    }

    /** @test */
    public function has_custom_error_messages()
    {
        $request = new SendTestEmailRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('test_email.required', $messages);
        $this->assertArrayHasKey('test_email.email', $messages);
        $this->assertArrayHasKey('test_email.max', $messages);
        $this->assertArrayHasKey('test_data.array', $messages);
        $this->assertArrayHasKey('test_data.*.string', $messages);
        $this->assertArrayHasKey('test_data.*.max', $messages);
    }

    /** @test */
    public function has_custom_attributes()
    {
        $request = new SendTestEmailRequest();
        $attributes = $request->attributes();

        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('test_email', $attributes);
        $this->assertArrayHasKey('test_data', $attributes);
        $this->assertArrayHasKey('test_data.*', $attributes);
    }

    /** @test */
    public function validates_various_email_formats()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validEmails = [
            'test@example.com',
            'user.name@domain.co.uk',
            'test+tag@example.org',
            'user123@test-domain.com',
            'a@b.co',
        ];

        foreach ($validEmails as $email) {
            $validData = ['test_email' => $email];
            $validator = Validator::make($validData, $request->rules(), $request->messages());
            $this->assertFalse($validator->fails(), "Email '{$email}' should be valid");
        }

        $invalidEmails = [
            'invalid-email',
            '@example.com',
            'test@',
            'test.example.com',
            'test@.com',
            'test@example.',
        ];

        foreach ($invalidEmails as $email) {
            $invalidData = ['test_email' => $email];
            $validator = Validator::make($invalidData, $request->rules(), $request->messages());
            $this->assertTrue($validator->fails(), "Email '{$email}' should be invalid");
        }
    }

    /** @test */
    public function handles_large_test_data_arrays()
    {
        $request = new SendTestEmailRequest();
        $request->setUserResolver(fn () => $this->admin);

        $largeTestData = [];
        for ($i = 0; $i < 100; $i++) {
            $largeTestData["field_{$i}"] = "Value {$i}";
        }

        $validData = [
            'test_email' => 'test@example.com',
            'test_data' => $largeTestData,
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }
}
