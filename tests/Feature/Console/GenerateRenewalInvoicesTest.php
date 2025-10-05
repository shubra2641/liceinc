<?php

namespace Tests\Feature\Console;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\EmailService;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Test suite for GenerateRenewalInvoices command.
 */
class GenerateRenewalInvoicesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $product;

    protected $license;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'renewal_price' => 99.99,
            'renewal_period' => 'annual',
            'duration_days' => 365,
        ]);
        $this->license = License::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'status' => 'active',
            'license_expires_at' => Carbon::now()->addDays(5),
        ]);
    }

    /**
     * Test command runs successfully with valid data.
     */
    public function test_command_runs_successfully(): void
    {
        $this->mock(InvoiceService::class, function ($mock) {
            $mock->shouldReceive('createRenewalInvoice')
                ->once()
                ->andReturn(Invoice::factory()->create());
        });

        $this->mock(EmailService::class, function ($mock) {
            $mock->shouldReceive('sendRenewalReminder')
                ->once()
                ->andReturn(true);
            $mock->shouldReceive('sendAdminRenewalReminder')
                ->once()
                ->andReturn(true);
        });

        $exitCode = Artisan::call('licenses:generate-renewal-invoices', [
            '--days' => 7,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Generated', Artisan::output());
    }

    /**
     * Test command validates days parameter.
     */
    public function test_command_validates_days_parameter(): void
    {
        $exitCode = Artisan::call('licenses:generate-renewal-invoices', [
            '--days' => 0,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Days parameter must be between 1 and 365', Artisan::output());
    }

    /**
     * Test command handles licenses without products.
     */
    public function test_command_handles_licenses_without_products(): void
    {
        $licenseWithoutProduct = License::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => null,
            'status' => 'active',
            'license_expires_at' => Carbon::now()->addDays(5),
        ]);

        $exitCode = Artisan::call('licenses:generate-renewal-invoices', [
            '--days' => 7,
        ]);

        $this->assertEquals(0, $exitCode);
    }

    /**
     * Test command skips licenses with pending renewal invoices.
     */
    public function test_command_skips_licenses_with_pending_invoices(): void
    {
        Invoice::factory()->create([
            'license_id' => $this->license->id,
            'type' => 'renewal',
            'status' => 'pending',
        ]);

        $exitCode = Artisan::call('licenses:generate-renewal-invoices', [
            '--days' => 7,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Generated 0 renewal invoices', Artisan::output());
    }

    /**
     * Test command handles products without renewal price.
     */
    public function test_command_handles_products_without_renewal_price(): void
    {
        $this->product->update(['renewal_price' => null]);

        $exitCode = Artisan::call('licenses:generate-renewal-invoices', [
            '--days' => 7,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('No renewal price set', Artisan::output());
    }

    /**
     * Test command calculates expiry dates correctly.
     */
    public function test_command_calculates_expiry_dates_correctly(): void
    {
        $testCases = [
            'monthly' => 1,
            'quarterly' => 3,
            'semi-annual' => 6,
            'annual' => 12,
            'three-years' => 36,
            'lifetime' => 1200,
        ];

        foreach ($testCases as $period => $expectedMonths) {
            $this->product->update(['renewal_period' => $period]);

            $this->mock(InvoiceService::class, function ($mock) use ($expectedMonths) {
                $mock->shouldReceive('createRenewalInvoice')
                    ->once()
                    ->with(\Mockery::on(function ($license) {
                        return $license instanceof License;
                    }), \Mockery::on(function ($data) use ($expectedMonths) {
                        $newExpiry = Carbon::parse($data['new_expiry_date']);
                        $currentExpiry = $this->license->license_expires_at;
                        $diffInMonths = $newExpiry->diffInMonths($currentExpiry);

                        return abs($diffInMonths - $expectedMonths) <= 1;
                    }))
                    ->andReturn(Invoice::factory()->create());
            });

            $this->mock(EmailService::class, function ($mock) {
                $mock->shouldReceive('sendRenewalReminder')->andReturn(true);
                $mock->shouldReceive('sendAdminRenewalReminder')->andReturn(true);
            });

            Artisan::call('licenses:generate-renewal-invoices', ['--days' => 7]);
        }
    }
}
