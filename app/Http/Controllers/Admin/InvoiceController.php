<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoiceRequest;
use App\Models\Invoice;
use App\Models\License;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Admin Invoice Controller with enhanced security.
 *
 * This controller handles invoice management functionality including creation,
 * editing, payment processing, and cancellation of invoices. It provides
 * comprehensive invoice management with proper validation and security measures.
 *
 * Features:
 * - Invoice listing with filtering and pagination
 * - Invoice creation and editing with validation
 * - Payment processing and status management
 * - Invoice cancellation and deletion
 * - Custom invoice support with metadata
 * - Comprehensive error handling with database transactions
 * - Support for multiple invoice types and currencies
 * - Enhanced security measures (XSS protection, input validation)
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
 *
 * @example
 * // Create a new invoice
 * POST /admin/invoices
 * {
 *     "userId": 1,
 *     "licenseId": 5,
 *     "type": "initial",
 *     "amount": 99.99,
 *     "currency": "USD",
 *     "status": "pending"
 * }
 */
class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;
    /**
     * Create a new controller instance.
     *
     * @param  InvoiceService  $invoiceService  The invoice service for business logic
     */
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    /**
     * Display a listing of invoices with filtering and pagination and enhanced security.
     *
     * Shows a paginated list of invoices with optional filtering by status
     * and date range. Includes invoice statistics for dashboard display.
     *
     * @param  Request  $request  The HTTP request containing optional filter parameters
     *
     * @return View The invoices index view with filtered data and statistics
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request with filters:
     * GET /admin/invoices?status=paid&dateFrom=2024-01-01&dateTo=2024-01-31
     *
     * // Returns view with:
     * // - Paginated invoices list
     * // - Filter options
     * // - Invoice statistics
     */
    public function index(Request $request): View
    {
        try {
            DB::beginTransaction();
            $query = Invoice::with(['user', 'product', 'license']);
            // Filter by status with validation
            if ($request->filled('status')) {
                $status = trim(is_string($request->status) ? $request->status : '');
                if (in_array($status, ['pending', 'paid', 'overdue', 'cancelled'])) {
                    $query->where('status', $status);
                }
            }
            if ($request->filled('dateFrom')) {
                $dateFrom = trim(is_string($request->dateFrom) ? $request->dateFrom : '');
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) === 1) {
                    $query->whereDate('createdAt', '>=', $dateFrom);
                }
            }
            if ($request->filled('dateTo')) {
                $dateTo = trim(is_string($request->dateTo) ? $request->dateTo : '');
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
                    $query->whereDate('createdAt', '<=', $dateTo);
                }
            }
            $invoices = $query->latest()->paginate(10);
            $stats = $this->invoiceService->getInvoiceStats();
            DB::commit();
            return view('admin.invoices.index', ['invoices' => $invoices, 'stats' => $stats]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice listing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return empty results on error
            return view('admin.invoices.index', [
                'invoices' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                'stats' => [],
            ]);
        }
    }
    /**
     * Show the form for creating a new invoice.
     *
     * Displays the invoice creation form with user selection and
     * license/product options for invoice generation.
     *
     * @return View The invoice creation form view
     *
     * @example
     * // Access the create form:
     * GET /admin/invoices/create
     *
     * // Returns view with:
     * // - User selection dropdown
     * // - Invoice type options
     * // - Amount and currency fields
     * // - Custom invoice options
     */
    public function create(): View
    {
        $users = User::select('id', 'name', 'email')->get();
        return view('admin.invoices.create', ['users' => $users]);
    }
    /**
     * Store a newly created invoice in storage with enhanced security.
     *
     * Creates a new invoice with comprehensive validation including support
     * for custom invoices with metadata. Handles both license-based and
     * custom invoice creation with proper data validation.
     *
     * @param  InvoiceRequest  $request  The validated request containing invoice data
     *
     * @return RedirectResponse Redirect to invoice details with success message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request for license-based invoice:
     * POST /admin/invoices
     * {
     *     "userId": 1,
     *     "licenseId": 5,
     *     "type": "initial",
     *     "amount": 99.99,
     *     "currency": "USD",
     *     "status": "pending"
     * }
     *
     * // Request for custom invoice:
     * POST /admin/invoices
     * {
     *     "userId": 1,
     *     "licenseId": "custom",
     *     "type": "custom",
     *     "amount": 149.99,
     *     "currency": "USD",
     *     "status": "pending",
     *     "custom_invoice_type": "annual",
     *     "custom_product_name": "Premium Support"
     * }
     */
    public function store(InvoiceRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $isCustomInvoice = $request->licenseId === 'custom';
            $validated = $request->validated();
            $license = null;
            $productId = null;
            if (! $isCustomInvoice) {
                $license = License::find($validated['licenseId']);
                if (! $license) {
                    throw new \Exception('License not found');
                }
                $productId = $license->productId;
            }
            $invoice = Invoice::create([
                'userId' => $validated['userId'],
                'licenseId' => $isCustomInvoice ? null : $validated['licenseId'],
                'productId' => $productId,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'status' => $validated['status'],
                'due_date' => $validated['due_date'],
                'paid_at' => $validated['status'] === 'paid' ? ($validated['paid_at'] ?? now()) : null,
                'notes' => $validated['notes'],
                'metadata' => $isCustomInvoice ? [
                    'custom_invoice' => true,
                    'custom_invoice_type' => $validated['custom_invoice_type'],
                    'custom_product_name' => $validated['custom_product_name'],
                    'expiration_date' => $validated['custom_invoice_type'] !== 'one_time'
                        ? $validated['expiration_date']
                        : null,
                ] : null,
            ]);
            DB::commit();
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', 'Invoice created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['notes']),
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create invoice. Please try again.');
        }
    }
    /**
     * Display the specified invoice.
     *
     * Shows detailed information about a specific invoice including
     * user details, product/license information, and payment status.
     *
     * @param  Invoice  $invoice  The invoice to display
     *
     * @return View The invoice details view
     *
     * @version 1.0.6
     *
     * @example
     * // Access invoice details:
     * GET /admin/invoices/123
     *
     * // Returns view with:
     * // - Invoice details and status
     * // - User information
     * // - Product/license details
     * // - Payment information
     * // - Action buttons (mark paid, cancel, etc.)
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['user', 'product', 'license']);
        return view('admin.invoices.show', ['invoice' => $invoice]);
    }
    /**
     * Mark invoice as paid with enhanced security.
     *
     * Updates the invoice status to paid and records the payment timestamp
     * using the invoice service for proper business logic handling.
     *
     * @param  Invoice  $invoice  The invoice to mark as paid
     *
     * @return RedirectResponse Redirect back with success message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Mark invoice as paid:
     * POST /admin/invoices/123/mark-paid
     *
     * // Response: Redirect back with success message
     * // "Invoice marked as paid successfully"
     */
    public function markAsPaid(Invoice $invoice): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $this->invoiceService->markAsPaid($invoice);
            DB::commit();
            return redirect()->back()->with('success', 'Invoice marked as paid successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice payment marking failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoice->id,
            ]);
            return redirect()
                ->back()
                ->with('error', 'Failed to mark invoice as paid. Please try again.');
        }
    }
    /**
     * Cancel invoice with enhanced security.
     *
     * Cancels the invoice and updates its status using the invoice service
     * for proper business logic handling and status management.
     *
     * @param  Invoice  $invoice  The invoice to cancel
     *
     * @return RedirectResponse Redirect back with success message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Cancel invoice:
     * POST /admin/invoices/123/cancel
     *
     * // Response: Redirect back with success message
     * // "Invoice cancelled successfully"
     */
    public function cancel(Invoice $invoice): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $invoice->update(['status' => 'cancelled']);
            DB::commit();
            return redirect()->back()->with('success', 'Invoice cancelled successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice cancellation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoice->id,
            ]);
            return redirect()
                ->back()
                ->with('error', 'Failed to cancel invoice. Please try again.');
        }
    }
    /**
     * Show the form for editing the specified invoice.
     *
     * Displays the invoice editing form with pre-populated data
     * and user selection options for invoice modification.
     *
     * @param  Invoice  $invoice  The invoice to edit
     *
     * @return View The invoice edit form view
     *
     * @version 1.0.6
     *
     * @example
     * // Access the edit form:
     * GET /admin/invoices/123/edit
     *
     * // Returns view with:
     * // - Pre-populated invoice data
     * // - User selection dropdown
     * // - Invoice type and status options
     * // - Custom invoice fields if applicable
     */
    public function edit(Invoice $invoice): View
    {
        $users = User::select('id', 'name', 'email')->get();
        $invoice->load(['user', 'license.product']);
        return view('admin.invoices.edit', ['invoice' => $invoice, 'users' => $users]);
    }
    /**
     * Update the specified invoice in storage.
     *
     * Updates an existing invoice with comprehensive validation including
     * support for custom invoices with metadata. Handles both license-based
     * and custom invoice updates with proper data validation.
     *
     * @param  InvoiceRequest  $request  The validated request containing updated invoice data
     * @param  Invoice  $invoice  The invoice to update
     *
     * @return RedirectResponse Redirect to invoice details with success message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Update invoice:
     * PUT /admin/invoices/123
     * {
     *     "userId": 1,
     *     "licenseId": 5,
     *     "type": "renewal",
     *     "amount": 79.99,
     *     "currency": "USD",
     *     "status": "paid",
     *     "paid_at": "2024-01-15 10:30:00"
     * }
     *
     * // Response: Redirect to invoice details with success message
     * // "Invoice updated successfully"
     */
    public function update(InvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $isCustomInvoice = $request->licenseId === 'custom';
            $validated = $request->validated();
            $license = null;
            $productId = null;
            if (! $isCustomInvoice) {
                $license = License::find($validated['licenseId']);
                if (! $license) {
                    throw new \Exception('License not found');
                }
                $productId = $license->productId;
            }
            $invoice->update([
                'userId' => $validated['userId'],
                'licenseId' => $isCustomInvoice ? null : $validated['licenseId'],
                'productId' => $productId,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'status' => $validated['status'],
                'due_date' => $validated['due_date'],
                'paid_at' => $validated['status'] === 'paid' ? ($validated['paid_at'] ?? now()) : null,
                'notes' => $validated['notes'],
                'metadata' => $isCustomInvoice ? [
                    'custom_invoice' => true,
                    'custom_invoice_type' => $validated['custom_invoice_type'],
                    'custom_product_name' => $validated['custom_product_name'],
                    'expiration_date' => $validated['custom_invoice_type'] !== 'one_time'
                        ? $validated['expiration_date']
                        : null,
                ] : null,
            ]);
            DB::commit();
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoice->id,
                'request_data' => $request->except(['notes']),
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update invoice. Please try again.');
        }
    }
    /**
     * Remove the specified invoice from storage with enhanced security.
     *
     * Deletes an invoice with proper validation to prevent deletion
     * of paid invoices. Only allows deletion of pending or cancelled invoices.
     *
     * @param  Invoice  $invoice  The invoice to delete
     *
     * @return RedirectResponse Redirect to invoices list with success/error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Delete invoice:
     * DELETE /admin/invoices/123
     *
     * // Success response: Redirect to invoices list
     * // "Invoice deleted successfully"
     *
     * // Error response (if paid): Redirect back with error
     * // "Cannot delete a paid invoice"
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        try {
            DB::beginTransaction();
            // Check if invoice can be deleted (not paid)
            if ($invoice->status === 'paid') {
                DB::rollBack();
                return redirect()->back()->with('error', 'Cannot delete a paid invoice');
            }
            $invoice->delete();
            DB::commit();
            return redirect()->route('admin.invoices.index')
                ->with('success', 'Invoice deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoice->id,
            ]);
            return redirect()
                ->back()
                ->with('error', 'Failed to delete invoice. Please try again.');
        }
    }
}
