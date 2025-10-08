<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Add missing preloader settings columns
            if (! Schema::hasColumn('settings', 'preloader_enabled')) {
                $table->boolean('preloader_enabled')->default(true)->after('license_lockout_minutes');
            }
            if (! Schema::hasColumn('settings', 'preloader_type')) {
                $table->string('preloader_type')->default('spinner')->after('preloader_enabled');
            }
            if (! Schema::hasColumn('settings', 'preloader_color')) {
                $table->string('preloader_color')->default('#3b82f6')->after('preloader_type');
            }
            if (! Schema::hasColumn('settings', 'preloader_background_color')) {
                $table->string('preloader_background_color')->default('#ffffff')->after('preloader_color');
            }
            if (! Schema::hasColumn('settings', 'preloader_duration')) {
                $table->integer('preloader_duration')->default(2000)->after('preloader_background_color');
            }
            if (! Schema::hasColumn('settings', 'preloader_custom_css')) {
                $table->text('preloader_custom_css')->nullable()->after('preloader_duration');
            }
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'preloader_enabled',
                'preloader_type',
                'preloader_color',
                'preloader_background_color',
                'preloader_duration',
                'preloader_custom_css',
            ]);
        });
    }
};
