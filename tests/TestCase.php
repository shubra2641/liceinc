<?php

namespace Tests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // إنشاء الأدوار والصلاحيات إذا كان الاختبار يستخدم RefreshDatabase
        if ($this->shouldSeedRoles()) {
            $this->seed(RolesAndPermissionsSeeder::class);
        }
    }

    /**
     * تحديد ما إذا كان يجب إضافة الأدوار والصلاحيات.
     */
    protected function shouldSeedRoles(): bool
    {
        $traits = class_uses_recursive(static::class);

        return in_array(RefreshDatabase::class, $traits);
    }
}
