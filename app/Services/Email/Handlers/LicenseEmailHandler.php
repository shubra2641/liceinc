<?php

declare(strict_types=1);

namespace App\Services\Email\Handlers;

use App\Models\Invoice;
use App\Models\License;
use App\Models\User;
use App\Services\Email\Contracts\EmailServiceInterface;
use App\Services\Email\Traits\EmailLoggingTrait;
use App\Services\Email\Traits\EmailValidationTrait;

/**
 * License Email Handler.
 *
 * Handles license-related email operations with enhanced security.
 *
 * @version 1.0.0
 */
class LicenseEmailHandler
{
    use EmailValidationTrait;
    use EmailLoggingTrait;

    public function __construct(
        protected EmailServiceInterface $emailService
    ) {
    }

    /**
     * Send payment confirmation email.
     */
    public function sendPaymentConfirmation(License $license, Invoice $invoice): bool
    {
        if (!$license->user) {
            $this->logInvalidUser('payment confirmation');
            return false;
        }

        return $this->emailService->sendToUser(
            $license->user,
            'payment_confirmation',
            [
                'customer_name' => $this->sanitizeString($license->user->name),
                'customer_email' => $this->sanitizeString($license->user->email),
                'product_name' => $this->sanitizeString($license->product->name ?? ''),
                'order_number' => $this->sanitizeString($invoice->invoice_number),
                'license_key' => $this->sanitizeString($license->license_key),
                'invoice_number' => $this->sanitizeString($invoice->invoice_number),
                'amount' => $invoice->amount,
                'currency' => $this->sanitizeString($invoice->currency),
                'payment_method' => $this->sanitizeString(
                    ucfirst(
                        is_string($invoice->metadata['gateway'] ?? null)
                            ? $invoice->metadata['gateway']
                            : 'Unknown'
                    )
                ),
                'payment_date' => $invoice->paid_at?->format('M d, Y \a\t g:i A') ?? 'Unknown',
                'license_expires_at' => $license->license_expires_at ?
                    $license->license_expires_at->format('M d, Y') : 'Never',
            ]
        );
    }

    /**
     * Send license expiration warning to user.
     *
     * @param array<string, mixed> $licenseData
     */
    public function sendLicenseExpiring(User $user, array $licenseData): bool
    {
        $licenseKey = $licenseData['license_key'] ?? '';
        $productName = $licenseData['product_name'] ?? '';
        $expiresAt = $licenseData['expires_at'] ?? '';
        $daysRemaining = $licenseData['days_remaining'] ?? 0;

        return $this->emailService->sendToUser($user, 'user_license_expiring', array_merge($licenseData, [
            'license_key' => is_string($licenseKey) ? $licenseKey : '',
            'product_name' => is_string($productName) ? $productName : '',
            'expires_at' => is_string($expiresAt) ? $expiresAt : '',
            'days_remaining' => is_numeric($daysRemaining) ? (int)$daysRemaining : 0,
        ]));
    }

    /**
     * Send license updated notification to user.
     *
     * @param array<string, mixed> $licenseData
     */
    public function sendLicenseUpdated(User $user, array $licenseData): bool
    {
        return $this->emailService->sendToUser($user, 'user_license_updated', array_merge($licenseData, [
            'license_key' => $licenseData['license_key'] ?? '',
            'product_name' => $licenseData['product_name'] ?? '',
            'update_type' => $licenseData['update_type'] ?? 'updated',
        ]));
    }

    /**
     * Send license creation notification to user.
     */
    public function sendLicenseCreated(License $license, ?User $user = null): bool
    {
        $targetUser = $user ?? $license->user;
        if (!$targetUser) {
            $this->logInvalidUser('license creation notification');
            return false;
        }

        return $this->emailService->sendToUser($targetUser, 'license_created', [
            'customer_name' => $license->user->name ?? '',
            'customer_email' => $license->user->email ?? '',
            'product_name' => $license->product->name ?? '',
            'license_key' => $license->license_key,
            'license_type' => ucfirst((string)($license->license_type ?? 'Unknown')),
            'max_domains' => $license->max_domains,
            'license_expires_at' => $license->license_expires_at ?
                $license->license_expires_at->format('M d, Y') : 'Never',
            'support_expires_at' => $license->support_expires_at ?
                $license->support_expires_at->format('M d, Y') : 'Never',
            'created_date' => $license->created_at?->format('M d, Y \a\t g:i A') ?? 'Unknown',
        ]);
    }

    /**
     * Send admin notification about payment and license creation.
     */
    public function sendAdminPaymentNotification(License $license, Invoice $invoice): bool
    {
        return $this->emailService->sendToAdmin('admin_payment_license_created', [
            'customer_name' => $license->user->name ?? '',
            'customer_email' => $license->user->email ?? '',
            'product_name' => $license->product->name ?? '',
            'license_key' => $license->license_key,
            'invoice_number' => $invoice->invoice_number,
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'payment_method' => ucfirst(
                is_string($invoice->metadata['gateway'] ?? null)
                    ? $invoice->metadata['gateway']
                    : 'Unknown'
            ),
            'transaction_id' => $invoice->metadata['transaction_id'] ?? 'N/A',
            'payment_date' => $invoice->paid_at?->format('M d, Y \a\t g:i A') ?? 'Unknown',
            'license_type' => ucfirst((string)($license->license_type ?? 'Unknown')),
            'max_domains' => $license->max_domains,
        ]);
    }
}
