<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            if (! Schema::hasColumn('kb_articles', 'image')) {
                $table->string('image')->nullable()->after('excerpt');
            }
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            if (Schema::hasColumn('kb_articles', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
