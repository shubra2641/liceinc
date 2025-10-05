<?php

namespace Tests\Unit\Models;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for KbCategory model.
 */
class KbCategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test category creation.
     */
    public function test_can_create_category(): void
    {
        $product = Product::factory()->create();

        $category = KbCategory::create([
            'name' => 'Test Category',
            'description' => 'Test description',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(KbCategory::class, $category);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('Test description', $category->description);
        $this->assertTrue($category->is_active);
        $this->assertFalse($category->is_featured);
        $this->assertEquals(1, $category->sort_order);
        $this->assertEquals($product->id, $category->product_id);
    }

    /**
     * Test automatic slug generation.
     */
    public function test_automatic_slug_generation(): void
    {
        $category = KbCategory::create([
            'name' => 'Test Category Name',
            'is_active' => true,
        ]);

        $this->assertNotNull($category->slug);
        $this->assertEquals('test-category-name', $category->slug);
    }

    /**
     * Test unique slug generation.
     */
    public function test_unique_slug_generation(): void
    {
        KbCategory::create([
            'name' => 'Test Category',
            'is_active' => true,
        ]);

        $category2 = KbCategory::create([
            'name' => 'Test Category',
            'is_active' => true,
        ]);

        $this->assertEquals('test-category-1', $category2->slug);
    }

    /**
     * Test hierarchical relationships.
     */
    public function test_hierarchical_relationships(): void
    {
        $parent = KbCategory::create([
            'name' => 'Parent Category',
            'is_active' => true,
        ]);

        $child = KbCategory::create([
            'name' => 'Child Category',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(KbCategory::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertTrue($parent->children->contains($child));
    }

    /**
     * Test requiresSerialAccess method.
     */
    public function test_requires_serial_access(): void
    {
        $categoryWithSerial = KbCategory::create([
            'name' => 'Protected Category',
            'is_active' => true,
            'requires_serial' => true,
            'serial' => 'TEST123',
        ]);

        $categoryWithoutSerial = KbCategory::create([
            'name' => 'Public Category',
            'is_active' => true,
            'requires_serial' => false,
        ]);

        $this->assertTrue($categoryWithSerial->requiresSerialAccess());
        $this->assertFalse($categoryWithoutSerial->requiresSerialAccess());
    }

    /**
     * Test full path attribute.
     */
    public function test_full_path_attribute(): void
    {
        $parent = KbCategory::create([
            'name' => 'Parent',
            'is_active' => true,
        ]);

        $child = KbCategory::create([
            'name' => 'Child',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $grandchild = KbCategory::create([
            'name' => 'Grandchild',
            'parent_id' => $child->id,
            'is_active' => true,
        ]);

        $this->assertEquals('Parent', $parent->full_path);
        $this->assertEquals('Parent > Child', $child->full_path);
        $this->assertEquals('Parent > Child > Grandchild', $grandchild->full_path);
    }

    /**
     * Test depth attribute.
     */
    public function test_depth_attribute(): void
    {
        $parent = KbCategory::create([
            'name' => 'Parent',
            'is_active' => true,
        ]);

        $child = KbCategory::create([
            'name' => 'Child',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $grandchild = KbCategory::create([
            'name' => 'Grandchild',
            'parent_id' => $child->id,
            'is_active' => true,
        ]);

        $this->assertEquals(0, $parent->depth);
        $this->assertEquals(1, $child->depth);
        $this->assertEquals(2, $grandchild->depth);
    }

    /**
     * Test getAllDescendants method.
     */
    public function test_get_all_descendants(): void
    {
        $parent = KbCategory::create([
            'name' => 'Parent',
            'is_active' => true,
        ]);

        $child1 = KbCategory::create([
            'name' => 'Child 1',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $child2 = KbCategory::create([
            'name' => 'Child 2',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $grandchild = KbCategory::create([
            'name' => 'Grandchild',
            'parent_id' => $child1->id,
            'is_active' => true,
        ]);

        $descendants = $parent->getAllDescendants();

        $this->assertCount(3, $descendants);
        $this->assertTrue($descendants->contains($child1));
        $this->assertTrue($descendants->contains($child2));
        $this->assertTrue($descendants->contains($grandchild));
    }

    /**
     * Test getAllAncestors method.
     */
    public function test_get_all_ancestors(): void
    {
        $parent = KbCategory::create([
            'name' => 'Parent',
            'is_active' => true,
        ]);

        $child = KbCategory::create([
            'name' => 'Child',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $grandchild = KbCategory::create([
            'name' => 'Grandchild',
            'parent_id' => $child->id,
            'is_active' => true,
        ]);

        $ancestors = $grandchild->getAllAncestors();

        $this->assertCount(2, $ancestors);
        $this->assertEquals('Parent', $ancestors->first()->name);
        $this->assertEquals('Child', $ancestors->last()->name);
    }

    /**
     * Test total articles count attribute.
     */
    public function test_total_articles_count_attribute(): void
    {
        $parent = KbCategory::create([
            'name' => 'Parent',
            'is_active' => true,
        ]);

        $child = KbCategory::create([
            'name' => 'Child',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        // Create articles
        KbArticle::create([
            'kb_category_id' => $parent->id,
            'title' => 'Parent Article',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        KbArticle::create([
            'kb_category_id' => $child->id,
            'title' => 'Child Article',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $this->assertEquals(2, $parent->total_articles_count);
        $this->assertEquals(1, $child->total_articles_count);
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        KbCategory::create([
            'name' => 'Active Category',
            'is_active' => true,
            'is_featured' => true,
            'requires_serial' => true,
        ]);

        KbCategory::create([
            'name' => 'Inactive Category',
            'is_active' => false,
            'is_featured' => false,
            'requires_serial' => false,
        ]);

        $parent = KbCategory::create([
            'name' => 'Parent Category',
            'is_active' => true,
        ]);

        KbCategory::create([
            'name' => 'Child Category',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $this->assertCount(2, KbCategory::active()->get());
        $this->assertCount(1, KbCategory::featured()->get());
        $this->assertCount(1, KbCategory::requiresSerial()->get());
        $this->assertCount(2, KbCategory::root()->get());
    }

    /**
     * Test search scope.
     */
    public function test_search_scope(): void
    {
        KbCategory::create([
            'name' => 'Laravel Tutorials',
            'description' => 'Learn Laravel framework',
            'is_active' => true,
        ]);

        KbCategory::create([
            'name' => 'PHP Basics',
            'description' => 'Learn PHP programming',
            'is_active' => true,
        ]);

        $results = KbCategory::search('Laravel')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Laravel Tutorials', $results->first()->name);
    }

    /**
     * Test relationships.
     */
    public function test_relationships(): void
    {
        $product = Product::factory()->create();

        $category = KbCategory::create([
            'name' => 'Test Category',
            'is_active' => true,
            'product_id' => $product->id,
        ]);

        $article = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $this->assertInstanceOf(Product::class, $category->product);
        $this->assertEquals($product->id, $category->product->id);
        $this->assertTrue($category->articles->contains($article));
    }

    /**
     * Test static methods.
     */
    public function test_static_methods(): void
    {
        $product = Product::factory()->create();

        KbCategory::create([
            'name' => 'Root Category 1',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        KbCategory::create([
            'name' => 'Root Category 2',
            'is_active' => true,
            'sort_order' => 2,
            'is_featured' => true,
        ]);

        KbCategory::create([
            'name' => 'Product Category',
            'is_active' => true,
            'product_id' => $product->id,
        ]);

        $rootCategories = KbCategory::getRootCategories();
        $featuredCategories = KbCategory::getFeatured();
        $tree = KbCategory::getTree();
        $productCategories = KbCategory::getForProduct($product->id);

        $this->assertCount(2, $rootCategories);
        $this->assertCount(1, $featuredCategories);
        $this->assertCount(2, $tree);
        $this->assertCount(1, $productCategories);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $category = KbCategory::create([
            'name' => 'Test Category',
            'is_active' => '1',
            'is_featured' => '0',
            'requires_serial' => '1',
            'sort_order' => '5',
            'product_id' => '10',
        ]);

        $this->assertIsBool($category->is_active);
        $this->assertIsBool($category->is_featured);
        $this->assertIsBool($category->requires_serial);
        $this->assertIsInt($category->sort_order);
        $this->assertIsInt($category->product_id);
    }

    /**
     * Test generateUniqueSlug method.
     */
    public function test_generate_unique_slug(): void
    {
        $slug = KbCategory::generateUniqueSlug('Test Category Name');

        $this->assertEquals('test-category-name', $slug);

        // Test with existing slug
        KbCategory::create([
            'name' => 'Test Category Name',
            'is_active' => true,
        ]);

        $newSlug = KbCategory::generateUniqueSlug('Test Category Name');
        $this->assertEquals('test-category-name-1', $newSlug);
    }
}
