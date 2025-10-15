<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('users')) return;

        Schema::table('users', function (Blueprint $table) {
            $this->addColumn($table, 'firstname', 'string', 'name');
            $this->addColumn($table, 'lastname', 'string', 'firstname');
            $this->addColumn($table, 'companyname', 'string', 'lastname');
            $this->addColumn($table, 'address1', 'text', 'companyname');
            $this->addColumn($table, 'address2', 'text', 'address1');
            $this->addColumn($table, 'city', 'string', 'address2');
            $this->addColumn($table, 'state', 'string', 'city');
            $this->addColumn($table, 'postcode', 'string', 'state');
            $this->addColumn($table, 'country', 'string', 'postcode');
            $this->addColumn($table, 'phonenumber', 'string', 'country');
            $this->addColumn($table, 'currency', 'string', 'phonenumber', 3, 'USD');
            $this->addColumn($table, 'notes', 'text', 'currency');
            $this->addColumn($table, 'cardnum', 'string', 'notes');
            $this->addColumn($table, 'startdate', 'date', 'cardnum');
            $this->addColumn($table, 'expdate', 'date', 'startdate');
            $this->addColumn($table, 'lastlogin', 'timestamp', 'expdate');
            $this->addEnumColumn($table, 'status', 'lastlogin', ['active', 'inactive', 'suspended'], 'active');
            $this->addColumn($table, 'language', 'string', 'status', 5, 'en');
            $this->addColumn($table, 'allow_sso', 'boolean', 'language', null, false);
            $this->addColumn($table, 'email_verified', 'boolean', 'allow_sso', null, false);
            $this->addColumn($table, 'email_preferences', 'json', 'email_verified');
            $this->addColumn($table, 'password_reset_token', 'string', 'email_preferences');
            $this->addColumn($table, 'password_reset_expires', 'timestamp', 'password_reset_token');
            $this->addColumn($table, 'balance', 'decimal', 'password_reset_expires', null, 0, [10, 2]);
            $this->addColumn($table, 'credit_limit', 'decimal', 'balance', null, null, [10, 2]);
            $this->addColumn($table, 'created_by', 'unsignedBigInteger', 'credit_limit');
            $this->addColumn($table, 'updated_by', 'unsignedBigInteger', 'created_by');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) return;

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'firstname', 'lastname', 'companyname', 'address1', 'address2', 'city', 'state', 'postcode', 'country',
                'phonenumber', 'currency', 'notes', 'cardnum', 'startdate', 'expdate', 'lastlogin', 'status', 'language',
                'allow_sso', 'email_verified', 'email_preferences', 'password_reset_token', 'password_reset_expires',
                'balance', 'credit_limit', 'created_by', 'updated_by'
            ]);
        });
    }

    private function addColumn(Blueprint $table, string $name, string $type, string $after, ?int $length = null, $default = null, ?array $precision = null): void
    {
        if (Schema::hasColumn('users', $name)) return;

        $column = $table->$type($name, $length);
        if ($precision) $column = $table->$type($name, $precision[0], $precision[1]);
        if ($default !== null) $column->default($default);
        $column->nullable()->after($after);
    }

    private function addEnumColumn(Blueprint $table, string $name, string $after, array $values, $default = null): void
    {
        if (Schema::hasColumn('users', $name)) return;

        $column = $table->enum($name, $values);
        if ($default !== null) $column->default($default);
        $column->after($after);
    }
};