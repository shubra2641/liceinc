<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * Test suite for ProfileController.
 *
 * Tests all profile management functionality including:
 * - Profile editing and updates
 * - Password changes
 * - Avatar uploads
 * - Envato integration
 * - Error handling and logging
 */
class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected User $customer;

    protected Setting $settings;

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

        // Create test settings
        $this->settings = Setting::factory()->create([
            'envato_personal_token' => 'test-token-1234567890',
        ]);

        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test admin can access profile edit form.
     */
    public function test_admin_can_access_profile_edit_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.profile.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.profile.edit');
        $response->assertViewHas('user');
    }

    /**
     * Test customer cannot access profile edit form.
     */
    public function test_customer_cannot_access_profile_edit_form(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.profile.edit'));

        $response->assertStatus(403);
    }

    /**
     * Test admin can update profile with valid data.
     */
    public function test_admin_can_update_profile_with_valid_data(): void
    {
        $profileData = [
            'name' => 'Updated Admin Name',
            'email' => 'updated@test.com',
            'phone' => '+1234567890',
            'company' => 'Test Company',
            'website' => 'https://test.com',
            'bio' => 'Updated bio information',
            'location' => 'Test Location',
            'timezone' => 'UTC',
            'language' => 'en',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.update'), $profileData);

        $response->assertRedirect(route('admin.profile.edit'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Updated Admin Name',
            'email' => 'updated@test.com',
            'phone' => '+1234567890',
            'company' => 'Test Company',
            'website' => 'https://test.com',
            'bio' => 'Updated bio information',
            'location' => 'Test Location',
            'timezone' => 'UTC',
            'language' => 'en',
        ]);
    }

    /**
     * Test profile update with avatar upload.
     */
    public function test_profile_update_with_avatar_upload(): void
    {
        $avatarFile = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $profileData = [
            'name' => 'Test Admin',
            'email' => $this->admin->email,
            'avatar' => $avatarFile,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.update'), $profileData);

        $response->assertRedirect(route('admin.profile.edit'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        // Check that avatar was stored
        Storage::disk('public')->assertExists('avatars/'.$avatarFile->hashName());

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Test Admin',
        ]);
    }

    /**
     * Test profile update fails with invalid data.
     */
    public function test_profile_update_fails_with_invalid_data(): void
    {
        $invalidData = [
            'name' => '', // Required field missing
            'email' => 'invalid-email', // Invalid email
            'phone' => str_repeat('a', 21), // Too long
            'company' => str_repeat('a', 256), // Too long
            'website' => 'not-a-url', // Invalid URL
            'bio' => str_repeat('a', 1001), // Too long
            'location' => str_repeat('a', 256), // Too long
            'timezone' => str_repeat('a', 51), // Too long
            'language' => str_repeat('a', 11), // Too long
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.update'), $invalidData);

        $response->assertSessionHasErrors([
            'name', 'email', 'phone', 'company', 'website', 'bio', 'location', 'timezone', 'language',
        ]);
    }

    /**
     * Test profile update with email change triggers verification.
     */
    public function test_profile_update_with_email_change_triggers_verification(): void
    {
        $profileData = [
            'name' => 'Test Admin',
            'email' => 'newemail@test.com',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.update'), $profileData);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'email' => 'newemail@test.com',
            'email_verified_at' => null,
        ]);
    }

    /**
     * Test admin can update password with valid data.
     */
    public function test_admin_can_update_password_with_valid_data(): void
    {
        $passwordData = [
            'current_password' => 'password', // Default password from factory
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.password.update'), $passwordData);

        $response->assertRedirect(route('admin.profile.edit'));
        $response->assertSessionHas('success', 'Password updated successfully.');

        // Verify password was updated
        $this->admin->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $this->admin->password));
    }

    /**
     * Test password update fails with invalid data.
     */
    public function test_password_update_fails_with_invalid_data(): void
    {
        $invalidData = [
            'current_password' => 'wrong-password', // Wrong current password
            'password' => 'weak', // Too weak
            'password_confirmation' => 'different', // Doesn't match
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.password.update'), $invalidData);

        $response->assertSessionHasErrors([
            'current_password', 'password',
        ]);
    }

    /**
     * Test password update fails with weak password.
     */
    public function test_password_update_fails_with_weak_password(): void
    {
        $weakPasswordData = [
            'current_password' => 'password',
            'password' => 'weakpass', // No uppercase, numbers, or special chars
            'password_confirmation' => 'weakpass',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.password.update'), $weakPasswordData);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test admin can connect to Envato with valid token.
     */
    public function test_admin_can_connect_to_envato_with_valid_token(): void
    {
        // Mock successful Envato API response
        Http::fake([
            'api.envato.com/*' => Http::response([
                'username' => 'testuser',
                'id' => 12345,
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.profile.envato.connect'));

        $response->assertRedirect(route('admin.profile.edit'));
        $response->assertSessionHas('success', 'Successfully connected to Envato account: testuser');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'envato_username' => 'testuser',
            'envato_id' => 12345,
        ]);
    }

    /**
     * Test Envato connection fails with invalid token.
     */
    public function test_envato_connection_fails_with_invalid_token(): void
    {
        // Mock failed Envato API response
        Http::fake([
            'api.envato.com/*' => Http::response([], 401),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.profile.envato.connect'));

        $response->assertRedirect(route('admin.profile.edit'));
        $response->assertSessionHas('error', 'Failed to connect to Envato. Please check your API token.');
    }

    /**
     * Test Envato connection fails when not configured.
     */
    public function test_envato_connection_fails_when_not_configured(): void
    {
        // Update settings to remove token
        $this->settings->update(['envato_personal_token' => null]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.profile.envato.connect'));

        $response->assertRedirect(route('admin.profile.edit'));
        $response->assertSessionHas('error', 'Envato API is not configured. Please configure it in Settings first.');
    }

    /**
     * Test admin can disconnect from Envato.
     */
    public function test_admin_can_disconnect_from_envato(): void
    {
        // Set up user with Envato data
        $this->admin->update([
            'envato_username' => 'testuser',
            'envato_id' => 12345,
            'envato_token' => 'test-token',
            'envato_refresh_token' => 'refresh-token',
            'envato_token_expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.profile.envato.disconnect'));

        $response->assertRedirect(route('admin.profile.edit'));
        $response->assertSessionHas('success', 'Successfully disconnected from Envato account.');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'envato_username' => null,
            'envato_id' => null,
            'envato_token' => null,
            'envato_refresh_token' => null,
            'envato_token_expires_at' => null,
        ]);
    }

    /**
     * Test unauthorized access attempts.
     */
    public function test_unauthorized_access_returns_403(): void
    {
        $routes = [
            'admin.profile.edit',
            'admin.profile.update',
            'admin.profile.password.update',
            'admin.profile.envato.connect',
            'admin.profile.envato.disconnect',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->customer)
                ->get(route($route));

            $response->assertStatus(403);
        }
    }

    /**
     * Test guest access attempts.
     */
    public function test_guest_access_redirects_to_login(): void
    {
        $response = $this->get(route('admin.profile.edit'));
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
            ->put(route('admin.profile.update'), [
                'name' => 'Test Admin',
                'email' => $this->admin->email,
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
            ->put(route('admin.profile.update'), [
                'name' => '',
                'email' => 'invalid-email',
                'website' => 'not-a-url',
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'website']);

        $errors = $response->session()->get('errors')->getBag('default');
        $this->assertTrue($errors->has('name'));
        $this->assertTrue($errors->has('email'));
        $this->assertTrue($errors->has('website'));
    }

    /**
     * Test string field trimming.
     */
    public function test_string_field_trimming(): void
    {
        $profileData = [
            'name' => '  Test Admin  ',
            'email' => '  test@example.com  ',
            'phone' => '  +1234567890  ',
            'company' => '  Test Company  ',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.update'), $profileData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'company' => 'Test Company',
        ]);
    }

    /**
     * Test avatar file validation.
     */
    public function test_avatar_file_validation(): void
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $profileData = [
            'name' => 'Test Admin',
            'email' => $this->admin->email,
            'avatar' => $invalidFile,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.update'), $profileData);

        $response->assertSessionHasErrors(['avatar']);
    }

    /**
     * Test avatar file size validation.
     */
    public function test_avatar_file_size_validation(): void
    {
        $largeFile = UploadedFile::fake()->image('large.jpg', 200, 200)->size(3000); // 3MB

        $profileData = [
            'name' => 'Test Admin',
            'email' => $this->admin->email,
            'avatar' => $largeFile,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.update'), $profileData);

        $response->assertSessionHasErrors(['avatar']);
    }

    /**
     * Test old avatar deletion when new one is uploaded.
     */
    public function test_old_avatar_deletion_when_new_one_is_uploaded(): void
    {
        // Set up user with existing avatar
        $this->admin->update(['avatar' => 'avatars/old-avatar.jpg']);
        Storage::disk('public')->put('avatars/old-avatar.jpg', 'fake content');

        $newAvatarFile = UploadedFile::fake()->image('new-avatar.jpg', 200, 200);

        $profileData = [
            'name' => 'Test Admin',
            'email' => $this->admin->email,
            'avatar' => $newAvatarFile,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.update'), $profileData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profile updated successfully.');

        // Check that old avatar was deleted
        Storage::disk('public')->assertMissing('avatars/old-avatar.jpg');
        // Check that new avatar was stored
        Storage::disk('public')->assertExists('avatars/'.$newAvatarFile->hashName());
    }

    /**
     * Test email uniqueness validation.
     */
    public function test_email_uniqueness_validation(): void
    {
        // Create another user with different email
        $otherUser = User::factory()->create(['email' => 'other@test.com']);

        $profileData = [
            'name' => 'Test Admin',
            'email' => 'other@test.com', // Same as other user
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.update'), $profileData);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test email uniqueness allows same user's current email.
     */
    public function test_email_uniqueness_allows_same_users_current_email(): void
    {
        $profileData = [
            'name' => 'Test Admin',
            'email' => $this->admin->email, // Same as current user's email
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.profile.update'), $profileData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profile updated successfully.');
    }
}
