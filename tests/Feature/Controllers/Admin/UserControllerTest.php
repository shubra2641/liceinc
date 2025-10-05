<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\License;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Test suite for UserController.
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Log is already configured for testing

        // Create roles
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'user', 'guard_name' => 'web']);

        // Create admin user
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->admin->assignRole('admin');

        // Create customer user
        $this->customer = User::factory()->create(['is_admin' => false]);
        $this->customer->assignRole('user');
    }

    /**
     * Test users index page.
     */
    public function test_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    }

    /**
     * Test users create page.
     */
    public function test_can_view_create_user_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
        $response->assertViewHas('roles');
    }

    /**
     * Test user creation.
     */
    public function test_can_create_user(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'firstname' => 'Test',
            'lastname' => 'User',
            'phonenumber' => '1234567890',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'name' => 'Test User',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'User created successfully via admin panel');
        });
    }

    /**
     * Test user creation validation.
     */
    public function test_user_creation_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => '',
                'email' => 'invalid-email',
                'password' => '123',
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /**
     * Test user show page.
     */
    public function test_can_view_user_details(): void
    {
        $user = User::factory()->create();
        License::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.show');
        $response->assertViewHas('user');
        $response->assertViewHas('licenses');
    }

    /**
     * Test user edit page.
     */
    public function test_can_view_edit_user_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
        $response->assertViewHas('user');
        $response->assertViewHas('roles');
    }

    /**
     * Test user update.
     */
    public function test_can_update_user(): void
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'user',
            'firstname' => 'Updated',
            'lastname' => 'Name',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'firstname' => 'Updated',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'User updated successfully via admin panel');
        });
    }

    /**
     * Test user password update.
     */
    public function test_can_update_user_password(): void
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'role' => 'user',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'User password updated');
        });
    }

    /**
     * Test user deletion.
     */
    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'User deleted successfully via admin panel');
        });
    }

    /**
     * Test cannot delete own account.
     */
    public function test_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin));

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Admin attempted to delete own account');
        });
    }

    /**
     * Test toggle admin status.
     */
    public function test_can_toggle_admin_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        // Promote to admin
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-admin', $user));

        $user->refresh();
        $this->assertTrue($user->hasRole('admin'));
        $response->assertSessionHas('success');

        // Demote to user
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-admin', $user));

        $user->refresh();
        $this->assertTrue($user->hasRole('user'));

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'User role toggled');
        });
    }

    /**
     * Test cannot toggle own role.
     */
    public function test_cannot_toggle_own_role(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-admin', $this->admin));

        $response->assertSessionHas('error');

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Admin attempted to change own role');
        });
    }

    /**
     * Test send password reset.
     */
    public function test_can_send_password_reset(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.send-password-reset', $user));

        $response->assertSessionHas('success');

        Log::assertLogged('info', function ($message, $context) use ($user) {
            return str_contains($message, 'Password reset email sent to user') &&
                   $context['user_id'] === $user->id;
        });
    }

    /**
     * Test get user licenses API.
     */
    public function test_can_get_user_licenses_api(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/users/{$user->id}/licenses");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
        $response->assertJsonStructure([
            'success',
            'user',
            'licenses',
            'total',
        ]);
    }

    /**
     * Test get user licenses for non-existent user.
     */
    public function test_get_user_licenses_returns_404_for_missing_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users/99999/licenses');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'User not found',
        ]);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'User not found for license retrieval');
        });
    }

    /**
     * Test authorization - customers cannot access admin panel.
     */
    public function test_customers_cannot_access_admin_users(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.users.index'));

        // Assuming middleware prevents access (403 or redirect)
        $this->assertTrue(
            $response->status() === 403 ||
            $response->isRedirect(),
        );
    }
}
