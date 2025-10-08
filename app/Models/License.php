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
 * @property int|null $productId
 * @property int|null $customer_id
 * @property int|null $userId
 * @property string $purchaseCode
 * @property string $licenseKey
 * @property string $status
 * @property int $maxDomains
 * @property string $licenseType
 * @property string|null $supportedUntil
 * @property \Illuminate\Support\Carbon|null $licenseExpiresAt
 * @property \Illuminate\Support\Carbon|null $supportExpiresAt
 * @property string|null $purchase_date
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LicenseDomain> $domains
 * @property-read int|null $domains_count
 * @property-read int $active_domains_count
 * @property mixed $expiresAt
 *
 * @property-read int $remaining_domains
 * @property-read mixed $support_active
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LicenseLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License active()
 * @method static \Database\Factories\LicenseFactory factory($count = null, $state = [])
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
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = LicenseFactory::class;

    protected $fillable = [
        'productId',
        'userId',
        'purchase_code',
        'licenseKey',
        'licenseType',
        'support_expiresAt',
        'licenseExpiresAt',
        'status',
        'maxDomains',
        'notes',
    ];
    protected $hidden = [
        'licenseKey',
        'purchase_code',
    ];
    protected $casts = [
        'support_expiresAt' => 'datetime',
        'licenseExpiresAt' => 'datetime',
        'maxDomains' => 'integer',
    ];
    protected static function booted(): void
    {
        static::creating(function (License $license): void {
            // Ensure purchaseCode exists; some flows (Envato) provide it explicitly
            if (empty($license->purchaseCode)) {
                $license->purchaseCode = static::generateUniquePurchaseCode();
            }
            // Always use purchaseCode as licenseKey for consistency
            $license->licenseKey = (string)$license->purchaseCode;
        });
    }
    protected static function generateUniquePurchaseCode(): string
    {
        do {
            $code = strtoupper(Str::random(16));
            // Format like XXXX-XXXX-XXXX-XXXX
            $code = substr($code, 0, 4) . '-' . substr($code, 4, 4) . '-'
                . substr($code, 8, 4) . '-' . substr($code, 12, 4);
        } while (static::where('purchase_code', $code)->exists());
        return $code;
    }
    protected static function generateUniqueLicenseKey(): string
    {
        do {
            $key = strtoupper(Str::random(32));
            // Format like XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX
            $key = substr($key, 0, 8) . '-' . substr($key, 8, 8) . '-'
                . substr($key, 16, 8) . '-' . substr($key, 24, 8);
        } while (static::where('licenseKey', $key)->exists());
        return $key;
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
                $q->whereNull('licenseExpiresAt')
                    ->orWhere('licenseExpiresAt', '>', now());
            });
    }
    /**
     * Scope a query to licenses belonging to a given user instance or user id.
     * This will match licenses where userId = user id or where the linked
     * customer record has the same email as the user (common mapping in this app).
     */
    /**
     * @param Builder<License> $query
     *
     * @return Builder<License>
     */
    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = null;
        if (is_numeric($user)) {
            $userId = (int)$user;
        } elseif ($user instanceof User) {
            $userId = $user->id;
        }
        return $query->where('userId', $userId);
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
        return $query->where('userId', $customerId);
    }
    /**
     * Check if support is active.
     */
    public function getSupportActiveAttribute(): bool
    {
        return $this->supportExpiresAt && $this->supportExpiresAt->isFuture();
    }
    /**
     * Get expiresAt attribute (alias for licenseExpiresAt).
     */
    public function getExpiresAtAttribute(): ?\Carbon\Carbon
    {
        return $this->licenseExpiresAt;
    }
    /**
     * Set expiresAt attribute (alias for licenseExpiresAt).
     */
    public function setExpiresAtAttribute(?\Carbon\Carbon $value): void
    {
        $this->licenseExpiresAt = $value ? \Illuminate\Support\Carbon::instance($value) : null;
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
        $maxDomains = $this->maxDomains ?? 1;
        return $this->activeDomainsCount >= $maxDomains;
    }
    /**
     * Get remaining domains that can be added.
     */
    public function getRemainingDomainsAttribute(): int
    {
        $maxDomains = $this->maxDomains ?? 1;
        return max(0, $maxDomains - $this->activeDomainsCount);
    }
}
