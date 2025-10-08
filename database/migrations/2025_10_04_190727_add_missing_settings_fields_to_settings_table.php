<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Add missing fields that are used in the form
            if (! Schema::hasColumn('settings', 'site_keywords')) {
                $table->text('site_keywords')->nullable()->after('site_description');
            }
            if (! Schema::hasColumn('settings', 'maintenance_message')) {
                $table->text('maintenance_message')->nullable()->after('maintenance_mode');
            }
            if (! Schema::hasColumn('settings', 'license_verification_enabled')) {
                $table->boolean('license_verification_enabled')->default(false)->after('license_api_token');
            }
            if (! Schema::hasColumn('settings', 'license_auto_verification')) {
                $table->boolean('license_auto_verification')->default(false)->after('license_verification_enabled');
            }
            if (! Schema::hasColumn('settings', 'license_verification_interval')) {
                $table->integer('license_verification_interval')->nullable()->after('license_auto_verification');
            }
            if (! Schema::hasColumn('settings', 'max_license_domains')) {
                $table->integer('max_license_domains')->nullable()->after('license_verification_interval');
            }
            if (! Schema::hasColumn('settings', 'license_expiry_warning_days')) {
                $table->integer('license_expiry_warning_days')->nullable()->after('max_license_domains');
            }
            if (! Schema::hasColumn('settings', 'auto_renewal_enabled')) {
                $table->boolean('auto_renewal_enabled')->default(false)->after('license_expiry_warning_days');
            }
            if (! Schema::hasColumn('settings', 'renewal_reminder_days')) {
                $table->integer('renewal_reminder_days')->nullable()->after('auto_renewal_enabled');
            }
            if (! Schema::hasColumn('settings', 'seo_og_title')) {
                $table->string('seo_og_title')->nullable()->after('seo_og_image');
            }
            if (! Schema::hasColumn('settings', 'seo_og_description')) {
                $table->text('seo_og_description')->nullable()->after('seo_og_title');
            }
            if (! Schema::hasColumn('settings', 'seo_og_type')) {
                $table->string('seo_og_type')->nullable()->after('seo_og_description');
            }
            if (! Schema::hasColumn('settings', 'seo_og_site_name')) {
                $table->string('seo_og_site_name')->nullable()->after('seo_og_type');
            }
            if (! Schema::hasColumn('settings', 'seo_twitter_card')) {
                $table->string('seo_twitter_card')->nullable()->after('seo_og_site_name');
            }
            if (! Schema::hasColumn('settings', 'seo_twitter_site')) {
                $table->string('seo_twitter_site')->nullable()->after('seo_twitter_card');
            }
            if (! Schema::hasColumn('settings', 'seo_twitter_creator')) {
                $table->string('seo_twitter_creator')->nullable()->after('seo_twitter_site');
            }
            if (! Schema::hasColumn('settings', 'analytics_google_analytics')) {
                $table->string('analytics_google_analytics')->nullable()->after('seo_twitter_creator');
            }
            if (! Schema::hasColumn('settings', 'analytics_google_tag_manager')) {
                $table->string('analytics_google_tag_manager')->nullable()->after('analytics_google_analytics');
            }
            if (! Schema::hasColumn('settings', 'analytics_facebook_pixel')) {
                $table->string('analytics_facebook_pixel')->nullable()->after('analytics_google_tag_manager');
            }
            if (! Schema::hasColumn('settings', 'social_facebook')) {
                $table->string('social_facebook')->nullable()->after('analytics_facebook_pixel');
            }
            if (! Schema::hasColumn('settings', 'social_twitter')) {
                $table->string('social_twitter')->nullable()->after('social_facebook');
            }
            if (! Schema::hasColumn('settings', 'social_instagram')) {
                $table->string('social_instagram')->nullable()->after('social_twitter');
            }
            if (! Schema::hasColumn('settings', 'social_linkedin')) {
                $table->string('social_linkedin')->nullable()->after('social_instagram');
            }
            if (! Schema::hasColumn('settings', 'social_youtube')) {
                $table->string('social_youtube')->nullable()->after('social_linkedin');
            }
            if (! Schema::hasColumn('settings', 'social_github')) {
                $table->string('social_github')->nullable()->after('social_youtube');
            }
            if (! Schema::hasColumn('settings', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('social_github');
            }
            if (! Schema::hasColumn('settings', 'contact_address')) {
                $table->text('contact_address')->nullable()->after('contact_phone');
            }
            if (! Schema::hasColumn('settings', 'contact_city')) {
                $table->string('contact_city')->nullable()->after('contact_address');
            }
            if (! Schema::hasColumn('settings', 'contact_state')) {
                $table->string('contact_state')->nullable()->after('contact_city');
            }
            if (! Schema::hasColumn('settings', 'contact_country')) {
                $table->string('contact_country')->nullable()->after('contact_state');
            }
            if (! Schema::hasColumn('settings', 'contact_postal_code')) {
                $table->string('contact_postal_code')->nullable()->after('contact_country');
            }
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
