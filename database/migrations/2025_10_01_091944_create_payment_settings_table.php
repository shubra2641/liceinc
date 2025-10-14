<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->default('stripe');
            $table->boolean('is_active')->default(true);
            $table->boolean('test_mode')->default(true);
            $table->json('credentials')->nullable();
            $table->json('webhook_settings')->nullable();
            $table->json('currency_settings')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['gateway', 'is_active']);
            $table->index(['test_mode', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};