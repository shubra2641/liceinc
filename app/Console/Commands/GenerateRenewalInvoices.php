<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\License;
use App\Models\Product;
use App\Services\Email\EmailFacade;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Helpers\SecurityHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Generate Renewal Invoices Command
 */
class GenerateRenewalInvoices extends Command
{
    protected $signature = 'licenses:generate-renewal-invoices {--days=7 : Number of days before expiry to generate invoices}';
    protected $description = 'Generate renewal invoices for licenses that are about to expire';

    protected InvoiceService $invoiceService;
    protected EmailFacade $emailService;

    public function __construct(InvoiceService $invoiceService, EmailFacade $emailService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
        $this->emailService = $emailService;
    }

    public function handle(): int
    {
        try {
            $days = $this->validateDays();
            $expiryDate = Carbon::now()->addDays($days);

            $this->info("Generating renewal invoices for licenses expiring within {$days} days...");

            $licenses = $this->getExpiringLicenses($expiryDate);
            $results = $this->processLicenses($licenses);

            $this->displayResults($results);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->handleError($e);
            return Command::FAILURE;
        }
    }

    protected function validateDays(): int
    {
        $days = (int) $this->option('days');

        if ($days < 1 || $days > 365) {
            throw new \InvalidArgumentException(
                'Days must be between 1 and 365, got: ' . SecurityHelper::escapeVariable((string) $days)
            );
        }

        return $days;
    }

    protected function getExpiringLicenses(Carbon $expiryDate)
    {
        return License::with(['user', 'product', 'invoices'])
            ->where('status', 'active')
            ->where('license_expires_at', '<=', $expiryDate)
            ->where('license_expires_at', '>', Carbon::now())
            ->get()
            ->filter(function ($license) {
                return !$license->invoices()
                    ->where('type', 'renewal')
                    ->where('status', 'pending')
                    ->exists();
            });
    }

    protected function processLicenses($licenses): array
    {
        $generated = 0;
        $emails = 0;
        $errors = 0;

        foreach ($licenses as $license) {
            $result = $this->processSingleLicense($license);
            $generated += $result['generated'];
            $emails += $result['emails'];
            $errors += $result['errors'];
        }

        return compact('generated', 'emails', 'errors');
    }

    protected function processSingleLicense(License $license): array
    {
        try {
            DB::beginTransaction();

            $invoice = $this->generateInvoice($license);
            if (!$invoice) {
                DB::rollBack();
                return ['generated' => 0, 'emails' => 0, 'errors' => 1];
            }

            $this->line("Generated renewal invoice for license {$license->license_key}");

            $emailsSent = $this->sendNotifications($license, $invoice) ? 1 : 0;

            DB::commit();

            return ['generated' => 1, 'emails' => $emailsSent, 'errors' => 0];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->handleLicenseError($license, $e);
            return ['generated' => 0, 'emails' => 0, 'errors' => 1];
        }
    }

    protected function generateInvoice(License $license): ?\App\Models\Invoice
    {
        $product = $license->product;
        if (!$product) {
            Log::warning('No product found for license', ['licenseKey' => $license->license_key]);
            return null;
        }

        $renewalPrice = $product->renewal_price ?? $product->price ?? 0;
        if ($renewalPrice <= 0) {
            $this->warn("No renewal price set for product {$product->name}, skipping invoice generation.");
            return null;
        }

        if ($renewalPrice > 999999.99) {
            throw new \InvalidArgumentException("Renewal price exceeds maximum allowed value: {$renewalPrice}");
        }

        $newExpiryDate = $this->calculateNewExpiryDate($license, $product);
        $description = htmlspecialchars(
            "Renewal for {$product->name} - License {$license->license_key}",
            ENT_QUOTES,
            'UTF-8'
        );

        return $this->invoiceService->createRenewalInvoice($license, [
            'amount' => $renewalPrice,
            'description' => $description,
            'due_date' => Carbon::now()->addDays(30),
            'new_expiry_date' => $newExpiryDate,
        ]);
    }

