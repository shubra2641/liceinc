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
        // Check if table exists before modifying it
        if (Schema::hasTable('kb_categories')) {
            Schema::table('kb_categories', function (Blueprint $table) {
                // Check if columns don't exist before adding them
                if (! Schema::hasColumn('kb_categories', 'icon')) {
                    $table->string('icon')->nullable()->default('fas fa-folder')->after('description');
                }
                if (! Schema::hasColumn('kb_categories', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('is_published');
                }
                if (! Schema::hasColumn('kb_categories', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_featured');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kb_categories')) {
            Schema::table('kb_categories', function (Blueprint $table) {
                $table->dropColumn(['icon', 'is_featured', 'is_active']);
            });
        }
    }
};
