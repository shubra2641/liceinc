<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
class Setting extends Model
{
    use HasFactory;
    protected $fillable = [
        'site_name',
        'site_logo',
        'support_email',
        'site_description',
        'support_phone',
        'timezone',
        'maintenance_mode',
        'envato_personal_token',
        'envato_api_key',
        'envato_auth_enabled',
        'envato_username',
        'auto_generate_license',
        'default_license_length',
        'envato_client_id',
        'envato_client_secret',
        'envato_redirect_uri',
        'envato_oauth_enabled',
        'license_api_token',
        'license_max_attempts',
        'license_lockout_minutes',
        'license_verify_envato',
        'license_fallback_internal',
        'license_cache_verification',
        'license_cache_duration',
        'license_allow_offline',
        'license_grace_period',
        'license_allow_localhost',
        'license_allow_ip_addresses',
        'license_allow_wildcards',
        'license_validate_ssl',
        'license_auto_approve_subdomains',
        'license_auto_register_domains',
        'license_max_domains',
        'license_domain_cooldown',
        'license_default_duration',
        'license_support_duration',
        'license_renewal_reminder',
        'license_expiration_grace',
        'license_auto_suspend',
        'license_allow_expired_verification',
        'license_encrypt_data',
        'license_secure_tokens',
        'license_validate_signatures',
        'license_prevent_sharing',
        'license_detect_suspicious',
        'license_block_vpn',
        'license_require_https',
        'license_notify_verification',
        'license_notify_expiration',
        'license_notify_domain_change',
        'license_notify_suspicious',
        'license_notification_email',
        'license_use_slack',
        'license_slack_webhook',
        'license_enable_caching',
        'license_cache_driver',
        'license_optimize_queries',
        'license_batch_size',
        'license_use_indexes',
        'license_compress_responses',
        'license_allow_test',
        'license_test_prefix',
        'license_bypass_testing',
        'license_mock_envato',
        'license_generate_fake_data',
        'seo_site_title',
        'seo_site_description',
        'seo_og_image',
        'seo_kb_title',
        'seo_kb_description',
        'seo_tickets_title',
        'seo_tickets_description',
        'preloader_enabled',
        'preloader_type',
        'preloader_color',
        'preloader_background_color',
        'preloader_duration',
        'preloader_custom_css',
        'site_logo_dark',
        'logo_width',
        'logo_height',
        'logo_show_text',
        'logo_text',
        'logo_text_color',
        'logo_text_font_size',
        'avg_response_time',
        'enable_captcha',
        'captcha_site_key',
        'captcha_secret_key',
        'enable_human_question',
        'human_questions',
        'version',
        'site_keywords',
        'maintenance_message',
        'license_verification_enabled',
        'license_auto_verification',
        'license_verification_interval',
        'max_license_domains',
        'license_expiry_warning_days',
        'auto_renewal_enabled',
        'renewal_reminder_days',
        'seo_og_title',
        'seo_og_description',
        'seo_og_type',
        'seo_og_site_name',
        'seo_twitter_card',
        'seo_twitter_site',
        'seo_twitter_creator',
        'analytics_google_analytics',
        'analytics_google_tag_manager',
        'analytics_facebook_pixel',
        'social_facebook',
        'social_twitter',
        'social_instagram',
        'social_linkedin',
        'social_youtube',
        'social_github',
        'contact_phone',
        'contact_address',
        'contact_city',
        'contact_state',
        'contact_country',
        'contact_postal_code',
    ];
    protected $casts = [
        'envato_auth_enabled' => 'boolean',
        'auto_generate_license' => 'boolean',
        'maintenance_mode' => 'boolean',
        'envato_oauth_enabled' => 'boolean',
        'preloader_enabled' => 'boolean',
        'logo_show_text' => 'boolean',
        'preloader_duration' => 'integer',
        'logo_width' => 'integer',
        'logo_height' => 'integer',
        'avg_response_time' => 'integer',
        'last_updated_at' => 'datetime',
        // License verification settings
        'license_verify_envato' => 'boolean',
        'license_fallback_internal' => 'boolean',
        'license_cache_verification' => 'boolean',
        'license_cache_duration' => 'integer',
        'license_allow_offline' => 'boolean',
        'license_grace_period' => 'integer',
        // Domain management settings
        'license_allow_localhost' => 'boolean',
        'license_allow_ip_addresses' => 'boolean',
        'license_allow_wildcards' => 'boolean',
        'license_validate_ssl' => 'boolean',
        'license_auto_approve_subdomains' => 'boolean',
        'license_auto_register_domains' => 'boolean',
        'license_max_domains' => 'integer',
        'license_domain_cooldown' => 'integer',
        // License expiration settings
        'license_default_duration' => 'integer',
        'license_support_duration' => 'integer',
        'license_renewal_reminder' => 'integer',
        'license_expiration_grace' => 'integer',
        'license_auto_suspend' => 'boolean',
        'license_allow_expired_verification' => 'boolean',
        // Security settings
        'license_encrypt_data' => 'boolean',
        'license_secure_tokens' => 'boolean',
        'license_validate_signatures' => 'boolean',
        'license_prevent_sharing' => 'boolean',
        'license_detect_suspicious' => 'boolean',
        'license_block_vpn' => 'boolean',
        'license_require_https' => 'boolean',
        // Notification settings
        'license_notify_verification' => 'boolean',
        'license_notify_expiration' => 'boolean',
        'license_notify_domain_change' => 'boolean',
        'license_notify_suspicious' => 'boolean',
        'license_use_slack' => 'boolean',
        // Performance settings
        'license_enable_caching' => 'boolean',
        'license_optimize_queries' => 'boolean',
        'license_use_indexes' => 'boolean',
        'license_compress_responses' => 'boolean',
        'license_batch_size' => 'integer',
        // Testing settings
        'license_allow_test' => 'boolean',
        'license_bypass_testing' => 'boolean',
        'license_mock_envato' => 'boolean',
        'license_generate_fake_data' => 'boolean',
        'human_questions' => 'array',
        'license_verification_enabled' => 'boolean',
        'license_auto_verification' => 'boolean',
        'auto_renewal_enabled' => 'boolean',
    ];
    /**
     * Get setting value by key with caching.
     */
    public static function get($key, $default = null)
    {
        try {
            return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
                $setting = static::first();
                return $setting ? $setting->$key : $default;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // If the settings table doesn't exist (tests or fresh environment), return default
            return $default;
        }
    }
    /**
     * Set setting value by key.
     */
    public static function set($key, $value)
    {
        try {
            $setting = static::firstOrCreate([]);
            $setting->$key = $value;
            $setting->save();
            // Clear cache
            Cache::forget("setting_{$key}");
            return $setting;
        } catch (\Illuminate\Database\QueryException $e) {
            // If DB is not migrated yet, return a new Setting instance with the key set (not persisted)
            $s = new static();
            $s->$key = $value;
            return $s;
        }
    }
    /**
     * Get all settings as array.
     */
    public static function allSettings()
    {
        try {
            return Cache::remember('all_settings', 3600, function () {
                return static::first() ?? new static();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return new static();
        }
    }
    /**
     * Clear all settings cache.
     */
    public static function clearCache()
    {
        Cache::forget('all_settings');
        // Clear individual caches
        $setting = static::first();
        if ($setting) {
            foreach ($setting->getFillable() as $key) {
                Cache::forget("setting_{$key}");
            }
        }
    }
}
