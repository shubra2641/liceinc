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
            if (! Schema::hasColumn('products', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('license_type');
            }
            if (! Schema::hasColumn('products', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(-1)->after('tax_rate'); // -1 means unlimited
            }
            if (! Schema::hasColumn('products', 'integration_file_path')) {
                $table->string('integration_file_path')->nullable()->after('stock_quantity');
            }
            if (! Schema::hasColumn('products', 'programming_language')) {
                $table->foreignId('programming_language')
                    ->nullable()
                    ->constrained('programming_languages')
                    ->after('integration_file_path');
            }
            if (! Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->constrained('product_categories')
                    ->after('programming_language');
            }
            if (! Schema::hasColumn('products', 'requires_domain')) {
                $table->boolean('requires_domain')->default(false)->after('category_id');
            }
            if (! Schema::hasColumn('products', 'features')) {
                $table->json('features')->nullable()->after('requires_domain');
            }
            if (! Schema::hasColumn('products', 'requirements')) {
                // Keep as text if not existing to avoid type conflicts with previous migrations
                $table->text('requirements')->nullable()->after('features');
            }
            if (! Schema::hasColumn('products', 'installation_guide')) {
                $table->text('installation_guide')->nullable()->after('requirements');
            }
            if (! Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('installation_guide');
            }
            if (! Schema::hasColumn('products', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (! Schema::hasColumn('products', 'tags')) {
                $table->json('tags')->nullable()->after('meta_description');
            }
            if (! Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('tags');
            }
            if (! Schema::hasColumn('products', 'is_popular')) {
                $table->boolean('is_popular')->default(false)->after('is_featured');
            }
            if (! Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable()->after('is_popular');
            }
            if (! Schema::hasColumn('products', 'gallery_images')) {
                $table->json('gallery_images')->nullable()->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop FKs first if the columns exist
            if (Schema::hasColumn('products', 'programming_language')) {
                // Index name follows Laravel's convention: products_programming_language_foreign
                $table->dropForeign(['programming_language']);
            }
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropForeign(['category_id']);
            }

            $columns = [
                'tax_rate',
                'stock_quantity',
                'integration_file_path',
                'programming_language',
                'category_id',
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
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
