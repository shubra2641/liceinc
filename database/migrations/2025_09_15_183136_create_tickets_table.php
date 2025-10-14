<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('type', ['bug', 'feature', 'question', 'other'])->default('question');
            $table->timestamps();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Foreign keys
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('license_id')->nullable()->constrained('licenses')->onDelete('set null');
            $table->foreignId('kb_category_id')->nullable()->constrained('kb_categories')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Ticket metadata
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('purchase_code')->nullable();
            $table->string('product_version')->nullable();
            $table->string('browser_info')->nullable();
            $table->json('attachments')->nullable();
            $table->json('tags')->nullable();
            $table->text('internal_notes')->nullable();
            
            // Response tracking
            $table->integer('response_count')->default(0);
            $table->timestamp('last_response_at')->nullable();
            $table->timestamp('last_customer_response_at')->nullable();
            $table->timestamp('last_staff_response_at')->nullable();
            
            // Indexes
            $table->index(['ticket_number', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['license_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};