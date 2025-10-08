<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id * @property string $version * @property \Illuminate\Support\Carbon|null $last_updated_at * @property string|null $key * @property string|null $value * @property string $type * @property string $site_name * @property string|null $site_logo * @property string|null $site_logo_dark * @property int $logo_width * @property int $logo_height * @property bool $logo_show_text * @property string|null $logo_text * @property string $logo_text_color * @property string $logo_text_font_size * @property string|null $support_email * @property string|null $admin_email * @property int|null $avg_response_time Average response time in hours * @property string|null $support_phone * @property string|null $site_description * @property string|null $site_keywords * @property string $timezone * @property bool $maintenance_mode * @property string|null $maintenance_message * @property string|null $envato_personal_token * @property string|null $envato_api_key * @property bool $envato_auth_enabled * @property string|null $envato_username * @property string|null $envato_client_id * @property string|null $envato_client_secret * @property string|null $envato_redirect_uri * @property bool $envato_oauth_enabled * @property string $payment_gateway * @property string $currency * @property string|null $date_format * @property string|null $time_format * @property int $test_mode * @property string|null $license_api_token * @property bool $license_verification_enabled * @property bool $license_auto_verification * @property int|null $license_verification_interval * @property int|null $max_license_domains * @property int|null $license_expiry_warning_days * @property bool $auto_renewal_enabled * @property int|null $renewal_reminder_days * @property int $enable_captcha * @property string|null $captcha_site_key * @property string|null $captcha_secret_key * @property int $enable_human_question * @property array<array-key, mixed>|null $human_questions * @property bool $auto_generate_license * @property int $default_license_length * @property int $license_max_attempts * @property int $license_lockout_minutes * @property bool $license_verify_envato * @property bool $license_fallback_internal * @property bool $license_cache_verification * @property int $license_cache_duration * @property bool $license_allow_offline * @property int $license_grace_period * @property bool $license_allow_localhost * @property bool $license_allow_ip_addresses * @property bool $license_allow_wildcards * @property bool $license_validate_ssl * @property bool $license_auto_approve_subdomains * @property bool $license_auto_register_domains * @property int $license_max_domains * @property int $license_domain_cooldown * @property int $license_default_duration * @property int $license_support_duration * @property int $license_renewal_reminder * @property int $license_expiration_grace * @property bool $license_auto_suspend * @property bool $license_allow_expired_verification * @property bool $license_encrypt_data * @property bool $license_secure_tokens * @property bool $license_validate_signatures * @property bool $license_prevent_sharing * @property bool $license_detect_suspicious * @property bool $license_block_vpn * @property bool $license_require_https * @property bool $license_notify_verification * @property bool $license_notify_expiration * @property bool $license_notify_domain_change * @property bool $license_notify_suspicious * @property string|null $license_notification_email * @property bool $license_use_slack * @property string|null $license_slack_webhook * @property bool $license_enable_caching * @property string $license_cache_driver * @property bool $license_optimize_queries * @property int $license_batch_size * @property bool $license_use_indexes * @property bool $license_compress_responses * @property bool $license_allow_test * @property string $license_test_prefix * @property bool $license_bypass_testing * @property bool $license_mock_envato * @property bool $license_generate_fake_data * @property bool $preloader_enabled * @property string $preloader_type * @property string $preloader_color * @property string $preloader_background_color * @property int $preloader_duration * @property string|null $preloader_custom_css * @property \Illuminate\Support\Carbon|null $created_at * @property \Illuminate\Support\Carbon|null $updated_at * @property string|null $seo_site_title * @property string|null $seo_site_description * @property string|null $seo_og_image * @property string|null $seo_og_title * @property string|null $seo_og_description * @property string|null $seo_og_type * @property string|null $seo_og_site_name * @property string|null $seo_twitter_card * @property string|null $seo_twitter_site * @property string|null $seo_twitter_creator * @property string|null $analytics_google_analytics * @property string|null $analytics_google_tag_manager * @property string|null $analytics_facebook_pixel * @property string|null $social_facebook * @property string|null $social_twitter * @property string|null $social_instagram * @property string|null $social_linkedin * @property string|null $social_youtube * @property string|null $social_github * @property string|null $contact_phone * @property string|null $contact_address * @property string|null $contact_city * @property string|null $contact_state * @property string|null $contact_country * @property string|null $contact_postal_code * @property string|null $seo_kb_title * @property string|null $seo_kb_description * @property string|null $seo_tickets_title * @property string|null $seo_tickets_description * @method static \Database\Factories\SettingFactory factory($count = null, $state = []) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery() * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery() * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query() * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAdminEmail($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAnalyticsFacebookPixel($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAnalyticsGoogleAnalytics($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAnalyticsGoogleTagManager($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAutoGenerateLicense($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAutoRenewalEnabled($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAvgResponseTime($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCaptchaSecretKey($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCaptchaSiteKey($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereContactAddress($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereContactCity($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereContactCountry($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereContactPhone($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereContactPostalCode($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereContactState($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCurrency($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDateFormat($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDefaultLicenseLength($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEnableCaptcha($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEnableHumanQuestion($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEnvatoApiKey($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEnvatoAuthEnabled($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEnvatoClientId($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEnvatoClientSecret($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEnvatoOauthEnabled($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEnvatoPersonalToken($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEnvatoRedirectUri($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEnvatoUsername($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereHumanQuestions($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLastUpdatedAt($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseAllowExpiredVerification($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseAllowIpAddresses($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseAllowLocalhost($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseAllowOffline($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseAllowTest($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseAllowWildcards($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseApiToken($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseAutoApproveSubdomains($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseAutoRegisterDomains($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseAutoSuspend($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseAutoVerification($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseBatchSize($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseBlockVpn($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseBypassTesting($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseCacheDriver($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseCacheDuration($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseCacheVerification($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseCompressResponses($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseDefaultDuration($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseDetectSuspicious($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseDomainCooldown($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseEnableCaching($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseEncryptData($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseExpirationGrace($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseExpiryWarningDays($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseFallbackInternal($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseGenerateFakeData($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseGracePeriod($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseLockoutMinutes($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseMaxAttempts($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseMaxDomains($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseMockEnvato($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseNotificationEmail($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseNotifyDomainChange($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseNotifyExpiration($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseNotifySuspicious($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseNotifyVerification($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseOptimizeQueries($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicensePreventSharing($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseRenewalReminder($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseRequireHttps($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseSecureTokens($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseSlackWebhook($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseSupportDuration($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseTestPrefix($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseUseIndexes($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseUseSlack($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseValidateSignatures($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseValidateSsl($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseVerificationEnabled($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseVerificationInterval($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLicenseVerifyEnvato($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLogoHeight($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLogoShowText($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLogoText($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLogoTextColor($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLogoTextFontSize($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLogoWidth($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereMaintenanceMessage($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereMaintenanceMode($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereMaxLicenseDomains($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePaymentGateway($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePreloaderBackgroundColor($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePreloaderColor($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePreloaderCustomCss($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePreloaderDuration($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePreloaderEnabled($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePreloaderType($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereRenewalReminderDays($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoKbDescription($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoKbTitle($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoOgDescription($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoOgImage($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoOgSiteName($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoOgTitle($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoOgType($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoSiteDescription($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoSiteTitle($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoTicketsDescription($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoTicketsTitle($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoTwitterCard($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoTwitterCreator($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSeoTwitterSite($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSiteDescription($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSiteKeywords($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSiteLogo($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSiteLogoDark($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSiteName($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSocialFacebook($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSocialGithub($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSocialInstagram($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSocialLinkedin($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSocialTwitter($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSocialYoutube($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSupportEmail($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSupportPhone($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTestMode($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTimeFormat($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTimezone($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereType($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereVersion($value) * @mixin \Eloquent */
class Setting extends Model
{
    /**   * @phpstan-ignore-next-line */
    use HasFactory;

    /**   * @phpstan-ignore-next-line */
    protected static $factory = SettingFactory::class;

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
    /**   * Get setting value by key with caching. */
    public static function get(string $key, mixed $default = null): mixed
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
    /**   * Set setting value by key. */
    public static function set(string $key, mixed $value): self
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
            $s = new self();
            $s->$key = $value;
            return $s;
        }
    }
    /**   * Get all settings as array. */
    public static function allSettings(): self
    {
        try {
            $result = Cache::remember('all_settings', 3600, function () {
                return static::first() ?? new self();
            });
            return $result instanceof self ? $result : new self();
        } catch (\Illuminate\Database\QueryException $e) {
            return new self();
        }
    }
    /**   * Clear all settings cache. */
    public static function clearCache(): void
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
