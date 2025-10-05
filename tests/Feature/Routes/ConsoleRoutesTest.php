<?php

namespace Tests\Feature\Routes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

/**
 * Console Routes Feature Test.
 */
class ConsoleRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test inspire command execution.
     */
    public function test_inspire_command_execution(): void
    {
        $this->artisan('inspire')
            ->assertExitCode(0);
    }

    /**
     * Test inspire command with error handling.
     */
    public function test_inspire_command_with_error_handling(): void
    {
        // Mock the Inspiring class to throw an exception
        $this->mock(\Illuminate\Foundation\Inspiring::class, function ($mock) {
            $mock->shouldReceive('quote')
                ->andThrow(new \Exception('Test exception'));
        });

        $this->artisan('inspire')
            ->assertExitCode(0);
    }

    /**
     * Test scheduled commands are registered.
     */
    public function test_scheduled_commands_are_registered(): void
    {
        $events = Schedule::events();

        // Check if invoice processing commands are scheduled
        $invoiceProcessFound = false;
        $invoiceOverdueFound = false;

        foreach ($events as $event) {
            if (str_contains($event->command, 'invoices:process') &&
                str_contains($event->expression, '0 9 * * *')) {
                $invoiceProcessFound = true;
            }

            if (str_contains($event->command, 'invoices:process --overdue') &&
                str_contains($event->expression, '0 * * * *')) {
                $invoiceOverdueFound = true;
            }
        }

        $this->assertTrue($invoiceProcessFound, 'Invoice processing command should be scheduled daily at 9 AM');
        $this->assertTrue($invoiceOverdueFound, 'Overdue invoice processing command should be scheduled hourly');
    }

    /**
     * Test renewal invoice generation commands are scheduled.
     */
    public function test_renewal_invoice_generation_commands_are_scheduled(): void
    {
        $events = Schedule::events();

        // Check if renewal invoice generation commands are scheduled
        $dailyRenewalFound = false;
        $weeklyRenewalFound = false;

        foreach ($events as $event) {
            if (str_contains($event->command, 'licenses:generate-renewal-invoices') &&
                str_contains($event->expression, '0 8 * * *')) {
                $dailyRenewalFound = true;
            }

            if (str_contains($event->command, 'licenses:generate-renewal-invoices --days=30') &&
                str_contains($event->expression, '0 8 * * 0')) {
                $weeklyRenewalFound = true;
            }
        }

        $this->assertTrue($dailyRenewalFound, 'Daily renewal invoice generation should be scheduled at 8 AM');
        $this->assertTrue($weeklyRenewalFound, 'Weekly renewal invoice generation should be scheduled on Sundays at 8 AM');
    }

    /**
     * Test security audit command is scheduled.
     */
    public function test_security_audit_command_is_scheduled(): void
    {
        $events = Schedule::events();

        $securityAuditFound = false;

        foreach ($events as $event) {
            if (str_contains($event->command, 'security:audit') &&
                str_contains($event->expression, '0 2 * * 0')) {
                $securityAuditFound = true;
            }
        }

        $this->assertTrue($securityAuditFound, 'Security audit command should be scheduled weekly on Sundays at 2 AM');
    }

    /**
     * Test cache clearing command is scheduled.
     */
    public function test_cache_clearing_command_is_scheduled(): void
    {
        $events = Schedule::events();

        $cacheClearFound = false;

        foreach ($events as $event) {
            if (str_contains($event->command, 'cache:clear') &&
                str_contains($event->expression, '0 3 * * *')) {
                $cacheClearFound = true;
            }
        }

        $this->assertTrue($cacheClearFound, 'Cache clearing command should be scheduled daily at 3 AM');
    }

    /**
     * Test scheduled commands have proper descriptions.
     */
    public function test_scheduled_commands_have_proper_descriptions(): void
    {
        $events = Schedule::events();

        $descriptions = [];
        foreach ($events as $event) {
            $descriptions[] = $event->description;
        }

        $this->assertContains('Process renewal and overdue invoices daily at 9 AM', $descriptions);
        $this->assertContains('Process overdue invoices hourly', $descriptions);
        $this->assertContains('Generate renewal invoices for licenses expiring within 7 days', $descriptions);
        $this->assertContains('Generate renewal invoices for licenses expiring within 30 days (weekly reminder)', $descriptions);
        $this->assertContains('Run weekly security audit', $descriptions);
        $this->assertContains('Clear application cache daily', $descriptions);
    }

    /**
     * Test scheduled commands have failure callbacks.
     */
    public function test_scheduled_commands_have_failure_callbacks(): void
    {
        $events = Schedule::events();

        $hasFailureCallbacks = false;
        foreach ($events as $event) {
            if ($event->onFailure !== null) {
                $hasFailureCallbacks = true;
                break;
            }
        }

        $this->assertTrue($hasFailureCallbacks, 'Scheduled commands should have failure callbacks for error handling');
    }

    /**
     * Test invoice processing command execution.
     */
    public function test_invoice_processing_command_execution(): void
    {
        $this->artisan('invoices:process')
            ->assertExitCode(0);
    }

    /**
     * Test invoice processing command with renewal option.
     */
    public function test_invoice_processing_command_with_renewal_option(): void
    {
        $this->artisan('invoices:process', ['--renewal' => true])
            ->assertExitCode(0);
    }

    /**
     * Test invoice processing command with overdue option.
     */
    public function test_invoice_processing_command_with_overdue_option(): void
    {
        $this->artisan('invoices:process', ['--overdue' => true])
            ->assertExitCode(0);
    }

    /**
     * Test renewal invoice generation command execution.
     */
    public function test_renewal_invoice_generation_command_execution(): void
    {
        $this->artisan('licenses:generate-renewal-invoices')
            ->assertExitCode(0);
    }

    /**
     * Test renewal invoice generation command with custom days.
     */
    public function test_renewal_invoice_generation_command_with_custom_days(): void
    {
        $this->artisan('licenses:generate-renewal-invoices', ['--days' => 30])
            ->assertExitCode(0);
    }

    /**
     * Test security audit command execution.
     */
    public function test_security_audit_command_execution(): void
    {
        $this->artisan('security:audit')
            ->assertExitCode(0);
    }

    /**
     * Test security audit command with fix option.
     */
    public function test_security_audit_command_with_fix_option(): void
    {
        $this->artisan('security:audit', ['--fix' => true])
            ->assertExitCode(0);
    }

    /**
     * Test security audit command with report option.
     */
    public function test_security_audit_command_with_report_option(): void
    {
        $this->artisan('security:audit', ['--report' => true])
            ->assertExitCode(0);
    }

    /**
     * Test cache clearing command execution.
     */
    public function test_cache_clearing_command_execution(): void
    {
        $this->artisan('cache:clear')
            ->assertExitCode(0);
    }

    /**
     * Test config clearing command execution.
     */
    public function test_config_clearing_command_execution(): void
    {
        $this->artisan('config:clear')
            ->assertExitCode(0);
    }

    /**
     * Test route clearing command execution.
     */
    public function test_route_clearing_command_execution(): void
    {
        $this->artisan('route:clear')
            ->assertExitCode(0);
    }

    /**
     * Test view clearing command execution.
     */
    public function test_view_clearing_command_execution(): void
    {
        $this->artisan('view:clear')
            ->assertExitCode(0);
    }

    /**
     * Test database migration command execution.
     */
    public function test_database_migration_command_execution(): void
    {
        $this->artisan('migrate', ['--force' => true])
            ->assertExitCode(0);
    }

    /**
     * Test database seeding command execution.
     */
    public function test_database_seeding_command_execution(): void
    {
        $this->artisan('db:seed', ['--force' => true])
            ->assertExitCode(0);
    }

    /**
     * Test queue work command execution.
     */
    public function test_queue_work_command_execution(): void
    {
        $this->artisan('queue:work', ['--once' => true])
            ->assertExitCode(0);
    }

    /**
     * Test queue failed command execution.
     */
    public function test_queue_failed_command_execution(): void
    {
        $this->artisan('queue:failed')
            ->assertExitCode(0);
    }

    /**
     * Test storage link command execution.
     */
    public function test_storage_link_command_execution(): void
    {
        $this->artisan('storage:link')
            ->assertExitCode(0);
    }

    /**
     * Test optimize command execution.
     */
    public function test_optimize_command_execution(): void
    {
        $this->artisan('optimize')
            ->assertExitCode(0);
    }

    /**
     * Test optimize clear command execution.
     */
    public function test_optimize_clear_command_execution(): void
    {
        $this->artisan('optimize:clear')
            ->assertExitCode(0);
    }

    /**
     * Test backup command execution.
     */
    public function test_backup_command_execution(): void
    {
        $this->artisan('backup:run')
            ->assertExitCode(0);
    }

    /**
     * Test backup list command execution.
     */
    public function test_backup_list_command_execution(): void
    {
        $this->artisan('backup:list')
            ->assertExitCode(0);
    }

    /**
     * Test scheduled command failure logging.
     */
    public function test_scheduled_command_failure_logging(): void
    {
        Log::shouldReceive('error')
            ->with('Scheduled invoice processing failed', \Mockery::type('array'))
            ->once();

        // Simulate a scheduled command failure
        $event = Schedule::events()[0];
        if ($event->onFailure) {
            $event->onFailure();
        }
    }

    /**
     * Test scheduled command success logging.
     */
    public function test_scheduled_command_success_logging(): void
    {
        // Test that successful commands don't log errors
        Log::shouldReceive('error')
            ->never();

        $this->artisan('invoices:process')
            ->assertExitCode(0);
    }

    /**
     * Test command output formatting.
     */
    public function test_command_output_formatting(): void
    {
        $this->artisan('inspire')
            ->expectsOutput(\Mockery::type('string'))
            ->assertExitCode(0);
    }

    /**
     * Test command with verbose output.
     */
    public function test_command_with_verbose_output(): void
    {
        $this->artisan('invoices:process', ['-v' => true])
            ->assertExitCode(0);
    }

    /**
     * Test command with quiet output.
     */
    public function test_command_with_quiet_output(): void
    {
        $this->artisan('invoices:process', ['-q' => true])
            ->assertExitCode(0);
    }

    /**
     * Test command with no interaction.
     */
    public function test_command_with_no_interaction(): void
    {
        $this->artisan('invoices:process', ['-n' => true])
            ->assertExitCode(0);
    }

    /**
     * Test command with environment specification.
     */
    public function test_command_with_environment_specification(): void
    {
        $this->artisan('invoices:process', ['--env' => 'testing'])
            ->assertExitCode(0);
    }

    /**
     * Test command timeout handling.
     */
    public function test_command_timeout_handling(): void
    {
        // Test that commands don't timeout unexpectedly
        $startTime = microtime(true);

        $this->artisan('invoices:process')
            ->assertExitCode(0);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(30, $executionTime, 'Command should complete within 30 seconds');
    }

    /**
     * Test command memory usage.
     */
    public function test_command_memory_usage(): void
    {
        $initialMemory = memory_get_usage();

        $this->artisan('invoices:process')
            ->assertExitCode(0);

        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;

        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Command should use less than 50MB of memory');
    }

    /**
     * Test command error handling.
     */
    public function test_command_error_handling(): void
    {
        // Test that commands handle errors gracefully
        $this->artisan('invoices:process', ['--invalid-option' => true])
            ->assertExitCode(1);
    }

    /**
     * Test command help output.
     */
    public function test_command_help_output(): void
    {
        $this->artisan('invoices:process', ['--help' => true])
            ->assertExitCode(0);
    }

    /**
     * Test command version output.
     */
    public function test_command_version_output(): void
    {
        $this->artisan('invoices:process', ['--version' => true])
            ->assertExitCode(0);
    }

    /**
     * Test multiple command execution.
     */
    public function test_multiple_command_execution(): void
    {
        $commands = [
            'cache:clear',
            'config:clear',
            'route:clear',
            'view:clear',
        ];

        foreach ($commands as $command) {
            $this->artisan($command)
                ->assertExitCode(0);
        }
    }

    /**
     * Test command with database transaction.
     */
    public function test_command_with_database_transaction(): void
    {
        $this->artisan('invoices:process')
            ->assertExitCode(0);

        // Verify that database state is consistent
        $this->assertDatabaseCount('invoices', 0);
    }

    /**
     * Test command with file operations.
     */
    public function test_command_with_file_operations(): void
    {
        $this->artisan('storage:link')
            ->assertExitCode(0);

        // Verify that storage link was created
        $this->assertTrue(\File::exists(public_path('storage')));
    }
}
