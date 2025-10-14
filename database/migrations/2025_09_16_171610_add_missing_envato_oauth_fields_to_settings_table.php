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
            // Check if columns don't exist before adding them
            if (! Schema::hasColumn('settings', 'envato_client_id')) {
                $table->string('envato_client_id')->nullable()->after('envato_username');
            }
            if (! Schema::hasColumn('settings', 'envato_client_secret')) {
                $table->string('envato_client_secret')->nullable()->after('envato_client_id');
            }
            if (! Schema::hasColumn('settings', 'envato_redirect_uri')) {
                $table->string('envato_redirect_uri')->nullable()->after('envato_client_secret');
            }
            if (! Schema::hasColumn('settings', 'envato_oauth_enabled')) {
                $table->boolean('envato_oauth_enabled')->default(false)->after('envato_redirect_uri');
            }
            if (! Schema::hasColumn('settings', 'license_max_attempts')) {
                $table->integer('license_max_attempts')->default(5)->after('default_license_length');
            }
            if (! Schema::hasColumn('settings', 'license_lockout_minutes')) {
                $table->integer('license_lockout_minutes')->default(15)->after('license_max_attempts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'envato_client_id',
                'envato_client_secret',
                'envato_redirect_uri',
                'envato_oauth_enabled',
                'license_max_attempts',
                'license_lockout_minutes',
            ]);
        });
    }
};
