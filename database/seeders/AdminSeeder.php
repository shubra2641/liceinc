<?php

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
                'is_admin' => true,
                'role' => 'admin',
                'status' => 'active',
            ]);
        }

        // Create default settings
        $settings = [
            // General Settings
            'site_name' => 'License Management System',
            'site_description' => 'Professional License Management System',
            'site_keywords' => 'license, management, system, software',
            'site_logo' => null,
            'site_favicon' => null,
            'default_language' => 'en',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',

            // Email Settings
            'mail_driver' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'License Management System',

            // License Settings
            'license_verification_enabled' => true,
            'license_api_token' => \Illuminate\Support\Str::random(32),
            'max_domains_per_license' => 1,
            'license_expiry_days' => 365,
            'auto_renewal_enabled' => false,

            // Security Settings
            'login_attempts_limit' => 5,
            'session_timeout' => 120,
            'password_reset_expiry' => 60,
            'two_factor_enabled' => false,

            // File Upload Settings
            'max_file_size' => 104857600, // 100MB
            'allowed_file_types' => 'zip, rar, pdf, php, js, css, html, json, xml, sql, jpg, jpeg, png, gif, svg',
            'file_encryption_enabled' => true,

            // Support Settings
            'support_email' => 'support@example.com',
            'support_phone' => '+1234567890',
            'support_hours' => '24/7',
            'avg_response_time' => 2,

            // SEO Settings
            'meta_title' => 'License Management System',
            'meta_description' => 'Professional License Management System for Software Products',
            'meta_keywords' => 'license, management, system, software, digital products',
            'google_analytics' => '',
            'facebook_pixel' => '',

            // Social Media
            'facebook_url' => '',
            'twitter_url' => '',
            'linkedin_url' => '',
            'instagram_url' => '',

            // Payment Settings
            'currency' => 'USD',
            'currency_symbol' => '$',
            'tax_rate' => 0,
            'payment_gateway' => 'stripe',

            // Notification Settings
            'notification_email' => 'notifications@example.com',
            'notify_on_expiration' => true,
            'notify_on_domain_change' => true,
            'notify_on_suspicious_activity' => true,

            // Maintenance Settings
            'maintenance_mode' => false,
            'maintenance_message' => 'System is under maintenance. Please try again later.',

            // Cache Settings
            'cache_enabled' => true,
            'cache_duration' => 3600,

            // Backup Settings
            'backup_enabled' => true,
            'backup_frequency' => 'daily',
            'backup_retention' => 30,

            // Anti-spam / Registration Protection
            'enable_captcha' => false,
            'captcha_site_key' => '',
            'captcha_secret_key' => '',
            'enable_human_question' => true,
            // Provide a default list of human questions (question/answer pairs) as JSON
            'human_questions' => json_encode([
                ['question' => 'What is 2 + 3?', 'answer' => '5'],
                ['question' => 'What color is the sky on a clear day?', 'answer' => 'blue'],
                ['question' => 'What is the opposite of up?', 'answer' => 'down'],
                ['question' => 'How many wheels does a bicycle have?', 'answer' => '2'],
                ['question' => 'What is 1 + 1?', 'answer' => '2'],
                ['question' => 'What is the first month of the year?', 'answer' => 'january'],
                ['question' => 'Spell the word cat', 'answer' => 'cat'],
                ['question' => 'What is five minus two?', 'answer' => '3'],
            ]),
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'string'],
            );
        }

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
                'icon' => 'fas fa-tools',
                'color' => '#007bff',
            ],
            [
                'name' => 'License Issues',
                'slug' => 'license-issues',
                'description' => 'License activation and verification issues',
                'is_active' => true,
                'sort_order' => 2,
                'icon' => 'fas fa-key',
                'color' => '#28a745',
            ],
            [
                'name' => 'Billing & Payments',
                'slug' => 'billing-payments',
                'description' => 'Billing, payments, and invoice issues',
                'is_active' => true,
                'sort_order' => 3,
                'icon' => 'fas fa-credit-card',
                'color' => '#ffc107',
            ],
            [
                'name' => 'Feature Requests',
                'slug' => 'feature-requests',
                'description' => 'Feature requests and suggestions',
                'is_active' => true,
                'sort_order' => 4,
                'icon' => 'fas fa-lightbulb',
                'color' => '#17a2b8',
            ],
            [
                'name' => 'Bug Reports',
                'slug' => 'bug-reports',
                'description' => 'Bug reports and issues',
                'is_active' => true,
                'sort_order' => 5,
                'icon' => 'fas fa-bug',
                'color' => '#dc3545',
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
