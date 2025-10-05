<?php

namespace Tests\Feature\Jobs;

use App\Jobs\CreateRenewalInvoices;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Test suite for CreateRenewalInvoices job.
 */
class CreateRenewalInvoicesTest extends TestCase
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
        CreateRenewalInvoices::dispatch();

        Queue::assertPushed(CreateRenewalInvoices::class);
    }

    /**
     * Test job handles successful execution.
     */
    public function test_job_handles_successful_execution(): void
    {
        $this->mock(InvoiceService::class, function ($mock) {
            $mock->shouldReceive('createRenewalInvoicesForExpiringLicenses')
                ->once()
                ->andReturn(5);
        });

        $job = new CreateRenewalInvoices();
        $invoiceService = app(InvoiceService::class);

        $job->handle($invoiceService);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Starting renewal invoices creation job');
        });

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Renewal invoices creation job completed successfully') &&
                   $context['created_count'] === 5;
        });
    }

    /**
     * Test job handles service exceptions.
     */
    public function test_job_handles_service_exceptions(): void
    {
        $this->mock(InvoiceService::class, function ($mock) {
            $mock->shouldReceive('createRenewalInvoicesForExpiringLicenses')
                ->once()
                ->andThrow(new \Exception('Service error'));
        });

        $job = new CreateRenewalInvoices();
        $invoiceService = app(InvoiceService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Service error');

        $job->handle($invoiceService);

        Log::assertLogged('error', function ($message, $context) {
            return str_contains($message, 'CreateRenewalInvoices job failed') &&
                   $context['error'] === 'Service error';
        });
    }

    /**
     * Test job failure handling.
     */
    public function test_job_failure_handling(): void
    {
        $job = new CreateRenewalInvoices();
        $exception = new \Exception('Job failed permanently');

        $job->failed($exception);

        Log::assertLogged('error', function ($message, $context) {
            return str_contains($message, 'CreateRenewalInvoices job permanently failed') &&
                   $context['error'] === 'Job failed permanently';
        });
    }

    /**
     * Test job configuration.
     */
    public function test_job_configuration(): void
    {
        $job = new CreateRenewalInvoices();

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
    }

    /**
     * Test job with null return value.
     */
    public function test_job_with_null_return_value(): void
    {
        $this->mock(InvoiceService::class, function ($mock) {
            $mock->shouldReceive('createRenewalInvoicesForExpiringLicenses')
                ->once()
                ->andReturn(null);
        });

        $job = new CreateRenewalInvoices();
        $invoiceService = app(InvoiceService::class);

        $job->handle($invoiceService);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Renewal invoices creation job completed successfully') &&
                   $context['created_count'] === 0;
        });
    }
}
