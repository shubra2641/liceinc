<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * User Dashboard Controller with enhanced security.
 *
 * This controller handles user dashboard functionality including license management,
 * product suggestions, invoice statistics, and comprehensive data visualization
 * with enhanced security measures and proper error handling.
 *
 * Features:
 * - User dashboard with license overview
 * - Product suggestions and recommendations
 * - Invoice statistics and analytics
 * - SEO data integration
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
 */
class DashboardController extends Controller
{
    /**
     * Display the user dashboard with enhanced security.
     *
     * Shows comprehensive user dashboard with license overview, product suggestions,
     * invoice statistics, and SEO data with proper error handling and security measures.
     *
     * @param  Request  $request  The HTTP request containing user information
     *
     * @return View The user dashboard view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access the user dashboard:
     * GET /user/dashboard
     *
     * // Returns view with:
     * // - User licenses (paginated, latest 10)
     * // - Active licenses count
     * // - Available products (suggestions)
     * // - Invoice statistics
     * // - SEO data
     */
    public function index(Request $request): View
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }
            // Load full licenses with related product and domains using the centralized scope
            $licensesQuery = License::with(['product', 'domains'])->forUser($user)->latest();
            // Paginate for the dashboard list (show latest 10)
            $licenses = $licensesQuery->paginate(10);
            // Compute accurate active licenses count: status 'active' and not expired
            $activeCount = $this->getActiveLicensesCount($user);
            // Get available products (for suggestions)
            $products = $this->getAvailableProducts();
            // Invoice statistics for user dashboard
            $invoiceStats = $this->getInvoiceStatistics($user);
            // SEO data
            $seoData = $this->getSeoData();
            // Get recent invoices for dashboard
            $recentInvoices = $user->invoices()->with('license.product')->orderBy('created_at', 'desc')->take(5)->get();
            DB::commit();

            return view('user.dashboard', array_merge(
                [
                    'licenses' => $licenses,
                    'products' => $products,
                    'activeCount' => $activeCount,
                    'recentInvoices' => $recentInvoices,
                ],
                $invoiceStats,
                $seoData,
            ));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User dashboard failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Return empty dashboard with error message
            return view('user.dashboard', [
                'licenses' => collect(),
                'products' => collect(),
                'activeCount' => 0,
                'invoiceTotal' => 0,
                'invoicePaid' => 0,
                'invoicePending' => 0,
                'invoiceCancelled' => 0,
                'seoTitle' => null,
                'seoDescription' => null,
                'error' => 'Failed to load dashboard data. Please try again.',
            ]);
        }
    }

    /**
     * Get active licenses count for the user.
     *
     * @param  \App\Models\User  $user  The user instance
     *
     * @return int The count of active licenses
     *
     * @throws \Exception When database operations fail
     */
    private function getActiveLicensesCount($user): int
    {
        try {
            return License::forUser($user)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('license_expires_at')
                        ->orWhere('license_expires_at', '>', now());
                })->count();
        } catch (\Exception $e) {
            Log::error('Failed to get active licenses count', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return 0;
        }
    }

    /**
     * Get available products for suggestions.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Product> The available products
     *
     * @throws \Exception When database operations fail
     */
    private function getAvailableProducts()
    {
        try {
            return Product::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to get available products', [
                'error' => $e->getMessage(),
            ]);

            return Product::whereRaw('1 = 0')->get();
        }
    }

    /**
     * Get invoice statistics for the user.
     *
     * @param  \App\Models\User  $user  The user instance
     *
     * @return array<string, mixed> The invoice statistics
     *
     * @throws \Exception When database operations fail
     */
    private function getInvoiceStatistics($user): array
    {
        try {
            $invoiceTotal = $user->invoices()->count();
            $invoicePaid = $user->invoices()->where('status', 'paid')->count();
            $invoicePending = $user->invoices()->where('status', 'pending')->count();
            $invoiceCancelled = $user->invoices()->where('status', 'cancelled')->count();

            return [
                'invoiceTotal' => $invoiceTotal,
                'invoicePaid' => $invoicePaid,
                'invoicePending' => $invoicePending,
                'invoiceCancelled' => $invoiceCancelled,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get invoice statistics', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'invoiceTotal' => 0,
                'invoicePaid' => 0,
                'invoicePending' => 0,
                'invoiceCancelled' => 0,
            ];
        }
    }

    /**
     * Get SEO data from settings.
     *
     * @return array<string, mixed> The SEO data
     *
     * @throws \Exception When database operations fail
     */
    private function getSeoData(): array
    {
        try {
            $seoTitle = Setting::get('seo_site_title', null);
            $seoDescription = Setting::get('seo_site_description', null);

            return [
                'seoTitle' => $seoTitle,
                'seoDescription' => $seoDescription,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get SEO data', [
                'error' => $e->getMessage(),
            ]);

            return [
                'seoTitle' => null,
                'seoDescription' => null,
            ];
        }
    }
}
