<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductFile;
use App\Models\User;
use App\Services\ProductFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * Test suite for ProductFileController.
 *
 * Tests all product file management functionality including:
 * - File uploads and management
 * - File downloads
 * - Status management
 * - Statistics and analytics
 * - Error handling and logging
 */
class ProductFileControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected User $customer;

    protected Product $product;

    protected ProductFileService $productFileService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);
        $this->admin->assignRole('admin');

        $this->customer = User::factory()->create([
            'email' => 'customer@test.com',
        ]);
        $this->customer->assignRole('customer');

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'is_active' => true,
        ]);

        // Mock ProductFileService
        $this->productFileService = Mockery::mock(ProductFileService::class);
        $this->app->instance(ProductFileService::class, $this->productFileService);

        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test admin can access product files index.
     */
    public function test_admin_can_access_product_files_index(): void
    {
        $this->productFileService->shouldReceive('getProductFiles')
            ->with($this->product, false)
            ->andReturn(collect());

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.files.index', $this->product));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.files.index');
        $response->assertViewHas(['product', 'files']);
    }

    /**
     * Test customer cannot access product files index.
     */
    public function test_customer_cannot_access_product_files_index(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.products.files.index', $this->product));

        $response->assertStatus(403);
    }

    /**
     * Test admin can upload file with valid data.
     */
    public function test_admin_can_upload_file_with_valid_data(): void
    {
        $file = UploadedFile::fake()->create('test-file.zip', 1024, 'application/zip');

        $fileData = [
            'id' => 1,
            'original_name' => 'test-file.zip',
            'formatted_size' => '1.00 KB',
            'file_type' => 'zip',
            'description' => 'Test file description',
            'download_count' => 0,
            'is_active' => true,
            'created_at' => now(),
        ];

        $this->productFileService->shouldReceive('uploadFile')
            ->with($this->product, $file, 'Test file description', true, 'zip')
            ->andReturn((object)$fileData);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.files.store', $this->product), [
                'file' => $file,
                'description' => 'Test file description',
                'is_active' => true,
                'file_type' => 'zip',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file' => [
                'id' => 1,
                'original_name' => 'test-file.zip',
                'file_size' => '1.00 KB',
                'file_type' => 'zip',
                'description' => 'Test file description',
                'download_count' => 0,
                'is_active' => true,
            ],
        ]);
    }

    /**
     * Test file upload fails with invalid data.
     */
    public function test_file_upload_fails_with_invalid_data(): void
    {
        $invalidFile = UploadedFile::fake()->create('document.exe', 100, 'application/x-executable');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.files.store', $this->product), [
                'file' => $invalidFile,
                'description' => str_repeat('a', 501), // Too long
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file', 'description']);
    }

    /**
     * Test file upload fails with large file.
     */
    public function test_file_upload_fails_with_large_file(): void
    {
        $largeFile = UploadedFile::fake()->create('large-file.zip', 102500, 'application/zip'); // 100MB+

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.files.store', $this->product), [
                'file' => $largeFile,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    /**
     * Test admin can download file.
     */
    public function test_admin_can_download_file(): void
    {
        $productFile = ProductFile::factory()->create([
            'product_id' => $this->product->id,
            'original_name' => 'test-file.zip',
        ]);

        $fileData = [
            'content' => 'fake file content',
            'mime_type' => 'application/zip',
            'filename' => 'test-file.zip',
            'size' => 1024,
        ];

        $this->productFileService->shouldReceive('downloadFile')
            ->with($productFile, $this->admin->id)
            ->andReturn($fileData);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.files.download', $productFile));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="test-file.zip"');
        $response->assertHeader('content-type', 'application/zip');
    }

    /**
     * Test download fails when file not found.
     */
    public function test_download_fails_when_file_not_found(): void
    {
        $productFile = ProductFile::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $this->productFileService->shouldReceive('downloadFile')
            ->with($productFile, $this->admin->id)
            ->andReturn(null);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.files.download', $productFile));

        $response->assertStatus(404);
    }

    /**
     * Test admin can update file.
     */
    public function test_admin_can_update_file(): void
    {
        $productFile = ProductFile::factory()->create([
            'product_id' => $this->product->id,
            'is_active' => true,
            'description' => 'Old description',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.files.update', $productFile), [
                'is_active' => false,
                'description' => 'Updated description',
                'file_type' => 'zip',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'File updated successfully',
            'file' => [
                'id' => $productFile->id,
                'is_active' => false,
                'description' => 'Updated description',
                'file_type' => 'zip',
            ],
        ]);

        $this->assertDatabaseHas('product_files', [
            'id' => $productFile->id,
            'is_active' => false,
            'description' => 'Updated description',
            'file_type' => 'zip',
        ]);
    }

    /**
     * Test file update fails with invalid data.
     */
    public function test_file_update_fails_with_invalid_data(): void
    {
        $productFile = ProductFile::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.files.update', $productFile), [
                'is_active' => 'invalid', // Not boolean
                'description' => str_repeat('a', 501), // Too long
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['is_active', 'description']);
    }

    /**
     * Test admin can delete file.
     */
    public function test_admin_can_delete_file(): void
    {
        $productFile = ProductFile::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $this->productFileService->shouldReceive('deleteFile')
            ->with($productFile)
            ->andReturn(true);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.products.files.destroy', $productFile));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Test file deletion fails when service returns false.
     */
    public function test_file_deletion_fails_when_service_returns_false(): void
    {
        $productFile = ProductFile::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $this->productFileService->shouldReceive('deleteFile')
            ->with($productFile)
            ->andReturn(false);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.products.files.destroy', $productFile));

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to delete file',
        ]);
    }

    /**
     * Test admin can get file statistics.
     */
    public function test_admin_can_get_file_statistics(): void
    {
        // Create test files
        ProductFile::factory()->count(3)->create([
            'product_id' => $this->product->id,
            'is_active' => true,
            'download_count' => 10,
            'file_size' => 1024,
        ]);

        ProductFile::factory()->count(2)->create([
            'product_id' => $this->product->id,
            'is_active' => false,
            'download_count' => 5,
            'file_size' => 2048,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.files.statistics', $this->product));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'statistics' => [
                'total_files' => 5,
                'active_files' => 3,
                'inactive_files' => 2,
                'total_downloads' => 40, // (3 * 10) + (2 * 5)
                'total_size' => 7168, // (3 * 1024) + (2 * 2048)
            ],
        ]);
    }

    /**
     * Test admin can get files for AJAX.
     */
    public function test_admin_can_get_files_for_ajax(): void
    {
        $files = collect([
            (object)[
                'id' => 1,
                'original_name' => 'test-file.zip',
                'formatted_size' => '1.00 KB',
                'file_type' => 'zip',
                'description' => 'Test file',
                'download_count' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->productFileService->shouldReceive('getProductFiles')
            ->with($this->product, false)
            ->andReturn($files);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.files.get-files', $this->product));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'files' => [
                [
                    'id' => 1,
                    'original_name' => 'test-file.zip',
                    'file_size' => '1.00 KB',
                    'file_type' => 'zip',
                    'description' => 'Test file',
                    'download_count' => 0,
                    'is_active' => true,
                ],
            ],
        ]);
    }

    /**
     * Test admin can toggle file status.
     */
    public function test_admin_can_toggle_file_status(): void
    {
        $productFile = ProductFile::factory()->create([
            'product_id' => $this->product->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.products.files.toggle-status', $productFile));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'File status updated successfully',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('product_files', [
            'id' => $productFile->id,
            'is_active' => false,
        ]);
    }

    /**
     * Test unauthorized access attempts.
     */
    public function test_unauthorized_access_returns_403(): void
    {
        $productFile = ProductFile::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $routes = [
            'admin.products.files.index',
            'admin.products.files.store',
            'admin.products.files.download',
            'admin.products.files.update',
            'admin.products.files.destroy',
            'admin.products.files.statistics',
            'admin.products.files.get-files',
            'admin.products.files.toggle-status',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->customer)
                ->get(route($route, $productFile));

            $response->assertStatus(403);
        }
    }

    /**
     * Test guest access attempts.
     */
    public function test_guest_access_redirects_to_login(): void
    {
        $response = $this->get(route('admin.products.files.index', $this->product));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test database transaction rollback on error.
     */
    public function test_database_transaction_rollback_on_error(): void
    {
        // Mock database error
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $file = UploadedFile::fake()->create('test-file.zip', 1024, 'application/zip');

        $this->productFileService->shouldReceive('uploadFile')
            ->andThrow(new \Exception('Service error'));

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.files.store', $this->product), [
                'file' => $file,
            ]);

        $response->assertStatus(500);
    }

    /**
     * Test validation error messages.
     */
    public function test_validation_error_messages(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.files.store', $this->product), [
                'file' => UploadedFile::fake()->create('document.exe', 100, 'application/x-executable'),
                'description' => str_repeat('a', 501),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file', 'description']);

        $errors = $response->json('errors');
        $this->assertArrayHasKey('file', $errors);
        $this->assertArrayHasKey('description', $errors);
    }

    /**
     * Test file type validation.
     */
    public function test_file_type_validation(): void
    {
        $allowedTypes = [
            'zip', 'rar', '7z', 'tar', 'gz', 'bz2',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'md', 'json', 'xml', 'php', 'js', 'css', 'html', 'htm',
            'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico',
            'woff', 'woff2', 'ttf', 'eot',
        ];

        foreach ($allowedTypes as $type) {
            $file = UploadedFile::fake()->create("test-file.{$type}", 1024, "application/{$type}");

            $this->productFileService->shouldReceive('uploadFile')
                ->andReturn((object)['id' => 1]);

            $response = $this->actingAs($this->admin)
                ->post(route('admin.products.files.store', $this->product), [
                    'file' => $file,
                ]);

            $response->assertStatus(200);
        }
    }

    /**
     * Test string field trimming.
     */
    public function test_string_field_trimming(): void
    {
        $file = UploadedFile::fake()->create('test-file.zip', 1024, 'application/zip');

        $this->productFileService->shouldReceive('uploadFile')
            ->with($this->product, $file, 'Test description', true, 'zip')
            ->andReturn((object)['id' => 1]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.files.store', $this->product), [
                'file' => $file,
                'description' => '  Test description  ',
                'file_type' => '  zip  ',
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test boolean field handling.
     */
    public function test_boolean_field_handling(): void
    {
        $file = UploadedFile::fake()->create('test-file.zip', 1024, 'application/zip');

        $this->productFileService->shouldReceive('uploadFile')
            ->with($this->product, $file, null, false, null)
            ->andReturn((object)['id' => 1]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.files.store', $this->product), [
                'file' => $file,
                'is_active' => '0',
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test file statistics with empty product.
     */
    public function test_file_statistics_with_empty_product(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.files.statistics', $this->product));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'statistics' => [
                'total_files' => 0,
                'active_files' => 0,
                'inactive_files' => 0,
                'total_downloads' => 0,
                'total_size' => 0,
                'formatted_total_size' => '0 B',
                'average_file_size' => 0,
                'formatted_average_size' => '0 B',
                'most_downloaded_file' => null,
                'file_types' => [],
            ],
        ]);
    }

    /**
     * Test format bytes helper method.
     */
    public function test_format_bytes_helper_method(): void
    {
        $productFile = ProductFile::factory()->create([
            'product_id' => $this->product->id,
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.files.statistics', $this->product));

        $response->assertStatus(200);
        $data = $response->json('statistics');
        $this->assertEquals('1.00 KB', $data['formatted_total_size']);
    }
}
