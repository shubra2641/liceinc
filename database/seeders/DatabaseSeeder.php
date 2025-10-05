<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            RoleSeeder::class,
            ProgrammingLanguageSeeder::class,
            EmailTemplateSeeder::class,
        ]);

        // Add test licenses at the very end if seeder exists
        if (class_exists(TestLicenseSeeder::class)) {
            $this->call(TestLicenseSeeder::class);
        }
    }
}
