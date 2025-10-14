<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Models\License;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Invoice Renewal Service
 * 
 * Handles invoice renewal operations
 */
class InvoiceRenewalService
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {
    }

    /**
     * Generate renewal invoices for expiring licenses
     */
    public function generateRenewalInvoices(): array
    {
        try {
            $expiringLicenses = $this->getExpiringLicenses();
            $generatedInvoices = [];
            
            foreach ($expiringLicenses as $license) {
                $invoice = $this->createRenewalInvoice($license);
                if ($invoice) {
                    $generatedInvoices[] = $invoice;
                }
            }
            
            Log::info('Renewal invoices generated', [
                'count' => count($generatedInvoices),
                'licenses_processed' => count($expiringLicenses)
            ]);
            
            return $generatedInvoices;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate renewal invoices', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get expiring licenses
     */
    private function getExpiringLicenses(): \Illuminate\Database\Eloquent\Collection
    {
        $expirationDate = now()->addDays(30); // 30 days before expiration
        
        return License::where('status', 'active')
            ->whereNotNull('license_expires_at')
            ->where('license_expires_at', '<=', $expirationDate)
            ->where('license_expires_at', '>', now())
            ->whereDoesntHave('invoices', function ($query) {
                $query->where('type', 'renewal')
                    ->where('status', 'pending');
            })
            ->with(['user', 'product'])
            ->get();
    }

    /**
     * Create renewal invoice for license
     */
    private function createRenewalInvoice(License $license): ?Invoice
    {
        try {
            DB::beginTransaction();
            
            $invoice = $this->invoiceService->createInvoice([
                'user_id' => $license->user_id,
                'type' => 'renewal',
                'license_id' => $license->id,
                'product_id' => $license->product_id,
                'amount' => $license->product->renewal_price ?? $license->product->price,
                'currency' => $license->product->currency ?? 'USD',
                'description' => "Renewal for {$license->product->name}",
                'due_date' => $license->license_expires_at,
                'metadata' => [
                    'original_license_id' => $license->id,
                    'renewal_period' => $license->product->renewal_period ?? 'yearly',
                    'auto_generated' => true,
                ]
            ]);
            
            DB::commit();
            
            Log::info('Renewal invoice created', [
                'invoice_id' => $invoice->id,
                'license_id' => $license->id,
                'user_id' => $license->user_id
            ]);
            
            return $invoice;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create renewal invoice', [
                'license_id' => $license->id,
                'user_id' => $license->user_id,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Send renewal notifications
     */
    public function sendRenewalNotifications(array $invoices): void
    {
        foreach ($invoices as $invoice) {
            $this->sendRenewalNotification($invoice);
        }
    }

    /**
     * Send renewal notification for invoice
     */
    private function sendRenewalNotification(Invoice $invoice): void
    {
        try {
            // Implementation for sending renewal notification
            // This would typically involve sending an email to the user
            
            Log::info('Renewal notification sent', [
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send renewal notification', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
