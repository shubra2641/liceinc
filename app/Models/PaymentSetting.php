<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $gateway
 * @property bool $is_enabled
 * @property bool $is_sandbox
 * @property array<array-key, mixed>|null $credentials
 * @property string|null $webhook_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\PaymentSettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting whereCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting whereIsSandbox($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSetting whereWebhookUrl($value)
 * @mixin \Eloquent
 */
class PaymentSetting extends Model
{
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = PaymentSettingFactory::class;

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
    /**
     * @return array<string>
     */
    public static function getEnabledGateways(): array
    {
        $gateways = static::where('is_enabled', true)
            ->pluck('gateway')
            ->toArray();
        $result = [];
        foreach ($gateways as $gateway) {
            if (is_string($gateway)) {
                $result[] = $gateway;
            }
        }
        return $result;
    }
    /**
     * Get credentials for a specific gateway.
     */
    /**
     * @return array<string, mixed>
     */
    public static function getCredentials(string $gateway): array
    {
        $setting = static::getByGateway($gateway);
        $credentials = $setting ? (is_array($setting->credentials) ? $setting->credentials : []) : [];
        /** @var array<string, mixed> $typedCredentials */
        $typedCredentials = $credentials;
        return $typedCredentials;
    }
}
