<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SebastianBergmann\Complexity\Calculator;
use SebastianBergmann\Complexity\ComplexityCollection;

class AnalyzeComplexityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complexity:analyze 
                            {path? : Path to analyze (default: app)}
                            {--threshold=10 : Complexity threshold}
                            {--format=table : Output format (table|json)}
                            {--sort=complexity : Sort by (complexity|name|file)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze code complexity using Sebastian/Complexity library';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path') ?? 'app';
        $threshold = (int) $this->option('threshold');
        $format = $this->option('format');
        $sortBy = $this->option('sort');

        $this->info("🔍 Analyzing complexity for: {$path}");
        $this->info("📊 Threshold: {$threshold}");
        $this->newLine();

        $complexities = $this->analyzeDirectory($path);
        
        if (empty($complexities)) {
            $this->warn('No PHP files found to analyze.');
            return;
        }

        if ($format === 'json') {
            $this->outputJson($complexities, $threshold);
        } else {
            $this->outputTable($complexities, $threshold, $sortBy);
        }

        $this->newLine();
        $this->displaySummary($complexities, $threshold);
    }

    /**
     * Analyze all PHP files in the given directory
     */
    private function analyzeDirectory(string $path): array
    {
        $files = $this->getPhpFiles($path);
        $allComplexities = [];

        $this->info("📁 Found " . count($files) . " PHP files to analyze...");
        
        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->start();

        foreach ($files as $file) {
            try {
                $calculator = new Calculator();
                $complexities = $calculator->calculateForSourceFile($file);
                
                foreach ($complexities as $complexity) {
                    $allComplexities[] = [
                        'file' => $this->getRelativePath($file),
                        'full_path' => $file,
                        'name' => $complexity->name(),
                        'complexity' => $complexity->cyclomaticComplexity(),
                        'is_method' => $complexity->isMethod(),
                        'type' => $complexity->isMethod() ? 'Method' : 'Function',
                    ];
                }
            } catch (\Exception $e) {
                $this->warn("⚠️  Could not analyze {$file}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        return $allComplexities;
    }

    /**
     * Get all PHP files in the directory
     */
    private function getPhpFiles(string $path): array
    {
        $files = [];
        
        if (!is_dir($path)) {
            if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                return [$path];
            }
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Output results as table
     */
    private function outputTable(array $complexities, int $threshold, string $sortBy): void
    {
        $headers = ['File', 'Type', 'Name', 'Complexity', 'Status'];
        $rows = [];

        foreach ($complexities as $item) {
            $status = $this->getStatusIcon($item['complexity'], $threshold);
            $rows[] = [
                $item['file'],
                $item['type'],
                $item['name'],
                $item['complexity'],
                $status
            ];
        }

        // Sort the results
        $this->sortResults($rows, $complexities, $sortBy);

        $this->table($headers, $rows);
    }

    /**
     * Output results as JSON
     */
    private function outputJson(array $complexities, int $threshold): void
    {
        $highComplexity = array_filter($complexities, fn($item) => $item['complexity'] > $threshold);
        
        $result = [
            'analysis' => [
                'threshold' => $threshold,
                'total_analyzed' => count($complexities),
                'high_complexity_count' => count($highComplexity),
                'average_complexity' => round(array_sum(array_column($complexities, 'complexity')) / count($complexities), 2),
            ],
            'high_complexity' => array_values($highComplexity),
            'all_complexities' => $complexities
        ];

        $this->line(json_encode($result, JSON_PRETTY_PRINT));
    }

    /**
     * Display summary statistics
     */
    private function displaySummary(array $complexities, int $threshold): void
    {
        $highComplexity = array_filter($complexities, fn($item) => $item['complexity'] > $threshold);
        $averageComplexity = round(array_sum(array_column($complexities, 'complexity')) / count($complexities), 2);
        $maxComplexity = max(array_column($complexities, 'complexity'));

        $this->info("📈 Summary:");
        $this->line("   • Total analyzed: " . count($complexities));
        $this->line("   • High complexity (>{$threshold}): " . count($highComplexity));
        $this->line("   • Average complexity: {$averageComplexity}");
        $this->line("   • Maximum complexity: {$maxComplexity}");

        if (count($highComplexity) > 0) {
            $this->newLine();
            $this->warn("⚠️  High complexity methods/functions found:");
            foreach ($highComplexity as $item) {
                $this->line("   • {$item['file']} - {$item['name']} (Complexity: {$item['complexity']})");
            }
        }
    }

    /**
     * Get status icon based on complexity
     */
    private function getStatusIcon(int $complexity, int $threshold): string
    {
        if ($complexity <= $threshold) {
            return '✅ OK';
        } elseif ($complexity <= $threshold * 1.5) {
            return '⚠️  Medium';
        } else {
            return '🚨 High';
        }
    }

    /**
     * Sort results based on the specified criteria
     */
    private function sortResults(array &$rows, array $complexities, string $sortBy): void
    {
        switch ($sortBy) {
            case 'name':
                usort($rows, fn($a, $b) => strcmp($a[2], $b[2]));
                break;
            case 'file':
                usort($rows, fn($a, $b) => strcmp($a[0], $b[0]));
                break;
            case 'complexity':
            default:
                usort($rows, fn($a, $b) => $b[3] <=> $a[3]);
                break;
        }
    }

    /**
     * Get relative path from project root
     */
    private function getRelativePath(string $fullPath): string
    {
        $projectRoot = base_path();
        return str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $fullPath);
    }
}
