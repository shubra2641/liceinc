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
        $envatoFields = [
            ['envato_client_id', 'string', 'envato_username'],
            ['envato_client_secret', 'string', 'envato_client_id'],
            ['envato_redirect_uri', 'string', 'envato_client_secret'],
            ['envato_oauth_enabled', 'boolean', 'envato_redirect_uri', ['default' => false]]
        ];

        foreach ($envatoFields as $field) {
            $this->addColumnIfNotExists($table, $field[0], $field[1], $field[2], $field[3] ?? []);
        }
    }

    /**
     * Add license security fields
     */
    private function addLicenseSecurityFields(Blueprint $table): void
    {
        $securityFields = [
            ['license_max_attempts', 'integer', 'default_license_length', ['default' => 5]],
            ['license_lockout_minutes', 'integer', 'license_max_attempts', ['default' => 15]]
        ];

        foreach ($securityFields as $field) {
            $this->addColumnIfNotExists($table, $field[0], $field[1], $field[2], $field[3]);
        }
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
            'envato_client_id', 'envato_client_secret', 'envato_redirect_uri', 'envato_oauth_enabled',
            'license_max_attempts', 'license_lockout_minutes'
        ];

        $table->dropColumn($columnsToDrop);
    }
};
