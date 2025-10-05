<?php

namespace Tests\Unit\Requests\Admin;

use App\Http\Requests\Admin\StoreInvoiceRequest;
use App\Models\License;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test suite for StoreInvoiceRequest.
 *
 * This test suite covers all validation rules, authorization,
 * and data preparation for invoice creation requests.
 */
class StoreInvoiceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected License $license;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create();
        $this->license = License::factory()->create();
    }

    /** @test */
    public function admin_can_authorize_request()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function non_admin_cannot_authorize_request()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->user);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function guest_cannot_authorize_request()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function validates_required_fields()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validator = Validator::make([], $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('user_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
        $this->assertArrayHasKey('currency', $validator->errors()->toArray());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_user_id_field()
    {
        $request = new StoreInvoiceRequest();
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
    public function validates_license_id_field_for_regular_invoice()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid license ID
        $validData = ['license_id' => $this->license->id];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid license ID
        $invalidData = ['license_id' => 99999];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('license_id', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_license_id_field_for_custom_invoice()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test custom invoice (license_id = 'custom')
        $validData = ['license_id' => 'custom'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function validates_type_field()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid types
        $validTypes = ['initial', 'renewal', 'upgrade', 'custom'];
        foreach ($validTypes as $type) {
            $validData = ['type' => $type];
            $validator = Validator::make($validData, $request->rules(), $request->messages());
            $this->assertFalse($validator->fails());
        }

        // Test invalid type
        $invalidData = ['type' => 'invalid_type'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_amount_field()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid amount
        $validData = ['amount' => 99.99];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test negative amount
        $invalidData = ['amount' => -10];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());

        // Test amount too large
        $invalidData = ['amount' => 1000000];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_currency_field()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid currencies
        $validCurrencies = ['USD', 'EUR', 'GBP', 'AED', 'SAR'];
        foreach ($validCurrencies as $currency) {
            $validData = ['currency' => $currency];
            $validator = Validator::make($validData, $request->rules(), $request->messages());
            $this->assertFalse($validator->fails());
        }

        // Test invalid currency
        $invalidData = ['currency' => 'INVALID'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('currency', $validator->errors()->toArray());

        // Test currency too short
        $invalidData = ['currency' => 'US'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('currency', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_status_field()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid statuses
        $validStatuses = ['pending', 'paid', 'overdue', 'cancelled'];
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
    public function validates_due_date_field()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid future date
        $validData = ['due_date' => now()->addDays(30)->format('Y-m-d')];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test past date
        $invalidData = ['due_date' => now()->subDays(1)->format('Y-m-d')];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('due_date', $validator->errors()->toArray());

        // Test invalid date format
        $invalidData = ['due_date' => 'invalid-date'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('due_date', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_paid_at_field()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid date
        $validData = ['paid_at' => now()->format('Y-m-d')];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid date format
        $invalidData = ['paid_at' => 'invalid-date'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('paid_at', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_notes_field()
    {
        $request = new StoreInvoiceRequest();
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
    public function validates_custom_invoice_fields()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid custom invoice type
        $validTypes = ['one_time', 'monthly', 'quarterly', 'semi_annual', 'annual', 'custom_recurring'];
        foreach ($validTypes as $type) {
            $validData = [
                'license_id' => 'custom',
                'custom_invoice_type' => $type,
            ];
            $validator = Validator::make($validData, $request->rules(), $request->messages());
            $this->assertFalse($validator->fails());
        }

        // Test invalid custom invoice type
        $invalidData = [
            'license_id' => 'custom',
            'custom_invoice_type' => 'invalid_type',
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('custom_invoice_type', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_custom_product_name_field()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid custom product name
        $validData = [
            'license_id' => 'custom',
            'custom_product_name' => 'Valid Product Name',
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test custom product name too long
        $invalidData = [
            'license_id' => 'custom',
            'custom_product_name' => str_repeat('a', 256),
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('custom_product_name', $validator->errors()->toArray());
    }

    /** @test */
    public function allows_null_optional_fields()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [
            'user_id' => $this->user->id,
            'license_id' => $this->license->id,
            'type' => 'initial',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function prepares_data_for_validation()
    {
        $request = new StoreInvoiceRequest();
        $request->setUserResolver(fn () => $this->admin);

        $data = [
            'user_id' => '123',
            'license_id' => '456',
            'amount' => '99.99',
            'type' => '  initial  ',
            'currency' => '  usd  ',
            'status' => '  pending  ',
            'notes' => '  test notes  ',
            'custom_invoice_type' => '  one_time  ',
            'custom_product_name' => '  test product  ',
        ];

        $request->replace($data);
        $request->prepareForValidation();

        $this->assertEquals(123, $request->input('user_id'));
        $this->assertEquals(456, $request->input('license_id'));
        $this->assertEquals(99.99, $request->input('amount'));
        $this->assertEquals('initial', $request->input('type'));
        $this->assertEquals('USD', $request->input('currency'));
        $this->assertEquals('pending', $request->input('status'));
        $this->assertEquals('test notes', $request->input('notes'));
        $this->assertEquals('one_time', $request->input('custom_invoice_type'));
        $this->assertEquals('test product', $request->input('custom_product_name'));
    }

    /** @test */
    public function has_custom_error_messages()
    {
        $request = new StoreInvoiceRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('user_id.required', $messages);
        $this->assertArrayHasKey('type.required', $messages);
        $this->assertArrayHasKey('amount.required', $messages);
        $this->assertArrayHasKey('currency.required', $messages);
        $this->assertArrayHasKey('status.required', $messages);
    }

    /** @test */
    public function has_custom_attributes()
    {
        $request = new StoreInvoiceRequest();
        $attributes = $request->attributes();

        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('user_id', $attributes);
        $this->assertArrayHasKey('type', $attributes);
        $this->assertArrayHasKey('amount', $attributes);
        $this->assertArrayHasKey('currency', $attributes);
        $this->assertArrayHasKey('status', $attributes);
    }
}
