<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * Test suite for ProductUpdateController.
 *
 * Tests all product update management functionality including:
 * - CRUD operations for product updates
 * - File upload and management
 * - Status toggling
 * - AJAX endpoints
 * - File downloads
 * - Error handling and logging
 */
class ProductUpdateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected User $customer;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'customer@test.com',
        ]);

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'is_active' => true,
        ]);

        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test admin can access product updates index.
     */
    public function test_admin_can_access_product_updates_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-updates.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product-updates.index');
        $response->assertViewHas(['updates', 'products', 'productId', 'product']);
    }

    /**
     * Test product updates index with product filter.
     */
    public function test_product_updates_index_with_product_filter(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-updates.index', ['product_id' => $this->product->id]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product-updates.index');
        $response->assertViewHas('product', $this->product);
    }

    /**
     * Test customer cannot access product updates index.
     */
    public function test_customer_cannot_access_product_updates_index(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.product-updates.index'));

        $response->assertStatus(403);
    }

    /**
     * Test admin can access product update creation form.
     */
    public function test_admin_can_access_product_update_creation_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-updates.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product-updates.create');
        $response->assertViewHas('products');
    }

    /**
     * Test product update creation form with product filter.
     */
    public function test_product_update_creation_form_with_product_filter(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-updates.create', ['product_id' => $this->product->id]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product-updates.create');
        $response->assertViewHas('product', $this->product);
    }

    /**
     * Test admin can create product update with valid data.
     */
    public function test_admin_can_create_product_update_with_valid_data(): void
    {
        $updateFile = UploadedFile::fake()->create('update.zip', 1024, 'application/zip');

        $updateData = [
            'product_id' => $this->product->id,
            'version' => '1.0.0',
            'title' => 'Test Update',
            'description' => 'Test update description',
            'changelog' => "New feature\nBug fix\nImprovement",
            'update_file' => $updateFile,
            'is_major' => true,
            'is_required' => false,
            'requirements' => ['PHP 8.0+', 'Laravel 9.0+'],
            'compatibility' => ['Windows', 'Linux', 'macOS'],
            'released_at' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-updates.store'), $updateData);

        $response->assertRedirect(route('admin.product-updates.index'));
        $response->assertSessionHas('success', 'Product update created successfully');

        $this->assertDatabaseHas('product_updates', [
            'product_id' => $this->product->id,
            'version' => '1.0.0',
            'title' => 'Test Update',
            'description' => 'Test update description',
            'is_major' => true,
            'is_required' => false,
            'is_active' => true,
        ]);

        // Check that file was stored
        Storage::disk('local')->assertExists('product-updates/update_test-product_1.0.0_'.time().'.zip');
    }

    /**
     * Test product update creation fails with invalid data.
     */
    public function test_product_update_creation_fails_with_invalid_data(): void
    {
        $invalidData = [
            'product_id' => 999, // Non-existent product
            'version' => '', // Required field missing
            'title' => '', // Required field missing
            'update_file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'), // Wrong file type
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-updates.store'), $invalidData);

        $response->assertSessionHasErrors([
            'product_id', 'version', 'title', 'update_file',
        ]);
    }

    /**
     * Test product update creation fails with duplicate version.
     */
    public function test_product_update_creation_fails_with_duplicate_version(): void
    {
        // Create existing update
        ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
            'version' => '1.0.0',
        ]);

        $updateFile = UploadedFile::fake()->create('update.zip', 1024, 'application/zip');

        $updateData = [
            'product_id' => $this->product->id,
            'version' => '1.0.0', // Duplicate version
            'title' => 'Test Update',
            'update_file' => $updateFile,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-updates.store'), $updateData);

        $response->assertSessionHasErrors(['version']);
    }

    /**
     * Test admin can view product update details.
     */
    public function test_admin_can_view_product_update_details(): void
    {
        $productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-updates.show', $productUpdate));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product-updates.show');
        $response->assertViewHas('productUpdate', $productUpdate);
    }

    /**
     * Test admin can access product update edit form.
     */
    public function test_admin_can_access_product_update_edit_form(): void
    {
        $productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-updates.edit', $productUpdate));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product-updates.edit');
        $response->assertViewHas(['productUpdate', 'products']);
    }

    /**
     * Test admin can update product update with valid data.
     */
    public function test_admin_can_update_product_update_with_valid_data(): void
    {
        $productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
            'version' => '1.0.0',
        ]);

        $updateData = [
            'product_id' => $this->product->id,
            'version' => '1.1.0',
            'title' => 'Updated Test Update',
            'description' => 'Updated description',
            'changelog' => "Updated feature\nNew bug fix",
            'is_major' => false,
            'is_required' => true,
            'is_active' => true,
            'requirements' => ['PHP 8.1+'],
            'compatibility' => ['Windows', 'Linux'],
            'released_at' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-updates.update', $productUpdate), $updateData);

        $response->assertRedirect(route('admin.product-updates.index'));
        $response->assertSessionHas('success', 'Product update updated successfully');

        $this->assertDatabaseHas('product_updates', [
            'id' => $productUpdate->id,
            'version' => '1.1.0',
            'title' => 'Updated Test Update',
            'description' => 'Updated description',
            'is_major' => false,
            'is_required' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Test product update with file upload.
     */
    public function test_product_update_with_file_upload(): void
    {
        $productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
            'file_path' => 'product-updates/old-update.zip',
        ]);

        // Create old file
        Storage::disk('local')->put('product-updates/old-update.zip', 'fake content');

        $newUpdateFile = UploadedFile::fake()->create('new-update.zip', 1024, 'application/zip');

        $updateData = [
            'product_id' => $this->product->id,
            'version' => '1.2.0',
            'title' => 'Updated Test Update',
            'update_file' => $newUpdateFile,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-updates.update', $productUpdate), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Product update updated successfully');

        // Check that old file was deleted
        Storage::disk('local')->assertMissing('product-updates/old-update.zip');
        // Check that new file was stored
        Storage::disk('local')->assertExists('product-updates/update_test-product_1.2.0_'.time().'.zip');
    }

    /**
     * Test product update fails with invalid data.
     */
    public function test_product_update_fails_with_invalid_data(): void
    {
        $productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $invalidData = [
            'product_id' => 999, // Non-existent product
            'version' => '', // Required field missing
            'title' => '', // Required field missing
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product-updates.update', $productUpdate), $invalidData);

        $response->assertSessionHasErrors([
            'product_id', 'version', 'title',
        ]);
    }

    /**
     * Test admin can delete product update.
     */
    public function test_admin_can_delete_product_update(): void
    {
        $productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
            'file_path' => 'product-updates/test-update.zip',
        ]);

        // Create file
        Storage::disk('local')->put('product-updates/test-update.zip', 'fake content');

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.product-updates.destroy', $productUpdate));

        $response->assertRedirect(route('admin.product-updates.index'));
        $response->assertSessionHas('success', 'Product update deleted successfully');

        $this->assertDatabaseMissing('product_updates', [
            'id' => $productUpdate->id,
        ]);

        // Check that file was deleted
        Storage::disk('local')->assertMissing('product-updates/test-update.zip');
    }

    /**
     * Test admin can toggle product update status.
     */
    public function test_admin_can_toggle_product_update_status(): void
    {
        $productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.product-updates.toggle-status', $productUpdate), [
                'is_active' => false,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Update status updated successfully',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('product_updates', [
            'id' => $productUpdate->id,
            'is_active' => false,
        ]);
    }

    /**
     * Test get product updates AJAX endpoint.
     */
    public function test_get_product_updates_ajax_endpoint(): void
    {
        // Create multiple updates
        ProductUpdate::factory()->count(3)->create([
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-updates.get-updates', ['product_id' => $this->product->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json();
        $this->assertCount(3, $data['updates']);
    }

    /**
     * Test get product updates AJAX endpoint fails without product ID.
     */
    public function test_get_product_updates_ajax_endpoint_fails_without_product_id(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-updates.get-updates'));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Product ID is required',
        ]);
    }

    /**
     * Test admin can download product update file.
     */
    public function test_admin_can_download_product_update_file(): void
    {
        $productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
            'file_path' => 'product-updates/test-update.zip',
            'file_name' => 'test-update.zip',
        ]);

        // Create file
        Storage::disk('local')->put('product-updates/test-update.zip', 'fake content');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-updates.download', $productUpdate));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=test-update.zip');
    }

    /**
     * Test download fails when file not found.
     */
    public function test_download_fails_when_file_not_found(): void
    {
        $productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
            'file_path' => 'product-updates/non-existent.zip',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-updates.download', $productUpdate));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error' => 'Update file not found']);
    }

    /**
     * Test unauthorized access attempts.
     */
    public function test_unauthorized_access_returns_403(): void
    {
        $productUpdate = ProductUpdate::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $routes = [
            'admin.product-updates.index',
            'admin.product-updates.create',
            'admin.product-updates.store',
            'admin.product-updates.show',
            'admin.product-updates.edit',
            'admin.product-updates.update',
            'admin.product-updates.destroy',
            'admin.product-updates.toggle-status',
            'admin.product-updates.get-updates',
            'admin.product-updates.download',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->customer)
                ->get(route($route, $productUpdate));

            $response->assertStatus(403);
        }
    }

    /**
     * Test guest access attempts.
     */
    public function test_guest_access_redirects_to_login(): void
    {
        $response = $this->get(route('admin.product-updates.index'));
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

        $updateFile = UploadedFile::fake()->create('update.zip', 1024, 'application/zip');

        $updateData = [
            'product_id' => $this->product->id,
            'version' => '1.0.0',
            'title' => 'Test Update',
            'update_file' => $updateFile,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-updates.store'), $updateData);

        // Should handle error gracefully
        $response->assertRedirect();
    }

    /**
     * Test validation error messages.
     */
    public function test_validation_error_messages(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-updates.store'), [
                'product_id' => 999,
                'version' => '',
                'title' => '',
                'update_file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ]);

        $response->assertSessionHasErrors(['product_id', 'version', 'title', 'update_file']);

        $errors = $response->session()->get('errors')->getBag('default');
        $this->assertTrue($errors->has('product_id'));
        $this->assertTrue($errors->has('version'));
        $this->assertTrue($errors->has('title'));
        $this->assertTrue($errors->has('update_file'));
    }

    /**
     * Test version format validation.
     */
    public function test_version_format_validation(): void
    {
        $updateFile = UploadedFile::fake()->create('update.zip', 1024, 'application/zip');

        $invalidVersions = [
            'invalid-version',
            '1.0',
            'v1.0.0',
            '1.0.0.0.0',
        ];

        foreach ($invalidVersions as $version) {
            $response = $this->actingAs($this->admin)
                ->post(route('admin.product-updates.store'), [
                    'product_id' => $this->product->id,
                    'version' => $version,
                    'title' => 'Test Update',
                    'update_file' => $updateFile,
                ]);

            $response->assertSessionHasErrors(['version']);
        }
    }

    /**
     * Test valid version formats.
     */
    public function test_valid_version_formats(): void
    {
        $updateFile = UploadedFile::fake()->create('update.zip', 1024, 'application/zip');

        $validVersions = [
            '1.0.0',
            '1.0.0-beta',
            '2.1.3',
            '1.0.0-alpha.1',
        ];

        foreach ($validVersions as $version) {
            $response = $this->actingAs($this->admin)
                ->post(route('admin.product-updates.store'), [
                    'product_id' => $this->product->id,
                    'version' => $version,
                    'title' => 'Test Update',
                    'update_file' => $updateFile,
                ]);

            $response->assertRedirect();
            $response->assertSessionHas('success', 'Product update created successfully');
        }
    }

    /**
     * Test file size validation.
     */
    public function test_file_size_validation(): void
    {
        $largeFile = UploadedFile::fake()->create('large-update.zip', 60000, 'application/zip'); // 60MB

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-updates.store'), [
                'product_id' => $this->product->id,
                'version' => '1.0.0',
                'title' => 'Test Update',
                'update_file' => $largeFile,
            ]);

        $response->assertSessionHasErrors(['update_file']);
    }

    /**
     * Test changelog text to array conversion.
     */
    public function test_changelog_text_to_array_conversion(): void
    {
        $updateFile = UploadedFile::fake()->create('update.zip', 1024, 'application/zip');

        $changelogText = "New feature\n\nBug fix\n  \nImprovement\n\n";

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-updates.store'), [
                'product_id' => $this->product->id,
                'version' => '1.0.0',
                'title' => 'Test Update',
                'changelog' => $changelogText,
                'update_file' => $updateFile,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Product update created successfully');

        $update = ProductUpdate::where('version', '1.0.0')->first();
        $this->assertEquals(['New feature', 'Bug fix', 'Improvement'], $update->changelog);
    }

    /**
     * Test string field trimming.
     */
    public function test_string_field_trimming(): void
    {
        $updateFile = UploadedFile::fake()->create('update.zip', 1024, 'application/zip');

        $updateData = [
            'product_id' => $this->product->id,
            'version' => '  1.0.0  ',
            'title' => '  Test Update  ',
            'description' => '  Test description  ',
            'changelog' => '  Test changelog  ',
            'update_file' => $updateFile,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-updates.store'), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Product update created successfully');

        $this->assertDatabaseHas('product_updates', [
            'version' => '1.0.0',
            'title' => 'Test Update',
            'description' => 'Test description',
        ]);
    }

    /**
     * Test boolean field handling.
     */
    public function test_boolean_field_handling(): void
    {
        $updateFile = UploadedFile::fake()->create('update.zip', 1024, 'application/zip');

        $updateData = [
            'product_id' => $this->product->id,
            'version' => '1.0.0',
            'title' => 'Test Update',
            'update_file' => $updateFile,
            'is_major' => '1',
            'is_required' => 'true',
            'is_active' => 'on',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-updates.store'), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Product update created successfully');

        $this->assertDatabaseHas('product_updates', [
            'version' => '1.0.0',
            'is_major' => true,
            'is_required' => true,
            'is_active' => true,
        ]);
    }
}
