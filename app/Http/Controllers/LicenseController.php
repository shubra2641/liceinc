<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Admin\LicenseRequest;
use App\Models\License;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * License Controller with enhanced security and comprehensive license management.
 *
 * This controller handles license management operations including creation, editing,
 * deletion, and viewing of licenses with comprehensive security measures, validation,
 * and access control to ensure proper license administration.
 *
 * Features:
 * - Comprehensive license CRUD operations with security validation
 * - License key generation with uniqueness validation
 * - User and product association management
 * - License status and expiration management
 * - Domain limit configuration and validation
 * - Enhanced error handling and logging
 * - Input validation and sanitization
 *
 * @example
 * // List all licenses
 * GET /admin/licenses
 *
 * // Create a new license
 * POST /admin/licenses
 */
class LicenseController extends Controller
{
    /**
     * Display a listing of licenses with filtering and pagination.
     *
     * Shows all licenses with optional filtering by user or customer,
     * includes proper pagination and relationship loading for optimal performance.
     *
     * @return \Illuminate\View\View The licenses index view
     *
     * @example
     * // List all licenses
     * GET /admin/licenses
     *
     * // Filter by user
     * GET /admin/licenses?user=4
     *
     * // Filter by customer (backwards compatibility)
     * GET /admin/licenses?customer=3
     */
    public function index()
    {
        try {
            $query = License::with(['user', 'product'])->latest();
            // Support filtering by user or customer with validation
            if ($userId = request('user')) {
                $userId = $this->sanitizeInput($userId);
                if (is_numeric($userId) && $userId > 0) {
                    $query->forUser((int)$userId);
                }
            }
            // Backwards-compat: if a customer query param is provided, treat it as userId
            if ($customerId = request('customer')) {
                $customerId = $this->sanitizeInput($customerId);
                if (is_numeric($customerId) && $customerId > 0) {
                    $query->forUser((int)$customerId);
                }
            }
            $licenses = $query->paginate(10)->appends(request()->query());
            return view('admin.licenses.index', ['licenses' => $licenses]);
        } catch (\Exception $e) {
            Log::error('Error displaying licenses index', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return view('admin.licenses.index', ['licenses' => collect()]);
        }
    }
    /**
     * Show the form for creating a new license with security validation.
     *
     * Displays the license creation form with users and products data,
     * includes proper validation and security measures.
     *
     * @return \Illuminate\View\View The license creation form view
     *
     * @example
     * // Show create form
     * GET /admin/licenses/create
     *
     * // Show create form with pre-selected user
     * GET /admin/licenses/create?userId=4
     */
    public function create()
    {
        try {
            $users = \App\Models\User::all();
            $products = Product::all();
            $selectedUserId = $this->sanitizeInput(request('userId'));
            // Validate selected user ID if provided
            if ($selectedUserId && (! is_numeric($selectedUserId) || $selectedUserId <= 0)) {
                $selectedUserId = null;
            }
            return view('admin.licenses.create', ['users' => $users, 'products' => $products, 'selectedUserId' => $selectedUserId]);
        } catch (\Exception $e) {
            Log::error('Error showing license creation form', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return view('admin.licenses.create', [
                'users' => collect(),
                'products' => collect(),
                'selectedUserId' => null,
            ]);
        }
    }
    /**
     * Store a newly created license with comprehensive security validation.
     *
     * Creates a new license with proper validation, sanitization, and security measures
     * including automatic license key generation and proper data mapping.
     *
     * @param  LicenseRequest  $request  The HTTP request containing license data
     *
     * @return \Illuminate\Http\RedirectResponse Redirect to licenses index
     *
     * @throws \Exception When license creation fails
     *
     * @example
     * // Create a new license
     * POST /admin/licenses
     * {
     *     "userId": 1,
     *     "productId": 2,
     *     "licenseType": "regular",
     *     "status": "active",
     *     "expiresAt": "2024-12-31",
     *     "maxDomains": 1,
     *     "notes": "License notes"
     * }
     */
    public function store(LicenseRequest $request)
    {
        try {
            $validated = $request->validate([
                'userId' => 'required|exists:users, id',
                'productId' => 'required|exists:products, id',
                'licenseType' => 'required|in:regular, extended',
                'status' => 'required|in:active, inactive, suspended, expired',
                'expiresAt' => 'nullable|date|after:today',
                'maxDomains' => 'nullable|integer|min:1',
                'notes' => 'nullable|string|max:1000',
            ]);
            // Keep backwards-compatible customer_id if passed (optional) - map to userId
            if ($request->filled('customer_id')) {
                $customerId = $this->sanitizeInput($request->validated('customer_id'));
                if (is_numeric($customerId) && $customerId > 0) {
                    $validatedArray = is_array($validated) ? $validated : [];
                    $validatedArray['userId'] = $customerId;
                    $validated = $validatedArray;
                }
            }
            // Sanitize notes if provided
            $validatedArray = is_array($validated) ? $validated : [];
            if (isset($validatedArray['notes'])) {
                $validatedArray['notes'] = $this->sanitizeInput($validatedArray['notes']);
                $validated = $validatedArray;
            }
            // Map UI field to DB column with proper parsing and allowing null to clear
            if (array_key_exists('expiresAt', $validatedArray)) {
                $expiresAt = $validatedArray['expiresAt'];
                $validatedArray['licenseExpiresAt'] = ($expiresAt !== null
                    && $expiresAt !== '')
                    ? Carbon::parse(is_string($expiresAt) ? $expiresAt : '')->format('Y-m-d H:i:s')
                    : null;
                unset($validatedArray['expiresAt']);
                $validated = $validatedArray;
            }
            // Generate license key automatically
            $validatedArray['licenseKey'] = $this->generateLicenseKey();
            // Set purchase_code to be the same as licenseKey for now
            // This ensures consistency between what users enter and what's stored
            $validatedArray['purchase_code'] = $validatedArray['licenseKey'];
            // Set default values
            if (! isset($validatedArray['maxDomains'])) {
                $validatedArray['maxDomains'] = 1;
            }
            $validated = $validatedArray;
            // @phpstan-ignore-next-line
            $license = License::create($validated);
            return redirect()->route('admin.licenses.index')
                ->with('success', trans('app.License created successfully.'));
        } catch (\Exception $e) {
            Log::error('Error creating license', [
                'userId' => auth()->id(),
                'request_data' => $request->except(['password', 'password_confirmation']),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create license. Please try again.'])
                ->withInput();
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(License $license): \Illuminate\View\View
    {
        $license->load(['user', 'product', 'logs']);
        return view('admin.licenses.show', ['license' => $license]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(License $license): \Illuminate\View\View
    {
        $users = \App\Models\User::all();
        $products = Product::all();
        return view('admin.licenses.edit', ['license' => $license, 'users' => $users, 'products' => $products]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(LicenseRequest $request, License $license): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'userId' => 'required|exists:users, id',
            'productId' => 'required|exists:products, id',
            'licenseType' => 'required|in:regular, extended',
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended', 'expired'])],
            'expiresAt' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'maxDomains' => ['nullable', 'integer', 'min:1'],
        ]);
        $validatedArray = is_array($validated) ? $validated : [];
        if ($request->filled('customer_id')) {
            $validatedArray['userId'] = $request->validated('customer_id');
            $validated = $validatedArray;
        }
        // Map UI field to DB column with proper parsing and allowing null to clear
        if (array_key_exists('expiresAt', $validatedArray)) {
            $expiresAt = $validatedArray['expiresAt'];
            $validatedArray['licenseExpiresAt'] = ($expiresAt !== null && $expiresAt !== '')
                ? Carbon::parse(is_string($expiresAt) ? $expiresAt : '')->format('Y-m-d H:i:s')
                : null;
            unset($validatedArray['expiresAt']);
            $validated = $validatedArray;
        }
        // Set default values
        if (! isset($validatedArray['maxDomains'])) {
            $validatedArray['maxDomains'] = 1;
            $validated = $validatedArray;
        }
        // @phpstan-ignore-next-line
        $license->update($validated);
        return redirect()->route('admin.licenses.index')
            ->with('success', trans('app.License updated successfully.'));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(License $license): \Illuminate\Http\RedirectResponse
    {
        $license->delete();
        return redirect()->route('admin.licenses.index')
            ->with('success', trans('app.License deleted successfully.'));
    }
    /**
     * Generate a unique license key with security validation.
     *
     * Creates a unique license key with proper format and validation
     * to ensure uniqueness and security of license keys.
     *
     * @return string The generated unique license key
     *
     * @throws \Exception When license key generation fails
     */
    private function generateLicenseKey(): string
    {
        try {
            $attempts = 0;
            $maxAttempts = 100;
            do {
                $key = strtoupper(substr(md5(microtime() . uniqid()), 0, 16));
                $key = substr($key, 0, 4) . '-' . substr($key, 4, 4) . '-' . substr($key, 8, 4) . '-' . substr($key, 12, 4);
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw new \Exception('Unable to generate unique license key after ' . $maxAttempts . ' attempts');
                }
            } while (License::where('licenseKey', $key)->exists());
            return $key;
        } catch (\Exception $e) {
            Log::error('Error generating license key', [
                'attempts' => $attempts,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
