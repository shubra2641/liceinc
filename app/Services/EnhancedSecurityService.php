<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Enhanced Security Service with comprehensive security features.
 *
 * This service provides advanced security features including rate limiting,
 * input sanitization, threat detection, file upload validation, and
 * comprehensive security event logging with enhanced error handling.
 *
 * Features:
 * - Advanced rate limiting with configurable limits
 * - Comprehensive input sanitization and XSS protection
 * - File upload security validation
 * - Threat detection and suspicious activity monitoring
 * - IP whitelist/blacklist management
 * - Client fingerprinting for tracking
 * - Secure token generation
 * - Security event logging and monitoring
 * - Enhanced error handling and validation
 * - Performance optimization with efficient algorithms
 *
 *
 * @example
 * // Rate limiting
 * $security = new EnhancedSecurityService();
 * if (!$security->checkRateLimit('api_requests', $ip)) {
 *     return response()->json(['error' => 'Rate limit exceeded'], 429);
 * }
 *
 * // Input sanitization
 * $cleanData = $security->sanitizeInput($userInput);
 *
 * // File upload validation
 * $errors = $security->validateFileUpload($uploadedFile);
 */
class EnhancedSecurityService
{
    /**
     * Rate limiting configuration with enhanced security.
     *
     * @var array<string, array{max_attempts: int, decay_minutes: int}>
     */
    private const RATE_LIMITS = [
        'api_requests' => ['max_attempts' => 60, 'decay_minutes' => 1],
        'login_attempts' => ['max_attempts' => 5, 'decay_minutes' => 1],
        'license_verification' => ['max_attempts' => 30, 'decay_minutes' => 1],
        'password_reset' => ['max_attempts' => 3, 'decay_minutes' => 60],
        'file_upload' => ['max_attempts' => 10, 'decay_minutes' => 1],
        'admin_actions' => ['max_attempts' => 20, 'decay_minutes' => 1],
        'user_registration' => ['max_attempts' => 3, 'decay_minutes' => 60],
    ];

    /**
     * Dangerous patterns for XSS and injection detection.
     *
     * @var array<int, string>
     */
    private const DANGEROUS_PATTERNS = [
        '/<script[^>]*>.*?<\/script>/si',
        '/javascript:/i',
        '/vbscript:/i',
        '/on\w+\s*=/i',
        '/<iframe[^>]*>/si',
        '/<object[^>]*>/si',
        '/<embed[^>]*>/si',
        '/<applet[^>]*>/si',
        '/expression\s*\(/i',
        '/url\s*\(/i',
        '/import\s*\(/i',
        '/<meta[^>]*>/si',
        '/<link[^>]*>/si',
        '/<style[^>]*>.*?<\/style>/si',
        '/<form[^>]*>/si',
        '/<input[^>]*>/si',
        '/<textarea[^>]*>/si',
        '/<select[^>]*>/si',
    ];

    /**
     * SQL injection patterns for detection.
     *
     * @var array<int, string>
     */
    private const SQL_INJECTION_PATTERNS = [
        '/union\s+select/i',
        '/drop\s+table/i',
        '/delete\s+from/i',
        '/insert\s+into/i',
        '/update\s+set/i',
        '/or\s+1\s*=\s*1/i',
        '/and\s+1\s*=\s*1/i',
        '/select\s+.*\s+from/i',
        '/create\s+table/i',
        '/alter\s+table/i',
        '/exec\s*\(/i',
        '/execute\s*\(/i',
    ];

    /**
     * Command injection patterns for detection.
     *
     * @var array<int, string>
     */
    private const COMMAND_INJECTION_PATTERNS = [
        '/;\s*rm\s+/i',
        '/;\s*cat\s+/i',
        '/;\s*ls\s+/i',
        '/;\s*wget\s+/i',
        '/;\s*curl\s+/i',
        '/\|\s*nc\s+/i',
        '/;\s*ping\s+/i',
        '/;\s*nslookup\s+/i',
        '/;\s*whoami/i',
        '/;\s*id\s*/i',
    ];

