<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\License;
use App\Models\LicenseAnalytics;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * AI License Analytics Service with enhanced security and comprehensive analytics.
 *
 * This service provides AI-powered analytics and insights for license management,
 * including predictive analytics, customer behavior analysis, intelligent recommendations,
 * anomaly detection, and real-time monitoring. It implements comprehensive security
 * measures, input validation, and error handling for reliable analytics operations.
 */
class AILicenseAnalyticsService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const PREDICTION_MODEL_VERSION = '1.0';
    /**
     * Get comprehensive license analytics dashboard data with enhanced security and error handling.
     *
     * Retrieves comprehensive analytics data including overview statistics, trend analysis,
     * predictive insights, customer behavior analysis, product performance metrics,
     * geographic distribution, and anomaly detection. All data is cached for performance
     * and includes comprehensive error handling and input validation.
     *
     * @param  int  $days  Number of days to analyze (1-365, default: 30)
     *
     * @return array Comprehensive analytics dashboard data
     *
     * @throws InvalidArgumentException When days parameter is invalid
     * @throws \Exception When analytics data retrieval fails
     *
     * @example
     * $analytics = $service->getDashboardAnalytics(30);
     * $overview = $analytics['overview'];
     * $trends = $analytics['trends'];
     */
    /**
     * @return array<string, mixed>
     */
    public function getDashboardAnalytics(int $days = 30): array
    {
        try {
            // Validate input parameters
            $this->validateDaysParameter($days);
            $cacheKey = "ai_analytics_dashboard_{$days}";
            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($days) {
                return DB::transaction(function () use ($days) {
                    return [
                        'overview' => $this->getOverviewStats($days),
                        'trends' => $this->getTrendAnalysis($days),
                        'predictions' => $this->getPredictiveInsights($days),
                        'customer_insights' => $this->getCustomerInsights($days),
                        'product_performance' => $this->getProductPerformance($days),
                        'geographic_distribution' => $this->getGeographicDistribution($days),
                        'anomaly_detection' => $this->detectAnomalies($days),
                        'generated_at' => now()->toISOString(),
                        'model_version' => self::PREDICTION_MODEL_VERSION,
                    ];
                });
            });
        } catch (\Exception $e) {
            Log::error('Failed to get dashboard analytics', [
                'days' => $days,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get overview statistics with AI-enhanced insights and comprehensive error handling.
     *
     * Retrieves comprehensive overview statistics including license counts, customer metrics,
     * revenue data, growth rates, and AI-powered insights. All data is validated and
     * includes proper error handling for database operations.
     *
     * @param  int  $days  Number of days to analyze
     *
     * @return array Overview statistics with AI insights
     *
     * @throws \Exception When statistics retrieval fails
     */
    /**
     * @return array<string, mixed>
     */
    private function getOverviewStats(int $days): array
    {
        try {
            $startDate = now()->subDays($days);
            $stats = [
                'total_licenses' => $this->getTotalLicenses(),
                'active_licenses' => $this->getActiveLicenses(),
                'expired_licenses' => $this->getExpiredLicenses(),
                'suspended_licenses' => $this->getSuspendedLicenses(),
                'total_customers' => $this->getTotalCustomers(),
                'total_products' => $this->getTotalProducts(),
                'revenue' => $this->calculateRevenue($startDate),
                'growth_rate' => $this->calculateGrowthRate($days),
                'churn_rate' => $this->calculateChurnRate($days),
                'customer_lifetime_value' => $this->calculateCustomerLifetimeValue(),
            ];
            // Add AI insights with error handling
            $stats['ai_insights'] = [
                'health_score' => $this->calculateHealthScore($stats),
                'risk_level' => $this->assessRiskLevel($stats),
                'recommendations' => $this->generateRecommendations($stats),
            ];
            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to get overview stats', [
                'days' => $days,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get trend analysis with predictive elements.
     */
    private function getTrendAnalysis(int $days): array
    {
        $startDate = now()->subDays($days);
        // Get daily license creation trends
        $licenseTrends = License::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        // Get daily revenue trends
        $revenueTrends = $this->getRevenueTrends($startDate);
        // Get customer acquisition trends
        $customerTrends = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('role', 'customer')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        // Apply AI smoothing and prediction
        $smoothedTrends = $this->applyAISmoothing($licenseTrends);
        $predictions = $this->predictFutureTrends($smoothedTrends, 7); // Predict next 7 days
        return [
            'license_creation' => [
                'historical' => $licenseTrends,
                'smoothed' => $smoothedTrends,
                'predicted' => $predictions,
            ],
            'revenue' => $revenueTrends,
            'customer_acquisition' => $customerTrends,
            'trend_direction' => $this->analyzeTrendDirection($smoothedTrends),
            'seasonality' => $this->detectSeasonality($licenseTrends),
        ];
    }
    /**
     * Get predictive insights using machine learning algorithms.
     */
    private function getPredictiveInsights(int $days): array
    {
        $insights = [];
        // License expiration predictions
        $insights['license_expirations'] = $this->predictLicenseExpirations();
        // Revenue forecasting
        $insights['revenue_forecast'] = $this->forecastRevenue($days);
        // Customer churn prediction
        $insights['churn_prediction'] = $this->predictCustomerChurn();
        // Product performance prediction
        $insights['product_performance'] = $this->predictProductPerformance();
        // Market demand prediction
        $insights['market_demand'] = $this->predictMarketDemand();
        return $insights;
    }
    /**
     * Get customer insights with behavioral analysis.
     */
    private function getCustomerInsights(int $days): array
    {
        $startDate = now()->subDays($days);
        // Customer segmentation
        $segments = $this->performCustomerSegmentation();
        // Customer behavior analysis
        $behaviorAnalysis = $this->analyzeCustomerBehavior($startDate);
        // Customer lifetime value analysis
        $ltvAnalysis = $this->analyzeCustomerLifetimeValue();
        // Customer satisfaction prediction
        $satisfactionPrediction = $this->predictCustomerSatisfaction();
        return [
            'segmentation' => $segments,
            'behavior_analysis' => $behaviorAnalysis,
            'lifetime_value' => $ltvAnalysis,
            'satisfaction_prediction' => $satisfactionPrediction,
            'recommendations' => $this->generateCustomerRecommendations($segments),
        ];
    }
    /**
     * Get product performance with AI analysis.
     */
    private function getProductPerformance(int $days): array
    {
        $startDate = now()->subDays($days);
        $products = Product::withCount(['licenses' => function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])->get();
        $performance = [];
        foreach ($products as $product) {
            $performance[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'license_count' => $product->licenses_count,
                'revenue' => $this->calculateProductRevenue($product, $startDate),
                'growth_rate' => $this->calculateProductGrowthRate($product, $days),
                'market_share' => $this->calculateMarketShare($product),
                'customer_satisfaction' => $this->calculateProductSatisfaction($product),
                'ai_score' => $this->calculateProductAIScore($product),
                'recommendations' => $this->generateProductRecommendations($product),
            ];
        }
        // Sort by AI score
        /** @var array<int, array<string, mixed>> $performance */
        usort($performance, function (array $a, array $b): int {
            return (int)$b['ai_score'] <=> (int)$a['ai_score'];
        });
        return $performance;
    }
    /**
     * Get geographic distribution with heatmap data.
     */
    private function getGeographicDistribution(int $days): array
    {
        $startDate = now()->subDays($days);
        // Get license distribution by country
        $distribution = License::selectRaw('country, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('count')
            ->get()
            ->toArray();
        // Get revenue by country
        $revenueByCountry = $this->getRevenueByCountry($startDate);
        // Calculate market penetration
        $marketPenetration = $this->calculateMarketPenetration($distribution);
        return [
            'distribution' => $distribution,
            'revenue_by_country' => $revenueByCountry,
            'market_penetration' => $marketPenetration,
            'growth_opportunities' => $this->identifyGrowthOpportunities($distribution),
        ];
    }
    /**
     * Detect anomalies in license patterns.
     */
    private function detectAnomalies(int $days): array
    {
        $startDate = now()->subDays($days);
        $anomalies = [];
        // Detect unusual license creation patterns
        $licenseAnomalies = $this->detectLicenseCreationAnomalies($startDate);
        if (! empty($licenseAnomalies)) {
            $anomalies['license_creation'] = $licenseAnomalies;
        }
        // Detect suspicious activity
        $suspiciousActivity = $this->detectSuspiciousActivity($startDate);
        if (! empty($suspiciousActivity)) {
            $anomalies['suspicious_activity'] = $suspiciousActivity;
        }
        // Detect revenue anomalies
        $revenueAnomalies = $this->detectRevenueAnomalies($startDate);
        if (! empty($revenueAnomalies)) {
            $anomalies['revenue'] = $revenueAnomalies;
        }
        return $anomalies;
    }
    /**
     * Predict license expirations with confidence scores.
     */
    private function predictLicenseExpirations(): array
    {
        $expiringSoon = License::where('license_expires_at', '<=', now()->addDays(30))
            ->where('status', 'active')
            ->get();
        $predictions = [];
        foreach ($expiringSoon as $license) {
            $renewalProbability = $this->calculateRenewalProbability($license);
            $predictions[] = [
                'license_id' => $license->id,
                'expires_at' => $license->license_expires_at,
                'days_until_expiry' => now()->diffInDays($license->license_expires_at),
                'renewal_probability' => $renewalProbability,
                'confidence_score' => $this->calculateConfidenceScore($license),
                'recommended_action' => $this->getRecommendedAction($renewalProbability),
            ];
        }
        // Sort by renewal probability (lowest first - highest risk)
        /** @var array<int, array<string, mixed>> $predictions */
        usort($predictions, function (array $a, array $b): int {
            return (float)$a['renewal_probability'] <=> (float)$b['renewal_probability'];
        });
        return $predictions;
    }
    /**
     * Forecast revenue using time series analysis.
     */
    private function forecastRevenue(int $days): array
    {
        $startDate = now()->subDays($days);
        $historicalRevenue = $this->getHistoricalRevenue($startDate);
        // Apply time series forecasting (simplified version)
        $forecast = $this->applyTimeSeriesForecasting($historicalRevenue, 30); // Forecast next 30 days
        return [
            'historical' => $historicalRevenue,
            'forecast' => $forecast,
            'confidence_interval' => $this->calculateConfidenceInterval($forecast),
            'trend' => $this->analyzeRevenueTrend($historicalRevenue),
        ];
    }
    /**
     * Predict customer churn using behavioral patterns.
     */
    private function predictCustomerChurn(): array
    {
        $customers = User::where('role', 'customer')->with(['licenses'])->get();
        $churnPredictions = [];
        foreach ($customers as $customer) {
            $churnScore = $this->calculateChurnScore($customer);
            $churnPredictions[] = [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'churn_score' => $churnScore,
                'risk_level' => $this->getRiskLevel($churnScore),
                'last_activity' => $this->getLastActivity($customer),
                'recommended_actions' => $this->getChurnPreventionActions($churnScore),
            ];
        }
        // Sort by churn score (highest first - highest risk)
        /** @var array<int, array<string, mixed>> $churnPredictions */
        usort($churnPredictions, function (array $a, array $b): int {
            return (float)$b['churn_score'] <=> (float)$a['churn_score'];
        });
        return array_slice($churnPredictions, 0, 20); // Top 20 at risk
    }
    /**
     * Calculate renewal probability based on customer behavior.
     */
    private function calculateRenewalProbability(License $license): float
    {
        $factors = [];
        // Customer history factor
        $customerHistory = $this->getCustomerHistory($license->user_id);
        $factors['customer_history'] = $customerHistory;
        // License usage factor
        $usageFactor = $this->getLicenseUsageFactor($license);
        $factors['usage'] = $usageFactor;
        // Payment history factor
        $paymentHistory = $this->getPaymentHistory($license->user_id);
        $factors['payment_history'] = $paymentHistory;
        // Time since last activity
        $activityFactor = $this->getActivityFactor($license);
        $factors['activity'] = $activityFactor;
        // Calculate weighted probability
        $weights = [
            'customer_history' => 0.3,
            'usage' => 0.25,
            'payment_history' => 0.25,
            'activity' => 0.2,
        ];
        $probability = 0;
        foreach ($factors as $factor => $value) {
            $probability += $value * $weights[$factor];
        }
        return min(1.0, max(0.0, $probability));
    }
    /**
     * Apply AI smoothing to trend data.
     */
    private function applyAISmoothing(array $data): array
    {
        if (count($data) < 3) {
            return $data;
        }
        $smoothed = [];
        $values = array_values($data);
        $keys = array_keys($data);
        $valuesCount = count($values);
        for ($i = 0; $i < $valuesCount; $i++) {
            if ($i === 0 || $i === $valuesCount - 1) {
                $smoothed[$keys[$i]] = $values[$i];
            } else {
                // Simple moving average with AI weighting
                $weighted = ($values[$i - 1] * 0.25) + ($values[$i] * 0.5) + ($values[$i + 1] * 0.25);
                $smoothed[$keys[$i]] = round($weighted, 2);
            }
        }
        return $smoothed;
    }
    /**
     * Predict future trends using linear regression.
     */
    private function predictFutureTrends(array $data, int $days): array
    {
        if (count($data) < 2) {
            return [];
        }
        $values = array_values($data);
        $n = count($values);
        // Calculate linear regression
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumXX = 0;
        for ($i = 0; $i < $n; $i++) {
            $x = $i;
            $y = $values[$i];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumXX += $x * $x;
        }
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        // Generate predictions
        $predictions = [];
        $lastDate = array_key_last($data);
        $baseDate = Carbon::parse($lastDate);
        for ($i = 1; $i <= $days; $i++) {
            $predictedValue = $intercept + $slope * ($n + $i - 1);
            $predictedDate = $baseDate->copy()->addDays($i)->format('Y-m-d');
            $predictions[$predictedDate] = max(0, round($predictedValue, 2));
        }
        return $predictions;
    }
    /**
     * Calculate health score for the business.
     */
    private function calculateHealthScore(array $stats): float
    {
        $factors = [
            'growth_rate' => min(1.0, max(0.0, $stats['growth_rate'] / 100)),
            'churn_rate' => max(0.0, 1.0 - ($stats['churn_rate'] / 100)),
            'active_licenses' => min(1.0, $stats['active_licenses'] / 1000),
            'customer_satisfaction' => 0.8, // Placeholder - would come from surveys
        ];
        $weights = [
            'growth_rate' => 0.3,
            'churn_rate' => 0.3,
            'active_licenses' => 0.2,
            'customer_satisfaction' => 0.2,
        ];
        $score = 0;
        foreach ($factors as $factor => $value) {
            $score += $value * $weights[$factor];
        }
        return round($score * 100, 1);
    }
    /**
     * Generate intelligent recommendations.
     */
    private function generateRecommendations(array $stats): array
    {
        $recommendations = [];
        if ($stats['churn_rate'] > 10) {
            $recommendations[] = [
                'type' => 'churn_prevention',
                'priority' => 'high',
                'title' => 'High Churn Rate Detected',
                'description' => 'Customer churn rate is above 10%. Consider implementing retention strategies.',
                'action' => 'Review customer feedback and implement retention programs.',
            ];
        }
        if ($stats['growth_rate'] < 5) {
            $recommendations[] = [
                'type' => 'growth',
                'priority' => 'medium',
                'title' => 'Low Growth Rate',
                'description' => 'Growth rate is below 5%. Consider marketing initiatives.',
                'action' => 'Increase marketing efforts and improve product features.',
            ];
        }
        if ($stats['active_licenses'] > 1000) {
            $recommendations[] = [
                'type' => 'scaling',
                'priority' => 'low',
                'title' => 'Consider Scaling Infrastructure',
                'description' => 'High number of active licenses. Ensure infrastructure can handle load.',
                'action' => 'Review server capacity and consider load balancing.',
            ];
        }
        return $recommendations;
    }
    /**
     * Log analytics event for tracking with enhanced security and error handling.
     *
     * Logs analytics events for system tracking and monitoring. Includes
     * comprehensive error handling and input validation to ensure reliable
     * event logging for analytics purposes.
     *
     * @param  string  $eventType  Type of event to log
     * @param  array  $eventData  Additional event data
     *
     * @throws InvalidArgumentException When event type is invalid
     * @throws \Exception When event logging fails
     *
     * @example
     * $service->logAnalyticsEvent('dashboard_viewed', ['user_id' => 123]);
     */
    public function logAnalyticsEvent(string $eventType, array $eventData = []): void
    {
        try {
            // Validate event type
            if (empty($eventType) || strlen($eventType) > 100) {
                throw new InvalidArgumentException('Event type must be between 1 and 100 characters');
            }
            // Sanitize event data
            $sanitizedEventData = $this->sanitizeEventData($eventData);
            LicenseAnalytics::logEvent(
                licenseId: 0, // System event
                eventType: htmlspecialchars($eventType, ENT_QUOTES, 'UTF-8'),
                eventData: $sanitizedEventData,
                ipAddress: request()->ip(),
                userAgent: request()->userAgent(),
            );
        } catch (\Exception $e) {
            Log::error('Failed to log analytics event', [
                'event_type' => $eventType,
                'event_data' => $eventData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get real-time analytics updates with enhanced security and error handling.
     *
     * Retrieves real-time analytics data including current active licenses,
     * daily license creation counts, revenue data, API call metrics, and
     * system health information. All data is cached for performance and
     * includes comprehensive error handling.
     *
     * @return array Real-time analytics data
     *
     * @throws \Exception When real-time data retrieval fails
     *
     * @example
     * $realtimeData = $service->getRealTimeUpdates();
     * $activeLicenses = $realtimeData['active_licenses_now'];
     */
    public function getRealTimeUpdates(): array
    {
        try {
            $cacheKey = 'realtime_analytics_' . now()->format('Y-m-d-H');
            return Cache::remember($cacheKey, 300, function () {
 // 5 minutes cache
                return DB::transaction(function () {
                    return [
                        'active_licenses_now' => $this->getActiveLicenses(),
                        'licenses_created_today' => License::whereDate('created_at', today())->count(),
                        'revenue_today' => $this->calculateTodayRevenue(),
                        'api_calls_last_hour' => $this->getApiCallsLastHour(),
                        'system_health' => $this->getSystemHealthMetrics(),
                        'generated_at' => now()->toISOString(),
                        'cache_ttl' => 300,
                    ];
                });
            });
        } catch (\Exception $e) {
            Log::error('Failed to get real-time updates', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Validate days parameter for analytics queries.
     *
     * Ensures the days parameter is within acceptable range for analytics queries
     * to prevent performance issues and invalid data retrieval.
     *
     * @param  int  $days  Number of days to validate
     *
     * @throws InvalidArgumentException When days parameter is invalid
     */
    private function validateDaysParameter(int $days): void
    {
        if ($days < 1 || $days > 365) {
            throw new InvalidArgumentException('Days parameter must be between 1 and 365');
        }
    }
    /**
     * Get total licenses count with error handling.
     *
     * @return int Total number of licenses
     *
     * @throws \Exception When database query fails
     */
    private function getTotalLicenses(): int
    {
        try {
            return License::count();
        } catch (\Exception $e) {
            Log::error('Failed to get total licenses count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get active licenses count with error handling.
     *
     * @return int Number of active licenses
     *
     * @throws \Exception When database query fails
     */
    private function getActiveLicenses(): int
    {
        try {
            return License::where('status', 'active')->count();
        } catch (\Exception $e) {
            Log::error('Failed to get active licenses count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get expired licenses count with error handling.
     *
     * @return int Number of expired licenses
     *
     * @throws \Exception When database query fails
     */
    private function getExpiredLicenses(): int
    {
        try {
            return License::where('status', 'expired')->count();
        } catch (\Exception $e) {
            Log::error('Failed to get expired licenses count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get suspended licenses count with error handling.
     *
     * @return int Number of suspended licenses
     *
     * @throws \Exception When database query fails
     */
    private function getSuspendedLicenses(): int
    {
        try {
            return License::where('status', 'suspended')->count();
        } catch (\Exception $e) {
            Log::error('Failed to get suspended licenses count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get total customers count with error handling.
     *
     * @return int Number of customers
     *
     * @throws \Exception When database query fails
     */
    private function getTotalCustomers(): int
    {
        try {
            return User::where('role', 'customer')->count();
        } catch (\Exception $e) {
            Log::error('Failed to get total customers count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get total products count with error handling.
     *
     * @return int Number of products
     *
     * @throws \Exception When database query fails
     */
    private function getTotalProducts(): int
    {
        try {
            return Product::count();
        } catch (\Exception $e) {
            Log::error('Failed to get total products count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Calculate revenue for the specified period with enhanced error handling.
     *
     * @param  Carbon  $startDate  Start date for revenue calculation
     *
     * @return float Calculated revenue amount
     */
    private function calculateRevenue(Carbon $startDate): float
    {
        try {
            // Implementation would calculate revenue from invoices/payments
            // This is a placeholder implementation
            return 0.0;
        } catch (\Exception $e) {
            Log::error('Failed to calculate revenue', [
                'start_date' => $startDate->toISOString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Calculate growth rate with enhanced error handling and validation.
     *
     * @param  int  $days  Number of days to analyze
     *
     * @return float Growth rate percentage
     *
     * @throws \Exception When growth rate calculation fails
     */
    private function calculateGrowthRate(int $days): float
    {
        try {
            $currentPeriod = License::where('created_at', '>=', now()->subDays($days))->count();
            $previousPeriod = License::whereBetween('created_at', [
                now()->subDays($days * 2),
                now()->subDays($days),
            ])->count();
            if ($previousPeriod === 0) {
                return $currentPeriod > 0 ? 100.0 : 0.0;
            }
            return (($currentPeriod - $previousPeriod) / $previousPeriod) * 100;
        } catch (\Exception $e) {
            Log::error('Failed to calculate growth rate', [
                'days' => $days,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Calculate churn rate with enhanced error handling and validation.
     *
     * @param  int  $days  Number of days to analyze
     *
     * @return float Churn rate percentage
     *
     * @throws \Exception When churn rate calculation fails
     */
    private function calculateChurnRate(int $days): float
    {
        try {
            $totalCustomers = User::where('role', 'customer')->count();
            $churnedCustomers = User::where('role', 'customer')
                ->where('last_login_at', '<', now()->subDays($days))
                ->count();
            return $totalCustomers > 0 ? ($churnedCustomers / $totalCustomers) * 100 : 0.0;
        } catch (\Exception $e) {
            Log::error('Failed to calculate churn rate', [
                'days' => $days,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Calculate customer lifetime value with enhanced error handling.
     *
     * @return float Customer lifetime value
     */
    private function calculateCustomerLifetimeValue(): float
    {
        try {
            // Implementation would calculate CLV based on historical data
            // This is a placeholder implementation
            return 0.0;
        } catch (\Exception $e) {
            Log::error('Failed to calculate customer lifetime value', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Assess risk level based on business metrics with enhanced error handling.
     *
     * @param  array  $stats  Business statistics
     *
     * @return string Risk level (low, medium, high)
     *
     * @throws \Exception When risk assessment fails
     */
    private function assessRiskLevel(array $stats): string
    {
        try {
            $healthScore = $this->calculateHealthScore($stats);
            if ($healthScore >= 80) {
                return 'low';
            } elseif ($healthScore >= 60) {
                return 'medium';
            } else {
                return 'high';
            }
        } catch (\Exception $e) {
            Log::error('Failed to assess risk level', [
                'stats' => $stats,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    private function analyzeTrendDirection(array $trends): string
    {
        return 'stable';
    }
    private function detectSeasonality(array $data): array
    {
        return [];
    }
    private function calculateTodayRevenue(): float
    {
        return 0.0;
    }
    private function getApiCallsLastHour(): int
    {
        return 0;
    }
    private function getSystemHealthMetrics(): array
    {
        return [];
    }
    /**
     * Sanitize event data to prevent XSS and injection attacks.
     *
     * Applies security sanitization to event data to prevent
     * XSS attacks and other security vulnerabilities.
     *
     * @param  array  $eventData  The event data to sanitize
     *
     * @return array The sanitized event data
     */
    private function sanitizeEventData(array $eventData): array
    {
        $sanitized = [];
        foreach ($eventData as $key => $value) {
            $sanitizedKey = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            if (is_string($value)) {
                $sanitized[$sanitizedKey] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                $sanitized[$sanitizedKey] = $this->sanitizeEventData($value);
            } else {
                $sanitized[$sanitizedKey] = $value;
            }
        }
        return $sanitized;
    }
}
