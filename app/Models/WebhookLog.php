<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WebhookLog Model
 *
 * @property int $id
 * @property int $webhook_id
 * @property string $event_type
 * @property array<string, mixed> $payload
 * @property int $response_status
 * @property string|null $response_body
 * @property float $execution_time
 * @property bool $is_successful
 * @property string|null $error_message
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class WebhookLog extends Model
{
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = WebhookLogFactory::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * @var list<string>
     */
    protected $fillable = [
        'webhook_id',
        'event_type',
        'payload',
        'response_status',
        'response_body',
        'execution_time',
        'is_successful',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
        'response_status' => 'integer',
        'execution_time' => 'float',
        'is_successful' => 'boolean',
    ];

    /**
     * Get the webhook that owns the log.
     */
    /**
     * @return BelongsTo<Webhook, $this>
     */
    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    /**
     * Scope a query to only include successful logs.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<WebhookLog> $query
     * @return \Illuminate\Database\Eloquent\Builder<WebhookLog>
     */
    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    /**
     * Scope a query to only include failed logs.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<WebhookLog> $query
     * @return \Illuminate\Database\Eloquent\Builder<WebhookLog>
     */
    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }

    /**
     * Scope a query to only include logs for a specific event type.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<WebhookLog> $query
     * @param string $eventType
     * @return \Illuminate\Database\Eloquent\Builder<WebhookLog>
     */
    public function scopeEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }
}