    protected function calculateNewExpiryDate(License $license, Product $product): Carbon
    {
        $currentExpiry = $license->license_expires_at ?? Carbon::now();
        $renewalPeriod = $product->renewal_period;

        return match ($renewalPeriod) {
            'monthly' => $currentExpiry->copy()->addMonth(),
            'quarterly' => $currentExpiry->copy()->addMonths(3),
            'semi-annual' => $currentExpiry->copy()->addMonths(6),
            'annual' => $currentExpiry->copy()->addYear(),
            'three-years' => $currentExpiry->copy()->addYears(3),
            'lifetime' => $currentExpiry->copy()->addYears(100),
            default => $currentExpiry->copy()->addDays($product->duration_days ?? 365),
        };
    }

    protected function sendNotifications(License $license, \App\Models\Invoice $invoice): bool
    {
        $sent = 0;
        $total = 0;

        if ($license->user) {
            $total++;
            if ($this->sendCustomerNotification($license, $invoice)) {
                $sent++;
            }
        }

        $total++;
        if ($this->sendAdminNotification($license, $invoice)) {
            $sent++;
        }

        if ($sent < $total) {
            Log::warning('Some renewal notifications failed to send', [
                'licenseKey' => $license->license_key,
                'sent' => $sent,
                'total' => $total,
            ]);
        }

        return $sent > 0;
    }

    protected function sendCustomerNotification(License $license, \App\Models\Invoice $invoice): bool
    {
        try {
            $customerData = $this->prepareCustomerData($license, $invoice);
            $this->emailService->sendRenewalReminder($license->user, $customerData);
            return true;
        } catch (\Exception $e) {
            $this->logNotificationError('customer', $license, $e);
            return false;
        }
    }

    protected function sendAdminNotification(License $license, \App\Models\Invoice $invoice): bool
    {
        try {
            $adminData = $this->prepareAdminData($license, $invoice);
            $this->emailService->sendAdminRenewalReminder($adminData);
            return true;
        } catch (\Exception $e) {
            $this->logNotificationError('admin', $license, $e);
            return false;
        }
    }

    protected function prepareCustomerData(License $license, \App\Models\Invoice $invoice): array
    {
        return [
            'license_key' => htmlspecialchars($license->license_key ?? '', ENT_QUOTES, 'UTF-8'),
            'product_name' => htmlspecialchars($license->product->name ?? '', ENT_QUOTES, 'UTF-8'),
            'expires_at' => $license->license_expires_at?->format('Y-m-d') ?? 'Unknown',
            'invoice_amount' => $invoice->amount,
            'invoice_due_date' => $invoice->due_date->format('Y-m-d'),
            'invoice_id' => $invoice->id,
        ];
    }

    protected function prepareAdminData(License $license, \App\Models\Invoice $invoice): array
    {
        $user = $license->user;

        return [
            'license_key' => htmlspecialchars($license->license_key ?? '', ENT_QUOTES, 'UTF-8'),
            'product_name' => htmlspecialchars($license->product->name ?? '', ENT_QUOTES, 'UTF-8'),
            'customer_name' => htmlspecialchars($user?->name ?? 'Unknown User', ENT_QUOTES, 'UTF-8'),
            'customer_email' => htmlspecialchars($user?->email ?? 'No email provided', ENT_QUOTES, 'UTF-8'),
            'expires_at' => $license->license_expires_at?->format('Y-m-d') ?? 'Unknown',
            'invoice_amount' => $invoice->amount ?? 0,
            'invoice_id' => $invoice->id ?? 'Unknown',
        ];
    }

    protected function displayResults(array $results): void
    {
        $this->info(
            "Generated {$results['generated']} renewal invoices and sent {$results['emails']} email notifications."
        );

        if ($results['errors'] > 0) {
            $this->warn("Encountered {$results['errors']} errors during processing. Check logs for details.");
        }
    }

    protected function handleError(\Exception $e): void
    {
        Log::error('GenerateRenewalInvoices command failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        $this->error('Command failed: ' . $e->getMessage());
    }

    protected function handleLicenseError(License $license, \Exception $e): void
    {
        Log::error('Failed to generate renewal invoice', [
            'licenseKey' => $license->license_key ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        $this->error("Failed to generate renewal invoice for license {$license->license_key}: {$e->getMessage()}");
    }

    protected function logNotificationError(string $type, License $license, \Exception $e): void
    {
        Log::error("Failed to send {$type} renewal notification", [
            'licenseKey' => $license->license_key ?? 'unknown',
            'userId' => $license->user?->id ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
