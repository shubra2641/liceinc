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
        Schema::table('tickets', function (Blueprint $table) {
            // If invoices table already exists, create a proper foreign key.
            // Otherwise create the column as unsignedBigInteger nullable to avoid migration ordering issues.
            if (Schema::hasTable('invoices')) {
                $table->foreignId('invoice_id')
                    ->nullable()
                    ->constrained('invoices')
                    ->nullOnDelete()
                    ->after('license_id');
            } else {
                $table->unsignedBigInteger('invoice_id')->nullable()->after('license_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Attempt to drop the foreign key if it exists; ignore errors if not present.
            try {
                $table->dropForeign(['invoice_id']);
            } catch (Exception $e) {
                // Foreign key might not exist - ignore
            }

            if (Schema::hasColumn('tickets', 'invoice_id')) {
                $table->dropColumn('invoice_id');
            }
        });
    }
};
