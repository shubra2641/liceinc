<?php

declare(strict_types=1);

/*
 * Generate Renewal Invoices Command with Enhanced Security.
 *
 * This console command generates renewal invoices for licenses that are about to expire.
 * It provides comprehensive invoice generation functionality with enhanced security measures.
 *
 * @package App\Console\Commands
 * @author  My Logos Team
 * @since   1.0.0
 */

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
 * Generate Renewal Invoices Command with Enhanced Security.
 *
 * This console command generates renewal invoices for licenses that are about to expire.
 * It provides comprehensive invoice generation functionality with enhanced security measures.
 *
 * Features:
 * - Automated renewal invoice generation for expiring licenses
 * - Email notifications for customers and administrators
 * - Database transaction support for data integrity
 * - Enhanced security measures and input validation
 * - Comprehensive error handling and logging
 * - Configurable expiry period for invoice generation
 * - Duplicate invoice prevention
 */
class GenerateRenewalInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licenses:generate-renewal-invoices ' .
        '{--days=7 : Number of days before expiry to generate invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate renewal invoices for licenses that are about to expire with enhanced security';

    /**
     * The invoice service instance.
     *
     * @var InvoiceService
     */
    protected InvoiceService $invoiceService;

    /**
     * The email service instance.
     *
     * @var EmailFacade
     */
    protected EmailFacade $emailService;


    /**
     * Create a new command instance with enhanced security.
     *
     * @param InvoiceService $invoiceService The invoice service instance.
     * @param EmailFacade    $emailService   The email service instance.
     */
    public function __construct(InvoiceService $invoiceService, EmailFacade $emailService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
        $this->emailService   = $emailService;
    }


    /**
     * Execute the console command with enhanced security and database transactions.
     *
     * @return integer Command exit code
     *
     * @throws \InvalidArgumentException When invalid options are provided.
     * @throws \Exception When command execution fails.
     */
    public function handle(): int
    {
        try {
            $daysBeforeExpiry = $this->validateAndSanitizeDaysOption();
            $expiryDate = $this->calculateExpiryDate($daysBeforeExpiry);

            $this->displayStartMessage($daysBeforeExpiry);

            $licenses = $this->getExpiringLicenses($expiryDate);
            $results = $this->processLicenses($licenses);

            $this->displayResults($results);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->handleCommandError($e);
            return Command::FAILURE;
        }
    }


    /**
     * Calculate expiry date based on days.
     */
    protected function calculateExpiryDate(int $days): Carbon
    {
        return Carbon::now()->addDays($days);
    }

    /**
     * Display start message.
     */
    protected function displayStartMessage(int $days): void
    {
        $this->info('Generating renewal invoices for licenses expiring within ' . $days . ' days...');
    }

    /**
     * Process all licenses and return results.
     */
    protected function processLicenses($licenses): array
    {
        $generatedCount = 0;
        $emailSentCount = 0;
        $errorCount = 0;

        foreach ($licenses as $license) {
            $result = $this->processSingleLicense($license);
            $generatedCount += $result['generated'];
            $emailSentCount += $result['emails'];
            $errorCount += $result['errors'];
        }

        return [
            'generated' => $generatedCount,
            'emails' => $emailSentCount,
            'errors' => $errorCount
        ];
    }

    /**
     * Process a single license.
     */
    protected function processSingleLicense(License $license): array
    {
        try {
            DB::beginTransaction();

            $invoice = $this->generateRenewalInvoice($license);
            if ($invoice === null) {
                DB::rollBack();
                return ['generated' => 0, 'emails' => 0, 'errors' => 1];
            }

            $this->displayLicenseSuccess($license);

            $emailsSent = $this->sendRenewalNotifications($license, $invoice) ? 1 : 0;

            DB::commit();

            return ['generated' => 1, 'emails' => $emailsSent, 'errors' => 0];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->handleLicenseError($license, $e);
            return ['generated' => 0, 'emails' => 0, 'errors' => 1];
        }
    }

    /**
     * Display license success message.
     */
    protected function displayLicenseSuccess(License $license): void
    {
        $this->line(
            'Generated renewal invoice for license ' .
            $license->license_key . ' (Product: ' .
            ($license->product->name ?? 'Unknown Product') . ')'
        );
    }

    /**
     * Display final results.
     */
    protected function displayResults(array $results): void
    {
        $this->info(
            'Generated ' . $results['generated'] . ' renewal invoices and sent ' .
            $results['emails'] . ' email notifications.'
        );

        if ($results['errors'] > 0) {
            $this->warn('Encountered ' . $results['errors'] . ' errors during processing. Check logs for details.');
        }
    }

    /**
     * Handle command error.
     */
    protected function handleCommandError(\Exception $e): void
    {
        Log::error(
            'GenerateRenewalInvoices command failed',
            [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]
        );
        $this->error('Command failed: ' . $e->getMessage());
    }

    /**
     * Validate and sanitize the days option with enhanced security.
     *
     * @return integer The validated days value
     *
     * @throws \InvalidArgumentException When invalid days value is provided.
     */
    protected function validateAndSanitizeDaysOption(): int
    {
        $days = (int) $this->option('days');
        // Validate days range (1-365 days).
        if ($days < 1 || $days > 365) {
            throw new \InvalidArgumentException(
                'Days must be between 1 and 365, got: ' . SecurityHelper::escapeVariable((string) $days)
            );
        }

        return $days;
    }


    /**
     * Get expiring licenses with enhanced security and validation.
     *
     * @param Carbon $expiryDate The expiry date to check against.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> The collection of expiring licenses
     *
     * @throws \Exception When database query fails.
     */
    protected function getExpiringLicenses(Carbon $expiryDate): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return License::with(['user', 'product', 'invoices'])
                ->where('status', 'active')
                ->where('license_expires_at', '<=', $expiryDate)
                ->where('license_expires_at', '>', Carbon::now())
                ->get()
                ->filter(
                    function ($license) {
                    // Check if there's already a pending renewal invoice.
                        return ! $license->invoices()->where('type', 'renewal')->where('status', 'pending')->exists();
                    }
                );
        } catch (\Exception $e) {
            Log::error(
                'Failed to get expiring licenses',
                [
                    'error'       => $e->getMessage(),
                    'expiry_date' => $expiryDate->toISOString(),
                    'trace'       => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }//end try
    }


    /**
     * Generate renewal invoice for a license with enhanced security.
     *
     * @param License $license The license to generate invoice for.
     *
     * @return \App\Models\Invoice|null The generated invoice or null if failed
     *
     * @throws \Exception When invoice generation fails.
     */
    protected function generateRenewalInvoice(License $license): ?\App\Models\Invoice
    {
        try {
            $product = $this->validateProduct($license);
            if ($product === null) {
                return null;
            }

            $renewalPrice = $this->calculateRenewalPrice($product, $license);
            if ($renewalPrice === null) {
                return null;
            }

            $this->validateRenewalPrice($renewalPrice, $product, $license);

            $newExpiryDate = $this->calculateNewExpiryDate($license, $product);
            $description = $this->createInvoiceDescription($product, $license);

            return $this->createInvoice($license, $renewalPrice, $description, $newExpiryDate);
        } catch (\Exception $e) {
            $this->logInvoiceError($license, $e);
            throw $e;
        }
    }

    /**
     * Validate product exists.
     */
    protected function validateProduct(License $license): ?\App\Models\Product
    {
        $product = $license->product;
        if ($product === null) {
            Log::warning(
                'No product found for license',
                ['licenseKey' => ($license->license_key ?? 'unknown')]
            );
        }
        return $product;
    }

    /**
     * Calculate renewal price.
     */
    protected function calculateRenewalPrice($product, License $license): ?float
    {
        $renewalPrice = ($product->renewal_price ?? $product->price ?? 0);

        if ($renewalPrice <= 0) {
            $this->logNoRenewalPrice($product, $license);
            $this->warn('No renewal price set for product ' . $product->name . ', skipping invoice generation.');
            return null;
        }

        return $renewalPrice;
    }

    /**
     * Log no renewal price warning.
     */
    protected function logNoRenewalPrice($product, License $license): void
    {
        Log::warning(
            'No renewal price set for product',
            [
                'productId' => $product->id,
                'productName' => $product->name,
                'licenseKey' => ($license->license_key ?? 'unknown'),
            ]
        );
    }

    /**
     * Validate renewal price.
     */
    protected function validateRenewalPrice(float $renewalPrice, $product, License $license): void
    {
        if ($renewalPrice > 999999.99) {
            Log::error(
                'Renewal price exceeds maximum allowed value',
                [
                    'productId' => $product->id,
                    'renewalPrice' => $renewalPrice,
                    'licenseKey' => ($license->license_key ?? 'unknown'),
                ]
            );
            throw new \InvalidArgumentException(
                'Renewal price exceeds maximum allowed value: ' . $renewalPrice
            );
        }
    }

    /**
     * Create invoice description.
     */
    protected function createInvoiceDescription($product, License $license): string
    {
        return htmlspecialchars(
            'Renewal for ' . $product->name . ' - License ' . $license->license_key,
            ENT_QUOTES,
            'UTF-8',
        );
    }

    /**
     * Create the invoice.
     */
    protected function createInvoice(License $license, float $renewalPrice, string $description, Carbon $newExpiryDate): \App\Models\Invoice
    {
        return $this->invoiceService->createRenewalInvoice(
            $license,
            [
                'amount' => $renewalPrice,
                'description' => $description,
                'due_date' => Carbon::now()->addDays(30),
                'new_expiry_date' => $newExpiryDate,
            ]
        );
    }

    /**
     * Log invoice error.
     */
    protected function logInvoiceError(License $license, \Exception $e): void
    {
        Log::error(
            'Failed to generate renewal invoice',
            [
                'licenseKey' => ($license->license_key ?? 'unknown'),
                'productId' => ($license->product_id ?? 'unknown'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]
        );
    }


    /**
     * Calculate new expiry date based on renewal period with enhanced validation.
     *
     * @param License $license The license to calculate expiry for.
     * @param Product $product The product with renewal period settings.
     *
     * @return Carbon The calculated new expiry date
     *
     * @throws \InvalidArgumentException When invalid renewal period is provided.
     * @throws \Exception When date calculation fails.
     */
    protected function calculateNewExpiryDate(License $license, Product $product): Carbon
    {
        try {
            $currentExpiry = $this->getCurrentExpiry($license);
            $renewalPeriod = $this->validateRenewalPeriod($product, $license);

            return $this->calculateExpiryByPeriod($currentExpiry, $renewalPeriod, $product);
        } catch (\Exception $e) {
            $this->logExpiryCalculationError($license, $product, $e);
            throw $e;
        }
    }

    /**
     * Get current expiry date.
     */
    protected function getCurrentExpiry(License $license): Carbon
    {
        return $license->license_expires_at ?? Carbon::now();
    }

    /**
     * Validate renewal period.
     */
    protected function validateRenewalPeriod($product, License $license): ?string
    {
        $renewalPeriod = $product->renewal_period;
        $validPeriods = [
            'monthly', 'quarterly', 'semi-annual',
            'annual', 'three-years', 'lifetime'
        ];

        if ($renewalPeriod !== null && !in_array($renewalPeriod, $validPeriods)) {
            $this->logInvalidRenewalPeriod($renewalPeriod, $product, $license);
            return null;
        }

        return $renewalPeriod;
    }

    /**
     * Log invalid renewal period.
     */
    protected function logInvalidRenewalPeriod($renewalPeriod, $product, License $license): void
    {
        Log::warning(
            'Invalid renewal period, using default',
            [
                'renewalPeriod' => $renewalPeriod,
                'productId' => $product->id,
                'licenseKey' => ($license->license_key ?? 'unknown'),
            ]
        );
    }

    /**
     * Calculate expiry by period.
     */
    protected function calculateExpiryByPeriod(Carbon $currentExpiry, ?string $renewalPeriod, $product): Carbon
    {
        if ($renewalPeriod === null) {
            return $this->calculateDefaultExpiry($currentExpiry, $product);
        }

        return $this->calculatePeriodExpiry($currentExpiry, $renewalPeriod);
    }

    /**
     * Calculate expiry for specific period.
     */
    protected function calculatePeriodExpiry(Carbon $currentExpiry, string $period): Carbon
    {
        return match ($period) {
            'monthly' => $currentExpiry->copy()->addMonth(),
            'quarterly' => $currentExpiry->copy()->addMonths(3),
            'semi-annual' => $currentExpiry->copy()->addMonths(6),
            'annual' => $currentExpiry->copy()->addYear(),
            'three-years' => $currentExpiry->copy()->addYears(3),
            'lifetime' => $currentExpiry->copy()->addYears(100),
            default => $currentExpiry->copy()->addDays(365),
        };
    }

    /**
     * Calculate default expiry based on product duration.
     */
    protected function calculateDefaultExpiry(Carbon $currentExpiry, $product): Carbon
    {
        $durationDays = $this->validateDurationDays($product);
        return $currentExpiry->copy()->addDays($durationDays);
    }

    /**
     * Validate duration days.
     */
    protected function validateDurationDays($product): int
    {
        $durationDays = $product->duration_days ?? 365;

        if ($durationDays < 1 || $durationDays > 36500) {
            $this->logInvalidDurationDays($durationDays, $product);
            return 365;
        }

        return $durationDays;
    }

    /**
     * Log invalid duration days.
     */
    protected function logInvalidDurationDays(int $durationDays, $product): void
    {
        Log::warning(
            'Invalid duration days, using default',
            [
                'durationDays' => $durationDays,
                'productId' => $product->id,
            ]
        );
    }

    /**
     * Log expiry calculation error.
     */
    protected function logExpiryCalculationError(License $license, $product, \Exception $e): void
    {
        Log::error(
            'Failed to calculate new expiry date',
            [
                'licenseKey' => ($license->license_key ?? 'unknown'),
                'productId' => $product->id,
                'renewalPeriod' => ($product->renewal_period ?? 'unknown'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]
        );
    }


    /**
     * Send renewal notifications with enhanced security and validation.
     *
     * @param License             $license The license to send notifications for.
     * @param \App\Models\Invoice $invoice The generated invoice.
     *
     * @return boolean True if notifications were sent successfully, false otherwise
     *
     * @throws \Exception When notification sending fails.
     */
    protected function sendRenewalNotifications(License $license, \App\Models\Invoice $invoice): bool
    {
        $results = $this->sendAllNotifications($license, $invoice);
        $this->logNotificationResults($license, $results);

        return $results['sent'] > 0;
    }

    /**
     * Send all notifications.
     */
    protected function sendAllNotifications(License $license, \App\Models\Invoice $invoice): array
    {
        $notificationsSent = 0;
        $totalNotifications = 0;

        // Send email to customer
        if ($license->user !== null) {
            $totalNotifications++;
            if ($this->sendCustomerNotification($license, $invoice)) {
                $notificationsSent++;
            }
        }

        // Send email to admin
        $totalNotifications++;
        if ($this->sendAdminNotification($license, $invoice)) {
            $notificationsSent++;
        }

        return [
            'sent' => $notificationsSent,
            'total' => $totalNotifications
        ];
    }

    /**
     * Log notification results.
     */
    protected function logNotificationResults(License $license, array $results): void
    {
        if ($results['sent'] < $results['total']) {
            Log::warning(
                'Some renewal notifications failed to send',
                [
                    'licenseKey' => $license->license_key ?? 'unknown',
                    'sent' => $results['sent'],
                    'total' => $results['total'],
                ]
            );
        }
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
        Log::error(
            "Failed to send {$type} renewal notification",
            [
                'licenseKey' => $license->license_key ?? 'unknown',
                'userId' => $license->user?->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]
        );
    }

    /**
     * Handle license processing error.
     *
     * @param License     $license The license
     * @param \Exception $e       The exception
     *
     * @return void
     */
    protected function handleLicenseError(License $license, \Exception $e): void
    {
        Log::error(
            'Failed to generate renewal invoice',
            [
                'licenseKey' => $license->license_key ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]
        );

        $this->error(
            'Failed to generate renewal invoice for license ' .
            $license->license_key . ': ' . $e->getMessage()
        );
    }
}
