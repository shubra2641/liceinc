<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('envato_item_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->string('license_type')->default('regular');
            $table->unsignedInteger('support_days')->default(180);
            $table->timestamp('supported_until')->nullable();
            $table->decimal('extended_support_price', 8, 2)->nullable();
            $table->integer('extended_support_days')->default(365);
            $table->timestamp('extended_supported_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
