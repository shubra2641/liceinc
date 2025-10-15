<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('kb_categories')) {
            return;
        }

        Schema::table('kb_categories', function (Blueprint $table) {
            $this->addColumnIfNotExists($table, 'icon', 'string', ['nullable', 'default' => 'fas fa-folder'], 'description');
            $this->addColumnIfNotExists($table, 'is_featured', 'boolean', ['default' => false], 'is_published');
            $this->addColumnIfNotExists($table, 'is_active', 'boolean', ['default' => true], 'is_featured');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('kb_categories')) {
            return;
        }

        Schema::table('kb_categories', function (Blueprint $table) {
            $table->dropColumn(['icon', 'is_featured', 'is_active']);
        });
    }

    private function addColumnIfNotExists(Blueprint $table, string $column, string $type, array $options = [], ?string $after = null): void
    {
        if (!Schema::hasColumn('kb_categories', $column)) {
            $columnDefinition = $table->$type($column);
            
            foreach ($options as $option => $value) {
                if (is_numeric($option)) {
                    $columnDefinition->$value();
                } else {
                    $columnDefinition->$option($value);
                }
            }
            
            if ($after) {
                $columnDefinition->after($after);
            }
        }
    }
};