<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProgrammingLanguage;
use Illuminate\Database\Seeder;

class ProgrammingLanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['name' => 'PHP', 'slug' => 'php', 'description' => 'Server-side scripting language', 'file_extension' => '.php', 'status' => 'active', 'sort_order' => 1],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'description' => 'Client-side and server-side language', 'file_extension' => '.js', 'status' => 'active', 'sort_order' => 2],
            ['name' => 'Python', 'slug' => 'python', 'description' => 'High-level programming language', 'file_extension' => '.py', 'status' => 'active', 'sort_order' => 3],
            ['name' => 'Java', 'slug' => 'java', 'description' => 'Object-oriented programming language', 'file_extension' => '.java', 'status' => 'active', 'sort_order' => 4],
            ['name' => 'C#', 'slug' => 'csharp', 'description' => 'Microsoft .NET language', 'file_extension' => '.cs', 'status' => 'active', 'sort_order' => 5],
            ['name' => 'C++', 'slug' => 'cpp', 'description' => 'General-purpose language', 'file_extension' => '.cpp', 'status' => 'active', 'sort_order' => 6],
            ['name' => 'WordPress', 'slug' => 'wordpress', 'description' => 'PHP-based CMS', 'file_extension' => '.php', 'status' => 'active', 'sort_order' => 7],
            ['name' => 'React', 'slug' => 'react', 'description' => 'JavaScript UI library', 'file_extension' => '.jsx', 'status' => 'active', 'sort_order' => 8],
            ['name' => 'Angular', 'slug' => 'angular', 'description' => 'TypeScript framework', 'file_extension' => '.ts', 'status' => 'active', 'sort_order' => 9],
            ['name' => 'Node.js', 'slug' => 'nodejs', 'description' => 'JavaScript runtime', 'file_extension' => '.js', 'status' => 'active', 'sort_order' => 10],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'description' => 'Progressive JavaScript framework', 'file_extension' => '.vue', 'status' => 'active', 'sort_order' => 11],
            ['name' => 'Go', 'slug' => 'go', 'description' => 'Google programming language', 'file_extension' => '.go', 'status' => 'active', 'sort_order' => 12],
            ['name' => 'Swift', 'slug' => 'swift', 'description' => 'Apple iOS language', 'file_extension' => '.swift', 'status' => 'active', 'sort_order' => 13],
            ['name' => 'TypeScript', 'slug' => 'typescript', 'description' => 'Typed JavaScript', 'file_extension' => '.ts', 'status' => 'active', 'sort_order' => 14],
            ['name' => 'Kotlin', 'slug' => 'kotlin', 'description' => 'Android language', 'file_extension' => '.kt', 'status' => 'active', 'sort_order' => 15],
            ['name' => 'C', 'slug' => 'c', 'description' => 'General-purpose language', 'file_extension' => '.c', 'status' => 'active', 'sort_order' => 16],
            ['name' => 'HTML/CSS', 'slug' => 'html-css', 'description' => 'Web markup languages', 'file_extension' => '.html', 'status' => 'active', 'sort_order' => 17],
            ['name' => 'Flask', 'slug' => 'flask', 'description' => 'Python web framework', 'file_extension' => '.py', 'status' => 'active', 'sort_order' => 18],
            ['name' => 'Django', 'slug' => 'django', 'description' => 'Python web framework', 'file_extension' => '.py', 'status' => 'active', 'sort_order' => 19],
            ['name' => 'Express.js', 'slug' => 'expressjs', 'description' => 'Node.js framework', 'file_extension' => '.js', 'status' => 'active', 'sort_order' => 20],
            ['name' => 'Ruby on Rails', 'slug' => 'ruby-on-rails', 'description' => 'Ruby web framework', 'file_extension' => '.rb', 'status' => 'active', 'sort_order' => 21],
            ['name' => 'Spring Boot', 'slug' => 'spring-boot', 'description' => 'Java framework', 'file_extension' => '.java', 'status' => 'active', 'sort_order' => 22],
            ['name' => 'Symfony', 'slug' => 'symfony', 'description' => 'PHP framework', 'file_extension' => '.php', 'status' => 'active', 'sort_order' => 23],
            ['name' => 'ASP.NET', 'slug' => 'aspnet', 'description' => 'Microsoft framework', 'file_extension' => '.cs', 'status' => 'active', 'sort_order' => 24],
            ['name' => 'HTML', 'slug' => 'html', 'description' => 'Markup language', 'file_extension' => '.html', 'status' => 'active', 'sort_order' => 25],
            ['name' => 'Ruby', 'slug' => 'ruby', 'description' => 'Dynamic language', 'file_extension' => '.rb', 'status' => 'active', 'sort_order' => 26],
        ];

        foreach ($languages as $lang) {
            ProgrammingLanguage::firstOrCreate(['slug' => $lang['slug']], $lang);
        }
    }
}