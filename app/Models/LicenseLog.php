<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $licenseId
 * @property string $domain
 * @property string $ipAddress
 * @property string $serial
 * @property string $status
 * @property string|null $user_agent
 * @property array<array-key, mixed>|null $request_data
 * @property array<array-key, mixed>|null $response_data
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property-read string|null $action
 * @property-read string|null $message
 * @property-read \App\Models\License $license
 * @method static \Database\Factories\LicenseLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereRequestData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseLog whereUserAgent($value)
 * @mixin \Eloquent
 */
class LicenseLog extends Model
{
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = LicenseLogFactory::class;

    protected $fillable = [
        'licenseId',
        'domain',
        'ipAddress',
        'serial',
        'status',
        'user_agent',
        'request_data',
        'response_data',
    ];
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];
    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
    /**
     * Virtual attribute to access the action from request_data.
     */
    public function getActionAttribute(): ?string
    {
        $data = $this->request_data;
        if ($data === null) {
            return null;
        }
        // Data is already type-hinted as array
        $action = $data['action'] ?? null;
        return is_string($action) ? $action : null;
    }
    /**
     * Virtual attribute to access the message from response_data.
     */
    public function getMessageAttribute(): ?string
    {
        $data = $this->response_data;
        if ($data === null) {
            return null;
        }
        // Data is already type-hinted as array
        $message = $data['message'] ?? null;
        return is_string($message) ? $message : null;
    }
    /**
     * Get API calls grouped by date for the last N days.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, LicenseLog>
     */
    public static function getApiCallsByDate(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return static::selectRaw('DATE(createdAt) as date, COUNT(*) as count')
            ->where('createdAt', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
    /**
     * Get API status distribution for the last N days.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, LicenseLog>
     */
    public static function getApiStatusDistribution(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return static::selectRaw('status, COUNT(*) as count')
            ->where('createdAt', '>=', now()->subDays($days))
            ->groupBy('status')
            ->get();
    }
    /**
     * Get top domains by API calls.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, LicenseLog>
     */
    public static function getTopDomainsByCalls(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::selectRaw('domain, COUNT(*) as calls')
            ->whereNotNull('domain')
            ->groupBy('domain')
            ->orderBy('calls', 'desc')
            ->limit($limit)
            ->get();
    }
    /**
     * Get API calls by hour for today.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, LicenseLog>
     */
    public static function getApiCallsByHour(): \Illuminate\Database\Eloquent\Collection
    {
        return static::selectRaw('HOUR(createdAt) as hour, COUNT(*) as count')
            ->whereDate('createdAt', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }
}
