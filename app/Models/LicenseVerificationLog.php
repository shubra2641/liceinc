<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id * @property string $purchase_code_hash * @property string $domain * @property string $ip_address * @property string|null $user_agent * @property bool $is_valid * @property string|null $response_message * @property array<array-key, mixed>|null $response_data * @property string $verification_source * @property string $status * @property string|null $error_details * @property \Illuminate\Support\Carbon|null $verified_at * @property \Illuminate\Support\Carbon|null $created_at * @property \Illuminate\Support\Carbon|null $updated_at * @property-read string $masked_purchase_code * @property-read string $source_badge_class * @property-read string $status_badge_class * @method static Builder<static>|LicenseVerificationLog failed() * @method static Builder<static>|LicenseVerificationLog forDomain(string $domain) * @method static Builder<static>|LicenseVerificationLog forIp(string $ip) * @method static Builder<static>|LicenseVerificationLog fromSource(string $source) * @method static Builder<static>|LicenseVerificationLog newModelQuery() * @method static Builder<static>|LicenseVerificationLog newQuery() * @method static Builder<static>|LicenseVerificationLog query() * @method static Builder<static>|LicenseVerificationLog recent(int $hours = 24) * @method static Builder<static>|LicenseVerificationLog successful() * @method static Builder<static>|LicenseVerificationLog whereCreatedAt($value) * @method static Builder<static>|LicenseVerificationLog whereDomain($value) * @method static Builder<static>|LicenseVerificationLog whereErrorDetails($value) * @method static Builder<static>|LicenseVerificationLog whereId($value) * @method static Builder<static>|LicenseVerificationLog whereIpAddress($value) * @method static Builder<static>|LicenseVerificationLog whereIsValid($value) * @method static Builder<static>|LicenseVerificationLog wherePurchaseCodeHash($value) * @method static Builder<static>|LicenseVerificationLog whereResponseData($value) * @method static Builder<static>|LicenseVerificationLog whereResponseMessage($value) * @method static Builder<static>|LicenseVerificationLog whereStatus($value) * @method static Builder<static>|LicenseVerificationLog whereUpdatedAt($value) * @method static Builder<static>|LicenseVerificationLog whereUserAgent($value) * @method static Builder<static>|LicenseVerificationLog whereVerificationSource($value) * @method static Builder<static>|LicenseVerificationLog whereVerifiedAt($value) * @mixin \Eloquent */
class LicenseVerificationLog extends Model
{
    /**   * @phpstan-ignore-next-line */
    use HasFactory;

    /**   * @phpstan-ignore-next-line */
    protected static $factory = LicenseVerificationLogFactory::class;
    protected $fillable = [
        'purchase_code_hash',
        'domain',
        'ip_address',
        'user_agent',
        'is_valid',
        'response_message',
        'response_data',
        'verification_source',
        'status',
        'error_details',
        'verified_at',
    ];
    protected $casts = [
        'is_valid' => 'boolean',
        'response_data' => 'array',
        'verified_at' => 'datetime',
    ];
    /**   * Scope for successful verifications. * @return Builder<LicenseVerificationLog> */
    /**   * @param Builder<LicenseVerificationLog> $query * @return Builder<LicenseVerificationLog> */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('is_valid', true)->where('status', 'success');
    }
    /**   * Scope for failed verifications. * @param Builder<LicenseVerificationLog> $query * @return Builder<LicenseVerificationLog> */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('is_valid', false);
    }
    /**   * Scope for specific domain. * @param Builder<LicenseVerificationLog> $query * @return Builder<LicenseVerificationLog> */
    public function scopeForDomain(Builder $query, string $domain): Builder
    {
        return $query->where('domain', $domain);
    }
    /**   * Scope for specific IP address. * @param Builder<LicenseVerificationLog> $query * @return Builder<LicenseVerificationLog> */
    public function scopeForIp(Builder $query, string $ip): Builder
    {
        return $query->where('ip_address', $ip);
    }
    /**   * Scope for specific verification source. * @param Builder<LicenseVerificationLog> $query * @return Builder<LicenseVerificationLog> */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('verification_source', $source);
    }
    /**   * Scope for recent attempts (last 24 hours). * @param Builder<LicenseVerificationLog> $query * @return Builder<LicenseVerificationLog> */
    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
    /**   * Get masked purchase code for display. */
    public function getMaskedPurchaseCodeAttribute(): string
    {
        // Show first 4 and last 4 characters, mask the middle
        $hash = $this->purchase_code_hash;
        if (strlen($hash) > 8) {
            return substr($hash, 0, 4) . '****' . substr($hash, -4);
        }
        return '****';
    }
    /**   * Get status badge class. */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'success' => 'badge-success',
            'failed' => 'badge-danger',
            'error' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
    /**   * Get verification source badge class. */
    public function getSourceBadgeClassAttribute(): string
    {
        return match ($this->verification_source) {
            'install' => 'badge-primary',
            'api' => 'badge-info',
            'admin' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}
