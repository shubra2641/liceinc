<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Email\EmailFacade;
use App\Services\Email\Contracts\EmailServiceInterface;

/**
 * Email Service - Legacy Compatibility Layer.
 *
 * This class provides backward compatibility with the old EmailService
 * while using the new modular email system underneath.
 *
 * @version 1.0.0
 * @deprecated Use \App\Services\Email\EmailFacade or \App\Services\Email\Facades\Email instead
 */
class EmailService
{
    protected EmailFacade $emailFacade;

    public function __construct(EmailFacade $emailFacade)
    {
        $this->emailFacade = $emailFacade;
    }

    /**
     * Send email using template name and data.
     *
     * @param array<string, mixed> $data
     */
    public function sendEmail(
        string $templateName,
        string $recipientEmail,
        array $data = [],
        ?string $recipientName = null
    ): bool {
        return $this->emailFacade->sendEmail($templateName, $recipientEmail, $data, $recipientName);
    }

    /**
     * Send email to user using template.
     *
     * @param array<string, mixed> $data
     */
    public function sendToUser($user, string $templateName, array $data = []): bool
    {
        return $this->emailFacade->sendToUser($user, $templateName, $data);
    }

    /**
     * Send email to admin using template.
     *
     * @param array<string, mixed> $data
     */
    public function sendToAdmin(string $templateName, array $data = []): bool
    {
        return $this->emailFacade->sendToAdmin($templateName, $data);
    }

    /**
     * Send bulk emails to multiple users.
     *
     * @param array<string, mixed> $users
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function sendBulkEmail(array $users, string $templateName, array $data = []): array
    {
        return $this->emailFacade->sendBulkEmail($users, $templateName, $data);
    }

    /**
     * Get available templates by type and category.
     */
    public function getTemplates(string $type, ?string $category = null)
    {
        return $this->emailFacade->getTemplates($type, $category);
    }

    /**
     * Test email template rendering.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function testTemplate(string $templateName, array $data = []): array
    {
        return $this->emailFacade->testTemplate($templateName, $data);
    }

    // User email methods
    public function sendUserWelcome($user): bool
    {
        return $this->emailFacade->sendUserWelcome($user);
    }

    public function sendWelcome($user, array $data = []): bool
    {
        return $this->emailFacade->sendToUser($user, 'user_welcome', $data);
    }

    public function sendEmailVerification($user, string $verificationUrl): bool
    {
        return $this->emailFacade->sendEmailVerification($user, $verificationUrl);
    }

    public function sendNewUserNotification($user): bool
    {
        return $this->emailFacade->sendNewUserNotification($user);
    }

    public function sendPasswordReset($user, string $resetUrl): bool
    {
        return $this->emailFacade->sendPasswordReset($user, $resetUrl);
    }

    // License email methods
    public function sendPaymentConfirmation($license, $invoice): bool
    {
        return $this->emailFacade->sendPaymentConfirmation($license, $invoice);
    }

    public function sendLicenseExpiring($user, array $licenseData): bool
    {
        return $this->emailFacade->sendLicenseExpiring($user, $licenseData);
    }

    public function sendLicenseUpdated($user, array $licenseData): bool
    {
        return $this->emailFacade->sendLicenseUpdated($user, $licenseData);
    }

    public function sendLicenseCreated($license, $user = null): bool
    {
        return $this->emailFacade->sendLicenseCreated($license, $user);
    }

    public function sendAdminPaymentNotification($license, $invoice): bool
    {
        return $this->emailFacade->sendAdminPaymentNotification($license, $invoice);
    }

    // Invoice email methods
    public function sendInvoiceApproachingDue($user, array $invoiceData): bool
    {
        return $this->emailFacade->sendInvoiceApproachingDue($user, $invoiceData);
    }

    public function sendInvoicePaid($user, array $invoiceData): bool
    {
        return $this->emailFacade->sendInvoicePaid($user, $invoiceData);
    }

    public function sendInvoiceCancelled($user, array $invoiceData): bool
    {
        return $this->emailFacade->sendInvoiceCancelled($user, $invoiceData);
    }

    public function sendCustomInvoicePaymentConfirmation($invoice): bool
    {
        return $this->emailFacade->sendCustomInvoicePaymentConfirmation($invoice);
    }

    public function sendAdminCustomInvoicePaymentNotification($invoice): bool
    {
        return $this->emailFacade->sendAdminCustomInvoicePaymentNotification($invoice);
    }

    public function sendPaymentFailureNotification($order): bool
    {
        return $this->emailFacade->sendPaymentFailureNotification($order);
    }

    // Ticket email methods
    public function sendTicketCreated($user, array $ticketData): bool
    {
        return $this->emailFacade->sendTicketCreated($user, $ticketData);
    }

    public function sendTicketStatusUpdate($user, array $ticketData): bool
    {
        return $this->emailFacade->sendTicketStatusUpdate($user, $ticketData);
    }

    public function sendTicketReply($user, array $ticketData): bool
    {
        return $this->emailFacade->sendTicketReply($user, $ticketData);
    }

    public function sendAdminTicketCreated(array $ticketData): bool
    {
        return $this->emailFacade->sendAdminTicketCreated($ticketData);
    }

    public function sendAdminTicketReply(array $ticketData): bool
    {
        return $this->emailFacade->sendAdminTicketReply($ticketData);
    }

    public function sendAdminTicketClosed(array $ticketData): bool
    {
        return $this->emailFacade->sendAdminTicketClosed($ticketData);
    }

    // Additional methods for backward compatibility
    public function createOrUpdateTemplate(array $templateData)
    {
        // This method would need to be implemented based on your EmailTemplate model
        // For now, we'll throw an exception to indicate it needs implementation
        throw new \BadMethodCallException('createOrUpdateTemplate method needs to be implemented');
    }

    // Admin notification methods
    public function sendAdminLicenseCreated(array $licenseData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_license_created', $licenseData);
    }

    public function sendAdminLicenseExpiring(array $licenseData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_license_expiring', $licenseData);
    }

    public function sendAdminLicenseRenewed(array $licenseData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_license_renewed', $licenseData);
    }

    public function sendRenewalReminder($user, array $renewalData): bool
    {
        return $this->emailFacade->sendToUser($user, 'user_renewal_reminder', $renewalData);
    }

    public function sendAdminRenewalReminder(array $renewalData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_renewal_reminder', $renewalData);
    }

    public function sendProductVersionUpdate($user, array $productData): bool
    {
        return $this->emailFacade->sendToUser($user, 'user_product_version_update', $productData);
    }

    public function sendAdminInvoiceApproachingDue(array $invoiceData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_invoice_approaching_due', $invoiceData);
    }

    public function sendAdminInvoiceCancelled(array $invoiceData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_invoice_cancelled', $invoiceData);
    }
}