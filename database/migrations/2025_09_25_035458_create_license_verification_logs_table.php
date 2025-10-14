<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_code');
            $table->string('domain');
            $table->string('ip_address');
            $table->string('user_agent');
            $table->boolean('is_valid');
            $table->text('message');
            $table->string('source');
            $table->json('response_data')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreignId('license_id')->nullable()->constrained('licenses')->onDelete('cascade');
            
            // Security fields
            $table->boolean('is_suspicious')->default(false);
            $table->text('suspicious_reason')->nullable();
            $table->json('security_flags')->nullable();
            
            // Indexes
            $table->index(['purchase_code', 'is_valid']);
            $table->index(['domain', 'is_valid']);
            $table->index(['ip_address', 'is_valid']);
            $table->index(['license_id', 'is_valid']);
            $table->index(['is_valid', 'created_at']);
            $table->index(['is_suspicious', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_verification_logs');
    }
};