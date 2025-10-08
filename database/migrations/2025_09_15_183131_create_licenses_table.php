<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('purchase_code')->unique();
            $table->string('license_key')->unique();
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            $table->enum('license_type', ['regular', 'extended'])->default('regular');
            $table->timestamp('supported_until')->nullable();
            $table->timestamp('license_expires_at')->nullable();
            $table->timestamp('support_expires_at')->nullable();
            $table->timestamp('purchase_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
