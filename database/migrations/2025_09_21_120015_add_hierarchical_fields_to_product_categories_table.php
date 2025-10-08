<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // Hierarchical fields
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->foreign('parent_id')->references('id')->on('product_categories')->onDelete('cascade');

            // SEO fields
            $table->string('meta_title')->nullable()->after('description');
            $table->text('meta_keywords')->nullable()->after('meta_title');
            $table->text('meta_description')->nullable()->after('meta_keywords');

            // Color and styling fields
            $table->string('color', 7)->nullable()->after('meta_description');
            $table->string('text_color', 7)->nullable()->after('color');
            $table->string('icon', 100)->nullable()->after('text_color');

            // Additional settings
            $table->boolean('show_in_menu')->default(true)->after('is_active');
            $table->boolean('is_featured')->default(false)->after('show_in_menu');
            $table->boolean('allow_subcategories')->default(true)->after('is_featured');
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'meta_title',
                'meta_keywords',
                'meta_description',
                'color',
                'text_color',
                'icon',
                'show_in_menu',
                'is_featured',
                'allow_subcategories',
            ]);
        });
    }
};
