@echo off
echo Starting Laravel Cron Runner...
echo Time: %date% %time%

cd /d "D:\xampp1\htdocs\my-logos"

echo Running license renewal invoices...
php artisan licenses:generate-renewal-invoices --days=7

echo Running invoice processing...
php artisan invoices:process

echo Running cron status check...
php artisan cron:status

