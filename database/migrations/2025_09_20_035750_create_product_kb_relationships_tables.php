<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        // Product to KB Categories relationship table
        Schema::create('product_kb_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('kb_category_id');
            $table->timestamps();

            $table->unique(['product_id', 'kb_category_id']);
            $table->index(['product_id']);
            $table->index(['kb_category_id']);

            // Add foreign key constraints after tables are created
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('kb_category_id')->references('id')->on('kb_categories')->onDelete('cascade');
        });

        // Product to KB Articles relationship table
        Schema::create('product_kb_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('kb_article_id');
            $table->timestamps();

            $table->unique(['product_id', 'kb_article_id']);
            $table->index(['product_id']);
            $table->index(['kb_article_id']);

            // Add foreign key constraints after tables are created
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('kb_article_id')->references('id')->on('kb_articles')->onDelete('cascade');
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('product_kb_articles');
        Schema::dropIfExists('product_kb_categories');
    }
};
