<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\KbArticle;
use App\Models\License;
use App\Models\LicenseLog;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Helpers\SecureFileHelper;

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
            $stats = [
                'products' => Product::count(),
                'customers' => User::count(),
                'licenses_active' => License::where('status', 'active')->count(),
                'tickets_open' => Ticket::whereIn('status', ['open', 'pending'])->count(),
                'kbArticles' => KbArticle::count(),
            ];
            // Invoice monetary statistics
            $invoiceTotalCount = Invoice::count();
            $invoiceTotalAmount = (float)Invoice::sum('amount');
            $invoicePaidAmount = (float)Invoice::where('status', 'paid')->sum('amount');
            $invoicePaidCount = Invoice::where('status', 'paid')->count();
            $invoiceDueSoonAmount = (float)Invoice::where('status', 'pending')
                ->where('due_date', '<=', now()->addDays(7))
                ->sum('amount');
            // Unpaid includes pending, overdue and cancelled
            $invoiceUnpaidAmount = (float)Invoice::where('status', '!=', 'paid')->sum('amount');
            // Cancelled invoices
            $invoiceCancelledCount = Invoice::where('status', 'cancelled')->count();
            $invoiceCancelledAmount = (float)Invoice::where('status', 'cancelled')->sum('amount');
            $stats['invoices_count'] = $invoiceTotalCount;
            $stats['invoices_total_amount'] = $invoiceTotalAmount;
            $stats['invoices_paid_amount'] = $invoicePaidAmount;
            $stats['invoices_paid_count'] = $invoicePaidCount;
            $stats['invoices_due_soon_amount'] = $invoiceDueSoonAmount;
            $stats['invoices_unpaid_amount'] = $invoiceUnpaidAmount;
            $stats['invoices_cancelled_count'] = $invoiceCancelledCount;
            $stats['invoices_cancelled_amount'] = $invoiceCancelledAmount;
            // API Statistics
            $stats['api_requests_today'] = LicenseLog::whereDate('createdAt', today())->count();
            $stats['api_requests_this_month'] = LicenseLog::whereMonth('createdAt', now()->month)->count();
            $stats['api_success_rate'] = $this->calculateApiSuccessRate();
            $stats['api_errors_today'] = $this->getApiErrorsToday();
            $stats['api_errors_this_month'] = $this->getApiErrorsThisMonth();
            $latestTickets = Ticket::latest()->with('user')->limit(5)->get();
            $latestLicenses = License::latest()->with('product', 'user')->limit(5)->get();
            // Read maintenance mode from cached settings. If true -> site is in maintenance (Offline)
            $isMaintenance = Setting::get('maintenance_mode', false);
            DB::commit();
            return view('admin.dashboard', ['stats' => $stats, 'latestTickets' => $latestTickets, 'latestLicenses' => $latestLicenses, 'isMaintenance' => $isMaintenance]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Dashboard data loading failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return fallback data
            $stats = [
                'products' => 0,
                'customers' => 0,
                'licenses_active' => 0,
                'tickets_open' => 0,
                'kbArticles' => 0,
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
            return view('admin.dashboard', ['stats' => $stats, 'isMaintenance' => $isMaintenance]);
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
            DB::beginTransaction();
            $activeLicenses = License::where('status', 'active')->count();
            $expiredLicenses = License::where('status', 'expired')->count();
            $pendingRequests = Ticket::whereIn('status', ['open', 'pending'])->count();
            $totalProducts = Product::count();
            DB::commit();
            return response()->json([
                'labels' => ['Active Licenses', 'Expired Licenses', 'Pending Requests', 'Total Products'],
                'data' => [$activeLicenses, $expiredLicenses, $pendingRequests, $totalProducts],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('System overview data loading failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return fallback data
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
            // Use actual enum values defined in the licenses table: regular / extended
            $regularLicenses = License::where('licenseType', 'regular')->count();
            $extendedLicenses = License::where('licenseType', 'extended')->count();
            return response()->json([
                'labels' => ['Regular', 'Extended'],
                'data' => [$regularLicenses, $extendedLicenses],
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            // License distribution data error handled gracefully
            // Return fallback data
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
            DB::beginTransaction();
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
            $year = isset($validatedArray['year']) && is_numeric($validatedArray['year']) ? (int)$validatedArray['year'] : (int)date('Y');
            if ($period === 'monthly') {
                $data = [];
                $labels = [];
                for ($month = 1; $month <= 12; $month++) {
                    $startDate = Carbon::create($year, $month, 1)?->startOfMonth();
                    $endDate = Carbon::create($year, $month, 1)?->endOfMonth();
                    // Calculate revenue from licenses created in this month
                    $monthlyRevenue = License::join('products', 'licenses.productId', '=', 'products.id')
                        ->whereBetween('licenses.createdAt', [$startDate, $endDate])
                        ->sum('products.price');
                    $data[] = (float)$monthlyRevenue;
                    $labels[] = Carbon::create($year, $month, 1)?->format('M');
                }
            } elseif ($period === 'quarterly') {
                $data = [];
                $labels = ['Q1', 'Q2', 'Q3', 'Q4'];
                for ($quarter = 1; $quarter <= 4; $quarter++) {
                    $startMonth = ($quarter - 1) * 3 + 1;
                    $endMonth = $quarter * 3;
                    $startDate = Carbon::create($year, $startMonth, 1)?->startOfMonth();
                    $endDate = Carbon::create($year, $endMonth, 1)?->endOfMonth();
                    $quarterlyRevenue = License::join('products', 'licenses.productId', '=', 'products.id')
                        ->whereBetween('licenses.createdAt', [$startDate, $endDate])
                        ->sum('products.price');
                    $data[] = (float)$quarterlyRevenue;
                }
            } else { // yearly
                $currentYear = date('Y');
                $data = [];
                $labels = [];
                for ($y = $currentYear - 4; $y <= $currentYear; $y++) {
                    $startDate = Carbon::create((int)$y, 1, 1)?->startOfYear();
                    $endDate = Carbon::create((int)$y, 12, 31)?->endOfYear();
                    $yearlyRevenue = License::join('products', 'licenses.productId', '=', 'products.id')
                        ->whereBetween('licenses.createdAt', [$startDate, $endDate])
                        ->sum('products.price');
                    $data[] = (float)$yearlyRevenue;
                    $labels[] = $y;
                }
            }
            DB::commit();
            return response()->json([
                'labels' => $labels,
                'data' => $data,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Revenue data loading failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'period' => $request->get('period'),
                'year' => $request->get('year'),
            ]);
            // Return fallback data
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
            $today = Carbon::today();
            $data = [];
            $labels = [];
            // Get activity data for the last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $startOfDay = $date->copy()->startOfDay();
                $endOfDay = $date->copy()->endOfDay();
                // Sum total activity counts for the day (tickets created + licenses created)
                $ticketsCount = Ticket::whereBetween('createdAt', [$startOfDay, $endOfDay])->count();
                $licensesCount = License::whereBetween('createdAt', [$startOfDay, $endOfDay])->count();
                $dailyTotal = $ticketsCount + $licensesCount;
                $data[] = $dailyTotal;
                $labels[] = $date->format('M j');
            }
            return response()->json([
                'labels' => $labels,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            // Activity timeline data error handled gracefully
            // Return fallback data
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
     *     "kbArticles": 25
     * }
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'products' => Product::count(),
                'customers' => User::count(),
                'licenses_active' => License::where('status', 'active')->count(),
                'licenses_expired' => License::where('status', 'expired')->count(),
                'tickets_open' => Ticket::whereIn('status', ['open', 'pending'])->count(),
                'tickets_closed' => Ticket::where('status', 'closed')->count(),
                'kbArticles' => KbArticle::count(),
            ];
            return response()->json($stats);
        } catch (\Exception $e) {
            // Log the error for debugging
            // Dashboard stats error handled gracefully
            // Return fallback data
            return response()->json([
                'products' => 0,
                'customers' => 0,
                'licenses_active' => 0,
                'licenses_expired' => 0,
                'tickets_open' => 0,
                'tickets_closed' => 0,
                'kbArticles' => 0,
            ]);
        }
    }
    /**
     * Calculate API success rate.
     *
     * Calculates the percentage of successful API requests out of total requests
     * based on license verification logs.
     *
     * @return float The API success rate as a percentage
     *
     * @version 1.0.6
     */
    private function calculateApiSuccessRate(): float
    {
        $totalRequests = LicenseLog::count();
        if ($totalRequests === 0) {
            return 0.0;
        }
        $successfulRequests = LicenseLog::where('status', 'success')->count();
        return round(($successfulRequests / $totalRequests) * 100, 2);
    }
    /**
     * Get API errors today from Laravel logs.
     *
     * Counts the number of API errors that occurred today by parsing
     * the Laravel log file for license verification errors.
     *
     * @return int The number of API errors today
     *
     * @version 1.0.6
     */
    private function getApiErrorsToday(): int
    {
        $logFile = storage_path('logs/laravel.log');
        if (! SecureFileHelper::fileExists($logFile)) {
            return 0;
        }
        $today = now()->format('Y-m-d');
        $errorCount = 0;
        $handle = fopen($logFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (
                    strpos($line, $today) !== false &&
                    strpos($line, 'License verification error') !== false
                ) {
                    $errorCount++;
                }
            }
            SecureFileHelper::closeFile($handle);
        }
        return $errorCount;
    }
    /**
     * Get API errors this month from Laravel logs.
     *
     * Counts the number of API errors that occurred this month by parsing
     * the Laravel log file for license verification errors.
     *
     * @return int The number of API errors this month
     *
     * @version 1.0.6
     */
    private function getApiErrorsThisMonth(): int
    {
        $logFile = storage_path('logs/laravel.log');
        if (! SecureFileHelper::fileExists($logFile)) {
            return 0;
        }
        $month = now()->format('Y-m');
        $errorCount = 0;
        $handle = fopen($logFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (
                    strpos($line, $month) !== false &&
                    strpos($line, 'License verification error') !== false
                ) {
                    $errorCount++;
                }
            }
            SecureFileHelper::closeFile($handle);
        }
        return $errorCount;
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
            DB::beginTransaction();
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
            $days = isset($validatedArray['days']) && is_numeric($validatedArray['days']) ? (int)$validatedArray['days'] : 7;
            $data = [];
            $labels = [];
            $successData = [];
            $failedData = [];
            if ($period === 'daily') {
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $startOfDay = $date->copy()->startOfDay();
                    $endOfDay = $date->copy()->endOfDay();
                    $totalRequests = LicenseLog::whereBetween('createdAt', [$startOfDay, $endOfDay])->count();
                    $successRequests = LicenseLog::whereBetween('createdAt', [$startOfDay, $endOfDay])
                        ->where('status', 'success')->count();
                    $failedRequests = LicenseLog::whereBetween('createdAt', [$startOfDay, $endOfDay])
                        ->where('status', 'failed')->count();
                    $data[] = $totalRequests;
                    $successData[] = $successRequests;
                    $failedData[] = $failedRequests;
                    $labels[] = $date->format('M j');
                }
            } elseif ($period === 'hourly') {
                for ($i = 23; $i >= 0; $i--) {
                    $hour = now()->subHours($i);
                    $startOfHour = $hour->copy()->startOfHour();
                    $endOfHour = $hour->copy()->endOfHour();
                    $totalRequests = LicenseLog::whereBetween('createdAt', [$startOfHour, $endOfHour])->count();
                    $successRequests = LicenseLog::whereBetween('createdAt', [$startOfHour, $endOfHour])
                        ->where('status', 'success')->count();
                    $failedRequests = LicenseLog::whereBetween('createdAt', [$startOfHour, $endOfHour])
                        ->where('status', 'failed')->count();
                    $data[] = $totalRequests;
                    $successData[] = $successRequests;
                    $failedData[] = $failedRequests;
                    $labels[] = $hour->format('H:i');
                }
            }
            DB::commit();
            return response()->json([
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Total Requests',
                        'data' => $data,
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                    ],
                    [
                        'label' => 'Successful',
                        'data' => $successData,
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                    ],
                    [
                        'label' => 'Failed',
                        'data' => $failedData,
                        'borderColor' => '#EF4444',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'fill' => true,
                    ],
                ],
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
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
            $today = now()->startOfDay();
            $yesterday = now()->subDay()->startOfDay();
            // Today's metrics
            $todayRequests = LicenseLog::whereDate('createdAt', today())->count();
            $todaySuccess = LicenseLog::whereDate('createdAt', today())->where('status', 'success')->count();
            $todayFailed = LicenseLog::whereDate('createdAt', today())->where('status', 'failed')->count();
            // Yesterday's metrics
            $yesterdayRequests = LicenseLog::whereDate('createdAt', $yesterday)->count();
            $yesterdaySuccess = LicenseLog::whereDate('createdAt', $yesterday)->where('status', 'success')->count();
            $yesterdayFailed = LicenseLog::whereDate('createdAt', $yesterday)->where('status', 'failed')->count();
            // Top domains
            $topDomains = LicenseLog::selectRaw('domain, COUNT(*) as count')
                ->where('createdAt', '>=', now()->subDays(7))
                ->groupBy('domain')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();
            return response()->json([
                'today' => [
                    'total' => $todayRequests,
                    'success' => $todaySuccess,
                    'failed' => $todayFailed,
                    'success_rate' => $todayRequests > 0 ? round(($todaySuccess / $todayRequests) * 100, 2) : 0,
                ],
                'yesterday' => [
                    'total' => $yesterdayRequests,
                    'success' => $yesterdaySuccess,
                    'failed' => $yesterdayFailed,
                    'success_rate' => $yesterdayRequests > 0
                        ? round(($yesterdaySuccess / $yesterdayRequests) * 100, 2)
                        : 0,
                ],
                'top_domains' => $topDomains,
            ]);
        } catch (\Exception $e) {
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
            DB::beginTransaction();
            // Clear application cache
            Artisan::call('cache:clear');
            // Clear config cache
            Artisan::call('config:clear');
            // Clear route cache
            Artisan::call('route:clear');
            // Clear view cache
            Artisan::call('view:clear');
            // Clear compiled classes
            Artisan::call('clear-compiled');
            // Clear license-specific caches if any
            Cache::flush(); // Clear all cache keys
            DB::commit();
            return redirect()->back()->with('success', 'All caches cleared successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cache clearing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to clear caches. Please try again.');
        }
    }
}
