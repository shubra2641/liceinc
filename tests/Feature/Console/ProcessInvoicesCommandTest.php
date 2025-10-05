<?php

namespace Tests\Feature\Console;

use App\Jobs\CreateRenewalInvoices;
use App\Jobs\ProcessOverdueInvoices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Test suite for ProcessInvoicesCommand.
 */
class ProcessInvoicesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /**
     * Test command processes both renewal and overdue invoices.
     */
    public function test_command_processes_both_invoice_types(): void
    {
        $exitCode = Artisan::call('invoices:process');

        $this->assertEquals(0, $exitCode);

        Queue::assertPushed(CreateRenewalInvoices::class);
        Queue::assertPushed(ProcessOverdueInvoices::class);

        $this->assertStringContainsString('All invoice processing jobs have been dispatched', Artisan::output());
    }

    /**
     * Test command processes only renewal invoices.
     */
    public function test_command_processes_renewal_invoices_only(): void
    {
        $exitCode = Artisan::call('invoices:process', ['--renewal' => true]);

        $this->assertEquals(0, $exitCode);

        Queue::assertPushed(CreateRenewalInvoices::class);
        Queue::assertNotPushed(ProcessOverdueInvoices::class);

        $this->assertStringContainsString('Renewal invoice processing job has been dispatched', Artisan::output());
    }

    /**
     * Test command processes only overdue invoices.
     */
    public function test_command_processes_overdue_invoices_only(): void
    {
        $exitCode = Artisan::call('invoices:process', ['--overdue' => true]);

        $this->assertEquals(0, $exitCode);

        Queue::assertNotPushed(CreateRenewalInvoices::class);
        Queue::assertPushed(ProcessOverdueInvoices::class);

        $this->assertStringContainsString('Overdue invoice processing job has been dispatched', Artisan::output());
    }

    /**
     * Test command handles job dispatch failures gracefully.
     */
    public function test_command_handles_job_dispatch_failures(): void
    {
        // Mock Queue to throw an exception
        Queue::shouldReceive('push')
            ->andThrow(new \Exception('Queue connection failed'));

        $exitCode = Artisan::call('invoices:process');

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Failed to process invoices', Artisan::output());
    }

    /**
     * Test command with both options specified.
     */
    public function test_command_with_both_options_specified(): void
    {
        $exitCode = Artisan::call('invoices:process', [
            '--renewal' => true,
            '--overdue' => true,
        ]);

        $this->assertEquals(0, $exitCode);

        // Should process both when both options are specified
        Queue::assertPushed(CreateRenewalInvoices::class);
        Queue::assertPushed(ProcessOverdueInvoices::class);
    }

    /**
     * Test command output messages.
     */
    public function test_command_output_messages(): void
    {
        Artisan::call('invoices:process');
        $output = Artisan::output();

        $this->assertStringContainsString('Processing renewal invoices...', $output);
        $this->assertStringContainsString('Processing overdue invoices...', $output);
        $this->assertStringContainsString('All invoice processing jobs have been dispatched', $output);
    }
}
