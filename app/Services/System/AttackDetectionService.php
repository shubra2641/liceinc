<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Attack Detection Service - Handles attack detection and prevention.
 */
class AttackDetectionService
{
    /**
     * Attack patterns configuration.
     */
    private const ATTACK_PATTERNS = [
        'sql_injection' => [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\balter\b.*\btable\b)/i',
            '/(\bexec\b.*\b\(\))/i',
            '/(\bexecute\b.*\b\(\))/i',
            '/(\bcreate\b.*\btable\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
        ],
        'xss' => [
            '/<script[^>]*>.*?<\/script>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i',
            '/<form[^>]*>/i',
            '/<input[^>]*>/i',
            '/<button[^>]*>/i',
        ],
        'path_traversal' => [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/%2e%2e%2f/',
            '/%2e%2e%5c/',
            '/\.\.%2f/',
            '/\.\.%5c/',
        ],
        'command_injection' => [
            '/[;&|`$]/',
            '/\b(cat|ls|pwd|whoami|id|uname|ps|netstat|ifconfig)\b/',
            '/\b(rm|del|mkdir|rmdir|copy|move|ren)\b/',
            '/\b(wget|curl|nc|netcat)\b/',
            '/\b(python|perl|ruby|php)\b/',
        ],
        'ldap_injection' => [
            '/[()=*!&|]/',
            '/\b(cn|ou|dc|uid|objectClass)\b/i',
        ],
        'xml_injection' => [
            '/<!DOCTYPE/i',
            '/<!ENTITY/i',
            '/<![CDATA/i',
            '/<xml/i',
        ],
    ];

    /**
     * Detect attacks in request.
     */
    public function detectAttacks(Request $request): array
    {
        try {
            $attacks = [];
            $input = $this->extractInput($request);

            foreach (self::ATTACK_PATTERNS as $type => $patterns) {
                $matches = $this->checkPatterns($input, $patterns);
                if (!empty($matches)) {
                    $attacks[] = [
                        'type' => $type,
                        'matches' => $matches,
                        'severity' => $this->getAttackSeverity($type),
                    ];
                }
            }

            $riskScore = $this->calculateRiskScore($attacks);
            $isAttack = $riskScore >= 3;

            if ($isAttack) {
                $this->logAttack($request, $attacks, $riskScore);
            }

            return [
                'is_attack' => $isAttack,
                'attacks' => $attacks,
                'risk_score' => $riskScore,
                'recommendations' => $this->getRecommendations($attacks),
            ];
        } catch (\Exception $e) {
            Log::error('Attack detection failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return [
                'is_attack' => false,
                'attacks' => [],
                'risk_score' => 0,
                'recommendations' => [],
            ];
        }
    }

    /**
     * Check for brute force attacks.
     */
    public function detectBruteForce(string $identifier, string $type = 'login'): bool
    {
        try {
            $cacheKey = "brute_force:{$type}:{$identifier}";
            $attempts = Cache::get($cacheKey, 0);
            $attempts++;

            Cache::put($cacheKey, $attempts, 300); // 5 minutes

            $threshold = $this->getBruteForceThreshold($type);
            return $attempts > $threshold;
        } catch (\Exception $e) {
            Log::error('Brute force detection failed', [
                'error' => $e->getMessage(),
                'identifier' => $identifier,
                'type' => $type,
            ]);
            return false;
        }
    }

    /**
     * Check for DDoS attacks.
     */
    public function detectDdos(string $ip): bool
    {
        try {
            $cacheKey = "ddos:{$ip}";
            $requests = Cache::get($cacheKey, 0);
            $requests++;

            Cache::put($cacheKey, $requests, 60); // 1 minute

            $threshold = 100; // 100 requests per minute
            return $requests > $threshold;
        } catch (\Exception $e) {
            Log::error('DDoS detection failed', [
                'error' => $e->getMessage(),
                'ip' => $ip,
            ]);
            return false;
        }
    }

    /**
     * Check for suspicious user agents.
     */
    public function detectSuspiciousUserAgent(string $userAgent): bool
    {
        try {
            $suspiciousPatterns = [
                '/bot/i',
                '/crawler/i',
                '/spider/i',
                '/scanner/i',
                '/curl/i',
                '/wget/i',
                '/python/i',
                '/php/i',
                '/java/i',
                '/perl/i',
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $userAgent)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Suspicious user agent detection failed', [
                'error' => $e->getMessage(),
                'user_agent' => $userAgent,
            ]);
            return false;
        }
    }

    /**
     * Check for suspicious IP addresses.
     */
    public function detectSuspiciousIp(string $ip): bool
    {
        try {
            // Check against known malicious IP ranges
            $maliciousRanges = [
                '10.0.0.0/8',
                '172.16.0.0/12',
                '192.168.0.0/16',
                '127.0.0.0/8',
            ];

            foreach ($maliciousRanges as $range) {
                if ($this->ipInRange($ip, $range)) {
                    return true;
                }
            }

            // Check for private IPs
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Suspicious IP detection failed', [
                'error' => $e->getMessage(),
                'ip' => $ip,
            ]);
            return false;
        }
    }

    /**
     * Get attack statistics.
     */
    public function getAttackStatistics(): array
    {
        try {
            $cacheKey = 'attack_statistics';
            $cached = Cache::get($cacheKey);

            if ($cached) {
                return $cached;
            }

            $stats = [
                'total_attacks' => 0,
                'attacks_by_type' => [],
                'attacks_by_severity' => [],
                'top_attack_ips' => [],
                'attacks_today' => 0,
            ];

            Cache::put($cacheKey, $stats, 300); // Cache for 5 minutes
            return $stats;
        } catch (\Exception $e) {
            Log::error('Attack statistics retrieval failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_attacks' => 0,
                'attacks_by_type' => [],
                'attacks_by_severity' => [],
                'top_attack_ips' => [],
                'attacks_today' => 0,
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
     * Get attack severity.
     */
    private function getAttackSeverity(string $type): string
    {
        $severityMap = [
            'sql_injection' => 'critical',
            'xss' => 'high',
            'path_traversal' => 'high',
            'command_injection' => 'critical',
            'ldap_injection' => 'high',
            'xml_injection' => 'medium',
        ];

        return $severityMap[$type] ?? 'medium';
    }

    /**
     * Calculate risk score.
     */
    private function calculateRiskScore(array $attacks): int
    {
        $score = 0;
        $severityMap = [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4,
        ];

        foreach ($attacks as $attack) {
            $severity = $attack['severity'];
            $score += $severityMap[$severity] ?? 1;
        }

        return $score;
    }

    /**
     * Log attack.
     */
    private function logAttack(Request $request, array $attacks, int $riskScore): void
    {
        Log::warning('Security attack detected', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attacks' => $attacks,
            'risk_score' => $riskScore,
            'request_data' => $request->all(),
        ]);
    }

    /**
     * Get recommendations.
     */
    private function getRecommendations(array $attacks): array
    {
        $recommendations = [];
        foreach ($attacks as $attack) {
            $type = $attack['type'];
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
                case 'ldap_injection':
                    $recommendations[] = 'Use LDAP parameterized queries';
                    break;
                case 'xml_injection':
                    $recommendations[] = 'Validate XML input and disable external entity processing';
                    break;
            }
        }
        return array_unique($recommendations);
    }

    /**
     * Get brute force threshold.
     */
    private function getBruteForceThreshold(string $type): int
    {
        $thresholds = [
            'login' => 5,
            'password_reset' => 3,
            'api' => 10,
            'file_upload' => 5,
        ];

        return $thresholds[$type] ?? 5;
    }

    /**
     * Check if IP is in range.
     */
    private function ipInRange(string $ip, string $range): bool
    {
        try {
            if (strpos($range, '/') === false) {
                return $ip === $range;
            }

            list($subnet, $bits) = explode('/', $range);
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet &= $mask;

            return ($ip & $mask) === $subnet;
        } catch (\Exception $e) {
            Log::error('IP range check failed', [
                'error' => $e->getMessage(),
                'ip' => $ip,
                'range' => $range,
            ]);
            return false;
        }
    }
}
