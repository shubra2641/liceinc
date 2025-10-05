<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for ProductCategory model.
 */
class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
    }

    /**
     * Test product category creation.
     */
    public function test_can_create_product_category(): void
    {
        $category = ProductCategory::create([
            'name' => 'Web Development',
            'description' => 'Web development tools and frameworks',
            'is_active' => true,
            'sort_order' => 1,
            'meta_title' => 'Web Development Tools',
            'meta_description' => 'Best web development tools',
            'color' => '#FF5733',
            'text_color' => '#FFFFFF',
            'icon' => 'fas fa-code',
            'show_in_menu' => true,
            'is_featured' => false,
        ]);

        $this->assertInstanceOf(ProductCategory::class, $category);
        $this->assertEquals('Web Development', $category->name);
        $this->assertEquals('web-development', $category->slug);
        $this->assertTrue($category->is_active);
        $this->assertTrue($category->show_in_menu);
        $this->assertFalse($category->is_featured);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Product category created') &&
                   $context['name'] === 'Web Development';
        });
    }

    /**
     * Test automatic slug generation.
     */
    public function test_automatic_slug_generation(): void
    {
        $category = ProductCategory::create(['name' => 'Mobile Apps']);
        $this->assertEquals('mobile-apps', $category->slug);

        // Test unique slug generation
        $category2 = ProductCategory::create(['name' => 'Mobile Apps']);
        $this->assertEquals('mobile-apps-1', $category2->slug);

        $category3 = ProductCategory::create(['name' => 'Mobile Apps']);
        $this->assertEquals('mobile-apps-2', $category3->slug);
    }

    /**
     * Test slug update on name change.
     */
    public function test_slug_update_on_name_change(): void
    {
        $category = ProductCategory::create(['name' => 'Original Name']);
        $this->assertEquals('original-name', $category->slug);

        $category->update(['name' => 'Updated Name']);
        $this->assertEquals('updated-name', $category->fresh()->slug);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Product category updated');
        });
    }

    /**
     * Test products relationship.
     */
    public function test_has_many_products(): void
    {
        $category = ProductCategory::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->products);
        $this->assertTrue($category->products->contains($product1));
        $this->assertTrue($category->products->contains($product2));
    }

    /**
     * Test active products relationship.
     */
    public function test_active_products_relationship(): void
    {
        $category = ProductCategory::factory()->create();
        $activeProduct = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);
        $inactiveProduct = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => false,
        ]);

        $activeProducts = $category->activeProducts;
        $this->assertCount(1, $activeProducts);
        $this->assertTrue($activeProducts->contains($activeProduct));
        $this->assertFalse($activeProducts->contains($inactiveProduct));
    }

    /**
     * Test status check methods.
     */
    public function test_status_check_methods(): void
    {
        $activeCategory = ProductCategory::factory()->create([
            'is_active' => true,
            'is_featured' => true,
            'show_in_menu' => true,
        ]);

        $inactiveCategory = ProductCategory::factory()->create([
            'is_active' => false,
            'is_featured' => false,
            'show_in_menu' => false,
        ]);

        $this->assertTrue($activeCategory->isActive());
        $this->assertTrue($activeCategory->isFeatured());
        $this->assertTrue($activeCategory->showsInMenu());

        $this->assertFalse($inactiveCategory->isActive());
        $this->assertFalse($inactiveCategory->isFeatured());
        $this->assertFalse($inactiveCategory->showsInMenu());
    }

    /**
     * Test URL attribute.
     */
    public function test_url_attribute(): void
    {
        $category = ProductCategory::factory()->create(['slug' => 'test-category']);

        // Mock the route helper
        $this->app['router']->get('/categories/{slug}', function () {})->name('categories.show');

        $url = $category->url;
        $this->assertStringContains('categories/test-category', $url);
    }

    /**
     * Test products count attributes.
     */
    public function test_products_count_attributes(): void
    {
        $category = ProductCategory::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => false,
        ]);

        $this->assertEquals(3, $category->products_count);
        $this->assertEquals(2, $category->active_products_count);
    }

    /**
     * Test badge classes and labels.
     */
    public function test_badge_classes_and_labels(): void
    {
        $activeCategory = ProductCategory::factory()->create([
            'is_active' => true,
            'is_featured' => true,
        ]);

        $inactiveCategory = ProductCategory::factory()->create([
            'is_active' => false,
            'is_featured' => false,
        ]);

        // Status badges
        $this->assertEquals('badge-success', $activeCategory->status_badge_class);
        $this->assertEquals('badge-secondary', $inactiveCategory->status_badge_class);

        // Status labels
        $this->assertEquals('Active', $activeCategory->status_label);
        $this->assertEquals('Inactive', $inactiveCategory->status_label);

        // Featured badges
        $this->assertEquals('badge-warning', $activeCategory->featured_badge_class);
        $this->assertEquals('badge-secondary', $inactiveCategory->featured_badge_class);

        // Featured labels
        $this->assertEquals('Featured', $activeCategory->featured_label);
        $this->assertEquals('Regular', $inactiveCategory->featured_label);
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        ProductCategory::factory()->create(['is_active' => true, 'is_featured' => true, 'show_in_menu' => true]);
        ProductCategory::factory()->create(['is_active' => true, 'is_featured' => false, 'show_in_menu' => true]);
        ProductCategory::factory()->create(['is_active' => false, 'is_featured' => false, 'show_in_menu' => false]);

        $this->assertCount(2, ProductCategory::active()->get());
        $this->assertCount(1, ProductCategory::featured()->get());
        $this->assertCount(2, ProductCategory::showInMenu()->get());
    }

    /**
     * Test ordered scope.
     */
    public function test_ordered_scope(): void
    {
        ProductCategory::factory()->create(['name' => 'Category C', 'sort_order' => 3]);
        ProductCategory::factory()->create(['name' => 'Category A', 'sort_order' => 1]);
        ProductCategory::factory()->create(['name' => 'Category B', 'sort_order' => 2]);

        $ordered = ProductCategory::ordered()->get();
        $this->assertEquals('Category A', $ordered[0]->name);
        $this->assertEquals('Category B', $ordered[1]->name);
        $this->assertEquals('Category C', $ordered[2]->name);
    }

    /**
     * Test search scope.
     */
    public function test_search_scope(): void
    {
        ProductCategory::factory()->create(['name' => 'Web Development', 'description' => 'Web tools']);
        ProductCategory::factory()->create(['name' => 'Mobile Apps', 'description' => 'Mobile development']);
        ProductCategory::factory()->create(['name' => 'Desktop Software', 'description' => 'Desktop applications']);

        $results = ProductCategory::search('web')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Web Development', $results->first()->name);

        $results = ProductCategory::search('development')->get();
        $this->assertCount(2, $results);
    }

    /**
     * Test activation methods.
     */
    public function test_activation_methods(): void
    {
        $category = ProductCategory::factory()->create(['is_active' => false]);

        $result = $category->activate();
        $this->assertTrue($result);
        $this->assertTrue($category->fresh()->is_active);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Product category activated');
        });

        $result = $category->deactivate();
        $this->assertTrue($result);
        $this->assertFalse($category->fresh()->is_active);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Product category deactivated');
        });
    }

    /**
     * Test featured methods.
     */
    public function test_featured_methods(): void
    {
        $category = ProductCategory::factory()->create(['is_featured' => false]);

        $result = $category->markAsFeatured();
        $this->assertTrue($result);
        $this->assertTrue($category->fresh()->is_featured);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Product category marked as featured');
        });

        $result = $category->removeFromFeatured();
        $this->assertTrue($result);
        $this->assertFalse($category->fresh()->is_featured);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Product category removed from featured');
        });
    }

    /**
     * Test statistics.
     */
    public function test_statistics(): void
    {
        ProductCategory::factory()->create(['is_active' => true, 'is_featured' => true, 'show_in_menu' => true]);
        ProductCategory::factory()->create(['is_active' => true, 'is_featured' => false, 'show_in_menu' => true]);
        ProductCategory::factory()->create(['is_active' => false, 'is_featured' => false, 'show_in_menu' => false]);

        $statistics = ProductCategory::getStatistics();

        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('active', $statistics);
        $this->assertArrayHasKey('featured', $statistics);
        $this->assertArrayHasKey('show_in_menu', $statistics);
        $this->assertArrayHasKey('by_status', $statistics);

        $this->assertEquals(3, $statistics['total']);
        $this->assertEquals(2, $statistics['active']);
        $this->assertEquals(1, $statistics['featured']);
        $this->assertEquals(2, $statistics['show_in_menu']);
    }

    /**
     * Test static query methods.
     */
    public function test_static_query_methods(): void
    {
        ProductCategory::factory()->create(['is_active' => true, 'is_featured' => true, 'sort_order' => 1]);
        ProductCategory::factory()->create(['is_active' => true, 'is_featured' => false, 'sort_order' => 2]);
        ProductCategory::factory()->create(['is_active' => false, 'is_featured' => false, 'sort_order' => 3]);

        $activeOrdered = ProductCategory::getActiveOrdered();
        $this->assertCount(2, $activeOrdered);

        $featured = ProductCategory::getFeatured();
        $this->assertCount(1, $featured);

        $forMenu = ProductCategory::getForMenu();
        $this->assertCount(2, $forMenu);

        $searchResults = ProductCategory::searchCategories('test');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $searchResults);
    }

    /**
     * Test configuration validation.
     */
    public function test_configuration_validation(): void
    {
        $validCategory = ProductCategory::factory()->create([
            'name' => 'Valid Category',
            'slug' => 'valid-category',
            'color' => '#FF5733',
            'text_color' => '#FFFFFF',
        ]);

        $invalidCategory = ProductCategory::factory()->create([
            'name' => '',
            'slug' => '',
            'color' => 'invalid-color',
            'text_color' => 'invalid-text-color',
        ]);

        $this->assertTrue($validCategory->isValidConfiguration());
        $this->assertEmpty($validCategory->validateConfiguration());

        $this->assertFalse($invalidCategory->isValidConfiguration());
        $errors = $invalidCategory->validateConfiguration();
        $this->assertContains('Category name is required', $errors);
        $this->assertContains('Category slug is required', $errors);
        $this->assertContains('Invalid color format', $errors);
        $this->assertContains('Invalid text color format', $errors);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $category = ProductCategory::factory()->create([
            'is_active' => '1',
            'show_in_menu' => '0',
            'is_featured' => '1',
            'sort_order' => '5',
        ]);

        $this->assertIsBool($category->is_active);
        $this->assertIsBool($category->show_in_menu);
        $this->assertIsBool($category->is_featured);
        $this->assertIsInt($category->sort_order);
    }
}
