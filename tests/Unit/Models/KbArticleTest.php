<?php

namespace Tests\Unit\Models;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for KbArticle model.
 */
class KbArticleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
    }

    /**
     * Test article creation.
     */
    public function test_can_create_article(): void
    {
        $category = KbCategory::factory()->create();
        $product = Product::factory()->create();

        $article = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'content' => 'This is test content',
            'excerpt' => 'Test excerpt',
            'is_published' => true,
            'is_featured' => false,
            'allow_comments' => true,
            'views' => 0,
        ]);

        $this->assertInstanceOf(KbArticle::class, $article);
        $this->assertEquals('Test Article', $article->title);
        $this->assertEquals('This is test content', $article->content);
        $this->assertEquals('Test excerpt', $article->excerpt);
        $this->assertTrue($article->is_published);
        $this->assertFalse($article->is_featured);
        $this->assertTrue($article->allow_comments);
        $this->assertEquals(0, $article->views);
    }

    /**
     * Test automatic slug generation.
     */
    public function test_automatic_slug_generation(): void
    {
        $category = KbCategory::factory()->create();

        $article = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article Title',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $this->assertNotNull($article->slug);
        $this->assertEquals('test-article-title', $article->slug);
    }

    /**
     * Test unique slug generation.
     */
    public function test_unique_slug_generation(): void
    {
        $category = KbCategory::factory()->create();

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $article2 = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'content' => 'Test content 2',
            'is_published' => true,
        ]);

        $this->assertEquals('test-article-1', $article2->slug);
    }

    /**
     * Test incrementViews method.
     */
    public function test_increment_views(): void
    {
        $category = KbCategory::factory()->create();

        $article = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'content' => 'Test content',
            'is_published' => true,
            'views' => 5,
        ]);

        $result = $article->incrementViews();

        $this->assertTrue($result);
        $this->assertEquals(6, $article->fresh()->views);
    }

    /**
     * Test requiresSerialAccess method.
     */
    public function test_requires_serial_access(): void
    {
        $category = KbCategory::factory()->create();

        $articleWithSerial = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'content' => 'Test content',
            'is_published' => true,
            'requires_serial' => true,
            'serial' => 'TEST123',
        ]);

        $articleWithoutSerial = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article 2',
            'content' => 'Test content',
            'is_published' => true,
            'requires_serial' => false,
        ]);

        $this->assertTrue($articleWithSerial->requiresSerialAccess());
        $this->assertFalse($articleWithoutSerial->requiresSerialAccess());
    }

    /**
     * Test formatted views attribute.
     */
    public function test_formatted_views_attribute(): void
    {
        $category = KbCategory::factory()->create();

        $article = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'content' => 'Test content',
            'is_published' => true,
            'views' => 1500,
        ]);

        $this->assertEquals('1.5K', $article->formatted_views);

        $article->update(['views' => 1500000]);
        $this->assertEquals('1.5M', $article->fresh()->formatted_views);

        $article->update(['views' => 500]);
        $this->assertEquals('500', $article->fresh()->formatted_views);
    }

    /**
     * Test reading time attribute.
     */
    public function test_reading_time_attribute(): void
    {
        $category = KbCategory::factory()->create();

        $article = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'content' => str_repeat('word ', 400), // 400 words
            'is_published' => true,
        ]);

        $this->assertEquals(2, $article->reading_time); // 400 words / 200 = 2 minutes
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        $category = KbCategory::factory()->create();

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Published Article',
            'content' => 'Test content',
            'is_published' => true,
            'is_featured' => true,
            'allow_comments' => true,
            'requires_serial' => true,
        ]);

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Unpublished Article',
            'content' => 'Test content',
            'is_published' => false,
            'is_featured' => false,
            'allow_comments' => false,
            'requires_serial' => false,
        ]);

        $this->assertCount(1, KbArticle::published()->get());
        $this->assertCount(1, KbArticle::featured()->get());
        $this->assertCount(1, KbArticle::allowComments()->get());
        $this->assertCount(1, KbArticle::requiresSerial()->get());
    }

    /**
     * Test search scope.
     */
    public function test_search_scope(): void
    {
        $category = KbCategory::factory()->create();

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Laravel Tutorial',
            'content' => 'Learn Laravel framework',
            'excerpt' => 'Laravel is great',
            'is_published' => true,
        ]);

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'PHP Basics',
            'content' => 'Learn PHP programming',
            'excerpt' => 'PHP is awesome',
            'is_published' => true,
        ]);

        $results = KbArticle::search('Laravel')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Laravel Tutorial', $results->first()->title);
    }

    /**
     * Test relationships.
     */
    public function test_relationships(): void
    {
        $category = KbCategory::factory()->create();
        $product = Product::factory()->create();

        $article = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $this->assertInstanceOf(KbCategory::class, $article->category);
        $this->assertEquals($category->id, $article->category->id);
    }

    /**
     * Test static methods.
     */
    public function test_static_methods(): void
    {
        $category = KbCategory::factory()->create();

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Featured Article',
            'content' => 'Test content',
            'is_published' => true,
            'is_featured' => true,
        ]);

        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Regular Article',
            'content' => 'Test content',
            'is_published' => true,
            'is_featured' => false,
            'views' => 100,
        ]);

        $featured = KbArticle::getFeatured();
        $popular = KbArticle::getPopular();
        $recent = KbArticle::getRecent();
        $byCategory = KbArticle::getByCategory($category->id);

        $this->assertCount(1, $featured);
        $this->assertCount(2, $popular);
        $this->assertCount(2, $recent);
        $this->assertCount(2, $byCategory);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $category = KbCategory::factory()->create();

        $article = KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article',
            'content' => 'Test content',
            'is_published' => '1',
            'is_featured' => '0',
            'allow_comments' => '1',
            'requires_serial' => '0',
            'views' => '100',
        ]);

        $this->assertIsBool($article->is_published);
        $this->assertIsBool($article->is_featured);
        $this->assertIsBool($article->allow_comments);
        $this->assertIsBool($article->requires_serial);
        $this->assertIsInt($article->views);
        $this->assertIsInt($article->kb_category_id);
    }

    /**
     * Test generateUniqueSlug method.
     */
    public function test_generate_unique_slug(): void
    {
        $slug = KbArticle::generateUniqueSlug('Test Article Title');

        $this->assertEquals('test-article-title', $slug);

        // Test with existing slug
        $category = KbCategory::factory()->create();
        KbArticle::create([
            'kb_category_id' => $category->id,
            'title' => 'Test Article Title',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $newSlug = KbArticle::generateUniqueSlug('Test Article Title');
        $this->assertEquals('test-article-title-1', $newSlug);
    }
}
