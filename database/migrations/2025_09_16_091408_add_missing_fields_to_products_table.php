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
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('product_categories')
                ->onDelete('set null');
            $table->foreignId('programming_language')
                ->nullable()
                ->constrained('programming_languages')
                ->onDelete('set null');
            $table->string('integration_file_path')->nullable();
            $table->decimal('renewal_price', 8, 2)->nullable();
            $table->enum(
                'renewal_period',
                ['monthly', 'quarterly', 'semi-annual', 'annual', 'three-years', 'lifetime'],
            )->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->integer('stock_quantity')->default(-1); // -1 = unlimited
            $table->boolean('requires_domain')->default(false);
            $table->json('features')->nullable();
            $table->text('requirements')->nullable();
            $table->text('installation_guide')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->string('image')->nullable();
            $table->json('gallery_images')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['programming_language']);
            $table->dropColumn([
                'category_id',
                'programming_language',
                'integration_file_path',
                'renewal_price',
                'renewal_period',
                'tax_rate',
                'stock_quantity',
                'requires_domain',
                'features',
                'requirements',
                'installation_guide',
                'meta_title',
                'meta_description',
                'tags',
                'is_featured',
                'is_popular',
                'image',
                'gallery_images',
            ]);
        });
    }
};
