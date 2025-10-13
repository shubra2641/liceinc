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
            // Validate and sanitize input.
            $daysBeforeExpiry = $this->validateAndSanitizeDaysOption();
            $expiryDate       = Carbon::now()->addDays($daysBeforeExpiry);
            $this->info('Generating renewal invoices for licenses expiring within ' . $daysBeforeExpiry . ' days...');
            // Find licenses that are about to expire and don't have pending renewal invoices.
            $licenses       = $this->getExpiringLicenses($expiryDate);
            $generatedCount = 0;
            $emailSentCount = 0;
            $errorCount     = 0;
            /*
             * @var License $license
             */
            foreach ($licenses as $license) {
                try {
                    DB::beginTransaction();
                    // Generate renewal invoice.
                    $invoice = $this->generateRenewalInvoice($license);
                    if ($invoice !== null) {
                        $generatedCount++;
                        $this->line(
                            'Generated renewal invoice for license ' .
                            $license->license_key . ' (Product: ' .
                            ($license->product->name ?? 'Unknown Product') . ')'
                        );
                        // Send email notifications.
                        if ($this->sendRenewalNotifications($license, $invoice) === true) {
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
                }//end try
            }//end foreach

            $this->info(
                'Generated ' . $generatedCount . ' renewal invoices and sent ' .
                $emailSentCount . ' email notifications.'
            );
            if ($errorCount > 0) {
                $this->warn('Encountered ' . $errorCount . ' errors during processing. Check logs for details.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error(
                'GenerateRenewalInvoices command failed',
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
            $this->error('Command failed: ' . $e->getMessage());
            return Command::FAILURE;
        }//end try
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
            $product = $license->product;
            if ($product === null) {
                Log::warning(
                    'No product found for license',
                    [
                        'licenseKey' => ($license->license_key ?? 'unknown'),
                    ]
                );
                return null;
            }

            /*
             * @var Product $product
             */
            // Calculate renewal price based on product settings.
            $renewalPrice = ($product->renewal_price ?? $product->price ?? 0);
            if ($renewalPrice <= 0) {
                Log::warning(
                    'No renewal price set for product',
                    [
                        'productId'   => $product->id,
                        'productName' => $product->name,
                        'licenseKey'  => ($license->license_key ?? 'unknown'),
                    ]
                );
                $this->warn(
                    'No renewal price set for product ' . $product->name . ', skipping invoice generation.'
                );
                return null;
            }

            // Validate renewal price.
            if ($renewalPrice > 999999.99) {
                Log::error(
                    'Renewal price exceeds maximum allowed value',
                    [
                        'productId'    => $product->id,
                        'renewalPrice' => $renewalPrice,
                        'licenseKey'   => ($license->license_key ?? 'unknown'),
                    ]
                );
                throw new \InvalidArgumentException(
                    'Renewal price exceeds maximum allowed value: ' . $renewalPrice
                );
            }

            // Calculate new expiry date based on renewal period.
            $newExpiryDate = $this->calculateNewExpiryDate($license, $product);
            // Sanitize description to prevent XSS.
            $description = htmlspecialchars(
                'Renewal for ' . $product->name . ' - License ' . $license->license_key,
                ENT_QUOTES,
                'UTF-8',
            );
            // Create renewal invoice.
            $invoice = $this->invoiceService->createRenewalInvoice(
                $license,
                [
                    'amount'          => $renewalPrice,
                    'description'     => $description,
                    'due_date'        => Carbon::now()->addDays(30),
                // 30 days to pay
                    'new_expiry_date' => $newExpiryDate,
                ]
            );
            return $invoice;
        } catch (\Exception $e) {
            Log::error(
                'Failed to generate renewal invoice',
                [
                    'licenseKey' => ($license->license_key ?? 'unknown'),
                    'productId'  => ($license->product_id ?? 'unknown'),
                    'error'      => $e->getMessage(),
                    'trace'      => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }//end try
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
            $currentExpiry = ($license->license_expires_at ?? Carbon::now());
            $renewalPeriod = $product->renewal_period;
            // Current expiry is already a Carbon instance.
            // Validate renewal period.
            $validPeriods = [
                'monthly',
                'quarterly',
                'semi-annual',
                'annual',
                'three-years',
                'lifetime',
            ];
            if ($renewalPeriod !== null && in_array($renewalPeriod, $validPeriods) === false) {
                Log::warning(
                    'Invalid renewal period, using default',
                    [
                        'renewalPeriod' => $renewalPeriod,
                        'productId'     => $product->id,
                        'licenseKey'    => ($license->license_key ?? 'unknown'),
                    ]
                );
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
                    // Default to product duration_days.
                    $durationDays = ($product->duration_days ?? 365);
                    // Validate duration days.
                    if ($durationDays < 1 || $durationDays > 36500) {
                        // Max 100 years.
                        Log::warning(
                            'Invalid duration days, using default',
                            [
                                'durationDays' => $durationDays,
                                'productId'    => $product->id,
                            ]
                        );
                        $durationDays = 365;
                    }
                    return $currentExpiry->copy()->addDays($durationDays);
            }//end switch
        } catch (\Exception $e) {
            Log::error(
                'Failed to calculate new expiry date',
                [
                    'licenseKey'    => ($license->license_key ?? 'unknown'),
                    'productId'     => $product->id,
                    'renewalPeriod' => ($product->renewal_period ?? 'unknown'),
                    'error'         => $e->getMessage(),
                    'trace'         => $e->getTraceAsString(),
                ]
            );
            throw $e;
        }//end try
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

        // Log notification results
        if ($notificationsSent < $totalNotifications) {
            Log::warning(
                'Some renewal notifications failed to send',
                [
                    'licenseKey' => $license->license_key ?? 'unknown',
                    'sent' => $notificationsSent,
                    'total' => $totalNotifications,
                ]
            );
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
