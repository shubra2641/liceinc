<?php

namespace Tests\Unit\Requests\Admin;

use App\Http\Requests\Admin\UpdateLicenseRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test suite for UpdateLicenseRequest.
 *
 * This test suite covers all validation rules, authorization,
 * and data preparation for license update requests.
 */
class UpdateLicenseRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    /** @test */
    public function admin_can_authorize_request()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function non_admin_cannot_authorize_request()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->user);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function guest_cannot_authorize_request()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function validates_required_fields()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validator = Validator::make([], $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('user_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('product_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_user_id_field()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid user ID
        $validData = ['user_id' => $this->user->id];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid user ID
        $invalidData = ['user_id' => 99999];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('user_id', $validator->errors()->toArray());

        // Test non-integer user ID
        $invalidData = ['user_id' => 'invalid'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('user_id', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_product_id_field()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid product ID
        $validData = ['product_id' => $this->product->id];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid product ID
        $invalidData = ['product_id' => 99999];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('product_id', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_license_type_field()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid license types
        $validTypes = ['single', 'multi', 'developer', 'extended'];
        foreach ($validTypes as $type) {
            $validData = ['license_type' => $type];
            $validator = Validator::make($validData, $request->rules(), $request->messages());
            $this->assertFalse($validator->fails());
        }

        // Test invalid license type
        $invalidData = ['license_type' => 'invalid_type'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('license_type', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_status_field()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid statuses
        $validStatuses = ['active', 'inactive', 'suspended', 'expired'];
        foreach ($validStatuses as $status) {
            $validData = ['status' => $status];
            $validator = Validator::make($validData, $request->rules(), $request->messages());
            $this->assertFalse($validator->fails());
        }

        // Test invalid status
        $invalidData = ['status' => 'invalid_status'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_max_domains_field()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid max domains
        $validData = ['max_domains' => 5];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test max domains too small
        $invalidData = ['max_domains' => 0];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('max_domains', $validator->errors()->toArray());

        // Test max domains too large
        $invalidData = ['max_domains' => 1001];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('max_domains', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_expires_at_field()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid date
        $validData = ['expires_at' => now()->addDays(30)->format('Y-m-d')];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid date format
        $invalidData = ['expires_at' => 'invalid-date'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('expires_at', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_notes_field()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid notes
        $validData = ['notes' => 'Valid notes'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test notes too long
        $invalidData = ['notes' => str_repeat('a', 1001)];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('notes', $validator->errors()->toArray());
    }

    /** @test */
    public function allows_null_optional_fields()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'status' => 'active',
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function prepares_data_for_validation()
    {
        $request = new UpdateLicenseRequest();
        $request->setUserResolver(fn () => $this->admin);

        $data = [
            'user_id' => '123',
            'product_id' => '456',
            'max_domains' => '5',
            'license_type' => '  single  ',
            'status' => '  active  ',
            'notes' => '  test notes  ',
        ];

        $request->replace($data);
        $request->prepareForValidation();

        $this->assertEquals(123, $request->input('user_id'));
        $this->assertEquals(456, $request->input('product_id'));
        $this->assertEquals(5, $request->input('max_domains'));
        $this->assertEquals('single', $request->input('license_type'));
        $this->assertEquals('active', $request->input('status'));
        $this->assertEquals('test notes', $request->input('notes'));
    }

    /** @test */
    public function has_custom_error_messages()
    {
        $request = new UpdateLicenseRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('user_id.required', $messages);
        $this->assertArrayHasKey('product_id.required', $messages);
        $this->assertArrayHasKey('status.required', $messages);
    }

    /** @test */
    public function has_custom_attributes()
    {
        $request = new UpdateLicenseRequest();
        $attributes = $request->attributes();

        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('user_id', $attributes);
        $this->assertArrayHasKey('product_id', $attributes);
        $this->assertArrayHasKey('status', $attributes);
    }
}
