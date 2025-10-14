<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            
            // Additional fields
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('bio')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->string('timezone')->default('UTC');
            $table->enum('language', ['en', 'ar', 'hi'])->default('en');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_admin')->default(false);
            $table->enum('role', ['user', 'admin', 'moderator'])->default('user');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->json('preferences')->nullable();
            $table->json('social_links')->nullable();
            
            // Envato fields
            $table->string('envato_username')->nullable();
            $table->string('envato_token')->nullable();
            $table->string('envato_refresh_token')->nullable();
            $table->timestamp('envato_token_expires_at')->nullable();
            $table->json('envato_profile')->nullable();
            
            // Security fields
            $table->string('two_factor_secret')->nullable();
            $table->json('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->json('login_history')->nullable();
            
            // Indexes
            $table->index(['email', 'is_active']);
            $table->index(['role', 'is_active']);
            $table->index('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};