<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Invoice;
use App\Models\KbArticle;
use App\Models\License;
use App\Models\LicenseLog;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardStatsService
{
    public function getBasicStats(): array
    {
        try {
            return [
                'products' => Product::count(),
                'customers' => User::count(),
                'licenses_active' => License::where('status', 'active')->count(),
                'tickets_open' => Ticket::whereIn('status', ['open', 'pending'])->count(),
                'kb_articles' => KbArticle::count(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get basic stats', ['error' => $e->getMessage()]);
            return $this->getFallbackBasicStats();
        }
    }

    public function getInvoiceStats(): array
    {
        try {
            $invoiceTotalCount = Invoice::count();
            $invoiceTotalAmount = (float)Invoice::sum('amount');
            $invoicePaidAmount = (float)Invoice::where('status', 'paid')->sum('amount');
            $invoicePaidCount = Invoice::where('status', 'paid')->count();
            $invoiceDueSoonAmount = (float)Invoice::where('status', 'pending')
                ->where('due_date', '<=', now()->addDays(7))
                ->sum('amount');
            $invoiceUnpaidAmount = (float)Invoice::where('status', '!=', 'paid')->sum('amount');
            $invoiceCancelledCount = Invoice::where('status', 'cancelled')->count();
            $invoiceCancelledAmount = (float)Invoice::where('status', 'cancelled')->sum('amount');

            return [
                'invoices_count' => $invoiceTotalCount,
                'invoices_total_amount' => $invoiceTotalAmount,
                'invoices_paid_amount' => $invoicePaidAmount,
                'invoices_paid_count' => $invoicePaidCount,
                'invoices_due_soon_amount' => $invoiceDueSoonAmount,
                'invoices_unpaid_amount' => $invoiceUnpaidAmount,
                'invoices_cancelled_count' => $invoiceCancelledCount,
                'invoices_cancelled_amount' => $invoiceCancelledAmount,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get invoice stats', ['error' => $e->getMessage()]);
            return $this->getFallbackInvoiceStats();
        }
    }

    public function getApiStats(): array
    {
        try {
            return [
                'api_requests_today' => LicenseLog::whereDate('created_at', today())->count(),
                'api_requests_this_month' => LicenseLog::whereMonth('created_at', now()->month)->count(),
                'api_success_rate' => $this->calculateApiSuccessRate(),
                'api_errors_today' => $this->getApiErrorsToday(),
                'api_errors_this_month' => $this->getApiErrorsThisMonth(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get API stats', ['error' => $e->getMessage()]);
            return $this->getFallbackApiStats();
        }
    }

    public function getLatestData(): array
    {
        try {
            return [
                'latestTickets' => Ticket::latest()->with('user')->limit(5)->get(),
                'latestLicenses' => License::latest()->with('product', 'user')->limit(5)->get(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get latest data', ['error' => $e->getMessage()]);
            return [
                'latestTickets' => collect(),
                'latestLicenses' => collect(),
            ];
        }
    }

    private function calculateApiSuccessRate(): float
    {
        $totalRequests = LicenseLog::count();
        if ($totalRequests === 0) {
            return 0.0;
        }
        $successfulRequests = LicenseLog::where('status', 'success')->count();
        return round(($successfulRequests / $totalRequests) * 100, 2);
    }

    private function getApiErrorsToday(): int
    {
        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            return 0;
        }
        
        $today = now()->format('Y-m-d');
        $errorCount = 0;
        $handle = fopen($logFile, 'r');
        
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (
                    strpos($line, $today) !== false &&
                    strpos($line, 'License verification error') !== false
                ) {
                    $errorCount++;
                }
            }
            fclose($handle);
        }
        
        return $errorCount;
    }

    private function getApiErrorsThisMonth(): int
    {
        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            return 0;
        }
        
        $month = now()->format('Y-m');
        $errorCount = 0;
        $handle = fopen($logFile, 'r');
        
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (
                    strpos($line, $month) !== false &&
                    strpos($line, 'License verification error') !== false
                ) {
                    $errorCount++;
                }
            }
            fclose($handle);
        }
        
        return $errorCount;
    }

    private function getFallbackBasicStats(): array
    {
        return [
            'products' => 0,
            'customers' => 0,
            'licenses_active' => 0,
            'tickets_open' => 0,
            'kb_articles' => 0,
        ];
    }

    private function getFallbackInvoiceStats(): array
    {
        return [
            'invoices_count' => 0,
            'invoices_total_amount' => 0,
            'invoices_paid_amount' => 0,
            'invoices_paid_count' => 0,
            'invoices_due_soon_amount' => 0,
            'invoices_unpaid_amount' => 0,
            'invoices_cancelled_count' => 0,
            'invoices_cancelled_amount' => 0,
        ];
    }

    private function getFallbackApiStats(): array
    {
        return [
            'api_requests_today' => 0,
            'api_requests_this_month' => 0,
            'api_success_rate' => 0,
            'api_errors_today' => 0,
            'api_errors_this_month' => 0,
        ];
    }
}