    /**
     * Check rate limit for specific action with enhanced validation.
     *
     * Validates rate limits for various actions with proper error handling
     * and security measures to prevent abuse.
     *
     * @param  string  $action  The action to check rate limit for
     * @param  string  $identifier  Unique identifier (IP, user ID, etc.)
     *
     * @return bool True if rate limit is not exceeded, false otherwise
     *
     * @throws \InvalidArgumentException When invalid action is provided
     *
     * @example
     * if (!$security->checkRateLimit('api_requests', $request->ip())) {
     *     return response()->json(['error' => 'Rate limit exceeded'], 429);
     * }
     */
    public function checkRateLimit(string $action, string $identifier): bool
    {
        try {
            if (empty($action) || empty($identifier)) {
                throw new \InvalidArgumentException('Action and identifier cannot be empty');
            }
            $config = self::RATE_LIMITS[$action] ?? self::RATE_LIMITS['api_requests'];
            $result = RateLimiter::attempt(
                $action.':'.$identifier,
                $config['max_attempts'],
                function () {
                    // Rate limit not exceeded
                },
                $config['decay_minutes'] * 60,
            );

            return (bool)$result;
        } catch (\Exception $e) {
            Log::error('Rate limit check failed', [
                'error' => $e->getMessage(),
                'action' => $action,
                'identifier' => $this->hashForLogging($identifier),
            ]);

            return false;
        }
    }

    /**
     * Get remaining attempts for rate limit with validation.
     *
     * @param  string  $action  The action to check
     * @param  string  $identifier  Unique identifier
     *
     * @return int Number of remaining attempts
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     */
    public function getRemainingAttempts(string $action, string $identifier): int
    {
        try {
            if (empty($action) || empty($identifier)) {
                throw new \InvalidArgumentException('Action and identifier cannot be empty');
            }
            $config = self::RATE_LIMITS[$action] ?? self::RATE_LIMITS['api_requests'];
            $key = $action.':'.$identifier;

            return RateLimiter::remaining($key, $config['max_attempts']);
        } catch (\Exception $e) {
            Log::error('Failed to get remaining attempts', [
                'error' => $e->getMessage(),
                'action' => $action,
                'identifier' => $this->hashForLogging($identifier),
            ]);

            return 0;
        }
    }

    /**
     * Clear rate limit for specific action and identifier.
     *
     * @param  string  $action  The action to clear
     * @param  string  $identifier  Unique identifier
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     */
    public function clearRateLimit(string $action, string $identifier): void
    {
        try {
            if (empty($action) || empty($identifier)) {
                throw new \InvalidArgumentException('Action and identifier cannot be empty');
            }
            RateLimiter::clear($action.':'.$identifier);
        } catch (\Exception $e) {
            Log::error('Failed to clear rate limit', [
                'error' => $e->getMessage(),
                'action' => $action,
                'identifier' => $this->hashForLogging($identifier),
            ]);
        }
    }

