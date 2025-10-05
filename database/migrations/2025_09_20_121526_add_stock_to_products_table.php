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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->nullable()->default(null); // null = unlimited
            $table->integer('duration_days')->default(365); // مدة الترخيص بالأيام
            $table->boolean('auto_renewal')->default(true); // هل يتم التجديد التلقائي
            $table->integer('renewal_reminder_days')->default(7); // عدد الأيام قبل التجديد لإرسال تذكير
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock', 'duration_days', 'auto_renewal', 'renewal_reminder_days']);
        });
    }
};
