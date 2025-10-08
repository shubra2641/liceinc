<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Add license information fields
            $table->string('key')->nullable()->after('id');
            $table->text('value')->nullable()->after('key');
            $table->string('type')->default('general')->after('value');

            // Add indexes for better performance
            $table->index(['key', 'type']);
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex(['key', 'type']);
            $table->dropColumn(['key', 'value', 'type']);
        });
    }
};
