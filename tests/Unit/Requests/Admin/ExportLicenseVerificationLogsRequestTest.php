<?php

namespace Tests\Unit\Requests\Admin;

use App\Http\Requests\Admin\ExportLicenseVerificationLogsRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test suite for ExportLicenseVerificationLogsRequest.
 *
 * This test suite covers all validation rules, authorization,
 * and data preparation for license verification log export requests.
 */
class ExportLicenseVerificationLogsRequestTest extends TestCase
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
        $request = new ExportLicenseVerificationLogsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function non_admin_cannot_authorize_request()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $request->setUserResolver(fn () => $this->user);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function guest_cannot_authorize_request()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function validates_status_field()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid status
        $validData = ['status' => 'success'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid status
        $invalidData = ['status' => 'invalid_status'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());

        // Test non-string status
        $invalidData = ['status' => 123];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_source_field()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid source
        $validData = ['source' => 'install'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid source
        $invalidData = ['source' => 'invalid_source'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('source', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_domain_field()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid domain
        $validData = ['domain' => 'example.com'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test domain too long
        $invalidData = ['domain' => str_repeat('a', 256)];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('domain', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_ip_field()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid IP
        $validData = ['ip' => '192.168.1.1'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test IP too long
        $invalidData = ['ip' => str_repeat('a', 50)];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ip', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_date_fields()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid dates
        $validData = [
            'date_from' => '2023-01-01',
            'date_to' => '2023-12-31',
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid date format
        $invalidData = ['date_from' => 'invalid-date'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date_from', $validator->errors()->toArray());

        // Test future date
        $invalidData = ['date_from' => '2030-01-01'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date_from', $validator->errors()->toArray());

        // Test date_to before date_from
        $invalidData = [
            'date_from' => '2023-12-31',
            'date_to' => '2023-01-01',
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date_to', $validator->errors()->toArray());
    }

    /** @test */
    public function allows_null_fields()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function prepares_data_for_validation()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $data = [
            'status' => '  success  ',
            'source' => '  install  ',
            'domain' => '  example.com  ',
            'ip' => '  192.168.1.1  ',
            'date_from' => '  2023-01-01  ',
            'date_to' => '  2023-12-31  ',
        ];

        $request->replace($data);
        $request->prepareForValidation();

        $this->assertEquals('success', $request->input('status'));
        $this->assertEquals('install', $request->input('source'));
        $this->assertEquals('example.com', $request->input('domain'));
        $this->assertEquals('192.168.1.1', $request->input('ip'));
        $this->assertEquals('2023-01-01', $request->input('date_from'));
        $this->assertEquals('2023-12-31', $request->input('date_to'));
    }

    /** @test */
    public function has_custom_error_messages()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('status.in', $messages);
        $this->assertArrayHasKey('source.in', $messages);
        $this->assertArrayHasKey('domain.max', $messages);
        $this->assertArrayHasKey('ip.max', $messages);
        $this->assertArrayHasKey('date_from.date', $messages);
        $this->assertArrayHasKey('date_to.date', $messages);
    }

    /** @test */
    public function has_custom_attributes()
    {
        $request = new ExportLicenseVerificationLogsRequest();
        $attributes = $request->attributes();

        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('status', $attributes);
        $this->assertArrayHasKey('source', $attributes);
        $this->assertArrayHasKey('domain', $attributes);
        $this->assertArrayHasKey('ip', $attributes);
        $this->assertArrayHasKey('date_from', $attributes);
        $this->assertArrayHasKey('date_to', $attributes);
    }
}
