<?php

namespace Tests\Unit\Requests\Admin;

use App\Http\Requests\Admin\StoreUpdateProductCategoryRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test suite for StoreUpdateProductCategoryRequest.
 *
 * This test suite covers all validation rules, authorization,
 * and data preparation for product category requests.
 */
class StoreUpdateProductCategoryRequestTest extends TestCase
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
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function non_admin_cannot_authorize_request()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->user);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function guest_cannot_authorize_request()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function validates_required_fields()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validator = Validator::make([], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_name_field()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid name
        $validData = ['name' => 'Valid Category Name'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test empty name
        $invalidData = ['name' => ''];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());

        // Test name too long
        $invalidData = ['name' => str_repeat('a', 256)];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_slug_field()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid slug
        $validData = ['name' => 'Test Category', 'slug' => 'valid-slug-123'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid slug with uppercase
        $invalidData = ['name' => 'Test Category', 'slug' => 'Invalid-Slug'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());

        // Test invalid slug with special characters
        $invalidData = ['name' => 'Test Category', 'slug' => 'invalid_slug!'];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_description_field()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid description
        $validData = ['name' => 'Test Category', 'description' => 'Valid description'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test description too long
        $invalidData = ['name' => 'Test Category', 'description' => str_repeat('a', 2001)];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_image_field()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid image
        $validImage = UploadedFile::fake()->image('category.jpg', 800, 600);
        $validData = ['name' => 'Test Category', 'image' => $validImage];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000);
        $invalidData = ['name' => 'Test Category', 'image' => $invalidFile];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('image', $validator->errors()->toArray());

        // Test file too large
        $largeImage = UploadedFile::fake()->image('large.jpg', 800, 600)->size(3000); // 3MB
        $invalidData = ['name' => 'Test Category', 'image' => $largeImage];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('image', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_boolean_fields()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid boolean values
        $validData = [
            'name' => 'Test Category',
            'is_active' => true,
            'show_in_menu' => false,
            'is_featured' => true,
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid boolean values
        $invalidData = [
            'name' => 'Test Category',
            'is_active' => 'invalid',
            'show_in_menu' => 'invalid',
            'is_featured' => 'invalid',
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('is_active', $validator->errors()->toArray());
        $this->assertArrayHasKey('show_in_menu', $validator->errors()->toArray());
        $this->assertArrayHasKey('is_featured', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_sort_order_field()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid sort order
        $validData = ['name' => 'Test Category', 'sort_order' => 5];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test negative sort order
        $invalidData = ['name' => 'Test Category', 'sort_order' => -1];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('sort_order', $validator->errors()->toArray());

        // Test sort order too large
        $invalidData = ['name' => 'Test Category', 'sort_order' => 10000];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('sort_order', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_meta_fields()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid meta fields
        $validData = [
            'name' => 'Test Category',
            'meta_title' => 'Valid Meta Title',
            'meta_keywords' => 'keyword1, keyword2, keyword3',
            'meta_description' => 'Valid meta description',
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test meta title too long
        $invalidData = [
            'name' => 'Test Category',
            'meta_title' => str_repeat('a', 256),
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('meta_title', $validator->errors()->toArray());

        // Test meta keywords too long
        $invalidData = [
            'name' => 'Test Category',
            'meta_keywords' => str_repeat('a', 501),
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('meta_keywords', $validator->errors()->toArray());

        // Test meta description too long
        $invalidData = [
            'name' => 'Test Category',
            'meta_description' => str_repeat('a', 501),
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('meta_description', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_color_fields()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid hex colors
        $validData = [
            'name' => 'Test Category',
            'color' => '#FF0000',
            'text_color' => '#FFFFFF',
        ];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test invalid color format
        $invalidData = [
            'name' => 'Test Category',
            'color' => 'red',
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('color', $validator->errors()->toArray());

        // Test invalid text color format
        $invalidData = [
            'name' => 'Test Category',
            'text_color' => 'white',
        ];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('text_color', $validator->errors()->toArray());
    }

    /** @test */
    public function validates_icon_field()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        // Test valid icon
        $validData = ['name' => 'Test Category', 'icon' => 'fa-icon'];
        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());

        // Test icon too long
        $invalidData = ['name' => 'Test Category', 'icon' => str_repeat('a', 101)];
        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('icon', $validator->errors()->toArray());
    }

    /** @test */
    public function prepares_data_for_validation()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $request->setUserResolver(fn () => $this->admin);

        $data = [
            'name' => '  Test Category  ',
            'slug' => '  test-slug  ',
            'description' => '  Test description  ',
            'meta_title' => '  Test Meta Title  ',
            'meta_keywords' => '  keyword1, keyword2  ',
            'meta_description' => '  Test meta description  ',
            'color' => '  #FF0000  ',
            'text_color' => '  #FFFFFF  ',
            'icon' => '  test-icon  ',
            'is_active' => '1',
            'show_in_menu' => '0',
            'is_featured' => '1',
        ];

        $request->replace($data);
        $request->prepareForValidation();

        $this->assertEquals('Test Category', $request->input('name'));
        $this->assertEquals('test-slug', $request->input('slug'));
        $this->assertEquals('Test description', $request->input('description'));
        $this->assertEquals('Test Meta Title', $request->input('meta_title'));
        $this->assertEquals('keyword1, keyword2', $request->input('meta_keywords'));
        $this->assertEquals('Test meta description', $request->input('meta_description'));
        $this->assertEquals('#FF0000', $request->input('color'));
        $this->assertEquals('#FFFFFF', $request->input('text_color'));
        $this->assertEquals('test-icon', $request->input('icon'));
        $this->assertTrue($request->input('is_active'));
        $this->assertFalse($request->input('show_in_menu'));
        $this->assertTrue($request->input('is_featured'));
    }

    /** @test */
    public function has_custom_error_messages()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('slug.regex', $messages);
        $this->assertArrayHasKey('image.mimes', $messages);
        $this->assertArrayHasKey('color.regex', $messages);
    }

    /** @test */
    public function has_custom_attributes()
    {
        $request = new StoreUpdateProductCategoryRequest();
        $attributes = $request->attributes();

        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('slug', $attributes);
        $this->assertArrayHasKey('image', $attributes);
        $this->assertArrayHasKey('color', $attributes);
    }
}
