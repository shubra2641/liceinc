<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $this->addBasicSettingsFields($table);
            $this->addLicenseSettingsFields($table);
            $this->addSeoSettingsFields($table);
            $this->addAnalyticsSettingsFields($table);
            $this->addSocialSettingsFields($table);
            $this->addContactSettingsFields($table);
        });
    }

    /**
     * Add basic settings fields.
     */
    private function addBasicSettingsFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'site_keywords', 'text', 'site_description');
        $this->addColumnIfNotExists($table, 'maintenance_message', 'text', 'maintenance_mode');
    }

    /**
     * Add license-related settings fields.
     */
    private function addLicenseSettingsFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'license_verification_enabled', 'boolean', 'license_api_token', ['default' => false]);
        $this->addColumnIfNotExists($table, 'license_auto_verification', 'boolean', 'license_verification_enabled', ['default' => false]);
        $this->addColumnIfNotExists($table, 'license_verification_interval', 'integer', 'license_auto_verification');
        $this->addColumnIfNotExists($table, 'max_license_domains', 'integer', 'license_verification_interval');
        $this->addColumnIfNotExists($table, 'license_expiry_warning_days', 'integer', 'max_license_domains');
        $this->addColumnIfNotExists($table, 'auto_renewal_enabled', 'boolean', 'license_expiry_warning_days', ['default' => false]);
        $this->addColumnIfNotExists($table, 'renewal_reminder_days', 'integer', 'auto_renewal_enabled');
    }

    /**
     * Add SEO settings fields.
     */
    private function addSeoSettingsFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'seo_og_title', 'string', 'seo_og_image');
        $this->addColumnIfNotExists($table, 'seo_og_description', 'text', 'seo_og_title');
        $this->addColumnIfNotExists($table, 'seo_og_type', 'string', 'seo_og_description');
        $this->addColumnIfNotExists($table, 'seo_og_site_name', 'string', 'seo_og_type');
        $this->addColumnIfNotExists($table, 'seo_twitter_card', 'string', 'seo_og_site_name');
        $this->addColumnIfNotExists($table, 'seo_twitter_site', 'string', 'seo_twitter_card');
        $this->addColumnIfNotExists($table, 'seo_twitter_creator', 'string', 'seo_twitter_site');
    }

    /**
     * Add analytics settings fields.
     */
    private function addAnalyticsSettingsFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'analytics_google_analytics', 'string', 'seo_twitter_creator');
        $this->addColumnIfNotExists($table, 'analytics_google_tag_manager', 'string', 'analytics_google_analytics');
        $this->addColumnIfNotExists($table, 'analytics_facebook_pixel', 'string', 'analytics_google_tag_manager');
    }

    /**
     * Add social media settings fields.
     */
    private function addSocialSettingsFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'social_facebook', 'string', 'analytics_facebook_pixel');
        $this->addColumnIfNotExists($table, 'social_twitter', 'string', 'social_facebook');
        $this->addColumnIfNotExists($table, 'social_instagram', 'string', 'social_twitter');
        $this->addColumnIfNotExists($table, 'social_linkedin', 'string', 'social_instagram');
        $this->addColumnIfNotExists($table, 'social_youtube', 'string', 'social_linkedin');
        $this->addColumnIfNotExists($table, 'social_github', 'string', 'social_youtube');
    }

    /**
     * Add contact settings fields.
     */
    private function addContactSettingsFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'contact_phone', 'string', 'social_github');
        $this->addColumnIfNotExists($table, 'contact_address', 'text', 'contact_phone');
        $this->addColumnIfNotExists($table, 'contact_city', 'string', 'contact_address');
        $this->addColumnIfNotExists($table, 'contact_state', 'string', 'contact_city');
        $this->addColumnIfNotExists($table, 'contact_country', 'string', 'contact_state');
        $this->addColumnIfNotExists($table, 'contact_postal_code', 'string', 'contact_country');
    }

    /**
     * Helper method to add column if it doesn't exist.
     */
    private function addColumnIfNotExists(Blueprint $table, string $column, string $type, string $after, array $options = []): void
    {
        if (Schema::hasColumn('settings', $column)) {
            return;
        }

        $columnDefinition = $table->{$type}($column);
        $columnDefinition->nullable();

        if (isset($options['default'])) {
            $columnDefinition->default($options['default']);
        }

        $columnDefinition->after($after);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $this->dropAddedColumns($table);
        });
    }

    /**
     * Drop all added columns.
     */
    private function dropAddedColumns(Blueprint $table): void
    {
        $columnsToDrop = [
            // Basic Settings
            'site_keywords', 'maintenance_message',

            // License Settings
            'license_verification_enabled', 'license_auto_verification', 'license_verification_interval',
            'max_license_domains', 'license_expiry_warning_days', 'auto_renewal_enabled', 'renewal_reminder_days',

            // SEO Settings
            'seo_og_title', 'seo_og_description', 'seo_og_type', 'seo_og_site_name',
            'seo_twitter_card', 'seo_twitter_site', 'seo_twitter_creator',

            // Analytics Settings
            'analytics_google_analytics', 'analytics_google_tag_manager', 'analytics_facebook_pixel',

            // Social Settings
            'social_facebook', 'social_twitter', 'social_instagram', 'social_linkedin',
            'social_youtube', 'social_github',

            // Contact Settings
            'contact_phone', 'contact_address', 'contact_city', 'contact_state',
            'contact_country', 'contact_postal_code',
        ];

        $table->dropColumn($columnsToDrop);
    }
};
