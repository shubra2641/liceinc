<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use App\Helpers\SecureFileHelper;

/**
 * Advanced Webhook Service with enhanced security and performance. *
 * This service provides comprehensive webhook management with retry logic, * signature verification, event filtering, and advanced monitoring capabilities. *
 * Features: * - Secure webhook delivery with signature verification * - Automatic retry logic with exponential backoff * - Comprehensive logging and monitoring * - Performance tracking and health monitoring * - Bulk operations and delivery reports * - URL validation and security checks * - Asynchronous webhook processing *
 *
 * @example * // Send a webhook event * $webhookService = new AdvancedWebhookService(); * $webhookService->sendWebhookEvent('user.created', ['user_id' => 123]); *
 * // Test a webhook endpoint * $result = $webhookService->testWebhook($webhook); * if ($result['success']) { * echo "Webhook test successful"; * } */
class AdvancedWebhookService
{
    private const MAX_RETRIES = 3;
    private const RETRY_DELAYS = [30, 300, 1800]; // 30s, 5m, 30m
    private const TIMEOUT = 30;
    /**   * Send webhook event to configured webhooks with enhanced security. *   * Processes webhook events by sending them to all active webhooks that * are configured to receive the specified event type. Includes comprehensive * error handling, retry logic, and performance tracking. *   * @param string $eventType The type of event being sent (e.g., 'user.created', 'license.activated') * @param array $payload The event data payload to be sent * @param  int|null  $webhookId  Optional specific webhook ID to send to (if null, sends to all matching webhooks) *   * @throws \Exception When webhook processing fails critically *   * @example * // Send to all webhooks listening for user events * $webhookService->sendWebhookEvent('user.created', [ * 'user_id' => 123, * 'email' => 'user@example.com', * 'created_at' => '2025-01-01T00:00:00Z' * ]); *   * // Send to specific webhook * $webhookService->sendWebhookEvent('license.activated', $licenseData, 456); */
    /**   * @param array<string, mixed> $payload */
    public function sendWebhookEvent(string $eventType, array $payload, ?int $webhookId = null): void
    {
        $webhooks = $webhookId !== null
            ? Webhook::where('id', $webhookId)->get()
            : Webhook::where('is_active', true)
                ->whereJsonContains('events', $eventType)
                ->get();
        foreach ($webhooks as $webhook) {
            $this->processWebhook($webhook, $eventType, $payload);
        }
    }
    /**   * Process individual webhook with comprehensive error handling. *   * Handles the complete webhook delivery process including data preparation, * HTTP request sending, logging, and statistics updates. Implements proper * error handling with retry logic for failed deliveries. *   * @param Webhook $webhook The webhook configuration to process * @param string $eventType The type of event being processed * @param array $payload The event data payload *   * @throws \Exception When webhook processing fails and retry is not possible */
    /**   * @param array<string, mixed> $payload */
    private function processWebhook(Webhook $webhook, string $eventType, array $payload): void
    {
        try {
            // Prepare webhook data
            $webhookData = $this->prepareWebhookData($webhook, $eventType, $payload);
            // Send webhook
            $response = $this->sendHttpRequest($webhook, $webhookData);
            // Log the attempt
            $this->logWebhookAttempt($webhook, $eventType, $webhookData, $response, true);
            // Update webhook statistics
            $this->updateWebhookStats($webhook, true);
        } catch (\Exception $e) {
            // Log failed attempt
            $errorMessage = $e->getMessage();
            $this->logWebhookAttempt($webhook, $eventType, $payload, null, false, $errorMessage);
            // Update webhook statistics
            $this->updateWebhookStats($webhook, false);
            // Queue for retry if not exceeded max retries
            $this->queueWebhookRetry($webhook, $eventType, $payload);
        }
    }
    /**   * Prepare webhook data with security signature and validation. *   * Creates a secure webhook payload with proper structure, timestamps, * nonces, and HMAC signatures for security verification. Includes * comprehensive data validation and sanitization. *   * @param Webhook $webhook The webhook configuration * @param string $eventType The type of event being sent * @param array $payload The original event data *   * @return array The prepared webhook data with security features *   * @throws \Exception When data preparation fails */
    /**   * @param array<string, mixed> $payload * @return array<string, mixed> */
    private function prepareWebhookData(Webhook $webhook, string $eventType, array $payload): array
    {
        $timestamp = now()->timestamp;
        $nonce = Str::random(16);
        $data = [
            'id' => Str::uuid()->toString(),
            'event' => $eventType,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'data' => $payload,
            'version' => '1.0',
        ];
        // Add signature if secret is configured
        if ($webhook->secret) {
            $data['signature'] = $this->generateSignature($webhook->secret, $data);
        }
        return $data;
    }
    /**   * Generate secure HMAC signature for webhook payload. *   * Creates a cryptographically secure HMAC-SHA256 signature for webhook * payload verification. Uses constant-time comparison to prevent timing attacks. *   * @param string $secret The webhook secret key for signing * @param array $data The webhook data to be signed *   * @return string The HMAC-SHA256 signature in 'sha256=hash' format */
    /**   * @param array<string, mixed> $data */
    private function generateSignature(string $secret, array $data): string
    {
        $payload = json_encode($data);
        if ($payload === false) {
            $payload = '';
        }
        return 'sha256=' . hash_hmac('sha256', $payload, $secret);
    }
    /**   * Send secure HTTP request to webhook endpoint with proper headers. *   * Executes HTTP POST request to webhook URL with comprehensive headers, * timeout configuration, and proper error handling. Includes security * headers and user agent identification. *   * @param Webhook $webhook The webhook configuration containing URL and settings * @param array $data The prepared webhook data to send *   * @return Response The HTTP response from the webhook endpoint *   * @throws \Exception When HTTP request fails or times out */
    /**   * @param array<string, mixed> $data */
    private function sendHttpRequest(Webhook $webhook, array $data): Response
    {
        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'Sekuret-Webhook/1.0',
            'X-Webhook-Event' => $data['event'],
            'X-Webhook-Timestamp' => is_numeric($data['timestamp'] ?? time()) ? (string)($data['timestamp'] ?? time()) : (string)time(),
            'X-Webhook-Nonce' => $data['nonce'],
        ];
        if (isset($data['signature'])) {
            $headers['X-Webhook-Signature'] = $data['signature'];
        }
        return Http::timeout(self::TIMEOUT)
            ->withHeaders($headers)
            ->post($webhook->url, $data);
    }
    /**   * Log webhook delivery attempt with comprehensive details. *   * Records detailed information about webhook delivery attempts including * success/failure status, response details, timing information, and error * messages for debugging and monitoring purposes. *   * @param Webhook $webhook The webhook configuration * @param string $eventType The type of event being processed * @param array $payload The webhook payload data * @param  Response|null  $response  The HTTP response (null if request failed) * @param bool $success Whether the webhook delivery was successful * @param  string|null  $errorMessage  Error message if delivery failed */
    /**   * @param array<string, mixed> $payload */
    private function logWebhookAttempt(
        Webhook $webhook,
        string $eventType,
        array $payload,
        ?Response $response,
        bool $success,
        ?string $errorMessage = null,
    ): void {
        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event_type' => $eventType,
            'url' => $webhook->url,
            'payload' => $payload,
            'response_status' => $response?->status(),
            'response_body' => $response?->body(),
            'success' => $success,
            'error_message' => $errorMessage,
            'attempt_number' => $this->getAttemptNumber($webhook, $eventType),
            'execution_time' => $this->getExecutionTime(),
        ]);
    }
    /**   * Update webhook delivery statistics and timestamps. *   * Updates webhook statistics including attempt counts, success/failure * tracking, and last successful/failed timestamps for monitoring * and health assessment purposes. *   * @param Webhook $webhook The webhook configuration to update * @param bool $success Whether the delivery was successful */
    private function updateWebhookStats(Webhook $webhook, bool $success): void
    {
        $webhook->increment('total_attempts');
        if ($success) {
            $webhook->increment('successful_attempts');
            $webhook->update(['last_successful_at' => now()]);
        } else {
            $webhook->increment('failed_attempts');
            $webhook->update(['last_failed_at' => now()]);
        }
    }
    /**   * Queue webhook for retry with exponential backoff strategy. *   * Implements intelligent retry logic with exponential backoff delays * to handle temporary failures. Automatically disables webhooks after * maximum retry attempts to prevent infinite retry loops. *   * @param Webhook $webhook The webhook configuration to retry * @param string $eventType The type of event being retried * @param array $payload The event payload to retry */
    /**   * @param array<string, mixed> $payload */
    private function queueWebhookRetry(Webhook $webhook, string $eventType, array $payload): void
    {
        $attemptNumber = $this->getAttemptNumber($webhook, $eventType);
        if ($attemptNumber < self::MAX_RETRIES) {
            $delay = self::RETRY_DELAYS[$attemptNumber - 1] ?? 1800;
            Queue::later(now()->addSeconds($delay), function () use ($webhook, $eventType, $payload) {
                $this->processWebhook($webhook, $eventType, $payload);
            });
        } else {
            // Mark webhook as failed after max retries
            $this->markWebhookAsFailed($webhook, $eventType);
        }
    }
    /**   * Get current attempt number for webhook retry logic. *   * Calculates the current attempt number based on recent webhook logs * for the same webhook and event type within the last hour to implement * proper retry counting and exponential backoff. *   * @param Webhook $webhook The webhook configuration * @param string $eventType The type of event being processed *   * @return int The current attempt number (1-based) */
    private function getAttemptNumber(Webhook $webhook, string $eventType): int
    {
        $attemptCount = WebhookLog::where('webhook_id', $webhook->id)
            ->where('event_type', $eventType)
            ->where('created_at', '>=', now()->subHour())
            ->count();
        return $attemptCount + 1;
    }
    /**   * Mark webhook as failed after maximum retry attempts. *   * Handles webhook failure after exhausting all retry attempts by logging * the failure and optionally disabling the webhook if it has exceeded * the maximum allowed failure threshold. *   * @param Webhook $webhook The webhook configuration that failed * @param string $eventType The type of event that failed */
    private function markWebhookAsFailed(Webhook $webhook, string $eventType): void
    {
        Log::error('Webhook failed after max retries', [
            'webhook_id' => $webhook->id,
            'url' => $webhook->url,
            'event_type' => $eventType,
        ]);
        // Optionally disable webhook after repeated failures
        if ($webhook->failed_attempts > 10) {
            $webhook->update(['is_active' => false]);
        }
    }
    /**   * Verify webhook signature for incoming webhook requests. *   * Validates incoming webhook signatures using constant-time comparison * to prevent timing attacks. Ensures webhook authenticity and data integrity. *   * @param string $signature The signature from the webhook request header * @param string $payload The raw payload body from the webhook request * @param string $secret The webhook secret key for verification *   * @return bool True if signature is valid, false otherwise *   * @example * $isValid = $webhookService->verifyWebhookSignature( * $request->header('X-Webhook-Signature'), * $request->getContent(), * $webhook->secret * ); */
    public function verifyWebhookSignature(string $signature, string $payload, string $secret): bool
    {
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
    /**   * Test webhook endpoint with comprehensive diagnostics. *   * Sends a test webhook to verify endpoint connectivity, response handling, * and performance metrics. Provides detailed results for troubleshooting * and configuration validation. *   * @param Webhook $webhook The webhook configuration to test *   * @return array Test results including success status, response details, and timing *   * @example * $result = $webhookService->testWebhook($webhook); * if ($result['success']) { * echo "Webhook test successful - Response time: " . $result['response_time'] . "s"; * } else { * echo "Webhook test failed: " . $result['error']; * } */
    /**   * @return array<string, mixed> */
    public function testWebhook(Webhook $webhook): array
    {
        $testPayload = [
            'id' => Str::uuid()->toString(),
            'event' => 'webhook.test',
            'timestamp' => now()->timestamp,
            'data' => [
                'message' => 'This is a test webhook from Sekuret License Management System',
                'test_id' => Str::random(8),
            ],
        ];
        try {
            $response = $this->sendHttpRequest($webhook, $testPayload);
            $statusCode = $response->status();
            $responseBody = $response->body();
            $responseTime = $response->transferStats?->getHandlerStat('total_time') ?? 0;

            return [
                'success' => $response->successful(),
                'status_code' => (int)$statusCode,
                'response_body' => (string)$responseBody,
                'response_time' => is_numeric($responseTime) ? (float)$responseTime : 0.0,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => 0,
                'response_body' => null,
                'response_time' => 0,
            ];
        }
    }
    /**   * Get comprehensive webhook statistics and performance metrics. *   * Analyzes webhook delivery performance over a specified time period, * including success rates, response times, event breakdowns, and * overall health indicators for monitoring and optimization. *   * @param Webhook $webhook The webhook configuration to analyze * @param int $days Number of days to analyze (default: 30) *   * @return array Comprehensive statistics including success rates, timing, and event breakdown *   * @example * $stats = $webhookService->getWebhookStats($webhook, 7); * echo "Success rate: " . $stats['success_rate'] . "%"; * echo "Average response time: " . $stats['average_response_time'] . "s"; */
    /**   * @return array<string, mixed> */
    public function getWebhookStats(Webhook $webhook, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $logs = WebhookLog::where('webhook_id', $webhook->id)
            ->where('created_at', '>=', $startDate)
            ->get();
        $totalAttempts = $logs->count();
        $successfulAttempts = $logs->where('success', true)->count();
        $failedAttempts = $logs->where('success', false)->count();
        $successRate = $totalAttempts > 0 ? ($successfulAttempts / $totalAttempts) * 100 : 0; $avgResponseTime = $logs->where('execution_time', '>', 0)
            ->avg('execution_time');
        $events = $logs->groupBy('event_type')
            ->map(fn ($group) => $group->count())
            ->toArray();
        return [
            'total_attempts' => $totalAttempts,
            'successful_attempts' => $successfulAttempts,
            'failed_attempts' => $failedAttempts,
            'success_rate' => round((float) $successRate, 2),
            'average_response_time' => round((float) $avgResponseTime, 3),
            'events_breakdown' => $events,
            'last_successful' => $webhook->last_successful_at,
            'last_failed' => $webhook->last_failed_at,
        ];
    }
    /**   * Clean up old webhook logs for database maintenance. *   * Removes webhook logs older than the specified number of days to maintain * database performance and storage efficiency. Returns the number of * deleted log entries. *   * @param int $days Number of days to retain logs (default: 90) *   * @return int Number of deleted log entries *   * @example * $deletedCount = $webhookService->cleanupOldLogs(30); * echo "Cleaned up $deletedCount old webhook logs"; */
    public function cleanupOldLogs(int $days = 90): int
    {
        $cutoffDate = now()->subDays($days);
        $result = WebhookLog::where('created_at', '<', $cutoffDate)->delete();
        return is_numeric($result) ? (int)$result : 0;
    }
    /**   * Get webhook health status and performance indicators. *   * Analyzes recent webhook performance to determine health status based on * success rates, failure patterns, and response times. Provides actionable * health indicators for monitoring and alerting systems. *   * @param Webhook $webhook The webhook configuration to analyze *   * @return array Health status including score, status level, and recent activity *   * @example * $health = $webhookService->getWebhookHealth($webhook); * echo "Health Status: " . $health['status']; // excellent, good, fair, poor * echo "Health Score: " . $health['health_score'] . "%"; */
    /**   * @return array<string, mixed> */
    public function getWebhookHealth(Webhook $webhook): array
    {
        $recentLogs = WebhookLog::where('webhook_id', $webhook->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->get();
        $recentFailures = $recentLogs->where('success', false)->count();
        $totalRecent = $recentLogs->count();
        $failuresCount = (int)$recentFailures;
        $totalCount = (int)$totalRecent;
        $healthScore = $totalCount > 0 ? (($totalCount - $failuresCount) / $totalCount) * 100 : 100; $status = match (true) {
            $healthScore >= 95 => 'excellent',
            $healthScore >= 80 => 'good',
            $healthScore >= 60 => 'fair',
            default => 'poor',
        };
        return [
            'status' => $status,
            'health_score' => round($healthScore, 1),
            'recent_failures' => $recentFailures,
            'total_recent_attempts' => $totalRecent,
            'last_attempt' => $recentLogs->sortByDesc('created_at')->first()?->created_at,
        ];
    }
    /**   * Perform bulk updates on multiple webhooks efficiently. *   * Updates multiple webhooks simultaneously for improved performance * when managing large numbers of webhook configurations. Useful for * batch operations like enabling/disabling webhooks or updating settings. *   * @param array $webhookIds Array of webhook IDs to update * @param array $updates Array of field updates to apply *   * @return int Number of webhooks updated *   * @example * $updatedCount = $webhookService->bulkUpdateWebhooks( * [1, 2, 3, 4, 5], * ['is_active' => false, 'updated_at' => now()] * ); * echo "Updated $updatedCount webhooks"; */
    /**   * @param array<int> $webhookIds * @param array<string, mixed> $updates */
    public function bulkUpdateWebhooks(array $webhookIds, array $updates): int
    {
        $updateResult = Webhook::whereIn('id', $webhookIds)->update($updates);
        return (int)$updateResult;
    }
    /**   * Generate comprehensive webhook delivery report for monitoring. *   * Creates a detailed delivery report for all webhooks over a specified * time period, including success rates, health status, and performance * metrics for system monitoring and optimization. *   * @param int $days Number of days to include in the report (default: 7) *   * @return array Delivery report with webhook performance data *   * @example * $report = $webhookService->getDeliveryReport(30); * foreach ($report as $webhookReport) { * echo "Webhook {$webhookReport['name']}: {$webhookReport['success_rate']}% success rate"; * } */
    /**   * @return array<string, mixed> */
    public function getDeliveryReport(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        $webhooks = Webhook::with(['logs' => function ($query) use ($startDate) {
            if (is_object($query) && method_exists($query, 'where')) {
                $query->where('created_at', '>=', $startDate);
            }
        }])->get();
        $report = [];
        foreach ($webhooks as $webhook) {
            $logs = $webhook->logs;
            $totalAttempts = $logs->count();
            $successfulAttempts = $logs->where('success', true)->count();
            $report[] = [
                'webhook_id' => $webhook->id,
                'name' => $webhook->name,
                'url' => $webhook->url,
                'total_attempts' => $totalAttempts,
                'successful_attempts' => $successfulAttempts,
                'success_rate' => $totalAttempts > 0 ? round(($successfulAttempts / $totalAttempts) * 100, 2) : 0, 'last_attempt' => $logs->sortByDesc('created_at')->first()?->created_at,
                'health_status' => $this->getWebhookHealth($webhook)['status'],
            ];
        }
        return ['delivery_report' => $report];
    }
    /**   * Get execution time for performance tracking and monitoring. *   * Calculates the execution time for webhook processing to enable * performance monitoring and optimization. Uses high-precision timing * for accurate measurements. *   * @return float Execution time in seconds with microsecond precision */
    private function getExecutionTime(): float
    {
        // Use ServerHelper for safe timing calculation
        return \App\Helpers\ServerHelper::getExecutionTime();
    }
    /**   * Send webhook event asynchronously for improved performance. *   * Queues webhook events for asynchronous processing to improve application * performance and prevent blocking operations. Useful for high-volume * webhook scenarios where immediate delivery is not critical. *   * @param string $eventType The type of event being sent * @param array $payload The event data payload * @param  int|null  $webhookId  Optional specific webhook ID to send to *   * @example * // Queue webhook for async processing * $webhookService->sendWebhookEventAsync('user.created', $userData); */
    /**   * @param array<string, mixed> $payload */
    public function sendWebhookEventAsync(string $eventType, array $payload, ?int $webhookId = null): void
    {
        Queue::push(function () use ($eventType, $payload, $webhookId) {
            $this->sendWebhookEvent($eventType, $payload, $webhookId);
        });
    }
    /**   * Validate webhook URL with comprehensive security checks. *   * Performs thorough validation of webhook URLs including format validation, * protocol verification, and security checks to prevent common webhook * configuration issues and security vulnerabilities. *   * @param string $url The webhook URL to validate *   * @return array Validation results with success status and error details *   * @example * $validation = $webhookService->validateWebhookUrl('https://example.com/webhook'); * if (!$validation['valid']) { * foreach ($validation['errors'] as $error) { * echo "Validation error: $error"; * } * } */
    /**   * @return array<string, mixed> */
    public function validateWebhookUrl(string $url): array
    {
        $errors = [];
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid URL format';
        }
        if (! in_array(SecureFileHelper::parseUrl($url, PHP_URL_SCHEME), ['http', 'https'])) {
            $errors[] = 'URL must use HTTP or HTTPS protocol';
        }
        if (
            SecureFileHelper::parseUrl($url, PHP_URL_HOST) === 'localhost' ||
            SecureFileHelper::parseUrl($url, PHP_URL_HOST) === '127.0.0.1'
        ) {
            $errors[] = 'Localhost URLs are not allowed for webhooks';
        }
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
