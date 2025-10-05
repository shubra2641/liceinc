<?php

namespace Tests\Feature\Api;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class KbApiControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $category;

    protected $article;

    protected $articleWithSerial;

    protected $categoryWithSerial;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test category
        $this->category = KbCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category description',
            'requires_serial' => false,
        ]);

        // Create test category with serial
        $this->categoryWithSerial = KbCategory::factory()->create([
            'name' => 'Serial Category',
            'slug' => 'serial-category',
            'description' => 'Category that requires serial',
            'requires_serial' => true,
            'serial' => 'CATEGORY-SERIAL-123',
            'serial_message' => 'Please enter the category serial code.',
        ]);

        // Create test article
        $this->article = KbArticle::factory()->create([
            'category_id' => $this->category->id,
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'This is test article content.',
            'excerpt' => 'Test article excerpt.',
            'status' => 'published',
            'requires_serial' => false,
            'views' => 0,
        ]);

        // Create test article with serial
        $this->articleWithSerial = KbArticle::factory()->create([
            'category_id' => $this->category->id,
            'title' => 'Serial Article',
            'slug' => 'serial-article',
            'content' => 'This is serial article content.',
            'excerpt' => 'Serial article excerpt.',
            'status' => 'published',
            'requires_serial' => true,
            'serial' => 'ARTICLE-SERIAL-456',
            'serial_message' => 'Please enter the article serial code.',
            'views' => 0,
        ]);

        // Clear cache
        Cache::flush();
    }

    /**
     * Test article serial verification with valid serial.
     */
    public function test_verify_article_serial_with_valid_serial()
    {
        $response = $this->postJson('/api/kb/article/serial-article/verify', [
            'serial' => 'ARTICLE-SERIAL-456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Serial verified successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'content',
                    'title',
                    'serial_source',
                    'views',
                    'last_updated',
                ],
            ]);

        // Verify views were incremented
        $this->articleWithSerial->refresh();
        $this->assertEquals(1, $this->articleWithSerial->views);
    }

    /**
     * Test article serial verification with invalid serial.
     */
    public function test_verify_article_serial_with_invalid_serial()
    {
        $response = $this->postJson('/api/kb/article/serial-article/verify', [
            'serial' => 'INVALID-SERIAL',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid serial code',
                'error_code' => 'INVALID_SERIAL',
            ]);
    }

    /**
     * Test article serial verification with category serial.
     */
    public function test_verify_article_serial_with_category_serial()
    {
        // Create article in category that requires serial
        $articleInSerialCategory = KbArticle::factory()->create([
            'category_id' => $this->categoryWithSerial->id,
            'title' => 'Article in Serial Category',
            'slug' => 'article-in-serial-category',
            'content' => 'This article is in a category that requires serial.',
            'excerpt' => 'Article in serial category excerpt.',
            'status' => 'published',
            'requires_serial' => false,
            'views' => 0,
        ]);

        $response = $this->postJson('/api/kb/article/article-in-serial-category/verify', [
            'serial' => 'CATEGORY-SERIAL-123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Serial verified successfully',
            ])
            ->assertJson([
                'data' => [
                    'serial_source' => 'category',
                ],
            ]);
    }

    /**
     * Test article serial verification without serial requirement.
     */
    public function test_verify_article_serial_without_serial_requirement()
    {
        $response = $this->postJson('/api/kb/article/test-article/verify', [
            'serial' => 'ANY-SERIAL',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'No serial required',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'content',
                    'title',
                    'views',
                    'last_updated',
                ],
            ]);

        // Verify views were incremented
        $this->article->refresh();
        $this->assertEquals(1, $this->article->views);
    }

    /**
     * Test article serial verification with non-existent article.
     */
    public function test_verify_article_serial_with_non_existent_article()
    {
        $response = $this->postJson('/api/kb/article/non-existent-article/verify', [
            'serial' => 'ANY-SERIAL',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Article not found',
                'error_code' => 'ARTICLE_NOT_FOUND',
            ]);
    }

    /**
     * Test get article requirements.
     */
    public function test_get_article_requirements()
    {
        $response = $this->getJson('/api/kb/article/serial-article/requirements');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'requires_serial' => true,
                    'title' => 'Serial Article',
                    'excerpt' => 'Serial article excerpt.',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'requires_serial',
                    'title',
                    'excerpt',
                    'views',
                    'last_updated',
                    'category',
                    'serial_message',
                    'serial_source',
                ],
            ]);
    }

    /**
     * Test get article requirements without serial.
     */
    public function test_get_article_requirements_without_serial()
    {
        $response = $this->getJson('/api/kb/article/test-article/requirements');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'requires_serial' => false,
                    'title' => 'Test Article',
                    'excerpt' => 'Test article excerpt.',
                ],
            ])
            ->assertJsonMissing([
                'data' => [
                    'serial_message',
                    'serial_source',
                ],
            ]);
    }

    /**
     * Test get article requirements with non-existent article.
     */
    public function test_get_article_requirements_with_non_existent_article()
    {
        $response = $this->getJson('/api/kb/article/non-existent-article/requirements');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Article not found',
                'error_code' => 'ARTICLE_NOT_FOUND',
            ]);
    }

    /**
     * Test get category requirements.
     */
    public function test_get_category_requirements()
    {
        $response = $this->getJson('/api/kb/category/serial-category/requirements');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'requires_serial' => true,
                    'name' => 'Serial Category',
                    'description' => 'Category that requires serial',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'requires_serial',
                    'name',
                    'description',
                    'articles_count',
                    'slug',
                    'created_at',
                    'updated_at',
                    'serial_message',
                ],
            ]);
    }

    /**
     * Test get category requirements without serial.
     */
    public function test_get_category_requirements_without_serial()
    {
        $response = $this->getJson('/api/kb/category/test-category/requirements');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'requires_serial' => false,
                    'name' => 'Test Category',
                    'description' => 'Test category description',
                ],
            ])
            ->assertJsonMissing([
                'data' => [
                    'serial_message',
                ],
            ]);
    }

    /**
     * Test get category requirements with non-existent category.
     */
    public function test_get_category_requirements_with_non_existent_category()
    {
        $response = $this->getJson('/api/kb/category/non-existent-category/requirements');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Category not found',
                'error_code' => 'CATEGORY_NOT_FOUND',
            ]);
    }

    /**
     * Test get statistics.
     */
    public function test_get_statistics()
    {
        // Create additional test data
        KbArticle::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'status' => 'published',
        ]);

        KbArticle::factory()->count(2)->create([
            'category_id' => $this->category->id,
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/kb/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'period',
                'articles' => [
                    'total_articles',
                    'published_articles',
                    'draft_articles',
                    'articles_with_serial',
                    'total_views',
                ],
                'categories' => [
                    'total_categories',
                    'categories_with_serial',
                    'categories_with_articles',
                ],
                'performance_metrics' => [
                    'average_response_time',
                    'cache_hit_rate',
                    'api_uptime',
                    'error_rate',
                ],
                'recent_activity',
                'generated_at',
            ]);
    }

    /**
     * Test caching functionality.
     */
    public function test_article_verification_caching()
    {
        // First request
        $response1 = $this->postJson('/api/kb/article/test-article/verify', [
            'serial' => 'ANY-SERIAL',
        ]);

        $response1->assertStatus(200);

        // Second request should use cache
        $response2 = $this->postJson('/api/kb/article/test-article/verify', [
            'serial' => 'ANY-SERIAL',
        ]);

        $response2->assertStatus(200);
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test requirements caching.
     */
    public function test_article_requirements_caching()
    {
        // First request
        $response1 = $this->getJson('/api/kb/article/test-article/requirements');
        $response1->assertStatus(200);

        // Second request should use cache
        $response2 = $this->getJson('/api/kb/article/test-article/requirements');
        $response2->assertStatus(200);
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test rate limiting.
     */
    public function test_rate_limiting()
    {
        // Make multiple requests to trigger rate limiting
        for ($i = 0; $i < 20; $i++) {
            $response = $this->postJson('/api/kb/article/test-article/verify', [
                'serial' => 'ANY-SERIAL',
            ]);

            if ($i >= 15) { // After 15 requests, should be rate limited
                $response->assertStatus(429)
                    ->assertJson([
                        'success' => false,
                        'message' => 'Too many requests. Please try again later.',
                    ]);
            }
        }
    }

    /**
     * Test validation errors.
     */
    public function test_validation_errors()
    {
        $response = $this->postJson('/api/kb/article/test-article/verify', [
            'serial' => '', // Invalid empty
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
                'error_code' => 'VALIDATION_ERROR',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
                'error_code',
            ]);
    }

    /**
     * Test security validation.
     */
    public function test_security_validation()
    {
        $response = $this->postJson('/api/kb/article/test-article/verify', [
            'serial' => '<script>alert("xss")</script>',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Serial code contains invalid characters',
                'error_code' => 'INVALID_SERIAL_CHARACTERS',
            ]);
    }

    /**
     * Test SQL injection prevention.
     */
    public function test_sql_injection_prevention()
    {
        $response = $this->postJson('/api/kb/article/test-article/verify', [
            'serial' => "'; DROP TABLE articles; --",
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid serial code format',
                'error_code' => 'INVALID_SERIAL_FORMAT',
            ]);
    }

    /**
     * Test security event logging.
     */
    public function test_security_event_logging()
    {
        Log::shouldReceive('error')
            ->once()
            ->with('KB API security event', \Mockery::type('array'));

        // Trigger security event by using invalid serial
        $this->postJson('/api/kb/article/serial-article/verify', [
            'serial' => 'INVALID-SERIAL',
        ]);
    }

    /**
     * Test error handling.
     */
    public function test_error_handling()
    {
        // Test with malformed JSON
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('/api/kb/article/test-article/verify', 'invalid json');

        $response->assertStatus(400);
    }

    /**
     * Test statistics caching.
     */
    public function test_statistics_caching()
    {
        // First request
        $response1 = $this->getJson('/api/kb/statistics');
        $response1->assertStatus(200);

        // Second request should use cache
        $response2 = $this->getJson('/api/kb/statistics');
        $response2->assertStatus(200);
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test force refresh statistics.
     */
    public function test_force_refresh_statistics()
    {
        // First request
        $response1 = $this->getJson('/api/kb/statistics');
        $response1->assertStatus(200);

        // Force refresh request
        $response2 = $this->getJson('/api/kb/statistics?force_refresh=1');
        $response2->assertStatus(200);

        // Both should be successful but may have different timestamps
        $this->assertTrue($response1->json('success'));
        $this->assertTrue($response2->json('success'));
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
