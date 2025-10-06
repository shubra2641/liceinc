<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * License Controller with enhanced security and comprehensive license management.
 *
 * This controller handles user license management operations including viewing
 * licenses, invoices, and license details. It implements comprehensive security
 * measures, input validation, and error handling for reliable license operations.
 */
class LicenseController extends Controller
{
    /**
     * Display a listing of the user's licenses with enhanced security and error handling.
     *
     * Shows a paginated list of user licenses and invoices with comprehensive
     * validation, security measures, and error handling for reliable license
     * management operations.
     *
     * @param  Request  $request  The HTTP request object
     *
     * @return View The licenses index view
     *
     * @throws InvalidArgumentException When user data is invalid
     * @throws \Exception When license retrieval fails
     *
     * @example
     * // Access via GET /user/licenses
     * // Returns paginated list of user's licenses and invoices
     */
    public function index(Request $request): View
    {
        try {
            // Validate request and user authentication
            $this->validateRequest($request, [
                'page' => 'sometimes|integer|min:1',
                'search' => 'sometimes|string|max:255',
                'status' => 'sometimes|string|in:active, inactive, expired',
            ]);
            $user = Auth::user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }
            DB::beginTransaction();
            // Get user licenses with product information
            $licenses = $this->getUserLicenses($user);
            // Get user invoices with product and license information
            $invoices = $this->getUserInvoices($user);
            DB::commit();
            return view('user.licenses.index', compact('licenses', 'invoices'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to display user licenses', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load licenses. Please try again.');
        }
    }
    /**
     * Display the specified license with enhanced security and error handling.
     *
     * Shows detailed information about a specific license with comprehensive
     * validation, security measures, and error handling for reliable license
     * viewing operations.
     *
     * @param  Request  $request  The HTTP request object
     * @param  int  $id  The license ID
     *
     * @return View|RedirectResponse The license show view or redirect on error
     *
     * @throws InvalidArgumentException When license ID is invalid
     * @throws \Exception When license retrieval fails
     *
     * @example
     * // Access via GET /user/licenses/{id}
     * // Returns detailed view of the specified license
     */
    public function show(Request $request, int $id): View|RedirectResponse
    {
        try {
            // Validate license ID parameter
            $this->validateLicenseId($id);
            $user = Auth::user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }
            DB::beginTransaction();
            // Get license with related data
            $license = $this->getUserLicense($user, $id);
            DB::commit();
            return view('user.licenses.show', compact('license'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to display license details', [
                'user_id' => Auth::id(),
                'license_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('user.licenses.index')
                ->with('error', 'License not found or access denied.');
        }
    }
    /**
     * Validate license ID with enhanced security and comprehensive validation.
     *
     * @param  int  $id  The license ID to validate
     *
     * @throws InvalidArgumentException When license ID is invalid
     */
    private function validateLicenseId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('License ID must be a positive integer');
        }
        if ($id > 999999999) {
            throw new InvalidArgumentException('License ID is too large');
        }
    }
    /**
     * Get user licenses with enhanced security and error handling.
     *
     * @param  mixed  $user  The user object
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @throws \Exception When license retrieval fails
     */
    private function getUserLicenses($user)
    {
        try {
            return $user->licenses()
                ->with('product')
                ->latest()
                ->paginate(10);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user licenses', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get user invoices with enhanced security and error handling.
     *
     * @param  mixed  $user  The user object
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @throws \Exception When invoice retrieval fails
     */
    private function getUserInvoices($user)
    {
        try {
            return $user->invoices()
                ->with(['product', 'license'])
                ->latest()
                ->paginate(10);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user invoices', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get user license by ID with enhanced security and error handling.
     *
     * @param  mixed  $user  The user object
     * @param  int  $id  The license ID
     *
     * @return License The license model
     *
     * @throws \Exception When license retrieval fails
     */
    private function getUserLicense($user, int $id): License
    {
        try {
            $license = $user->licenses()
                ->with(['product', 'domains'])
                ->findOrFail($id);
            return $license;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user license', [
                'user_id' => $user->id ?? 'unknown',
                'license_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
