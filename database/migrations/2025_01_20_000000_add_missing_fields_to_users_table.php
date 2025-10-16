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
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $this->addPersonalInfoFields($table);
            $this->addAddressFields($table);
            $this->addContactFields($table);
            $this->addAdditionalFields($table);
            $this->addEmailAndSSOFields($table);
            $this->addPasswordResetFields($table);
            $this->addFinancialFields($table);
            $this->addTimestampFields($table);
        });
    }

    /**
     * Add personal information fields.
     */
    private function addPersonalInfoFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'firstname', 'string', 'name');
        $this->addColumnIfNotExists($table, 'lastname', 'string', 'firstname');
        $this->addColumnIfNotExists($table, 'companyname', 'string', 'lastname');
    }

    /**
     * Add address fields.
     */
    private function addAddressFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'address1', 'text', 'companyname');
        $this->addColumnIfNotExists($table, 'address2', 'text', 'address1');
        $this->addColumnIfNotExists($table, 'city', 'string', 'address2');
        $this->addColumnIfNotExists($table, 'state', 'string', 'city');
        $this->addColumnIfNotExists($table, 'postcode', 'string', 'state');
        $this->addColumnIfNotExists($table, 'country', 'string', 'postcode');
    }

    /**
     * Add contact information fields.
     */
    private function addContactFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'phonenumber', 'string', 'country');
        $this->addColumnIfNotExists($table, 'currency', 'string', 'phonenumber', ['length' => 3, 'default' => 'USD']);
    }

    /**
     * Add additional user fields.
     */
    private function addAdditionalFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'notes', 'text', 'currency');
        $this->addColumnIfNotExists($table, 'cardnum', 'string', 'notes');
        $this->addColumnIfNotExists($table, 'startdate', 'date', 'cardnum');
        $this->addColumnIfNotExists($table, 'expdate', 'date', 'startdate');
        $this->addColumnIfNotExists($table, 'lastlogin', 'timestamp', 'expdate');
        $this->addColumnIfNotExists($table, 'status', 'enum', 'lastlogin', [
            'values' => ['active', 'inactive', 'suspended'],
            'default' => 'active',
        ]);
        $this->addColumnIfNotExists($table, 'language', 'string', 'status', ['length' => 5, 'default' => 'en']);
    }

    /**
     * Add email and SSO fields.
     */
    private function addEmailAndSSOFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'allow_sso', 'boolean', 'language', ['default' => false]);
        $this->addColumnIfNotExists($table, 'email_verified', 'boolean', 'allow_sso', ['default' => false]);
        $this->addColumnIfNotExists($table, 'email_preferences', 'json', 'email_verified');
    }

    /**
     * Add password reset fields.
     */
    private function addPasswordResetFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'pwresetkey', 'string', 'email_preferences');
        $this->addColumnIfNotExists($table, 'pwresetexpiry', 'timestamp', 'pwresetkey');
    }

    /**
     * Add financial fields.
     */
    private function addFinancialFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'credit', 'decimal', 'pwresetexpiry', [
            'precision' => 10,
            'scale' => 2,
            'default' => 0.00,
        ]);

        $booleanFields = [
            'taxexempt', 'latefeeoveride', 'overideduenotices', 'separateinvoices',
            'disableautocc', 'emailoptout', 'marketing_emails_opt_in', 'overrideautoclose',
        ];

        $previousField = 'credit';
        foreach ($booleanFields as $field) {
            $this->addColumnIfNotExists($table, $field, 'boolean', $previousField, ['default' => false]);
            $previousField = $field;
        }
    }

    /**
     * Add timestamp fields.
     */
    private function addTimestampFields(Blueprint $table): void
    {
        $this->addColumnIfNotExists($table, 'datecreated', 'timestamp', 'overrideautoclose');
    }

    /**
     * Helper method to add column if it doesn't exist.
     */
    private function addColumnIfNotExists(Blueprint $table, string $column, string $type, string $after, array $options = []): void
    {
        if (Schema::hasColumn('users', $column)) {
            return;
        }

        $columnDefinition = $table->{$type}($column, $options['length'] ?? null);

        if (isset($options['precision'])) {
            $columnDefinition = $table->{$type}($column, $options['precision'], $options['scale'] ?? 0);
        }

        if (isset($options['values'])) {
            $columnDefinition = $table->{$type}($column, $options['values']);
        }

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
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $this->dropAddedColumns($table);
        });
    }

    /**
     * Drop all added columns.
     */
    private function dropAddedColumns(Blueprint $table): void
    {
        $columnsToDrop = [
            // Personal Information
            'firstname', 'lastname', 'companyname',

            // Address Fields
            'address1', 'address2', 'city', 'state', 'postcode', 'country',

            // Contact Information
            'phonenumber', 'currency',

            // Additional Fields
            'notes', 'cardnum', 'startdate', 'expdate', 'lastlogin', 'status', 'language',

            // SSO and Email
            'allow_sso', 'email_verified', 'email_preferences',

            // Password Reset
            'pwresetkey', 'pwresetexpiry',

            // Financial Fields
            'credit', 'taxexempt', 'latefeeoveride', 'overideduenotices',
            'separateinvoices', 'disableautocc', 'emailoptout',
            'marketing_emails_opt_in', 'overrideautoclose',

            // Timestamps
            'datecreated',
        ];

        $table->dropColumn($columnsToDrop);
    }
};
