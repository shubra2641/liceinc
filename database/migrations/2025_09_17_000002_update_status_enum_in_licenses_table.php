<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        // Unify old values before modifying field type to avoid data loss
        DB::statement("UPDATE `licenses` SET `status` = 'inactive' WHERE `status` = 'revoked'");
        // Set ENUM to match values used in the interface
        // MySQL supports MODIFY for enum changes; sqlite used by tests does not.
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE `licenses` MODIFY `status` ENUM('active', 'inactive', 'suspended', 'expired') NOT NULL DEFAULT 'active'",
            );
        }
    }

    public function down(): void
    {
        // Restore statuses to old group in case of rollback
        DB::statement("UPDATE `licenses` SET `status` = 'revoked' WHERE `status` = 'suspended'");
        DB::statement("UPDATE `licenses` SET `status` = 'active' WHERE `status` = 'inactive'");
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE `licenses` MODIFY `status` ENUM('active', 'expired', 'revoked') NOT NULL DEFAULT 'active'",
            );
        }
    }
};
