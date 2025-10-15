<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\LicenseVerificationLog;
use Illuminate\Support\Facades\Log;

/**
 * Verification Analytics Service - Handles verification analytics and statistics.
 */
class VerificationAnalyticsService
{
    /**
     * Get verification statistics.
     */
    public function getVerificationStats(): array
    {
        try {
            return [
                'total_verifications' => LicenseVerificationLog::count(),
                'successful_verifications' => LicenseVerificationLog::successful()->count(),
                'failed_verifications' => LicenseVerificationLog::failed()->count(),
                'success_rate' => $this->calculateSuccessRate(),
                'recent_verifications' => LicenseVerificationLog::recent(24)->count(),
                'top_domains' => $this->getTopDomains(),
                'top_sources' => $this->getTopSources(),
                'verification_trends' => $this->getVerificationTrends(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get verification statistics', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get verification trends.
     */
    public function getVerificationTrends(int $days = 30): array
    {
        try {
            $trends = [];
            $startDate = now()->subDays($days);

            for ($i = 0; $i < $days; $i++) {
                $date = $startDate->copy()->addDays($i);
                $dayStart = $date->copy()->startOfDay();
                $dayEnd = $date->copy()->endOfDay();

                $total = LicenseVerificationLog::whereBetween('created_at', [$dayStart, $dayEnd])->count();
                $successful = LicenseVerificationLog::whereBetween('created_at', [$dayStart, $dayEnd])
                    ->where('is_valid', true)
                    ->count();
                $failed = LicenseVerificationLog::whereBetween('created_at', [$dayStart, $dayEnd])
                    ->where('is_valid', false)
                    ->count();

                $trends[] = [
                    'date' => $date->format('Y-m-d'),
                    'total' => $total,
                    'successful' => $successful,
                    'failed' => $failed,
                    'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
                ];
            }

            return $trends;
        } catch (\Exception $e) {
            Log::error('Failed to get verification trends', [
                'error' => $e->getMessage(),
                'days' => $days,
            ]);
            throw $e;
        }
    }

    /**
     * Get top domains.
     */
    public function getTopDomains(int $limit = 10): array
    {
        try {
            return LicenseVerificationLog::selectRaw('domain, COUNT(*) as count, SUM(CASE WHEN is_valid = 1 THEN 1 ELSE 0 END) as successful')
                ->groupBy('domain')
                ->orderBy('count', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'domain' => $item->domain,
                        'total_verifications' => $item->count,
                        'successful_verifications' => $item->successful,
                        'success_rate' => $item->count > 0 ? round(($item->successful / $item->count) * 100, 2) : 0,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get top domains', [
                'error' => $e->getMessage(),
                'limit' => $limit,
            ]);
            throw $e;
        }
    }

    /**
     * Get top sources.
     */
    public function getTopSources(int $limit = 10): array
    {
        try {
            return LicenseVerificationLog::selectRaw('verification_source, COUNT(*) as count, SUM(CASE WHEN is_valid = 1 THEN 1 ELSE 0 END) as successful')
                ->groupBy('verification_source')
                ->orderBy('count', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'source' => $item->verification_source,
                        'total_verifications' => $item->count,
                        'successful_verifications' => $item->successful,
                        'success_rate' => $item->count > 0 ? round(($item->successful / $item->count) * 100, 2) : 0,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get top sources', [
                'error' => $e->getMessage(),
                'limit' => $limit,
            ]);
            throw $e;
        }
    }

    /**
     * Get suspicious activity.
     */
    public function getSuspiciousActivity(): array
    {
        try {
            $suspicious = [];

            // Multiple failed attempts from same IP
            $failedAttempts = LicenseVerificationLog::selectRaw('ip_address, COUNT(*) as count')
                ->where('is_valid', false)
                ->where('created_at', '>=', now()->subHours(24))
                ->groupBy('ip_address')
                ->having('count', '>', 5)
                ->get();

            foreach ($failedAttempts as $attempt) {
                $suspicious[] = [
                    'type' => 'multiple_failed_attempts',
                    'ip_address' => $attempt->ip_address,
                    'count' => $attempt->count,
                    'description' => 'Multiple failed verification attempts from same IP',
                ];
            }

            // Multiple domains from same IP
            $multipleDomains = LicenseVerificationLog::selectRaw('ip_address, COUNT(DISTINCT domain) as domain_count')
                ->where('created_at', '>=', now()->subHours(24))
                ->groupBy('ip_address')
                ->having('domain_count', '>', 10)
                ->get();

            foreach ($multipleDomains as $domain) {
                $suspicious[] = [
                    'type' => 'multiple_domains',
                    'ip_address' => $domain->ip_address,
                    'domain_count' => $domain->domain_count,
                    'description' => 'Multiple domains from same IP',
                ];
            }

            return $suspicious;
        } catch (\Exception $e) {
            Log::error('Failed to get suspicious activity', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get verification by domain.
     */
    public function getVerificationsByDomain(string $domain, int $limit = 50): array
    {
        try {
            return LicenseVerificationLog::where('domain', $domain)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'is_valid' => $log->is_valid,
                        'status' => $log->status,
                        'verification_source' => $log->verification_source,
                        'ip_address' => $log->ip_address,
                        'response_message' => $log->response_message,
                        'error_details' => $log->error_details,
                        'created_at' => $log->created_at,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get verifications by domain', [
                'error' => $e->getMessage(),
                'domain' => $domain,
            ]);
            throw $e;
        }
    }

    /**
     * Get verification by IP.
     */
    public function getVerificationsByIp(string $ip, int $limit = 50): array
    {
        try {
            return LicenseVerificationLog::where('ip_address', $ip)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'domain' => $log->domain,
                        'is_valid' => $log->is_valid,
                        'status' => $log->status,
                        'verification_source' => $log->verification_source,
                        'response_message' => $log->response_message,
                        'error_details' => $log->error_details,
                        'created_at' => $log->created_at,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get verifications by IP', [
                'error' => $e->getMessage(),
                'ip' => $ip,
            ]);
            throw $e;
        }
    }

    /**
     * Calculate success rate.
     */
    private function calculateSuccessRate(): float
    {
        $total = LicenseVerificationLog::count();
        if ($total === 0) {
            return 0;
        }

        $successful = LicenseVerificationLog::successful()->count();
        return round(($successful / $total) * 100, 2);
    }
}
