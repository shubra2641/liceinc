<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('kb_categories')) {
            Schema::create('kb_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->foreignId('parent_id')->nullable()->constrained('kb_categories')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->boolean('is_published')->default(true);
                $table->string('icon')->nullable()->default('fas fa-folder');
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->timestamps();
            });
        } else {
            // Update existing table structure
            Schema::table('kb_categories', function (Blueprint $table) {
                // Add missing columns if they don't exist
                if (!Schema::hasColumn('kb_categories', 'name')) {
                    $table->string('name')->after('id');
                }
                if (!Schema::hasColumn('kb_categories', 'slug')) {
                    $table->string('slug')->unique()->after('name');
                }
                if (!Schema::hasColumn('kb_categories', 'description')) {
                    $table->text('description')->nullable()->after('slug');
                }
                if (!Schema::hasColumn('kb_categories', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('description');
                }
                if (!Schema::hasColumn('kb_categories', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->constrained('kb_categories')->nullOnDelete()->after('sort_order');
                }
                if (!Schema::hasColumn('kb_categories', 'product_id')) {
                    $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->after('parent_id');
                }
                if (!Schema::hasColumn('kb_categories', 'is_published')) {
                    $table->boolean('is_published')->default(true)->after('product_id');
                }
                if (!Schema::hasColumn('kb_categories', 'icon')) {
                    $table->string('icon')->nullable()->default('fas fa-folder')->after('is_published');
                }
                if (!Schema::hasColumn('kb_categories', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('icon');
                }
                if (!Schema::hasColumn('kb_categories', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_featured');
                }
                if (!Schema::hasColumn('kb_categories', 'meta_title')) {
                    $table->string('meta_title')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('kb_categories', 'meta_description')) {
                    $table->text('meta_description')->nullable()->after('meta_title');
                }
                if (!Schema::hasColumn('kb_categories', 'meta_keywords')) {
                    $table->text('meta_keywords')->nullable()->after('meta_description');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kb_categories');
    }
};
