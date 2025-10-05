<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
/**
 * User Invoice Controller with enhanced security.
 *
 * This controller handles user invoice management functionality including
 * invoice listing, filtering, and detailed invoice viewing with enhanced
 * security measures and proper error handling.
 *
 * Features:
 * - User invoice listing with pagination
 * - Invoice filtering by status
 * - Detailed invoice viewing with relationships
 * - User authorization and access control
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Model relationship integration for optimized queries
 */
class InvoiceController extends Controller
{
    /**
     * Pagination limit for invoice listing.
     */
    private const PAGINATION_LIMIT = 10;
    /**
     * Display a listing of user invoices with enhanced security.
     *
     * Shows paginated list of user invoices with optional status filtering,
     * proper authorization, and comprehensive error handling.
     *
     * @param  Request  $request  The HTTP request containing filter parameters
     *
     * @return View The invoice listing view
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Access user invoices:
     * GET /user/invoices
     *
     * // Filter by status:
     * GET /user/invoices?status=pending
     *
     * // Returns view with:
     * // - Paginated invoices (10 per page)
     * // - Status filtering
     * // - Product and license relationships
     */
    public function index(Request $request): View
    {
        try {
            if (! $request) {
                throw new \InvalidArgumentException('Request cannot be null');
            }
            $userId = Auth::id();
            if (! $userId) {
                throw new Exception('User not authenticated');
            }
            DB::beginTransaction();
            $query = Invoice::where('user_id', $userId)
                ->with(['product', 'license']);
            // Filter by status with validation
            if ($request->filled('status')) {
                $status = $this->validateStatus($request->input('status'));
                $query->where('status', $status);
            }
            $invoices = $query->latest()->paginate(self::PAGINATION_LIMIT);
            DB::commit();
            return view('user.invoices.index', compact('invoices'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load user invoices: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'request_url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString(),
            ]);
            return view('user.invoices.index', ['invoices' => collect()])
                ->with('error', 'Failed to load invoices. Please try again.');
        }
    }
    /**
     * Display the specified invoice with enhanced security.
     *
     * Shows detailed invoice information with proper user authorization,
     * relationship loading, and comprehensive error handling.
     *
     * @param  Invoice  $invoice  The invoice instance
     *
     * @return View The invoice detail view
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Access specific invoice:
     * GET /user/invoices/123
     *
     * // Returns view with:
     * // - Invoice details
     * // - Product information
     * // - License information
     * // - User authorization check
     */
    public function show(Invoice $invoice): View
    {
        try {
            if (! $invoice) {
                throw new \InvalidArgumentException('Invoice cannot be null');
            }
            $userId = Auth::id();
            if (! $userId) {
                throw new Exception('User not authenticated');
            }
            // Ensure user can only view their own invoices
            if ($invoice->user_id !== $userId) {
                Log::warning('Unauthorized invoice access attempt', [
                    'user_id' => $userId,
                    'invoice_id' => $invoice->id,
                    'invoice_user_id' => $invoice->user_id,
                    'ip_address' => request()->ip(),
                ]);
                abort(403, 'Unauthorized access to invoice');
            }
            DB::beginTransaction();
            $invoice->load(['product', 'license.product']);
            // Calculate invoice data for view
            $hasLicense = $invoice->license && $invoice->license->product;
            $hasProduct = $invoice->product;
            $isCustomInvoice = ! $hasLicense && ! $hasProduct; // Custom invoice for additional services
            $productForPayment = $hasLicense ? $invoice->license->product : ($invoice->product ?? null);
            // Get enabled payment gateways
            $enabledGateways = \App\Models\PaymentSetting::getEnabledGateways();
            DB::commit();
            return view(
                'user.invoices.show',
                compact(
                    'invoice',
                    'hasLicense',
                    'hasProduct',
                    'isCustomInvoice',
                    'productForPayment',
                    'enabledGateways',
                ),
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load invoice details: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'invoice_id' => $invoice->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Failed to load invoice details. Please try again.');
        }
    }
    /**
     * Validate invoice status parameter.
     *
     * @param  string  $status  The status to validate
     *
     * @return string The validated status
     *
     * @throws \InvalidArgumentException When status is invalid
     */
    private function validateStatus(string $status): string
    {
        $validStatuses = ['pending', 'paid', 'overdue', 'cancelled'];
        if (! in_array($status, $validStatuses, true)) {
            throw new \InvalidArgumentException('Invalid invoice status: '.$status);
        }
        return $status;
    }
}
