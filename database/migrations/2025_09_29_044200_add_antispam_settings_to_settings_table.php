<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add anti-spam related columns to single-row settings table if they don't exist
        if (! Schema::hasTable('settings')) {
            return;
        }

        if (! Schema::hasColumn('settings', 'enable_captcha') || ! Schema::hasColumn('settings', 'captcha_site_key') || ! Schema::hasColumn('settings', 'captcha_secret_key') || ! Schema::hasColumn('settings', 'enable_human_question') || ! Schema::hasColumn('settings', 'human_questions')) {
            Schema::table('settings', function (Blueprint $table) {
                if (! Schema::hasColumn('settings', 'enable_captcha')) {
                    $table->boolean('enable_captcha')->default(false)->after('license_api_token');
                }
                if (! Schema::hasColumn('settings', 'captcha_site_key')) {
                    $table->string('captcha_site_key')->nullable()->after('enable_captcha');
                }
                if (! Schema::hasColumn('settings', 'captcha_secret_key')) {
                    $table->string('captcha_secret_key')->nullable()->after('captcha_site_key');
                }
                if (! Schema::hasColumn('settings', 'enable_human_question')) {
                    $table->boolean('enable_human_question')->default(true)->after('captcha_secret_key');
                }
                if (! Schema::hasColumn('settings', 'human_questions')) {
                    $table->text('human_questions')->nullable()->after('enable_human_question');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'human_questions')) {
                $table->dropColumn('human_questions');
            }
            if (Schema::hasColumn('settings', 'enable_human_question')) {
                $table->dropColumn('enable_human_question');
            }
            if (Schema::hasColumn('settings', 'captcha_secret_key')) {
                $table->dropColumn('captcha_secret_key');
            }
            if (Schema::hasColumn('settings', 'captcha_site_key')) {
                $table->dropColumn('captcha_site_key');
            }
            if (Schema::hasColumn('settings', 'enable_captcha')) {
                $table->dropColumn('enable_captcha');
            }
        });
    }
};
