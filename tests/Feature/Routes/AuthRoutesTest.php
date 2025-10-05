<?php

namespace Tests\Feature\Routes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Authentication Routes Feature Test.
 */
class AuthRoutesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
    }

    /**
     * Test guest routes accessibility.
     */
    public function test_guest_routes_accessibility(): void
    {
        // Test register page
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        // Test login page
        $response = $this->get(route('login'));
        $response->assertStatus(200);

        // Test forgot password page
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
    }

    /**
     * Test user registration with rate limiting.
     */
    public function test_user_registration_with_rate_limiting(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $response = $this->post(route('register'), [
                'name' => $this->faker->name,
                'email' => $this->faker->unique()->safeEmail,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            if ($i < 5) {
                $response->assertStatus(302); // Redirect after registration
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test user login with rate limiting.
     */
    public function test_user_login_with_rate_limiting(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $response = $this->post(route('login'), [
                'email' => $this->user->email,
                'password' => 'password',
            ]);

            if ($i < 5) {
                $response->assertStatus(302); // Redirect after login
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test password reset request with rate limiting.
     */
    public function test_password_reset_request_with_rate_limiting(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('password.email'), [
                'email' => $this->user->email,
            ]);

            if ($i < 3) {
                $response->assertStatus(302); // Redirect after request
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test password reset with valid token.
     */
    public function test_password_reset_with_valid_token(): void
    {
        $token = Password::createToken($this->user);

        $response = $this->get(route('password.reset', ['token' => $token]));
        $response->assertStatus(200);
    }

    /**
     * Test password reset with invalid token.
     */
    public function test_password_reset_with_invalid_token(): void
    {
        $response = $this->get(route('password.reset', ['token' => 'invalid-token!']));
        $response->assertStatus(404);
    }

    /**
     * Test password reset with rate limiting.
     */
    public function test_password_reset_with_rate_limiting(): void
    {
        $token = Password::createToken($this->user);

        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('password.store'), [
                'token' => $token,
                'email' => $this->user->email,
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ]);

            if ($i < 3) {
                $response->assertStatus(302); // Redirect after reset
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test authenticated routes accessibility.
     */
    public function test_authenticated_routes_accessibility(): void
    {
        // Test email verification notice
        $response = $this->actingAs($this->user)
            ->get(route('verification.notice'));
        $response->assertStatus(200);

        // Test password confirmation page
        $response = $this->actingAs($this->user)
            ->get(route('password.confirm'));
        $response->assertStatus(200);
    }

    /**
     * Test email verification with valid parameters.
     */
    public function test_email_verification_with_valid_parameters(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('verification.verify', [
                'id' => $this->user->id,
                'hash' => sha1($this->user->email),
            ]));
        $response->assertStatus(302);
    }

    /**
     * Test email verification with invalid parameters.
     */
    public function test_email_verification_with_invalid_parameters(): void
    {
        // Test with invalid user ID
        $response = $this->actingAs($this->user)
            ->get(route('verification.verify', [
                'id' => 'invalid',
                'hash' => sha1($this->user->email),
            ]));
        $response->assertStatus(404);

        // Test with invalid hash
        $response = $this->actingAs($this->user)
            ->get(route('verification.verify', [
                'id' => $this->user->id,
                'hash' => 'invalid-hash!',
            ]));
        $response->assertStatus(404);
    }

    /**
     * Test email verification with rate limiting.
     */
    public function test_email_verification_with_rate_limiting(): void
    {
        for ($i = 0; $i < 8; $i++) {
            $response = $this->actingAs($this->user)
                ->get(route('verification.verify', [
                    'id' => $this->user->id,
                    'hash' => sha1($this->user->email),
                ]));

            if ($i < 6) {
                $response->assertStatus(302);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test email verification notification with rate limiting.
     */
    public function test_email_verification_notification_with_rate_limiting(): void
    {
        for ($i = 0; $i < 8; $i++) {
            $response = $this->actingAs($this->user)
                ->post(route('verification.send'));

            if ($i < 6) {
                $response->assertStatus(302);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test password confirmation with rate limiting.
     */
    public function test_password_confirmation_with_rate_limiting(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $response = $this->actingAs($this->user)
                ->post(route('password.confirm'), [
                    'password' => 'password',
                ]);

            if ($i < 5) {
                $response->assertStatus(302);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test password update with rate limiting.
     */
    public function test_password_update_with_rate_limiting(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($this->user)
                ->put(route('password.update'), [
                    'current_password' => 'password',
                    'password' => 'newpassword',
                    'password_confirmation' => 'newpassword',
                ]);

            if ($i < 3) {
                $response->assertStatus(302);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test logout functionality.
     */
    public function test_logout_functionality(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('logout'));
        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
    }

    /**
     * Test guest access to authenticated routes.
     */
    public function test_guest_access_to_authenticated_routes(): void
    {
        // Test email verification notice
        $response = $this->get(route('verification.notice'));
        $response->assertRedirect(route('login'));

        // Test email verification
        $response = $this->get(route('verification.verify', [
            'id' => 1,
            'hash' => 'test-hash',
        ]));
        $response->assertRedirect(route('login'));

        // Test password confirmation
        $response = $this->get(route('password.confirm'));
        $response->assertRedirect(route('login'));

        // Test password update
        $response = $this->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);
        $response->assertRedirect(route('login'));

        // Test logout
        $response = $this->post(route('logout'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test authenticated access to guest routes.
     */
    public function test_authenticated_access_to_guest_routes(): void
    {
        // Test register page
        $response = $this->actingAs($this->user)
            ->get(route('register'));
        $response->assertRedirect(route('dashboard'));

        // Test login page
        $response = $this->actingAs($this->user)
            ->get(route('login'));
        $response->assertRedirect(route('dashboard'));

        // Test forgot password page
        $response = $this->actingAs($this->user)
            ->get(route('password.request'));
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test form validation for registration.
     */
    public function test_form_validation_for_registration(): void
    {
        // Test with missing required fields
        $response = $this->post(route('register'), []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'email', 'password']);

        // Test with invalid email
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);

        // Test with password mismatch
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test form validation for login.
     */
    public function test_form_validation_for_login(): void
    {
        // Test with missing credentials
        $response = $this->post(route('login'), []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email', 'password']);

        // Test with invalid credentials
        $response = $this->post(route('login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test form validation for password reset.
     */
    public function test_form_validation_for_password_reset(): void
    {
        // Test with missing email
        $response = $this->post(route('password.email'), []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);

        // Test with invalid email
        $response = $this->post(route('password.email'), [
            'email' => 'invalid-email',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test form validation for password update.
     */
    public function test_form_validation_for_password_update(): void
    {
        // Test with missing fields
        $response = $this->actingAs($this->user)
            ->put(route('password.update'), []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['current_password', 'password']);

        // Test with wrong current password
        $response = $this->actingAs($this->user)
            ->put(route('password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['current_password']);

        // Test with password mismatch
        $response = $this->actingAs($this->user)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'newpassword',
                'password_confirmation' => 'different-password',
            ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test CSRF protection.
     */
    public function test_csrf_protection(): void
    {
        // Test registration without CSRF token
        $response = $this->postJson(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertStatus(419); // CSRF token mismatch

        // Test login without CSRF token
        $response = $this->postJson(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        $response->assertStatus(419); // CSRF token mismatch
    }

    /**
     * Test session management.
     */
    public function test_session_management(): void
    {
        // Test login creates session
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        $response->assertStatus(302);
        $this->assertAuthenticated();

        // Test logout destroys session
        $response = $this->actingAs($this->user)
            ->post(route('logout'));
        $response->assertStatus(302);
        $this->assertGuest();
    }

    /**
     * Test remember me functionality.
     */
    public function test_remember_me_functionality(): void
    {
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
            'remember' => true,
        ]);
        $response->assertStatus(302);
        $this->assertAuthenticated();

        // Check if remember token is set
        $this->assertNotNull($this->user->fresh()->remember_token);
    }

    /**
     * Test concurrent login attempts.
     */
    public function test_concurrent_login_attempts(): void
    {
        $responses = [];

        // Simulate concurrent login attempts
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->post(route('login'), [
                'email' => $this->user->email,
                'password' => 'password',
            ]);
        }

        // All should succeed (rate limiting is per IP, not per user)
        foreach ($responses as $response) {
            $response->assertStatus(302);
        }
    }

    /**
     * Test password reset token expiration.
     */
    public function test_password_reset_token_expiration(): void
    {
        $token = Password::createToken($this->user);

        // Simulate token expiration by modifying the created_at timestamp
        $passwordReset = \DB::table('password_reset_tokens')
            ->where('email', $this->user->email)
            ->first();

        if ($passwordReset) {
            \DB::table('password_reset_tokens')
                ->where('email', $this->user->email)
                ->update(['created_at' => now()->subHours(2)]);
        }

        $response = $this->post(route('password.store'), [
            'token' => $token,
            'email' => $this->user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }
}
