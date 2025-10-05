<?php
namespace App\Http\Controllers;
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
                    $query->forUser($userId);
                }
            }
            // Backwards-compat: if a customer query param is provided, treat it as user_id
            if ($customerId = request('customer')) {
                $customerId = $this->sanitizeInput($customerId);
                if (is_numeric($customerId) && $customerId > 0) {
                    $query->forUser($customerId);
                }
            }
            $licenses = $query->paginate(10)->appends(request()->query());
            return view('admin.licenses.index', compact('licenses'));
        } catch (\Exception $e) {
            Log::error('Error displaying licenses index', [
                'user_id' => auth()->id(),
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
     * GET /admin/licenses/create?user_id=4
     */
    public function create()
    {
        try {
            $users = \App\Models\User::all();
            $products = Product::all();
            $selectedUserId = $this->sanitizeInput(request('user_id'));
            // Validate selected user ID if provided
            if ($selectedUserId && (! is_numeric($selectedUserId) || $selectedUserId <= 0)) {
                $selectedUserId = null;
            }
            return view('admin.licenses.create', compact('users', 'products', 'selectedUserId'));
        } catch (\Exception $e) {
            Log::error('Error showing license creation form', [
                'user_id' => auth()->id(),
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
     * @param  Request  $request  The HTTP request containing license data
     *
     * @return \Illuminate\Http\RedirectResponse Redirect to licenses index
     *
     * @throws \Exception When license creation fails
     *
     * @example
     * // Create a new license
     * POST /admin/licenses
     * {
     *     "user_id": 1,
     *     "product_id": 2,
     *     "license_type": "regular",
     *     "status": "active",
     *     "expires_at": "2024-12-31",
     *     "max_domains": 1,
     *     "notes": "License notes"
     * }
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users, id',
                'product_id' => 'required|exists:products, id',
                'license_type' => 'required|in:regular, extended',
                'status' => 'required|in:active, inactive, suspended, expired',
                'expires_at' => 'nullable|date|after:today',
                'max_domains' => 'nullable|integer|min:1',
                'notes' => 'nullable|string|max:1000',
            ]);
            // Keep backwards-compatible customer_id if passed (optional) - map to user_id
            if ($request->filled('customer_id')) {
                $customerId = $this->sanitizeInput($request->input('customer_id'));
                if (is_numeric($customerId) && $customerId > 0) {
                    $validated['user_id'] = $customerId;
                }
            }
            // Sanitize notes if provided
            if (isset($validated['notes'])) {
                $validated['notes'] = $this->sanitizeInput($validated['notes']);
            }
            // Map UI field to DB column with proper parsing and allowing null to clear
            if (array_key_exists('expires_at', $validated)) {
                $validated['license_expires_at'] = ($validated['expires_at'] !== null
                    && $validated['expires_at'] !== '')
                    ? Carbon::parse($validated['expires_at'])->format('Y-m-d H:i:s')
                    : null;
                unset($validated['expires_at']);
            }
            // Generate license key automatically
            $validated['license_key'] = $this->generateLicenseKey();
            // Set purchase_code to be the same as license_key for now
            // This ensures consistency between what users enter and what's stored
            $validated['purchase_code'] = $validated['license_key'];
            // Set default values
            if (! isset($validated['max_domains'])) {
                $validated['max_domains'] = 1;
            }
            $license = License::create($validated);
            return redirect()->route('admin.licenses.index')
                ->with('success', trans('app.License created successfully.'));
        } catch (\Exception $e) {
            Log::error('Error creating license', [
                'user_id' => auth()->id(),
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
    public function show(License $license)
    {
        $license->load(['user', 'product', 'logs']);
        return view('admin.licenses.show', compact('license'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(License $license)
    {
        $users = \App\Models\User::all();
        $products = Product::all();
        return view('admin.licenses.edit', compact('license', 'users', 'products'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, License $license)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users, id',
            'product_id' => 'required|exists:products, id',
            'license_type' => 'required|in:regular, extended',
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended', 'expired'])],
            'expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'max_domains' => ['nullable', 'integer', 'min:1'],
        ]);
        if ($request->filled('customer_id')) {
            $validated['user_id'] = $request->input('customer_id');
        }
        // Map UI field to DB column with proper parsing and allowing null to clear
        if (array_key_exists('expires_at', $validated)) {
            $validated['license_expires_at'] = ($validated['expires_at'] !== null && $validated['expires_at'] !== '')
                ? Carbon::parse($validated['expires_at'])->format('Y-m-d H:i:s')
                : null;
            unset($validated['expires_at']);
        }
        // Set default values
        if (! isset($validated['max_domains'])) {
            $validated['max_domains'] = 1;
        }
        $license->update($validated);
        return redirect()->route('admin.licenses.index')
            ->with('success', trans('app.License updated successfully.'));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(License $license)
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
                $key = strtoupper(substr(md5(microtime().uniqid()), 0, 16));
                $key = substr($key, 0, 4).'-'.substr($key, 4, 4).'-'.substr($key, 8, 4).'-'.substr($key, 12, 4);
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw new \Exception('Unable to generate unique license key after '.$maxAttempts.' attempts');
                }
            } while (License::where('license_key', $key)->exists());
            return $key;
        } catch (\Exception $e) {
            Log::error('Error generating license key', [
                'attempts' => $attempts ?? 0,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
