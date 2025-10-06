<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Webhook Model
 *
 * @property int $id
 * @property string $name
 * @property string $url
 * @property string $secret
 * @property bool $is_active
 * @property int $failed_attempts
 * @property \Carbon\Carbon|null $last_successful_at
 * @property \Carbon\Carbon|null $last_failed_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Webhook extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'url',
        'secret',
        'is_active',
        'failed_attempts',
        'last_successful_at',
        'last_failed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'failed_attempts' => 'integer',
        'last_successful_at' => 'datetime',
        'last_failed_at' => 'datetime',
    ];

    /**
     * Get the webhook logs for the webhook.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Scope a query to only include active webhooks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include failed webhooks.
     */
    public function scopeFailed($query)
    {
        return $query->where('failed_attempts', '>', 0);
    }
}
