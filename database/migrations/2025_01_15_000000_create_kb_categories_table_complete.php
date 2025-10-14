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
            $this->addBasicFields($table);
            $this->addRelationshipFields($table);
            $this->addStatusFields($table);
            $this->addMetaFields($table);
        });
    }

    /**
     * Add basic category fields
     */
    private function addBasicFields(Blueprint $table): void
    {
        $basicFields = [
            ['name', 'string', 'id', []],
            ['slug', 'string', 'name', ['unique' => true]],
            ['description', 'text', 'slug', ['nullable' => true]],
            ['sort_order', 'integer', 'description', ['default' => 0]]
        ];

        foreach ($basicFields as $field) {
            $this->addColumnIfMissing($table, $field[0], $field[1], $field[2], $field[3]);
        }
    }

    /**
     * Add relationship fields
     */
    private function addRelationshipFields(Blueprint $table): void
    {
        $relationshipFields = [
            ['parent_id', 'foreignId', 'sort_order', ['nullable' => true, 'constrained' => 'kb_categories']],
            ['product_id', 'foreignId', 'parent_id', ['nullable' => true, 'constrained' => 'products']]
        ];

        foreach ($relationshipFields as $field) {
            $this->addColumnIfMissing($table, $field[0], $field[1], $field[2], $field[3]);
        }
    }

    /**
     * Add status and display fields
     */
    private function addStatusFields(Blueprint $table): void
    {
        $statusFields = [
            ['is_published', 'boolean', 'product_id', ['default' => true]],
            ['icon', 'string', 'is_published', ['nullable' => true, 'default' => 'fas fa-folder']],
            ['is_featured', 'boolean', 'icon', ['default' => false]],
            ['is_active', 'boolean', 'is_featured', ['default' => true]]
        ];

        foreach ($statusFields as $field) {
            $this->addColumnIfMissing($table, $field[0], $field[1], $field[2], $field[3]);
        }
    }

    /**
     * Add meta fields
     */
    private function addMetaFields(Blueprint $table): void
    {
        $metaFields = [
            ['meta_title', 'string', 'is_active', ['nullable' => true]],
            ['meta_description', 'text', 'meta_title', ['nullable' => true]],
            ['meta_keywords', 'text', 'meta_description', ['nullable' => true]]
        ];

        foreach ($metaFields as $field) {
            $this->addColumnIfMissing($table, $field[0], $field[1], $field[2], $field[3]);
        }
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
        $this->applyColumnOptions($columnDefinition, $options);
        $columnDefinition->after($after);
    }

    /**
     * Apply options to column definition
     */
    private function applyColumnOptions($columnDefinition, array $options): void
    {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('kb_categories')) {
            return;
        }

        $this->dropForeignKeys();
        Schema::dropIfExists('kb_categories');
    }

    /**
     * Drop foreign key constraints
     */
    private function dropForeignKeys(): void
    {
        Schema::table('kb_categories', function (Blueprint $table) {
            if (Schema::hasColumn('kb_categories', 'parent_id')) {
                $table->dropForeign(['parent_id']);
            }
            if (Schema::hasColumn('kb_categories', 'product_id')) {
                $table->dropForeign(['product_id']);
            }
        });
    }
};