<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $license_id
 * @property string $domain
 * @property string $status
 * @property bool $is_verified
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property \Illuminate\Support\Carbon|null $added_at
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read License $license
 *
 * @method static \Database\Factories\LicenseDomainFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain whereAddedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain whereLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseDomain whereVerifiedAt($value)
 *
 * @mixin \Eloquent
 */
class LicenseDomain extends Model
{
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = LicenseDomainFactory::class;

    protected $fillable = [
        'license_id', 'domain', 'status', 'is_verified', 'verified_at', 'added_at', 'last_used_at',
    ];

    protected $casts = [
        'license_id' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'added_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
