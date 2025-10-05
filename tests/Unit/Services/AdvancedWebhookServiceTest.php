<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Webhook;
use App\Models\WebhookLog;
use App\Services\AdvancedWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Advanced Webhook Service Test.
 *
 * Comprehensive test suite for the AdvancedWebhookService class
 * covering all methods and edge cases.
 */
class AdvancedWebhookServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdvancedWebhookService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdvancedWebhookService();
    }

    /** @test */
    public function it_can_send_webhook_event_with_valid_data(): void
    {
        // Arrange
        Http::fake([
            'https://example.com/webhook' => Http::response(['success' => true], 200),
        ]);

        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook',
            'is_active' => true,
            'events' => ['test.event'],
        ]);

        $eventType = 'test.event';
        $payload = ['message' => 'Test webhook'];

        // Act
        $this->service->sendWebhookEvent($eventType, $payload);

        // Assert
        Http::assertSent(function ($request) use ($webhook, $eventType) {
            return $request->url() === $webhook->url &&
                   $request->header('X-Webhook-Event') === $eventType &&
                   $request->header('Content-Type') === 'application/json';
        });

        $this->assertDatabaseHas('webhook_logs', [
            'webhook_id' => $webhook->id,
            'event_type' => $eventType,
            'success' => true,
        ]);
    }

    /** @test */
    public function it_throws_exception_for_invalid_event_type(): void
    {
        // Arrange
        $eventType = 'invalid@event#type';
        $payload = ['message' => 'Test'];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid event type format');

        $this->service->sendWebhookEvent($eventType, $payload);
    }

    /** @test */
    public function it_sanitizes_payload_data(): void
    {
        // Arrange
        Http::fake([
            'https://example.com/webhook' => Http::response(['success' => true], 200),
        ]);

        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook',
            'is_active' => true,
            'events' => ['test.event'],
        ]);

        $payload = [
            'message' => '<script>alert("xss")</script>',
            'data' => [
                'nested' => 'value with "quotes"',
            ],
        ];

        // Act
        $this->service->sendWebhookEvent('test.event', $payload);

        // Assert
        Http::assertSent(function ($request) {
            $data = $request->data();

            return ! str_contains($data['data']['message'], '<script>') &&
                   str_contains($data['data']['message'], '&lt;script&gt;');
        });
    }

    /** @test */
    public function it_handles_webhook_failure_and_retries(): void
    {
        // Arrange
        Http::fake([
            'https://example.com/webhook' => Http::response(['error' => 'Server error'], 500),
        ]);

        Queue::fake();

        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook',
            'is_active' => true,
            'events' => ['test.event'],
        ]);

        // Act
        $this->service->sendWebhookEvent('test.event', ['message' => 'Test']);

        // Assert
        $this->assertDatabaseHas('webhook_logs', [
            'webhook_id' => $webhook->id,
            'success' => false,
        ]);

        Queue::assertPushed(function ($job) {
            return true; // Queue job was pushed for retry
        });
    }

    /** @test */
    public function it_generates_webhook_signature_correctly(): void
    {
        // Arrange
        $secret = 'test-secret';
        $payload = '{"test": "data"}';

        // Act
        $signature = $this->service->verifyWebhookSignature(
            'sha256='.hash_hmac('sha256', $payload, $secret),
            $payload,
            $secret,
        );

        // Assert
        $this->assertTrue($signature);
    }

    /** @test */
    public function it_validates_webhook_url_correctly(): void
    {
        // Test valid URL
        $result = $this->service->validateWebhookUrl('https://example.com/webhook');
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);

        // Test invalid URL
        $result = $this->service->validateWebhookUrl('invalid-url');
        $this->assertFalse($result['valid']);
        $this->assertContains('Invalid URL format', $result['errors']);

        // Test localhost URL (should be rejected)
        $result = $this->service->validateWebhookUrl('http://localhost/webhook');
        $this->assertFalse($result['valid']);
        $this->assertContains('Localhost URLs are not allowed for webhooks', $result['errors']);
    }

    /** @test */
    public function it_tests_webhook_endpoint(): void
    {
        // Arrange
        Http::fake([
            'https://example.com/webhook' => Http::response(['success' => true], 200),
        ]);

        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook',
        ]);

        // Act
        $result = $this->service->testWebhook($webhook);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
        $this->assertArrayHasKey('response_time', $result);
    }

    /** @test */
    public function it_gets_webhook_statistics(): void
    {
        // Arrange
        $webhook = Webhook::factory()->create();

        WebhookLog::factory()->count(5)->create([
            'webhook_id' => $webhook->id,
            'success' => true,
        ]);

        WebhookLog::factory()->count(2)->create([
            'webhook_id' => $webhook->id,
            'success' => false,
        ]);

        // Act
        $stats = $this->service->getWebhookStats($webhook, 30);

        // Assert
        $this->assertEquals(7, $stats['total_attempts']);
        $this->assertEquals(5, $stats['successful_attempts']);
        $this->assertEquals(2, $stats['failed_attempts']);
        $this->assertEquals(71.43, $stats['success_rate']);
    }

    /** @test */
    public function it_gets_webhook_health_status(): void
    {
        // Arrange
        $webhook = Webhook::factory()->create();

        WebhookLog::factory()->count(8)->create([
            'webhook_id' => $webhook->id,
            'success' => true,
            'created_at' => now()->subHours(12),
        ]);

        WebhookLog::factory()->count(2)->create([
            'webhook_id' => $webhook->id,
            'success' => false,
            'created_at' => now()->subHours(6),
        ]);

        // Act
        $health = $this->service->getWebhookHealth($webhook);

        // Assert
        $this->assertEquals('good', $health['status']);
        $this->assertEquals(80.0, $health['health_score']);
        $this->assertEquals(2, $health['recent_failures']);
        $this->assertEquals(10, $health['total_recent_attempts']);
    }

    /** @test */
    public function it_cleans_up_old_logs(): void
    {
        // Arrange
        $webhook = Webhook::factory()->create();

        WebhookLog::factory()->create([
            'webhook_id' => $webhook->id,
            'created_at' => now()->subDays(100),
        ]);

        WebhookLog::factory()->create([
            'webhook_id' => $webhook->id,
            'created_at' => now()->subDays(30),
        ]);

        // Act
        $deletedCount = $this->service->cleanupOldLogs(90);

        // Assert
        $this->assertEquals(1, $deletedCount);
        $this->assertDatabaseCount('webhook_logs', 1);
    }

    /** @test */
    public function it_bulk_updates_webhooks(): void
    {
        // Arrange
        $webhook1 = Webhook::factory()->create();
        $webhook2 = Webhook::factory()->create();
        $webhook3 = Webhook::factory()->create();

        // Act
        $updatedCount = $this->service->bulkUpdateWebhooks(
            [$webhook1->id, $webhook2->id],
            ['is_active' => false],
        );

        // Assert
        $this->assertEquals(2, $updatedCount);

        $webhook1->refresh();
        $webhook2->refresh();
        $webhook3->refresh();

        $this->assertFalse($webhook1->is_active);
        $this->assertFalse($webhook2->is_active);
        $this->assertTrue($webhook3->is_active);
    }

    /** @test */
    public function it_gets_delivery_report(): void
    {
        // Arrange
        $webhook = Webhook::factory()->create();

        WebhookLog::factory()->count(3)->create([
            'webhook_id' => $webhook->id,
            'success' => true,
            'created_at' => now()->subDays(3),
        ]);

        // Act
        $report = $this->service->getDeliveryReport(7);

        // Assert
        $this->assertCount(1, $report);
        $this->assertEquals($webhook->id, $report[0]['webhook_id']);
        $this->assertEquals(3, $report[0]['total_attempts']);
        $this->assertEquals(3, $report[0]['successful_attempts']);
        $this->assertEquals(100.0, $report[0]['success_rate']);
    }

    /** @test */
    public function it_sends_webhook_event_async(): void
    {
        // Arrange
        Queue::fake();
        $eventType = 'test.event';
        $payload = ['message' => 'Test'];

        // Act
        $this->service->sendWebhookEventAsync($eventType, $payload);

        // Assert
        Queue::assertPushed(function ($job) {
            return true; // Queue job was pushed
        });
    }

    /** @test */
    public function it_handles_webhook_with_secret(): void
    {
        // Arrange
        Http::fake([
            'https://example.com/webhook' => Http::response(['success' => true], 200),
        ]);

        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook',
            'secret' => 'test-secret',
            'is_active' => true,
            'events' => ['test.event'],
        ]);

        // Act
        $this->service->sendWebhookEvent('test.event', ['message' => 'Test']);

        // Assert
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Webhook-Signature') &&
                   ! empty($request->header('X-Webhook-Signature'));
        });
    }

    /** @test */
    public function it_marks_webhook_as_failed_after_max_retries(): void
    {
        // Arrange
        Log::fake();

        $webhook = Webhook::factory()->create([
            'failed_attempts' => 11,
        ]);

        // Act
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('markWebhookAsFailed');
        $method->setAccessible(true);
        $method->invoke($this->service, $webhook, 'test.event');

        // Assert
        $webhook->refresh();
        $this->assertFalse($webhook->is_active);

        Log::assertLogged('error', function ($message, $context) {
            return str_contains($message, 'Webhook failed after max retries');
        });
    }

    /** @test */
    public function it_handles_empty_payload(): void
    {
        // Arrange
        Http::fake([
            'https://example.com/webhook' => Http::response(['success' => true], 200),
        ]);

        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook',
            'is_active' => true,
            'events' => ['test.event'],
        ]);

        // Act
        $this->service->sendWebhookEvent('test.event', []);

        // Assert
        Http::assertSent(function ($request) {
            $data = $request->data();

            return isset($data['data']) && is_array($data['data']) && empty($data['data']);
        });
    }

    /** @test */
    public function it_handles_network_timeout(): void
    {
        // Arrange
        Http::fake([
            'https://example.com/webhook' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        Queue::fake();

        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook',
            'is_active' => true,
            'events' => ['test.event'],
        ]);

        // Act
        $this->service->sendWebhookEvent('test.event', ['message' => 'Test']);

        // Assert
        $this->assertDatabaseHas('webhook_logs', [
            'webhook_id' => $webhook->id,
            'success' => false,
            'error_message' => 'Connection timeout',
        ]);

        Queue::assertPushed(function ($job) {
            return true; // Retry job was queued
        });
    }
}
