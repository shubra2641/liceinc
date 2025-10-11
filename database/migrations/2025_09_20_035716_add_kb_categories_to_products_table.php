<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add KB categories relationship
            $table->json('kb_categories')->nullable()->after('extended_supported_until')
                ->comment('Array of KB category IDs linked to this product');

            // Add KB articles relationship
            $table->json('kb_articles')->nullable()->after('kb_categories')
                ->comment('Array of KB article IDs linked to this product');

            // Add KB access control
            $table->boolean('kb_access_required')->default(false)->after('kb_articles')
                ->comment('Whether KB access is required for this product');

            $table->text('kb_access_message')->nullable()->after('kb_access_required')
                ->comment('Custom message for KB access requirement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'kb_categories',
                'kb_articles',
                'kb_access_required',
                'kb_access_message',
            ]);
        });
    }
};