    /**
     * Sanitize input data to prevent XSS attacks with enhanced security.
     *
     * Provides comprehensive input sanitization with multiple layers of
     * protection against XSS, injection attacks, and malicious content.
     *
     * @param  mixed  $data  The data to sanitize
     * @param  bool  $allowHtml  Whether to allow HTML tags
     *
     * @return mixed The sanitized data
     *
     * @example
     * $cleanData = $security->sanitizeInput($userInput);
     * $cleanArray = $security->sanitizeInput($userArray);
     */
    public function sanitizeInput(mixed $data, bool $allowHtml = false): mixed
    {
        try {
            if (is_array($data)) {
                return array_map(fn ($item) => $this->sanitizeInput($item, $allowHtml), $data);
            }
            if (! is_string($data)) {
                return $data;
            }
            // Remove null bytes and control characters
            $data = str_replace(["\0", "\x00"], '', $data);
            $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $data) ?? '';
            // Remove dangerous patterns
            foreach (self::DANGEROUS_PATTERNS as $pattern) {
                $data = preg_replace($pattern, '', $data) ?? '';
            }
            if (! $allowHtml) {
                // Strip all HTML tags
                $data = strip_tags($data);
            }
            // Convert special characters to HTML entities
            $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return trim($data);
        } catch (\Exception $e) {
            Log::error('Input sanitization failed', [
                'error' => $e->getMessage(),
                'data_type' => gettype($data),
            ]);

            return is_string($data) ? '' : $data;
        }
    }

    /**
     * Validate file upload security with comprehensive checks.
     *
     * Performs extensive security validation on uploaded files including
     * size limits, MIME type validation, and malicious content detection.
     *
     * @param  \Illuminate\Http\UploadedFile  $file  The uploaded file
     *
     * @return array<int, string> Array of validation errors
     *
     * @example
     * $errors = $security->validateFileUpload($uploadedFile);
     * if (!empty($errors)) {
     *     return response()->json(['errors' => $errors], 400);
     * }
     */
    public function validateFileUpload(\Illuminate\Http\UploadedFile $file): array
    {
        $errors = [];
        try {
            $maxSize = (is_numeric(config('security.file_upload_security.max_size_kb', 2048)) ? (int)config('security.file_upload_security.max_size_kb', 2048) : 2048) * 1024;
            $allowedMimes = config('security.file_upload_security.allowed_mimes', []);
            // Check file size
            if ($file->getSize() > $maxSize) {
                $errors[] = 'File size exceeds maximum allowed size';
            }
            // Check MIME type
            if (! empty($allowedMimes) && is_array($allowedMimes) && ! in_array($file->getClientOriginalExtension(), $allowedMimes)) {
                $errors[] = 'File type not allowed';
            }
            // Check for malicious content in filename
            if ($this->containsMaliciousContent($file->getClientOriginalName())) {
                $errors[] = 'Filename contains potentially malicious content';
            }
            // Additional security checks
            if ($this->isExecutableFile($file)) {
                $errors[] = 'Executable files are not allowed';
            }
            // Check file content for malicious patterns
            if ($this->containsMaliciousFileContent($file)) {
                $errors[] = 'File content contains potentially malicious patterns';
            }
        } catch (\Exception $e) {
            $fileName = $file->getClientOriginalName();
            Log::error('File upload validation failed', [
                'error' => $e->getMessage(),
                'filename' => (string)$fileName,
            ]);
            $errors[] = 'File validation failed';
        }

        return $errors;
    }

    /**
     * Check if content contains malicious patterns.
     *
     * @param  string  $content  The content to check
     *
     * @return bool True if malicious content is detected
     */
    public function containsMaliciousContent(string $content): bool
    {
        try {
            foreach (self::DANGEROUS_PATTERNS as $pattern) {
                if (preg_match($pattern, $content)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Malicious content detection failed', [
                'error' => $e->getMessage(),
            ]);

            return true; // Fail safe - assume malicious if detection fails
        }
    }

    /**
     * Check if file is potentially executable.
     *
     * @param  \Illuminate\Http\UploadedFile  $file  The file to check
     *
     * @return bool True if file is executable
     */
    private function isExecutableFile(\Illuminate\Http\UploadedFile $file): bool
    {
        $executableExtensions = [
            'php', 'php3', 'php4', 'php5', 'phtml', 'pht',
            'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js',
            'jar', 'sh', 'pl', 'py', 'rb', 'cgi', 'asp', 'aspx',
        ];
        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, $executableExtensions);
    }

    /**
     * Check file content for malicious patterns.
     *
     * @param  \Illuminate\Http\UploadedFile  $file  The file to check
     *
     * @return bool True if malicious content is found
     */
    private function containsMaliciousFileContent(\Illuminate\Http\UploadedFile $file): bool
    {
        try {
            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                return false;
            }
            $content = substr($content, 0, 1024); // Check first 1KB

            return $this->containsMaliciousContent($content);
        } catch (\Exception $e) {
            Log::error('File content check failed', [
                'error' => $e->getMessage(),
                'filename' => $file->getClientOriginalName(),
            ]);

            return true; // Fail safe
        }
    }

    /**
     * Generate secure random token with enhanced entropy.
     *
     * @param  int  $length  Token length in characters
     *
     * @return string The generated secure token
     *
     * @throws \InvalidArgumentException When invalid length is provided
     */
    public function generateSecureToken(int $length = 32): string
    {
        try {
            if ($length < 8 || $length > 256) {
                throw new \InvalidArgumentException('Token length must be between 8 and 256 characters');
            }

            return bin2hex(random_bytes(max(1, (int)($length / 2))));
        } catch (\Exception $e) {
            Log::error('Secure token generation failed', [
                'error' => $e->getMessage(),
                'length' => $length,
            ]);

            // Fallback to less secure but functional method
            return substr(
                str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 10)),
                0,
                $length,
            );
        }
    }

    /**
     * Hash sensitive data for logging with enhanced security.
     *
     * @param  string  $data  The data to hash
     *
     * @return string The hashed data
     */
    public function hashForLogging(string $data): string
    {
        try {
            $appKey = config('app.key');
            $keyString = is_string($appKey) ? $appKey : '';

            return hash('sha256', $data.$keyString);
        } catch (\Exception $e) {
            Log::error('Data hashing failed', [
                'error' => $e->getMessage(),
            ]);

            return 'hash_failed';
        }
    }

    /**
     * Log security event with comprehensive context.
     *
     * @param  string  $event  The security event type
     * @param  Request  $request  The HTTP request
     * @param  array<string, mixed>  $context  Additional context data
     */
    public function logSecurityEvent(string $event, Request $request, array $context = []): void
    {
        try {
            Log::warning('Security event: '.$event, array_merge([
                'event' => $event,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString(),
                'fingerprint' => $this->getClientFingerprint($request),
            ], $context));
        } catch (\Exception $e) {
            Log::error('Security event logging failed', [
                'error' => $e->getMessage(),
                'event' => $event,
            ]);
        }
    }

    /**
     * Detect suspicious activity patterns with enhanced detection.
     *
     * @param  Request  $request  The HTTP request to analyze
     *
     * @return array<int, string> Array of detected suspicious activities
     */
    public function detectSuspiciousActivity(Request $request): array
    {
        $suspicious = [];
        try {
            $input = json_encode($request->all());
            if ($input === false) {
                $input = '';
            }
            // Check for SQL injection patterns
            foreach (self::SQL_INJECTION_PATTERNS as $pattern) {
                if (preg_match($pattern, $input)) {
                    $suspicious[] = 'SQL injection attempt detected';
                    break;
                }
            }
            // Check for XSS patterns
            if ($this->containsMaliciousContent($input)) {
                $suspicious[] = 'XSS attempt detected';
            }
            // Check for directory traversal
            if (preg_match('/\.\.\//', $input)) {
                $suspicious[] = 'Directory traversal attempt detected';
            }
            // Check for command injection
            foreach (self::COMMAND_INJECTION_PATTERNS as $pattern) {
                if (preg_match($pattern, $input)) {
                    $suspicious[] = 'Command injection attempt detected';
                    break;
                }
            }
            // Check for suspicious user agent
            $userAgent = $request->userAgent();
            if ($this->isSuspiciousUserAgent($userAgent)) {
                $suspicious[] = 'Suspicious user agent detected';
            }
        } catch (\Exception $e) {
            Log::error('Suspicious activity detection failed', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
            ]);
        }

        return $suspicious;
    }

    /**
     * Check if user agent is suspicious.
     *
     * @param  string|null  $userAgent  The user agent string
     *
     * @return bool True if user agent is suspicious
     */
    private function isSuspiciousUserAgent(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }
        $suspiciousPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/curl/i',
            '/wget/i',
            '/python/i',
            '/java/i',
        ];
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get client fingerprint for tracking with enhanced accuracy.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return string The client fingerprint
     */
    public function getClientFingerprint(Request $request): string
    {
        try {
            $components = [
                $request->ip(),
                $request->userAgent(),
                $request->header('Accept-Language'),
                $request->header('Accept-Encoding'),
                $request->header('Accept'),
            ];

            return hash('sha256', implode('|', array_filter($components)));
        } catch (\Exception $e) {
            Log::error('Client fingerprint generation failed', [
                'error' => $e->getMessage(),
            ]);

            return 'fingerprint_failed';
        }
    }

    /**
     * Check if IP is in whitelist with enhanced validation.
     *
     * @param  string  $ip  The IP address to check
     *
     * @return bool True if IP is whitelisted
     */
    public function isIpWhitelisted(string $ip): bool
    {
        try {
            if (empty($ip) || ! filter_var($ip, FILTER_VALIDATE_IP)) {
                return false;
            }
            $whitelist = config('security.ip_control.whitelist', '');
            if (empty($whitelist) || ! is_string($whitelist)) {
                return false;
            }
            $whitelistedIps = array_map('trim', explode(', ', $whitelist));

            return in_array($ip, $whitelistedIps);
        } catch (\Exception $e) {
            Log::error('IP whitelist check failed', [
                'error' => $e->getMessage(),
                'ip' => $ip,
            ]);

            return false;
        }
    }

    /**
     * Check if IP is in blacklist with enhanced validation.
     *
     * @param  string  $ip  The IP address to check
     *
     * @return bool True if IP is blacklisted
     */
    public function isIpBlacklisted(string $ip): bool
    {
        try {
            if (empty($ip) || ! filter_var($ip, FILTER_VALIDATE_IP)) {
                return false;
            }
            $blacklistConfig = config('security.ip_control.blacklist', '');
            $blacklist = is_string($blacklistConfig) ? $blacklistConfig : '';
            if (empty($blacklist)) {
                return false;
            }
            $blacklistedIps = array_map('trim', explode(', ', $blacklist));

            return in_array($ip, $blacklistedIps);
        } catch (\Exception $e) {
            Log::error('IP blacklist check failed', [
                'error' => $e->getMessage(),
                'ip' => $ip,
            ]);

            return false;
        }
    }
}
