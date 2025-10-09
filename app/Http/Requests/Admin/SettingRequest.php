<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Setting Request with enhanced security.
 *
 * This unified request class handles validation for both updating system settings
 * and testing API connections with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both update and test operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - File upload validation with security checks
 * - API token validation and testing
 */
class SettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && $user && ($user->is_admin || $user->hasRole('admin'));
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $route = $this->route();
        $isTest = $this->isMethod('POST') && $route && str_contains($route->getName() ?? '', 'test');
        // Test API validation
        if ($isTest) {
            return [
                'token' => [
                    'required',
                    'string',
                    'min:32',
                    'max:255',
                    'regex:/^[a-zA-Z0-9]+$/',
                ],
            ];
        }
        // Update settings validation
        return [
            'site_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'site_description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'support_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'admin_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'timezone' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\/_\-]+$/',
            ],
            'currency' => [
                'nullable',
                'string',
                'max:3',
                'regex:/^[A-Z]{3}$/',
            ],
            'date_format' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\s\-_.,!?@#$%&*():YmdHis]+$/',
            ],
            'time_format' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\s\-_.,!?@#$%&*():Hism]+$/',
            ],
            'maintenance_mode' => [
                'boolean',
            ],
            'site_keywords' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'maintenance_message' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'avg_response_time' => [
                'nullable',
                'integer',
                'min:1',
                'max:999',
            ],
            'support_phone' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9\s\-\+\(\)]+$/',
            ],
            'seo_site_title' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_site_description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_kb_title' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_kb_description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_tickets_title' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_tickets_description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'envato_personal_token' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9]+$/',
            ],
            'envato_api_key' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9]+$/',
            ],
            'envato_client_id' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9]+$/',
            ],
            'envato_client_secret' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9]+$/',
            ],
            'envato_redirect_uri' => [
                'nullable',
                'url',
                'max:500',
            ],
            'envato_oauth_enabled' => [
                'boolean',
            ],
            'enable_captcha' => [
                'boolean',
            ],
            'captcha_site_key' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'captcha_secret_key' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'enable_human_question' => [
                'boolean',
            ],
            'human_questions' => [
                'nullable',
                'array',
            ],
            'license_max_attempts' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'license_lockout_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:1440',
            ],
            'preloader_enabled' => [
                'boolean',
            ],
            'preloader_type' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'preloader_color' => [
                'nullable',
                'string',
                'max:7',
                'regex:/^#[a-fA-F0-9]{6}$/',
            ],
            'preloader_background_color' => [
                'nullable',
                'string',
                'max:7',
                'regex:/^#[a-fA-F0-9]{6}$/',
            ],
            'preloader_duration' => [
                'nullable',
            ],
            'preloader_custom_css' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'site_logo_dark' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120', // 5MB
                'dimensions:max_width=2048,max_height=2048',
            ],
            'logo_width' => [
                'nullable',
                'integer',
                'min:10',
                'max:1000',
            ],
            'logo_height' => [
                'nullable',
                'integer',
                'min:10',
                'max:1000',
            ],
            'logo_show_text' => [
                'boolean',
            ],
            'logo_text' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'logo_text_color' => [
                'nullable',
                'string',
                'max:7',
                'regex:/^#[a-fA-F0-9]{6}$/',
            ],
            'logo_text_font_size' => [
                'nullable',
            ],
            'license_verify_envato' => [
                'boolean',
            ],
            'license_fallback_internal' => [
                'boolean',
            ],
            'license_cache_duration' => [
                'nullable',
                'integer',
                'min:1',
                'max:1440',
            ],
            'license_grace_period' => [
                'nullable',
                'integer',
                'min:0',
                'max:30',
            ],
            'license_allow_localhost' => [
                'boolean',
            ],
            'license_allow_wildcards' => [
                'boolean',
            ],
            'license_auto_register_domains' => [
                'boolean',
            ],
            'license_max_domains' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'license_domain_cooldown' => [
                'nullable',
                'integer',
                'min:1',
                'max:168',
            ],
            'license_encrypt_data' => [
                'boolean',
            ],
            'license_secure_tokens' => [
                'boolean',
            ],
            'license_prevent_sharing' => [
                'boolean',
            ],
            'license_detect_suspicious' => [
                'boolean',
            ],
            'license_notification_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'license_notify_expiration' => [
                'boolean',
            ],
            'license_api_token' => [
                'nullable',
                'string',
                'min:32',
                'max:255',
                'regex:/^[a-zA-Z0-9]+$/',
            ],
            'license_api_url' => [
                'nullable',
                'url',
                'max:500',
            ],
            'license_verification_interval' => [
                'nullable',
                'integer',
                'min:1',
                'max:1440', // 24 hours in minutes
            ],
            'max_license_domains' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'license_expiry_warning_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:365',
            ],
            'auto_renewal_enabled' => [
                'boolean',
            ],
            'renewal_reminder_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:30',
            ],
            'site_logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp,svg',
                'max:5120', // 5MB
                'dimensions:max_width=2048,max_height=2048',
            ],
            'favicon' => [
                'nullable',
                'image',
                'mimes:ico,png,jpg,gif',
                'max:1024', // 1MB
                'dimensions:max_width=512,max_height=512',
            ],
            'seo_og_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120', // 5MB
                'dimensions:max_width=1200,max_height=630',
            ],
            'seo_og_title' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_og_description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_og_type' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_og_site_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_twitter_card' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_twitter_site' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'seo_twitter_creator' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'analytics_google_analytics' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'analytics_google_tag_manager' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'analytics_facebook_pixel' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'social_facebook' => [
                'nullable',
                'url',
                'max:500',
            ],
            'social_twitter' => [
                'nullable',
                'url',
                'max:500',
            ],
            'social_instagram' => [
                'nullable',
                'url',
                'max:500',
            ],
            'social_linkedin' => [
                'nullable',
                'url',
                'max:500',
            ],
            'social_youtube' => [
                'nullable',
                'url',
                'max:500',
            ],
            'social_github' => [
                'nullable',
                'url',
                'max:500',
            ],
            'contact_phone' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9\s\-\+\(\)]+$/',
            ],
            'contact_address' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'contact_city' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'contact_state' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'contact_country' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'contact_postal_code' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
        ];
    }
    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'site_name.required' => 'Site name is required.',
            'site_name.regex' => 'Site name contains invalid characters.',
            'site_description.regex' => 'Site description contains invalid characters.',
            'site_keywords.regex' => 'Site keywords contain invalid characters.',
            'support_email.required' => 'Support email is required.',
            'support_email.email' => 'Support email must be a valid email address.',
            'admin_email.required' => 'Admin email is required.',
            'admin_email.email' => 'Admin email must be a valid email address.',
            'timezone.required' => 'Timezone is required.',
            'timezone.regex' => 'Timezone contains invalid characters.',
            'currency.required' => 'Currency is required.',
            'currency.regex' => 'Currency must be a 3-letter code (e.g., USD).',
            'date_format.required' => 'Date format is required.',
            'date_format.regex' => 'Date format contains invalid characters.',
            'time_format.required' => 'Time format is required.',
            'time_format.regex' => 'Time format contains invalid characters.',
            'maintenance_message.regex' => 'Maintenance message contains invalid characters.',
            'license_api_token.regex' => 'License API token can only contain letters and numbers.',
            'license_api_url.url' => 'License API URL must be a valid URL.',
            'license_verification_interval.min' => 'Verification interval must be at least 1 minute.',
            'license_verification_interval.max' => 'Verification interval cannot exceed 1440 minutes (24 hours).',
            'max_license_domains.min' => 'Maximum license domains must be at least 1.',
            'max_license_domains.max' => 'Maximum license domains cannot exceed 100.',
            'license_expiry_warning_days.min' => 'Expiry warning days must be at least 1.',
            'license_expiry_warning_days.max' => 'Expiry warning days cannot exceed 365.',
            'renewal_reminder_days.min' => 'Renewal reminder days must be at least 1.',
            'renewal_reminder_days.max' => 'Renewal reminder days cannot exceed 30.',
            'site_logo.dimensions' => 'Site logo dimensions must not exceed 2048x2048 pixels.',
            'site_logo.max' => 'Site logo size must not exceed 5MB.',
            'site_logo.mimes' => 'Site logo must be a file of type: jpeg, png, jpg, gif, webp, svg.',
            'favicon.dimensions' => 'Favicon dimensions must not exceed 512x512 pixels.',
            'favicon.max' => 'Favicon size must not exceed 1MB.',
            'favicon.mimes' => 'Favicon must be a file of type: ico, png, jpg, gif.',
            'seo_og_image.dimensions' => 'SEO OG image dimensions must not exceed 1200x630 pixels.',
            'seo_og_image.max' => 'SEO OG image size must not exceed 5MB.',
            'seo_og_image.mimes' => 'SEO OG image must be a file of type: jpeg, png, jpg, gif, webp.',
            'seo_og_title.regex' => 'SEO OG title contains invalid characters.',
            'seo_og_description.regex' => 'SEO OG description contains invalid characters.',
            'seo_og_type.regex' => 'SEO OG type contains invalid characters.',
            'seo_og_site_name.regex' => 'SEO OG site name contains invalid characters.',
            'seo_twitter_card.regex' => 'SEO Twitter card contains invalid characters.',
            'seo_twitter_site.regex' => 'SEO Twitter site contains invalid characters.',
            'seo_twitter_creator.regex' => 'SEO Twitter creator contains invalid characters.',
            'analytics_google_analytics.regex' => 'Google Analytics ID contains invalid characters.',
            'analytics_google_tag_manager.regex' => 'Google Tag Manager ID contains invalid characters.',
            'analytics_facebook_pixel.regex' => 'Facebook Pixel ID contains invalid characters.',
            'social_facebook.url' => 'Facebook URL must be a valid URL.',
            'social_twitter.url' => 'Twitter URL must be a valid URL.',
            'social_instagram.url' => 'Instagram URL must be a valid URL.',
            'social_linkedin.url' => 'LinkedIn URL must be a valid URL.',
            'social_youtube.url' => 'YouTube URL must be a valid URL.',
            'social_github.url' => 'GitHub URL must be a valid URL.',
            'contact_phone.regex' => 'Contact phone contains invalid characters.',
            'contact_address.regex' => 'Contact address contains invalid characters.',
            'contact_city.regex' => 'Contact city contains invalid characters.',
            'contact_state.regex' => 'Contact state contains invalid characters.',
            'contact_country.regex' => 'Contact country contains invalid characters.',
            'contact_postal_code.regex' => 'Contact postal code contains invalid characters.',
            'token.required' => 'API token is required for testing.',
            'token.min' => 'API token must be at least 32 characters.',
            'token.regex' => 'API token can only contain letters and numbers.',
        ];
    }
    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'site_name' => 'site name',
            'site_description' => 'site description',
            'site_keywords' => 'site keywords',
            'support_email' => 'support email',
            'admin_email' => 'admin email',
            'timezone' => 'timezone',
            'currency' => 'currency',
            'date_format' => 'date format',
            'time_format' => 'time format',
            'maintenance_mode' => 'maintenance mode',
            'maintenance_message' => 'maintenance message',
            'license_api_token' => 'license API token',
            'license_api_url' => 'license API URL',
            'license_verification_enabled' => 'license verification enabled',
            'license_auto_verification' => 'license auto verification',
            'license_verification_interval' => 'license verification interval',
            'max_license_domains' => 'maximum license domains',
            'license_expiry_warning_days' => 'license expiry warning days',
            'auto_renewal_enabled' => 'auto renewal enabled',
            'renewal_reminder_days' => 'renewal reminder days',
            'site_logo' => 'site logo',
            'favicon' => 'favicon',
            'seo_og_image' => 'SEO OG image',
            'seo_og_title' => 'SEO OG title',
            'seo_og_description' => 'SEO OG description',
            'seo_og_type' => 'SEO OG type',
            'seo_og_site_name' => 'SEO OG site name',
            'seo_twitter_card' => 'SEO Twitter card',
            'seo_twitter_site' => 'SEO Twitter site',
            'seo_twitter_creator' => 'SEO Twitter creator',
            'analytics_google_analytics' => 'Google Analytics ID',
            'analytics_google_tag_manager' => 'Google Tag Manager ID',
            'analytics_facebook_pixel' => 'Facebook Pixel ID',
            'social_facebook' => 'Facebook URL',
            'social_twitter' => 'Twitter URL',
            'social_instagram' => 'Instagram URL',
            'social_linkedin' => 'LinkedIn URL',
            'social_youtube' => 'YouTube URL',
            'social_github' => 'GitHub URL',
            'contact_phone' => 'contact phone',
            'contact_address' => 'contact address',
            'contact_city' => 'contact city',
            'contact_state' => 'contact state',
            'contact_country' => 'contact country',
            'contact_postal_code' => 'contact postal code',
            'token' => 'API token',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'site_name' => $this->sanitizeInput($this->input('site_name')),
            'site_description' => $this->input('site_description')
                ? $this->sanitizeInput($this->input('site_description'))
                : null,
            'site_keywords' => $this->input('site_keywords')
                ? $this->sanitizeInput($this->input('site_keywords'))
                : null,
            'maintenance_message' => $this->input('maintenance_message')
                ? $this->sanitizeInput($this->input('maintenance_message'))
                : null,
            'seo_og_title' => $this->input('seo_og_title')
                ? $this->sanitizeInput($this->input('seo_og_title'))
                : null,
            'seo_og_description' => $this->input('seo_og_description')
                ? $this->sanitizeInput($this->input('seo_og_description'))
                : null,
            'seo_og_type' => $this->input('seo_og_type')
                ? $this->sanitizeInput($this->input('seo_og_type'))
                : null,
            'seo_og_site_name' => $this->input('seo_og_site_name')
                ? $this->sanitizeInput($this->input('seo_og_site_name'))
                : null,
            'seo_twitter_card' => $this->input('seo_twitter_card')
                ? $this->sanitizeInput($this->input('seo_twitter_card'))
                : null,
            'seo_twitter_site' => $this->input('seo_twitter_site')
                ? $this->sanitizeInput($this->input('seo_twitter_site'))
                : null,
            'seo_twitter_creator' => $this->input('seo_twitter_creator')
                ? $this->sanitizeInput($this->input('seo_twitter_creator'))
                : null,
            'analytics_google_analytics' => $this->input('analytics_google_analytics')
                ? $this->sanitizeInput($this->input('analytics_google_analytics'))
                : null,
            'analytics_google_tag_manager' => $this->input('analytics_google_tag_manager')
                ? $this->sanitizeInput($this->input('analytics_google_tag_manager'))
                : null,
            'analytics_facebook_pixel' => $this->input('analytics_facebook_pixel')
                ? $this->sanitizeInput($this->input('analytics_facebook_pixel'))
                : null,
            'contact_phone' => $this->input('contact_phone')
                ? $this->sanitizeInput($this->input('contact_phone'))
                : null,
            'contact_address' => $this->input('contact_address')
                ? $this->sanitizeInput($this->input('contact_address'))
                : null,
            'contact_city' => $this->input('contact_city')
                ? $this->sanitizeInput($this->input('contact_city'))
                : null,
            'contact_state' => $this->input('contact_state')
                ? $this->sanitizeInput($this->input('contact_state'))
                : null,
            'contact_country' => $this->input('contact_country')
                ? $this->sanitizeInput($this->input('contact_country'))
                : null,
            'contact_postal_code' => $this->input('contact_postal_code')
                ? $this->sanitizeInput($this->input('contact_postal_code'))
                : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'maintenance_mode' => $this->has('maintenance_mode'),
            'license_verification_enabled' => $this->has('license_verification_enabled'),
            'license_auto_verification' => $this->has('license_auto_verification'),
            'auto_renewal_enabled' => $this->has('auto_renewal_enabled'),
        ]);
        // Set default values
        $this->merge([
            'currency' => $this->currency ?? 'USD',
            'timezone' => $this->timezone ?? 'UTC',
            'date_format' => $this->date_format ?? 'Y-m-d',
            'time_format' => $this->time_format ?? 'H:i:s',
        ]);
    }
    /**
     * Sanitize input to prevent XSS attacks.
     *
     * @param  mixed  $input  The input to sanitize
     *
     * @return string|null The sanitized input
     */
    private function sanitizeInput(mixed $input): ?string
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (!is_string($input)) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
