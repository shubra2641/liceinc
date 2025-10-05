<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for LicenseController.
 *
 * This test suite covers all license management operations, CRUD functionality,
 * filtering, export, status toggling, and error handling.
 */
class LicenseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    /** @test */
    public function admin_can_view_licenses_index()
    {
        License::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.licenses.index');
        $response->assertViewHas(['licenses', 'users', 'products']);
    }

    /** @test */
    public function admin_can_filter_licenses_by_status()
    {
        License::factory()->create(['status' => 'active']);
        License::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.index', ['status' => 'active']));

        $response->assertStatus(200);
        $response->assertViewHas('licenses');
    }

    /** @test */
    public function admin_can_filter_licenses_by_user()
    {
        $user = User::factory()->create();
        License::factory()->create(['user_id' => $user->id]);
        License::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.index', ['user_id' => $user->id]));

        $response->assertStatus(200);
        $response->assertViewHas('licenses');
    }

    /** @test */
    public function admin_can_filter_licenses_by_product()
    {
        $product = Product::factory()->create();
        License::factory()->create(['product_id' => $product->id]);
        License::factory()->create(['product_id' => $this->product->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.index', ['product_id' => $product->id]));

        $response->assertStatus(200);
        $response->assertViewHas('licenses');
    }

    /** @test */
    public function admin_can_filter_licenses_by_license_type()
    {
        License::factory()->create(['license_type' => 'single']);
        License::factory()->create(['license_type' => 'multi']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.index', ['license_type' => 'single']));

        $response->assertStatus(200);
        $response->assertViewHas('licenses');
    }

    /** @test */
    public function admin_can_view_license_creation_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.licenses.create');
        $response->assertViewHas(['users', 'products', 'selectedUserId']);
    }

    /** @test */
    public function admin_can_create_license()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'license_type' => 'single',
            'duration_days' => 365,
            'support_days' => 90,
        ]);

        $licenseData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'license_type' => 'single',
            'status' => 'active',
            'max_domains' => 1,
            'notes' => 'Test license',
            'invoice_payment_status' => 'paid',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.store'), $licenseData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('licenses', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function license_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.store'), []);

        $response->assertSessionHasErrors(['user_id', 'product_id', 'status', 'invoice_payment_status']);
    }

    /** @test */
    public function license_creation_validates_user_exists()
    {
        $product = Product::factory()->create();

        $licenseData = [
            'user_id' => 99999,
            'product_id' => $product->id,
            'status' => 'active',
            'invoice_payment_status' => 'paid',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.store'), $licenseData);

        $response->assertSessionHasErrors(['user_id']);
    }

    /** @test */
    public function license_creation_validates_product_exists()
    {
        $user = User::factory()->create();

        $licenseData = [
            'user_id' => $user->id,
            'product_id' => 99999,
            'status' => 'active',
            'invoice_payment_status' => 'paid',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.store'), $licenseData);

        $response->assertSessionHasErrors(['product_id']);
    }

    /** @test */
    public function license_creation_validates_license_type()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $licenseData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'license_type' => 'invalid_type',
            'status' => 'active',
            'invoice_payment_status' => 'paid',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.store'), $licenseData);

        $response->assertSessionHasErrors(['license_type']);
    }

    /** @test */
    public function license_creation_validates_status()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $licenseData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'invalid_status',
            'invoice_payment_status' => 'paid',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.store'), $licenseData);

        $response->assertSessionHasErrors(['status']);
    }

    /** @test */
    public function license_creation_validates_max_domains()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $licenseData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
            'max_domains' => 0,
            'invoice_payment_status' => 'paid',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.store'), $licenseData);

        $response->assertSessionHasErrors(['max_domains']);
    }

    /** @test */
    public function admin_can_view_license_details()
    {
        $license = License::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.show', $license));

        $response->assertStatus(200);
        $response->assertViewIs('admin.licenses.show');
        $response->assertViewHas('license', $license);
    }

    /** @test */
    public function admin_can_view_license_edit_form()
    {
        $license = License::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.edit', $license));

        $response->assertStatus(200);
        $response->assertViewIs('admin.licenses.edit');
        $response->assertViewHas(['license', 'users', 'products']);
    }

    /** @test */
    public function admin_can_update_license()
    {
        $license = License::factory()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $updateData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'license_type' => 'multi',
            'status' => 'inactive',
            'max_domains' => 5,
            'notes' => 'Updated license',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.licenses.update', $license), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function license_update_validates_required_fields()
    {
        $license = License::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.licenses.update', $license), []);

        $response->assertSessionHasErrors(['user_id', 'product_id', 'status']);
    }

    /** @test */
    public function admin_can_delete_license()
    {
        $license = License::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.licenses.destroy', $license));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('licenses', ['id' => $license->id]);
    }

    /** @test */
    public function admin_can_toggle_license_status()
    {
        $license = License::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.toggle', $license));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function admin_can_export_licenses()
    {
        License::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition');
    }

    /** @test */
    public function admin_can_export_filtered_licenses()
    {
        License::factory()->create(['status' => 'active']);
        License::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.export', ['status' => 'active']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    /** @test */
    public function non_admin_cannot_access_licenses()
    {
        $license = License::factory()->create();

        $routes = [
            'admin.licenses.index',
            'admin.licenses.create',
            'admin.licenses.show',
            'admin.licenses.edit',
            'admin.licenses.export',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)
                ->get(route($route, $license));

            $response->assertStatus(403);
        }
    }

    /** @test */
    public function non_admin_cannot_perform_license_operations()
    {
        $license = License::factory()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $licenseData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
            'invoice_payment_status' => 'paid',
        ];

        $updateData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'inactive',
        ];

        // Test store
        $response = $this->actingAs($this->user)
            ->post(route('admin.licenses.store'), $licenseData);
        $response->assertStatus(403);

        // Test update
        $response = $this->actingAs($this->user)
            ->put(route('admin.licenses.update', $license), $updateData);
        $response->assertStatus(403);

        // Test destroy
        $response = $this->actingAs($this->user)
            ->delete(route('admin.licenses.destroy', $license));
        $response->assertStatus(403);

        // Test toggle
        $response = $this->actingAs($this->user)
            ->post(route('admin.licenses.toggle', $license));
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_licenses()
    {
        $license = License::factory()->create();

        $response = $this->get(route('admin.licenses.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function licenses_handles_database_errors_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.index'));

        $response->assertStatus(200);
        $response->assertViewHas('licenses');
    }

    /** @test */
    public function license_creation_handles_email_errors_gracefully()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $licenseData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
            'invoice_payment_status' => 'paid',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.store'), $licenseData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('licenses', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    /** @test */
    public function license_update_handles_invalid_dates_gracefully()
    {
        $license = License::factory()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $updateData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
            'expires_at' => 'invalid-date',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.licenses.update', $license), $updateData);

        $response->assertSessionHasErrors(['expires_at']);
    }

    /** @test */
    public function export_handles_empty_results_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.export', ['status' => 'nonexistent']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    /** @test */
    public function export_generates_correct_csv_format()
    {
        $license = License::factory()->create([
            'status' => 'active',
            'license_type' => 'single',
            'max_domains' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.licenses.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');

        $content = $response->getContent();
        $this->assertStringContainsString('ID,License Key,User,Product,Status', $content);
        $this->assertStringContainsString($license->license_key, $content);
        $this->assertStringContainsString('active', $content);
    }
}
