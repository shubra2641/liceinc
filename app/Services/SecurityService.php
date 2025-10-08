<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Security Service with enhanced security.
 *
 * A comprehensive security service that provides centralized security functionality
 * including input validation, threat detection, security monitoring, and comprehensive
 * error handling with enhanced security measures.
 *
 * Features:
 * - Advanced input validation and sanitization
 * - XSS protection and HTML sanitization
 * - Threat detection and suspicious activity monitoring
 * - Rate limiting and IP blacklisting
 * - File upload security validation
 * - Attack pattern detection
 * - Security event logging
 * - Enhanced error handling and validation
 * - Comprehensive security measures
 * - Clean code structure with no duplicate patterns
 * - Proper type hints and return types
 */
class SecurityService
{
    /**
     * Validate and sanitize input data with enhanced security.
     *
     * Validates and sanitizes input data with comprehensive security measures
     * including XSS protection, length validation, and custom rule application.
     *
     * @param  array  $data  The input data to validate and sanitize
     * @param  array  $rules  Custom validation rules to apply
     *
     * @return array The validated and sanitized data
     *
     * @throws \InvalidArgumentException When input data is invalid
     *
     * @version 1.0.6
     */
    /**
     * @param array<mixed> $data
     * @param array<mixed> $rules
     * @return array<mixed>
     */
    public function validateAndSanitizeInput(array $data, array $rules = []): array
    {
        try {
            if (empty($data)) {
                throw new \InvalidArgumentException('Input data cannot be empty');
            }
            $sanitized = [];
            $maxStringLength = config('security.validation.max_string_length', 10000);
            $sanitizeHtml = config('security.validation.sanitize_html', true);
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    // Limit string length
                    if (strlen($value) > $maxStringLength) {
                        $maxLength = is_numeric($maxStringLength) ? (int)$maxStringLength : 1000;
                        $value = substr($value, 0, $maxLength);
                    }
                    // Sanitize HTML if enabled
                    if ($sanitizeHtml) {
                        $value = $this->sanitizeHtml($value);
                    }
                    // Apply specific validation rules if provided
                    if (isset($rules[$key])) {
                        $rule = is_string($rules[$key] ?? '') ? (string)($rules[$key] ?? '') : '';
                        $value = $this->applyValidationRule($value, $rule);
                    }
                } elseif (is_array($value)) {
                    $subRules = is_array($rules[$key] ?? []) ? (array)($rules[$key] ?? []) : [];
                    $value = $this->validateAndSanitizeInput($value, $subRules);
                }
                $sanitized[$key] = $value;
            }
            return $sanitized;
        } catch (Exception $e) {
            Log::error('Failed to validate and sanitize input: ' . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Sanitize HTML content with enhanced security.
     *
     * Sanitizes HTML content by removing dangerous patterns, converting
     * special characters, and applying comprehensive XSS protection.
     *
     * @param  string  $content  The HTML content to sanitize
     *
     * @return string The sanitized HTML content
     *
     * @throws \InvalidArgumentException When content is invalid
     *
     * @version 1.0.6
     */
    public function sanitizeHtml(string $content): string
    {
        try {
            if (empty($content)) {
                return '';
            }
            // Remove null bytes
            $content = str_replace("\0", '', $content);
            // Get allowed tags from configuration
            $allowedTags = config('security.xss_protection.allowed_tags', '');
            // Strip dangerous tags
            $allowedTagsString = is_string($allowedTags) ? $allowedTags : '';
            $content = strip_tags($content, $allowedTagsString);
            // Convert special characters to HTML entities
            $content = htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Remove dangerous JavaScript patterns
            $content = $this->removeDangerousPatterns($content);
            return $content;
        } catch (Exception $e) {
            Log::error('Failed to sanitize HTML content: ' . $e->getMessage());
            return '';
        }
    }
    /**
     * Remove dangerous patterns from content with enhanced security.
     *
     * Removes dangerous JavaScript patterns, malicious scripts, and
     * other security threats from content.
     *
     * @param  string  $content  The content to clean
     *
     * @return string The cleaned content
     *
     * @version 1.0.6
     */
    private function removeDangerousPatterns(string $content): string
    {
        try {
            $dangerousPatterns = [
                '/javascript:/i',
                '/vbscript:/i',
                '/data:text\/html/i',
                '/on\w+\s*=/i',
                '/<script.*?<\/script>/si',
                '/<iframe.*?<\/iframe>/si',
                '/<object.*?<\/object>/si',
                '/<embed.*?<\/embed>/si',
                '/<applet.*?<\/applet>/si',
                '/expression\s*\(/i',
                '/url\s*\(/i',
                '/import\s*\(/i',
                '/@import/i',
                '/binding\s*:/i',
                '/behaviour\s*:/i',
                '/-moz-binding/i',
            ];
            foreach ($dangerousPatterns as $pattern) {
                try {
                    $content = preg_replace($pattern, '', (string)$content);
                } catch (Exception $e) {
                    Log::error('Failed to apply dangerous pattern filter: ' . $e->getMessage());
                    continue;
                }
            }
            return (string)$content;
        } catch (Exception $e) {
            Log::error('Failed to remove dangerous patterns: ' . $e->getMessage());
            return (string)$content;
        }
    }
    /**
     * Apply specific validation rule with enhanced security.
     *
     * Applies specific validation rules to values with comprehensive
     * error handling and security measures.
     *
     * @param  mixed  $value  The value to validate
     * @param  string  $rule  The validation rule to apply
     *
     * @return mixed The validated value
     *
     * @throws \InvalidArgumentException When rule is invalid
     *
     * @version 1.0.6
     */
    private function applyValidationRule($value, string $rule)
    {
        try {
            $allowedRules = ['email', 'url', 'int', 'float', 'string'];
            if (! in_array($rule, $allowedRules, true)) {
                throw new \InvalidArgumentException('Invalid validation rule: ' . $rule);
            }
            switch ($rule) {
                case 'email':
                    return filter_var($value, FILTER_SANITIZE_EMAIL);
                case 'url':
                    return filter_var($value, FILTER_SANITIZE_URL);
                case 'int':
                    return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                case 'float':
                    return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                case 'string':
                    return filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                default:
                    return $value;
            }
        } catch (Exception $e) {
            Log::error('Failed to apply validation rule: ' . $e->getMessage());
            return $value;
        }
    }
    /**
     * Check if request is from a suspicious source with enhanced security.
     *
     * Analyzes the request for suspicious indicators including rate limiting,
     * user agent analysis, header inspection, and attack pattern detection.
     *
     * @param  Request  $request  The HTTP request to analyze
     *
     * @return bool True if request is suspicious, false otherwise
     *
     * @throws \InvalidArgumentException When request is invalid
     *
     * @version 1.0.6
     */
    public function isSuspiciousRequest(Request $request): bool
    {
        try {
            $suspiciousIndicators = [
                $this->hasHighRequestRate($request),
                $this->hasSuspiciousUserAgent($request),
                $this->hasSuspiciousHeaders($request),
                $this->isFromBlacklistedIP($request),
                $this->hasKnownAttackPatterns($request),
            ];
            $isSuspicious = in_array(true, $suspiciousIndicators);
            if ($isSuspicious) {
                $this->logSecurityEvent('suspicious_request_detected', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                    'indicators' => $suspiciousIndicators,
                ]);
            }
            return $isSuspicious;
        } catch (Exception $e) {
            Log::error('Failed to check suspicious request: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Check if request has high rate.
     */
    private function hasHighRequestRate(Request $request): bool
    {
        $key = 'rate_limit:' . $request->ip();
        $maxRequests = config('security.rate_limiting.api_requests_per_minute', 60);
        $maxRequestsInt = is_numeric($maxRequests) ? (int)$maxRequests : 60;
        return RateLimiter::tooManyAttempts($key, $maxRequestsInt);
    }
    /**
     * Check if user agent is suspicious.
     */
    private function hasSuspiciousUserAgent(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');
        $suspiciousAgents = [
            'bot', 'crawler', 'spider', 'scraper', 'scanner',
            'nikto', 'sqlmap', 'nmap', 'burp', 'zap',
            'python-requests', 'curl', 'wget', 'httpclient',
        ];
        foreach ($suspiciousAgents as $agent) {
            if (strpos($userAgent, $agent) !== false) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if request has suspicious headers.
     */
    private function hasSuspiciousHeaders(Request $request): bool
    {
        $suspiciousHeaders = [
            'X-Forwarded-For' => ['127.0.0.1', 'localhost'],
            'X-Real-IP' => ['127.0.0.1', 'localhost'],
            'X-Originating-IP' => ['127.0.0.1', 'localhost'],
        ];
        foreach ($suspiciousHeaders as $header => $suspiciousValues) {
            $headerValue = $request->header($header);
            if ($headerValue && in_array((string) $headerValue, $suspiciousValues)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if IP is blacklisted.
     */
    private function isFromBlacklistedIP(Request $request): bool
    {
        $ip = $request->ip();
        $blacklistConfig = config('security.ip_control.blacklist', '');
        $blacklistString = is_string($blacklistConfig) ? $blacklistConfig : '';
        $blacklist = explode(', ', $blacklistString);
        return in_array($ip, array_filter($blacklist));
    }
    /**
     * Check if request contains known attack patterns.
     */
    private function hasKnownAttackPatterns(Request $request): bool
    {
        $attackPatterns = [
            // SQL Injection patterns
            '/union\s+select/i',
            '/select\s+.*\s+from/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
            '/drop\s+table/i',
            '/update\s+.*\s+set/i',
            '/or\s+1\s*=\s*1/i',
            '/and\s+1\s*=\s*1/i',
            '/\'\s*or\s*\'/i',
            '/\"\s*or\s*\"/i',
            // XSS patterns
            '/<script.*?>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe.*?>/i',
            // Command injection patterns
            '/;\s*cat\s+/i',
            '/;\s*ls\s+/i',
            '/;\s*pwd/i',
            '/;\s*id/i',
            '/;\s*whoami/i',
            '/\|\s*nc\s+/i',
            '/\|\s*netcat\s+/i',
            // Path traversal patterns
            '/\.\.\/\.\.\/\.\.\//i',
            '/\.\.\\\.\.\\\..\\\/i',
            '/%2e%2e%2f/i',
            '/%2e%2e%5c/i',
        ];
        $requestData = json_encode($request->all()) ?: '';
        foreach ($attackPatterns as $pattern) {
            try {
                if (preg_match($pattern, (string)$requestData)) {
                    return true;
                }
            } catch (Exception $e) {
                // Skip invalid patterns
                continue;
            }
        }
        return false;
    }
    /**
     * Log security event with enhanced security.
     *
     * Logs security events with comprehensive context and proper
     * error handling for security monitoring and analysis.
     *
     * @param  string  $event  The security event type
     * @param  array  $data  Additional event data
     * @param  string  $level  The log level (warning, error, info)
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     */
    /**
     * @param array<string, mixed> $data
     */
    public function logSecurityEvent(string $event, array $data = [], string $level = 'warning'): void
    {
        try {
            if (empty($event)) {
                throw new \InvalidArgumentException('Event name cannot be empty');
            }
            $allowedLevels = ['warning', 'error', 'info'];
            if (! in_array($level, $allowedLevels, true)) {
                throw new \InvalidArgumentException('Invalid log level: ' . $level);
            }
            $logData = array_merge([
                'event' => $event,
                'timestamp' => now()->toISOString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
            ], $data);
            Log::channel('single')->{$level}('Security event: ' . $event, $logData);
        } catch (Exception $e) {
            Log::error('Failed to log security event: ' . $e->getMessage());
        }
    }
    /**
     * Generate secure token with enhanced security.
     *
     * Generates a cryptographically secure random token with
     * proper validation and error handling.
     *
     * @param  int  $length  The length of the token in characters
     *
     * @return string The generated secure token
     *
     * @throws \InvalidArgumentException When length is invalid
     *
     * @version 1.0.6
     */
    public function generateSecureToken(int $length = 32): string
    {
        try {
            if ($length <= 0 || $length > 128) {
                throw new \InvalidArgumentException('Token length must be between 1 and 128');
            }
            if ($length % 2 !== 0) {
                throw new \InvalidArgumentException('Token length must be even');
            }
            return bin2hex(random_bytes(max(1, (int)($length / 2))));
        } catch (Exception $e) {
            Log::error('Failed to generate secure token: ' . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Validate file upload security with enhanced security.
     *
     * Validates file uploads for security threats including size limits,
     * file type validation, MIME type checking, and content scanning.
     *
     * @param  mixed  $file  The uploaded file to validate
     *
     * @return array Validation result with valid flag and errors
     *
     * @throws \InvalidArgumentException When file is invalid
     *
     * @version 1.0.6
     */
    /**
     * @return array<string, mixed>
     */
    /**
     * @param mixed $file
     * @return array<string, mixed>
     */
    public function validateFileUpload($file): array
    {
        try {
            if (!$file || !is_object($file)) {
                throw new \InvalidArgumentException('File cannot be null or must be an object');
            }
            $result = [
                'valid' => true,
                'errors' => [],
            ];
            // Check file size
            $maxSizeConfig = config('security.file_upload_security.max_upload_size', 10240);
            $maxSize = is_numeric($maxSizeConfig) ? (int)((float)$maxSizeConfig * 1024) : 10240 * 1024;
            $fileSize = method_exists($file, 'getSize') ? $file->getSize() : 0;
            if ($fileSize > $maxSize) {
                $result['valid'] = false;
                $result['errors'][] = 'File size exceeds maximum allowed size';
            }
            // Check file extension
            $allowedExtensionsConfig = config('security.file_upload_security.allowed_extensions', []);
            $allowedExtensions = is_array($allowedExtensionsConfig) ? $allowedExtensionsConfig : [];
            $extension = method_exists($file, 'getClientOriginalExtension') ?
                (is_string($file->getClientOriginalExtension()) ? strtolower($file->getClientOriginalExtension()) : '') : '';
            $isAllowed = false;
            foreach ($allowedExtensions as $category => $extensions) {
                $extensionsArray = is_array($extensions) ? $extensions : [];
                if (in_array($extension, $extensionsArray)) {
                    $isAllowed = true;
                    break;
                }
            }
            if (!$isAllowed) {
                $result['valid'] = false;
                $result['errors'][] = 'File type not allowed';
            }
            // Check MIME type
            $mimeType = method_exists($file, 'getMimeType') ?
                (is_string($file->getMimeType()) ? $file->getMimeType() : '') : '';
            if (!$this->isAllowedMimeType($mimeType)) {
                $result['valid'] = false;
                $result['errors'][] = 'Invalid file MIME type';
            }
            // Scan file content for malicious patterns
            if (config('security.file_upload_security.validate_file_content', true)) {
                $filePath = method_exists($file, 'getRealPath') ? $file->getRealPath() : '';
                $filePathString = is_string($filePath) ? $filePath : '';
                $content = file_get_contents($filePathString);
                if ($content === false) {
                    $result['valid'] = false;
                    $result['errors'][] = 'Unable to read file content';
                } elseif ($this->containsMaliciousContent($content)) {
                    $result['valid'] = false;
                    $result['errors'][] = 'File contains potentially malicious content';
                }
            }
            if (!$result['valid']) {
                $fileName = method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : '';
                $fileSize = method_exists($file, 'getSize') ? $file->getSize() : 0;
                $this->logSecurityEvent('file_upload_validation_failed', [
                    'fileName' => is_string($fileName) ? $fileName : '',
                    'file_size' => is_numeric($fileSize) ? (int)$fileSize : 0,
                    'mime_type' => $mimeType,
                    'errors' => $result['errors'],
                ]);
            }
            return $result;
        } catch (Exception $e) {
            Log::error('Failed to validate file upload: ' . $e->getMessage());
            return [
                'valid' => false,
                'errors' => ['File validation failed'],
            ];
        }
    }
    /**
     * Check if MIME type is allowed with enhanced security.
     *
     * Validates MIME types against a whitelist of allowed types
     * with comprehensive security measures.
     *
     * @param  string  $mimeType  The MIME type to validate
     *
     * @return bool True if MIME type is allowed, false otherwise
     *
     * @version 1.0.6
     */
    private function isAllowedMimeType(string $mimeType): bool
    {
        try {
            if (empty($mimeType)) {
                return false;
            }
            $allowedMimeTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
                'text/html',
                'text/css',
                'application/javascript',
                'text/javascript',
                'application/json',
            ];
            return in_array($mimeType, $allowedMimeTypes, true);
        } catch (Exception $e) {
            Log::error('Failed to validate MIME type: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Check if content contains malicious patterns with enhanced security.
     *
     * Scans content for malicious patterns including PHP code, JavaScript,
     * and other potentially dangerous content.
     *
     * @param  string  $content  The content to scan
     *
     * @return bool True if malicious content is found, false otherwise
     *
     * @version 1.0.6
     */
    private function containsMaliciousContent(string $content): bool
    {
        try {
            if (empty($content)) {
                return false;
            }
            $maliciousPatterns = [
                '/eval\s*\(/i',
                '/exec\s*\(/i',
                '/system\s*\(/i',
                '/shell_exec\s*\(/i',
                '/passthru\s*\(/i',
                '/file_get_contents\s*\(/i',
                '/file_put_contents\s*\(/i',
                '/fopen\s*\(/i',
                '/fwrite\s*\(/i',
                '/include\s*\(/i',
                '/require\s*\(/i',
                '/<\?php/i',
                '/<script.*?>/i',
                '/javascript:/i',
                '/vbscript:/i',
            ];
            foreach ($maliciousPatterns as $pattern) {
                try {
                    if (preg_match($pattern, $content) === 1) {
                        return true;
                    }
                } catch (Exception $e) {
                    Log::error('Failed to check malicious pattern: ' . $e->getMessage());
                    continue;
                }
            }
            return false;
        } catch (Exception $e) {
            Log::error('Failed to scan content for malicious patterns: ' . $e->getMessage());
            return true; // Fail safe - assume malicious if scan fails
        }
    }
    /**
     * Rate limit a specific action with enhanced security.
     *
     * Checks if rate limit has been exceeded for a specific action
     * with proper validation and error handling.
     *
     * @param  string  $key  The rate limit key
     * @param  int  $maxAttempts  Maximum number of attempts allowed
     * @param  int  $decayMinutes  Decay time in minutes
     *
     * @return bool True if rate limit exceeded, false otherwise
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     */
    public function rateLimitExceeded(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        try {
            if (empty($key)) {
                throw new \InvalidArgumentException('Rate limit key cannot be empty');
            }
            if ($maxAttempts <= 0) {
                throw new \InvalidArgumentException('Max attempts must be greater than 0');
            }
            if ($decayMinutes <= 0) {
                throw new \InvalidArgumentException('Decay minutes must be greater than 0');
            }
            return (bool) RateLimiter::tooManyAttempts($key, $maxAttempts);
        } catch (Exception $e) {
            Log::error('Failed to check rate limit: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Clear rate limit for a key with enhanced security.
     *
     * Clears the rate limit for a specific key with proper
     * validation and error handling.
     *
     * @param  string  $key  The rate limit key to clear
     *
     * @throws \InvalidArgumentException When key is invalid
     *
     * @version 1.0.6
     */
    public function clearRateLimit(string $key): void
    {
        try {
            if (empty($key)) {
                throw new \InvalidArgumentException('Rate limit key cannot be empty');
            }
            RateLimiter::clear($key);
        } catch (Exception $e) {
            Log::error('Failed to clear rate limit: ' . $e->getMessage());
        }
    }
}
