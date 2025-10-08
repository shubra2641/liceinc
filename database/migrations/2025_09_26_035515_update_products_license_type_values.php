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
        // Update existing products with 'regular' license_type to 'single'
        DB::table('products')
            ->where('license_type', 'regular')
            ->update(['license_type' => 'single']);

        // Update the column to allow the new values and set default
        Schema::table('products', function (Blueprint $table) {
            $table->string('license_type')->default('single')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old values
        DB::table('products')
            ->where('license_type', 'single')
            ->update(['license_type' => 'regular']);

        // Revert the column default
        Schema::table('products', function (Blueprint $table) {
            $table->string('license_type')->default('regular')->change();
        });
    }
};
