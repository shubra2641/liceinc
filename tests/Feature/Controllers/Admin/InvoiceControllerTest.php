<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for InvoiceController.
 *
 * This test suite covers all invoice management operations, CRUD functionality,
 * filtering, export, status management, and error handling.
 */
class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected Product $product;

    protected License $license;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
        $this->license = License::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    /** @test */
    public function admin_can_view_invoices_index()
    {
        Invoice::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.invoices.index');
        $response->assertViewHas(['invoices', 'stats', 'users']);
    }

    /** @test */
    public function admin_can_filter_invoices_by_status()
    {
        Invoice::factory()->create(['status' => 'pending']);
        Invoice::factory()->create(['status' => 'paid']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }

    /** @test */
    public function admin_can_filter_invoices_by_type()
    {
        Invoice::factory()->create(['type' => 'initial']);
        Invoice::factory()->create(['type' => 'renewal']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.index', ['type' => 'initial']));

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }

    /** @test */
    public function admin_can_filter_invoices_by_user()
    {
        $user = User::factory()->create();
        Invoice::factory()->create(['user_id' => $user->id]);
        Invoice::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.index', ['user_id' => $user->id]));

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }

    /** @test */
    public function admin_can_filter_invoices_by_date_range()
    {
        Invoice::factory()->create(['created_at' => now()->subDays(5)]);
        Invoice::factory()->create(['created_at' => now()->subDays(10)]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.index', [
                'date_from' => now()->subDays(7)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }

    /** @test */
    public function admin_can_view_invoice_creation_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.invoices.create');
        $response->assertViewHas(['users', 'licenses', 'selectedUserId']);
    }

    /** @test */
    public function admin_can_create_regular_invoice()
    {
        $invoiceData = [
            'user_id' => $this->user->id,
            'license_id' => $this->license->id,
            'type' => 'initial',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Test invoice',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), $invoiceData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->user->id,
            'license_id' => $this->license->id,
            'type' => 'initial',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function admin_can_create_custom_invoice()
    {
        $invoiceData = [
            'user_id' => $this->user->id,
            'license_id' => 'custom',
            'type' => 'custom',
            'amount' => 149.99,
            'currency' => 'EUR',
            'status' => 'pending',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Custom invoice',
            'custom_invoice_type' => 'one_time',
            'custom_product_name' => 'Custom Product',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), $invoiceData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->user->id,
            'license_id' => null,
            'type' => 'custom',
            'amount' => 149.99,
            'currency' => 'EUR',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function invoice_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), []);

        $response->assertSessionHasErrors(['user_id', 'type', 'amount', 'currency', 'status']);
    }

    /** @test */
    public function invoice_creation_validates_user_exists()
    {
        $invoiceData = [
            'user_id' => 99999,
            'license_id' => $this->license->id,
            'type' => 'initial',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), $invoiceData);

        $response->assertSessionHasErrors(['user_id']);
    }

    /** @test */
    public function invoice_creation_validates_license_exists()
    {
        $invoiceData = [
            'user_id' => $this->user->id,
            'license_id' => 99999,
            'type' => 'initial',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), $invoiceData);

        $response->assertSessionHasErrors(['license_id']);
    }

    /** @test */
    public function invoice_creation_validates_type()
    {
        $invoiceData = [
            'user_id' => $this->user->id,
            'license_id' => $this->license->id,
            'type' => 'invalid_type',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), $invoiceData);

        $response->assertSessionHasErrors(['type']);
    }

    /** @test */
    public function invoice_creation_validates_amount()
    {
        $invoiceData = [
            'user_id' => $this->user->id,
            'license_id' => $this->license->id,
            'type' => 'initial',
            'amount' => -10,
            'currency' => 'USD',
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), $invoiceData);

        $response->assertSessionHasErrors(['amount']);
    }

    /** @test */
    public function invoice_creation_validates_currency()
    {
        $invoiceData = [
            'user_id' => $this->user->id,
            'license_id' => $this->license->id,
            'type' => 'initial',
            'amount' => 99.99,
            'currency' => 'INVALID',
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), $invoiceData);

        $response->assertSessionHasErrors(['currency']);
    }

    /** @test */
    public function invoice_creation_validates_status()
    {
        $invoiceData = [
            'user_id' => $this->user->id,
            'license_id' => $this->license->id,
            'type' => 'initial',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), $invoiceData);

        $response->assertSessionHasErrors(['status']);
    }

    /** @test */
    public function admin_can_view_invoice_details()
    {
        $invoice = Invoice::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.show', $invoice));

        $response->assertStatus(200);
        $response->assertViewIs('admin.invoices.show');
        $response->assertViewHas('invoice', $invoice);
    }

    /** @test */
    public function admin_can_mark_invoice_as_paid()
    {
        $invoice = Invoice::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.mark-as-paid', $invoice));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
    }

    /** @test */
    public function admin_can_cancel_invoice()
    {
        $invoice = Invoice::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.cancel', $invoice));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function admin_can_view_invoice_edit_form()
    {
        $invoice = Invoice::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.edit', $invoice));

        $response->assertStatus(200);
        $response->assertViewIs('admin.invoices.edit');
        $response->assertViewHas(['invoice', 'users', 'licenses']);
    }

    /** @test */
    public function admin_can_update_invoice()
    {
        $invoice = Invoice::factory()->create();
        $user = User::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'user_id' => $user->id,
            'license_id' => $license->id,
            'type' => 'renewal',
            'amount' => 149.99,
            'currency' => 'EUR',
            'status' => 'paid',
            'notes' => 'Updated invoice',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.invoices.update', $invoice), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'user_id' => $user->id,
            'license_id' => $license->id,
            'type' => 'renewal',
            'amount' => 149.99,
            'currency' => 'EUR',
            'status' => 'paid',
        ]);
    }

    /** @test */
    public function invoice_update_validates_required_fields()
    {
        $invoice = Invoice::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.invoices.update', $invoice), []);

        $response->assertSessionHasErrors(['user_id', 'type', 'amount', 'currency', 'status']);
    }

    /** @test */
    public function admin_can_delete_pending_invoice()
    {
        $invoice = Invoice::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.invoices.destroy', $invoice));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    /** @test */
    public function admin_cannot_delete_paid_invoice()
    {
        $invoice = Invoice::factory()->create(['status' => 'paid']);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.invoices.destroy', $invoice));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete a paid invoice');

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    /** @test */
    public function admin_can_export_invoices()
    {
        Invoice::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition');
    }

    /** @test */
    public function admin_can_export_filtered_invoices()
    {
        Invoice::factory()->create(['status' => 'pending']);
        Invoice::factory()->create(['status' => 'paid']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.export', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    /** @test */
    public function non_admin_cannot_access_invoices()
    {
        $invoice = Invoice::factory()->create();

        $routes = [
            'admin.invoices.index',
            'admin.invoices.create',
            'admin.invoices.show',
            'admin.invoices.edit',
            'admin.invoices.export',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)
                ->get(route($route, $invoice));

            $response->assertStatus(403);
        }
    }

    /** @test */
    public function non_admin_cannot_perform_invoice_operations()
    {
        $invoice = Invoice::factory()->create();
        $user = User::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id]);

        $invoiceData = [
            'user_id' => $user->id,
            'license_id' => $license->id,
            'type' => 'initial',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
        ];

        $updateData = [
            'user_id' => $user->id,
            'license_id' => $license->id,
            'type' => 'renewal',
            'amount' => 149.99,
            'currency' => 'EUR',
            'status' => 'paid',
        ];

        // Test store
        $response = $this->actingAs($this->user)
            ->post(route('admin.invoices.store'), $invoiceData);
        $response->assertStatus(403);

        // Test update
        $response = $this->actingAs($this->user)
            ->put(route('admin.invoices.update', $invoice), $updateData);
        $response->assertStatus(403);

        // Test destroy
        $response = $this->actingAs($this->user)
            ->delete(route('admin.invoices.destroy', $invoice));
        $response->assertStatus(403);

        // Test mark as paid
        $response = $this->actingAs($this->user)
            ->post(route('admin.invoices.mark-as-paid', $invoice));
        $response->assertStatus(403);

        // Test cancel
        $response = $this->actingAs($this->user)
            ->post(route('admin.invoices.cancel', $invoice));
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_invoices()
    {
        $invoice = Invoice::factory()->create();

        $response = $this->get(route('admin.invoices.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function invoices_handles_database_errors_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.index'));

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }

    /** @test */
    public function invoice_creation_handles_license_not_found_gracefully()
    {
        $invoiceData = [
            'user_id' => $this->user->id,
            'license_id' => 99999,
            'type' => 'initial',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), $invoiceData);

        $response->assertSessionHasErrors(['license_id']);
    }

    /** @test */
    public function invoice_update_handles_license_not_found_gracefully()
    {
        $invoice = Invoice::factory()->create();
        $user = User::factory()->create();

        $updateData = [
            'user_id' => $user->id,
            'license_id' => 99999,
            'type' => 'renewal',
            'amount' => 149.99,
            'currency' => 'EUR',
            'status' => 'paid',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.invoices.update', $invoice), $updateData);

        $response->assertSessionHasErrors(['license_id']);
    }

    /** @test */
    public function export_handles_empty_results_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.export', ['status' => 'nonexistent']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    /** @test */
    public function export_generates_correct_csv_format()
    {
        $invoice = Invoice::factory()->create([
            'status' => 'pending',
            'type' => 'initial',
            'amount' => 99.99,
            'currency' => 'USD',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');

        $content = $response->getContent();
        $this->assertStringContainsString('ID,Invoice Number,User,Product,License Key,Type,Amount,Currency,Status', $content);
        $this->assertStringContainsString($invoice->invoice_number, $content);
        $this->assertStringContainsString('pending', $content);
        $this->assertStringContainsString('initial', $content);
    }
}
