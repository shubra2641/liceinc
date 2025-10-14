<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\ProgrammingLanguage;
use App\Models\Setting;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user if one doesn't already exist
        $adminEmail = 'admin@example.com';
        $admin = User::where('email', $adminEmail)->first();
        if (! $admin) {
            $admin = User::create([
                'name' => 'System Administrator',
                'email' => $adminEmail,
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
        }

        // Create default settings
        Setting::create([
            'site_name' => 'License Management System',
            'site_logo' => null,
        ]);

        // Create default product categories
        $categories = [
            [
                'name' => 'Web Applications',
                'slug' => 'web-applications',
                'description' => 'Web-based applications and software',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Mobile Apps',
                'slug' => 'mobile-apps',
                'description' => 'Mobile applications for iOS and Android',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Desktop Software',
                'slug' => 'desktop-software',
                'description' => 'Desktop applications and software',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'WordPress Themes',
                'slug' => 'wordpress-themes',
                'description' => 'WordPress themes and templates',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'WordPress Plugins',
                'slug' => 'wordpress-plugins',
                'description' => 'WordPress plugins and extensions',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }

        // Create default programming languages
        $languages = [
            [
                'name' => 'PHP',
                'slug' => 'php',
                'description' => 'PHP programming language',
                'is_active' => true,
                'sort_order' => 1,
                'license_template' => 'PHP License Template',
            ],
            [
                'name' => 'JavaScript',
                'slug' => 'javascript',
                'description' => 'JavaScript programming language',
                'is_active' => true,
                'sort_order' => 2,
                'license_template' => 'JavaScript License Template',
            ],
            [
                'name' => 'Python',
                'slug' => 'python',
                'description' => 'Python programming language',
                'is_active' => true,
                'sort_order' => 3,
                'license_template' => 'Python License Template',
            ],
            [
                'name' => 'Java',
                'slug' => 'java',
                'description' => 'Java programming language',
                'is_active' => true,
                'sort_order' => 4,
                'license_template' => 'Java License Template',
            ],
            [
                'name' => 'C#',
                'slug' => 'csharp',
                'description' => 'C# programming language',
                'is_active' => true,
                'sort_order' => 5,
                'license_template' => 'C# License Template',
            ],
        ];

        foreach ($languages as $language) {
            ProgrammingLanguage::updateOrCreate(
                ['slug' => $language['slug']],
                $language,
            );
        }

        // Create default ticket categories
        $ticketCategories = [
            [
                'name' => 'Technical Support',
                'slug' => 'technical-support',
                'description' => 'Technical support and troubleshooting',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'License Issues',
                'slug' => 'license-issues',
                'description' => 'License activation and verification issues',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Billing & Payments',
                'slug' => 'billing-payments',
                'description' => 'Billing, payments, and invoice issues',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($ticketCategories as $category) {
            TicketCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }

        $this->command->info('Default admin user created successfully!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: admin123');
        $this->command->info('Default settings, categories, and languages created successfully!');
    }
}
