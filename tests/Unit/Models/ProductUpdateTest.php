<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test suite for ProductUpdate model.
 */
class ProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
        Storage::fake('public');
    }

    /**
     * Test product update creation.
     */
    public function test_can_create_product_update(): void
    {
        $product = Product::factory()->create();

        $update = ProductUpdate::create([
            'product_id' => $product->id,
            'version' => '1.1.0',
            'title' => 'Bug Fixes and Improvements',
            'description' => 'This update includes various bug fixes and performance improvements.',
            'changelog' => ['Fixed login issue', 'Improved performance', 'Added new features'],
            'file_path' => 'updates/update-1.1.0.zip',
            'file_name' => 'update-1.1.0.zip',
            'file_size' => 1024000,
            'file_hash' => 'abc123def456',
            'is_major' => false,
            'is_required' => true,
            'is_active' => true,
            'requirements' => ['php_version' => '8.0', 'laravel_version' => '9.0'],
            'compatibility' => ['1.0.0', '1.0.1'],
            'released_at' => now(),
        ]);

        $this->assertInstanceOf(ProductUpdate::class, $update);
        $this->assertEquals($product->id, $update->product_id);
        $this->assertEquals('1.1.0', $update->version);
        $this->assertEquals('Bug Fixes and Improvements', $update->title);
        $this->assertFalse($update->is_major);
        $this->assertTrue($update->is_required);
        $this->assertTrue($update->is_active);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Product update created') &&
                   $context['version'] === '1.1.0';
        });
    }

    /**
     * Test product relationship.
     */
    public function test_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $update = ProductUpdate::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $update->product);
        $this->assertEquals($product->id, $update->product->id);
    }

    /**
     * Test status check methods.
     */
    public function test_status_check_methods(): void
    {
        $update = ProductUpdate::factory()->create([
            'is_major' => true,
            'is_required' => false,
            'is_active' => true,
            'released_at' => now()->subDay(),
        ]);

        $this->assertTrue($update->isMajor());
        $this->assertFalse($update->isRequired());
        $this->assertTrue($update->isActive());
        $this->assertTrue($update->isReleased());
    }

    /**
     * Test version comparison.
     */
    public function test_version_comparison(): void
    {
        $update = ProductUpdate::factory()->create(['version' => '1.2.0']);

        $this->assertTrue($update->isNewerThan('1.1.0'));
        $this->assertTrue($update->isNewerThan('1.1.9'));
        $this->assertFalse($update->isNewerThan('1.2.0'));
        $this->assertFalse($update->isNewerThan('1.3.0'));
    }

    /**
     * Test compatibility checking.
     */
    public function test_compatibility_checking(): void
    {
        $update = ProductUpdate::factory()->create([
            'compatibility' => ['1.0.0', '1.0.1', '1.1.0'],
        ]);

        $this->assertTrue($update->isCompatibleWith('1.0.0'));
        $this->assertTrue($update->isCompatibleWith('1.1.0'));
        $this->assertFalse($update->isCompatibleWith('1.2.0'));

        // Test with no compatibility restrictions
        $updateNoCompat = ProductUpdate::factory()->create(['compatibility' => null]);
        $this->assertTrue($updateNoCompat->isCompatibleWith('any-version'));
    }

    /**
     * Test requirements checking.
     */
    public function test_requirements_checking(): void
    {
        $update = ProductUpdate::factory()->create([
            'requirements' => [
                'php_version' => '8.0',
                'laravel_version' => '9.0',
                'extensions' => ['openssl', 'mbstring'],
            ],
        ]);

        // This should pass in our test environment
        $this->assertTrue($update->meetsRequirements());

        // Test with no requirements
        $updateNoReqs = ProductUpdate::factory()->create(['requirements' => null]);
        $this->assertTrue($updateNoReqs->meetsRequirements());
    }

    /**
     * Test badge classes and labels.
     */
    public function test_badge_classes_and_labels(): void
    {
        $requiredUpdate = ProductUpdate::factory()->create([
            'is_required' => true,
            'is_major' => false,
            'is_active' => true,
            'released_at' => now()->subDay(),
        ]);

        $majorUpdate = ProductUpdate::factory()->create([
            'is_required' => false,
            'is_major' => true,
            'is_active' => true,
            'released_at' => now()->subDay(),
        ]);

        $minorUpdate = ProductUpdate::factory()->create([
            'is_required' => false,
            'is_major' => false,
            'is_active' => true,
            'released_at' => now()->subDay(),
        ]);

        $inactiveUpdate = ProductUpdate::factory()->create([
            'is_active' => false,
            'released_at' => null,
        ]);

        // Type badges
        $this->assertEquals('badge-danger', $requiredUpdate->type_badge_class);
        $this->assertEquals('badge-warning', $majorUpdate->type_badge_class);
        $this->assertEquals('badge-info', $minorUpdate->type_badge_class);

        // Type labels
        $this->assertEquals('Required', $requiredUpdate->type_label);
        $this->assertEquals('Major', $majorUpdate->type_label);
        $this->assertEquals('Minor', $minorUpdate->type_label);

        // Status badges
        $this->assertEquals('badge-success', $requiredUpdate->status_badge_class);
        $this->assertEquals('badge-secondary', $inactiveUpdate->status_badge_class);

        // Status labels
        $this->assertEquals('Released', $requiredUpdate->status_label);
        $this->assertEquals('Inactive', $inactiveUpdate->status_label);
    }

    /**
     * Test formatted file size.
     */
    public function test_formatted_file_size(): void
    {
        $update = ProductUpdate::factory()->create(['file_size' => 1536000]); // 1.5 MB

        $this->assertEquals('1.46 MB', $update->formatted_file_size);

        $updateNoSize = ProductUpdate::factory()->create(['file_size' => null]);
        $this->assertEquals('Unknown', $updateNoSize->formatted_file_size);
    }

    /**
     * Test changelog text attribute.
     */
    public function test_changelog_text_attribute(): void
    {
        $update = ProductUpdate::factory()->create([
            'changelog' => ['Fixed bug 1', 'Added feature 2', 'Improved performance'],
        ]);

        $expectedText = "Fixed bug 1\nAdded feature 2\nImproved performance";
        $this->assertEquals($expectedText, $update->changelog_text);

        $updateNoChangelog = ProductUpdate::factory()->create(['changelog' => null]);
        $this->assertEquals('', $updateNoChangelog->changelog_text);
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        ProductUpdate::factory()->create(['is_active' => true, 'is_major' => true, 'is_required' => false]);
        ProductUpdate::factory()->create(['is_active' => true, 'is_major' => false, 'is_required' => true]);
        ProductUpdate::factory()->create(['is_active' => false, 'is_major' => false, 'is_required' => false]);
        ProductUpdate::factory()->create(['released_at' => now()->subDay()]);
        ProductUpdate::factory()->create(['released_at' => now()->addDay()]);

        $this->assertCount(2, ProductUpdate::active()->get());
        $this->assertCount(1, ProductUpdate::major()->get());
        $this->assertCount(1, ProductUpdate::required()->get());
        $this->assertCount(1, ProductUpdate::released()->get());
    }

    /**
     * Test activation methods.
     */
    public function test_activation_methods(): void
    {
        $update = ProductUpdate::factory()->create(['is_active' => false]);

        $result = $update->activate();
        $this->assertTrue($result);
        $this->assertTrue($update->fresh()->is_active);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Product update activated');
        });

        $result = $update->deactivate();
        $this->assertTrue($result);
        $this->assertFalse($update->fresh()->is_active);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Product update deactivated');
        });
    }

    /**
     * Test marking methods.
     */
    public function test_marking_methods(): void
    {
        $update = ProductUpdate::factory()->create([
            'is_major' => false,
            'is_required' => false,
        ]);

        $result = $update->markAsMajor();
        $this->assertTrue($result);
        $this->assertTrue($update->fresh()->is_major);

        $result = $update->markAsRequired();
        $this->assertTrue($result);
        $this->assertTrue($update->fresh()->is_required);
    }

    /**
     * Test release method.
     */
    public function test_release_method(): void
    {
        $update = ProductUpdate::factory()->create(['released_at' => null]);

        $result = $update->release();
        $this->assertTrue($result);
        $this->assertNotNull($update->fresh()->released_at);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Product update released');
        });
    }

    /**
     * Test statistics.
     */
    public function test_statistics(): void
    {
        ProductUpdate::factory()->create(['is_active' => true, 'is_major' => true, 'is_required' => false]);
        ProductUpdate::factory()->create(['is_active' => true, 'is_major' => false, 'is_required' => true]);
        ProductUpdate::factory()->create(['is_active' => false, 'is_major' => false, 'is_required' => false]);
        ProductUpdate::factory()->create(['released_at' => now()->subDay()]);

        $statistics = ProductUpdate::getStatistics();

        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('active', $statistics);
        $this->assertArrayHasKey('major', $statistics);
        $this->assertArrayHasKey('required', $statistics);
        $this->assertArrayHasKey('released', $statistics);
        $this->assertArrayHasKey('by_type', $statistics);

        $this->assertEquals(4, $statistics['total']);
        $this->assertEquals(2, $statistics['active']);
        $this->assertEquals(1, $statistics['major']);
        $this->assertEquals(1, $statistics['required']);
        $this->assertEquals(1, $statistics['released']);
    }

    /**
     * Test static query methods.
     */
    public function test_static_query_methods(): void
    {
        $product = Product::factory()->create();

        ProductUpdate::factory()->create([
            'product_id' => $product->id,
            'version' => '1.0.0',
            'is_active' => true,
            'released_at' => now()->subDay(),
        ]);

        ProductUpdate::factory()->create([
            'product_id' => $product->id,
            'version' => '1.1.0',
            'is_active' => true,
            'released_at' => now()->subDay(),
            'is_required' => true,
        ]);

        $updates = ProductUpdate::getForProduct($product->id);
        $this->assertCount(2, $updates);

        $latest = ProductUpdate::getLatestForProduct($product->id);
        $this->assertEquals('1.1.0', $latest->version);

        $required = ProductUpdate::getRequiredForProduct($product->id);
        $this->assertCount(1, $required);
        $this->assertEquals('1.1.0', $required->first()->version);
    }

    /**
     * Test configuration validation.
     */
    public function test_configuration_validation(): void
    {
        $validUpdate = ProductUpdate::factory()->create([
            'version' => '1.0.0',
            'title' => 'Test Update',
            'description' => 'Test Description',
            'file_path' => 'test.zip',
        ]);

        $invalidUpdate = ProductUpdate::factory()->create([
            'version' => '',
            'title' => '',
            'description' => '',
            'file_path' => null,
            'is_active' => true,
        ]);

        $this->assertTrue($validUpdate->isValidConfiguration());
        $this->assertEmpty($validUpdate->validateConfiguration());

        $this->assertFalse($invalidUpdate->isValidConfiguration());
        $errors = $invalidUpdate->validateConfiguration();
        $this->assertContains('Version is required', $errors);
        $this->assertContains('Title is required', $errors);
        $this->assertContains('Description is required', $errors);
        $this->assertContains('File path is required for active updates', $errors);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $update = ProductUpdate::factory()->create([
            'is_major' => '1',
            'is_required' => '0',
            'is_active' => '1',
            'changelog' => ['item1', 'item2'],
            'requirements' => ['php' => '8.0'],
            'compatibility' => ['1.0.0'],
        ]);

        $this->assertIsBool($update->is_major);
        $this->assertIsBool($update->is_required);
        $this->assertIsBool($update->is_active);
        $this->assertIsArray($update->changelog);
        $this->assertIsArray($update->requirements);
        $this->assertIsArray($update->compatibility);
    }
}
