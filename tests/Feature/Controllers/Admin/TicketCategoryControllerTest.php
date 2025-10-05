<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test suite for TicketCategoryController.
 *
 * Tests all ticket category management functionality including:
 * - CRUD operations for ticket categories
 * - Validation and error handling
 * - Authorization and access control
 * - Database transactions and rollbacks
 * - Logging and monitoring
 */
class TicketCategoryControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected User $customer;

    protected TicketCategory $category;

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

        // Create test category
        $this->category = TicketCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'color' => '#ff0000',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    /**
     * Test admin can access ticket categories index.
     */
    public function test_admin_can_access_ticket_categories_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.ticket-categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ticket-categories.index');
        $response->assertViewHas('categories');
    }

    /**
     * Test customer cannot access ticket categories index.
     */
    public function test_customer_cannot_access_ticket_categories_index(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.ticket-categories.index'));

        $response->assertStatus(403);
    }

    /**
     * Test admin can access create ticket category form.
     */
    public function test_admin_can_access_create_ticket_category_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.ticket-categories.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ticket-categories.create');
    }

    /**
     * Test admin can create ticket category with valid data.
     */
    public function test_admin_can_create_ticket_category_with_valid_data(): void
    {
        $categoryData = [
            'name' => 'New Category',
            'slug' => 'new-category',
            'description' => 'Test description',
            'color' => '#00ff00',
            'sort_order' => 2,
            'is_active' => true,
            'requires_login' => false,
            'requires_valid_purchase_code' => false,
            'meta_title' => 'Test Meta Title',
            'meta_keywords' => 'test, keywords',
            'meta_description' => 'Test meta description',
            'icon' => 'fas fa-test',
            'priority' => 'medium',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ticket-categories.store'), $categoryData);

        $response->assertRedirect(route('admin.ticket-categories.index'));
        $response->assertSessionHas('success', 'Ticket category created successfully.');

        $this->assertDatabaseHas('ticket_categories', [
            'name' => 'New Category',
            'slug' => 'new-category',
            'color' => '#00ff00',
        ]);
    }

    /**
     * Test ticket category creation with auto-generated slug.
     */
    public function test_ticket_category_creation_with_auto_generated_slug(): void
    {
        $categoryData = [
            'name' => 'Auto Slug Category',
            'slug' => '', // Empty slug should be auto-generated
            'color' => '#0000ff',
            'sort_order' => 3,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ticket-categories.store'), $categoryData);

        $response->assertRedirect(route('admin.ticket-categories.index'));

        $this->assertDatabaseHas('ticket_categories', [
            'name' => 'Auto Slug Category',
            'slug' => 'auto-slug-category',
        ]);
    }

    /**
     * Test ticket category creation fails with invalid data.
     */
    public function test_ticket_category_creation_fails_with_invalid_data(): void
    {
        $invalidData = [
            'name' => '', // Required field missing
            'color' => 'invalid-color', // Invalid color format
            'sort_order' => 'not-a-number', // Invalid sort order
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ticket-categories.store'), $invalidData);

        $response->assertSessionHasErrors(['name', 'color', 'sort_order']);
    }

    /**
     * Test ticket category creation fails with duplicate slug.
     */
    public function test_ticket_category_creation_fails_with_duplicate_slug(): void
    {
        $categoryData = [
            'name' => 'Duplicate Slug Category',
            'slug' => 'test-category', // Same as existing category
            'color' => '#ff00ff',
            'sort_order' => 4,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ticket-categories.store'), $categoryData);

        $response->assertSessionHasErrors(['slug']);
    }

    /**
     * Test admin can view ticket category details.
     */
    public function test_admin_can_view_ticket_category_details(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.ticket-categories.show', $this->category));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ticket-categories.show');
        $response->assertViewHas('ticketCategory', $this->category);
    }

    /**
     * Test admin can access edit ticket category form.
     */
    public function test_admin_can_access_edit_ticket_category_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.ticket-categories.edit', $this->category));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ticket-categories.edit');
        $response->assertViewHas('ticketCategory', $this->category);
    }

    /**
     * Test admin can update ticket category with valid data.
     */
    public function test_admin_can_update_ticket_category_with_valid_data(): void
    {
        $updateData = [
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'description' => 'Updated description',
            'color' => '#ffff00',
            'sort_order' => 5,
            'is_active' => false,
            'requires_login' => true,
            'requires_valid_purchase_code' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.ticket-categories.update', $this->category), $updateData);

        $response->assertRedirect(route('admin.ticket-categories.index'));
        $response->assertSessionHas('success', 'Ticket category updated successfully.');

        $this->assertDatabaseHas('ticket_categories', [
            'id' => $this->category->id,
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'color' => '#ffff00',
        ]);
    }

    /**
     * Test ticket category update fails with invalid data.
     */
    public function test_ticket_category_update_fails_with_invalid_data(): void
    {
        $invalidData = [
            'name' => '', // Required field missing
            'color' => 'invalid', // Invalid color format
            'sort_order' => -1, // Invalid sort order
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.ticket-categories.update', $this->category), $invalidData);

        $response->assertSessionHasErrors(['name', 'color', 'sort_order']);
    }

    /**
     * Test admin can delete ticket category without tickets.
     */
    public function test_admin_can_delete_ticket_category_without_tickets(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.ticket-categories.destroy', $this->category));

        $response->assertRedirect(route('admin.ticket-categories.index'));
        $response->assertSessionHas('success', 'Ticket category deleted successfully.');

        $this->assertDatabaseMissing('ticket_categories', [
            'id' => $this->category->id,
        ]);
    }

    /**
     * Test ticket category deletion fails when category has tickets.
     */
    public function test_ticket_category_deletion_fails_when_category_has_tickets(): void
    {
        // Create a ticket for this category
        Ticket::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.ticket-categories.destroy', $this->category));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete category with existing tickets. Please reassign or delete tickets first.');

        // Category should still exist
        $this->assertDatabaseHas('ticket_categories', [
            'id' => $this->category->id,
        ]);
    }

    /**
     * Test unauthorized access attempts.
     */
    public function test_unauthorized_access_returns_403(): void
    {
        $routes = [
            'admin.ticket-categories.index',
            'admin.ticket-categories.create',
            'admin.ticket-categories.store',
            'admin.ticket-categories.show',
            'admin.ticket-categories.edit',
            'admin.ticket-categories.update',
            'admin.ticket-categories.destroy',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->customer)
                ->get(route($route, $this->category));

            $response->assertStatus(403);
        }
    }

    /**
     * Test guest access attempts.
     */
    public function test_guest_access_redirects_to_login(): void
    {
        $response = $this->get(route('admin.ticket-categories.index'));
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
            ->post(route('admin.ticket-categories.store'), [
                'name' => 'Test Category',
                'color' => '#ff0000',
                'sort_order' => 1,
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
            ->post(route('admin.ticket-categories.store'), [
                'name' => '',
                'color' => 'invalid',
                'sort_order' => 'not-a-number',
            ]);

        $response->assertSessionHasErrors(['name', 'color', 'sort_order']);

        $errors = $response->session()->get('errors')->getBag('default');
        $this->assertTrue($errors->has('name'));
        $this->assertTrue($errors->has('color'));
        $this->assertTrue($errors->has('sort_order'));
    }

    /**
     * Test boolean field handling.
     */
    public function test_boolean_field_handling(): void
    {
        $categoryData = [
            'name' => 'Boolean Test Category',
            'color' => '#ff0000',
            'sort_order' => 1,
            'is_active' => '1', // String boolean
            'requires_login' => '0', // String boolean
            'requires_valid_purchase_code' => 'true', // String boolean
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ticket-categories.store'), $categoryData);

        $response->assertRedirect(route('admin.ticket-categories.index'));

        $this->assertDatabaseHas('ticket_categories', [
            'name' => 'Boolean Test Category',
            'is_active' => true,
            'requires_login' => false,
            'requires_valid_purchase_code' => true,
        ]);
    }

    /**
     * Test priority field validation.
     */
    public function test_priority_field_validation(): void
    {
        $validPriorities = ['low', 'medium', 'high', 'urgent'];

        foreach ($validPriorities as $priority) {
            $categoryData = [
                'name' => "Priority Test Category {$priority}",
                'color' => '#ff0000',
                'sort_order' => 1,
                'priority' => $priority,
            ];

            $response = $this->actingAs($this->admin)
                ->post(route('admin.ticket-categories.store'), $categoryData);

            $response->assertRedirect(route('admin.ticket-categories.index'));

            $this->assertDatabaseHas('ticket_categories', [
                'name' => "Priority Test Category {$priority}",
                'priority' => $priority,
            ]);
        }
    }

    /**
     * Test invalid priority field.
     */
    public function test_invalid_priority_field(): void
    {
        $categoryData = [
            'name' => 'Invalid Priority Category',
            'color' => '#ff0000',
            'sort_order' => 1,
            'priority' => 'invalid-priority',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ticket-categories.store'), $categoryData);

        $response->assertSessionHasErrors(['priority']);
    }

    /**
     * Test meta fields validation.
     */
    public function test_meta_fields_validation(): void
    {
        $categoryData = [
            'name' => 'Meta Test Category',
            'color' => '#ff0000',
            'sort_order' => 1,
            'meta_title' => str_repeat('a', 255), // Max length
            'meta_keywords' => str_repeat('b', 500), // Max length
            'meta_description' => str_repeat('c', 500), // Max length
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ticket-categories.store'), $categoryData);

        $response->assertRedirect(route('admin.ticket-categories.index'));

        $this->assertDatabaseHas('ticket_categories', [
            'name' => 'Meta Test Category',
            'meta_title' => str_repeat('a', 255),
            'meta_keywords' => str_repeat('b', 500),
            'meta_description' => str_repeat('c', 500),
        ]);
    }

    /**
     * Test meta fields length validation.
     */
    public function test_meta_fields_length_validation(): void
    {
        $categoryData = [
            'name' => 'Meta Length Test Category',
            'color' => '#ff0000',
            'sort_order' => 1,
            'meta_title' => str_repeat('a', 256), // Exceeds max length
            'meta_keywords' => str_repeat('b', 501), // Exceeds max length
            'meta_description' => str_repeat('c', 501), // Exceeds max length
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ticket-categories.store'), $categoryData);

        $response->assertSessionHasErrors(['meta_title', 'meta_keywords', 'meta_description']);
    }
}
