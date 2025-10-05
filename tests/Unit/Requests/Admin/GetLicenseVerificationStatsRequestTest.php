<?php

namespace Tests\Unit\Requests\Admin;

use App\Http\Requests\Admin\GetLicenseVerificationStatsRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test suite for GetLicenseVerificationStatsRequest.
 *
 * This test suite covers all validation rules, authorization,
 * and data preparation for license verification statistics requests.
 */
class GetLicenseVerificationStatsRequestTest extends TestCase
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
        $request = new GetLicenseVerificationStatsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function non_admin_cannot_authorize_request()
    {
        $request = new GetLicenseVerificationStatsRequest();
        $request->setUserResolver(fn () => $this->user);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function guest_cannot_authorize_request()
    {
        $request = new GetLicenseVerificationStatsRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function validates_days_field()
    {
        $request = new GetLicenseVerificationStatsRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid days
        $validData = ['days' => 30];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test days too small
        $invalidData = ['days' => 0];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('days', $validator->errors()->toArray());

        // Test days too large
        $invalidData = ['days' => 500];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('days', $validator->errors()->toArray());

        // Test non-integer days
        $invalidData = ['days' => 'invalid'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('days', $validator->errors()->toArray());
    }

    /** @test */
    public function allows_null_days_field()
    {
        $request = new GetLicenseVerificationStatsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function prepares_data_for_validation()
    {
        $request = new GetLicenseVerificationStatsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $data = ['days' => '30'];

        $request->replace($data);
        $request->prepareForValidation();

        $this->assertEquals(30, $request->input('days'));
    }

    /** @test */
    public function has_custom_error_messages()
    {
        $request = new GetLicenseVerificationStatsRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('days.integer', $messages);
        $this->assertArrayHasKey('days.min', $messages);
        $this->assertArrayHasKey('days.max', $messages);
    }

    /** @test */
    public function has_custom_attributes()
    {
        $request = new GetLicenseVerificationStatsRequest();
        $attributes = $request->attributes();

        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('days', $attributes);
    }
}
