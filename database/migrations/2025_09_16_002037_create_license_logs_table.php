<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_logs', function (Blueprint $table) {
            $table->id();
            $table->string('serial');
            $table->string('domain')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('status', ['success', 'failed', 'blocked'])->default('success');
            $table->text('message')->nullable();
            $table->string('method')->default('api');
            $table->json('response_data')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreignId('license_id')->nullable()->constrained('licenses')->onDelete('cascade');
            
            // Security fields
            $table->boolean('is_suspicious')->default(false);
            $table->text('suspicious_reason')->nullable();
            $table->json('security_flags')->nullable();
            
            // Indexes
            $table->index(['serial', 'status']);
            $table->index(['license_id', 'status']);
            $table->index(['domain', 'status']);
            $table->index(['ip_address', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['is_suspicious', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_logs');
    }
};