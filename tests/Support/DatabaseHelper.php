<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * مساعد لإدارة قاعدة البيانات في الاختبارات.
 */
class DatabaseHelper
{
    /**
     * إعادة تعيين قاعدة البيانات.
     */
    public static function resetDatabase(): void
    {
        try {
            // إغلاق جميع الـ connections
            DB::purge('sqlite');

            // تنظيف الـ schema cache
            Schema::connection('sqlite')->getConnection()->purge();

            // إعادة الاتصال
            DB::reconnect('sqlite');
        } catch (\Exception $e) {
            // في حالة فشل الإعادة تعيين، إنشاء connection جديد
            config(['database.connections.sqlite.database' => ':memory:']);
            DB::purge('sqlite');
        }
    }

    /**
     * بدء transaction آمن.
     */
    public static function beginTransaction(): void
    {
        try {
            if (! DB::transactionLevel()) {
                DB::beginTransaction();
            }
        } catch (\Exception $e) {
            self::resetDatabase();
            DB::beginTransaction();
        }
    }

    /**
     * rollback آمن.
     */
    public static function rollback(): void
    {
        try {
            while (DB::transactionLevel() > 0) {
                DB::rollback();
            }
        } catch (\Exception $e) {
            self::resetDatabase();
        }
    }
}
