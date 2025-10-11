<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LicenseRequest;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\EmailService;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Helpers\SecureFileHelper;

/**
 * Admin License Controller with enhanced security.
 *
 * This controller handles comprehensive license management in the admin panel
 * including creation, editing, status management, and export functionality.
 *
 * Features:
 * - License CRUD operations with validation
 * - Automatic invoice generation
 * - Email notifications for license events
 * - License status management and toggling
 * - CSV export functionality
 * - Product inheritance for license types
 * - Domain limit management
 * - Comprehensive error handling with database transactions
 * - Enhanced security measures (XSS protection, input validation)
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
 */
class LicenseController extends Controller
{
    protected EmailService $emailService;
    /**
     * Create a new controller instance.
     *
     * @param  EmailService  $emailService  The email service for notifications
     *
     * @version 1.0.6
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    /**
     * Display a listing of licenses with pagination.
     *
     * @return View The licenses index view
     *
     * @version 1.0.6
     */
    public function index(): View
    {
        $licenses = License::with(['user', 'product'])
            ->latest()
            ->paginate(10);
        return view('admin.licenses.index', ['licenses' => $licenses]);
    }
    /**
     * Show the form for creating a new license.
     *
     * @return View The license creation form view
     *
     * @version 1.0.6
     */
    public function create(): View
    {
        $users = User::all();
        $products = Product::all();
        $selectedUserId = null;
        return view('admin.licenses.create', [
            'users' => $users,
            'products' => $products,
            'selectedUserId' => $selectedUserId
        ]);
    }
    /**
     * Store a newly created resource in storage with enhanced security.
     *
     * Creates a new license with comprehensive validation, automatic invoice
     * generation, and email notifications. Includes product inheritance
     * and proper error handling.
     *
     * @param  LicenseRequest  $request  The HTTP request containing license data
     *
     * @return RedirectResponse Redirect to license details with success message
     *
     * @throws \Exception When database operations fail
     */
    public function store(LicenseRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Get product details
            $product = Product::find($validated['product_id']);
            if (! $product) {
                DB::rollBack();
                return back()->withErrors(['product_id' => 'Product not found.']);
            }
            // Inherit license type from product if not specified
            if (empty($validated['license_type'])) {
                $validated['license_type'] = $product->license_type ?? 'single';
            }
            // Set max_domains based on license type
            if (empty($validated['max_domains'])) {
                switch ($validated['license_type']) {
                    case 'single':
                        $validated['max_domains'] = 1;
                        break;
                    case 'multi':
                        $validated['max_domains'] = $request->input('max_domains', 5); // Default to 5 for multi
                        break;
                    case 'developer':
                        $validated['max_domains'] = $request->input('max_domains', 10); // Default to 10 for developer
                        break;
                    case 'extended':
                        $validated['max_domains'] = $request->input('max_domains', 20); // Default to 20 for extended
                        break;
                    default:
                        $validated['max_domains'] = 1;
                }
            }
            // Set default values
            $validated['status'] = $validated['status'] ?? 'active';
            // Calculate license expiration date based on product duration
            if (empty($validated['license_expires_at'])) {
                if ($product->duration_days) {
                    $validated['license_expires_at'] = now()->addDays(
                        is_numeric($product->duration_days) ? (int)$product->duration_days : 0
                    );
                }
            }
            // Calculate support expiration date based on product support days
            if (empty($validated['support_expires_at'])) {
                if ($product->support_days) {
                    $validated['support_expires_at'] = now()->addDays(
                        is_numeric($product->support_days) ? (int)$product->support_days : 0
                    );
                }
            }
            $license = License::create($validated);
            // Automatically create initial invoice with specified payment status
            $invoiceService = app(InvoiceService::class);
            $invoice = $invoiceService->createInitialInvoice(
                $license,
                is_string($validated['invoice_payment_status'] ?? null)
                    ? $validated['invoice_payment_status']
                    : 'pending',
                ($validated['invoice_due_date'] ?? null) instanceof \DateTimeInterface
                    ? $validated['invoice_due_date']
                    : null,
            );
            // Send email notifications
            try {
                // Send notification to user
                if ($license->user) {
                    $this->emailService->sendLicenseCreated($license, $license->user);
                }
                // Send notification to admin
                $this->emailService->sendAdminLicenseCreated([
                    'license_key' => $license->license_key,
                    'product_name' => $license->product->name ?? 'Unknown Product',
                    'customer_name' => $license->user ? $license->user->name : 'Unknown User',
                    'customer_email' => $license->user ? $license->user->email : 'No email provided',
                ]);
            } catch (\Exception $e) {
                // Log email errors but don't fail license creation
                Log::warning('Email notification failed during license creation', [
                    'error' => $e->getMessage(),
                    'license_id' => $license->id,
                ]);
            }
            DB::commit();
            return redirect()->route('admin.licenses.show', $license)
                ->with('success', 'License created successfully with automatic invoice generation.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['notes']),
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create license. Please try again.');
        }
    }
    /**
     * Display the specified license with related data.
     *
     * Shows detailed information about a specific license including
     * user details, product information, associated domains, and logs.
     *
     * @param  License  $license  The license to display
     *
     * @return View The license details view
     *
     * @version 1.0.6
     */
    public function show(License $license): View
    {
        $license->load(['user', 'product', 'domains', 'logs']);
        return view('admin.licenses.show', ['license' => $license]);
    }
    /**
     * Show the form for editing the specified license.
     *
     * Displays the license edit form with populated data and
     * available users and products for selection.
     *
     * @param  License  $license  The license to edit
     *
     * @return View The license edit form view
     *
     * @version 1.0.6
     */
    public function edit(License $license): View
    {
        $users = User::all();
        $products = Product::all();
        return view('admin.licenses.edit', ['license' => $license, 'users' => $users, 'products' => $products]);
    }
    /**
     * Update the specified resource in storage with enhanced security.
     *
     * Updates an existing license with comprehensive validation and proper
     * error handling. Includes product inheritance and domain management.
     *
     * @param  LicenseRequest  $request  The HTTP request containing updated license data
     * @param  License  $license  The license to update
     *
     * @return RedirectResponse Redirect to license details with success message
     *
     * @throws \Exception When database operations fail
     */
    public function update(LicenseRequest $request, License $license): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Map UI field to DB column with proper parsing
            // and allowing null to clear
            if (array_key_exists('expires_at', $validated)) {
                $validated['license_expires_at'] = ($validated['expires_at'] !== null
                && $validated['expires_at'] !== '')
                    ? \Carbon\Carbon::parse(
                        is_string($validated['expires_at'])
                            ? $validated['expires_at']
                            : ''
                    )->format('Y-m-d H:i:s')
                    : null;
                unset($validated['expires_at']);
            }
            // Get product details for inheritance
            $product = Product::find($validated['product_id']);
            if (! $product) {
                DB::rollBack();
                return back()->withErrors(['product_id' => 'Product not found.']);
            }
            // Inherit license type from product if not specified
            if (empty($validated['license_type'])) {
                $validated['license_type'] = $product->license_type ?? 'single';
            }
            // Set max_domains based on license type
            if (empty($validated['max_domains'])) {
                switch ($validated['license_type']) {
                    case 'single':
                        $validated['max_domains'] = 1;
                        break;
                    case 'multi':
                        $validated['max_domains'] = 5;
                        break;
                    case 'developer':
                        $validated['max_domains'] = 10;
                        break;
                    case 'extended':
                        $validated['max_domains'] = 20;
                        break;
                    default:
                        $validated['max_domains'] = 1;
                }
            }
            // Set default values
            $validated['max_domains'] = $validated['max_domains'];
            $license->update($validated);
            DB::commit();
            return redirect()->route('admin.licenses.show', $license)
                ->with('success', 'License updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'license_id' => $license->id,
                'request_data' => $request->except(['notes']),
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update license. Please try again.');
        }
    }
    /**
     * Remove the specified resource from storage with enhanced security.
     *
     * Deletes a license with proper validation and error handling.
     *
     * @param  License  $license  The license to delete
     *
     * @return RedirectResponse Redirect to licenses list with success message
     *
     * @throws \Exception When database operations fail
     */
    public function destroy(License $license): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $license->delete();
            DB::commit();
            return redirect()->route('admin.licenses.index')
                ->with('success', 'License deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'license_id' => $license->id,
            ]);
            return redirect()
                ->back()
                ->with('error', 'Failed to delete license. Please try again.');
        }
    }
    /**
     * Toggle license status with enhanced security.
     *
     * Toggles the license status between active and inactive with proper
     * error handling and database transactions.
     *
     * @param  License  $license  The license to toggle
     *
     * @return RedirectResponse Redirect back with success message
     *
     * @throws \Exception When database operations fail
     */
    public function toggle(License $license): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $license->update([
                'status' => $license->status === 'active' ? 'inactive' : 'active',
            ]);
            DB::commit();
            return back()->with('success', 'License status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'license_id' => $license->id,
            ]);
            return back()->with('error', 'Failed to update license status. Please try again.');
        }
    }
    /**
     * Export licenses to CSV format with comprehensive data.
     *
     * Generates a CSV file containing all licenses with their associated
     * user and product information for administrative purposes.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse The CSV download response
     *
     * @version 1.0.6
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $licenses = License::with(['user', 'product'])->get();
        $filename = 'licenses_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($licenses) {
            $file = SecureFileHelper::openOutput('w');
            if (!is_resource($file)) {
                return;
            }
            // CSV Headers
            fputcsv($file, [
                'ID',
                'License Key',
                'User',
                'Product',
                'Status',
                'Max Domains',
                'Expires At',
                'Created At',
            ]);
            // CSV Data
            foreach ($licenses as $license) {
                fputcsv($file, [
                    $license->id,
                    $license->license_key,
                    $license->user->name ?? 'N/A',
                    $license->product->name ?? 'N/A',
                    $license->status,
                    $license->max_domains,
                    $license->expires_at,
                    $license->created_at,
                ]);
            }
            SecureFileHelper::closeFile($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}
