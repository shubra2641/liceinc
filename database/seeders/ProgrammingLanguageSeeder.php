<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProgrammingLanguage;
use Illuminate\Database\Seeder;

class ProgrammingLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'name' => 'PHP',
                'slug' => 'php',
                'description' => 'Server-side scripting language for web development',
                'file_extension' => '.php',
                'status' => 'active',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'JavaScript',
                'slug' => 'javascript',
                'description' => 'Client-side and server-side programming language',
                'file_extension' => '.js',
                'status' => 'active',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Python',
                'slug' => 'python',
                'description' => 'High-level programming language for various applications',
                'file_extension' => '.py',
                'status' => 'active',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Java',
                'slug' => 'java',
                'description' => 'Object-oriented programming language for enterprise applications',
                'file_extension' => '.java',
                'status' => 'active',
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'C#',
                'slug' => 'csharp',
                'description' => 'Microsoft programming language for .NET applications',
                'file_extension' => '.cs',
                'status' => 'active',
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'C++',
                'slug' => 'cpp',
                'description' => 'General-purpose programming language with object-oriented features',
                'file_extension' => '.cpp',
                'status' => 'active',
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HTML/CSS',
                'slug' => 'html-css',
                'description' => 'Markup and styling languages for web development',
                'file_extension' => '.html',
                'status' => 'active',
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'WordPress',
                'slug' => 'wordpress',
                'description' => 'Content management system with PHP-based plugins and themes',
                'file_extension' => '.php',
                'status' => 'active',
                'sort_order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Laravel',
                'slug' => 'laravel',
                'description' => 'PHP web application framework with elegant syntax',
                'file_extension' => '.php',
                'status' => 'active',
                'sort_order' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'React',
                'slug' => 'react',
                'description' => 'JavaScript library for building user interfaces',
                'file_extension' => '.jsx',
                'status' => 'active',
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vue.js',
                'slug' => 'vuejs',
                'description' => 'Progressive JavaScript framework for building UIs',
                'file_extension' => '.vue',
                'status' => 'active',
                'sort_order' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Angular',
                'slug' => 'angular',
                'description' => 'TypeScript-based web application framework',
                'file_extension' => '.ts',
                'status' => 'active',
                'sort_order' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Node.js',
                'slug' => 'nodejs',
                'description' => 'JavaScript runtime for server-side development',
                'file_extension' => '.js',
                'status' => 'active',
                'sort_order' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ruby',
                'slug' => 'ruby',
                'description' => 'Dynamic programming language with elegant syntax',
                'file_extension' => '.rb',
                'status' => 'active',
                'sort_order' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Go',
                'slug' => 'go',
                'description' => 'Google programming language for efficient software development',
                'file_extension' => '.go',
                'status' => 'active',
                'sort_order' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($languages as $language) {
            ProgrammingLanguage::create($language);
        }

        $this->command->info('Programming languages created successfully:');
        $this->command->info('- PHP, JavaScript, Python, Java, C#, C++');
        $this->command->info('- HTML/CSS, WordPress, Laravel, React');
        $this->command->info('- Vue.js, Angular, Node.js, Ruby, Go');
        $this->command->info('Total: 15 programming languages');
    }
}
