<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Email\MigrationHelper;
use Illuminate\Console\Command;

/**
 * Email Migration Command.
 *
 * Provides utilities to analyze and migrate from the old EmailService
 * to the new modular email system.
 *
 * @version 1.0.0
 */
class EmailMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:migrate 
                            {--analyze : Analyze the project for EmailService usage}
                            {--check : Check if the new email system is configured}
                            {--stats : Show usage statistics}
                            {--file= : Analyze a specific file}
                            {--generate-script : Generate migration script for a file}';

    /**
     * The console command description.
     */
    protected $description = 'Analyze and migrate from old EmailService to new modular email system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('analyze')) {
            return $this->analyzeProject();
        }

        if ($this->option('check')) {
            return $this->checkConfiguration();
        }

        if ($this->option('stats')) {
            return $this->showStatistics();
        }

        if ($this->option('file')) {
            return $this->analyzeFile();
        }

        if ($this->option('generate-script')) {
            return $this->generateScript();
        }

        $this->info('Email Migration Helper');
        $this->line('Use --help to see available options');
        
        return 0;
    }

    /**
     * Analyze the project for EmailService usage.
     */
    protected function analyzeProject(): int
    {
        $this->info('Analyzing project for EmailService usage...');
        
        $analysis = MigrationHelper::analyzeProject();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Files Analyzed', $analysis['files_analyzed']],
                ['Files with EmailService', count($analysis['files_with_email_service'])],
                ['Total Recommendations', $analysis['summary']['total_recommendations']],
            ]
        );

        if (!empty($analysis['files_with_email_service'])) {
            $this->warn('Files that use EmailService:');
            foreach ($analysis['files_with_email_service'] as $file) {
                $this->line('- ' . str_replace(base_path(), '', $file));
            }
        }

        return 0;
    }

    /**
     * Check if the new email system is configured.
     */
    protected function checkConfiguration(): int
    {
        $this->info('Checking new email system configuration...');
        
        $status = MigrationHelper::checkConfiguration();
        
        $this->table(
            ['Component', 'Status'],
            [
                ['Service Provider Registered', $status['service_provider_registered'] ? '✓' : '✗'],
                ['Facade Available', $status['facade_available'] ? '✓' : '✗'],
                ['Config File Exists', $status['config_file_exists'] ? '✓' : '✗'],
                ['Handlers Registered', $status['handlers_registered'] ? '✓' : '✗'],
            ]
        );

        if (!$status['service_provider_registered']) {
            $this->warn('Service provider not registered. Add to config/app.php providers array.');
        }

        if (!$status['facade_available']) {
            $this->warn('Facade not available. Check if EmailServiceProvider is registered.');
        }

        if (!$status['config_file_exists']) {
            $this->warn('Config file missing. Run: php artisan vendor:publish --tag=email-config');
        }

        return 0;
    }

    /**
     * Show usage statistics.
     */
    protected function showStatistics(): int
    {
        $this->info('EmailService Usage Statistics');
        
        $stats = MigrationHelper::getUsageStatistics();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total PHP Files', $stats['total_files']],
                ['Files with EmailService', $stats['files_with_email_service']],
                ['Import Statements', $stats['import_statements']],
            ]
        );

        if (!empty($stats['method_usage'])) {
            $this->info('Most Used Methods:');
            arsort($stats['method_usage']);
            foreach (array_slice($stats['method_usage'], 0, 5, true) as $method => $count) {
                $this->line("- {$method}: {$count} times");
            }
        }

        return 0;
    }

    /**
     * Analyze a specific file.
     */
    protected function analyzeFile(): int
    {
        $filePath = $this->option('file');
        
        if (!$filePath) {
            $this->error('Please specify a file with --file=path/to/file.php');
            return 1;
        }

        $this->info("Analyzing file: {$filePath}");
        
        $analysis = MigrationHelper::analyzeFile($filePath);
        
        if (isset($analysis['error'])) {
            $this->error($analysis['error']);
            return 1;
        }

        $this->table(
            ['Type', 'Message', 'Suggestion'],
            array_map(function ($rec) {
                return [
                    $rec['type'],
                    $rec['message'],
                    $rec['suggestion']
                ];
            }, $analysis['recommendations'])
        );

        return 0;
    }

    /**
     * Generate migration script for a file.
     */
    protected function generateScript(): int
    {
        $filePath = $this->option('file');
        
        if (!$filePath) {
            $this->error('Please specify a file with --file=path/to/file.php');
            return 1;
        }

        $this->info("Generating migration script for: {$filePath}");
        
        $script = MigrationHelper::generateMigrationScript($filePath);
        
        $outputFile = base_path('email_migration_' . date('Y-m-d_H-i-s') . '.php');
        file_put_contents($outputFile, $script);
        
        $this->info("Migration script generated: {$outputFile}");
        
        return 0;
    }
}
