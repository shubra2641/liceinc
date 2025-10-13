<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\License;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Cron Status Command - Monitor cron job status
 * 
 * Simple command to check the status of all cron-related processes
 */
class CronStatusCommand extends Command
{
    protected $signature = 'cron:status';
    protected $description = 'Check the status of all cron processes';

    public function handle(): int
    {
        $this->info('📊 Cron Status Report');
        $this->newLine();
        
        // License Status
        $this->info('🔑 License Status:');
        $this->displayLicenseStatus();
        $this->newLine();
        
        // Invoice Status  
        $this->info('💰 Invoice Status:');
        $this->displayInvoiceStatus();
        $this->newLine();
        
        // System Status
        $this->info('⚙️ System Status:');
        $this->displaySystemStatus();
        
        return Command::SUCCESS;
    }

    private function displayLicenseStatus(): void
    {
        $total = License::count();
        $active = License::where('status', 'active')->count();
        $expired = License::where('license_expires_at', '<', Carbon::now())->count();
        $expiring = License::where('license_expires_at', '<=', Carbon::now()->addDays(7))
            ->where('license_expires_at', '>', Carbon::now())
            ->count();
            
        $this->line("   Total Licenses: {$total}");
        $this->line("   Active: {$active}");
        $this->line("   Expired: {$expired}");
        $this->line("   Expiring (7 days): {$expiring}");
    }

    private function displayInvoiceStatus(): void
    {
        $total = Invoice::count();
        $pending = Invoice::where('status', 'pending')->count();
        $overdue = Invoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->whereNotNull('due_date')
            ->count();
        $renewal = Invoice::where('type', 'renewal')->count();
        
        $this->line("   Total Invoices: {$total}");
        $this->line("   Pending: {$pending}");
        $this->line("   Overdue: {$overdue}");
        $this->line("   Renewal: {$renewal}");
    }

    private function displaySystemStatus(): void
    {
        $this->line("   Database: " . (DB::connection()->getPdo() ? '✅ Connected' : '❌ Disconnected'));
        $this->line("   Time: " . Carbon::now()->format('Y-m-d H:i:s'));
        $this->line("   Timezone: " . config('app.timezone'));
    }
}
