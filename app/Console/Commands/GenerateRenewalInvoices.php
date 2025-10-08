<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\License;
use App\Models\Product;
use App\Services\EmailService;
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
    protected $signature = 'licenses:generate-renewal-invoices '
        . '{--days=7 : Number of days before expiry to generate invoices}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate renewal invoices for licenses that are about to expire with enhanced security';
    /**
     * The invoice service instance.
     */
    protected InvoiceService $invoiceService;
    /**
     * The email service instance.
     */
    protected EmailService $emailService;
    /**
     * Create a new command instance with enhanced security.
     *
     * @param  InvoiceService  $invoiceService  The invoice service instance
     * @param  EmailService  $emailService  The email service instance
     */
    public function __construct(InvoiceService $invoiceService, EmailService $emailService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
        $this->emailService = $emailService;
    }
    /**
     * Execute the console command with enhanced security and database transactions.
     *
     * @return int Command exit code
     *
     * @throws \InvalidArgumentException When invalid options are provided
     * @throws \Exception When command execution fails
     */
    public function handle(): int
    {
        try {
            // Validate and sanitize input
            $daysBeforeExpiry = $this->validateAndSanitizeDaysOption();
            $expiryDate = Carbon::now()->addDays($daysBeforeExpiry);
            $this->info("Generating renewal invoices for licenses expiring within {$daysBeforeExpiry} days...");
            // Find licenses that are about to expire and don't have pending renewal invoices
            $licenses = $this->getExpiringLicenses($expiryDate);
            $generatedCount = 0;
            $emailSentCount = 0;
            $errorCount = 0;
            /**
 * @var License $license
*/
            foreach ($licenses as $license) {
                try {
                    DB::beginTransaction();
                    // Generate renewal invoice
                    $invoice = $this->generateRenewalInvoice($license);
                    if ($invoice) {
                        $generatedCount++;
                        $this->line("Generated renewal invoice for license {$license->licenseKey} "
                            . "(Product: " . ($license->product->name ?? 'Unknown Product') . ")");
                        // Send email notifications
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
                    Log::error('Failed to generate renewal invoice', [
                        'licenseKey' => $license->licenseKey ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->error("Failed to generate renewal invoice for license {$license->licenseKey}: "
                        . $e->getMessage());
                }
            }
            $this->info("Generated {$generatedCount} renewal invoices and sent {$emailSentCount} "
                . 'email notifications.');
            if ($errorCount > 0) {
                $this->warn("Encountered {$errorCount} errors during processing. Check logs for details.");
            }
            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error('GenerateRenewalInvoices command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Command failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    /**
     * Validate and sanitize the days option with enhanced security.
     *
     * @return int The validated days value
     *
     * @throws \InvalidArgumentException When invalid days value is provided
     */
    protected function validateAndSanitizeDaysOption(): int
    {
        $days = (int)$this->option('days');
        // Validate days range (1-365 days)
        if ($days < 1 || $days > 365) {
            throw new \InvalidArgumentException("Days must be between 1 and 365, got: " . SecurityHelper::escapeVariable((string)$days));
        }
        return $days;
    }
    /**
     * Get expiring licenses with enhanced security and validation.
     *
     * @param  Carbon  $expiryDate  The expiry date to check against
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> The collection of expiring licenses
     *
     * @throws \Exception When database query fails
     */
    protected function getExpiringLicenses(Carbon $expiryDate): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return License::with(['user', 'product', 'invoices'])
                ->where('status', 'active')
                ->where('licenseExpiresAt', '<=', $expiryDate)
                ->where('licenseExpiresAt', '>', Carbon::now())
                ->get()
                ->filter(function ($license) {
                    // Check if there's already a pending renewal invoice
                    return ! $license->invoices()
                        ->where('type', 'renewal')
                        ->where('status', 'pending')
                        ->exists();
                });
        } catch (\Exception $e) {
            Log::error('Failed to get expiring licenses', [
                'error' => $e->getMessage(),
                'expiry_date' => $expiryDate->toISOString(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Generate renewal invoice for a license with enhanced security.
     *
     * @param  License  $license  The license to generate invoice for
     *
     * @return \App\Models\Invoice|null The generated invoice or null if failed
     *
     * @throws \Exception When invoice generation fails
     */
    protected function generateRenewalInvoice(License $license): ?\App\Models\Invoice
    {
        try {
            $product = $license->product;
            if (! $product) {
                Log::warning('No product found for license', [
                    'licenseKey' => $license->licenseKey ?? 'unknown',
                ]);
                return null;
            }

            /**
 * @var Product $product
*/
            // Calculate renewal price based on product settings
            $renewalPrice = $product->renewalPrice ?? $product->price ?? 0;
            if ($renewalPrice <= 0) {
                Log::warning('No renewal price set for product', [
                    'productId' => $product->id,
                    'product_name' => $product->name,
                    'licenseKey' => $license->licenseKey ?? 'unknown',
                ]);
                $this->warn("No renewal price set for product {$product->name}, "
                    . 'skipping invoice generation.');
                return null;
            }
            // Validate renewal price
            if ($renewalPrice > 999999.99) {
                Log::error('Renewal price exceeds maximum allowed value', [
                    'productId' => $product->id,
                    'renewalPrice' => $renewalPrice,
                    'licenseKey' => $license->licenseKey ?? 'unknown',
                ]);
                throw new \InvalidArgumentException('Renewal price exceeds maximum allowed value: '
                    . $renewalPrice);
            }
            // Calculate new expiry date based on renewal period
            $newExpiryDate = $this->calculateNewExpiryDate($license, $product);
            // Sanitize description to prevent XSS
            $description = htmlspecialchars(
                "Renewal for {$product->name} - License {$license->licenseKey}",
                ENT_QUOTES,
                'UTF-8',
            );
            // Create renewal invoice
            $invoice = $this->invoiceService->createRenewalInvoice($license, [
                'amount' => $renewalPrice,
                'description' => $description,
                'due_date' => Carbon::now()->addDays(30), // 30 days to pay
                'new_expiry_date' => $newExpiryDate,
            ]);
            return $invoice;
        } catch (\Exception $e) {
            Log::error('Failed to generate renewal invoice', [
                'licenseKey' => $license->licenseKey ?? 'unknown',
                'productId' => $license->productId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Calculate new expiry date based on renewal period with enhanced validation.
     *
     * @param  License  $license  The license to calculate expiry for
     * @param  Product  $product  The product with renewal period settings
     *
     * @return Carbon The calculated new expiry date
     *
     * @throws \InvalidArgumentException When invalid renewal period is provided
     * @throws \Exception When date calculation fails
     */
    protected function calculateNewExpiryDate(License $license, Product $product): Carbon
    {
        try {
            $currentExpiry = $license->licenseExpiresAt ?? Carbon::now();
            $renewalPeriod = $product->renewalPeriod;
            // Current expiry is already a Carbon instance
            // Validate renewal period
            $validPeriods = [
                'monthly',
                'quarterly',
                'semi-annual',
                'annual',
                'three-years',
                'lifetime',
            ];
            if ($renewalPeriod && ! in_array($renewalPeriod, $validPeriods)) {
                Log::warning('Invalid renewal period, using default', [
                    'renewalPeriod' => $renewalPeriod,
                    'productId' => $product->id,
                    'licenseKey' => $license->licenseKey ?? 'unknown',
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
                    // Default to product durationDays
                    $durationDays = $product->durationDays ?? 365;
                    // Validate duration days
                    if ($durationDays < 1 || $durationDays > 36500) { // Max 100 years
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
                'licenseKey' => $license->licenseKey ?? 'unknown',
                'productId' => $product->id,
                'renewalPeriod' => $product->renewalPeriod ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Send renewal notifications with enhanced security and validation.
     *
     * @param  License  $license  The license to send notifications for
     * @param  \App\Models\Invoice  $invoice  The generated invoice
     *
     * @return bool True if notifications were sent successfully, false otherwise
     *
     * @throws \Exception When notification sending fails
     */
    protected function sendRenewalNotifications(License $license, \App\Models\Invoice $invoice): bool
    {
        try {
            $notificationsSent = 0;
            $totalNotifications = 0;
            // Send email to customer
            if ($license->user) {
                $totalNotifications++;
                try {
                    // Sanitize data to prevent XSS
                    $customerData = [
                        'licenseKey' => htmlspecialchars(
                            $license->licenseKey ?? '',
                            ENT_QUOTES,
                            'UTF-8',
                        ),
                        'product_name' => htmlspecialchars(
                            $license->product->name ?? '',
                            ENT_QUOTES,
                            'UTF-8',
                        ),
                        'expiresAt' => $license->licenseExpiresAt
                            ? $license->licenseExpiresAt->format('Y-m-d')
                            : 'Unknown',
                        'invoice_amount' => $invoice->amount,
                        'invoice_due_date' => $invoice->due_date->format('Y-m-d'),
                        'invoice_id' => $invoice->id,
                    ];
                    /**
 * @var \App\Models\User $user
*/
                    $user = $license->user;
                    $this->emailService->sendRenewalReminder($user, $customerData);
                    $notificationsSent++;
                } catch (\Exception $e) {
                    Log::error('Failed to send customer renewal notification', [
                        'licenseKey' => $license->licenseKey ?? 'unknown',
                        'userId' => $license->user->id ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
            // Send email to admin
            $totalNotifications++;
            try {
                // Sanitize data to prevent XSS
                /**
 * @var \App\Models\User|null $user
*/
                $user = $license->user;
                $adminData = [
                    'licenseKey' => htmlspecialchars(
                        $license->licenseKey ?? '',
                        ENT_QUOTES,
                        'UTF-8',
                    ),
                    'product_name' => htmlspecialchars(
                        $license->product->name ?? '',
                        ENT_QUOTES,
                        'UTF-8',
                    ),
                    'customer_name' => htmlspecialchars(
                        $user ? $user->name : 'Unknown User',
                        ENT_QUOTES,
                        'UTF-8',
                    ),
                    'customer_email' => htmlspecialchars(
                        $user ? $user->email : 'No email provided',
                        ENT_QUOTES,
                        'UTF-8',
                    ),
                    'expiresAt' => $license->licenseExpiresAt
                        ? $license->licenseExpiresAt->format('Y-m-d')
                        : 'Unknown',
                    'invoice_amount' => $invoice->amount ?? 0,
                    'invoice_id' => $invoice->id ?? 'Unknown',
                ];
                $this->emailService->sendAdminRenewalReminder($adminData);
                $notificationsSent++;
            } catch (\Exception $e) {
                Log::error('Failed to send admin renewal notification', [
                    'licenseKey' => $license->licenseKey ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            // Log notification results
            if ($notificationsSent < $totalNotifications) {
                Log::warning('Some renewal notifications failed to send', [
                    'licenseKey' => $license->licenseKey ?? 'unknown',
                    'sent' => $notificationsSent,
                    'total' => $totalNotifications,
                ]);
            }
            return $notificationsSent > 0;
        } catch (\Exception $e) {
            Log::error('Failed to send renewal notifications', [
                'licenseKey' => $license->licenseKey ?? 'unknown',
                'invoice_id' => $invoice->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error("Failed to send renewal notifications for license {$license->licenseKey}: " . $e->getMessage());
            return false;
        }
    }
}
