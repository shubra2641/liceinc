<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Dashboard\DashboardStatsService;
use App\Services\Dashboard\DashboardChartService;
use App\Services\Dashboard\DashboardCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Admin Dashboard Controller with enhanced security.
 *
 * This controller handles the admin dashboard functionality including statistics,
 * charts, analytics, and system overview data. It provides comprehensive
 * dashboard management with real-time data visualization.
 *
 * Features:
 * - Dashboard statistics and metrics
 * - System overview and analytics
 * - License distribution tracking
 * - Revenue and financial reporting
 * - Activity timeline monitoring
 * - API performance metrics
 * - Cache management and optimization
 * - Comprehensive error handling with database transactions
 * - Real-time data visualization
 * - Enhanced security measures (XSS protection, input validation)
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
 *
 * @example
 * // Get dashboard statistics
 * GET /admin/dashboard/stats
 *
 * // Get system overview data
 * GET /admin/dashboard/system-overview
 *
 * // Get revenue data for specific period
 * GET /admin/dashboard/revenue?period=monthly&year=2024
 */
class DashboardController extends Controller
{
    public function __construct(
        private DashboardStatsService $statsService,
        private DashboardChartService $chartService,
        private DashboardCacheService $cacheService
    ) {
    }
    /**
     * Display the admin dashboard with comprehensive statistics and enhanced security.
     *
     * Shows the main admin dashboard with key metrics including products,
     * customers, licenses, tickets, invoices, and API statistics.
     *
     * @return \Illuminate\View\View The dashboard view with statistics
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access the dashboard
     * GET /admin/dashboard
     *
     * // Returns view with:
     * // - Product count
     * // - Customer count
     * // - Active licenses
     * // - Open tickets
     * // - KB articles
     * // - Invoice statistics
     * // - API metrics
     */
    public function index()
    {
        try {
            DB::beginTransaction();
            
            $basicStats = $this->statsService->getBasicStats();
            $invoiceStats = $this->statsService->getInvoiceStats();
            $apiStats = $this->statsService->getApiStats();
            $latestData = $this->statsService->getLatestData();
            
            $stats = array_merge($basicStats, $invoiceStats, $apiStats);
            $isMaintenance = Setting::get('maintenance_mode', false);
            
            DB::commit();
            
            return view('admin.dashboard', [
                'stats' => $stats,
                'latestTickets' => $latestData['latestTickets'],
                'latestLicenses' => $latestData['latestLicenses'],
                'isMaintenance' => $isMaintenance
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Dashboard data loading failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->getFallbackDashboardData();
        }
    }
    /**
     * Get system overview chart data with enhanced security.
     *
     * Retrieves data for the system overview chart showing active licenses,
     * expired licenses, pending requests, and total products.
     *
     * @return JsonResponse JSON response with chart data
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * GET /admin/dashboard/system-overview
     *
     * // Success response:
     * {
     *     "labels": ["Active Licenses", "Expired Licenses", "Pending Requests", "Total Products"],
     *     "data": [150, 25, 10, 5]
     * }
     */
    public function getSystemOverviewData(): JsonResponse
    {
        try {
            $data = $this->chartService->getSystemOverviewData();
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('System overview data loading failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'labels' => ['Active Licenses', 'Expired Licenses', 'Pending Requests', 'Total Products'],
                'data' => [0, 0, 0, 0],
            ]);
        }
    }
    /**
     * Get license distribution chart data.
     *
     * Retrieves data for the license distribution chart showing the count
     * of regular and extended licenses in the system.
     *
     * @return JsonResponse JSON response with license distribution data
     *
     * @version 1.0.6
     *
     * @example
     * // Request:
     * GET /admin/dashboard/license-distribution
     *
     * // Success response:
     * {
     *     "labels": ["Regular", "Extended"],
     *     "data": [120, 30]
     * }
     */
    public function getLicenseDistributionData(): JsonResponse
    {
        try {
            $data = $this->chartService->getLicenseDistributionData();
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('License distribution data loading failed', ['error' => $e->getMessage()]);
            return response()->json([
                'labels' => ['Regular', 'Extended'],
                'data' => [0, 0],
            ]);
        }
    }
    /**
     * Get revenue chart data with enhanced security.
     *
     * Retrieves revenue data for charts based on the specified period
     * (monthly, quarterly, or yearly) and year.
     *
     * @param  Request  $request  The HTTP request containing period and year parameters
     *
     * @return JsonResponse JSON response with revenue chart data
     *
     * @throws ValidationException When validation fails
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * GET /admin/dashboard/revenue?period=monthly&year=2024
     *
     * // Success response:
     * {
     *     "labels": ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
     *     "data": [1500.00, 2300.00, 1800.00, 2100.00, 1900.00, 2400.00]
     * }
     */
    public function getRevenueData(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'period' => ['sometimes', 'string', 'in:monthly, quarterly, yearly'],
                'year' => ['sometimes', 'integer', 'min:2020', 'max:2030'],
            ], [
                'period.in' => 'Period must be one of: monthly, quarterly, yearly.',
                'year.min' => 'Year must be at least 2020.',
                'year.max' => 'Year cannot exceed 2030.',
            ]);
            
