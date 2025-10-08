<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('license_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('renewal'); // renewal, initial
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // لتخزين معلومات إضافية
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['due_date']);
            $table->index(['license_id']);
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
