<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip this migration in testing environment if using SQLite
        if (config('database.default') === 'sqlite' && app()->environment('testing')) {
            return;
        }

        // Only run on MySQL/MariaDB
        if (config('database.default') !== 'mysql') {
            return;
        }

        // Remove deprecated columns if they exist
        $columnsToRemove = [
            'old_license_key', 'old_license_email', 'old_verification_token',
            'old_usage_count', 'old_max_usage', 'deprecated_status',
            'legacy_identifier', 'temp_migration_id',
        ];

        Schema::table('licenses', function (Blueprint $table) use ($columnsToRemove) {
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('licenses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Clean up license_type enum values (MySQL specific)
        if (Schema::hasColumn('licenses', 'license_type')) {
            // Create a temporary column with the correct enum values
            Schema::table('licenses', function (Blueprint $table) {
                $table->enum('license_type_temp', ['single', 'multi', 'developer', 'extended'])
                    ->default('single')
                    ->after('license_type');
            });

            // Update data to map old values to new values
            DB::statement("UPDATE licenses SET license_type_temp = CASE 
                WHEN license_type = 'regular' THEN 'single'
                WHEN license_type = 'extended' THEN 'extended'
                ELSE 'single'
            END");

            // Drop the old column
            Schema::table('licenses', function (Blueprint $table) {
                $table->dropColumn('license_type');
            });
        }

        // Rename the new column to the original name.
        // Use a raw ALTER TABLE statement to avoid Blueprint creating malformed default quoting
        // which can produce SQL like default '''single'''.
        if (Schema::hasColumn('licenses', 'license_type_temp')) {
            // MySQL ALTER TABLE CHANGE requires the full column definition; ensure correct ENUM and default
            DB::statement("ALTER TABLE `licenses` CHANGE `license_type_temp` `license_type` ENUM('single','multi','developer','extended') NOT NULL DEFAULT 'single'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip this migration in testing environment if using SQLite
        if (config('database.default') === 'sqlite' && app()->environment('testing')) {
            return;
        }

        // Only run on MySQL/MariaDB
        if (config('database.default') !== 'mysql') {
            return;
        }

        // Create a temporary column with the old enum values
        Schema::table('licenses', function (Blueprint $table) {
            $table->enum('license_type_old', ['regular', 'extended'])->default('regular');
        });

        // Map current values back to old values
        DB::statement("UPDATE licenses SET license_type_old = CASE 
            WHEN license_type IN ('single', 'multi', 'developer') THEN 'regular'
            WHEN license_type = 'extended' THEN 'extended'
            ELSE 'regular'
        END");

        // Drop current column and rename old one back
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('license_type');
        });

        DB::statement("ALTER TABLE `licenses` CHANGE `license_type_old` `license_type` ENUM('regular','extended') NOT NULL DEFAULT 'regular'");
    }
};
