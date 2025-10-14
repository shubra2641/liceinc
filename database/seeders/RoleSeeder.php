<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create basic permissions
        $permissions = [
            'manage_users',
            'manage_products',
            'manage_licenses',
            'manage_tickets',
            'manage_settings',
            'view_reports',
            'manage_kb',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to admin role
        $adminRole->syncPermissions($permissions);

        // Optionally sync users with is_admin field.
        // This can be destructive if run on a production DB where 'is_admin'
        // values are incorrect. Require an explicit opt-in via environment
        // or run only in the local environment.
        if (app()->environment('local') || config('app.seed_sync_users', false)) {
            // Sync users with is_admin field
            $adminUsers = User::where('is_admin', 1)->get();
            foreach ($adminUsers as $user) {
                if (! $user->hasRole('admin')) {
                    $user->assignRole('admin');
                }
            }

            // Assign user role to non-admin users
            $regularUsers = User::where('is_admin', 0)->get();
            foreach ($regularUsers as $user) {
                if (! $user->hasRole('user')) {
                    $user->assignRole('user');
                }
            }
        } else {
            $this->command->info(
                'Skipping syncing users by is_admin flag. Set APP_ENV=local or SEED_SYNC_USERS=true to enable.',
            );
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
