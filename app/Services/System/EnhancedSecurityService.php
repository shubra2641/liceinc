<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced Security Service - Provides comprehensive security features.
 */
class EnhancedSecurityService
{
    public function __construct(
        private RateLimitService $rateLimitService,
        private ThreatDetectionService $threatDetectionService,
        private SecurityValidationService $securityValidationService,
        private SecurityMonitoringService $securityMonitoringService
    ) {
    }

    /**
     * Check rate limit for a specific key.
     */
    public function checkRateLimit(string $key, string $identifier): bool
    {
        return $this->rateLimitService->checkRateLimit($key, $identifier);
    }

    /**
     * Get rate limit status.
     */
    public function getRateLimitStatus(string $key, string $identifier): array
    {
        return $this->rateLimitService->getRateLimitStatus($key, $identifier);
    }

    /**
     * Clear rate limit for a specific key.
     */
    public function clearRateLimit(string $key, string $identifier): bool
    {
        return $this->rateLimitService->clearRateLimit($key, $identifier);
    }

    /**
     * Check rate limit for request.
     */
    public function checkRateLimitForRequest(Request $request, string $key): bool
    {
        return $this->rateLimitService->checkRateLimitForRequest($request, $key);
    }

    /**
     * Analyze request for threats.
     */
    public function analyzeRequest(Request $request): array
    {
        return $this->threatDetectionService->analyzeRequest($request);
    }

    /**
     * Check if request is suspicious.
     */
    public function isSuspiciousRequest(Request $request): bool
    {
        return $this->threatDetectionService->isSuspiciousRequest($request);
    }

    /**
     * Get threat statistics.
     */
    public function getThreatStatistics(): array
    {
        return $this->threatDetectionService->getThreatStatistics();
    }

    /**
     * Validate input for security threats.
     */
    public function validateInput(array $input): array
    {
        return $this->securityValidationService->validateInput($input);
    }

    /**
     * Validate file upload.
     */
    public function validateFileUpload($file, array $allowedTypes = [], int $maxSize = 5242880): array
    {
        return $this->securityValidationService->validateFileUpload($file, $allowedTypes, $maxSize);
    }

    /**
     * Validate API request.
     */
    public function validateApiRequest(Request $request): array
    {
        return $this->securityValidationService->validateApiRequest($request);
    }

    /**
     * Validate user input.
     */
    public function validateUserInput(array $input, array $rules = []): array
    {
        return $this->securityValidationService->validateUserInput($input, $rules);
    }

    /**
     * Log security event.
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        $this->securityMonitoringService->logSecurityEvent($event, $data);
    }

    /**
     * Log failed login attempt.
     */
    public function logFailedLogin(string $email, string $reason = 'Invalid credentials'): void
    {
        $this->securityMonitoringService->logFailedLogin($email, $reason);
    }

    /**
     * Log successful login.
     */
    public function logSuccessfulLogin(int $userId, string $email): void
    {
        $this->securityMonitoringService->logSuccessfulLogin($userId, $email);
    }

    /**
     * Log password change.
     */
    public function logPasswordChange(int $userId, string $email): void
    {
        $this->securityMonitoringService->logPasswordChange($userId, $email);
    }

    /**
     * Log account lockout.
     */
    public function logAccountLockout(string $email, string $reason): void
    {
        $this->securityMonitoringService->logAccountLockout($email, $reason);
    }

    /**
     * Log suspicious activity.
     */
    public function logSuspiciousActivity(string $activity, array $details = []): void
    {
        $this->securityMonitoringService->logSuspiciousActivity($activity, $details);
    }

    /**
     * Get security statistics.
     */
    public function getSecurityStatistics(): array
    {
        return $this->securityMonitoringService->getSecurityStatistics();
    }

    /**
     * Get recent security events.
     */
    public function getRecentSecurityEvents(int $limit = 50): array
    {
        return $this->securityMonitoringService->getRecentSecurityEvents($limit);
    }

    /**
     * Get security alerts.
     */
    public function getSecurityAlerts(): array
    {
        return $this->securityMonitoringService->getSecurityAlerts();
    }

    /**
     * Check for security anomalies.
     */
    public function checkSecurityAnomalies(): array
    {
        return $this->securityMonitoringService->checkSecurityAnomalies();
    }

    /**
     * Comprehensive security check for request.
     */
    public function performSecurityCheck(Request $request): array
    {
        try {
            $results = [
                'rate_limit' => $this->checkRateLimitForRequest($request, 'api_requests'),
                'threat_analysis' => $this->analyzeRequest($request),
                'suspicious' => $this->isSuspiciousRequest($request),
                'validation' => $this->validateApiRequest($request),
            ];

            $isSecure = $results['rate_limit'] &&
                       !$results['threat_analysis']['is_threat'] &&
                       !$results['suspicious'] &&
                       $results['validation']['valid'];

            if (!$isSecure) {
                $this->logSuspiciousActivity('Security check failed', $results);
            }

            return [
                'secure' => $isSecure,
                'results' => $results,
                'recommendations' => $this->getSecurityRecommendations($results),
            ];
        } catch (\Exception $e) {
            Log::error('Security check failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return [
                'secure' => false,
                'results' => [],
                'recommendations' => ['Investigate security check failure'],
            ];
        }
    }

    /**
     * Get security recommendations.
     */
    private function getSecurityRecommendations(array $results): array
    {
        $recommendations = [];

        if (!$results['rate_limit']) {
            $recommendations[] = 'Rate limit exceeded - consider implementing backoff strategy';
        }

        if ($results['threat_analysis']['is_threat']) {
            $recommendations[] = 'Threat detected - review and block suspicious activity';
        }

        if ($results['suspicious']) {
            $recommendations[] = 'Suspicious request detected - investigate further';
        }

        if (!$results['validation']['valid']) {
            $recommendations[] = 'Input validation failed - review input sanitization';
        }

        return $recommendations;
    }
}
