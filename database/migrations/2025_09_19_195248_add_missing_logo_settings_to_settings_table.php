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
        Schema::table('settings', function (Blueprint $table) {
            $this->addLogoImageFields($table);
            $this->addLogoSizeFields($table);
            $this->addLogoTextFields($table);
        });
    }

    /**
     * Add logo image fields
     */
    private function addLogoImageFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'site_logo_dark', 'string', 'site_logo');
    }

    /**
     * Add logo size fields
     */
    private function addLogoSizeFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'logo_width', 'integer', 'site_logo_dark', ['default' => 150]);
        $this->addColumnIfNotExists($table, 'logo_height', 'integer', 'logo_width', ['default' => 50]);
    }

    /**
     * Add logo text fields
     */
    private function addLogoTextFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'logo_show_text', 'boolean', 'logo_height', ['default' => true]);
        $this->addColumnIfNotExists($table, 'logo_text', 'string', 'logo_show_text');
        $this->addColumnIfNotExists($table, 'logo_text_color', 'string', 'logo_text', ['default' => '#1f2937']);
        $this->addColumnIfNotExists($table, 'logo_text_font_size', 'string', 'logo_text_color', ['default' => '24px']);
    }

    /**
     * Helper method to add column if it doesn't exist
     */
    private function addColumnIfNotExists(Blueprint $table, string $column, string $type, string $after, array $options = []): void
    {
        if (Schema::hasColumn('settings', $column)) {
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
        Schema::table('settings', function (Blueprint $table) {
            $this->dropAddedColumns($table);
        });
    }

    /**
     * Drop all added columns
     */
    private function dropAddedColumns(Blueprint $table): void
    {
        $columnsToDrop = [
            // Logo Image Fields
            'site_logo_dark',
            
            // Logo Size Fields
            'logo_width', 'logo_height',
            
            // Logo Text Fields
            'logo_show_text', 'logo_text', 'logo_text_color', 'logo_text_font_size'
        ];

        $table->dropColumn($columnsToDrop);
    }
};
