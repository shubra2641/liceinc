<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_code')->unique();
            $table->string('license_key')->unique();
            $table->enum('status', ['active', 'inactive', 'expired', 'suspended', 'revoked'])->default('active');
            $table->enum('license_type', ['single', 'multi', 'developer', 'extended'])->default('single');
            $table->integer('max_domains')->default(1);
            $table->timestamp('license_expires_at')->nullable();
            $table->timestamp('support_expires_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->string('last_verified_ip')->nullable();
            $table->string('last_verified_domain')->nullable();
            $table->integer('verification_count')->default(0);
            $table->json('verification_history')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Envato fields
            $table->string('envato_purchase_code')->nullable();
            $table->string('envato_buyer_username')->nullable();
            $table->string('envato_buyer_email')->nullable();
            $table->timestamp('envato_purchase_date')->nullable();
            $table->decimal('envato_purchase_price', 10, 2)->nullable();
            $table->string('envato_license_type')->nullable();
            $table->json('envato_purchase_data')->nullable();
            
            // License metadata
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_company')->nullable();
            $table->json('customer_address')->nullable();
            $table->json('license_metadata')->nullable();
            
            // Security fields
            $table->string('verification_token')->nullable();
            $table->timestamp('verification_token_expires_at')->nullable();
            $table->json('security_flags')->nullable();
            $table->boolean('is_suspicious')->default(false);
            $table->text('suspicious_reason')->nullable();
            
            // Indexes
            $table->index(['purchase_code', 'status']);
            $table->index(['license_key', 'status']);
            $table->index(['product_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'license_expires_at']);
            $table->index(['last_verified_at', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};