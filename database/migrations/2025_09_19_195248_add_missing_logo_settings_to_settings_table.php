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
            // Add missing logo settings columns
            if (! Schema::hasColumn('settings', 'site_logo_dark')) {
                $table->string('site_logo_dark')->nullable()->after('site_logo');
            }
            if (! Schema::hasColumn('settings', 'logo_width')) {
                $table->integer('logo_width')->default(150)->after('site_logo_dark');
            }
            if (! Schema::hasColumn('settings', 'logo_height')) {
                $table->integer('logo_height')->default(50)->after('logo_width');
            }
            if (! Schema::hasColumn('settings', 'logo_show_text')) {
                $table->boolean('logo_show_text')->default(true)->after('logo_height');
            }
            if (! Schema::hasColumn('settings', 'logo_text')) {
                $table->string('logo_text')->nullable()->after('logo_show_text');
            }
            if (! Schema::hasColumn('settings', 'logo_text_color')) {
                $table->string('logo_text_color')->default('#1f2937')->after('logo_text');
            }
            if (! Schema::hasColumn('settings', 'logo_text_font_size')) {
                $table->string('logo_text_font_size')->default('24px')->after('logo_text_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'site_logo_dark',
                'logo_width',
                'logo_height',
                'logo_show_text',
                'logo_text',
                'logo_text_color',
                'logo_text_font_size',
            ]);
        });
    }
};
