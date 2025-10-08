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
        Schema::table('products', function (Blueprint $table) {
            // Make extended_support_days nullable
            $table->integer('extended_support_days')->nullable()->change();

            // Make extended_support_price nullable
            $table->decimal('extended_support_price', 10, 2)->nullable()->change();

            // Make extended_supported_until nullable
            $table->timestamp('extended_supported_until')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert extended_support_days to not nullable
            $table->integer('extended_support_days')->nullable(false)->change();

            // Revert extended_support_price to not nullable
            $table->decimal('extended_support_price', 10, 2)->nullable(false)->change();

            // Revert extended_supported_until to not nullable
            $table->timestamp('extended_supported_until')->nullable(false)->change();
        });
    }
};
