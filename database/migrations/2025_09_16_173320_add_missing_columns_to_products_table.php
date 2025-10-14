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
        Schema::table('products', function (Blueprint $table) {
            $this->addPricingAndStockFields($table);
            $this->addIntegrationFields($table);
            $this->addRelationshipFields($table);
            $this->addProductDetailsFields($table);
            $this->addSeoFields($table);
            $this->addDisplayFields($table);
            $this->addMediaFields($table);
        });
    }

    /**
     * Add pricing and stock related fields
     */
    private function addPricingAndStockFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'tax_rate', 'decimal', 'license_type', [
            'precision' => 5,
            'scale' => 2,
            'default' => 0
        ]);
        $this->addColumnIfNotExists($table, 'stock_quantity', 'integer', 'tax_rate', ['default' => -1]);
    }

    /**
     * Add integration related fields
     */
    private function addIntegrationFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'integration_file_path', 'string', 'stock_quantity');
    }

    /**
     * Add relationship fields
     */
    private function addRelationshipFields(Blueprint $table): void
    {
        $this->addForeignKeyIfNotExists($table, 'programming_language', 'integration_file_path', 'programming_languages');
        $this->addForeignKeyIfNotExists($table, 'category_id', 'programming_language', 'product_categories');
        $this->addColumnIfNotExists($table, 'requires_domain', 'boolean', 'category_id', ['default' => false]);
    }

    /**
     * Add product details fields
     */
    private function addProductDetailsFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'features', 'json', 'requires_domain');
        $this->addColumnIfNotExists($table, 'requirements', 'text', 'features');
        $this->addColumnIfNotExists($table, 'installation_guide', 'text', 'requirements');
    }

    /**
     * Add SEO fields
     */
    private function addSeoFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'meta_title', 'string', 'installation_guide');
        $this->addColumnIfNotExists($table, 'meta_description', 'text', 'meta_title');
        $this->addColumnIfNotExists($table, 'tags', 'json', 'meta_description');
    }

    /**
     * Add display fields
     */
    private function addDisplayFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'is_featured', 'boolean', 'tags', ['default' => false]);
        $this->addColumnIfNotExists($table, 'is_popular', 'boolean', 'is_featured', ['default' => false]);
    }

    /**
     * Add media fields
     */
    private function addMediaFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'image', 'string', 'is_popular');
        $this->addColumnIfNotExists($table, 'gallery_images', 'json', 'image');
    }

    /**
     * Helper method to add column if it doesn't exist
     */
    private function addColumnIfNotExists(Blueprint $table, string $column, string $type, string $after, array $options = []): void
    {
        if (Schema::hasColumn('products', $column)) {
            return;
        }

        $columnDefinition = $table->{$type}($column);
        
        if (isset($options['precision'])) {
            $columnDefinition = $table->{$type}($column, $options['precision'], $options['scale'] ?? 0);
        }
        
        $columnDefinition->nullable();
        
        if (isset($options['default'])) {
            $columnDefinition->default($options['default']);
        }
        
        $columnDefinition->after($after);
    }

    /**
     * Helper method to add foreign key if it doesn't exist
     */
    private function addForeignKeyIfNotExists(Blueprint $table, string $column, string $after, string $referencedTable): void
    {
        if (Schema::hasColumn('products', $column)) {
            return;
        }

        $table->foreignId($column)
            ->nullable()
            ->constrained($referencedTable)
            ->after($after);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $this->dropForeignKeys($table);
            $this->dropAddedColumns($table);
        });
    }

    /**
     * Drop foreign keys first
     */
    private function dropForeignKeys(Blueprint $table): void
    {
        if (Schema::hasColumn('products', 'programming_language')) {
            $table->dropForeign(['programming_language']);
        }
        if (Schema::hasColumn('products', 'category_id')) {
            $table->dropForeign(['category_id']);
        }
    }

    /**
     * Drop all added columns
     */
    private function dropAddedColumns(Blueprint $table): void
    {
        $columnsToDrop = [
            // Pricing and Stock
            'tax_rate', 'stock_quantity',
            
            // Integration
            'integration_file_path',
            
            // Relationships
            'programming_language', 'category_id', 'requires_domain',
            
            // Product Details
            'features', 'requirements', 'installation_guide',
            
            // SEO
            'meta_title', 'meta_description', 'tags',
            
            // Display
            'is_featured', 'is_popular',
            
            // Media
            'image', 'gallery_images'
        ];

        foreach ($columnsToDrop as $column) {
            if (Schema::hasColumn('products', $column)) {
                $table->dropColumn($column);
            }
        }
    }
};
