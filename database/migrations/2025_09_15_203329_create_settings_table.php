<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Lic');
            $table->string('site_logo')->nullable();
            $table->string('support_email')->nullable();
            $table->text('site_description')->nullable();

            // Envato Settings
            $table->string('envato_personal_token')->nullable();
            $table->string('envato_api_key')->nullable();
            $table->boolean('envato_auth_enabled')->default(false);
            $table->string('envato_username')->nullable();

            // License Settings
            $table->boolean('auto_generate_license')->default(true);
            $table->integer('default_license_length')->default(32);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
