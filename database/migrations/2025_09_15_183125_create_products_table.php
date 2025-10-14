<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('screenshots')->nullable();
            $table->string('version')->nullable();
            $table->text('changelog')->nullable();
            $table->json('requirements')->nullable();
            $table->json('installation_guide')->nullable();
            $table->json('features')->nullable();
            $table->json('tags')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->string('support_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_downloadable')->default(true);
            $table->boolean('requires_domain')->default(false);
            $table->enum('license_type', ['single', 'multi', 'developer', 'extended'])->default('single');
            $table->integer('duration_days')->nullable();
            $table->integer('support_days')->default(365);
            $table->integer('stock')->nullable();
            $table->decimal('renewal_price', 10, 2)->nullable();
            $table->enum('renewal_period', ['monthly', 'yearly', 'lifetime'])->nullable();
            $table->date('extended_supported_until')->nullable();
            $table->string('integration_file_path')->nullable();
            $table->json('kb_categories')->nullable();
            $table->integer('kb_category_id')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->foreignId('programming_language_id')->nullable()->constrained('programming_languages')->onDelete('set null');
            
            // Envato fields
            $table->string('envato_item_id')->nullable();
            $table->string('purchase_url_envato')->nullable();
            $table->string('purchase_url_buy')->nullable();
            
            // SEO fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            
            // Indexes
            $table->index(['slug', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['programming_language_id', 'is_active']);
            $table->index(['license_type', 'is_active']);
            $table->index(['is_featured', 'is_active']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};