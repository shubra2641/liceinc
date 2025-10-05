<?php

namespace Tests\Unit\Requests\Admin;

use App\Http\Requests\Admin\StoreUpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test suite for StoreUpdateEmailTemplateRequest.
 *
 * This test suite covers all validation rules, authorization,
 * and data preparation for email template creation and update requests.
 */
class StoreUpdateEmailTemplateRequestTest extends TestCase
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
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function non_admin_cannot_authorize_request()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->user);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function guest_cannot_authorize_request()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function validates_required_fields()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validator = Validator::make([], $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('subject', $validator->errors()->toArray());
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
        $this->assertArrayHasKey('category', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_name_field()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid name
        $validData = ['name' => 'Valid Template Name'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test name too long
        $invalidData = ['name' => str_repeat('a', 256)];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());

        // Test empty name
        $invalidData = ['name' => ''];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_name_uniqueness_for_create()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        EmailTemplate::factory()->create(['name' => 'Existing Template']);

        // Test duplicate name
        $invalidData = ['name' => 'Existing Template'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_name_uniqueness_for_update()
    {
        $template1 = EmailTemplate::factory()->create(['name' => 'Template 1']);
        $template2 = EmailTemplate::factory()->create(['name' => 'Template 2']);

        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);
        $request->setRouteResolver(function () use ($template2) {
            $route = new \Illuminate\Routing\Route('PUT', '/test', []);
            $route->setParameter('emailTemplate', $template2);

            return $route;
        });

        // Test duplicate name (should fail)
        $invalidData = ['name' => 'Template 1'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());

        // Test same name for same template (should pass)
        $validData = ['name' => 'Template 2'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function validates_subject_field()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid subject
        $validData = ['subject' => 'Valid Email Subject'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test subject too long
        $invalidData = ['subject' => str_repeat('a', 256)];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('subject', $validator->errors()->toArray());

        // Test empty subject
        $invalidData = ['subject' => ''];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('subject', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_body_field()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid body
        $validData = ['body' => 'This is a valid email body content with sufficient length.'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test body too short
        $invalidData = ['body' => 'Short'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('body', $validator->errors()->toArray());

        // Test empty body
        $invalidData = ['body' => ''];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_type_field()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid types
        $validTypes = ['user', 'admin'];
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

        // Test empty type
        $invalidData = ['type' => ''];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_category_field()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid category
        $validData = ['category' => 'registration'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test category too long
        $invalidData = ['category' => str_repeat('a', 256)];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category', $validator->errors()->toArray());

        // Test empty category
        $invalidData = ['category' => ''];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_variables_field()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid variables array
        $validData = ['variables' => ['user_name', 'site_name', 'custom_field']];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid variables (not array)
        $invalidData = ['variables' => 'not_an_array'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('variables', $validator->errors()->toArray());

        // Test variables with invalid values
        $invalidData = ['variables' => [123, 'valid_string', null]];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('variables.0', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_is_active_field()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid boolean values
        $validValues = [true, false, 1, 0, '1', '0'];
        foreach ($validValues as $value) {
            $validData = ['is_active' => $value];
            $validator = Validator::make($validData, $request->rules(), $request->messages());
            $this->assertFalse($validator->fails());
        }

        // Test invalid boolean value
        $invalidData = ['is_active' => 'invalid_boolean'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('is_active', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_description_field()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid description
        $validData = ['description' => 'Valid description'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test description too long
        $invalidData = ['description' => str_repeat('a', 1001)];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());

        // Test null description (should pass)
        $validData = ['description' => null];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function allows_null_optional_fields()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => 'This is a valid email body content.',
            'type' => 'user',
            'category' => 'registration',
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function prepares_data_for_validation()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $request->setUserResolver(fn () => $this->admin);

        $data = [
            'name' => '  Test Template  ',
            'subject' => '  Test Subject  ',
            'body' => '  Test body content  ',
            'type' => '  user  ',
            'category' => '  registration  ',
            'description' => '  Test description  ',
            'is_active' => '1',
            'variables' => ['  user_name  ', '  site_name  ', '', '  custom_field  '],
        ];

        $request->replace($data);
        $request->prepareForValidation();

        $this->assertEquals('Test Template', $request->input('name'));
        $this->assertEquals('Test Subject', $request->input('subject'));
        $this->assertEquals('Test body content', $request->input('body'));
        $this->assertEquals('user', $request->input('type'));
        $this->assertEquals('registration', $request->input('category'));
        $this->assertEquals('Test description', $request->input('description'));
        $this->assertTrue($request->input('is_active'));
        $this->assertEquals(['user_name', 'site_name', 'custom_field'], $request->input('variables'));
    }

    /** @test */
    public function has_custom_error_messages()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('subject.required', $messages);
        $this->assertArrayHasKey('body.required', $messages);
        $this->assertArrayHasKey('type.required', $messages);
        $this->assertArrayHasKey('category.required', $messages);
    }

    /** @test */
    public function has_custom_attributes()
    {
        $request = new StoreUpdateEmailTemplateRequest();
        $attributes = $request->attributes();

        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('subject', $attributes);
        $this->assertArrayHasKey('body', $attributes);
        $this->assertArrayHasKey('type', $attributes);
        $this->assertArrayHasKey('category', $attributes);
    }
}
