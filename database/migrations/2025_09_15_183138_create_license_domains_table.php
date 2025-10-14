<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->timestamps();
            $table->timestamp('added_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            
            // Foreign keys
            $table->foreignId('license_id')->constrained('licenses')->onDelete('cascade');
            
            // Domain metadata
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->json('verification_data')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            // Security fields
            $table->boolean('is_suspicious')->default(false);
            $table->text('suspicious_reason')->nullable();
            $table->json('security_flags')->nullable();
            $table->integer('verification_count')->default(0);
            $table->timestamp('last_verification_at')->nullable();
            
            // Indexes
            $table->index(['domain', 'status']);
            $table->index(['license_id', 'status']);
            $table->index(['status', 'last_used_at']);
            $table->index(['is_verified', 'status']);
            $table->index(['is_suspicious', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_domains');
    }
};