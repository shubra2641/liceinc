<?php

declare(strict_types=1);

namespace App\Services\Email;

/**
 * Email Service Migration Helper.
 *
 * Provides utilities to help migrate from the old EmailService
 * to the new modular email system.
 *
 * @version 1.0.0
 */
class MigrationHelper
{
    /**
     * Get migration recommendations for a file.
     *
     * @param string $filePath Path to the file to analyze
     *
     * @return array<string, mixed> Migration recommendations
     */
    public static function analyzeFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['error' => 'File not found: ' . $filePath];
        }

        $content = file_get_contents($filePath);
        $recommendations = [];

        // Check for EmailService usage
        if (is_string($content) && strpos($content, 'EmailService') !== false) {
            $recommendations[] = [
                'type' => 'class_usage',
                'message' => 'Consider using EmailFacade instead of EmailService',
                'suggestion' => 'Replace EmailService with EmailFacade for better type safety',
                'example' => [
                    'old' => 'use App\Services\EmailService;',
                    'new' => 'use App\Services\Email\Facades\Email;'
                ]
            ];
        }

        // Check for direct method calls
        $methods = [
            'sendEmail' => 'Email::sendEmail()',
            'sendToUser' => 'Email::sendToUser()',
            'sendToAdmin' => 'Email::sendToAdmin()',
            'sendBulkEmail' => 'Email::sendBulkEmail()',
            'sendUserWelcome' => 'Email::sendUserWelcome()',
            'sendEmailVerification' => 'Email::sendEmailVerification()',
            'sendPasswordReset' => 'Email::sendPasswordReset()',
        ];

        foreach ($methods as $method => $suggestion) {
            if (is_string($content) && strpos($content, $method) !== false) {
                $recommendations[] = [
                    'type' => 'method_usage',
                    'message' => "Method {$method} can be called directly on Email facade",
                    'suggestion' => "Use {$suggestion} instead of \$emailService->{$method}()",
                    'example' => [
                        'old' => "\$emailService->{$method}(...);",
                        'new' => "Email::{$method}(...);"
                    ]
                ];
            }
        }

        return [
            'file' => $filePath,
            'recommendations' => $recommendations,
            'total_recommendations' => count($recommendations)
        ];
    }

    /**
     * Get migration recommendations for the entire project.
     *
     * @return array<string, mixed> Project-wide migration recommendations
     */
    public static function analyzeProject(): array
    {
        $projectPath = base_path();
        $recommendations = [];
        $files = [];

        // Find all PHP files that might use EmailService
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($projectPath . '/app')
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                $content = file_get_contents($filePath);

                if (is_string($content) && strpos($content, 'EmailService') !== false) {
                    $files[] = $filePath;
                    $recommendations[] = self::analyzeFile($filePath);
                }
            }
        }

        return [
            'project_path' => $projectPath,
            'files_analyzed' => count($files),
            'files_with_email_service' => $files,
            'recommendations' => $recommendations,
            'summary' => [
                'total_files' => count($files),
                'total_recommendations' => array_sum(array_column($recommendations, 'total_recommendations'))
            ]
        ];
    }

    /**
     * Generate migration script for a specific file.
     *
     * @param string $filePath Path to the file to migrate
     *
     * @return string Generated migration script
     */
    public static function generateMigrationScript(string $filePath): string
    {
        $analysis = self::analyzeFile($filePath);

        if (isset($analysis['error']) && is_string($analysis['error'])) {
            return "// Error: {$analysis['error']}\n";
        }

        $script = "<?php\n\n";
        $script .= "// Migration script for: {$filePath}\n";
        $script .= "// Generated on: " . date('Y-m-d H:i:s') . "\n\n";

        if (isset($analysis['recommendations']) && is_array($analysis['recommendations'])) {
            foreach ($analysis['recommendations'] as $recommendation) {
                if (is_array($recommendation)) {
                    $message = isset($recommendation['message']) && is_string($recommendation['message'])
                        ? $recommendation['message']
                        : '';
                    $suggestion = isset($recommendation['suggestion']) && is_string($recommendation['suggestion'])
                        ? $recommendation['suggestion']
                        : '';
                    $oldExample = '';
                    $newExample = '';
                    if (isset($recommendation['example']) && is_array($recommendation['example'])) {
                        $oldExample = isset($recommendation['example']['old'])
                            && is_string($recommendation['example']['old'])
                            ? $recommendation['example']['old']
                            : '';
                        $newExample = isset($recommendation['example']['new'])
                            && is_string($recommendation['example']['new'])
                            ? $recommendation['example']['new']
                            : '';
                    }

                    $script .= "// {$message}\n";
                    $script .= "// {$suggestion}\n";
                    $script .= "// Old: {$oldExample}\n";
                    $script .= "// New: {$newExample}\n\n";
                }
            }
        }

        return $script;
    }

    /**
     * Check if the new email system is properly configured.
     *
     * @return array<string, mixed> Configuration status
     */
    public static function checkConfiguration(): array
    {
        $status = [
            'service_provider_registered' => false,
            'facade_available' => false,
            'config_file_exists' => false,
            'handlers_registered' => false,
        ];

        // Check if service provider is registered
        $providers = config('app.providers', []);
        $status['service_provider_registered'] = is_array($providers) && in_array(
            \App\Services\Email\EmailServiceProvider::class,
            $providers
        );

        // Check if facade is available
        $status['facade_available'] = class_exists(\App\Services\Email\Facades\Email::class);

        // Check if config file exists
        $status['config_file_exists'] = file_exists(config_path('email_services.php'));

        // Check if handlers are registered
        $status['handlers_registered'] = app()->bound(\App\Services\Email\Handlers\UserEmailHandler::class);

        return $status;
    }

    /**
     * Get usage statistics for the old EmailService.
     *
     * @return array<string, mixed> Usage statistics
     */
    public static function getUsageStatistics(): array
    {
        $projectPath = base_path();
        $stats = [
            'total_files' => 0,
            'files_with_email_service' => 0,
            'method_usage' => [],
            'import_statements' => 0,
        ];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($projectPath . '/app')
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                $stats['total_files']++;
                $filePath = $file->getPathname();
                $content = file_get_contents($filePath);

                if (is_string($content) && strpos($content, 'EmailService') !== false) {
                    $stats['files_with_email_service']++;

                    // Count import statements
                    if (strpos($content, 'use App\Services\EmailService') !== false) {
                        $stats['import_statements']++;
                    }

                    // Count method usage
                    $methods = [
                        'sendEmail', 'sendToUser', 'sendToAdmin', 'sendBulkEmail',
                        'sendUserWelcome', 'sendEmailVerification', 'sendPasswordReset',
                        'sendNewUserNotification', 'sendPaymentConfirmation'
                    ];

                    foreach ($methods as $method) {
                        $count = substr_count($content, $method);
                        if ($count > 0) {
                            $stats['method_usage'][$method] = ($stats['method_usage'][$method] ?? 0) + $count;
                        }
                    }
                }
            }
        }

        return $stats;
    }
}
