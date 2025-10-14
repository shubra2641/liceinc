<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            
            // Site settings
            $table->string('site_name')->nullable();
            $table->string('site_logo')->nullable();
            $table->string('site_favicon')->nullable();
            $table->text('site_description')->nullable();
            $table->string('site_keywords')->nullable();
            $table->string('site_author')->nullable();
            $table->string('site_language')->default('en');
            $table->string('site_timezone')->default('UTC');
            $table->string('site_currency')->default('USD');
            $table->string('site_email')->nullable();
            $table->string('site_phone')->nullable();
            $table->string('site_address')->nullable();
            $table->string('site_city')->nullable();
            $table->string('site_country')->nullable();
            $table->string('site_postal_code')->nullable();
            
            // SEO settings
            $table->string('seo_site_title')->nullable();
            $table->text('seo_site_description')->nullable();
            $table->string('seo_og_image')->nullable();
            $table->string('seo_twitter_card')->nullable();
            $table->string('seo_twitter_site')->nullable();
            $table->string('seo_google_analytics')->nullable();
            $table->string('seo_google_tag_manager')->nullable();
            $table->string('seo_facebook_pixel')->nullable();
            
            // Logo settings
            $table->string('logo_text')->nullable();
            $table->boolean('logo_show_text')->default(true);
            $table->string('logo_position')->default('left');
            $table->string('logo_size')->default('medium');
            
            // Preloader settings
            $table->boolean('preloader_enabled')->default(true);
            $table->string('preloader_type')->default('spinner');
            $table->string('preloader_color')->default('#3b82f6');
            $table->string('preloader_background_color')->default('#ffffff');
            $table->integer('preloader_duration')->default(2000);
            $table->integer('preloader_min_duration')->default(0);
            
            // License settings
            $table->string('license_api_token')->nullable();
            $table->boolean('license_auto_register_domains')->default(false);
            $table->integer('license_max_domains')->default(1);
            $table->integer('license_verification_timeout')->default(30);
            $table->boolean('license_strict_verification')->default(true);
            $table->json('license_allowed_domains')->nullable();
            $table->json('license_blocked_domains')->nullable();
            
            // Envato settings
            $table->string('envato_username')->nullable();
            $table->string('envato_personal_token')->nullable();
            $table->string('envato_client_id')->nullable();
            $table->string('envato_client_secret')->nullable();
            $table->string('envato_redirect_uri')->nullable();
            $table->string('envato_access_token')->nullable();
            $table->string('envato_refresh_token')->nullable();
            $table->timestamp('envato_token_expires_at')->nullable();
            
            // Email settings
            $table->string('mail_driver')->default('smtp');
            $table->string('mail_host')->nullable();
            $table->integer('mail_port')->nullable();
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_encryption')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            
            // Payment settings
            $table->string('payment_gateway')->default('stripe');
            $table->string('stripe_public_key')->nullable();
            $table->string('stripe_secret_key')->nullable();
            $table->string('stripe_webhook_secret')->nullable();
            $table->string('paypal_client_id')->nullable();
            $table->string('paypal_client_secret')->nullable();
            $table->string('paypal_webhook_id')->nullable();
            $table->boolean('payment_test_mode')->default(true);
            
            // Security settings
            $table->boolean('security_2fa_enabled')->default(false);
            $table->boolean('security_ip_whitelist_enabled')->default(false);
            $table->json('security_ip_whitelist')->nullable();
            $table->boolean('security_rate_limiting_enabled')->default(true);
            $table->integer('security_rate_limit_requests')->default(100);
            $table->integer('security_rate_limit_window')->default(60);
            $table->boolean('security_captcha_enabled')->default(false);
            $table->string('security_captcha_site_key')->nullable();
            $table->string('security_captcha_secret_key')->nullable();
            
            // Antispam settings
            $table->boolean('antispam_enabled')->default(true);
            $table->integer('antispam_max_attempts')->default(5);
            $table->integer('antispam_lockout_duration')->default(900);
            $table->json('antispam_blocked_ips')->nullable();
            $table->json('antispam_blocked_emails')->nullable();
            $table->json('antispam_blocked_domains')->nullable();
            
            // Performance settings
            $table->integer('avg_response_time')->default(200);
            $table->boolean('cache_enabled')->default(true);
            $table->integer('cache_duration')->default(3600);
            $table->boolean('compression_enabled')->default(true);
            $table->boolean('cdn_enabled')->default(false);
            $table->string('cdn_url')->nullable();
            
            // System settings
            $table->string('app_version')->default('1.0.0');
            $table->string('app_environment')->default('production');
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            $table->boolean('debug_mode')->default(false);
            $table->boolean('log_enabled')->default(true);
            $table->string('log_level')->default('info');
            
            // Indexes
            $table->index(['key', 'is_public']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};