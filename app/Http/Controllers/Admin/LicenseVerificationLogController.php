<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SecureFileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LicenseVerificationLogRequest;
use App\Models\LicenseVerificationLog;
use App\Services\LicenseVerificationLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Admin License Verification Log Controller with enhanced security.
 *
 * This controller handles license verification log management including viewing,
 * filtering, statistics, and export functionality. It provides comprehensive
 * log analysis and monitoring capabilities.
 *
 * Features:
 * - License verification log viewing and filtering
 * - Statistics and analytics for verification attempts
 * - Suspicious activity detection and monitoring
 * - CSV export functionality for log data
 * - Log cleanup and maintenance operations
 * - Comprehensive error handling with database transactions
 * - Enhanced security measures (XSS protection, input validation)
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
 */
class LicenseVerificationLogController extends Controller
{
    /**
     * Display a listing of license verification logs with filtering and enhanced security.
     *
     * Shows a paginated list of license verification logs with optional filtering
     * by status, source, domain, IP address, and date range. Includes statistics
     * and suspicious activity monitoring.
     *
     * @param  LicenseVerificationLogRequest  $request  The HTTP request containing optional filter parameters
     *
     * @return View The license verification logs index view with filtered data
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request with filters:
     * GET /admin/license-verification-logs?status=success&source=api&date_from=2024-01-01
     *
     * // Returns view with:
     * // - Paginated logs list
     * // - Filter options
     * // - Statistics and analytics
     * // - Suspicious activity alerts
     */
    public function index(LicenseVerificationLogRequest $request): View
    {
        try {
            DB::beginTransaction();
            $query = $this->applyFilters(LicenseVerificationLog::query(), $request);
            $logs = $query->orderBy('created_at', 'desc')->paginate(20);
            // Get statistics
            $stats = LicenseVerificationLogger::getStats(30);
            // Get suspicious activity
            $suspiciousActivity = LicenseVerificationLogger::getSuspiciousActivity(24, 3);
            // Get unique sources and domains for filters
            $sources = LicenseVerificationLog::distinct()->pluck('verification_source')->filter();
            $domains = LicenseVerificationLog::distinct()->pluck('domain')->filter();
            DB::commit();

            return view('admin.license-verification-logs.index', [
                'logs' => $logs,
                'stats' => $stats,
                'suspiciousActivity' => $suspiciousActivity,
                'sources' => $sources,
                'domains' => $domains,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License verification logs listing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty results on error
            return view('admin.license-verification-logs.index', [
                'logs' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'stats' => [],
                'suspiciousActivity' => [],
                'sources' => collect(),
                'domains' => collect(),
            ]);
        }
    }

    /**
     * Get license verification statistics with enhanced security.
     *
     * Retrieves statistics for license verification attempts over a specified
     * number of days with proper validation and error handling.
     *
     * @param  LicenseVerificationLogRequest  $request  The HTTP request containing days parameter
     *
     * @return JsonResponse JSON response with statistics data
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Get statistics for last 30 days:
     * GET /admin/license-verification-logs/stats?days=30
     *
     * // Returns JSON with:
     * // - Total verification attempts
     * // - Success/failure rates
     * // - Daily breakdown
     */
    public function getStats(LicenseVerificationLogRequest $request): JsonResponse
    {
        try {
            $days = $request->validated()['days'] ?? 30;
            $stats = LicenseVerificationLogger::getStats(is_numeric($days) ? (int)$days : 30);

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('License verification stats retrieval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to retrieve statistics',
            ], 500);
        }
    }

    /**
     * Get suspicious activity data with enhanced security.
     *
     * Retrieves suspicious license verification activity based on time period
     * and minimum attempt thresholds with proper validation.
     *
     * @param  LicenseVerificationLogRequest  $request
     *         The HTTP request containing hours and min_attempts parameters
     *
     * @return JsonResponse JSON response with suspicious activity data
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Get suspicious activity for last 24 hours with 3+ attempts:
     * GET /admin/license-verification-logs/suspicious-activity?hours=24&min_attempts=3
     *
     * // Returns JSON with:
     * // - IP addresses with suspicious activity
     * // - Attempt counts and patterns
     * // - Risk assessment data
     */
    public function getSuspiciousActivity(LicenseVerificationLogRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $hours = $validated['hours'] ?? 24;
            $minAttempts = $validated['min_attempts'] ?? 3;
            $activity = LicenseVerificationLogger::getSuspiciousActivity(is_numeric($hours) ? (int)$hours : 24, is_numeric($minAttempts) ? (int)$minAttempts : 3);

            return response()->json($activity);
        } catch (\Exception $e) {
            Log::error('Suspicious activity retrieval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to retrieve suspicious activity data',
            ], 500);
        }
    }

    /**
     * Display the specified license verification log with enhanced security.
     *
     * Shows detailed information about a specific license verification log
     * entry including all relevant data and context.
     *
     * @param  LicenseVerificationLog  $log  The license verification log to display
     *
     * @return View The license verification log details view
     *
     * @throws \Exception When view rendering fails
     *
     * @example
     * // Access log details:
     * GET /admin/license-verification-logs/123
     *
     * // Returns view with:
     * // - Complete log details
     * // - Related data and context
     * // - Action history
     */
    public function show(LicenseVerificationLog $log): View
    {
        try {
            return view('admin.license-verification-logs.show', ['log' => $log]);
        } catch (\Exception $e) {
            Log::error('License verification log view failed to load', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'log_id' => $log->id,
            ]);

            // Return a fallback view or redirect
            return view('admin.license-verification-logs.show', [
                'log' => $log,
                'error' => 'Unable to load the log details. Please try again later.',
            ]);
        }
    }

    /**
     * Clean old license verification logs with enhanced security.
     *
     * Removes license verification logs older than the specified number of days
     * with proper validation and error handling.
     *
     * @param  LicenseVerificationLogRequest  $request  The HTTP request containing days parameter
     *
     * @return JsonResponse JSON response with cleanup results
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Clean logs older than 90 days:
     * POST /admin/license-verification-logs/clean?days=90
     *
     * // Returns JSON with:
     * // - Success status
     * // - Number of cleaned entries
     * // - Confirmation message
     */
    public function cleanOldLogs(LicenseVerificationLogRequest $request): JsonResponse
    {
        // Rate limiting for cleanup functionality
        $key = 'license-logs-cleanup:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'error' => 'Too many cleanup attempts. Please try again later.',
            ], 429);
        }
        RateLimiter::hit($key, 600); // 10 minutes
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $daysToKeep = $validated['days'] ?? 90;
            $cleanedCount = LicenseVerificationLogger::cleanOldLogs(is_numeric($daysToKeep) ? (int)$daysToKeep : 90);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully cleaned {$cleanedCount} old log entries",
                'cleaned_count' => $cleanedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Log cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to clean old logs. Please try again.',
            ], 500);
        }
    }

    /**
     * Export license verification logs to CSV with enhanced security.
     *
     * Exports filtered license verification logs to CSV format with proper
     * validation and error handling.
     *
     * @param  LicenseVerificationLogRequest  $request  The HTTP request containing filter parameters
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse CSV file download
     *
     * @throws \Exception When export operations fail
     *
     * @example
     * // Export logs with filters:
     * GET /admin/license-verification-logs/export?status=success&date_from=2024-01-01
     *
     * // Returns CSV file with:
     * // - Filtered log data
     * // - All relevant fields
     * // - Proper formatting
     */
    public function export(LicenseVerificationLogRequest $request)
    {
        // Rate limiting for export functionality
        $key = 'license-logs-export:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return new \Symfony\Component\HttpFoundation\StreamedResponse(function() {
                echo 'Too many export attempts. Please try again later.';
            }, 429);
        }
        RateLimiter::hit($key, 300); // 5 minutes
        try {
            DB::beginTransaction();
            $query = $this->applyFilters(LicenseVerificationLog::query(), $request);
            $logs = $query->orderBy('created_at', 'desc')->get();
            $filename = 'license_verification_logs_'.date('Y-m-d_H-i-s').'.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ];
            $callback = function () use ($logs) {
                $file = SecureFileHelper::openOutput('w');
                if (!is_resource($file)) {
                    return;
                }
                // CSV Headers
                fputcsv($file, [
                    'ID', 'Purchase Code Hash', 'Domain', 'IP Address', 'Status',
                    'Source', 'Is Valid', 'Response Message', 'Created At',
                ]);
                // CSV Data
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->purchase_code_hash,
                        $this->sanitizeOutput($log->domain),
                        $log->ip_address,
                        $log->status,
                        $log->verification_source,
                        $log->is_valid ? 'Yes' : 'No',
                        $this->sanitizeOutput($log->response_message),
                        $log->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
                SecureFileHelper::closeFile($file);
            };
            DB::commit();

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License verification logs export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new \Symfony\Component\HttpFoundation\StreamedResponse(function() {
                echo 'Failed to export logs. Please try again.';
            }, 500);
        }
    }

    /**
     * Apply filters to license verification log query with enhanced security validation.
     *
     * This method consolidates all filtering logic to eliminate code duplication
     * and ensures consistent security validation across all methods.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query  The query builder instance
     * @param  LicenseVerificationLogRequest  $request  The HTTP request containing filter parameters
     *
     * @return \Illuminate\Database\Eloquent\Builder The filtered query builder
     *
     * @throws \Exception When filter validation fails
     *
     * @example
     * // Apply filters to query:
     * $query = $this->applyFilters(LicenseVerificationLog::query(), $request);
     *
     * // Returns filtered query with:
     * // - Status filtering (success, failed, error)
     * // - Source filtering (api, web, cron, manual)
     * // - Domain filtering with regex validation
     * // - IP address filtering with validation
     * // - Date range filtering with format validation
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<LicenseVerificationLog> $query
     *
     * @return \Illuminate\Database\Eloquent\Builder<LicenseVerificationLog>
     */
    private function applyFilters($query, LicenseVerificationLogRequest $request)
    {
        $validated = $request->validated();
        // Apply status filter
        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }
        // Apply source filter
        if (! empty($validated['source'])) {
            $query->where('verification_source', $validated['source']);
        }
        // Apply domain filter
        if (! empty($validated['domain'])) {
            $query->where('domain', 'like', '%'.(is_string($validated['domain']) ? $validated['domain'] : '').'%');
        }
        // Apply IP address filter
        if (! empty($validated['ip'])) {
            $query->where('ip_address', 'like', '%'.(is_string($validated['ip']) ? $validated['ip'] : '').'%');
        }
        // Apply date from filter
        if (! empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', is_string($validated['date_from']) ? $validated['date_from'] : null);
        }
        // Apply date to filter
        if (! empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', is_string($validated['date_to']) ? $validated['date_to'] : null);
        }

        return $query;
    }

    /**
     * Sanitize output to prevent XSS attacks.
     *
     * @param  string|null  $output  The output to sanitize
     *
     * @return string The sanitized output
     */
    private function sanitizeOutput(?string $output): string
    {
        if ($output === null) {
            return '';
        }

        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
}
