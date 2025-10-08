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
        // Check if table exists before modifying it
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // Check if column doesn't exist before adding it
                if (! Schema::hasColumn('products', 'is_downloadable')) {
                    $table->boolean('is_downloadable')->default(true)->after('is_popular');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_downloadable');
            });
        }
    }
};
