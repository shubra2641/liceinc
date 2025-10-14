<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LicenseRequest;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\License\LicenseManagementService;
use App\Services\License\LicenseExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

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
    public function __construct(
        private LicenseManagementService $licenseService,
        private LicenseExportService $exportService
    ) {
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
        $products = Product::with(['category'])->get();
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
            $license = $this->licenseService->createLicense($validated);
            
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
            $validated = $this->processExpirationDate($validated);
            
            $this->licenseService->updateLicense($license, $validated);
            
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
            
            $this->licenseService->deleteLicense($license);
            
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
            
            $this->licenseService->toggleLicenseStatus($license);
            
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
        try {
            $exportResult = $this->exportService->exportToCsv();
            
            if (!$exportResult['success']) {
                throw new \Exception($exportResult['error']);
            }
            
            $callback = $this->exportService->generateCsvCallback($exportResult['licenses']);
            $headers = $this->exportService->getCsvHeaders();
            
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('License export failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to export licenses: ' . $e->getMessage());
        }
    }

    /**
     * Process expiration date field mapping
     */
    private function processExpirationDate(array $validated): array
    {
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
        
        return $validated;
    }
}
