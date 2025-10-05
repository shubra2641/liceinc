<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
class LicenseVerificationLog extends Model
{
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
    /**
     * Scope for successful verifications.
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('is_valid', true)->where('status', 'success');
    }
    /**
     * Scope for failed verifications.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('is_valid', false);
    }
    /**
     * Scope for specific domain.
     */
    public function scopeForDomain(Builder $query, string $domain): Builder
    {
        return $query->where('domain', $domain);
    }
    /**
     * Scope for specific IP address.
     */
    public function scopeForIp(Builder $query, string $ip): Builder
    {
        return $query->where('ip_address', $ip);
    }
    /**
     * Scope for specific verification source.
     */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('verification_source', $source);
    }
    /**
     * Scope for recent attempts (last 24 hours).
     */
    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
    /**
     * Get masked purchase code for display.
     */
    public function getMaskedPurchaseCodeAttribute(): string
    {
        // Show first 4 and last 4 characters, mask the middle
        $hash = $this->purchase_code_hash;
        if (strlen($hash) > 8) {
            return substr($hash, 0, 4).'****'.substr($hash, -4);
        }
        return '****';
    }
    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'success' => 'badge-success',
            'failed' => 'badge-danger',
            'error' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
    /**
     * Get verification source badge class.
     */
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
