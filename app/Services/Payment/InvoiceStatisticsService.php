<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

/**
 * Invoice Statistics Service - Handles invoice statistics and reporting.
 */
class InvoiceStatisticsService
{
    /**
     * Get comprehensive invoice statistics.
     */
    public function getInvoiceStats(): array
    {
        try {
            return [
                'total_invoices' => Invoice::count(),
                'paid_invoices' => Invoice::where('status', 'paid')->count(),
                'pending_invoices' => Invoice::where('status', 'pending')->count(),
                'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
                'cancelled_invoices' => Invoice::where('status', 'cancelled')->count(),
                'total_revenue' => $this->sanitizeAmount((float)Invoice::where('status', 'paid')->sum('amount')),
                'pending_revenue' => $this->sanitizeAmount((float)Invoice::where('status', 'pending')->sum('amount')),
                'overdue_revenue' => $this->sanitizeAmount((float)Invoice::where('status', 'overdue')->sum('amount')),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get invoice statistics', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get revenue statistics by period.
     */
    public function getRevenueByPeriod(string $period = 'month'): array
    {
        try {
            $query = Invoice::where('status', 'paid');

            switch ($period) {
                case 'day':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid period specified');
            }

            return [
                'period' => $period,
                'total_revenue' => $this->sanitizeAmount((float)$query->sum('amount')),
                'invoice_count' => $query->count(),
                'average_invoice' => $this->sanitizeAmount((float)$query->avg('amount')),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get revenue by period', [
                'error' => $e->getMessage(),
                'period' => $period,
            ]);
            throw $e;
        }
    }

    /**
     * Get invoice status distribution.
     */
    public function getStatusDistribution(): array
    {
        try {
            $statuses = ['paid', 'pending', 'overdue', 'cancelled'];
            $distribution = [];

            foreach ($statuses as $status) {
                $count = Invoice::where('status', $status)->count();
                $revenue = Invoice::where('status', $status)->sum('amount');

                $distribution[$status] = [
                    'count' => $count,
                    'revenue' => $this->sanitizeAmount((float)$revenue),
                    'percentage' => $this->calculatePercentage($count, Invoice::count()),
                ];
            }

            return $distribution;
        } catch (\Exception $e) {
            Log::error('Failed to get status distribution', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get top customers by revenue.
     */
    public function getTopCustomersByRevenue(int $limit = 10): array
    {
        try {
            $customers = Invoice::selectRaw('user_id, SUM(amount) as total_revenue, COUNT(*) as invoice_count')
                ->where('status', 'paid')
                ->groupBy('user_id')
                ->orderBy('total_revenue', 'desc')
                ->limit($limit)
                ->get();

            return $customers->map(function ($customer) {
                return [
                    'user_id' => $customer->user_id,
                    'total_revenue' => $this->sanitizeAmount((float)$customer->total_revenue),
                    'invoice_count' => $customer->invoice_count,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get top customers by revenue', [
                'error' => $e->getMessage(),
                'limit' => $limit,
            ]);
            throw $e;
        }
    }

    /**
     * Get invoice trends.
     */
    public function getInvoiceTrends(int $months = 12): array
    {
        try {
            $trends = [];
            $startDate = now()->subMonths($months);

            for ($i = 0; $i < $months; $i++) {
                $date = $startDate->copy()->addMonths($i);
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();

                $invoices = Invoice::whereBetween('created_at', [$monthStart, $monthEnd]);

                $trends[] = [
                    'month' => $date->format('Y-m'),
                    'total_invoices' => $invoices->count(),
                    'paid_invoices' => $invoices->where('status', 'paid')->count(),
                    'revenue' => $this->sanitizeAmount((float)$invoices->where('status', 'paid')->sum('amount')),
                ];
            }

            return $trends;
        } catch (\Exception $e) {
            Log::error('Failed to get invoice trends', [
                'error' => $e->getMessage(),
                'months' => $months,
            ]);
            throw $e;
        }
    }

    /**
     * Get overdue invoices.
     */
    public function getOverdueInvoices(): array
    {
        try {
            $overdueInvoices = Invoice::where('status', 'overdue')
                ->orWhere(function ($query) {
                    $query->where('status', 'pending')
                          ->where('due_date', '<', now());
                })
                ->with(['user', 'license', 'product'])
                ->get();

            return $overdueInvoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'user_id' => $invoice->user_id,
                    'amount' => $this->sanitizeAmount((float)$invoice->amount),
                    'due_date' => $invoice->due_date,
                    'days_overdue' => now()->diffInDays($invoice->due_date),
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get overdue invoices', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize amount.
     */
    private function sanitizeAmount(float $amount): float
    {
        return max(0, round($amount, 2));
    }

    /**
     * Calculate percentage.
     */
    private function calculatePercentage(int $value, int $total): float
    {
        if ($total === 0) {
            return 0;
        }
        return round(($value / $total) * 100, 2);
    }
}
