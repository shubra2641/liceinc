<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessOverdueInvoices;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Test suite for ProcessOverdueInvoices job.
 */
class ProcessOverdueInvoicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        // Log is already configured for testing
    }

    /**
     * Test job can be dispatched successfully.
     */
    public function test_job_can_be_dispatched(): void
    {
        ProcessOverdueInvoices::dispatch();

        Queue::assertPushed(ProcessOverdueInvoices::class);
    }

    /**
     * Test job handles successful execution.
     */
    public function test_job_handles_successful_execution(): void
    {
        $this->mock(InvoiceService::class, function ($mock) {
            $mock->shouldReceive('processOverdueInvoices')
                ->once()
                ->andReturn(10);
        });

        $job = new ProcessOverdueInvoices();
        $invoiceService = app(InvoiceService::class);

        $job->handle($invoiceService);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Starting overdue invoices processing job');
        });

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Overdue invoices processing job completed successfully') &&
                   $context['processed_count'] === 10;
        });
    }

    /**
     * Test job handles service exceptions.
     */
    public function test_job_handles_service_exceptions(): void
    {
        $this->mock(InvoiceService::class, function ($mock) {
            $mock->shouldReceive('processOverdueInvoices')
                ->once()
                ->andThrow(new \Exception('Service error'));
        });

        $job = new ProcessOverdueInvoices();
        $invoiceService = app(InvoiceService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Service error');

        $job->handle($invoiceService);

        Log::assertLogged('error', function ($message, $context) {
            return str_contains($message, 'ProcessOverdueInvoices job failed') &&
                   $context['error'] === 'Service error';
        });
    }

    /**
     * Test job failure handling.
     */
    public function test_job_failure_handling(): void
    {
        $job = new ProcessOverdueInvoices();
        $exception = new \Exception('Job failed permanently');

        $job->failed($exception);

        Log::assertLogged('error', function ($message, $context) {
            return str_contains($message, 'ProcessOverdueInvoices job permanently failed') &&
                   $context['error'] === 'Job failed permanently';
        });
    }

    /**
     * Test job configuration.
     */
    public function test_job_configuration(): void
    {
        $job = new ProcessOverdueInvoices();

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
    }

    /**
     * Test job with null return value.
     */
    public function test_job_with_null_return_value(): void
    {
        $this->mock(InvoiceService::class, function ($mock) {
            $mock->shouldReceive('processOverdueInvoices')
                ->once()
                ->andReturn(null);
        });

        $job = new ProcessOverdueInvoices();
        $invoiceService = app(InvoiceService::class);

        $job->handle($invoiceService);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Overdue invoices processing job completed successfully') &&
                   $context['processed_count'] === 0;
        });
    }

    /**
     * Test job with zero processed invoices.
     */
    public function test_job_with_zero_processed_invoices(): void
    {
        $this->mock(InvoiceService::class, function ($mock) {
            $mock->shouldReceive('processOverdueInvoices')
                ->once()
                ->andReturn(0);
        });

        $job = new ProcessOverdueInvoices();
        $invoiceService = app(InvoiceService::class);

        $job->handle($invoiceService);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Overdue invoices processing job completed successfully') &&
                   $context['processed_count'] === 0;
        });
    }
}
