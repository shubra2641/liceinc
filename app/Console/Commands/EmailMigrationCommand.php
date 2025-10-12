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
     *
     * @return int
     */
    protected function analyzeProject(): int
    {
        $this->info('Analyzing project for EmailService usage...');

        $analysis = MigrationHelper::analyzeProject();

        $filesAnalyzed = $analysis['files_analyzed'] ?? 0;
        $filesWithEmailService = $analysis['files_with_email_service'] ?? [];
        $summary = $analysis['summary'] ?? [];
        $totalRecommendations = is_array($summary) && isset($summary['total_recommendations'])
            ? $summary['total_recommendations'] : 0;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Files Analyzed', $filesAnalyzed],
                ['Files with EmailService', is_array($filesWithEmailService) ? count($filesWithEmailService) : 0],
                ['Total Recommendations', $totalRecommendations],
            ]
        );

        if (!empty($filesWithEmailService) && is_array($filesWithEmailService)) {
            $this->warn('Files that use EmailService:');
            foreach ($filesWithEmailService as $file) {
                $filePath = is_string($file) ? $file : '';
                $this->line('- ' . str_replace(base_path(), '', $filePath));
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
     *
     * @return int
     */
    protected function showStatistics(): int
    {
        $this->info('EmailService Usage Statistics');

        $stats = MigrationHelper::getUsageStatistics();

        $totalFiles = $stats['total_files'] ?? 0;
        $filesWithEmailService = $stats['files_with_email_service'] ?? 0;
        $importStatements = $stats['import_statements'] ?? 0;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total PHP Files', $totalFiles],
                ['Files with EmailService', $filesWithEmailService],
                ['Import Statements', $importStatements],
            ]
        );

        $methodUsage = $stats['method_usage'] ?? [];

        if (!empty($methodUsage) && is_array($methodUsage)) {
            $this->info('Most Used Methods:');
            arsort($methodUsage);
            $topMethods = array_slice($methodUsage, 0, 5, true);
            foreach ($topMethods as $method => $count) {
                $methodName = is_string($method) ? $method : 'unknown';
                $countValue = is_numeric($count) ? $count : 0;
                $this->line("- {$methodName}: {$countValue} times");
            }
        }

        return 0;
    }

    /**
     * Analyze a specific file.
     *
     * @return int
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
            $errorMessage = is_string($analysis['error']) ? $analysis['error'] : 'Unknown error';
            $this->error($errorMessage);
            return 1;
        }

        $recommendations = $analysis['recommendations'] ?? [];

        if (!empty($recommendations) && is_array($recommendations)) {
            $this->table(
                ['Type', 'Message', 'Suggestion'],
                array_map(function ($rec) {
                    if (!is_array($rec)) {
                        return ['Unknown', 'Invalid recommendation', 'N/A'];
                    }
                    return [
                        $rec['type'] ?? 'Unknown',
                        $rec['message'] ?? 'No message',
                        $rec['suggestion'] ?? 'No suggestion'
                    ];
                }, $recommendations)
            );
        }

        return 0;
    }

    /**
     * Generate migration script for a file.
     *
     * @return int
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

        if (empty($script)) {
            $this->error('Failed to generate migration script');
            return 1;
        }

        $outputFile = base_path('email_migration_' . date('Y-m-d_H-i-s') . '.php');
        $result = file_put_contents($outputFile, $script);

        if ($result === false) {
            $this->error('Failed to write migration script to file');
            return 1;
        }

        $this->info("Migration script generated: {$outputFile}");

        return 0;
    }
}
