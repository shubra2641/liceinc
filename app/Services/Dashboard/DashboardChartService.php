<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\License;
use App\Models\LicenseLog;
use App\Models\Product;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardChartService
{
    public function getSystemOverviewData(): array
    {
        try {
            DB::beginTransaction();
            
            $activeLicenses = License::where('status', 'active')->count();
            $expiredLicenses = License::where('status', 'expired')->count();
            $pendingRequests = Ticket::whereIn('status', ['open', 'pending'])->count();
            $totalProducts = Product::count();
            
            DB::commit();
            
            return [
                'labels' => ['Active Licenses', 'Expired Licenses', 'Pending Requests', 'Total Products'],
                'data' => [$activeLicenses, $expiredLicenses, $pendingRequests, $totalProducts],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('System overview data loading failed', ['error' => $e->getMessage()]);
            return $this->getFallbackSystemOverviewData();
        }
    }

    public function getLicenseDistributionData(): array
    {
        try {
            $regularLicenses = License::where('license_type', 'regular')->count();
            $extendedLicenses = License::where('license_type', 'extended')->count();
            
            return [
                'labels' => ['Regular', 'Extended'],
                'data' => [$regularLicenses, $extendedLicenses],
            ];
        } catch (\Exception $e) {
            Log::error('License distribution data loading failed', ['error' => $e->getMessage()]);
            return $this->getFallbackLicenseDistributionData();
        }
    }

    public function getRevenueData(string $period, int $year): array
    {
        try {
            DB::beginTransaction();
            
            if ($period === 'monthly') {
                return $this->getMonthlyRevenueData($year);
            } elseif ($period === 'quarterly') {
                return $this->getQuarterlyRevenueData($year);
            } else {
                return $this->getYearlyRevenueData();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Revenue data loading failed', ['error' => $e->getMessage()]);
            return $this->getFallbackRevenueData();
        }
    }

    public function getActivityTimelineData(): array
    {
        try {
            $today = Carbon::today();
            $data = [];
            $labels = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $startOfDay = $date->copy()->startOfDay();
                $endOfDay = $date->copy()->endOfDay();
                
                $ticketsCount = Ticket::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
                $licensesCount = License::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
                $dailyTotal = $ticketsCount + $licensesCount;
                
                $data[] = $dailyTotal;
                $labels[] = $date->format('M j');
            }
            
            return [
                'labels' => $labels,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Activity timeline data loading failed', ['error' => $e->getMessage()]);
            return $this->getFallbackActivityTimelineData();
        }
    }

    public function getApiRequestsData(string $period, int $days): array
    {
        try {
            DB::beginTransaction();
            
            if ($period === 'daily') {
                return $this->getDailyApiRequestsData($days);
            } else {
                return $this->getHourlyApiRequestsData();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API requests data loading failed', ['error' => $e->getMessage()]);
            return $this->getFallbackApiRequestsData();
        }
    }

    public function getApiPerformanceData(): array
    {
        try {
            $today = now()->startOfDay();
            $yesterday = now()->subDay()->startOfDay();
            
            $todayStats = $this->getDayStats($today);
            $yesterdayStats = $this->getDayStats($yesterday);
            $topDomains = $this->getTopDomains();
            
            return [
                'today' => $todayStats,
                'yesterday' => $yesterdayStats,
                'top_domains' => $topDomains,
            ];
        } catch (\Exception $e) {
            Log::error('API performance data loading failed', ['error' => $e->getMessage()]);
            return $this->getFallbackApiPerformanceData();
        }
    }

    private function getMonthlyRevenueData(int $year): array
    {
        $data = [];
        $labels = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1)?->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)?->endOfMonth();
            
            $monthlyRevenue = License::join('products', 'licenses.product_id', '=', 'products.id')
                ->whereBetween('licenses.created_at', [$startDate, $endDate])
                ->sum('products.price');
            
            $data[] = (float)$monthlyRevenue;
            $labels[] = Carbon::create($year, $month, 1)?->format('M');
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getQuarterlyRevenueData(int $year): array
    {
        $data = [];
        $labels = ['Q1', 'Q2', 'Q3', 'Q4'];
        
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $quarter * 3;
            $startDate = Carbon::create($year, $startMonth, 1)?->startOfMonth();
            $endDate = Carbon::create($year, $endMonth, 1)?->endOfMonth();
            
            $quarterlyRevenue = License::join('products', 'licenses.product_id', '=', 'products.id')
                ->whereBetween('licenses.created_at', [$startDate, $endDate])
                ->sum('products.price');
            
            $data[] = (float)$quarterlyRevenue;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getYearlyRevenueData(): array
    {
        $currentYear = date('Y');
        $data = [];
        $labels = [];
        
        for ($y = $currentYear - 4; $y <= $currentYear; $y++) {
            $startDate = Carbon::create((int)$y, 1, 1)?->startOfYear();
            $endDate = Carbon::create((int)$y, 12, 31)?->endOfYear();
            
            $yearlyRevenue = License::join('products', 'licenses.product_id', '=', 'products.id')
                ->whereBetween('licenses.created_at', [$startDate, $endDate])
                ->sum('products.price');
            
            $data[] = (float)$yearlyRevenue;
            $labels[] = $y;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getDailyApiRequestsData(int $days): array
    {
        $data = [];
        $labels = [];
        $successData = [];
        $failedData = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
            
            $totalRequests = LicenseLog::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
            $successRequests = LicenseLog::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('status', 'success')->count();
            $failedRequests = LicenseLog::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('status', 'failed')->count();
            
            $data[] = $totalRequests;
            $successData[] = $successRequests;
            $failedData[] = $failedRequests;
            $labels[] = $date->format('M j');
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Requests',
                    'data' => $data,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Successful',
                    'data' => $successData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Failed',
                    'data' => $failedData,
                    'borderColor' => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
            ],
        ];
    }

    private function getHourlyApiRequestsData(): array
    {
        $data = [];
        $labels = [];
        $successData = [];
        $failedData = [];
        
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $startOfHour = $hour->copy()->startOfHour();
            $endOfHour = $hour->copy()->endOfHour();
            
            $totalRequests = LicenseLog::whereBetween('created_at', [$startOfHour, $endOfHour])->count();
            $successRequests = LicenseLog::whereBetween('created_at', [$startOfHour, $endOfHour])
                ->where('status', 'success')->count();
            $failedRequests = LicenseLog::whereBetween('created_at', [$startOfHour, $endOfHour])
                ->where('status', 'failed')->count();
            
            $data[] = $totalRequests;
            $successData[] = $successRequests;
            $failedData[] = $failedRequests;
            $labels[] = $hour->format('H:i');
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Requests',
                    'data' => $data,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Successful',
                    'data' => $successData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Failed',
                    'data' => $failedData,
                    'borderColor' => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
            ],
        ];
    }

    private function getDayStats(Carbon $date): array
    {
        $requests = LicenseLog::whereDate('created_at', $date)->count();
        $success = LicenseLog::whereDate('created_at', $date)->where('status', 'success')->count();
        $failed = LicenseLog::whereDate('created_at', $date)->where('status', 'failed')->count();
        
        return [
            'total' => $requests,
            'success' => $success,
            'failed' => $failed,
            'success_rate' => $requests > 0 ? round(($success / $requests) * 100, 2) : 0,
        ];
    }

    private function getTopDomains(): array
    {
        return LicenseLog::selectRaw('domain, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('domain')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function getFallbackSystemOverviewData(): array
    {
        return [
            'labels' => ['Active Licenses', 'Expired Licenses', 'Pending Requests', 'Total Products'],
            'data' => [0, 0, 0, 0],
        ];
    }

    private function getFallbackLicenseDistributionData(): array
    {
        return [
            'labels' => ['Regular', 'Extended'],
            'data' => [0, 0],
        ];
    }

    private function getFallbackRevenueData(): array
    {
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [0, 0, 0, 0, 0, 0],
        ];
    }

    private function getFallbackActivityTimelineData(): array
    {
        return [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'data' => [0, 0, 0, 0, 0, 0, 0],
        ];
    }

    private function getFallbackApiRequestsData(): array
    {
        return [
            'labels' => [],
            'datasets' => [],
        ];
    }

    private function getFallbackApiPerformanceData(): array
    {
        return [
            'today' => ['total' => 0, 'success' => 0, 'failed' => 0, 'success_rate' => 0],
            'yesterday' => ['total' => 0, 'success' => 0, 'failed' => 0, 'success_rate' => 0],
            'top_domains' => [],
        ];
    }
}
