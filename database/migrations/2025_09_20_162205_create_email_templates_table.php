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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Template identifier (e.g., 'user_welcome', 'admin_license_created')
            $table->string('subject'); // Email subject
            $table->text('body'); // Email body content
            $table->string('type')->default('user'); // 'user' or 'admin'
            $table->string('category'); // 'registration', 'license', 'ticket', 'invoice', etc.
            $table->json('variables')->nullable(); // Available variables for this template
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['type', 'category']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