            $validatedArray = is_array($validated) ? $validated : [];
            $period = $this->sanitizeInput($validatedArray['period'] ?? 'monthly');
            $year = isset($validatedArray['year']) && is_numeric($validatedArray['year'])
                ? (int)$validatedArray['year']
                : (int)date('Y');
            
            $data = $this->chartService->getRevenueData($period, $year);
            return response()->json($data);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Revenue data loading failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'period' => $request->get('period'),
                'year' => $request->get('year'),
            ]);
            
            return response()->json([
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'data' => [0, 0, 0, 0, 0, 0],
            ]);
        }
    }
    /**
     * Get activity timeline chart data.
     *
     * Retrieves activity data for the last 7 days showing daily totals
     * of tickets created and licenses created.
     *
     * @return JsonResponse JSON response with activity timeline data
     *
     * @version 1.0.6
     *
     * @example
     * // Request:
     * GET /admin/dashboard/activity-timeline
     *
     * // Success response:
     * {
     *     "labels": ["Jan 15", "Jan 16", "Jan 17", "Jan 18", "Jan 19", "Jan 20", "Jan 21"],
     *     "data": [5, 8, 3, 12, 7, 9, 4]
     * }
     */
    public function getActivityTimelineData(): JsonResponse
    {
        try {
            $data = $this->chartService->getActivityTimelineData();
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Activity timeline data loading failed', ['error' => $e->getMessage()]);
            return response()->json([
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'data' => [0, 0, 0, 0, 0, 0, 0],
            ]);
        }
    }
    /**
     * Get dashboard statistics.
     *
     * Retrieves comprehensive dashboard statistics including products,
     * customers, licenses, tickets, and knowledge base articles.
     *
     * @return JsonResponse JSON response with dashboard statistics
     *
     * @version 1.0.6
     *
     * @example
     * // Request:
     * GET /admin/dashboard/stats
     *
     * // Success response:
     * {
     *     "products": 5,
     *     "customers": 150,
     *     "licenses_active": 120,
     *     "licenses_expired": 30,
     *     "tickets_open": 8,
     *     "tickets_closed": 45,
     *     "kb_articles": 25
     * }
     */
    public function getStats(): JsonResponse
    {
        try {
            $basicStats = $this->statsService->getBasicStats();
            $additionalStats = [
                'licenses_expired' => \App\Models\License::where('status', 'expired')->count(),
                'tickets_closed' => \App\Models\Ticket::where('status', 'closed')->count(),
            ];
            
            $stats = array_merge($basicStats, $additionalStats);
            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Dashboard stats error', ['error' => $e->getMessage()]);
            return response()->json([
                'products' => 0,
                'customers' => 0,
                'licenses_active' => 0,
                'licenses_expired' => 0,
                'tickets_open' => 0,
                'tickets_closed' => 0,
                'kb_articles' => 0,
            ]);
        }
    }
    /**
     * Get API requests chart data with enhanced security.
     *
     * Retrieves API request data for charts showing total, successful,
     * and failed requests over a specified period (daily or hourly).
     *
     * @param  Request  $request  The HTTP request containing period and days parameters
     *
     * @return JsonResponse JSON response with API requests chart data
     *
     * @throws ValidationException When validation fails
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * GET /admin/dashboard/api-requests?period=daily&days=7
     *
     * // Success response:
     * {
     *     "labels": ["Jan 15", "Jan 16", "Jan 17"],
     *     "datasets": [
     *         {"label": "Total Requests", "data": [100, 120, 90]},
     *         {"label": "Successful", "data": [95, 115, 85]},
     *         {"label": "Failed", "data": [5, 5, 5]}
     *     ]
     * }
     */
    public function getApiRequestsData(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'period' => ['sometimes', 'string', 'in:daily, hourly'],
                'days' => ['sometimes', 'integer', 'min:1', 'max:30'],
            ], [
                'period.in' => 'Period must be one of: daily, hourly.',
                'days.min' => 'Days must be at least 1.',
                'days.max' => 'Days cannot exceed 30.',
            ]);
            
            $validatedArray = is_array($validated) ? $validated : [];
            $period = $this->sanitizeInput($validatedArray['period'] ?? 'daily');
            $days = isset($validatedArray['days']) && is_numeric($validatedArray['days'])
                ? (int)$validatedArray['days']
                : 7;
            
            $data = $this->chartService->getApiRequestsData($period, $days);
            return response()->json($data);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('API requests data loading failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'period' => $request->get('period'),
                'days' => $request->get('days'),
            ]);
            
            return response()->json([
                'labels' => [],
                'datasets' => [],
            ]);
        }
    }
    /**
     * Get API performance metrics.
     *
     * Retrieves comprehensive API performance metrics including today's
     * and yesterday's statistics, success rates, and top domains.
     *
     * @return JsonResponse JSON response with API performance data
     *
     * @version 1.0.6
     *
     * @example
     * // Request:
     * GET /admin/dashboard/api-performance
     *
     * // Success response:
     * {
     *     "today": {
     *         "total": 150,
     *         "success": 145,
     *         "failed": 5,
     *         "success_rate": 96.67
     *     },
     *     "yesterday": {
     *         "total": 120,
     *         "success": 115,
     *         "failed": 5,
     *         "success_rate": 95.83
     *     },
     *     "top_domains": [
     *         {"domain": "example.com", "count": 25},
     *         {"domain": "test.com", "count": 20}
     *     ]
     * }
     */
    public function getApiPerformanceData(): JsonResponse
    {
        try {
            $data = $this->chartService->getApiPerformanceData();
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('API performance data loading failed', ['error' => $e->getMessage()]);
            return response()->json([
                'today' => ['total' => 0, 'success' => 0, 'failed' => 0, 'success_rate' => 0],
                'yesterday' => ['total' => 0, 'success' => 0, 'failed' => 0, 'success_rate' => 0],
                'top_domains' => [],
            ]);
        }
    }
    /**
     * Clear all application caches with enhanced security.
     *
     * Clears all application caches including application cache, config cache,
     * route cache, view cache, compiled classes, and license-specific caches.
     *
     * @return \Illuminate\Http\RedirectResponse Redirect back with success message
     *
     * @throws \Exception When cache clearing operations fail
     *
     * @example
     * // Request:
     * POST /admin/dashboard/clear-cache
     *
     * // Response: Redirect back with success message
     * // "All caches cleared successfully!"
     */
    public function clearCache()
    {
        try {
            $result = $this->cacheService->clearAllCaches();
            
            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Cache clearing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to clear caches. Please try again.');
        }
    }

    /**
     * Get fallback dashboard data
     */
    private function getFallbackDashboardData()
    {
        $stats = [
            'products' => 0,
            'customers' => 0,
            'licenses_active' => 0,
            'tickets_open' => 0,
            'kb_articles' => 0,
            'invoices_count' => 0,
            'invoices_total_amount' => 0,
            'invoices_paid_amount' => 0,
            'invoices_paid_count' => 0,
            'invoices_due_soon_amount' => 0,
            'invoices_unpaid_amount' => 0,
            'invoices_cancelled_count' => 0,
            'invoices_cancelled_amount' => 0,
            'api_requests_today' => 0,
            'api_requests_this_month' => 0,
            'api_success_rate' => 0,
            'api_errors_today' => 0,
            'api_errors_this_month' => 0,
        ];
        
        $isMaintenance = Setting::get('maintenance_mode', false);
        
        return view('admin.dashboard', [
            'stats' => $stats,
            'latestTickets' => collect(),
            'latestLicenses' => collect(),
            'isMaintenance' => $isMaintenance
        ]);
    }

    /**
     * Sanitize input string
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
