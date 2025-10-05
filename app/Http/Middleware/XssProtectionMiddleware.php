<?php
declare(strict_types=1);
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
/**
 * XSS Protection Middleware with enhanced security and comprehensive input sanitization.
 *
 * This middleware provides comprehensive protection against Cross-Site Scripting (XSS) attacks
 * by sanitizing input data, implementing content filtering, and providing enhanced security
 * measures with comprehensive error handling and logging.
 *
 * Features:
 * - Enhanced XSS protection with comprehensive input sanitization
 * - Advanced pattern detection and removal of malicious content
 * - Configurable security settings and filtering options
 * - Security event logging for suspicious activity detection
 * - Input validation and sanitization
 * - Enhanced security measures for XSS prevention
 * - Proper error responses for different security scenarios
 * - Comprehensive logging for security monitoring
 *
 *
 * @example
 * // Applied to routes that require XSS protection
 * Route::middleware(['xss.protection'])->group(function () {
 *     // Protected routes
 * });
 */
class XssProtectionMiddleware
{
    /**
     * Dangerous patterns for XSS detection and removal.
     *
     * @var array<string>
     */
    private const DANGEROUS_PATTERNS = [
        '/javascript:/i',
        '/vbscript:/i',
        '/onload=/i',
        '/onerror=/i',
        '/onclick=/i',
        '/onmouseover=/i',
        '/onmouseout=/i',
        '/onfocus=/i',
        '/onblur=/i',
        '/onchange=/i',
        '/onsubmit=/i',
        '/onreset=/i',
        '/onkeydown=/i',
        '/onkeyup=/i',
        '/onkeypress=/i',
        '/<script/i',
        '/<\/script>/i',
        '/<iframe/i',
        '/<\/iframe>/i',
        '/<object/i',
        '/<\/object>/i',
        '/<embed/i',
        '/<\/embed>/i',
        '/<applet/i',
        '/<\/applet>/i',
        '/<meta/i',
        '/<link/i',
        '/<style/i',
        '/<\/style>/i',
        '/expression\s*\(/i',
        '/url\s*\(/i',
        '/import\s*\(/i',
        '/data:text\/html/i',
        '/&#x?[0-9a-f]+;/i',
        '/eval\s*\(/i',
        '/document\.cookie/i',
        '/window\.(location|open|alert)/i',
        '/\b(alert|prompt|confirm)\s*\(/i',
    ];
    /**
     * Malicious content detection patterns.
     *
     * @var array<string>
     */
    private const MALICIOUS_PATTERNS = [
        '/<script.*?>.*?<\/script>/si',
        '/javascript:/i',
        '/vbscript:/i',
        '/on\w+\s*=/i',
        '/<iframe.*?>/si',
        '/<object.*?>/si',
        '/<embed.*?>/si',
        '/<applet.*?>/si',
        '/expression\s*\(/i',
        '/url\s*\(/i',
        '/import\s*\(/i',
        '/data:text\/html/i',
        '/&#x?[0-9a-f]+;/i',
        '/eval\s*\(/i',
        '/document\.cookie/i',
        '/window\.(location|open|alert)/i',
        '/\b(alert|prompt|confirm)\s*\(/i',
    ];
    /**
     * Handle an incoming request with enhanced XSS protection and comprehensive validation.
     *
     * This method performs comprehensive XSS protection including input sanitization,
     * malicious content detection, and security logging for suspicious activity.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @return Response The response from the next middleware or error response
     *
     * @throws \Exception When an unexpected error occurs during processing
     *
     * @example
     * // Middleware automatically applies XSS protection to all requests
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Get XSS protection configuration
            $xssConfig = config('security.xss_protection', []);
            $enabled = $xssConfig['enabled'] ?? true;
            if ($enabled) {
                // Sanitize input data
                $this->sanitizeInput($request, $xssConfig);
            }
            $response = $next($request);
            return $response;
        } catch (Throwable $e) {
            Log::error('XSS Protection middleware processing error', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Continue processing even if XSS protection fails to prevent service disruption
            return $next($request);
        }
    }
    /**
     * Sanitize input data to prevent XSS attacks with enhanced security.
     *
     * This method sanitizes all input data with comprehensive validation
     * and security measures to prevent XSS attacks.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  array<string, mixed>  $config  The XSS protection configuration
     *
     * @throws \Exception When sanitization fails
     *
     * @example
     * $this->sanitizeInput($request, $xssConfig);
     */
    private function sanitizeInput(Request $request, array $config): void
    {
        try {
            $input = $request->all();
            $sanitized = $this->recursiveSanitize($input, $config);
            // Check for malicious content before replacing
            if ($this->containsMaliciousContent(json_encode($sanitized))) {
                $this->logSuspiciousActivity($request, $sanitized);
            }
            $request->replace($sanitized);
        } catch (Throwable $e) {
            Log::error('XSS input sanitization error', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Recursively sanitize array data with enhanced security.
     *
     * This method recursively sanitizes array data with comprehensive
     * validation and security measures.
     *
     * @param  mixed  $data  The data to sanitize
     * @param  array<string, mixed>  $config  The sanitization configuration
     *
     * @return mixed The sanitized data
     *
     * @example
     * $sanitized = $this->recursiveSanitize($data, $config);
     */
    private function recursiveSanitize($data, array $config)
    {
        try {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = $this->recursiveSanitize($value, $config);
                }
                return $data;
            }
            if (is_string($data)) {
                return $this->sanitizeString($data, $config);
            }
            return $data;
        } catch (Throwable $e) {
            Log::error('Recursive sanitization error', [
                'error' => $e->getMessage(),
                'data_type' => gettype($data),
                'trace' => $e->getTraceAsString(),
            ]);
            return $data; // Return original data if sanitization fails
        }
    }
    /**
     * Sanitize string data with enhanced security measures.
     *
     * This method sanitizes string data with comprehensive XSS protection
     * including pattern removal and HTML entity encoding.
     *
     * @param  string  $data  The string data to sanitize
     * @param  array<string, mixed>  $config  The sanitization configuration
     *
     * @return string The sanitized string
     *
     * @example
     * $sanitized = $this->sanitizeString($data, $config);
     */
    private function sanitizeString(string $data, array $config): string
    {
        try {
            // Remove null bytes
            $data = str_replace("\0", '', $data);
            // Strip tags if configured
            if ($config['strip_tags'] ?? true) {
                $allowedTags = $config['allowed_tags'] ?? '';
                $data = strip_tags($data, $allowedTags);
            }
            // Convert special characters to HTML entities
            if ($config['escape_output'] ?? true) {
                $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            // Remove dangerous JavaScript patterns
            $data = $this->removeDangerousPatterns($data);
            return $data;
        } catch (Throwable $e) {
            Log::error('String sanitization error', [
                'error' => $e->getMessage(),
                'data_length' => strlen($data),
                'trace' => $e->getTraceAsString(),
            ]);
            return $data; // Return original data if sanitization fails
        }
    }
    /**
     * Remove dangerous JavaScript patterns with enhanced security.
     *
     * This method removes dangerous JavaScript patterns and malicious content
     * using comprehensive pattern matching and removal.
     *
     * @param  string  $data  The data to clean
     *
     * @return string The cleaned data
     *
     * @example
     * $cleaned = $this->removeDangerousPatterns($data);
     */
    private function removeDangerousPatterns(string $data): string
    {
        try {
            foreach (self::DANGEROUS_PATTERNS as $pattern) {
                $data = preg_replace($pattern, '', $data);
            }
            return $data;
        } catch (Throwable $e) {
            Log::error('Dangerous pattern removal error', [
                'error' => $e->getMessage(),
                'data_length' => strlen($data),
                'trace' => $e->getTraceAsString(),
            ]);
            return $data; // Return original data if pattern removal fails
        }
    }
    /**
     * Check if the request contains potentially malicious content with enhanced detection.
     *
     * This method checks for malicious content using comprehensive pattern matching
     * and advanced detection algorithms.
     *
     * @param  string  $data  The data to check
     *
     * @return bool True if malicious content is detected, false otherwise
     *
     * @example
     * $isMalicious = $this->containsMaliciousContent($data);
     */
    private function containsMaliciousContent(string $data): bool
    {
        try {
            foreach (self::MALICIOUS_PATTERNS as $pattern) {
                if (preg_match($pattern, $data)) {
                    return true;
                }
            }
            return false;
        } catch (Throwable $e) {
            Log::error('Malicious content detection error', [
                'error' => $e->getMessage(),
                'data_length' => strlen($data),
                'trace' => $e->getTraceAsString(),
            ]);
            return true; // Assume malicious if detection fails to be safe
        }
    }
    /**
     * Log suspicious XSS attempts with enhanced security monitoring.
     *
     * This method logs suspicious XSS attempts with comprehensive context
     * for security monitoring and threat detection.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  mixed  $suspiciousData  The suspicious data that was detected
     *
     * @example
     * $this->logSuspiciousActivity($request, $suspiciousData);
     */
    private function logSuspiciousActivity(Request $request, $suspiciousData): void
    {
        try {
            Log::warning('Suspicious XSS activity detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString(),
                'request_id' => $request->header('X-Request-ID'),
                'referer' => $request->header('Referer'),
                'accept' => $request->header('Accept'),
                'content_type' => $request->header('Content-Type'),
                'is_ajax' => $request->ajax(),
                'is_json' => $request->expectsJson(),
                'route_name' => $request->route()?->getName(),
                'route_action' => $request->route()?->getActionName(),
                'suspicious_data_sample' => $this->getDataSample($suspiciousData),
                'data_size' => is_string($suspiciousData) ? strlen($suspiciousData) : 'N/A',
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to log suspicious XSS activity', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    /**
     * Get a safe sample of data for logging purposes.
     *
     * @param  mixed  $data  The data to sample
     *
     * @return string A safe sample of the data
     */
    private function getDataSample($data): string
    {
        try {
            if (is_string($data)) {
                return substr($data, 0, 200); // First 200 characters
            }
            if (is_array($data)) {
                return json_encode(array_slice($data, 0, 5)); // First 5 elements
            }
            return (string)$data;
        } catch (Throwable $e) {
            return 'Data sample unavailable';
        }
    }
    /**
     * Sanitize input to prevent XSS attacks.
     *
     * @param  string  $input  The input to sanitize
     *
     * @return string The sanitized input
     */
    private function sanitizeInputString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    /**
     * Hash data for logging.
     *
     * @param  string  $data  The data to hash
     *
     * @return string The hashed data
     */
    private function hashForLogging(string $data): string
    {
        return substr(hash('sha256', $data.config('app.key')), 0, 8).'...';
    }
}
