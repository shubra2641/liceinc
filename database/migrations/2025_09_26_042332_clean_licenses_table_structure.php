<?php

declare(strict_types=1);

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
        if (!$this->shouldRunMigration()) {
            return;
        }

        $this->removeDeprecatedColumns();
        $this->cleanupLicenseTypeEnum();
    }

    /**
     * Check if migration should run
     */
    private function shouldRunMigration(): bool
    {
        // Skip this migration in testing environment if using SQLite
        if (config('database.default') === 'sqlite' && app()->environment('testing')) {
            return false;
        }

        // Only run on MySQL/MariaDB
        return config('database.default') === 'mysql';
    }

    /**
     * Remove deprecated columns
     */
    private function removeDeprecatedColumns(): void
    {
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
    }

    /**
     * Clean up license_type enum values
     */
    private function cleanupLicenseTypeEnum(): void
    {
        if (!Schema::hasColumn('licenses', 'license_type')) {
            return;
        }

        $this->createTemporaryColumn();
        $this->updateDataMapping();
        $this->replaceOriginalColumn();
    }

    /**
     * Create temporary column with correct enum values
     */
    private function createTemporaryColumn(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->enum('license_type_temp', ['single', 'multi', 'developer', 'extended'])
                ->default('single')
                ->after('license_type');
        });
    }

    /**
     * Update data to map old values to new values
     */
    private function updateDataMapping(): void
    {
        DB::statement("UPDATE licenses SET license_type_temp = CASE 
            WHEN license_type = 'regular' THEN 'single'
            WHEN license_type = 'extended' THEN 'extended'
            ELSE 'single'
        END");
    }

    /**
     * Replace original column with cleaned up version
     */
    private function replaceOriginalColumn(): void
    {
        // Drop the old column
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('license_type');
        });

        // Rename the new column to the original name
        if (Schema::hasColumn('licenses', 'license_type_temp')) {
            DB::statement("ALTER TABLE `licenses` CHANGE `license_type_temp` `license_type` ENUM('single','multi','developer','extended') NOT NULL DEFAULT 'single'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!$this->shouldRunMigration()) {
            return;
        }

        $this->restoreOldLicenseTypeEnum();
    }

    /**
     * Restore old license_type enum values
     */
    private function restoreOldLicenseTypeEnum(): void
    {
        $this->createOldTemporaryColumn();
        $this->mapDataBackToOldValues();
        $this->replaceWithOldColumn();
    }

    /**
     * Create temporary column with old enum values
     */
    private function createOldTemporaryColumn(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->enum('license_type_old', ['regular', 'extended'])->default('regular');
        });
    }

    /**
     * Map current values back to old values
     */
    private function mapDataBackToOldValues(): void
    {
        DB::statement("UPDATE licenses SET license_type_old = CASE 
            WHEN license_type IN ('single', 'multi', 'developer') THEN 'regular'
            WHEN license_type = 'extended' THEN 'extended'
            ELSE 'regular'
        END");
    }

    /**
     * Replace current column with old version
     */
    private function replaceWithOldColumn(): void
    {
        // Drop current column
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('license_type');
        });

        // Rename old column back
        DB::statement("ALTER TABLE `licenses` CHANGE `license_type_old` `license_type` ENUM('regular','extended') NOT NULL DEFAULT 'regular'");
    }
};
