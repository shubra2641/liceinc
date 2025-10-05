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
        Schema::table('product_files', function (Blueprint $table) {
            // Change encryption_key column from string to text to accommodate longer encrypted keys
            $table->text('encryption_key')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_files', function (Blueprint $table) {
            // Revert back to string (this might cause issues if data is too long)
            $table->string('encryption_key')->change();
        });
    }
};
