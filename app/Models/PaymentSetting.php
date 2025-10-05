<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PaymentSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'gateway',
        'is_enabled',
        'is_sandbox',
        'credentials',
        'webhook_url',
    ];
    protected $casts = [
        'is_enabled' => 'boolean',
        'is_sandbox' => 'boolean',
        'credentials' => 'array',
    ];
    /**
     * Get payment setting by gateway.
     */
    public static function getByGateway(string $gateway): ?self
    {
        return static::where('gateway', $gateway)->first();
    }
    /**
     * Check if gateway is enabled.
     */
    public static function isGatewayEnabled(string $gateway): bool
    {
        $setting = static::getByGateway($gateway);
        return $setting ? $setting->is_enabled : false;
    }
    /**
     * Get enabled gateways.
     */
    public static function getEnabledGateways(): array
    {
        return static::where('is_enabled', true)
            ->pluck('gateway')
            ->toArray();
    }
    /**
     * Get credentials for a specific gateway.
     */
    public static function getCredentials(string $gateway): array
    {
        $setting = static::getByGateway($gateway);
        return $setting ? $setting->credentials : [];
    }
}
