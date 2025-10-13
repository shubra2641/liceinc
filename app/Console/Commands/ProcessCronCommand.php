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
 * Process Cron Command - Simple cron processing
 * 
 * This command processes all cron tasks in a simple, reliable way
 */
class ProcessCronCommand extends Command
{
    protected $signature = 'cron:process {--type=all : Type to process (all, licenses, invoices)}';
    protected $description = 'Process all cron tasks';

    public function handle(): int
    {
        $type = $this->option('type');
        
        $this->info('🚀 Processing Cron Tasks...');
        $this->newLine();
        
        try {
            DB::beginTransaction();
            
            $processed = 0;
            
            if ($type === 'all' || $type === 'licenses') {
                $processed += $this->processExpiredLicenses();
            }
            
            if ($type === 'all' || $type === 'invoices') {
                $processed += $this->processOverdueInvoices();
                $processed += $this->processPaidInvoices();
                $processed += $this->generateRenewalInvoices();
            }
            
            DB::commit();
            
            $this->newLine();
            $this->info("✅ Processed {$processed} items successfully!");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Processing failed: ' . $e->getMessage());
            Log::error('Cron processing failed', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }

    private function processExpiredLicenses(): int
    {
        $this->info('🔑 Processing Expired Licenses...');
        
        $expiredLicenses = License::where('license_expires_at', '<', Carbon::now())
            ->where('status', 'active')
            ->get();
            
        $processed = 0;
        
        foreach ($expiredLicenses as $license) {
            try {
                $license->update(['status' => 'expired']);
                $processed++;
                $this->line("   • Expired license: {$license->license_key}");
            } catch (\Exception $e) {
                Log::warning("Failed to expire license {$license->license_key}", ['error' => $e->getMessage()]);
            }
        }
        
        $this->line("   Processed: {$processed} expired licenses");
        return $processed;
    }

    private function processOverdueInvoices(): int
    {
        $this->info('💰 Processing Overdue Invoices...');
        
        $overdueInvoices = Invoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->whereNotNull('due_date')
            ->get();
            
        $processed = 0;
        
        foreach ($overdueInvoices as $invoice) {
            try {
                $invoice->update(['status' => 'overdue']);
                $processed++;
                $this->line("   • Overdue invoice: {$invoice->invoice_number}");
            } catch (\Exception $e) {
                Log::warning("Failed to process overdue invoice {$invoice->invoice_number}", ['error' => $e->getMessage()]);
            }
        }
        
        $this->line("   Processed: {$processed} overdue invoices");
        return $processed;
    }

    private function processPaidInvoices(): int
    {
        $this->info('✅ Processing Paid Invoices...');
        
        $paidInvoices = Invoice::where('status', 'paid')
            ->where('type', 'renewal')
            ->where('paid_at', '>=', Carbon::now()->subDays(1))
            ->with('license')
            ->get();
            
        $processed = 0;
        
        foreach ($paidInvoices as $invoice) {
            try {
                $license = $invoice->license;
                if ($license && $license->status === 'expired') {
                    // Extend license
                    $newExpiryDate = $this->calculateNewExpiryDate($license);
                    $license->update([
                        'status' => 'active',
                        'license_expires_at' => $newExpiryDate
                    ]);
                    $processed++;
                    $this->line("   • Renewed license: {$license->license_key}");
                    
                    Log::info('License renewed via paid invoice', [
                        'license_id' => $license->id,
                        'license_key' => $license->license_key,
                        'invoice_id' => $invoice->id,
                        'new_expiry_date' => $newExpiryDate
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("Failed to renew license for invoice {$invoice->invoice_number}",
                    ['error' => $e->getMessage()]);
            }
        }
        
        $this->line("   Processed: {$processed} paid invoices");
        return $processed;
    }

    private function calculateNewExpiryDate(License $license): Carbon
    {
        $currentExpiry = $license->license_expires_at ?? Carbon::now();
        $product = $license->product;
        
        if ($product && $product->renewal_period) {
            switch ($product->renewal_period) {
                case 'monthly':
                    return $currentExpiry->copy()->addMonth();
                case 'quarterly':
                    return $currentExpiry->copy()->addMonths(3);
                case 'semi-annual':
                    return $currentExpiry->copy()->addMonths(6);
                case 'annual':
                    return $currentExpiry->copy()->addYear();
                case 'three-years':
                    return $currentExpiry->copy()->addYears(3);
                case 'lifetime':
                    return $currentExpiry->copy()->addYears(100);
            }
        }
        
        // Default to product duration
        $durationDays = $product->duration_days ?? 365;
        return $currentExpiry->copy()->addDays($durationDays);
    }

    private function generateRenewalInvoices(): int
    {
        $this->info('🔄 Generating Renewal Invoices...');
        
        $expiringLicenses = License::where('license_expires_at', '<=', Carbon::now()->addDays(7))
            ->where('license_expires_at', '>', Carbon::now())
            ->where('status', 'active')
            ->with(['product', 'invoices'])
            ->get();
            
        $processed = 0;
        
        foreach ($expiringLicenses as $license) {
            try {
                // Check if renewal invoice already exists
                $existingInvoice = $license->invoices()
                    ->where('type', 'renewal')
                    ->where('status', 'pending')
                    ->first();
                    
                if (!$existingInvoice && $license->product) {
                    $renewalPrice = $license->product->renewal_price ?? $license->product->price ?? 0;
                    
                    if ($renewalPrice > 0) {
                        // Create renewal invoice
                        $invoice = Invoice::create([
                            'user_id' => $license->user_id,
                            'license_id' => $license->id,
                            'product_id' => $license->product_id,
                            'invoice_number' => 'REN-' . time() . '-' . $license->id,
                            'type' => 'renewal',
                            'status' => 'pending',
                            'amount' => $renewalPrice,
                            'currency' => 'USD',
                            'due_date' => Carbon::now()->addDays(30),
                            'notes' => "Renewal for {$license->product->name} - License {$license->license_key}",
                        ]);
                        
                        $processed++;
                        $this->line("   • Created renewal invoice: {$invoice->invoice_number}");
                        
                        // Log the creation
                        Log::info('Renewal invoice created', [
                            'license_id' => $license->id,
                            'license_key' => $license->license_key,
                            'invoice_id' => $invoice->id,
                            'amount' => $renewalPrice
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Failed to create renewal invoice for license {$license->license_key}", ['error' => $e->getMessage()]);
            }
        }
        
        $this->line("   Generated: {$processed} renewal invoices");
        return $processed;
    }
}
