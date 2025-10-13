<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Setup Cron Command - Simple cron setup
 * 
 * This command helps setup cron jobs for Windows and Linux
 */
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
        
        try {
            switch ($platform) {
                case 'windows':
                    $this->setupWindowsCron();
                    break;
                case 'linux':
                    $this->setupLinuxCron();
                    break;
                default:
                    $this->error('❌ Unsupported platform: ' . $platform);
                    return Command::FAILURE;
            }
            
            $this->newLine();
            $this->info('✅ Cron setup completed successfully!');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Setup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function setupWindowsCron(): void
    {
        $this->info('🪟 Setting up Windows Task Scheduler...');
        
        // Create batch file
        $batchContent = '@echo off
echo Starting Laravel Cron Runner...
echo Time: %date% %time%

cd /d "' . base_path() . '"

echo Running license renewal invoices...
php artisan licenses:generate-renewal-invoices --days=7

echo Running invoice processing...
php artisan invoices:process

echo Running cron status check...
php artisan cron:status

echo Cron runner completed at %date% %time%';

        File::put(base_path('cron-runner.bat'), $batchContent);
        $this->line('   ✅ Created cron-runner.bat');
        
        // Create PowerShell setup script
        $psContent = '# PowerShell script to setup Windows Task Scheduler for Laravel Cron
# Run this script as Administrator

Write-Host "Setting up Windows Task Scheduler for Laravel Cron..." -ForegroundColor Green

$taskName = "LaravelCron"
$taskDescription = "Laravel Cron Jobs for License and Invoice Processing"
$scriptPath = "' . base_path() . '\cron-runner.bat"

# Remove existing task if it exists
try {
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false -ErrorAction SilentlyContinue
    Write-Host "Removed existing task: $taskName" -ForegroundColor Yellow
}
catch {
    Write-Host "No existing task found" -ForegroundColor Gray
}

# Create new task
$action = New-ScheduledTaskAction -Execute $scriptPath
$trigger = New-ScheduledTaskTrigger -Daily -At "08:00"
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable

Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -Description $taskDescription

Write-Host "✅ Task created successfully!" -ForegroundColor Green
Write-Host "Task Name: $taskName" -ForegroundColor Cyan
Write-Host "Schedule: Daily at 8:00 AM" -ForegroundColor Cyan
Write-Host "Script: $scriptPath" -ForegroundColor Cyan

Write-Host "`n✅ Setup completed! The cron job will run daily at 8:00 AM" -ForegroundColor Green
Write-Host "You can manage this task in Windows Task Scheduler" -ForegroundColor Gray';

        File::put(base_path('setup-windows-cron.ps1'), $psContent);
        $this->line('   ✅ Created setup-windows-cron.ps1');
        
        $this->newLine();
        $this->warn('📋 Next Steps:');
        $this->line('   1. Run PowerShell as Administrator');
        $this->line('   2. Execute: .\\setup-windows-cron.ps1');
        $this->line('   3. Or manually run: cron-runner.bat to test');
    }

    private function setupLinuxCron(): void
    {
        $this->info('🐧 Setting up Linux Cron...');
        
        $cronEntry = '0 8 * * * cd ' . base_path() . ' && php artisan licenses:generate-renewal-invoices --days=7 >> /dev/null 2>&1
0 9 * * * cd ' . base_path() . ' && php artisan invoices:process >> /dev/null 2>&1
0 * * * * cd ' . base_path() . ' && php artisan invoices:process --overdue >> /dev/null 2>&1';
        
        File::put(base_path('crontab.txt'), $cronEntry);
        $this->line('   ✅ Created crontab.txt');
        
        $this->newLine();
        $this->warn('📋 Next Steps:');
        $this->line('   1. Run: crontab -e');
        $this->line('   2. Add the contents of crontab.txt');
        $this->line('   3. Or run: crontab crontab.txt');
    }
}