<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // Add hierarchical fields back
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->foreign('parent_id')->references('id')->on('product_categories')->onDelete('cascade');

            // Add allow_subcategories field
            $table->boolean('allow_subcategories')->default(true)->after('is_featured');
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // Remove hierarchical fields
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'allow_subcategories',
            ]);
        });
    }
};
