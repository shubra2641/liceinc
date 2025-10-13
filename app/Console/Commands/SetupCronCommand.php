<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupCronCommand extends Command
{
    protected $signature = 'cron:setup {--platform=auto : Platform (windows, linux, auto)}';
    protected $description = 'Setup cron jobs for the application';

    public function handle(): int
    {
        $platform = $this->option('platform');
        if ($platform === 'auto') {
            $platform = PHP_OS_FAMILY === 'Windows' ? 'windows' : 'linux';
        }
        
        $this->info('🔧 Setting up Cron Jobs...');
        $this->newLine();

        if ($platform === 'windows') {
            return $this->setupWindowsCron();
        } else {
            return $this->setupLinuxCron();
        }
    }

    private function setupWindowsCron(): int
    {
        $this->info('🪟 Setting up Windows Task Scheduler...');
        
        // Create batch file for Windows
        $batchContent = $this->generateWindowsBatch();
        $batchFile = base_path('run_cron.bat');
        
        File::put($batchFile, $batchContent);
        $this->info("✅ Created batch file: {$batchFile}");
        
        $this->newLine();
        $this->warn('📋 Next Steps:');
        $this->line('   1. Open Task Scheduler');
        $this->line('   2. Create Basic Task');
        $this->line('   3. Set trigger to "Daily"');
        $this->line('   4. Set action to run: ' . $batchFile);
        $this->line('   5. Set to run every 1 minute');
        
        return 0;
    }

    private function setupLinuxCron(): int
    {
        $this->info('🐧 Setting up Linux Cron...');
        
        // Generate cron entries
        $cronEntries = $this->generateCronEntries();
        $cronFile = base_path('crontab.txt');
        
        File::put($cronFile, $cronEntries);
        $this->info("✅ Created crontab file: {$cronFile}");
        
        $this->newLine();
        $this->warn('📋 Next Steps:');
        $this->line('   1. Run: crontab -e');
        $this->line('   2. Add the contents of crontab.txt');
        $this->line('   3. Or run: crontab crontab.txt');
        
        return 0;
    }

    private function generateWindowsBatch(): string
    {
        $phpPath = PHP_BINARY;
        $artisanPath = base_path('artisan');
        
        return "@echo off\n" .
               "cd /d \"" . base_path() . "\"\n" .
               "\"{$phpPath}\" \"{$artisanPath}\" schedule:run\n" .
               "timeout /t 60 /nobreak\n" .
               "goto :eof\n";
    }

    private function generateCronEntries(): string
    {
        $phpPath = PHP_BINARY;
        $artisanPath = base_path('artisan');
        
        return "# Laravel Cron Jobs\n" .
               "# Run every minute\n" .
               "* * * * * cd " . base_path() . " && {$phpPath} {$artisanPath} schedule:run >> /dev/null 2>&1\n" .
               "\n" .
               "# Optional: Log cron output\n" .
               "# * * * * * cd " . base_path() . " && {$phpPath} {$artisanPath} schedule:run >> " . storage_path('logs/cron.log') . " 2>&1\n";
    }
}