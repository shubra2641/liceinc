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
        // Check if table exists before modifying it
        if (Schema::hasTable('kb_articles')) {
            Schema::table('kb_articles', function (Blueprint $table) {
                // Check if columns don't exist before adding them
                if (! Schema::hasColumn('kb_articles', 'allow_comments')) {
                    // Add after meta_keywords if it exists, otherwise after is_published
                    if (Schema::hasColumn('kb_articles', 'meta_keywords')) {
                        $table->boolean('allow_comments')->default(true)->after('meta_keywords');
                    } else {
                        $table->boolean('allow_comments')->default(true)->after('is_published');
                    }
                }
                if (! Schema::hasColumn('kb_articles', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('allow_comments');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kb_articles')) {
            Schema::table('kb_articles', function (Blueprint $table) {
                $table->dropColumn(['allow_comments', 'is_featured']);
            });
        }
    }
};
