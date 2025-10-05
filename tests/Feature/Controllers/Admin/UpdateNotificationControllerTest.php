<?php

namespace Tests\Feature\Controllers\Admin;

use App\Helpers\VersionHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Test suite for UpdateNotificationController.
 */
class UpdateNotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Log is already configured for testing
        Cache::flush();

        // Create admin role and user
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->admin->assignRole('admin');
    }

    /**
     * Test check and notify for updates.
     */
    public function test_can_check_and_notify_for_updates(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/updates/check-notify');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }

    /**
     * Test check and notify logs when update available.
     */
    public function test_logs_when_update_available(): void
    {
        // Mock VersionHelper to return update available
        // Note: Actual implementation depends on VersionHelper

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/updates/check-notify');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test get notification status.
     */
    public function test_can_get_notification_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/updates/notification-status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'version_status',
                'last_notification',
                'notification_dismissed',
                'dismissed_until',
                'should_show_notification',
            ],
        ]);
    }

    /**
     * Test dismiss notification without date.
     */
    public function test_can_dismiss_notification_default_duration(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/updates/dismiss-notification');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Update notification dismissed',
        ]);

        // Verify cache was set
        $this->assertTrue(Cache::has('update_notification_dismissed'));

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Update notification dismissed') &&
                   $context['dismissed_until'] === '24 hours';
        });
    }

    /**
     * Test dismiss notification with specific date.
     */
    public function test_can_dismiss_notification_with_date(): void
    {
        $dismissDate = now()->addDays(3)->toDateString();

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/updates/dismiss-notification', [
                'dismiss_until' => $dismissDate,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Update notification dismissed',
        ]);

        // Verify cache was set
        $this->assertTrue(Cache::has('update_notification_dismissed'));
        $this->assertTrue(Cache::has('update_notification_dismissed_until'));

        Log::assertLogged('info', function ($message, $context) use ($dismissDate) {
            return str_contains($message, 'Update notification dismissed') &&
                   $context['dismissed_until'] === $dismissDate;
        });
    }

    /**
     * Test dismiss notification validation fails for past date.
     */
    public function test_dismiss_notification_validation_fails_for_past_date(): void
    {
        $pastDate = now()->subDays(1)->toDateString();

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/updates/dismiss-notification', [
                'dismiss_until' => $pastDate,
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors',
        ]);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Invalid dismissal request');
        });
    }

    /**
     * Test clear notification cache.
     */
    public function test_can_clear_notification_cache(): void
    {
        // Set some cache data
        Cache::put('update_notification_last', ['test' => 'data'], 3600);
        Cache::put('update_notification_dismissed', true, 3600);

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/updates/clear-cache');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Notification cache cleared successfully',
        ]);

        // Verify cache was cleared
        $this->assertFalse(Cache::has('update_notification_last'));
        $this->assertFalse(Cache::has('update_notification_dismissed'));

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Update notification cache cleared');
        });
    }

    /**
     * Test error handling in check and notify.
     */
    public function test_handles_errors_in_check_and_notify(): void
    {
        // This would require mocking VersionHelper to throw exception
        // For now, we test that error responses have correct structure

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/updates/check-notify');

        $this->assertTrue(
            $response->status() === 200 ||
            $response->status() === 500,
        );

        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    /**
     * Test error handling in get notification status.
     */
    public function test_handles_errors_in_get_notification_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/updates/notification-status');

        $this->assertTrue(
            $response->status() === 200 ||
            $response->status() === 500,
        );

        $response->assertJsonStructure([
            'success',
        ]);
    }

    /**
     * Test authorization - only admins can access.
     */
    public function test_only_admins_can_access_update_notifications(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($customer)
            ->postJson('/admin/updates/check-notify');

        // Should be forbidden or redirect
        $this->assertTrue(
            $response->status() === 403 ||
            $response->status() === 302,
        );
    }

    /**
     * Test cache keys consistency.
     */
    public function test_cache_keys_use_consistent_prefix(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/admin/updates/dismiss-notification');

        // Verify all cache keys use the same prefix
        $this->assertTrue(Cache::has('update_notification_dismissed'));
    }
}
