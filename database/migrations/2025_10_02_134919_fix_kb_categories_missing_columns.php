<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        // Check if table exists before modifying it
        if (Schema::hasTable('kb_categories')) {
            Schema::table('kb_categories', function (Blueprint $table) {
                // Add icon column if it doesn't exist
                if (! Schema::hasColumn('kb_categories', 'icon')) {
                    $table->string('icon')->nullable()->default('fas fa-folder')->after('description');
                }

                // Add is_featured column if it doesn't exist
                if (! Schema::hasColumn('kb_categories', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('is_published');
                }

                // Add is_active column if it doesn't exist
                if (! Schema::hasColumn('kb_categories', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_featured');
                }
            });
        }
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        if (Schema::hasTable('kb_categories')) {
            Schema::table('kb_categories', function (Blueprint $table) {
                // Only drop columns if they exist
                if (Schema::hasColumn('kb_categories', 'icon')) {
                    $table->dropColumn('icon');
                }
                if (Schema::hasColumn('kb_categories', 'is_featured')) {
                    $table->dropColumn('is_featured');
                }
                if (Schema::hasColumn('kb_categories', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};
