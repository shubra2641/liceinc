<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::create('product_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('version', 20);
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('changelog')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('file_hash')->nullable();
            $table->boolean('is_major')->default(false);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('requirements')->nullable(); // PHP version, Laravel version, etc.
            $table->json('compatibility')->nullable(); // Compatible with versions
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'version']);
            $table->index(['product_id', 'is_active']);
            $table->unique(['product_id', 'version']);
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('product_updates');
    }
};
