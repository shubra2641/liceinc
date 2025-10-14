<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kb_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('article_count')->default(0);
            $table->timestamps();
            
            // SEO fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            
            // Serial number for ordering
            $table->string('serial')->nullable();
            
            // Product relationship
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            
            // Indexes
            $table->index(['slug', 'is_published']);
            $table->index(['is_featured', 'is_published']);
            $table->index(['sort_order', 'is_published']);
            $table->index(['product_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_categories');
    }
};