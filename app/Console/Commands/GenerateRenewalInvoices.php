<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\License;
use App\Models\Product;
use App\Services\Email\EmailFacade;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Generate Renewal Invoices Command - Simplified.
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

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $daysBeforeExpiry = $this->validateDaysOption();
            $expiryDate = Carbon::now()->addDays($daysBeforeExpiry);

            $this->info('Generating renewal invoices for licenses expiring within '.$daysBeforeExpiry.' days...');

            $licenses = $this->getExpiringLicenses($expiryDate);
            $generatedCount = 0;
            $emailSentCount = 0;
            $errorCount = 0;

            foreach ($licenses as $license) {
                try {
                    DB::beginTransaction();
                    $invoice = $this->generateRenewalInvoice($license);

                    if ($invoice !== null) {
                        $generatedCount++;
                        $this->line('Generated renewal invoice for license '.$license->license_key.' (Product: '.($license->product->name ?? 'Unknown Product').')');

                        if ($this->sendRenewalNotifications($license, $invoice)) {
                            $emailSentCount++;
                        }
                        DB::commit();
                    } else {
                        DB::rollBack();
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errorCount++;
                    $this->handleLicenseError($license, $e);
                }
            }

            $this->info('Generated '.$generatedCount.' renewal invoices and sent '.$emailSentCount.' email notifications.');
            if ($errorCount > 0) {
                $this->warn('Encountered '.$errorCount.' errors during processing. Check logs for details.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error('GenerateRenewalInvoices command failed', ['error' => $e->getMessage()]);
            $this->error('Command failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Validate days option.
     */
    protected function validateDaysOption(): int
    {
        $days = (int)$this->option('days');
        if ($days < 1 || $days > 365) {
            throw new \InvalidArgumentException('Days must be between 1 and 365, got: '.$days);
        }

        return $days;
    }

    /**
     * Get expiring licenses.
     */
    protected function getExpiringLicenses(Carbon $expiryDate): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return License::with(['user', 'product', 'invoices'])
                ->where('status', 'active')
                ->where('license_expires_at', '<=', $expiryDate)
                ->where('license_expires_at', '>', Carbon::now())
                ->get()
                ->filter(function ($license) {
                    return ! $license->invoices()->where('type', 'renewal')->where('status', 'pending')->exists();
                });
        } catch (\Exception $e) {
            Log::error('Failed to get expiring licenses', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate renewal invoice for a license.
     */
    protected function generateRenewalInvoice(License $license): ?\App\Models\Invoice
    {
        try {
            $product = $license->product;
            if ($product === null) {
                Log::warning('No product found for license', ['licenseKey' => $license->license_key ?? 'unknown']);

                return null;
            }

            $renewalPrice = $product->renewal_price ?? $product->price ?? 0;
            if ($renewalPrice <= 0) {
                Log::warning('No renewal price set for product', [
                    'productId' => $product->id,
                    'productName' => $product->name,
                    'licenseKey' => $license->license_key ?? 'unknown',
                ]);
                $this->warn('No renewal price set for product '.$product->name.', skipping invoice generation.');

                return null;
            }

            if ($renewalPrice > 999999.99) {
                Log::error('Renewal price exceeds maximum allowed value', [
                    'productId' => $product->id,
                    'renewalPrice' => $renewalPrice,
                    'licenseKey' => $license->license_key ?? 'unknown',
                ]);
                throw new \InvalidArgumentException('Renewal price exceeds maximum allowed value: '.$renewalPrice);
            }

            $newExpiryDate = $this->calculateNewExpiryDate($license, $product);
            $description = htmlspecialchars('Renewal for '.$product->name.' - License '.$license->license_key, ENT_QUOTES, 'UTF-8');

            $invoice = $this->invoiceService->createRenewalInvoice($license, [
                'amount' => $renewalPrice,
                'description' => $description,
                'due_date' => Carbon::now()->addDays(30),
                'new_expiry_date' => $newExpiryDate,
            ]);

            return $invoice;
        } catch (\Exception $e) {
            Log::error('Failed to generate renewal invoice', [
                'licenseKey' => $license->license_key ?? 'unknown',
                'productId' => $license->product_id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate new expiry date based on renewal period.
     */
    protected function calculateNewExpiryDate(License $license, Product $product): Carbon
    {
        try {
            $currentExpiry = $license->license_expires_at ?? Carbon::now();
            $renewalPeriod = $product->renewal_period;

            $validPeriods = ['monthly', 'quarterly', 'semi-annual', 'annual', 'three-years', 'lifetime'];
            if ($renewalPeriod !== null && ! in_array($renewalPeriod, $validPeriods)) {
                Log::warning('Invalid renewal period, using default', [
                    'renewalPeriod' => $renewalPeriod,
                    'productId' => $product->id,
                    'licenseKey' => $license->license_key ?? 'unknown',
                ]);
                $renewalPeriod = null;
            }

            switch ($renewalPeriod) {
                case 'monthly':
                    return $currentExpiry->copy()->addMonth();
                case 'quarterly':
                    return $currentExpiry->copy()->addMonths(3);
                case 'semi-annual':
                    return $currentExpiry->copy()->addMonths(6);
                case 'annual':
                    return $currentExpiry->copy()->addYear();
                case 'three-years':
                    return $currentExpiry->copy()->addYears(3);
                case 'lifetime':
                    return $currentExpiry->copy()->addYears(100);
                default:
                    $durationDays = $product->duration_days ?? 365;
                    if ($durationDays < 1 || $durationDays > 36500) {
                        Log::warning('Invalid duration days, using default', [
                            'durationDays' => $durationDays,
                            'productId' => $product->id,
                        ]);
                        $durationDays = 365;
                    }

                    return $currentExpiry->copy()->addDays($durationDays);
            }
        } catch (\Exception $e) {
            Log::error('Failed to calculate new expiry date', [
                'licenseKey' => $license->license_key ?? 'unknown',
                'productId' => $product->id,
                'renewalPeriod' => $product->renewal_period ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send renewal notifications.
     */
    protected function sendRenewalNotifications(License $license, \App\Models\Invoice $invoice): bool
    {
        $notificationsSent = 0;
        $totalNotifications = 0;

        if ($license->user !== null) {
            $totalNotifications++;
            if ($this->sendCustomerNotification($license, $invoice)) {
                $notificationsSent++;
            }
        }

        $totalNotifications++;
        if ($this->sendAdminNotification($license, $invoice)) {
            $notificationsSent++;
        }

        if ($notificationsSent < $totalNotifications) {
            Log::warning('Some renewal notifications failed to send', [
                'licenseKey' => $license->license_key ?? 'unknown',
                'sent' => $notificationsSent,
                'total' => $totalNotifications,
            ]);
        }

        return $notificationsSent > 0;
    }

    /**
     * Send customer renewal notification.
     */
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

    /**
     * Send admin renewal notification.
     */
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

    /**
     * Prepare customer notification data.
     */
    protected function prepareCustomerData(License $license, \App\Models\Invoice $invoice): array
    {
        return [
            'license_key' => $this->sanitizeString($license->license_key ?? ''),
            'product_name' => $this->sanitizeString($license->product->name ?? ''),
            'expires_at' => $license->license_expires_at?->format('Y-m-d') ?? 'Unknown',
            'invoice_amount' => $invoice->amount,
            'invoice_due_date' => $invoice->due_date->format('Y-m-d'),
            'invoice_id' => $invoice->id,
        ];
    }

    /**
     * Prepare admin notification data.
     */
    protected function prepareAdminData(License $license, \App\Models\Invoice $invoice): array
    {
        $user = $license->user;

        return [
            'license_key' => $this->sanitizeString($license->license_key ?? ''),
            'product_name' => $this->sanitizeString($license->product->name ?? ''),
            'customer_name' => $this->sanitizeString($user?->name ?? 'Unknown User'),
            'customer_email' => $this->sanitizeString($user?->email ?? 'No email provided'),
            'expires_at' => $license->license_expires_at?->format('Y-m-d') ?? 'Unknown',
            'invoice_amount' => $invoice->amount ?? 0,
            'invoice_id' => $invoice->id ?? 'Unknown',
        ];
    }

    /**
     * Sanitize string to prevent XSS.
     */
    protected function sanitizeString(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Log notification error.
     */
    protected function logNotificationError(string $type, License $license, \Exception $e): void
    {
        Log::error("Failed to send {$type} renewal notification", [
            'licenseKey' => $license->license_key ?? 'unknown',
            'userId' => $license->user?->id ?? 'unknown',
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * Handle license processing error.
     */
    protected function handleLicenseError(License $license, \Exception $e): void
    {
        Log::error('Failed to generate renewal invoice', [
            'licenseKey' => $license->license_key ?? 'unknown',
            'error' => $e->getMessage(),
        ]);

        $this->error('Failed to generate renewal invoice for license '.$license->license_key.': '.$e->getMessage());
    }
}
