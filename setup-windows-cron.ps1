# PowerShell script to setup Windows Task Scheduler for Laravel Cron
# Run this script as Administrator

Write-Host "Setting up Windows Task Scheduler for Laravel Cron..." -ForegroundColor Green

$taskName = "LaravelCron"
$taskDescription = "Laravel Cron Jobs for License and Invoice Processing"
$scriptPath = "D:\xampp1\htdocs\my-logos\cron-runner.bat"

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
