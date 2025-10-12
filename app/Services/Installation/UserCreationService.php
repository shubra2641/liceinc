<?php

declare(strict_types=1);

namespace App\Services\Installation;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * User Creation Service
 *
 * Handles user creation operations to reduce controller complexity.
 */
class UserCreationService
{
    /**
     * Create admin user.
     *
     * @param array<string, mixed> $adminData
     */
    public function createAdminUser(array $adminData): bool
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'password' => Hash::make(is_string($adminData['password'] ?? null) ? $adminData['password'] : ''),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);

            // Assign admin role
            $user->assignRole('admin');

            DB::commit();
            Log::info('Admin user created successfully', ['user_id' => $user->id]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create admin user', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create test user for development.
     */
    public function createTestUser(): bool
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]);

            // Assign user role
            $user->assignRole('user');

            DB::commit();
            Log::info('Test user created successfully', ['user_id' => $user->id]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create test user', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
