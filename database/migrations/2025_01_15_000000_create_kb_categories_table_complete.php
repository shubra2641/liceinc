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
            $this->createTable();
        } else {
            $this->updateTable();
        }
    }

    /**
     * Create the kb_categories table
     */
    private function createTable(): void
    {
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
    }

    /**
     * Update existing table with missing columns
     */
    private function updateTable(): void
    {
        Schema::table('kb_categories', function (Blueprint $table) {
            $this->addColumnIfMissing($table, 'name', 'string', 'id');
            $this->addColumnIfMissing($table, 'slug', 'string', 'name', ['unique' => true]);
            $this->addColumnIfMissing($table, 'description', 'text', 'slug', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'sort_order', 'integer', 'description', ['default' => 0]);
            $this->addColumnIfMissing($table, 'parent_id', 'foreignId', 'sort_order', ['nullable' => true, 'constrained' => 'kb_categories']);
            $this->addColumnIfMissing($table, 'product_id', 'foreignId', 'parent_id', ['nullable' => true, 'constrained' => 'products']);
            $this->addColumnIfMissing($table, 'is_published', 'boolean', 'product_id', ['default' => true]);
            $this->addColumnIfMissing($table, 'icon', 'string', 'is_published', ['nullable' => true, 'default' => 'fas fa-folder']);
            $this->addColumnIfMissing($table, 'is_featured', 'boolean', 'icon', ['default' => false]);
            $this->addColumnIfMissing($table, 'is_active', 'boolean', 'is_featured', ['default' => true]);
            $this->addColumnIfMissing($table, 'meta_title', 'string', 'is_active', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'meta_description', 'text', 'meta_title', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'meta_keywords', 'text', 'meta_description', ['nullable' => true]);
        });
    }

    /**
     * Add column if it doesn't exist
     */
    private function addColumnIfMissing(Blueprint $table, string $column, string $type, string $after, array $options = []): void
    {
        if (Schema::hasColumn('kb_categories', $column)) {
            return;
        }

        $columnDefinition = $table->{$type}($column);
        
        if (isset($options['nullable']) && $options['nullable']) {
            $columnDefinition->nullable();
        }
        
        if (isset($options['default'])) {
            $columnDefinition->default($options['default']);
        }
        
        if (isset($options['unique']) && $options['unique']) {
            $columnDefinition->unique();
        }
        
        if (isset($options['constrained'])) {
            $columnDefinition->constrained($options['constrained'])->nullOnDelete();
        }
        
        $columnDefinition->after($after);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kb_categories')) {
            Schema::table('kb_categories', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
                $table->dropForeign(['product_id']);
            });
            Schema::dropIfExists('kb_categories');
        }
    }
};