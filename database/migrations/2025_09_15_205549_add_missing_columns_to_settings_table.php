<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('support_phone')->nullable()->after('support_email');
            $table->string('timezone')->default('UTC')->after('site_description');
            $table->boolean('maintenance_mode')->default(false)->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['support_phone', 'timezone', 'maintenance_mode']);
        });
    }
};
