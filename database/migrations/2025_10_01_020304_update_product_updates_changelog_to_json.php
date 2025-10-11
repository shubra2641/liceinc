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
        Schema::table('product_updates', function (Blueprint $table) {
            // Change changelog from text to json
            $table->json('changelog')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_updates', function (Blueprint $table) {
            // Change changelog back to text
            $table->text('changelog')->nullable()->change();
        });
    }
};
