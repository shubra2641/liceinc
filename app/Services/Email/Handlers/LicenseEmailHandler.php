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

        $data = $this->buildPaymentConfirmationData($license, $invoice);
        return $this->emailService->sendToUser($license->user, 'payment_confirmation', $data);
    }

    /**
     * Build payment confirmation email data.
     */
    private function buildPaymentConfirmationData(License $license, Invoice $invoice): array
    {
        return [
            'customer_name' => $this->sanitizeString($license->user->name),
            'customer_email' => $this->sanitizeString($license->user->email),
            'product_name' => $this->sanitizeString($license->product->name ?? ''),
            'order_number' => $this->sanitizeString($invoice->invoice_number),
            'license_key' => $this->sanitizeString($license->license_key),
            'invoice_number' => $this->sanitizeString($invoice->invoice_number),
            'amount' => $invoice->amount,
            'currency' => $this->sanitizeString($invoice->currency),
            'payment_method' => $this->getPaymentMethod($invoice),
            'payment_date' => $this->formatPaymentDate($invoice),
            'license_expires_at' => $this->formatLicenseExpiry($license),
        ];
    }

    /**
     * Get formatted payment method.
     */
    private function getPaymentMethod(Invoice $invoice): string
    {
        $gateway = $invoice->metadata['gateway'] ?? null;
        $method = is_string($gateway) ? $gateway : 'Unknown';
        return $this->sanitizeString(ucfirst($method));
    }

    /**
     * Format payment date.
     */
    private function formatPaymentDate(Invoice $invoice): string
    {
        return $invoice->paid_at?->format('M d, Y \a\t g:i A') ?? 'Unknown';
    }

    /**
     * Format license expiry date.
     */
    private function formatLicenseExpiry(License $license): string
    {
        return $license->license_expires_at?->format('M d, Y') ?? 'Never';
    }

    /**
     * Send license expiration warning to user.
     *
     * @param array<string, mixed> $licenseData
     */
    public function sendLicenseExpiring(User $user, array $licenseData): bool
    {
        $sanitizedData = $this->sanitizeLicenseData($licenseData);
        return $this->emailService->sendToUser($user, 'user_license_expiring', $sanitizedData);
    }

    /**
     * Sanitize license data for email.
     *
     * @param array<string, mixed> $licenseData
     *
     * @return array<string, mixed>
     */
    private function sanitizeLicenseData(array $licenseData): array
    {
        $sanitized = $licenseData;
        $sanitized['license_key'] = $this->sanitizeString($licenseData['license_key'] ?? '');
        $sanitized['product_name'] = $this->sanitizeString($licenseData['product_name'] ?? '');
        $sanitized['expires_at'] = $this->sanitizeString($licenseData['expires_at'] ?? '');
        $sanitized['days_remaining'] = $this->sanitizeDaysRemaining($licenseData['days_remaining'] ?? 0);
        
        return $sanitized;
    }

    /**
     * Sanitize days remaining value.
     */
    private function sanitizeDaysRemaining(mixed $daysRemaining): int
    {
        return is_numeric($daysRemaining) ? (int)$daysRemaining : 0;
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

        $data = $this->buildLicenseCreatedData($license);
        return $this->emailService->sendToUser($targetUser, 'license_created', $data);
    }

    /**
     * Build license created email data.
     */
    private function buildLicenseCreatedData(License $license): array
    {
        return [
            'customer_name' => $license->user->name ?? '',
            'customer_email' => $license->user->email ?? '',
            'product_name' => $license->product->name ?? '',
            'license_key' => $license->license_key,
            'license_type' => $this->formatLicenseType($license),
            'max_domains' => $license->max_domains,
            'license_expires_at' => $this->formatLicenseExpiry($license),
            'support_expires_at' => $this->formatSupportExpiry($license),
            'created_date' => $this->formatCreatedDate($license),
        ];
    }

    /**
     * Format license type.
     */
    private function formatLicenseType(License $license): string
    {
        return ucfirst((string)($license->license_type ?? 'Unknown'));
    }

    /**
     * Format support expiry date.
     */
    private function formatSupportExpiry(License $license): string
    {
        return $license->support_expires_at?->format('M d, Y') ?? 'Never';
    }

    /**
     * Format created date.
     */
    private function formatCreatedDate(License $license): string
    {
        return $license->created_at?->format('M d, Y \a\t g:i A') ?? 'Unknown';
    }

    /**
     * Send admin notification about payment and license creation.
     */
    public function sendAdminPaymentNotification(License $license, Invoice $invoice): bool
    {
        $data = $this->buildAdminPaymentData($license, $invoice);
        return $this->emailService->sendToAdmin('admin_payment_license_created', $data);
    }

    /**
     * Build admin payment notification data.
     */
    private function buildAdminPaymentData(License $license, Invoice $invoice): array
    {
        return [
            'customer_name' => $license->user->name ?? '',
            'customer_email' => $license->user->email ?? '',
            'product_name' => $license->product->name ?? '',
            'license_key' => $license->license_key,
            'invoice_number' => $invoice->invoice_number,
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'payment_method' => $this->getPaymentMethod($invoice),
            'transaction_id' => $this->getTransactionId($invoice),
            'payment_date' => $this->formatPaymentDate($invoice),
            'license_type' => $this->formatLicenseType($license),
            'max_domains' => $license->max_domains,
        ];
    }

    /**
     * Get transaction ID from invoice.
     */
    private function getTransactionId(Invoice $invoice): string
    {
        return $invoice->metadata['transaction_id'] ?? 'N/A';
    }
}
