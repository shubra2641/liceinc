<?php

declare(strict_types=1);

namespace App\Services\Installation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling installation security operations.
 */
class InstallationSecurityService
{
    /**
     * Sanitize input data.
     *
     * @param mixed $input The input to sanitize
     * @return string The sanitized input
     */
    public function sanitizeInput($input): string
    {
        if (!is_string($input)) {
            return '';
        }
        
        return trim(strip_tags($input));
    }

    /**
     * Validate license key format.
     *
     * @param string $licenseKey The license key to validate
     * @return bool True if valid, false otherwise
     */
    public function validateLicenseKeyFormat(string $licenseKey): bool
    {
        // Basic format validation - adjust pattern as needed
        return preg_match('/^[A-Za-z0-9\-]{8,}$/', $licenseKey) === 1;
    }

    /**
     * Validate domain format.
     *
     * @param string $domain The domain to validate
     * @return bool True if valid, false otherwise
     */
    public function validateDomainFormat(string $domain): bool
    {
        return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

    /**
     * Log security event.
     *
     * @param string $event The security event
     * @param array<string, mixed> $data Additional data
     * @return void
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        Log::warning('Installation Security Event', [
            'event' => $event,
            'data' => $data,
            'timestamp' => now(),
        ]);
    }

    /**
     * Validate request rate limiting.
     *
     * @param Request $request The HTTP request
     * @return bool True if within limits, false otherwise
     */
    public function validateRateLimit(Request $request): bool
    {
        // Simple rate limiting - can be enhanced with Redis/cache
        $key = 'install_attempts_' . $request->ip();
        $attempts = session($key, 0);
        
        if ($attempts >= 5) {
            $this->logSecurityEvent('Rate limit exceeded', [
                'ip' => $request->ip(),
                'attempts' => $attempts,
            ]);
            return false;
        }
        
        session([$key => $attempts + 1]);
        return true;
    }
}
