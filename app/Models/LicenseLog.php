<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * @property int $id
 * @property int $license_id
 * @property string $domain
 * @property string $ip_address
 * @property string $serial
 * @property string $status
 * @property string|null $user_agent
 * @property array<array-key, mixed>|null $request_data
 * @property array<array-key, mixed>|null $response_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
    use HasFactory;
    protected $fillable = [
        'license_id',
        'domain',
        'ip_address',
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
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
    /**
     * Virtual attribute to access the action from request_data.
     */
    public function getActionAttribute(): ?string
    {
        $data = $this->request_data ?? [];
        return is_array($data) ? ($data['action'] ?? null) : null;
    }
    /**
     * Virtual attribute to access the message from response_data.
     */
    public function getMessageAttribute(): ?string
    {
        $data = $this->response_data ?? [];
        return is_array($data) ? ($data['message'] ?? null) : null;
    }
    /**
     * Get API calls grouped by date for the last N days.
     */
    public static function getApiCallsByDate($days = 30)
    {
        return static::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
    /**
     * Get API status distribution for the last N days.
     */
    public static function getApiStatusDistribution($days = 30)
    {
        return static::selectRaw('status, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('status')
            ->get();
    }
    /**
     * Get top domains by API calls.
     */
    public static function getTopDomainsByCalls($limit = 10)
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
     */
    public static function getApiCallsByHour()
    {
        return static::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->whereDate('created_at', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }
}
