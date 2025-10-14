<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('filename');
            $table->string('path');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->string('hash')->nullable();
            $table->string('encryption_key')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('download_count')->default(0);
            $table->timestamps();
            
            // Foreign keys
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // File metadata
            $table->text('description')->nullable();
            $table->string('version')->nullable();
            $table->json('metadata')->nullable();
            $table->json('permissions')->nullable();
            
            // Indexes
            $table->index(['product_id', 'is_active']);
            $table->index(['filename', 'is_active']);
            $table->index(['mime_type', 'is_active']);
            $table->index(['download_count', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_files');
    }
};
