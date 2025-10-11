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
        Schema::table('users', function (Blueprint $table) {
            $table->string('envato_username')->nullable()->after('email_verified_at');
            $table->string('envato_id')->nullable()->after('envato_username');
            $table->text('envato_token')->nullable()->after('envato_id');
            $table->text('envato_refresh_token')->nullable()->after('envato_token');
            $table->timestamp('envato_token_expires_at')->nullable()->after('envato_refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'envato_username',
                'envato_id',
                'envato_token',
                'envato_refresh_token',
                'envato_token_expires_at',
            ]);
        });
    }
};
