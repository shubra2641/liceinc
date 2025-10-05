<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/**
 * Test suite for TicketController.
 *
 * Tests all ticket management functionality including:
 * - CRUD operations for tickets
 * - Ticket replies and status updates
 * - Invoice creation and management
 * - Email notifications
 * - Validation and error handling
 * - Authorization and access control
 */
class TicketControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected User $customer;

    protected TicketCategory $category;

    protected Product $product;

    protected Ticket $ticket;

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

        // Create test category
        $this->category = TicketCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'duration_days' => 30,
        ]);

        // Create test ticket
        $this->ticket = Ticket::factory()->create([
            'user_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'subject' => 'Test Ticket',
            'priority' => 'medium',
            'status' => 'open',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test admin can access tickets index.
     */
    public function test_admin_can_access_tickets_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.tickets.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.tickets.index');
        $response->assertViewHas('tickets');
    }

    /**
     * Test customer cannot access tickets index.
     */
    public function test_customer_cannot_access_tickets_index(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.tickets.index'));

        $response->assertStatus(403);
    }

    /**
     * Test admin can access create ticket form.
     */
    public function test_admin_can_access_create_ticket_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.tickets.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.tickets.create');
        $response->assertViewHas(['users', 'categories', 'products']);
    }

    /**
     * Test admin can create ticket with valid data.
     */
    public function test_admin_can_create_ticket_with_valid_data(): void
    {
        $ticketData = [
            'user_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'subject' => 'New Test Ticket',
            'priority' => 'high',
            'content' => 'This is a test ticket content with enough characters.',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.store'), $ticketData);

        $response->assertRedirect(route('admin.tickets.index'));
        $response->assertSessionHas('success', 'Ticket created successfully for user');

        $this->assertDatabaseHas('tickets', [
            'user_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'subject' => 'New Test Ticket',
            'priority' => 'high',
            'status' => 'open',
        ]);
    }

    /**
     * Test ticket creation with invoice.
     */
    public function test_admin_can_create_ticket_with_invoice(): void
    {
        $ticketData = [
            'user_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'subject' => 'Ticket with Invoice',
            'priority' => 'medium',
            'content' => 'This is a test ticket with invoice creation.',
            'create_invoice' => true,
            'invoice_product_id' => $this->product->id,
            'billing_type' => 'one_time',
            'invoice_amount' => 150.00,
            'invoice_duration_days' => 60,
            'invoice_status' => 'pending',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.store'), $ticketData);

        $response->assertRedirect(route('admin.tickets.index'));

        $this->assertDatabaseHas('tickets', [
            'user_id' => $this->customer->id,
            'subject' => 'Ticket with Invoice',
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'amount' => 150.00,
            'status' => 'pending',
        ]);
    }

    /**
     * Test ticket creation fails with invalid data.
     */
    public function test_ticket_creation_fails_with_invalid_data(): void
    {
        $invalidData = [
            'user_id' => '', // Required field missing
            'category_id' => 999, // Non-existent category
            'subject' => '', // Required field missing
            'priority' => 'invalid', // Invalid priority
            'content' => 'short', // Too short content
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.store'), $invalidData);

        $response->assertSessionHasErrors(['user_id', 'category_id', 'subject', 'priority', 'content']);
    }

    /**
     * Test admin can view ticket details.
     */
    public function test_admin_can_view_ticket_details(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.tickets.show', $this->ticket));

        $response->assertStatus(200);
        $response->assertViewIs('admin.tickets.show');
        $response->assertViewHas('ticket', $this->ticket);
    }

    /**
     * Test admin can access edit ticket form.
     */
    public function test_admin_can_access_edit_ticket_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.tickets.edit', $this->ticket));

        $response->assertStatus(200);
        $response->assertViewIs('admin.tickets.edit');
        $response->assertViewHas(['ticket', 'categories']);
    }

    /**
     * Test admin can update ticket with valid data.
     */
    public function test_admin_can_update_ticket_with_valid_data(): void
    {
        $updateData = [
            'subject' => 'Updated Ticket Subject',
            'priority' => 'high',
            'status' => 'pending',
            'content' => 'This is updated ticket content with enough characters.',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.tickets.update', $this->ticket), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Ticket updated');

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'subject' => 'Updated Ticket Subject',
            'priority' => 'high',
            'status' => 'pending',
        ]);
    }

    /**
     * Test ticket update fails with invalid data.
     */
    public function test_ticket_update_fails_with_invalid_data(): void
    {
        $invalidData = [
            'subject' => '', // Required field missing
            'priority' => 'invalid', // Invalid priority
            'status' => 'invalid', // Invalid status
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.tickets.update', $this->ticket), $invalidData);

        $response->assertSessionHasErrors(['subject', 'priority', 'status']);
    }

    /**
     * Test admin can delete ticket.
     */
    public function test_admin_can_delete_ticket(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.tickets.destroy', $this->ticket));

        $response->assertRedirect(route('admin.tickets.index'));
        $response->assertSessionHas('success', 'Ticket deleted');

        $this->assertDatabaseMissing('tickets', [
            'id' => $this->ticket->id,
        ]);
    }

    /**
     * Test admin can add reply to ticket.
     */
    public function test_admin_can_add_reply_to_ticket(): void
    {
        $replyData = [
            'message' => 'This is a test reply message with enough characters.',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.reply', $this->ticket), $replyData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Reply added');

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->admin->id,
            'message' => 'This is a test reply message with enough characters.',
        ]);
    }

    /**
     * Test ticket reply fails with invalid data.
     */
    public function test_ticket_reply_fails_with_invalid_data(): void
    {
        $invalidData = [
            'message' => 'short', // Too short message
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.reply', $this->ticket), $invalidData);

        $response->assertSessionHasErrors(['message']);
    }

    /**
     * Test admin can update ticket status.
     */
    public function test_admin_can_update_ticket_status(): void
    {
        $statusData = [
            'status' => 'resolved',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.update-status', $this->ticket), $statusData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Ticket status updated to Resolved');

        $this->assertDatabaseHas('tickets', [
            'id' => $this->ticket->id,
            'status' => 'resolved',
        ]);
    }

    /**
     * Test ticket status update fails with invalid status.
     */
    public function test_ticket_status_update_fails_with_invalid_status(): void
    {
        $invalidData = [
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.update-status', $this->ticket), $invalidData);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * Test email service is called when adding reply.
     */
    public function test_email_service_called_when_adding_reply(): void
    {
        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldReceive('sendTicketReply')
            ->once()
            ->andReturn(true);

        $this->app->instance(EmailService::class, $emailService);

        $replyData = [
            'message' => 'This is a test reply message with enough characters.',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.reply', $this->ticket), $replyData);

        $response->assertRedirect();
    }

    /**
     * Test email service is called when updating status.
     */
    public function test_email_service_called_when_updating_status(): void
    {
        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldReceive('sendTicketStatusUpdate')
            ->once()
            ->andReturn(true);

        $this->app->instance(EmailService::class, $emailService);

        $statusData = [
            'status' => 'resolved',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.update-status', $this->ticket), $statusData);

        $response->assertRedirect();
    }

    /**
     * Test unauthorized access attempts.
     */
    public function test_unauthorized_access_returns_403(): void
    {
        $routes = [
            'admin.tickets.index',
            'admin.tickets.create',
            'admin.tickets.store',
            'admin.tickets.show',
            'admin.tickets.edit',
            'admin.tickets.update',
            'admin.tickets.destroy',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->customer)
                ->get(route($route, $this->ticket));

            $response->assertStatus(403);
        }
    }

    /**
     * Test guest access attempts.
     */
    public function test_guest_access_redirects_to_login(): void
    {
        $response = $this->get(route('admin.tickets.index'));
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

        // This should trigger an error and rollback
        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.store'), [
                'user_id' => $this->customer->id,
                'category_id' => $this->category->id,
                'subject' => 'Test Ticket',
                'priority' => 'medium',
                'content' => 'This is a test ticket content.',
            ]);

        // Should handle error gracefully
        $response->assertRedirect();
    }

    /**
     * Test validation error messages.
     */
    public function test_validation_error_messages(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.store'), [
                'user_id' => '',
                'category_id' => '',
                'subject' => '',
                'priority' => 'invalid',
                'content' => 'short',
            ]);

        $response->assertSessionHasErrors(['user_id', 'category_id', 'subject', 'priority', 'content']);

        $errors = $response->session()->get('errors')->getBag('default');
        $this->assertTrue($errors->has('user_id'));
        $this->assertTrue($errors->has('category_id'));
        $this->assertTrue($errors->has('subject'));
        $this->assertTrue($errors->has('priority'));
        $this->assertTrue($errors->has('content'));
    }

    /**
     * Test invoice creation with custom billing.
     */
    public function test_ticket_creation_with_custom_invoice_billing(): void
    {
        $ticketData = [
            'user_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'subject' => 'Custom Invoice Ticket',
            'priority' => 'medium',
            'content' => 'This is a test ticket with custom invoice.',
            'create_invoice' => true,
            'invoice_product_id' => 'custom',
            'billing_type' => 'monthly',
            'invoice_amount' => 200.00,
            'invoice_duration_days' => 30,
            'invoice_status' => 'pending',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.store'), $ticketData);

        $response->assertRedirect(route('admin.tickets.index'));

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->customer->id,
            'product_id' => null,
            'amount' => 200.00,
            'type' => 'recurring',
        ]);
    }

    /**
     * Test invoice creation with product-based billing.
     */
    public function test_ticket_creation_with_product_invoice_billing(): void
    {
        $ticketData = [
            'user_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'subject' => 'Product Invoice Ticket',
            'priority' => 'medium',
            'content' => 'This is a test ticket with product invoice.',
            'create_invoice' => true,
            'invoice_product_id' => $this->product->id,
            'billing_type' => 'one_time',
            'invoice_amount' => 150.00,
            'invoice_duration_days' => 45,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.store'), $ticketData);

        $response->assertRedirect(route('admin.tickets.index'));

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'amount' => 150.00,
            'type' => 'one_time',
        ]);
    }

    /**
     * Test invoice creation validation.
     */
    public function test_invoice_creation_validation(): void
    {
        $ticketData = [
            'user_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'subject' => 'Invalid Invoice Ticket',
            'priority' => 'medium',
            'content' => 'This is a test ticket with invalid invoice data.',
            'create_invoice' => true,
            'billing_type' => 'invalid_type',
            'invoice_amount' => 'not_a_number',
            'invoice_duration_days' => -1,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.store'), $ticketData);

        $response->assertSessionHasErrors(['billing_type', 'invoice_amount', 'invoice_duration_days']);
    }

    /**
     * Test ticket with existing replies.
     */
    public function test_ticket_with_existing_replies(): void
    {
        // Create some replies
        TicketReply::factory()->count(3)->create([
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.tickets.show', $this->ticket));

        $response->assertStatus(200);
        $response->assertViewHas('ticket');

        $ticket = $response->viewData('ticket');
        $this->assertCount(3, $ticket->replies);
    }

    /**
     * Test ticket with invoice relationship.
     */
    public function test_ticket_with_invoice_relationship(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);

        $this->ticket->update(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.tickets.show', $this->ticket));

        $response->assertStatus(200);
        $response->assertViewHas('ticket');

        $ticket = $response->viewData('ticket');
        $this->assertNotNull($ticket->invoice);
        $this->assertEquals($invoice->id, $ticket->invoice->id);
    }
}
