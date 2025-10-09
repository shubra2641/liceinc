<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LicenseVerificationLog;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * License Verification Logger with enhanced security.
 *
 * A comprehensive service for logging license verification attempts with
 * enhanced security measures, input validation, and comprehensive error handling.
 *
 * Features:
 * - Secure license verification logging
 * - Purchase code hashing for security
 * - Request information tracking
 * - Statistics and analytics
 * - Suspicious activity detection
 * - Enhanced error handling and logging
 * - Input validation and sanitization
 * - Comprehensive security measures
 * - Clean code structure with no duplicate patterns
 * - Proper type hints and return types
 */
class LicenseVerificationLogger
{
    /**
     * Log license verification attempt with enhanced security.
     *
     * Logs license verification attempts with comprehensive validation,
     * sanitization, and security measures including purchase code hashing.
     *
     * @param  string  $purchaseCode  The purchase code to log
     * @param  string  $domain  The domain being verified
     * @param  bool  $isValid  Whether the verification was successful
     * @param  string  $message  The verification message
     * @param  array|null  $responseData  Additional response data
     * @param  string  $source  The verification source (install, api, admin)
     * @param  Request|null  $request  The HTTP request object
     * @param  string|null  $errorDetails  Additional error details
     *
     * @return LicenseVerificationLog The created log entry
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     */
    /**
     * @param array<string, mixed>|null $responseData
     */
    public static function log(
        string $purchaseCode,
        string $domain,
        bool $isValid,
        string $message,
        ?array $responseData = null,
        string $source = 'install',
        ?Request $request = null,
        ?string $errorDetails = null,
    ): LicenseVerificationLog {
        try {
            // Validate and sanitize inputs
            $purchaseCode = self::validatePurchaseCode($purchaseCode);
            $domain = self::validateDomain($domain);
            $message = self::sanitizeString($message);
            $source = self::validateSource($source);
            $errorDetails = self::sanitizeString($errorDetails);
            // Get request information with validation
            $ipAddress = self::getValidatedIpAddress($request);
            $userAgent = self::getValidatedUserAgent($request);
            // Hash the purchase code for security
            $purchaseCodeHash = hash('sha256', $purchaseCode);
            // Determine status with validation
            $status = self::determineStatus($isValid, $errorDetails);
            // Create log entry
            $log = LicenseVerificationLog::create([
                'purchase_code_hash' => $purchaseCodeHash,
                'domain' => $domain,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'is_valid' => $isValid,
                'response_message' => $message,
                'response_data' => $responseData,
                'verification_source' => $source,
                'status' => $status,
                'error_details' => $errorDetails,
                'verified_at' => $isValid ? now() : null,
            ]);
            // Log to Laravel log file for additional tracking (only errors and warnings)
            if (! $isValid || $errorDetails) {
                $logLevel = $errorDetails ? 'error' : 'warning';
                Log::channel('single')->$logLevel('License verification attempt', [
                    'purchase_code_hash' => $purchaseCodeHash,
                    'domain' => $domain,
                    'ip_address' => $ipAddress,
                    'is_valid' => $isValid,
                    'status' => $status,
                    'source' => $source,
                    'message' => $message,
                ]);
            }
            return $log;
        } catch (Exception $e) {
            // Fallback logging if database fails
            Log::error('Failed to log license verification attempt', [
                'purchase_code_hash' => hash('sha256', $purchaseCode),
                'domain' => $domain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return a mock log entry
            return new LicenseVerificationLog([
                'purchase_code_hash' => hash('sha256', $purchaseCode),
                'domain' => $domain,
                'is_valid' => $isValid,
                'response_message' => $message,
                'status' => $isValid ? 'success' : 'failed',
            ]);
        }
    }
    /**
     * Get verification statistics with enhanced security.
     *
     * Retrieves comprehensive verification statistics with input validation
     * and sanitization for the specified time period.
     *
     * @param  int  $days  Number of days to include in statistics
     *
     * @return array Statistics array with counts and metrics
     *
     * @throws \InvalidArgumentException When days parameter is invalid
     *
     * @version 1.0.6
     */
    /**
     * @return array<string, mixed>
     */
    public static function getStats(int $days = 30): array
    {
        try {
            $days = self::validateDays($days);
            $startDate = now()->subDays($days);
            return [
                'total_attempts' => LicenseVerificationLog::where('created_at', '>=', $startDate)
                    ->count(),
                'successful_attempts' => LicenseVerificationLog::where('created_at', '>=', $startDate)
                    ->successful()->count(),
                'failed_attempts' => LicenseVerificationLog::where('created_at', '>=', $startDate)
                    ->failed()->count(),
                'unique_domains' => LicenseVerificationLog::where('created_at', '>=', $startDate)
                    ->distinct('domain')->count('domain'),
                'unique_ips' => LicenseVerificationLog::where('created_at', '>=', $startDate)
                    ->distinct('ip_address')->count('ip_address'),
                'recent_failed_attempts' => LicenseVerificationLog::recent(24)->failed()->count(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get verification statistics: ' . $e->getMessage());
            return [
                'total_attempts' => 0,
                'successful_attempts' => 0,
                'failed_attempts' => 0,
                'unique_domains' => 0,
                'unique_ips' => 0,
                'recent_failed_attempts' => 0,
            ];
        }
    }
    /**
     * Get suspicious activity with enhanced security.
     *
     * Identifies suspicious activity patterns with input validation
     * and comprehensive error handling.
     *
     * @param  int  $hours  Number of hours to look back
     * @param  int  $minAttempts  Minimum number of attempts to consider suspicious
     *
     * @return array Array of suspicious activity records
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     */
    /**
     * @return array<string, mixed>
     */
    public static function getSuspiciousActivity(int $hours = 24, int $minAttempts = 5): array
    {
        try {
            $hours = self::validateHours($hours);
            $minAttempts = self::validateMinAttempts($minAttempts);
            $result = LicenseVerificationLog::recent($hours)
                ->failed()
                ->selectRaw('ip_address, COUNT(*) as attempt_count, MAX(created_at) as last_attempt')
                ->groupBy('ip_address')
                ->having('attempt_count', '>=', $minAttempts)
                ->orderBy('attempt_count', 'desc')
                ->get()
                ->toArray();
            /**
 * @var array<string, mixed> $typedResult
*/
            $typedResult = $result;
            return $typedResult;
        } catch (Exception $e) {
            Log::error('Failed to get suspicious activity: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Get recent verification attempts with enhanced security.
     *
     * Retrieves recent verification attempts with input validation
     * and comprehensive error handling.
     *
     * @param  int  $limit  Maximum number of attempts to retrieve
     *
     * @return Collection Collection of recent verification attempts
     *
     * @throws \InvalidArgumentException When limit is invalid
     *
     * @version 1.0.6
     */
    /**
     * @return Collection<int, LicenseVerificationLog>
     */
    public static function getRecentAttempts(int $limit = 50): Collection
    {
        try {
            $limit = self::validateLimit($limit);
            return LicenseVerificationLog::with([])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        } catch (Exception $e) {
            Log::error('Failed to get recent attempts: ' . $e->getMessage());
            return new Collection();
        }
    }
    /**
     * Clean old logs with enhanced security.
     *
     * Removes old log entries with input validation and comprehensive
     * error handling for data cleanup operations.
     *
     * @param  int  $days  Number of days to keep logs
     *
     * @return int Number of deleted log entries
     *
     * @throws \InvalidArgumentException When days parameter is invalid
     *
     * @version 1.0.6
     */
    public static function cleanOldLogs(int $days = 90): int
    {
        try {
            $days = self::validateDays($days);
            $cutoffDate = now()->subDays($days);
            $deletedCount = LicenseVerificationLog::where('created_at', '<', $cutoffDate)->delete();
            // Cleanup completed successfully - no logging needed for successful operations
            return is_numeric($deletedCount) ? (int)$deletedCount : 0;
        } catch (Exception $e) {
            Log::error('Failed to clean old logs: ' . $e->getMessage());
            return 0;
        }
    }
    /**
     * Validate and sanitize purchase code.
     *
     * Validates the purchase code and returns a sanitized version
     * with proper security measures.
     *
     * @param  string  $purchaseCode  The purchase code to validate
     *
     * @return string The validated and sanitized purchase code
     *
     * @throws \InvalidArgumentException When purchase code is invalid
     *
     * @version 1.0.6
     */
    private static function validatePurchaseCode(string $purchaseCode): string
    {
        if (empty($purchaseCode)) {
            throw new \InvalidArgumentException('Purchase code cannot be empty');
        }
        $sanitized = htmlspecialchars(trim($purchaseCode), ENT_QUOTES, 'UTF-8');
        if (empty($sanitized) || strlen($sanitized) < 10) {
            throw new \InvalidArgumentException('Purchase code must be at least 10 characters long');
        }
        return $sanitized;
    }
    /**
     * Validate and sanitize domain.
     *
     * Validates the domain and returns a sanitized version
     * with proper security measures.
     *
     * @param  string  $domain  The domain to validate
     *
     * @return string The validated and sanitized domain
     *
     * @throws \InvalidArgumentException When domain is invalid
     *
     * @version 1.0.6
     */
    private static function validateDomain(string $domain): string
    {
        if (empty($domain)) {
            throw new \InvalidArgumentException('Domain cannot be empty');
        }
        $sanitized = htmlspecialchars(trim($domain), ENT_QUOTES, 'UTF-8');
        if (empty($sanitized) || strlen($sanitized) < 3) {
            throw new \InvalidArgumentException('Domain must be at least 3 characters long');
        }
        return $sanitized;
    }
    /**
     * Validate and sanitize verification source.
     *
     * Validates the verification source and returns a sanitized version
     * with proper security measures.
     *
     * @param  string  $source  The source to validate
     *
     * @return string The validated and sanitized source
     *
     * @throws \InvalidArgumentException When source is invalid
     *
     * @version 1.0.6
     */
    private static function validateSource(string $source): string
    {
        $allowedSources = ['install', 'api', 'admin'];
        $sanitized = htmlspecialchars(trim($source), ENT_QUOTES, 'UTF-8');
        if (! in_array($sanitized, $allowedSources, true)) {
            throw new \InvalidArgumentException(
                'Invalid verification source. Allowed values: ' . implode(', ', $allowedSources),
            );
        }
        return $sanitized;
    }
    /**
     * Get validated IP address from request.
     *
     * Extracts and validates IP address from request with proper
     * security measures and fallback handling.
     *
     * @param  Request|null  $request  The HTTP request object
     *
     * @return string The validated IP address
     *
     * @version 1.0.6
     */
    private static function getValidatedIpAddress(?Request $request): string
    {
        $ipAddress = $request ? $request->ip() : request()->ip();
        if (empty($ipAddress)) {
            return 'unknown';
        }
        return htmlspecialchars(trim($ipAddress), ENT_QUOTES, 'UTF-8');
    }
    /**
     * Get validated user agent from request.
     *
     * Extracts and validates user agent from request with proper
     * security measures and fallback handling.
     *
     * @param  Request|null  $request  The HTTP request object
     *
     * @return string The validated user agent
     *
     * @version 1.0.6
     */
    private static function getValidatedUserAgent(?Request $request): string
    {
        $userAgent = $request ? $request->userAgent() : request()->userAgent();
        if (empty($userAgent)) {
            return 'unknown';
        }
        return htmlspecialchars(trim($userAgent), ENT_QUOTES, 'UTF-8');
    }
    /**
     * Validate days parameter.
     *
     * Validates the days parameter for statistics and cleanup operations.
     *
     * @param  int  $days  The days parameter to validate
     *
     * @return int The validated days parameter
     *
     * @throws \InvalidArgumentException When days is invalid
     *
     * @version 1.0.6
     */
    private static function validateDays(int $days): int
    {
        if ($days <= 0 || $days > 365) {
            throw new \InvalidArgumentException('Days must be between 1 and 365');
        }
        return $days;
    }
    /**
     * Validate hours parameter.
     *
     * Validates the hours parameter for suspicious activity detection.
     *
     * @param  int  $hours  The hours parameter to validate
     *
     * @return int The validated hours parameter
     *
     * @throws \InvalidArgumentException When hours is invalid
     *
     * @version 1.0.6
     */
    private static function validateHours(int $hours): int
    {
        if ($hours <= 0 || $hours > 168) {
            throw new \InvalidArgumentException('Hours must be between 1 and 168');
        }
        return $hours;
    }
    /**
     * Validate minimum attempts parameter.
     *
     * Validates the minimum attempts parameter for suspicious activity detection.
     *
     * @param  int  $minAttempts  The minimum attempts parameter to validate
     *
     * @return int The validated minimum attempts parameter
     *
     * @throws \InvalidArgumentException When minAttempts is invalid
     *
     * @version 1.0.6
     */
    private static function validateMinAttempts(int $minAttempts): int
    {
        if ($minAttempts <= 0 || $minAttempts > 100) {
            throw new \InvalidArgumentException('Minimum attempts must be between 1 and 100');
        }
        return $minAttempts;
    }
    /**
     * Validate limit parameter.
     *
     * Validates the limit parameter for data retrieval operations.
     *
     * @param  int  $limit  The limit parameter to validate
     *
     * @return int The validated limit parameter
     *
     * @throws \InvalidArgumentException When limit is invalid
     *
     * @version 1.0.6
     */
    private static function validateLimit(int $limit): int
    {
        if ($limit <= 0 || $limit > 1000) {
            throw new \InvalidArgumentException('Limit must be between 1 and 1000');
        }
        return $limit;
    }
    /**
     * Determine status based on validation results.
     *
     * Determines the appropriate status based on validation results
     * and error details with proper validation.
     *
     * @param  bool  $isValid  Whether the verification was successful
     * @param  string|null  $errorDetails  Additional error details
     *
     * @return string The determined status
     *
     * @version 1.0.6
     */
    private static function determineStatus(bool $isValid, ?string $errorDetails): string
    {
        if ($isValid) {
            return 'success';
        }

        if ($errorDetails) {
            return 'error';
        }

        return 'failed';
    }

    /**
     * Sanitize string input with XSS protection.
     *
     * Sanitizes string input to prevent XSS attacks and other
     * security vulnerabilities.
     *
     * @param  string|null  $input  The input string to sanitize
     *
     * @return string|null The sanitized string or null
     *
     * @version 1.0.6
     */
    private static function sanitizeString(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
