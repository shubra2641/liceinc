<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Helpers\VersionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateNotificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Admin Update Notification Controller with enhanced security.
 *
 * This controller handles update notification management in the admin panel
 * including checking for updates, sending notifications, and managing dismissal.
 *
 * Features:
 * - Update notification checking and sending
 * - Notification status management
 * - Notification dismissal with time limits
 * - Cache-based notification storage
 * - Comprehensive error handling
 * - Enhanced security measures (XSS protection, input validation)
 * - Proper logging for errors and warnings only
 * - Version status integration
 */
class UpdateNotificationController extends Controller
{
    /**
     * Check for updates and send notifications with enhanced security.
     *
     * Checks for available system updates and sends notifications to admin
     * with proper error handling and security measures.
     *
     * @return JsonResponse JSON response with update status and notification result
     *
     * @throws \Exception When update checking fails
     *
     * @version 1.0.6
     */
    public function checkAndNotify(): JsonResponse
    {
        try {
            $versionStatus = VersionHelper::getVersionStatus();
            if ($versionStatus['is_update_available']) {
                $this->sendUpdateNotification($versionStatus);
                return response()->json([
                    'success' => true,
                    'message' => 'Update notification sent',
                    'data' => $versionStatus,
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'No updates available',
                'data' => $versionStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Update notification check failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to check for updates: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Get update notification status with comprehensive information.
     *
     * Retrieves the current notification status including version information,
     * last notification details, and dismissal status.
     *
     * @return JsonResponse JSON response with notification status data
     *
     * @throws \Exception When status retrieval fails
     *
     * @version 1.0.6
     */
    public function getNotificationStatus(): JsonResponse
    {
        try {
            $versionStatus = VersionHelper::getVersionStatus();
            $lastNotification = Cache::get('last_update_notification');
            $notificationDismissed = Cache::get('update_notification_dismissed', false);
            return response()->json([
                'success' => true,
                'data' => [
                    'version_status' => $versionStatus,
                    'last_notification' => $lastNotification,
                    'notification_dismissed' => $notificationDismissed,
                    'should_show_notification' => $versionStatus['is_update_available'] && ! $notificationDismissed,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get notification status', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification status: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Dismiss update notification with enhanced security.
     *
     * Dismisses update notifications with optional time-based dismissal
     * and proper validation and security measures.
     *
     * @param  UpdateNotificationRequest  $request  The validated request containing dismissal data
     *
     * @return JsonResponse JSON response with dismissal result
     *
     * @throws \Exception When dismissal operation fails
     *
     * @version 1.0.6
     */
    public function dismissNotification(UpdateNotificationRequest $request): JsonResponse
    {
        try {
            $dismissUntil = $request->validated()['dismiss_until'] ?? null;
            if ($dismissUntil) {
                // Dismiss until specific date
                Cache::put(
                    'update_notification_dismissed',
                    true,
                    now()->parse(is_string($dismissUntil) ? $dismissUntil : '')
                );
                Cache::put(
                    'update_notification_dismissed_until',
                    $dismissUntil,
                    now()->parse(is_string($dismissUntil) ? $dismissUntil : '')
                );
            } else {
                // Dismiss for 24 hours
                Cache::put('update_notification_dismissed', true, now()->addHours(24));
            }
            return response()->json([
                'success' => true,
                'message' => 'Update notification dismissed',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dismiss notification', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to dismiss notification: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Send update notification with enhanced security.
     *
     * Sends update notification to admin with proper cache management
     * and security measures.
     *
     * @param  array  $versionStatus  The version status information
     *
     * @throws \Exception When notification sending fails
     *
     * @version 1.0.6
     */
    /**
     * @param array<string, mixed> $versionStatus
     */
    private function sendUpdateNotification(array $versionStatus): void
    {
        // Store notification in cache
        Cache::put('last_update_notification', [
            'timestamp' => now()->toISOString(),
            'current_version' => $versionStatus['current_version'],
            'latest_version' => $versionStatus['latest_version'],
            'sent_to' => auth()->id(),
        ], now()->addDays(7));
        // Reset dismissal status when new update is available
        Cache::forget('update_notification_dismissed');
    }
}
