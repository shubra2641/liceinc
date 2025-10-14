<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kb_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->integer('dislike_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->timestamp('published_at')->nullable();
            
            // Foreign keys
            $table->foreignId('kb_category_id')->constrained('kb_categories')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            
            // SEO fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            
            // Serial number for ordering
            $table->string('serial')->nullable();
            
            // Article metadata
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->json('tags')->nullable();
            $table->json('attachments')->nullable();
            $table->json('related_articles')->nullable();
            
            // Version control
            $table->string('version')->default('1.0');
            $table->integer('revision')->default(1);
            $table->foreignId('parent_id')->nullable()->constrained('kb_articles')->onDelete('cascade');
            
            // Indexes
            $table->index(['slug', 'is_published']);
            $table->index(['kb_category_id', 'is_published']);
            $table->index(['product_id', 'is_published']);
            $table->index(['is_featured', 'is_published']);
            $table->index(['published_at', 'is_published']);
            $table->index(['view_count', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_articles');
    }
};