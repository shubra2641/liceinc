<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\ProductFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test suite for ProductFile model.
 */
class ProductFileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
        Storage::fake('private');
    }

    /**
     * Test product file creation.
     */
    public function test_can_create_product_file(): void
    {
        $product = Product::factory()->create();

        $file = ProductFile::create([
            'product_id' => $product->id,
            'original_name' => 'test-file.zip',
            'encrypted_name' => 'encrypted_file_123.zip',
            'file_path' => 'products/test-file.zip',
            'file_type' => 'application/zip',
            'file_size' => 1024000,
            'encryption_key' => Crypt::encryptString('test-key'),
            'checksum' => 'abc123def456',
            'description' => 'Test product file',
            'download_count' => 0,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(ProductFile::class, $file);
        $this->assertEquals($product->id, $file->product_id);
        $this->assertEquals('test-file.zip', $file->original_name);
        $this->assertEquals('application/zip', $file->file_type);
        $this->assertEquals(1024000, $file->file_size);
        $this->assertTrue($file->is_active);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Product file created') &&
                   $context['original_name'] === 'test-file.zip';
        });
    }

    /**
     * Test product relationship.
     */
    public function test_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $file = ProductFile::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $file->product);
        $this->assertEquals($product->id, $file->product->id);
    }

    /**
     * Test status check methods.
     */
    public function test_status_check_methods(): void
    {
        $activeFile = ProductFile::factory()->create(['is_active' => true]);
        $inactiveFile = ProductFile::factory()->create(['is_active' => false]);

        $this->assertTrue($activeFile->isActive());
        $this->assertFalse($inactiveFile->isActive());
    }

    /**
     * Test formatted file size.
     */
    public function test_formatted_file_size(): void
    {
        $file = ProductFile::factory()->create(['file_size' => 1536000]); // 1.5 MB
        $this->assertEquals('1.46 MB', $file->formatted_size);

        $file = ProductFile::factory()->create(['file_size' => 1024]); // 1 KB
        $this->assertEquals('1 KB', $file->formatted_size);

        $file = ProductFile::factory()->create(['file_size' => 1073741824]); // 1 GB
        $this->assertEquals('1 GB', $file->formatted_size);
    }

    /**
     * Test file extension attribute.
     */
    public function test_file_extension_attribute(): void
    {
        $file = ProductFile::factory()->create(['original_name' => 'test-file.zip']);
        $this->assertEquals('zip', $file->file_extension);

        $file = ProductFile::factory()->create(['original_name' => 'document.pdf']);
        $this->assertEquals('pdf', $file->file_extension);
    }

    /**
     * Test download URL attribute.
     */
    public function test_download_url_attribute(): void
    {
        $product = Product::factory()->create();
        $file = ProductFile::factory()->create(['product_id' => $product->id]);

        // Mock the route helper
        $this->app['router']->get('/api/products/{product}/files/{file}/download', function () {})->name('api.product-files.download');

        $url = $file->download_url;
        $this->assertStringContains('api/products/'.$product->id.'/files/'.$file->id.'/download', $url);
    }

    /**
     * Test badge classes and labels.
     */
    public function test_badge_classes_and_labels(): void
    {
        $activeFile = ProductFile::factory()->create(['is_active' => true]);
        $inactiveFile = ProductFile::factory()->create(['is_active' => false]);

        $this->assertEquals('badge-success', $activeFile->status_badge_class);
        $this->assertEquals('badge-secondary', $inactiveFile->status_badge_class);

        $this->assertEquals('Active', $activeFile->status_label);
        $this->assertEquals('Inactive', $inactiveFile->status_label);
    }

    /**
     * Test formatted download count.
     */
    public function test_formatted_download_count(): void
    {
        $file = ProductFile::factory()->create(['download_count' => 1500]);
        $this->assertEquals('1.5K', $file->formatted_download_count);

        $file = ProductFile::factory()->create(['download_count' => 1500000]);
        $this->assertEquals('1.5M', $file->formatted_download_count);

        $file = ProductFile::factory()->create(['download_count' => 500]);
        $this->assertEquals('500', $file->formatted_download_count);
    }

    /**
     * Test file exists method.
     */
    public function test_file_exists_method(): void
    {
        $file = ProductFile::factory()->create(['file_path' => 'test-file.zip']);

        // File doesn't exist initially
        $this->assertFalse($file->fileExists());

        // Create the file in storage
        Storage::disk('private')->put('test-file.zip', 'test content');
        $this->assertTrue($file->fileExists());
    }

    /**
     * Test decrypted content method.
     */
    public function test_decrypted_content_method(): void
    {
        $file = ProductFile::factory()->create([
            'file_path' => 'test-file.zip',
            'encryption_key' => Crypt::encryptString('test-key'),
        ]);

        // File doesn't exist
        $this->assertNull($file->getDecryptedContent());

        // Create encrypted file content
        $originalContent = 'test file content';
        $encryptedContent = openssl_encrypt($originalContent, 'AES-256-CBC', 'test-key', 0, substr(hash('sha256', 'test-key'), 0, 16));
        Storage::disk('private')->put('test-file.zip', $encryptedContent);

        $decryptedContent = $file->getDecryptedContent();
        $this->assertEquals($originalContent, $decryptedContent);
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        $product = Product::factory()->create();

        ProductFile::factory()->create(['is_active' => true, 'product_id' => $product->id, 'file_type' => 'application/zip']);
        ProductFile::factory()->create(['is_active' => true, 'product_id' => $product->id, 'file_type' => 'application/pdf']);
        ProductFile::factory()->create(['is_active' => false, 'product_id' => $product->id, 'file_type' => 'application/zip']);
        ProductFile::factory()->create(['is_active' => true, 'download_count' => 100]);
        ProductFile::factory()->create(['is_active' => true, 'download_count' => 50]);

        $this->assertCount(3, ProductFile::active()->get());
        $this->assertCount(2, ProductFile::forProduct($product->id)->get());
        $this->assertCount(2, ProductFile::byType('application/zip')->get());

        $popular = ProductFile::popular()->get();
        $this->assertEquals(100, $popular->first()->download_count);
    }

    /**
     * Test increment download count.
     */
    public function test_increment_download_count(): void
    {
        $file = ProductFile::factory()->create(['download_count' => 5]);

        $result = $file->incrementDownloadCount();
        $this->assertTrue($result);
        $this->assertEquals(6, $file->fresh()->download_count);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Product file download count incremented');
        });
    }

    /**
     * Test activation methods.
     */
    public function test_activation_methods(): void
    {
        $file = ProductFile::factory()->create(['is_active' => false]);

        $result = $file->activate();
        $this->assertTrue($result);
        $this->assertTrue($file->fresh()->is_active);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Product file activated');
        });

        $result = $file->deactivate();
        $this->assertTrue($result);
        $this->assertFalse($file->fresh()->is_active);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Product file deactivated');
        });
    }

    /**
     * Test statistics.
     */
    public function test_statistics(): void
    {
        $product = Product::factory()->create();

        ProductFile::factory()->create(['is_active' => true, 'file_type' => 'application/zip', 'download_count' => 100]);
        ProductFile::factory()->create(['is_active' => true, 'file_type' => 'application/pdf', 'download_count' => 50]);
        ProductFile::factory()->create(['is_active' => false, 'file_type' => 'application/zip', 'download_count' => 25]);

        $statistics = ProductFile::getStatistics();

        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('active', $statistics);
        $this->assertArrayHasKey('total_downloads', $statistics);
        $this->assertArrayHasKey('by_type', $statistics);
        $this->assertArrayHasKey('popular_files', $statistics);

        $this->assertEquals(3, $statistics['total']);
        $this->assertEquals(2, $statistics['active']);
        $this->assertEquals(175, $statistics['total_downloads']);
        $this->assertEquals(2, $statistics['by_type']['application/zip']);
        $this->assertEquals(1, $statistics['by_type']['application/pdf']);
    }

    /**
     * Test static query methods.
     */
    public function test_static_query_methods(): void
    {
        $product = Product::factory()->create();

        ProductFile::factory()->create(['product_id' => $product->id, 'is_active' => true, 'download_count' => 100]);
        ProductFile::factory()->create(['product_id' => $product->id, 'is_active' => true, 'download_count' => 50]);
        ProductFile::factory()->create(['product_id' => $product->id, 'is_active' => false, 'download_count' => 25]);

        $files = ProductFile::getForProduct($product->id);
        $this->assertCount(2, $files); // Only active files

        $popular = ProductFile::getPopular(2);
        $this->assertCount(2, $popular);
        $this->assertEquals(100, $popular->first()->download_count);

        $recent = ProductFile::getRecent(2);
        $this->assertCount(2, $recent);
    }

    /**
     * Test search files method.
     */
    public function test_search_files_method(): void
    {
        ProductFile::factory()->create([
            'original_name' => 'test-file.zip',
            'description' => 'Test file for testing',
            'is_active' => true,
        ]);
        ProductFile::factory()->create([
            'original_name' => 'document.pdf',
            'description' => 'Important document',
            'is_active' => true,
        ]);
        ProductFile::factory()->create([
            'original_name' => 'another-file.zip',
            'description' => 'Another test file',
            'is_active' => false,
        ]);

        $results = ProductFile::searchFiles('test');
        $this->assertCount(1, $results);
        $this->assertEquals('test-file.zip', $results->first()->original_name);

        $results = ProductFile::searchFiles('document');
        $this->assertCount(1, $results);
        $this->assertEquals('document.pdf', $results->first()->original_name);
    }

    /**
     * Test configuration validation.
     */
    public function test_configuration_validation(): void
    {
        $validFile = ProductFile::factory()->create([
            'original_name' => 'test-file.zip',
            'file_path' => 'test-file.zip',
            'file_type' => 'application/zip',
            'file_size' => 1024,
            'encryption_key' => Crypt::encryptString('test-key'),
            'checksum' => 'abc123',
        ]);

        $invalidFile = ProductFile::factory()->create([
            'original_name' => '',
            'file_path' => '',
            'file_type' => '',
            'file_size' => 0,
            'encryption_key' => '',
            'checksum' => '',
        ]);

        $this->assertTrue($validFile->isValidConfiguration());
        $this->assertEmpty($validFile->validateConfiguration());

        $this->assertFalse($invalidFile->isValidConfiguration());
        $errors = $invalidFile->validateConfiguration();
        $this->assertContains('Original file name is required', $errors);
        $this->assertContains('File path is required', $errors);
        $this->assertContains('File type is required', $errors);
        $this->assertContains('Valid file size is required', $errors);
        $this->assertContains('Encryption key is required', $errors);
        $this->assertContains('File checksum is required', $errors);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $file = ProductFile::factory()->create([
            'file_size' => '1024',
            'download_count' => '5',
            'is_active' => '1',
            'product_id' => '1',
        ]);

        $this->assertIsInt($file->file_size);
        $this->assertIsInt($file->download_count);
        $this->assertIsBool($file->is_active);
        $this->assertIsInt($file->product_id);
    }

    /**
     * Test hidden attributes.
     */
    public function test_hidden_attributes(): void
    {
        $file = ProductFile::factory()->create([
            'encryption_key' => Crypt::encryptString('test-key'),
        ]);

        $array = $file->toArray();
        $this->assertArrayNotHasKey('encryption_key', $array);
    }
}
