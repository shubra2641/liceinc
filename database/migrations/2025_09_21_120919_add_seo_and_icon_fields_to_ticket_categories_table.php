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
        Schema::table('ticket_categories', function (Blueprint $table) {
            // SEO fields
            $table->string('meta_title')->nullable()->after('description');
            $table->text('meta_keywords')->nullable()->after('meta_title');
            $table->text('meta_description')->nullable()->after('meta_keywords');

            // Icon and priority fields
            $table->string('icon', 100)->nullable()->after('meta_description');
            $table->string('priority', 20)->default('medium')->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            // Remove added fields
            $table->dropColumn(['meta_title', 'meta_keywords', 'meta_description', 'icon', 'priority']);
        });
    }
};
