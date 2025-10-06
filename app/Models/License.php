<?php

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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LicenseDomain> $domains
 * @property-read int|null $domains_count
 * @property-read int $active_domains_count
 * @property mixed $expires_at
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
            // Ensure purchase_code exists; some flows (Envato) provide it explicitly
            if (empty($license->purchase_code)) {
                $license->purchase_code = static::generateUniquePurchaseCode();
            }
            // Always use purchase_code as license_key for consistency
            $license->license_key = $license->purchase_code;
        });
    }
    protected static function generateUniquePurchaseCode(): string
    {
        do {
            $code = strtoupper(Str::random(16));
            // Format like XXXX-XXXX-XXXX-XXXX
            $code = substr($code, 0, 4) . '-' . substr($code, 4, 4) . '-' . substr($code, 8, 4) . '-' . substr($code, 12, 4);
        } while (static::where('purchase_code', $code)->exists());
        return $code;
    }
    protected static function generateUniqueLicenseKey(): string
    {
        do {
            $key = strtoupper(Str::random(32));
            // Format like XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX
            $key = substr($key, 0, 8) . '-' . substr($key, 8, 8) . '-' . substr($key, 16, 8) . '-' . substr($key, 24, 8);
        } while (static::where('license_key', $key)->exists());
        return $key;
    }
    /**
     * @return BelongsTo<Product, License>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * @return BelongsTo<User, License>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * @return HasMany<LicenseDomain, License>
     */
    public function domains(): HasMany
    {
        return $this->hasMany(LicenseDomain::class);
    }
    /**
     * @return HasMany<LicenseLog, License>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(LicenseLog::class);
    }
    /**
     * @return HasMany<Invoice, License>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    /**
     * Scope a query to only active licenses (status = active and not expired).
     * @param Builder<License> $query
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
        return $query->where('user_id', $userId);
    }
    /**
     * Scope a query to licenses for a specific customer id (backwards compatibility).
     */
    /**
     * @param Builder<License> $query
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
        $this->license_expires_at = $value;
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
        $maxDomains = $this->max_domains ?? 1;
        return $this->active_domains_count >= $maxDomains;
    }
    /**
     * Get remaining domains that can be added.
     */
    public function getRemainingDomainsAttribute(): int
    {
        $maxDomains = $this->max_domains ?? 1;
        return max(0, $maxDomains - $this->active_domains_count);
    }
}
