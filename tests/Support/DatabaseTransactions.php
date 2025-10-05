<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Trait للتعامل مع قاعدة البيانات في الاختبارات بطريقة آمنة.
 */
trait DatabaseTransactions
{
    /**
     * إعداد قاعدة البيانات.
     */
    protected function setUpDatabase(): void
    {
        // إعادة تعيين جميع الـ connections
        DB::purge();

        // تشغيل migrations
        Artisan::call('migrate:fresh', [
            '--database' => 'sqlite',
            '--force' => true,
        ]);
    }

    /**
     * تنظيف قاعدة البيانات.
     */
    protected function tearDownDatabase(): void
    {
        try {
            // إغلاق جميع transactions المفتوحة
            while (DB::transactionLevel() > 0) {
                DB::rollback();
            }
        } catch (\Exception $e) {
            // تجاهل أخطاء rollback
        }

        try {
            // قطع الاتصال وإعادة تعيين
            DB::disconnect();
            DB::purge();
        } catch (\Exception $e) {
            // تجاهل أخطاء قطع الاتصال
        }
    }

    /**
     * بدء transaction جديد بطريقة آمنة.
     */
    protected function beginDatabaseTransaction(): void
    {
        try {
            if (DB::transactionLevel() === 0) {
                DB::beginTransaction();
            }
        } catch (\Exception $e) {
            $this->setUpDatabase();
            DB::beginTransaction();
        }
    }
}
