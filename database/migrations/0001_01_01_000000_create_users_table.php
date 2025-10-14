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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            
            // Personal Information Fields
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('companyname')->nullable();
            
            // Address Fields
            $table->text('address1')->nullable();
            $table->text('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->nullable();
            
            // Contact Information
            $table->string('phonenumber')->nullable();
            $table->string('currency', 3)->default('USD');
            
            // Additional Fields
            $table->text('notes')->nullable();
            $table->string('cardnum')->nullable();
            $table->date('startdate')->nullable();
            $table->date('expdate')->nullable();
            $table->timestamp('lastlogin')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('language', 5)->default('en');
            
            // SSO and Email Preferences
            $table->boolean('allow_sso')->default(false);
            $table->boolean('email_verified')->default(false);
            $table->json('email_preferences')->nullable();
            
            // Password Reset Fields
            $table->string('pwresetkey')->nullable();
            $table->timestamp('pwresetexpiry')->nullable();
            
            // Financial Fields
            $table->decimal('credit', 10, 2)->default(0.00);
            $table->boolean('taxexempt')->default(false);
            $table->boolean('latefeeoveride')->default(false);
            $table->boolean('overideduenotices')->default(false);
            $table->boolean('separateinvoices')->default(false);
            $table->boolean('disableautocc')->default(false);
            $table->boolean('emailoptout')->default(false);
            $table->boolean('marketing_emails_opt_in')->default(false);
            $table->boolean('overrideautoclose')->default(false);
            
            // Timestamps
            $table->timestamp('datecreated')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};