<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Models\License;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceManagementService
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {
    }

    public function getFilteredInvoices(Request $request)
    {
        $query = Invoice::with(['user', 'product', 'license']);
        
        $this->applyStatusFilter($query, $request);
        $this->applyDateFilters($query, $request);
        
        return $query->latest()->paginate(10);
    }

    public function createInvoice(array $validated): Invoice
    {
        $isCustomInvoice = $validated['license_id'] === 'custom';
        $license = null;
        $productId = null;
        
        if (!$isCustomInvoice) {
            $license = $this->getLicense($validated['license_id']);
            $productId = $license->product_id;
        }
        
        $invoiceNumber = $validated['invoice_number'] ?? $this->generateInvoiceNumber();
        
        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'user_id' => $validated['user_id'],
            'license_id' => $isCustomInvoice ? null : $validated['license_id'],
            'product_id' => $productId,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'status' => $validated['status'],
            'due_date' => $validated['due_date'],
            'paid_at' => $validated['status'] === 'paid' ? ($validated['paid_at'] ?? now()) : null,
            'notes' => $validated['notes'] ?? null,
            'metadata' => $this->buildMetadata($isCustomInvoice, $validated),
        ]);
    }

    public function updateInvoice(Invoice $invoice, array $validated): Invoice
    {
        $isCustomInvoice = $validated['license_id'] === 'custom';
        $license = null;
        $productId = null;
        
        if (!$isCustomInvoice) {
            $license = $this->getLicense($validated['license_id']);
            $productId = $license->product_id;
        }
        
        $invoice->update([
            'user_id' => $validated['user_id'],
            'license_id' => $isCustomInvoice ? null : $validated['license_id'],
            'product_id' => $productId,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'status' => $validated['status'],
            'due_date' => $validated['due_date'],
            'paid_at' => $validated['status'] === 'paid' ? ($validated['paid_at'] ?? now()) : null,
            'notes' => $validated['notes'],
            'metadata' => $this->buildMetadata($isCustomInvoice, $validated),
        ]);
        
        return $invoice;
    }

    public function markAsPaid(Invoice $invoice): void
    {
        $this->invoiceService->markAsPaid($invoice);
    }

    public function cancelInvoice(Invoice $invoice): void
    {
        $invoice->update(['status' => 'cancelled']);
    }

    public function deleteInvoice(Invoice $invoice): void
    {
        if ($invoice->status === 'paid') {
            throw new \Exception('Cannot delete a paid invoice');
        }
        
        $invoice->delete();
    }

    public function generateInvoiceNumber(): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
            $attempts++;

            if ($attempts > $maxAttempts) {
                throw new \Exception('Failed to generate unique invoice number after ' . $maxAttempts . ' attempts');
            }
        } while (Invoice::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }

    private function applyStatusFilter($query, Request $request): void
    {
        if ($request->filled('status')) {
            $status = trim(is_string($request->status) ? $request->status : '');
            if (in_array($status, ['pending', 'paid', 'overdue', 'cancelled'])) {
                $query->where('status', $status);
            }
        }
    }

    private function applyDateFilters($query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $dateFrom = trim(is_string($request->date_from) ? $request->date_from : '');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) === 1) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
        }
        
        if ($request->filled('date_to')) {
            $dateTo = trim(is_string($request->date_to) ? $request->date_to : '');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
        }
    }

    private function getLicense(int $licenseId): License
    {
        $license = License::find($licenseId);
        if (!$license) {
            throw new \Exception('License not found');
        }
        return $license;
    }

    private function buildMetadata(bool $isCustomInvoice, array $validated): ?array
    {
        if (!$isCustomInvoice) {
            return null;
        }
        
        return [
            'custom_invoice' => true,
            'custom_invoice_type' => $validated['custom_invoice_type'] ?? null,
            'custom_product_name' => $validated['custom_product_name'] ?? null,
            'expiration_date' => ($validated['custom_invoice_type'] ?? 'one_time') !== 'one_time'
                ? ($validated['expiration_date'] ?? null)
                : null,
        ];
    }
}
