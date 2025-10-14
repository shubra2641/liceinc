<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * AI License Analytics Service - Simplified
 */
class AILicenseAnalyticsService
{
    private const CACHE_TTL = 3600;

    /**
     * Get dashboard analytics data
     */
    public function getDashboardAnalytics(int $days = 30): array
    {
        $this->validateDays($days);
        $cacheKey = "analytics_dashboard_{$days}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($days) {
                    return [
                'overview' => $this->getOverview($days),
                'trends' => $this->getTrends($days),
                'predictions' => $this->getPredictions($days),
                        'generated_at' => now()->toISOString(),
                    ];
                });
    }

    /**
     * Get overview statistics
     */
    private function getOverview(int $days): array
    {
            $startDate = now()->subDays($days);
        
        return [
            'total_licenses' => License::count(),
            'active_licenses' => License::where('status', 'active')->count(),
            'expired_licenses' => License::where('status', 'expired')->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_products' => Product::count(),
                'revenue' => $this->calculateRevenue($startDate),
                'growth_rate' => $this->calculateGrowthRate($days),
                'churn_rate' => $this->calculateChurnRate($days),
            'health_score' => $this->calculateHealthScore($days),
        ];
    }

    /**
     * Get trend analysis
     */
    private function getTrends(int $days): array
    {
        $startDate = now()->subDays($days);
        
        $licenseTrends = License::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $customerTrends = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('role', 'customer')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        return [
            'license_creation' => $licenseTrends,
            'customer_acquisition' => $customerTrends,
            'revenue' => $this->getRevenueTrends($startDate),
        ];
    }

    /**
     * Get predictive insights
     */
    private function getPredictions(int $days): array
    {
        return [
            'license_expirations' => $this->predictExpirations(),
            'revenue_forecast' => $this->forecastRevenue($days),
            'churn_prediction' => $this->predictChurn(),
        ];
    }

    /**
     * Get real-time updates
     */
    public function getRealTimeUpdates(): array
    {
            $cacheKey = 'realtime_analytics_' . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () {
                    return [
                'active_licenses_now' => License::where('status', 'active')->count(),
                        'licenses_created_today' => License::whereDate('created_at', today())->count(),
                        'revenue_today' => $this->calculateTodayRevenue(),
                        'generated_at' => now()->toISOString(),
                    ];
                });
    }

    /**
     * Log analytics event
     */
    public function logAnalyticsEvent(string $eventType, array $eventData = []): void
    {
        if (empty($eventType) || strlen($eventType) > 100) {
            throw new InvalidArgumentException('Invalid event type');
        }

        $sanitizedData = $this->sanitizeData($eventData);
        // Log event implementation would go here
    }

    /**
     * Calculate revenue for period
     */
    private function calculateRevenue(Carbon $startDate): float
    {
        return (float) DB::table('invoices')
            ->where('status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');
    }

    /**
     * Calculate growth rate
     */
    private function calculateGrowthRate(int $days): float
    {
        $current = License::where('created_at', '>=', now()->subDays($days))->count();
        $previous = License::whereBetween('created_at', [
                now()->subDays($days * 2),
                now()->subDays($days),
            ])->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Calculate churn rate
     */
    private function calculateChurnRate(int $days): float
    {
        $total = User::where('role', 'customer')->count();
        $churned = User::where('role', 'customer')
                ->where('last_login_at', '<', now()->subDays($days))
                ->count();

        return $total > 0 ? ($churned / $total) * 100 : 0.0;
    }

    /**
     * Calculate health score
     */
    private function calculateHealthScore(int $days): float
    {
        $growthRate = $this->calculateGrowthRate($days);
        $churnRate = $this->calculateChurnRate($days);
        $activeLicenses = License::where('status', 'active')->count();

        $growthScore = min(1.0, max(0.0, $growthRate / 100));
        $churnScore = max(0.0, 1.0 - ($churnRate / 100));
        $licenseScore = min(1.0, $activeLicenses / 1000);

        return round((($growthScore * 0.4) + ($churnScore * 0.4) + ($licenseScore * 0.2)) * 100, 1);
    }

    /**
     * Get revenue trends
     */
    private function getRevenueTrends(Carbon $startDate): array
    {
        $revenueData = DB::table('invoices')
            ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
            ->where('status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trends = [];
        foreach ($revenueData as $row) {
            $trends[$row->date] = $row->revenue;
        }

        return $trends;
    }

    /**
     * Predict license expirations
     */
    private function predictExpirations(): array
    {
        $expiring = License::where('license_expires_at', '<=', now()->addDays(30))
            ->where('status', 'active')
            ->get();

        $predictions = [];
        foreach ($expiring as $license) {
            $predictions[] = [
                'license_id' => $license->id,
                'expires_at' => $license->license_expires_at,
                'days_until_expiry' => now()->diffInDays($license->license_expires_at),
                'renewal_probability' => $this->calculateRenewalProbability($license),
            ];
        }

        return $predictions;
    }

    /**
     * Forecast revenue
     */
    private function forecastRevenue(int $days): array
    {
        $historical = $this->getRevenueTrends(now()->subDays($days));
        $avgRevenue = count($historical) > 0 ? array_sum($historical) / count($historical) : 0;
        
        return [
            'historical' => $historical,
            'forecast' => ['next_30_days' => $avgRevenue * 1.1],
            'confidence' => 0.85,
        ];
    }

    /**
     * Predict customer churn
     */
    private function predictChurn(): array
    {
        $customers = User::where('role', 'customer')->with('licenses')->get();
        $predictions = [];

        foreach ($customers as $customer) {
            $churnScore = $this->calculateChurnScore($customer->id);
            if ($churnScore > 0.5) {
                $predictions[] = [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'churn_score' => $churnScore,
                    'risk_level' => $churnScore > 0.8 ? 'high' : 'medium',
                ];
            }
        }

        return $predictions;
    }

    /**
     * Calculate renewal probability
     */
    private function calculateRenewalProbability(License $license): float
    {
        $user = $license->user;
        if (!$user) return 0.5;

        $factors = [
            'history' => $this->getUserHistory($user->id),
            'activity' => $this->getUserActivity($user->id),
            'payment' => $this->getPaymentHistory($user->id),
        ];

        $weights = ['history' => 0.4, 'activity' => 0.3, 'payment' => 0.3];
        $probability = 0;

        foreach ($factors as $factor => $value) {
            $probability += $value * $weights[$factor];
        }

        return min(1.0, max(0.0, $probability));
    }

    /**
     * Calculate churn score
     */
    private function calculateChurnScore(int $userId): float
    {
        $user = User::find($userId);
        if (!$user) return 0.0;

        $lastLogin = $user->last_login_at;
        $daysSinceLogin = $lastLogin ? now()->diffInDays($lastLogin) : 999;
        
        if ($daysSinceLogin > 30) return 0.9;
        if ($daysSinceLogin > 14) return 0.6;
        if ($daysSinceLogin > 7) return 0.3;
        
        return 0.1;
    }

    /**
     * Calculate today's revenue
     */
    private function calculateTodayRevenue(): float
    {
        return (float) DB::table('invoices')
            ->where('status', 'paid')
            ->whereDate('created_at', today())
            ->sum('amount');
    }

    /**
     * Get user history
     */
    private function getUserHistory(int $userId): float
    {
        $purchases = DB::table('licenses')->where('user_id', $userId)->count();
        return min(1.0, $purchases / 5);
    }

    /**
     * Get user activity
     */
    private function getUserActivity(int $userId): float
    {
        $user = User::find($userId);
        if (!$user || !$user->last_login_at) return 0.0;
        
        $daysSinceLogin = now()->diffInDays($user->last_login_at);
        return max(0.0, 1.0 - ($daysSinceLogin / 30));
    }

    /**
     * Get payment history
     */
    private function getPaymentHistory(int $userId): float
    {
        $payments = DB::table('invoices')
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->count();
            
        $latePayments = DB::table('invoices')
            ->where('user_id', $userId)
            ->where('status', 'overdue')
            ->count();

        if ($payments === 0) return 0.5;
        
        return max(0.0, 1.0 - ($latePayments / $payments));
    }

    /**
     * Validate days parameter
     */
    private function validateDays(int $days): void
    {
        if ($days < 1 || $days > 365) {
            throw new InvalidArgumentException('Days must be between 1 and 365');
        }
    }

    /**
     * Sanitize data
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}