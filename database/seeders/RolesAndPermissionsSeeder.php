<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إعادة تعيين cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الأدوار
        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);

        // إنشاء الصلاحيات
        $permissions = [
            // User permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            // License permissions
            'view_licenses',
            'create_licenses',
            'edit_licenses',
            'delete_licenses',

            // Product permissions
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',

            // Ticket permissions
            'view_tickets',
            'create_tickets',
            'edit_tickets',
            'delete_tickets',

            // Settings permissions
            'view_settings',
            'edit_settings',

            // Dashboard access
            'access_admin_dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // إسناد جميع الصلاحيات للأدمن
        $adminRole->givePermissionTo(Permission::all());

        // إسناد صلاحيات محددة للعميل
        $customerRole->givePermissionTo([
            'view_tickets',
            'create_tickets',
            'view_licenses',
        ]);
    }
}
