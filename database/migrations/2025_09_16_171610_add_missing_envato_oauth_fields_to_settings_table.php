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
            $this->addEnvatoOAuthFields($table);
            $this->addLicenseSecurityFields($table);
        });
    }

    /**
     * Add Envato OAuth fields
     */
    private function addEnvatoOAuthFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'envato_client_id', 'string', 'envato_username');
        $this->addColumnIfNotExists($table, 'envato_client_secret', 'string', 'envato_client_id');
        $this->addColumnIfNotExists($table, 'envato_redirect_uri', 'string', 'envato_client_secret');
        $this->addColumnIfNotExists($table, 'envato_oauth_enabled', 'boolean', 'envato_redirect_uri', ['default' => false]);
    }

    /**
     * Add license security fields
     */
    private function addLicenseSecurityFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'license_max_attempts', 'integer', 'default_license_length', ['default' => 5]);
        $this->addColumnIfNotExists($table, 'license_lockout_minutes', 'integer', 'license_max_attempts', ['default' => 15]);
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
            // Envato OAuth Fields
            'envato_client_id', 'envato_client_secret', 'envato_redirect_uri', 'envato_oauth_enabled',
            
            // License Security Fields
            'license_max_attempts', 'license_lockout_minutes'
        ];

        $table->dropColumn($columnsToDrop);
    }
};
