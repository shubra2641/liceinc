<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Threat Detection Service - Handles threat detection and analysis.
 */
class ThreatDetectionService
{
    /**
     * Threat detection configuration.
     */
    private const THREAT_PATTERNS = [
        'sql_injection' => [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\balter\b.*\btable\b)/i',
            '/(\bexec\b.*\b\(\))/i',
            '/(\bexecute\b.*\b\(\))/i',
        ],
        'xss' => [
            '/<script[^>]*>.*?<\/script>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i',
        ],
        'path_traversal' => [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/%2e%2e%2f/',
            '/%2e%2e%5c/',
        ],
        'command_injection' => [
            '/[;&|`$]/',
            '/\b(cat|ls|pwd|whoami|id|uname|ps|netstat|ifconfig)\b/',
            '/\b(rm|del|mkdir|rmdir|copy|move|ren)\b/',
        ],
    ];

    /**
     * Threat severity levels.
     */
    private const THREAT_LEVELS = [
        'low' => 1,
        'medium' => 2,
        'high' => 3,
        'critical' => 4,
    ];

    /**
     * Analyze request for threats.
     */
    public function analyzeRequest(Request $request): array
    {
        try {
            $threats = [];
            $input = $this->extractInput($request);

            foreach (self::THREAT_PATTERNS as $type => $patterns) {
                $matches = $this->checkPatterns($input, $patterns);
                if (!empty($matches)) {
                    $threats[] = [
                        'type' => $type,
                        'matches' => $matches,
                        'severity' => $this->getThreatSeverity($type),
                    ];
                }
            }

            $riskScore = $this->calculateRiskScore($threats);
            $isThreat = $riskScore >= 3;

            if ($isThreat) {
                $this->logThreat($request, $threats, $riskScore);
            }

            return [
                'is_threat' => $isThreat,
                'threats' => $threats,
                'risk_score' => $riskScore,
                'recommendations' => $this->getRecommendations($threats),
            ];
        } catch (\Exception $e) {
            Log::error('Threat analysis failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return [
                'is_threat' => false,
                'threats' => [],
                'risk_score' => 0,
                'recommendations' => [],
            ];
        }
    }

    /**
     * Check if request is suspicious.
     */
    public function isSuspiciousRequest(Request $request): bool
    {
        try {
            $suspiciousIndicators = [
                'unusual_user_agent' => $this->checkUnusualUserAgent($request),
                'rapid_requests' => $this->checkRapidRequests($request),
                'suspicious_ip' => $this->checkSuspiciousIp($request),
                'unusual_headers' => $this->checkUnusualHeaders($request),
            ];

            $suspiciousCount = array_sum($suspiciousIndicators);
            return $suspiciousCount >= 2;
        } catch (\Exception $e) {
            Log::error('Suspicious request check failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return false;
        }
    }

    /**
     * Get threat statistics.
     */
    public function getThreatStatistics(): array
    {
        try {
            $cacheKey = 'threat_statistics';
            $cached = Cache::get($cacheKey);

            if ($cached) {
                return $cached;
            }

            $stats = [
                'total_threats' => 0,
                'threats_by_type' => [],
                'threats_by_severity' => [],
                'top_threat_ips' => [],
                'threats_today' => 0,
            ];

            Cache::put($cacheKey, $stats, 300); // Cache for 5 minutes
            return $stats;
        } catch (\Exception $e) {
            Log::error('Threat statistics retrieval failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_threats' => 0,
                'threats_by_type' => [],
                'threats_by_severity' => [],
                'top_threat_ips' => [],
                'threats_today' => 0,
            ];
        }
    }

    /**
     * Extract input from request.
     */
    private function extractInput(Request $request): string
    {
        $input = array_merge(
            $request->all(),
            $request->headers->all(),
            $request->server->all()
        );

        return json_encode($input);
    }

    /**
     * Check patterns against input.
     */
    private function checkPatterns(string $input, array $patterns): array
    {
        $matches = [];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, $match)) {
                $matches[] = $match[0];
            }
        }
        return $matches;
    }

    /**
     * Get threat severity.
     */
    private function getThreatSeverity(string $type): string
    {
        $severityMap = [
            'sql_injection' => 'critical',
            'xss' => 'high',
            'path_traversal' => 'high',
            'command_injection' => 'critical',
        ];

        return $severityMap[$type] ?? 'medium';
    }

    /**
     * Calculate risk score.
     */
    private function calculateRiskScore(array $threats): int
    {
        $score = 0;
        foreach ($threats as $threat) {
            $severity = $threat['severity'];
            $score += self::THREAT_LEVELS[$severity] ?? 1;
        }
        return $score;
    }

    /**
     * Log threat.
     */
    private function logThreat(Request $request, array $threats, int $riskScore): void
    {
        Log::warning('Security threat detected', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'threats' => $threats,
            'risk_score' => $riskScore,
            'request_data' => $request->all(),
        ]);
    }

    /**
     * Get recommendations.
     */
    private function getRecommendations(array $threats): array
    {
        $recommendations = [];
        foreach ($threats as $threat) {
            $type = $threat['type'];
            switch ($type) {
                case 'sql_injection':
                    $recommendations[] = 'Use prepared statements and parameterized queries';
                    break;
                case 'xss':
                    $recommendations[] = 'Implement proper input sanitization and output encoding';
                    break;
                case 'path_traversal':
                    $recommendations[] = 'Validate file paths and restrict access to sensitive directories';
                    break;
                case 'command_injection':
                    $recommendations[] = 'Avoid executing system commands with user input';
                    break;
            }
        }
        return array_unique($recommendations);
    }

    /**
     * Check unusual user agent.
     */
    private function checkUnusualUserAgent(Request $request): bool
    {
        $userAgent = $request->userAgent();
        if (!$userAgent) {
            return true;
        }

        $suspiciousPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scanner/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check rapid requests.
     */
    private function checkRapidRequests(Request $request): bool
    {
        $ip = $request->ip();
        $cacheKey = "rapid_requests:{$ip}";
        $count = Cache::get($cacheKey, 0);
        $count++;
        Cache::put($cacheKey, $count, 60); // 1 minute

        return $count > 10;
    }

    /**
     * Check suspicious IP.
     */
    private function checkSuspiciousIp(Request $request): bool
    {
        $ip = $request->ip();
        $suspiciousIps = [
            '127.0.0.1',
            '0.0.0.0',
            '::1',
        ];

        return in_array($ip, $suspiciousIps);
    }

    /**
     * Check unusual headers.
     */
    private function checkUnusualHeaders(Request $request): bool
    {
        $suspiciousHeaders = [
            'X-Forwarded-For',
            'X-Real-IP',
            'X-Originating-IP',
            'X-Remote-IP',
            'X-Remote-Addr',
        ];

        foreach ($suspiciousHeaders as $header) {
            if ($request->hasHeader($header)) {
                return true;
            }
        }

        return false;
    }
}
