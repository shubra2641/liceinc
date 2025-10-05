<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test suite for ProductCategoryController.
 *
 * This test suite covers all CRUD operations, file uploads,
 * status management, and error handling for product categories.
 */
class ProductCategoryControllerTest extends TestCase
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
    public function admin_can_view_categories_index()
    {
        ProductCategory::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product-categories.index');
        $response->assertViewHas('categories');
    }

    /** @test */
    public function non_admin_cannot_view_categories_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.product-categories.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_create_category_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-categories.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product-categories.create');
    }

    /** @test */
    public function admin_can_create_category_with_valid_data()
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test description',
            'is_active' => true,
            'sort_order' => 1,
            'meta_title' => 'Test Meta Title',
            'meta_description' => 'Test meta description',
            'color' => '#FF0000',
            'text_color' => '#FFFFFF',
            'icon' => 'test-icon',
            'show_in_menu' => true,
            'is_featured' => false,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.store'), $categoryData);

        $response->assertRedirect(route('admin.product-categories.index'));
        $response->assertSessionHas('success', 'Category created successfully.');

        $this->assertDatabaseHas('product_categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    /** @test */
    public function admin_can_create_category_with_custom_slug()
    {
        $categoryData = [
            'name' => 'Test Category',
            'slug' => 'custom-slug',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.store'), $categoryData);

        $response->assertRedirect(route('admin.product-categories.index'));

        $this->assertDatabaseHas('product_categories', [
            'name' => 'Test Category',
            'slug' => 'custom-slug',
        ]);
    }

    /** @test */
    public function admin_can_upload_category_image()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('category.jpg', 800, 600);

        $categoryData = [
            'name' => 'Test Category',
            'image' => $image,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.store'), $categoryData);

        $response->assertRedirect(route('admin.product-categories.index'));

        $category = ProductCategory::where('name', 'Test Category')->first();
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image);
    }

    /** @test */
    public function category_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.store'), []);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function category_creation_validates_slug_uniqueness()
    {
        ProductCategory::factory()->create(['slug' => 'existing-slug']);

        $categoryData = [
            'name' => 'Test Category',
            'slug' => 'existing-slug',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.store'), $categoryData);

        $response->assertSessionHasErrors(['slug']);
    }

    /** @test */
    public function category_creation_validates_image_format()
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000);

        $categoryData = [
            'name' => 'Test Category',
            'image' => $invalidFile,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.store'), $categoryData);

        $response->assertSessionHasErrors(['image']);
    }

    /** @test */
    public function category_creation_validates_color_format()
    {
        $categoryData = [
            'name' => 'Test Category',
            'color' => 'invalid-color',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.store'), $categoryData);

        $response->assertSessionHasErrors(['color']);
    }

    /** @test */
    public function admin_can_view_category_details()
    {
        $category = ProductCategory::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-categories.show', $category));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product-categories.show');
        $response->assertViewHas('productCategory', $category);
    }

    /** @test */
    public function admin_can_view_edit_category_form()
    {
        $category = ProductCategory::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-categories.edit', $category));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product-categories.edit');
        $response->assertViewHas('productCategory', $category);
    }

    /** @test */
    public function admin_can_update_category()
    {
        $category = ProductCategory::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original description',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'is_active' => false,
            'sort_order' => 5,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-categories.update', $category), $updateData);

        $response->assertRedirect(route('admin.product-categories.index'));
        $response->assertSessionHas('success', 'Category updated successfully.');

        $category->refresh();
        $this->assertEquals('Updated Name', $category->name);
        $this->assertEquals('Updated description', $category->description);
        $this->assertFalse($category->is_active);
        $this->assertEquals(5, $category->sort_order);
    }

    /** @test */
    public function admin_can_update_category_image()
    {
        Storage::fake('public');

        $category = ProductCategory::factory()->create();
        $oldImage = 'categories/old-image.jpg';
        $category->update(['image' => $oldImage]);
        Storage::disk('public')->put($oldImage, 'fake content');

        $newImage = UploadedFile::fake()->image('new-category.jpg', 800, 600);

        $updateData = [
            'name' => $category->name,
            'image' => $newImage,
            'is_active' => $category->is_active,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-categories.update', $category), $updateData);

        $response->assertRedirect(route('admin.product-categories.index'));

        $category->refresh();
        $this->assertNotEquals($oldImage, $category->image);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($category->image);
    }

    /** @test */
    public function category_update_validates_slug_uniqueness()
    {
        $category1 = ProductCategory::factory()->create(['slug' => 'slug1']);
        $category2 = ProductCategory::factory()->create(['slug' => 'slug2']);

        $updateData = [
            'name' => $category2->name,
            'slug' => 'slug1', // Same as category1
            'is_active' => $category2->is_active,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-categories.update', $category2), $updateData);

        $response->assertSessionHasErrors(['slug']);
    }

    /** @test */
    public function admin_can_delete_category_without_products()
    {
        $category = ProductCategory::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.product-categories.destroy', $category));

        $response->assertRedirect(route('admin.product-categories.index'));
        $response->assertSessionHas('success', 'Category deleted successfully.');

        $this->assertDatabaseMissing('product_categories', ['id' => $category->id]);
    }

    /** @test */
    public function admin_cannot_delete_category_with_products()
    {
        $category = ProductCategory::factory()->create();
        $category->products()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'category_id' => $category->id,
            'license_type' => 'single',
            'programming_language' => 1,
            'requires_domain' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.product-categories.destroy', $category));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete category with existing products.');

        $this->assertDatabaseHas('product_categories', ['id' => $category->id]);
    }

    /** @test */
    public function category_deletion_removes_associated_image()
    {
        Storage::fake('public');

        $category = ProductCategory::factory()->create();
        $imagePath = 'categories/test-image.jpg';
        $category->update(['image' => $imagePath]);
        Storage::disk('public')->put($imagePath, 'fake content');

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.product-categories.destroy', $category));

        $response->assertRedirect(route('admin.product-categories.index'));
        Storage::disk('public')->assertMissing($imagePath);
    }

    /** @test */
    public function admin_can_toggle_category_status()
    {
        $category = ProductCategory::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.toggle-status', $category));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Category deactivated successfully.');

        $category->refresh();
        $this->assertFalse($category->is_active);
    }

    /** @test */
    public function admin_can_toggle_category_featured_status()
    {
        $category = ProductCategory::factory()->create(['is_featured' => false]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.toggle-featured', $category));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Category featured successfully.');

        $category->refresh();
        $this->assertTrue($category->is_featured);
    }

    /** @test */
    public function admin_can_toggle_category_menu_visibility()
    {
        $category = ProductCategory::factory()->create(['show_in_menu' => true]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.toggle-menu-visibility', $category));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Category menu visibility hidden successfully.');

        $category->refresh();
        $this->assertFalse($category->show_in_menu);
    }

    /** @test */
    public function admin_can_get_category_statistics()
    {
        $category = ProductCategory::factory()->create();

        // Create some products for the category
        $category->products()->createMany([
            [
                'name' => 'Product 1',
                'slug' => 'product-1',
                'license_type' => 'single',
                'programming_language' => 1,
                'requires_domain' => true,
                'is_active' => true,
                'is_featured' => true,
                'download_count' => 100,
                'price' => 50.00,
            ],
            [
                'name' => 'Product 2',
                'slug' => 'product-2',
                'license_type' => 'single',
                'programming_language' => 1,
                'requires_domain' => true,
                'is_active' => false,
                'is_featured' => false,
                'download_count' => 50,
                'price' => 25.00,
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-categories.statistics', $category));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'statistics' => [
                'total_products' => 2,
                'active_products' => 1,
                'inactive_products' => 1,
                'featured_products' => 1,
                'total_downloads' => 150,
                'average_price' => 37.5,
            ],
        ]);
    }

    /** @test */
    public function non_admin_cannot_access_category_management()
    {
        $category = ProductCategory::factory()->create();

        $routes = [
            'admin.product-categories.create',
            'admin.product-categories.store',
            'admin.product-categories.show',
            'admin.product-categories.edit',
            'admin.product-categories.update',
            'admin.product-categories.destroy',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)
                ->get(route($route, $category));

            $response->assertStatus(403);
        }
    }

    /** @test */
    public function guest_cannot_access_category_management()
    {
        $category = ProductCategory::factory()->create();

        $response = $this->get(route('admin.product-categories.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function category_creation_handles_database_errors_gracefully()
    {
        // Mock database error by using invalid data that would cause a constraint violation
        $categoryData = [
            'name' => str_repeat('a', 300), // Exceeds database limit
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-categories.store'), $categoryData);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function category_update_handles_database_errors_gracefully()
    {
        $category = ProductCategory::factory()->create();

        // Mock database error by using invalid data
        $updateData = [
            'name' => str_repeat('a', 300), // Exceeds database limit
            'is_active' => $category->is_active,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-categories.update', $category), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function category_deletion_handles_database_errors_gracefully()
    {
        $category = ProductCategory::factory()->create();

        // Mock database error by deleting the category first
        $category->delete();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.product-categories.destroy', $category));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
