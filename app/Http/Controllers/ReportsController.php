<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\License;
use App\Models\LicenseDomain;
use App\Models\LicenseLog;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;
use App\Helpers\SecureFileHelper;

/**
 * Reports Controller with enhanced security and comprehensive reporting functionality.
 *
 * This controller provides comprehensive reporting functionality including
 * dashboard metrics, data visualization, export capabilities, and enhanced security measures
 * with comprehensive error handling and logging.
 *
 * Features:
 * - Enhanced dashboard metrics and analytics
 * - Comprehensive data visualization with charts
 * - Export functionality for PDF and CSV formats
 * - License and user analytics
 * - API usage statistics and monitoring
 * - Revenue and financial reporting
 * - Comprehensive error handling and logging
 * - Input validation and sanitization
 * - Enhanced security measures for report operations
 * - Database transaction support for data integrity
 * - Proper error responses for different scenarios
 * - Comprehensive logging for security monitoring
 *
 * @example
 * // Get reports dashboard
 * GET /admin/reports
 *
 * // Export reports
 * POST /admin/reports/export
 * {
 *     "format": "csv",
 *     "dateFrom": "2024-01-01",
 *     "dateTo": "2024-12-31"
 * }
 */
class ReportsController extends Controller
{
    /**
     * Display the reports dashboard with enhanced security and comprehensive metrics.
     *
     * This method displays the comprehensive reports dashboard with various metrics,
     * charts, and analytics including license statistics, revenue data, user analytics,
     * and system overview with enhanced security measures.
     *
     * @return View The reports dashboard view
     *
     * @throws \Exception When dashboard data retrieval fails
     *
     * @example
     * // Display reports dashboard
     * $view = $reportsController->index();
     */
    public function index(): View
    {
        try {
            /**
 * @var View $result
*/
            $result = $this->transaction(function () {
                // Basic metrics
                $totalLicenses = License::count();
                $activeLicenses = License::where('status', 'active')->count();
                $expiredLicenses = License::where('status', 'expired')->count();
                $totalUsers = User::count();
                $totalProducts = Product::count();
                $totalTickets = Ticket::count();
                $openTickets = Ticket::whereIn('status', ['open', 'pending'])->count();
                // Monthly license data for charts - Last 3 months
                $monthlyLicensesRaw = License::select(
                    DB::raw('YEAR(createdAt) as year'),
                    DB::raw('MONTH(createdAt) as month'),
                    DB::raw('COUNT(*) as count'),
                )
                    ->where('createdAt', '>=', now()->subMonths(3))
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();
                // Monthly revenue data for charts (from licenses) - Last 3 months
                $monthlyRevenueRaw = License::select(
                    DB::raw('YEAR(licenses.created_at) as year'),
                    DB::raw('MONTH(licenses.created_at) as month'),
                    DB::raw('SUM(products.price) as revenue'),
                )
                    ->join('products', 'licenses.product_id', '=', 'products.id')
                    ->where('licenses.created_at', '>=', now()->subMonths(3))
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();
                // Convert to Chart.js format with last 3 months
                $last3Months = [];
                $last3MonthsLabels = [];
                for ($i = 2; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $last3Months[] = $date->format('Y-m');
                    $last3MonthsLabels[] = $date->format('M Y'); // More readable format
                }
                $monthlyRevenueData = [];
                foreach ($last3Months as $month) {
                    $found = $monthlyRevenueRaw->first(function ($item) use ($month) {
                        return (is_string($item->year) ? $item->year : '') . '-'
                            . str_pad((string)(is_numeric($item->month) ? $item->month : 0), 2, '0', STR_PAD_LEFT)
                            === $month;
                    });
                    $monthlyRevenueData[] = $found ? (float)(is_numeric($found->revenue) ? $found->revenue : 0) : 0;
                }
                $monthlyRevenue = [
                    'labels' => $last3MonthsLabels,
                    'datasets' => [[
                        'label' => __('app.monthly_revenue'),
                        'data' => $monthlyRevenueData,
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'pointBackgroundColor' => '#10b981',
                        'pointBorderColor' => '#ffffff',
                        'pointBorderWidth' => 2,
                        'pointRadius' => 5,
                    ]],
                ];
                // Convert to Chart.js format with last 3 months
                $monthlyLicensesData = [];
                foreach ($last3Months as $month) {
                    $found = $monthlyLicensesRaw->first(function ($item) use ($month) {
                        return (is_string($item->year) ? $item->year : '') . '-'
                            . str_pad((string)(is_numeric($item->month) ? $item->month : 0), 2, '0', STR_PAD_LEFT)
                            === $month;
                    });
                    $monthlyLicensesData[] = $found ? (int)(is_numeric($found->count) ? $found->count : 0) : 0;
                }
                $monthlyLicenses = [
                    'labels' => $last3MonthsLabels,
                    'datasets' => [[
                        'label' => __('app.licenses_created'),
                        'data' => $monthlyLicensesData,
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'pointBackgroundColor' => '#3b82f6',
                        'pointBorderColor' => '#ffffff',
                        'pointBorderWidth' => 2,
                        'pointRadius' => 5,
                    ]],
                ];
                // License type distribution (regular vs extended)
                $licenseTypeDataRaw = License::select('licenseType', DB::raw('COUNT(*) as count'))
                    ->groupBy('licenseType')
                    ->get();
                // Convert to Chart.js format
                $licenseTypeData = [
                    'labels' => $licenseTypeDataRaw->pluck('licenseType')->map(function ($type) {
                        return __('app.' . (is_string($type) ? $type : '')) ?: ucfirst(is_string($type) ? $type : '');
                    })->toArray(),
                    'datasets' => [[
                        'data' => $licenseTypeDataRaw->pluck('count')->toArray(),
                        'backgroundColor' => ['#3b82f6', '#10b981'],
                        'borderWidth' => 0,
                    ]],
                ];
                // License status distribution
                $licenseStatusDataRaw = License::select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get();
                // Convert to Chart.js format
                $licenseStatusData = [
                    'labels' => $licenseStatusDataRaw->pluck('status')->map(function ($status) {
                        return __('app.' . (is_string($status) ? $status : '')) ?: ucfirst(is_string($status) ? $status : '');
                    })->toArray(),
                    'datasets' => [[
                        'data' => $licenseStatusDataRaw->pluck('count')->toArray(),
                        'backgroundColor' => ['#4F46E5', '#10B981', '#EF4444', '#F59E0B', '#8B5CF6'],
                        'borderWidth' => 0,
                    ]],
                ];
                // API calls data (from license logs)
                $apiCallsDataRaw = LicenseLog::getApiCallsByDate(30);
                // Convert to Chart.js format
                $apiCallsData = [
                    'labels' => $apiCallsDataRaw->pluck('date')->toArray(),
                    'datasets' => [[
                        'label' => __('app.api_calls'),
                        'data' => $apiCallsDataRaw->pluck('count')->toArray(),
                        'borderColor' => '#8b5cf6',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ]],
                ];
                // API status distribution
                $apiStatusDataRaw = LicenseLog::getApiStatusDistribution(30);
                // Convert to Chart.js format
                $apiStatusData = [
                    'labels' => $apiStatusDataRaw->pluck('status')->map(function ($status) {
                        return __('app.' . (is_string($status) ? $status : '')) ?: ucfirst(is_string($status) ? $status : '');
                    })->toArray(),
                    'datasets' => [[
                        'label' => __('app.api_calls'),
                        'data' => $apiStatusDataRaw->pluck('count')->toArray(),
                        'backgroundColor' => ['#10B981', '#EF4444', '#F59E0B', '#4F46E5', '#8B5CF6'],
                        'borderWidth' => 0,
                    ]],
                ];
                // Top products by license count
                $topProducts = Product::withCount('licenses')
                    ->orderBy('licenses_count', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($product) {
                        $product->revenue = $product->licenses->sum(function ($license) {
                            return $license->product->price ?? 0;
                        });
                        return $product;
                    });
                // Recent license activities
                $recentActivities = LicenseLog::with(['license', 'license.user'])
                    ->orderBy('createdAt', 'desc')
                    ->limit(20)
                    ->get();
                // Calculate total revenue (from products table via licenses relationship)
                $totalRevenue = License::join('products', 'licenses.productId', '=', 'products.id')
                    ->sum('products.price') ?: 0;
                // --- Invoices: monthly amounts and status totals - Last 3 months ---
                $invoiceMonthlyRaw = Invoice::select(
                    DB::raw('YEAR(createdAt) as year'),
                    DB::raw('MONTH(createdAt) as month'),
                    DB::raw('SUM(amount) as total'),
                )
                    ->where('createdAt', '>=', now()->subMonths(3))
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();
                // Convert to Chart.js format with last 3 months
                $invoiceMonthlyData = [];
                foreach ($last3Months as $month) {
                    $found = $invoiceMonthlyRaw->first(function ($item) use ($month) {
                        return (is_string($item->year) ? $item->year : '') . '-' . str_pad((string)(is_numeric($item->month) ? $item->month : 0), 2, '0', STR_PAD_LEFT) === $month;
                    });
                    $invoiceMonthlyData[] = $found ? (float)(is_numeric($found->total) ? $found->total : 0) : 0;
                }
                $invoiceMonthlyAmounts = [
                    'labels' => $last3MonthsLabels,
                    'datasets' => [[
                        'label' => __('app.invoice_amounts'),
                        'data' => $invoiceMonthlyData,
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'pointBackgroundColor' => '#10b981',
                        'pointBorderColor' => '#ffffff',
                        'pointBorderWidth' => 2,
                        'pointRadius' => 5,
                    ]],
                ];
                // Status-based monetary totals
                $invoiceStatusTotals = [
                    'paid' => (float)Invoice::where('status', 'paid')->sum('amount'),
                    'due_soon' => (float)Invoice::where('status', 'pending')
                        ->whereBetween('due_date', [now(), now()->addDays(7)])
                        ->sum('amount'),
                    'pending' => (float)Invoice::where('status', 'pending')->sum('amount'),
                    'cancelled' => (float)Invoice::whereIn('status', ['cancelled', 'overdue'])->sum('amount'),
                ];
                // Domain statistics
                $totalDomains = LicenseDomain::count();
                $activeDomains = LicenseDomain::where('status', 'active')->count();
                // User registrations data for charts - Last 3 months
                $userRegistrationsRaw = User::select(
                    DB::raw('YEAR(createdAt) as year'),
                    DB::raw('MONTH(createdAt) as month'),
                    DB::raw('COUNT(*) as count'),
                )
                    ->where('createdAt', '>=', now()->subMonths(3))
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();
                // Convert to Chart.js format with last 3 months
                $userRegistrationsData = [];
                foreach ($last3Months as $month) {
                    $found = $userRegistrationsRaw->first(function ($item) use ($month) {
                        return (is_string($item->year) ? $item->year : '') . '-' . str_pad((string)(is_numeric($item->month) ? $item->month : 0), 2, '0', STR_PAD_LEFT) === $month;
                    });
                    $userRegistrationsData[] = $found ? (int)(is_numeric($found->count) ? $found->count : 0) : 0;
                }
                $userRegistrations = [
                    'labels' => $last3MonthsLabels,
                    'datasets' => [[
                        'label' => __('app.user_registrations'),
                        'data' => $userRegistrationsData,
                        'borderColor' => '#f59e0b',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'pointBackgroundColor' => '#f59e0b',
                        'pointBorderColor' => '#ffffff',
                        'pointBorderWidth' => 2,
                        'pointRadius' => 5,
                    ]],
                ];
                // System overview data
                $systemOverviewData = [
                    'labels' => [__('app.active_licenses'), __('app.expired_licenses'),
                        __('app.pending_requests'), __('app.total_products')],
                    'datasets' => [[
                        'data' => [$activeLicenses, $expiredLicenses, $openTickets, $totalProducts],
                        'backgroundColor' => ['#10b981', '#ef4444', '#f59e0b', '#3b82f6'],
                        'borderWidth' => 0,
                    ]],
                ];
                // Activity timeline data (last 7 days)
                $activityTimelineRaw = [];
                $today = Carbon::today();
                for ($i = 6; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i);
                    $startOfDay = $date->copy()->startOfDay();
                    $endOfDay = $date->copy()->endOfDay();
                    // Sum total activity counts for the day (tickets created + licenses created)
                    $ticketsCount = Ticket::whereBetween('createdAt', [$startOfDay, $endOfDay])->count();
                    $licensesCount = License::whereBetween('createdAt', [$startOfDay, $endOfDay])->count();
                    $dailyTotal = $ticketsCount + $licensesCount;
                    $activityTimelineRaw[] = [
                        'date' => $date->format('M j'),
                        'count' => $dailyTotal,
                    ];
                }
                // Convert to Chart.js format
                $activityTimeline = [
                    'labels' => collect($activityTimelineRaw)->pluck('date')->toArray(),
                    'datasets' => [[
                        'label' => __('app.daily_activity'),
                        'data' => collect($activityTimelineRaw)->pluck('count')->toArray(),
                        'borderColor' => '#8b5cf6',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ]],
                ];
                // For demo purposes, we'll show some sample data if there are failed API calls
                $failedApiCalls = LicenseLog::where('status', 'failed')
                    ->where('createdAt', '>=', now()->subDays(1))
                    ->select('ipAddress', DB::raw('COUNT(*) as attempts'))
                    ->groupBy('ipAddress')
                    ->having('attempts', '>=', 3)
                    ->get();
                $rateLimitedIPs = collect();
                $totalRateLimitedAttempts = 0;
                foreach ($failedApiCalls as $failedCall) {
                    $rateLimitedIPs->push([
                        'ip' => $failedCall->ipAddress,
                        'attempts' => $failedCall->attempts,
                        'blocked_until' => now()->addMinutes(15), // Default lockout time
                    ]);
                    $totalRateLimitedAttempts += is_numeric($failedCall->attempts) ? (int)$failedCall->attempts : 0;
                }
                return view('admin.reports', [
                    'totalLicenses' => $totalLicenses,
                    'activeLicenses' => $activeLicenses,
                    'expiredLicenses' => $expiredLicenses,
                    'totalUsers' => $totalUsers,
                    'totalProducts' => $totalProducts,
                    'totalTickets' => $totalTickets,
                    'openTickets' => $openTickets,
                    'totalRevenue' => $totalRevenue,
                    'monthlyLicenses' => $monthlyLicenses,
                    'monthlyRevenue' => $monthlyRevenue,
                    'activityTimeline' => $activityTimeline,
                    'systemOverviewData' => $systemOverviewData,
                    'licenseStatusData' => $licenseStatusData,
                    'licenseTypeData' => $licenseTypeData,
                    'apiCallsData' => $apiCallsData,
                    'apiStatusData' => $apiStatusData,
                    'topProducts' => $topProducts,
                    'recentActivities' => $recentActivities,
                    'totalDomains' => $totalDomains,
                    'activeDomains' => $activeDomains,
                    'userRegistrations' => $userRegistrations,
                    'rateLimitedIPs' => $rateLimitedIPs,
                    'totalRateLimitedAttempts' => $totalRateLimitedAttempts,
                    'invoiceStatusTotals' => $invoiceStatusTotals,
                    'invoiceMonthlyAmounts' => $invoiceMonthlyAmounts,
                ]);
            });
            return $result;
        } catch (Throwable $e) {
            Log::error('Failed to load reports dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return view('admin.reports', [
                'totalLicenses' => 0,
                'activeLicenses' => 0,
                'expiredLicenses' => 0,
                'totalUsers' => 0,
                'totalProducts' => 0,
                'totalTickets' => 0,
                'openTickets' => 0,
                'totalRevenue' => 0,
                'monthlyLicenses' => ['labels' => [], 'datasets' => []],
                'monthlyRevenue' => ['labels' => [], 'datasets' => []],
                'activityTimeline' => ['labels' => [], 'datasets' => []],
                'systemOverviewData' => ['labels' => [], 'datasets' => []],
                'licenseStatusData' => ['labels' => [], 'datasets' => []],
                'licenseTypeData' => ['labels' => [], 'datasets' => []],
                'apiCallsData' => ['labels' => [], 'datasets' => []],
                'apiStatusData' => ['labels' => [], 'datasets' => []],
                'topProducts' => collect(),
                'recentActivities' => collect(),
                'totalDomains' => 0,
                'activeDomains' => 0,
                'userRegistrations' => ['labels' => [], 'datasets' => []],
                'rateLimitedIPs' => collect(),
                'totalRateLimitedAttempts' => 0,
                'invoiceStatusTotals' => [],
                'invoiceMonthlyAmounts' => ['labels' => [], 'datasets' => []],
            ])->with('error', 'Failed to load reports data. Please try again.');
        }
    }
    /**
     * Get license data for AJAX requests with enhanced security and validation.
     *
     * This method retrieves license data for AJAX requests with comprehensive
     * validation and enhanced security measures.
     *
     * @param  Request  $request  The current HTTP request instance
     *
     * @return JsonResponse JSON response with license data
     *
     * @throws \Exception When license data retrieval fails
     *
     * @example
     * // Get license data
     * $response = $reportsController->getLicenseData($request);
     */
    public function getLicenseData(Request $request): JsonResponse
    {
        try {
            /**
 * @var JsonResponse $result
*/
            $result = $this->transaction(function () use ($request) {
                $period = $this->sanitizeInput($request->get('period', 'month'));
                switch ($period) {
                    case 'week':
                        $startDate = now()->subDays(7);
                        break;
                    case 'month':
                        $startDate = now()->subDays(30);
                        break;
                    case 'year':
                        $startDate = now()->subDays(365);
                        break;
                    default:
                        $startDate = now()->subDays(30);
                }
                $data = License::select(
                    DB::raw('DATE(createdAt) as date'),
                    DB::raw('COUNT(*) as count'),
                )
                    ->where('createdAt', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                Log::debug('License data retrieved successfully', [
                    'period' => $period,
                    'start_date' => $startDate->format('Y-m-d'),
                    'records_count' => $data->count(),
                    'ip' => $request->ip(),
                ]);
                return $this->successResponse($data, 'License data retrieved successfully');
            });
            return $result;
        } catch (Throwable $e) {
            Log::error('Failed to retrieve license data', [
                'error' => $e->getMessage(),
                'period' => $request->get('period', 'month'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse(
                'Failed to retrieve license data. Please try again.',
                null,
                500,
            );
        }
    }
    /**
     * Get API status data with enhanced security and validation.
     *
     * This method retrieves API status data for AJAX requests with comprehensive
     * validation and enhanced security measures.
     *
     * @param  Request  $request  The current HTTP request instance
     *
     * @return JsonResponse JSON response with API status data
     *
     * @throws \Exception When API status data retrieval fails
     *
     * @example
     * // Get API status data
     * $response = $reportsController->getApiStatusData($request);
     */
    public function getApiStatusData(Request $request): JsonResponse
    {
        try {
            /**
 * @var JsonResponse $result
*/
            $result = $this->transaction(function () use ($request) {
                $period = $this->sanitizeInput($request->get('period', 'week'));
                switch ($period) {
                    case 'day':
                        $startDate = now()->subDay();
                        break;
                    case 'week':
                        $startDate = now()->subDays(7);
                        break;
                    case 'month':
                        $startDate = now()->subDays(30);
                        break;
                    default:
                        $startDate = now()->subDays(7);
                }
                $data = LicenseLog::select(
                    'status',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('DATE(createdAt) as date'),
                )
                    ->where('createdAt', '>=', $startDate)
                    ->groupBy('status', 'date')
                    ->orderBy('date')
                    ->get();
                Log::debug('API status data retrieved successfully', [
                    'period' => $period,
                    'start_date' => $startDate->format('Y-m-d'),
                    'records_count' => $data->count(),
                    'ip' => $request->ip(),
                ]);
                return $this->successResponse($data, 'API status data retrieved successfully');
            });
            return $result;
        } catch (Throwable $e) {
            Log::error('Failed to retrieve API status data', [
                'error' => $e->getMessage(),
                'period' => $request->get('period', 'week'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse(
                'Failed to retrieve API status data. Please try again.',
                null,
                500,
            );
        }
    }
    /**
     * Export reports data to PDF or CSV format with enhanced security and validation.
     *
     * This method exports reports data to PDF or CSV format with comprehensive
     * validation and enhanced security measures.
     *
     * @param  Request  $request  The current HTTP request instance
     *
     * @return \Illuminate\Http\Response The export file response
     *
     * @throws \Exception When export operation fails
     *
     * @example
     * // Export reports to CSV
     * $response = $reportsController->export($request);
     */
    public function export(Request $request): \Illuminate\Http\Response|JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        try {
            /**
 * @var \Illuminate\Http\Response|JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse $result
*/
            $result = $this->transaction(function () use ($request) {
                $format = $this->sanitizeInput($request->get('format', 'pdf'));
                $dateFrom = $this->sanitizeInput($request->get('dateFrom'));
                $dateTo = $this->sanitizeInput($request->get('dateTo'));
                // Get data for export
                $data = $this->getExportData(
                    is_string($dateFrom) ? $dateFrom : null,
                    is_string($dateTo) ? $dateTo : null
                );
                Log::debug('Reports export initiated', [
                    'format' => $format,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'records_count' => is_array($data['licenses']) ? count($data['licenses']) : 0,
                    'ip' => $request->ip(),
                ]);
                if ($format === 'csv') {
                    return $this->exportToCsv($data);
                } else {
                    return $this->exportToPdf($data);
                }
            });
            return $result;
        } catch (Throwable $e) {
            Log::error('Failed to export reports', [
                'error' => $e->getMessage(),
                'format' => $request->get('format', 'pdf'),
                'dateFrom' => $request->get('dateFrom'),
                'dateTo' => $request->get('dateTo'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to export reports. Please try again.',
            ], 500);
        }
    }
    /**
     * Get data for export with enhanced security and validation.
     *
     * @param  string|null  $dateFrom  The start date for export
     * @param  string|null  $dateTo  The end date for export
     *
     * @return array<string, mixed> The export data array
     *
     * @throws \Exception When data retrieval fails
     */
    private function getExportData(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = License::with(['user', 'product']);
        if ($dateFrom) {
            $query->where('createdAt', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('createdAt', '<=', $dateTo);
        }
        $licenses = $query->get();
        return [
            'licenses' => $licenses,
            'summary' => [
                'total_licenses' => $licenses->count(),
                'active_licenses' => $licenses->where('status', 'active')->count(),
                'expired_licenses' => $licenses->where('licenseExpiresAt', '<', now())->count(),
                'total_revenue' => $licenses->sum(function ($license) {
                    return $license->product ? $license->product->price : 0;
                }),
            ],
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
    }
    /**
     * Export data to CSV format with enhanced security and validation.
     *
     * @param  array<string, mixed>  $data  The data to export
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse The CSV file response
     *
     * @throws \Exception When CSV export fails
     */
    private function exportToCsv(array $data): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'reports_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            if ($file !== false) {
                // CSV headers
                fputcsv($file, ['License Key', 'Product', 'User', 'Status', 'Created At', 'Expires At', 'Price']);
            // CSV data
                if (is_array($data['licenses'])) {
                    foreach ($data['licenses'] as $license) {
                        if (is_object($license) && isset($license->licenseKey)) {
                            $csvData = [
                            is_string($license->licenseKey) ? $license->licenseKey : '',
                            (isset($license->product) && is_object($license->product) && isset($license->product->name)) ? $license->product->name : 'N/A',
                            (isset($license->user) && is_object($license->user) && isset($license->user->name)) ? $license->user->name : 'N/A',
                            (isset($license->status) && is_string($license->status)) ? $license->status : '',
                            (isset($license->createdAt) && is_object($license->createdAt) && method_exists($license->createdAt, 'format')) ? $license->createdAt->format('Y-m-d H:i:s') : 'N/A',
                            (isset($license->licenseExpiresAt) && is_object($license->licenseExpiresAt) && method_exists($license->licenseExpiresAt, 'format')) ? $license->licenseExpiresAt->format('Y-m-d H:i:s') : 'N/A',
                            (isset($license->product) && is_object($license->product) && isset($license->product->price)) ? $license->product->price : '0',
                            ];
                            /**
 * @var array<int|string, bool|float|int|string|null> $typedCsvData
*/
                            $typedCsvData = $csvData;
                            fputcsv($file, $typedCsvData);
                        }
                    }
                }
                SecureFileHelper::closeFile($file);
            }
        };
        return response()->stream($callback, 200, $headers);
    }
    /**
     * Export data to PDF format with enhanced security and validation.
     *
     * @param  array<string, mixed>  $data  The data to export
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse The PDF file response
     *
     * @throws \Exception When PDF export fails
     */
    private function exportToPdf(array $data): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // For now, return CSV as PDF generation requires additional packages
        // You can install dompdf or similar package for proper PDF generation
        $filename = 'reports_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            if ($file !== false) {
                // For now, just output CSV content as PDF placeholder
                // In production, use a proper PDF library like dompdf
                fputcsv($file, ['License Key', 'Product', 'User', 'Status', 'Created At', 'Expires At', 'Price']);
                if (is_array($data['licenses'])) {
                    foreach ($data['licenses'] as $license) {
                        if (is_object($license) && isset($license->licenseKey)) {
                            $csvData = [
                            is_string($license->licenseKey) ? $license->licenseKey : '',
                            (isset($license->product) && is_object($license->product) && isset($license->product->name)) ? $license->product->name : 'N/A',
                            (isset($license->user) && is_object($license->user) && isset($license->user->name)) ? $license->user->name : 'N/A',
                            (isset($license->status) && is_string($license->status)) ? $license->status : '',
                            (isset($license->createdAt) && is_object($license->createdAt) && method_exists($license->createdAt, 'format')) ? $license->createdAt->format('Y-m-d H:i:s') : 'N/A',
                            (isset($license->licenseExpiresAt) && is_object($license->licenseExpiresAt) && method_exists($license->licenseExpiresAt, 'format')) ? $license->licenseExpiresAt->format('Y-m-d H:i:s') : 'N/A',
                            (isset($license->product) && is_object($license->product) && isset($license->product->price)) ? $license->product->price : '0',
                            ];
                            /**
 * @var array<int|string, bool|float|int|string|null> $typedCsvData
*/
                            $typedCsvData = $csvData;
                            fputcsv($file, $typedCsvData);
                        }
                    }
                }
                SecureFileHelper::closeFile($file);
            }
        };
        return response()->stream($callback, 200, $headers);
    }
}
