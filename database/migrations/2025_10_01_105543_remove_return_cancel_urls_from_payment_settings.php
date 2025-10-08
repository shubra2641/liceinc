<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->dropColumn(['return_url', 'cancel_url']);
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->string('return_url')->nullable();
            $table->string('cancel_url')->nullable();
        });
    }
};
