<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int|null $product_id
 * @property int|null $customer_id
 * @property int|null $user_id
 * @property string $purchase_code
 * @property string $license_key
 * @property string $status
 * @property int $max_domains
 * @property string $license_type
 * @property string|null $supported_until
 * @property \Illuminate\Support\Carbon|null $license_expires_at
 * @property \Illuminate\Support\Carbon|null $support_expires_at
 * @property string|null $purchase_date
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $expires_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LicenseDomain> $domains
 * @property-read int|null $domains_count
 * @property-read int $active_domains_count
 * @property-read int $remaining_domains
 * @property-read mixed $support_active
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LicenseLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License forCustomer($customerId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License forUser($user)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereMaxDomains($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License wherePurchaseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereSupportExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereSupportedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereUserId($value)
 * @mixin \Eloquent
 */
class License extends Model
{
    /**
     * @phpstan-ignore-next-line
     */


    protected $fillable = [
        'product_id',
        'user_id',
        'purchase_code',
        'license_key',
        'license_type',
        'support_expires_at',
        'license_expires_at',
        'status',
        'max_domains',
        'notes',
    ];
    protected $hidden = [
        'license_key',
        'purchase_code',
    ];
    protected $casts = [
        'support_expires_at' => 'datetime',
        'license_expires_at' => 'datetime',
        'max_domains' => 'integer',
    ];
    protected static function booted(): void
    {
        static::creating(function (License $license): void {
            static::initializeLicenseKeys($license);
        });
    }

    /**
     * Initialize license keys during creation.
     */
    protected static function initializeLicenseKeys(License $license): void
    {
        if (empty($license->purchase_code)) {
            $license->purchase_code = static::generateUniquePurchaseCode();
        }
        
        // Always use purchase_code as license_key for consistency
        $license->license_key = $license->purchase_code;
    }
    protected static function generateUniquePurchaseCode(): string
    {
        return static::generateUniqueCode('purchase_code', 16, 4);
    }

    protected static function generateUniqueLicenseKey(): string
    {
        return static::generateUniqueCode('license_key', 32, 8);
    }

    /**
     * Generate a unique code with specified length and segment size.
     */
    protected static function generateUniqueCode(string $field, int $length, int $segmentSize): string
    {
        do {
            $code = strtoupper(Str::random($length));
            $formattedCode = static::formatCode($code, $segmentSize);
        } while (static::where($field, $formattedCode)->exists());
        
        return $formattedCode;
    }

    /**
     * Format code with dashes between segments.
     */
    protected static function formatCode(string $code, int $segmentSize): string
    {
        $segments = [];
        for ($i = 0; $i < strlen($code); $i += $segmentSize) {
            $segments[] = substr($code, $i, $segmentSize);
        }
        return implode('-', $segments);
    }
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * @return HasMany<LicenseDomain, $this>
     */
    public function domains(): HasMany
    {
        return $this->hasMany(LicenseDomain::class);
    }
    /**
     * @return HasMany<LicenseLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(LicenseLog::class);
    }
    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    /**
     * Scope a query to only active licenses (status = active and not expired).
     *
     * @param Builder<License> $query
     *
     * @return Builder<License>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('license_expires_at')
                    ->orWhere('license_expires_at', '>', now());
            });
    }
    /**
     * Scope a query to licenses belonging to a given user instance or user id.
     * This will match licenses where user_id = user id or where the linked
     * customer record has the same email as the user (common mapping in this app).
     */
    /**
     * @param Builder<License> $query
     *
     * @return Builder<License>
     */
    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = static::extractUserId($user);
        return $query->where('user_id', $userId);
    }

    /**
     * Extract user ID from user instance or integer.
     */
    protected static function extractUserId(User|int $user): ?int
    {
        if (is_numeric($user)) {
            return (int)$user;
        }
        
        if ($user instanceof User) {
            return $user->id;
        }
        
        return null;
    }
    /**
     * Scope a query to licenses for a specific customer id (backwards compatibility).
     */
    /**
     * @param Builder<License> $query
     *
     * @return Builder<License>
     */
    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('user_id', $customerId);
    }
    /**
     * Check if support is active.
     */
    public function getSupportActiveAttribute(): bool
    {
        return $this->support_expires_at && $this->support_expires_at->isFuture();
    }
    /**
     * Get expires_at attribute (alias for license_expires_at).
     */
    public function getExpiresAtAttribute(): ?\Carbon\Carbon
    {
        return $this->license_expires_at;
    }
    /**
     * Set expires_at attribute (alias for license_expires_at).
     */
    public function setExpiresAtAttribute(?\Carbon\Carbon $value): void
    {
        $this->license_expires_at = $value ? \Illuminate\Support\Carbon::instance($value) : null;
    }
    /**
     * Get the number of active domains for this license.
     */
    public function getActiveDomainsCountAttribute(): int
    {
        return $this->domains()->where('status', 'active')->count();
    }
    /**
     * Check if license has reached its domain limit.
     */
    public function hasReachedDomainLimit(): bool
    {
        return $this->active_domains_count >= $this->getMaxDomains();
    }

    /**
     * Get remaining domains that can be added.
     */
    public function getRemainingDomainsAttribute(): int
    {
        return max(0, $this->getMaxDomains() - $this->active_domains_count);
    }

    /**
     * Get the maximum number of domains allowed for this license.
     */
    protected function getMaxDomains(): int
    {
        return $this->max_domains ?? 1;
    }
}
