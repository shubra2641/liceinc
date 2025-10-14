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
            // License verification settings
            $table->boolean('license_verify_envato')->default(true)->after('license_lockout_minutes');
            $table->boolean('license_fallback_internal')->default(true)->after('license_verify_envato');
            $table->boolean('license_cache_verification')->default(true)->after('license_fallback_internal');
            $table->integer('license_cache_duration')->default(60)->after('license_cache_verification');
            $table->boolean('license_allow_offline')->default(false)->after('license_cache_duration');
            $table->integer('license_grace_period')->default(7)->after('license_allow_offline');

            // Domain management settings
            $table->boolean('license_allow_localhost')->default(true)->after('license_grace_period');
            $table->boolean('license_allow_ip_addresses')->default(false)->after('license_allow_localhost');
            $table->boolean('license_allow_wildcards')->default(true)->after('license_allow_ip_addresses');
            $table->boolean('license_validate_ssl')->default(false)->after('license_allow_wildcards');
            $table->boolean('license_auto_approve_subdomains')->default(false)->after('license_validate_ssl');
            $table->boolean('license_auto_register_domains')->default(false)->after('license_auto_approve_subdomains');
            $table->integer('license_max_domains')->default(5)->after('license_auto_register_domains');
            $table->integer('license_domain_cooldown')->default(24)->after('license_max_domains');

            // License expiration settings
            $table->integer('license_default_duration')->default(365)->after('license_domain_cooldown');
            $table->integer('license_support_duration')->default(365)->after('license_default_duration');
            $table->integer('license_renewal_reminder')->default(30)->after('license_support_duration');
            $table->integer('license_expiration_grace')->default(7)->after('license_renewal_reminder');
            $table->boolean('license_auto_suspend')->default(true)->after('license_expiration_grace');
            $table->boolean('license_allow_expired_verification')->default(false)->after('license_auto_suspend');

            // Security settings
            $table->boolean('license_encrypt_data')->default(true)->after('license_allow_expired_verification');
            $table->boolean('license_secure_tokens')->default(true)->after('license_encrypt_data');
            $table->boolean('license_validate_signatures')->default(false)->after('license_secure_tokens');
            $table->boolean('license_prevent_sharing')->default(true)->after('license_validate_signatures');
            $table->boolean('license_detect_suspicious')->default(true)->after('license_prevent_sharing');
            $table->boolean('license_block_vpn')->default(false)->after('license_detect_suspicious');
            $table->boolean('license_require_https')->default(true)->after('license_block_vpn');

            // Notification settings
            $table->boolean('license_notify_verification')->default(false)->after('license_require_https');
            $table->boolean('license_notify_expiration')->default(true)->after('license_notify_verification');
            $table->boolean('license_notify_domain_change')->default(true)->after('license_notify_expiration');
            $table->boolean('license_notify_suspicious')->default(true)->after('license_notify_domain_change');
            $table->string('license_notification_email')->nullable()->after('license_notify_suspicious');
            $table->boolean('license_use_slack')->default(false)->after('license_notification_email');
            $table->string('license_slack_webhook')->nullable()->after('license_use_slack');

            // Performance settings
            $table->boolean('license_enable_caching')->default(true)->after('license_slack_webhook');
            $table->string('license_cache_driver')->default('redis')->after('license_enable_caching');
            $table->boolean('license_optimize_queries')->default(true)->after('license_cache_driver');
            $table->integer('license_batch_size')->default(100)->after('license_optimize_queries');
            $table->boolean('license_use_indexes')->default(true)->after('license_batch_size');
            $table->boolean('license_compress_responses')->default(true)->after('license_use_indexes');

            // Testing settings
            $table->boolean('license_allow_test')->default(true)->after('license_compress_responses');
            $table->string('license_test_prefix')->default('TEST-')->after('license_allow_test');
            $table->boolean('license_bypass_testing')->default(false)->after('license_test_prefix');
            $table->boolean('license_mock_envato')->default(false)->after('license_bypass_testing');
            $table->boolean('license_generate_fake_data')->default(false)->after('license_mock_envato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
