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
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'seo_site_title')) {
                $table->string('seo_site_title')->nullable();
            }
            if (! Schema::hasColumn('settings', 'seo_site_description')) {
                $table->text('seo_site_description')->nullable();
            }
            if (! Schema::hasColumn('settings', 'seo_og_image')) {
                $table->string('seo_og_image')->nullable();
            }
            if (! Schema::hasColumn('settings', 'seo_kb_title')) {
                $table->string('seo_kb_title')->nullable();
            }
            if (! Schema::hasColumn('settings', 'seo_kb_description')) {
                $table->text('seo_kb_description')->nullable();
            }
            if (! Schema::hasColumn('settings', 'seo_tickets_title')) {
                $table->string('seo_tickets_title')->nullable();
            }
            if (! Schema::hasColumn('settings', 'seo_tickets_description')) {
                $table->text('seo_tickets_description')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'seo_tickets_description')) {
                $table->dropColumn('seo_tickets_description');
            }
            if (Schema::hasColumn('settings', 'seo_tickets_title')) {
                $table->dropColumn('seo_tickets_title');
            }
            if (Schema::hasColumn('settings', 'seo_kb_description')) {
                $table->dropColumn('seo_kb_description');
            }
            if (Schema::hasColumn('settings', 'seo_kb_title')) {
                $table->dropColumn('seo_kb_title');
            }
            if (Schema::hasColumn('settings', 'seo_og_image')) {
                $table->dropColumn('seo_og_image');
            }
            if (Schema::hasColumn('settings', 'seo_site_description')) {
                $table->dropColumn('seo_site_description');
            }
            if (Schema::hasColumn('settings', 'seo_site_title')) {
                $table->dropColumn('seo_site_title');
            }
        });
    }
};
