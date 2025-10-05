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
        Schema::create('product_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('original_name'); // Original filename
            $table->string('encrypted_name'); // Encrypted filename for storage
            $table->string('file_path'); // Path to encrypted file
            $table->string('file_type'); // MIME type
            $table->bigInteger('file_size'); // File size in bytes
            $table->string('encryption_key'); // Encrypted encryption key
            $table->string('checksum'); // File checksum for integrity
            $table->text('description')->nullable(); // Optional file description
            $table->integer('download_count')->default(0); // Track downloads
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_files');
    }
};
