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
            $this->addSeoSiteFields($table);
            $this->addSeoKbFields($table);
            $this->addSeoTicketsFields($table);
        });
    }

    /**
     * Add SEO site fields
     */
    private function addSeoSiteFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'seo_site_title', 'string');
        $this->addColumnIfNotExists($table, 'seo_site_description', 'text');
        $this->addColumnIfNotExists($table, 'seo_og_image', 'string');
    }

    /**
     * Add SEO knowledge base fields
     */
    private function addSeoKbFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'seo_kb_title', 'string');
        $this->addColumnIfNotExists($table, 'seo_kb_description', 'text');
    }

    /**
     * Add SEO tickets fields
     */
    private function addSeoTicketsFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'seo_tickets_title', 'string');
        $this->addColumnIfNotExists($table, 'seo_tickets_description', 'text');
    }

    /**
     * Helper method to add column if it doesn't exist
     */
    private function addColumnIfNotExists(Blueprint $table, string $column, string $type): void
    {
        if (Schema::hasColumn('settings', $column)) {
            return;
        }

        $table->{$type}($column)->nullable();
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
            // SEO Site Fields
            'seo_site_title', 'seo_site_description', 'seo_og_image',
            
            // SEO Knowledge Base Fields
            'seo_kb_title', 'seo_kb_description',
            
            // SEO Tickets Fields
            'seo_tickets_title', 'seo_tickets_description'
        ];

        foreach ($columnsToDrop as $column) {
            if (Schema::hasColumn('settings', $column)) {
                $table->dropColumn($column);
            }
        }
    }
};
