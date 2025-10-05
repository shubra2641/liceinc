<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class License extends Model
{
    use HasFactory;
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
            $code = substr($code, 0, 4).'-'.substr($code, 4, 4).'-'.substr($code, 8, 4).'-'.substr($code, 12, 4);
        } while (static::where('purchase_code', $code)->exists());
        return $code;
    }
    protected static function generateUniqueLicenseKey(): string
    {
        do {
            $key = strtoupper(Str::random(32));
            // Format like XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX
            $key = substr($key, 0, 8).'-'.substr($key, 8, 8).'-'.substr($key, 16, 8).'-'.substr($key, 24, 8);
        } while (static::where('license_key', $key)->exists());
        return $key;
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function domains()
    {
        return $this->hasMany(LicenseDomain::class);
    }
    public function logs()
    {
        return $this->hasMany(LicenseLog::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    /**
     * Scope a query to only active licenses (status = active and not expired).
     */
    public function scopeActive($query)
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
    public function scopeForUser($query, $user)
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
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('user_id', $customerId);
    }
    /**
     * Check if support is active.
     */
    public function getSupportActiveAttribute()
    {
        return $this->support_expires_at && $this->support_expires_at->isFuture();
    }
    /**
     * Get expires_at attribute (alias for license_expires_at).
     */
    public function getExpiresAtAttribute()
    {
        return $this->license_expires_at;
    }
    /**
     * Set expires_at attribute (alias for license_expires_at).
     */
    public function setExpiresAtAttribute($value)
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
