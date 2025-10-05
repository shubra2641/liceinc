<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\ProgrammingLanguage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for ProgrammingLanguage model.
 */
class ProgrammingLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
    }

    /**
     * Test programming language creation.
     */
    public function test_can_create_programming_language(): void
    {
        $language = ProgrammingLanguage::create([
            'name' => 'PHP',
            'description' => 'Server-side scripting language',
            'icon' => 'fab fa-php',
            'is_active' => true,
            'sort_order' => 1,
            'file_extension' => 'php',
            'license_template' => '<?php // License template',
        ]);

        $this->assertInstanceOf(ProgrammingLanguage::class, $language);
        $this->assertEquals('PHP', $language->name);
        $this->assertEquals('php', $language->slug);
        $this->assertTrue($language->is_active);
        $this->assertEquals('php', $language->file_extension);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Programming language created') &&
                   $context['name'] === 'PHP';
        });
    }

    /**
     * Test automatic slug generation.
     */
    public function test_automatic_slug_generation(): void
    {
        $language = ProgrammingLanguage::create(['name' => 'JavaScript']);
        $this->assertEquals('javascript', $language->slug);

        // Test unique slug generation
        $language2 = ProgrammingLanguage::create(['name' => 'JavaScript']);
        $this->assertEquals('javascript-1', $language2->slug);

        $language3 = ProgrammingLanguage::create(['name' => 'JavaScript']);
        $this->assertEquals('javascript-2', $language3->slug);
    }

    /**
     * Test slug update on name change.
     */
    public function test_slug_update_on_name_change(): void
    {
        $language = ProgrammingLanguage::create(['name' => 'Original Name']);
        $this->assertEquals('original-name', $language->slug);

        $language->update(['name' => 'Updated Name']);
        $this->assertEquals('updated-name', $language->fresh()->slug);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Programming language updated');
        });
    }

    /**
     * Test products relationship.
     */
    public function test_has_many_products(): void
    {
        $language = ProgrammingLanguage::factory()->create();
        $product1 = Product::factory()->create(['programming_language' => $language->id]);
        $product2 = Product::factory()->create(['programming_language' => $language->id]);

        $this->assertCount(2, $language->products);
        $this->assertTrue($language->products->contains($product1));
        $this->assertTrue($language->products->contains($product2));
    }

    /**
     * Test active products relationship.
     */
    public function test_active_products_relationship(): void
    {
        $language = ProgrammingLanguage::factory()->create();
        $activeProduct = Product::factory()->create([
            'programming_language' => $language->id,
            'is_active' => true,
        ]);
        $inactiveProduct = Product::factory()->create([
            'programming_language' => $language->id,
            'is_active' => false,
        ]);

        $activeProducts = $language->activeProducts;
        $this->assertCount(1, $activeProducts);
        $this->assertTrue($activeProducts->contains($activeProduct));
        $this->assertFalse($activeProducts->contains($inactiveProduct));
    }

    /**
     * Test status check methods.
     */
    public function test_status_check_methods(): void
    {
        $activeLanguage = ProgrammingLanguage::factory()->create(['is_active' => true]);
        $inactiveLanguage = ProgrammingLanguage::factory()->create(['is_active' => false]);

        $this->assertTrue($activeLanguage->isActive());
        $this->assertFalse($inactiveLanguage->isActive());
    }

    /**
     * Test products count attributes.
     */
    public function test_products_count_attributes(): void
    {
        $language = ProgrammingLanguage::factory()->create();

        Product::factory()->create([
            'programming_language' => $language->id,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'programming_language' => $language->id,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'programming_language' => $language->id,
            'is_active' => false,
        ]);

        $this->assertEquals(3, $language->products_count);
        $this->assertEquals(2, $language->active_products_count);
    }

    /**
     * Test badge classes and labels.
     */
    public function test_badge_classes_and_labels(): void
    {
        $activeLanguage = ProgrammingLanguage::factory()->create(['is_active' => true]);
        $inactiveLanguage = ProgrammingLanguage::factory()->create(['is_active' => false]);

        $this->assertEquals('badge-success', $activeLanguage->status_badge_class);
        $this->assertEquals('badge-secondary', $inactiveLanguage->status_badge_class);

        $this->assertEquals('Active', $activeLanguage->status_label);
        $this->assertEquals('Inactive', $inactiveLanguage->status_label);
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        ProgrammingLanguage::factory()->create(['is_active' => true, 'sort_order' => 2]);
        ProgrammingLanguage::factory()->create(['is_active' => true, 'sort_order' => 1]);
        ProgrammingLanguage::factory()->create(['is_active' => false, 'sort_order' => 3]);

        $this->assertCount(2, ProgrammingLanguage::active()->get());
    }

    /**
     * Test ordered scope.
     */
    public function test_ordered_scope(): void
    {
        ProgrammingLanguage::factory()->create(['name' => 'Language C', 'sort_order' => 3]);
        ProgrammingLanguage::factory()->create(['name' => 'Language A', 'sort_order' => 1]);
        ProgrammingLanguage::factory()->create(['name' => 'Language B', 'sort_order' => 2]);

        $ordered = ProgrammingLanguage::ordered()->get();
        $this->assertEquals('Language A', $ordered[0]->name);
        $this->assertEquals('Language B', $ordered[1]->name);
        $this->assertEquals('Language C', $ordered[2]->name);
    }

    /**
     * Test search scope.
     */
    public function test_search_scope(): void
    {
        ProgrammingLanguage::factory()->create(['name' => 'PHP', 'description' => 'Server-side language']);
        ProgrammingLanguage::factory()->create(['name' => 'JavaScript', 'description' => 'Client-side language']);
        ProgrammingLanguage::factory()->create(['name' => 'Python', 'description' => 'General purpose language']);

        $results = ProgrammingLanguage::search('php')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('PHP', $results->first()->name);

        $results = ProgrammingLanguage::search('language')->get();
        $this->assertCount(3, $results);
    }

    /**
     * Test activation methods.
     */
    public function test_activation_methods(): void
    {
        $language = ProgrammingLanguage::factory()->create(['is_active' => false]);

        $result = $language->activate();
        $this->assertTrue($result);
        $this->assertTrue($language->fresh()->is_active);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Programming language activated');
        });

        $result = $language->deactivate();
        $this->assertTrue($result);
        $this->assertFalse($language->fresh()->is_active);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Programming language deactivated');
        });
    }

    /**
     * Test statistics.
     */
    public function test_statistics(): void
    {
        ProgrammingLanguage::factory()->create(['is_active' => true]);
        ProgrammingLanguage::factory()->create(['is_active' => true]);
        ProgrammingLanguage::factory()->create(['is_active' => false]);

        $statistics = ProgrammingLanguage::getStatistics();

        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('active', $statistics);
        $this->assertArrayHasKey('by_status', $statistics);

        $this->assertEquals(3, $statistics['total']);
        $this->assertEquals(2, $statistics['active']);
    }

    /**
     * Test static query methods.
     */
    public function test_static_query_methods(): void
    {
        ProgrammingLanguage::factory()->create(['is_active' => true, 'sort_order' => 1]);
        ProgrammingLanguage::factory()->create(['is_active' => true, 'sort_order' => 2]);
        ProgrammingLanguage::factory()->create(['is_active' => false, 'sort_order' => 3]);

        $activeOrdered = ProgrammingLanguage::getActiveOrdered();
        $this->assertCount(2, $activeOrdered);

        $searchResults = ProgrammingLanguage::searchLanguages('test');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $searchResults);
    }

    /**
     * Test license template methods.
     */
    public function test_license_template_methods(): void
    {
        $language = ProgrammingLanguage::factory()->create([
            'slug' => 'php',
            'license_template' => 'Custom template',
        ]);

        $this->assertEquals('Custom template', $language->getLicenseTemplate());

        $languageNoTemplate = ProgrammingLanguage::factory()->create(['slug' => 'php']);
        $template = $languageNoTemplate->getLicenseTemplate();
        $this->assertStringContains('<?php', $template);
        $this->assertStringContains('License Verification', $template);
    }

    /**
     * Test template file methods.
     */
    public function test_template_file_methods(): void
    {
        $language = ProgrammingLanguage::factory()->create(['slug' => 'php']);

        $templatePath = $language->getTemplateFilePath();
        $this->assertStringContains('templates/licenses/php.php', $templatePath);

        $this->assertFalse($language->hasTemplateFile());

        $templateInfo = $language->getTemplateInfo();
        $this->assertArrayHasKey('has_file', $templateInfo);
        $this->assertArrayHasKey('file_path', $templateInfo);
        $this->assertArrayHasKey('file_size', $templateInfo);
        $this->assertArrayHasKey('last_modified', $templateInfo);
        $this->assertArrayHasKey('template_content', $templateInfo);
    }

    /**
     * Test available template files.
     */
    public function test_available_template_files(): void
    {
        $templates = ProgrammingLanguage::getAvailableTemplateFiles();
        $this->assertIsArray($templates);
    }

    /**
     * Test configuration validation.
     */
    public function test_configuration_validation(): void
    {
        $validLanguage = ProgrammingLanguage::factory()->create([
            'name' => 'Valid Language',
            'slug' => 'valid-language',
            'file_extension' => 'php',
        ]);

        $invalidLanguage = ProgrammingLanguage::factory()->create([
            'name' => '',
            'slug' => '',
            'file_extension' => 'invalid-extension!',
        ]);

        $this->assertTrue($validLanguage->isValidConfiguration());
        $this->assertEmpty($validLanguage->validateConfiguration());

        $this->assertFalse($invalidLanguage->isValidConfiguration());
        $errors = $invalidLanguage->validateConfiguration();
        $this->assertContains('Language name is required', $errors);
        $this->assertContains('Language slug is required', $errors);
        $this->assertContains('Invalid file extension format', $errors);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $language = ProgrammingLanguage::factory()->create([
            'is_active' => '1',
            'sort_order' => '5',
        ]);

        $this->assertIsBool($language->is_active);
        $this->assertIsInt($language->sort_order);
    }
}
