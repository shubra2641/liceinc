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
            return;
        }

        Schema::table('kb_categories', function (Blueprint $table) {
            $this->addMissingColumns($table);
        });
    }

    /**
     * Add missing columns to kb_categories table
     */
    private function addMissingColumns(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'icon', 'string', 'description', [
            'default' => 'fas fa-folder'
        ]);
        $this->addColumnIfNotExists($table, 'is_featured', 'boolean', 'is_published', [
            'default' => false
        ]);
        $this->addColumnIfNotExists($table, 'is_active', 'boolean', 'is_featured', [
            'default' => true
        ]);
    }

    /**
     * Helper method to add column if it doesn't exist
     */
    private function addColumnIfNotExists(Blueprint $table, string $column, string $type, string $after, array $options = []): void
    {
        if (Schema::hasColumn('kb_categories', $column)) {
            return;
        }

        $columnDefinition = $table->{$type}($column);
        $columnDefinition->nullable();
        
        if (isset($options['default'])) {
            $columnDefinition->default($options['default']);
        }
        
        $columnDefinition->after($after);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('kb_categories')) {
            return;
        }

        Schema::table('kb_categories', function (Blueprint $table) {
            $this->dropAddedColumns($table);
        });
    }

    /**
     * Drop all added columns
     */
    private function dropAddedColumns(Blueprint $table): void
    {
        $columnsToDrop = ['icon', 'is_featured', 'is_active'];

        foreach ($columnsToDrop as $column) {
            if (Schema::hasColumn('kb_categories', $column)) {
                $table->dropColumn($column);
            }
        }
    }
};
