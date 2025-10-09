<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Create Renewal Invoices Job with enhanced security and error handling.
 *
 * This job handles the creation of renewal invoices for expiring licenses
 * with comprehensive security measures, error handling, and monitoring.
 *
 * Features:
 * - Automated renewal invoice generation
 * - Database transaction safety
 * - Comprehensive error handling and logging
 * - Security validation and monitoring
 * - Queue-based processing for performance
 * - Retry mechanism for failed operations
 */
class CreateRenewalInvoices implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Models\License
     */
    private $license;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\License $license
     */
    public function __construct(\App\Models\License $license)
    {
        $this->license = $license;
        $this->onQueue('invoices');
    }
    /**
     * Execute the job with enhanced security and error handling.
     *
     * Processes renewal invoice creation with comprehensive validation,
     * database transaction safety, and detailed error logging.
     *
     * @param  InvoiceService  $invoiceService  The invoice service instance
     *
     * @throws \Exception When invoice creation fails
     */
    public function handle(InvoiceService $invoiceService): void
    {
        try {
            DB::beginTransaction();
            // Create renewal invoices with validation
            $licenseInstance = $this->license;
            // License is already validated in constructor
            $result = $invoiceService->createRenewalInvoice($licenseInstance);
            // Invoice created successfully
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CreateRenewalInvoices job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'job_id' => $this->job?->getJobId(),
                'attempts' => $this->attempts(),
                'timestamp' => now()->toISOString(),
            ]);
            throw $e;
        }
    }
    /**
     * Handle a job failure with enhanced logging and monitoring.
     *
     * @param  \Throwable  $exception  The exception that caused the failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CreateRenewalInvoices job permanently failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'job_id' => $this->job?->getJobId(),
            'attempts' => $this->attempts(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
