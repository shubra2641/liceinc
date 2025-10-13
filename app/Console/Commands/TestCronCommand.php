<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\License;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Test Cron Command - Simple and reliable cron testing
 * 
 * This command tests all cron-related functionality without complexity
 */
class TestCronCommand extends Command
{
    protected $signature = 'cron:test {--type=all : Type of test (all, licenses, invoices)}';
    protected $description = 'Test cron functionality for licenses and invoices';

    public function handle(): int
    {
        $type = $this->option('type');
        
        $this->info('🔍 Testing Cron Functionality...');
        $this->newLine();
        
        try {
            switch ($type) {
                case 'licenses':
                    $this->testLicenses();
                    break;
                case 'invoices':
                    $this->testInvoices();
                    break;
                default:
                    $this->testLicenses();
                    $this->newLine();
                    $this->testInvoices();
            }
            
            $this->newLine();
            $this->info('✅ Cron test completed successfully!');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Cron test failed: ' . $e->getMessage());
            Log::error('Cron test failed', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }

    private function testLicenses(): void
    {
        $this->info('📋 Testing License Expiration...');
        
        // Count expiring licenses
        $expiringCount = License::where('license_expires_at', '<=', Carbon::now()->addDays(7))
            ->where('license_expires_at', '>', Carbon::now())
            ->where('status', 'active')
            ->count();
            
        $this->line("   • Licenses expiring in 7 days: {$expiringCount}");
        
        // Count expired licenses
        $expiredCount = License::where('license_expires_at', '<', Carbon::now())
            ->where('status', 'active')
            ->count();
            
        $this->line("   • Expired licenses: {$expiredCount}");
        
        // Count total active licenses
        $activeCount = License::where('status', 'active')->count();
        $this->line("   • Total active licenses: {$activeCount}");
    }

    private function testInvoices(): void
    {
        $this->info('💰 Testing Invoice Processing...');
        
        // Count pending invoices
        $pendingCount = Invoice::where('status', 'pending')->count();
        $this->line("   • Pending invoices: {$pendingCount}");
        
        // Count overdue invoices
        $overdueCount = Invoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->whereNotNull('due_date')
            ->count();
        $this->line("   • Overdue invoices: {$overdueCount}");
        
        // Count renewal invoices
        $renewalCount = Invoice::where('type', 'renewal')->count();
        $this->line("   • Renewal invoices: {$renewalCount}");
    }
}
