<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->unique(); // 'paypal' or 'stripe'
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_sandbox')->default(true);
            $table->json('credentials')->nullable(); // Store API keys and secrets
            $table->string('webhook_url')->nullable();
            $table->string('return_url')->nullable();
            $table->string('cancel_url')->nullable();
            $table->timestamps();
        });

        // Insert default payment settings
        DB::table('payment_settings')->insert([
            [
                'gateway' => 'paypal',
                'is_enabled' => false,
                'is_sandbox' => true,
                'credentials' => json_encode([
                    'client_id' => '',
                    'client_secret' => '',
                ]),
                'webhook_url' => '',
                'return_url' => '',
                'cancel_url' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gateway' => 'stripe',
                'is_enabled' => false,
                'is_sandbox' => true,
                'credentials' => json_encode([
                    'publishable_key' => '',
                    'secret_key' => '',
                    'webhook_secret' => '',
                ]),
                'webhook_url' => '',
                'return_url' => '',
                'cancel_url' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
