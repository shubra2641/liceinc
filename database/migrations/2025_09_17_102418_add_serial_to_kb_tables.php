<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add serial fields to kb_articles
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->string('serial')->nullable()->after('content');
            $table->boolean('requires_serial')->default(false)->after('serial');
            $table->text('serial_message')->nullable()->after('requires_serial');
        });

        // Add serial fields to kb_categories
        Schema::table('kb_categories', function (Blueprint $table) {
            $table->string('serial')->nullable()->after('description');
            $table->boolean('requires_serial')->default(false)->after('serial');
            $table->text('serial_message')->nullable()->after('requires_serial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove serial fields from kb_articles
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropColumn(['serial', 'requires_serial', 'serial_message']);
        });

        // Remove serial fields from kb_categories
        Schema::table('kb_categories', function (Blueprint $table) {
            $table->dropColumn(['serial', 'requires_serial', 'serial_message']);
        });
    }
};
