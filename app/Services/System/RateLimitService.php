<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate Limit Service - Handles rate limiting operations.
 */
class RateLimitService
{
    /**
     * Rate limiting configuration.
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
     * Check rate limit for a specific key.
     */
    public function checkRateLimit(string $key, string $identifier): bool
    {
        try {
            if (!isset(self::RATE_LIMITS[$key])) {
                Log::warning('Unknown rate limit key', ['key' => $key]);
                return true;
            }

            $config = self::RATE_LIMITS[$key];
            $rateLimitKey = $this->buildRateLimitKey($key, $identifier);

            return RateLimiter::attempt($rateLimitKey, $config['max_attempts'], function () {
                // Rate limit not exceeded
            }, $config['decay_minutes'] * 60);
        } catch (\Exception $e) {
            Log::error('Rate limit check failed', [
                'key' => $key,
                'identifier' => $identifier,
                'error' => $e->getMessage(),
            ]);
            return true; // Allow on error
        }
    }

    /**
     * Get rate limit status.
     */
    public function getRateLimitStatus(string $key, string $identifier): array
    {
        try {
            if (!isset(self::RATE_LIMITS[$key])) {
                return [
                    'allowed' => true,
                    'remaining' => 0,
                    'reset_time' => null,
                ];
            }

            $config = self::RATE_LIMITS[$key];
            $rateLimitKey = $this->buildRateLimitKey($key, $identifier);

            $remaining = RateLimiter::remaining($rateLimitKey, $config['max_attempts']);
            $availableIn = RateLimiter::availableIn($rateLimitKey);

            return [
                'allowed' => $remaining > 0,
                'remaining' => $remaining,
                'reset_time' => $availableIn > 0 ? now()->addSeconds($availableIn) : null,
            ];
        } catch (\Exception $e) {
            Log::error('Rate limit status check failed', [
                'key' => $key,
                'identifier' => $identifier,
                'error' => $e->getMessage(),
            ]);

            return [
                'allowed' => true,
                'remaining' => 0,
                'reset_time' => null,
            ];
        }
    }

    /**
     * Clear rate limit for a specific key.
     */
    public function clearRateLimit(string $key, string $identifier): bool
    {
        try {
            $rateLimitKey = $this->buildRateLimitKey($key, $identifier);
            RateLimiter::clear($rateLimitKey);
            return true;
        } catch (\Exception $e) {
            Log::error('Rate limit clear failed', [
                'key' => $key,
                'identifier' => $identifier,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get rate limit configuration.
     */
    public function getRateLimitConfig(string $key): ?array
    {
        return self::RATE_LIMITS[$key] ?? null;
    }

    /**
     * Get all rate limit configurations.
     */
    public function getAllRateLimitConfigs(): array
    {
        return self::RATE_LIMITS;
    }

    /**
     * Build rate limit key.
     */
    private function buildRateLimitKey(string $key, string $identifier): string
    {
        return "rate_limit:{$key}:{$identifier}";
    }

    /**
     * Check rate limit for request.
     */
    public function checkRateLimitForRequest(Request $request, string $key): bool
    {
        $identifier = $this->getRequestIdentifier($request);
        return $this->checkRateLimit($key, $identifier);
    }

    /**
     * Get request identifier.
     */
    private function getRequestIdentifier(Request $request): string
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $userId = auth()->id();

        if ($userId) {
            return "user:{$userId}";
        }

        return "ip:{$ip}";
    }
}
