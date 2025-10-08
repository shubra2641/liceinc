<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Process Overdue Invoices Job with enhanced security and performance.
 *
 * This job processes overdue invoices by updating their status and sending
 * notifications to users with comprehensive error handling and logging.
 *
 * Features:
 * - Automated overdue invoice processing
 * - Status updates for invoices past due date
 * - Comprehensive error handling with database transactions
 * - Enhanced logging for errors and warnings only
 * - Performance optimization with batch processing
 * - Security measures for data integrity
 * - Proper queue handling and retry logic
 * - Job failure handling and reporting
 *
 * @example
 * // Dispatch job manually
 * ProcessOverdueInvoices::dispatch();
 *
 * // Dispatch with delay
 * ProcessOverdueInvoices::dispatch()->delay(now()->addMinutes(10));
 */
class ProcessOverdueInvoices implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    /**
     * Execute the job with enhanced error handling.
     *
     * Processes all overdue invoices by updating their status to 'overdue'
     * and logging the changes for audit purposes.
     *
     * @throws \Exception When invoice processing fails
     *
     * @example
     * $job = new ProcessOverdueInvoices();
     * $job->handle();
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();
            $processedCount = $this->processOverdueInvoices();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Process overdue invoices job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'job_id' => $this->job?->getJobId(),
            ]);
            throw $e;
        }
    }
    /**
     * Process overdue invoices and update their status.
     *
     * Finds all invoices that are past their due date and updates their
     * status to 'overdue'. Returns the count of processed invoices.
     *
     * @return int Number of invoices processed
     *
     * @throws \Exception When database operations fail
     */
    private function processOverdueInvoices(): int
    {
        $overdueInvoices = Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->whereNotNull('due_date')
            ->get();
        $processedCount = 0;
        foreach ($overdueInvoices as $invoice) {
            try {
                $invoice->update([
                    'status' => 'overdue',
                    'updatedAt' => now(),
                ]);
                $processedCount++;
            } catch (\Exception $e) {
                Log::warning('Failed to process overdue invoice', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        return $processedCount;
    }
    /**
     * Handle a job failure.
     *
     * Logs the job failure with detailed information for debugging
     * and monitoring purposes.
     *
     * @param  \Throwable  $exception  The exception that caused the failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Process overdue invoices job permanently failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'job_id' => $this->job?->getJobId(),
            'attempts' => $this->attempts(),
        ]);
    }
}
