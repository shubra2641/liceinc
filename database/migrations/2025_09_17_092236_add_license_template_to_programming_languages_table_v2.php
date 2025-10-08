<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::table('programming_languages', function (Blueprint $table) {
            if (! Schema::hasColumn('programming_languages', 'license_template')) {
                $table->string('license_template')->nullable()->after('file_extension');
            }
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::table('programming_languages', function (Blueprint $table) {
            if (Schema::hasColumn('programming_languages', 'license_template')) {
                $table->dropColumn('license_template');
            }
        });
    }
};
