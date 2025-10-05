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
        Schema::table('license_domains', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('status');
            $table->timestamp('verified_at')->nullable()->after('is_verified');
            $table->timestamp('added_at')->nullable()->after('verified_at');
            $table->timestamp('last_used_at')->nullable()->after('added_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('license_domains', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'verified_at', 'added_at', 'last_used_at']);
        });
    }
};
