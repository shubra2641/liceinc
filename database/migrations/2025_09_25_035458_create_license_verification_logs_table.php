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
        Schema::create('license_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_code_hash', 64); // MD5 hash of purchase code for security
            $table->string('domain', 255);
            $table->string('ip_address', 45); // IPv6 support
            $table->string('user_agent', 500)->nullable();
            $table->boolean('is_valid')->default(false);
            $table->text('response_message')->nullable();
            $table->json('response_data')->nullable();
            $table->string('verification_source', 50)->default('install'); // install, api, admin
            $table->string('status', 20)->default('failed'); // success, failed, error
            $table->text('error_details')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['purchase_code_hash', 'domain']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['is_valid', 'created_at']);
            $table->index('verification_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_verification_logs');
    }
};
