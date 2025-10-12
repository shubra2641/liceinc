<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\License;
use App\Models\User;
use App\Services\Email\EmailFacade;
use App\Services\Email\Contracts\EmailServiceInterface;
use Illuminate\Database\Eloquent\Collection;

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
     * @param User $user
     * @param string $templateName
     * @param array<string, mixed> $data
     *
     * @return bool
     */
    public function sendToUser(User $user, string $templateName, array $data = []): bool
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
     *
     * @param string $type
     * @param string|null $category
     *
     * @return Collection<int, \App\Models\EmailTemplate>
     */
    public function getTemplates(string $type, ?string $category = null): Collection
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
    /**
     * Send user welcome email.
     *
     * @param User $user
     * @return bool
     */
    public function sendUserWelcome(User $user): bool
    {
        return $this->emailFacade->sendUserWelcome($user);
    }

    /**
     * Send welcome email.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return bool
     */
    public function sendWelcome(User $user, array $data = []): bool
    {
        return $this->emailFacade->sendToUser($user, 'user_welcome', $data);
    }

    /**
     * Send email verification.
     *
     * @param User $user
     * @param string $verificationUrl
     * @return bool
     */
    public function sendEmailVerification(User $user, string $verificationUrl): bool
    {
        return $this->emailFacade->sendEmailVerification($user, $verificationUrl);
    }

    /**
     * Send new user notification.
     *
     * @param User $user
     * @return bool
     */
    public function sendNewUserNotification(User $user): bool
    {
        return $this->emailFacade->sendNewUserNotification($user);
    }

    /**
     * Send password reset email.
     *
     * @param User $user
     * @param string $resetUrl
     * @return bool
     */
    public function sendPasswordReset(User $user, string $resetUrl): bool
    {
        return $this->emailFacade->sendPasswordReset($user, $resetUrl);
    }

    // License email methods
    /**
     * Send payment confirmation email.
     *
     * @param License $license
     * @param Invoice $invoice
     * @return bool
     */
    public function sendPaymentConfirmation(License $license, Invoice $invoice): bool
    {
        return $this->emailFacade->sendPaymentConfirmation($license, $invoice);
    }

    /**
     * Send license expiring email.
     *
     * @param User $user
     * @param array<string, mixed> $licenseData
     * @return bool
     */
    public function sendLicenseExpiring(User $user, array $licenseData): bool
    {
        return $this->emailFacade->sendLicenseExpiring($user, $licenseData);
    }

    /**
     * Send license updated email.
     *
     * @param User $user
     * @param array<string, mixed> $licenseData
     * @return bool
     */
    public function sendLicenseUpdated(User $user, array $licenseData): bool
    {
        return $this->emailFacade->sendLicenseUpdated($user, $licenseData);
    }

    /**
     * Send license created email.
     *
     * @param License $license
     * @param User|null $user
     * @return bool
     */
    public function sendLicenseCreated(License $license, ?User $user = null): bool
    {
        return $this->emailFacade->sendLicenseCreated($license, $user);
    }

    /**
     * Send admin payment notification.
     *
     * @param License $license
     * @param Invoice $invoice
     * @return bool
     */
    public function sendAdminPaymentNotification(License $license, Invoice $invoice): bool
    {
        return $this->emailFacade->sendAdminPaymentNotification($license, $invoice);
    }

    // Invoice email methods
    /**
     * Send invoice approaching due email.
     *
     * @param User $user
     * @param array<string, mixed> $invoiceData
     * @return bool
     */
    public function sendInvoiceApproachingDue(User $user, array $invoiceData): bool
    {
        return $this->emailFacade->sendInvoiceApproachingDue($user, $invoiceData);
    }

    /**
     * Send invoice paid email.
     *
     * @param User $user
     * @param array<string, mixed> $invoiceData
     * @return bool
     */
    public function sendInvoicePaid(User $user, array $invoiceData): bool
    {
        return $this->emailFacade->sendInvoicePaid($user, $invoiceData);
    }

    /**
     * Send invoice cancelled email.
     *
     * @param User $user
     * @param array<string, mixed> $invoiceData
     * @return bool
     */
    public function sendInvoiceCancelled(User $user, array $invoiceData): bool
    {
        return $this->emailFacade->sendInvoiceCancelled($user, $invoiceData);
    }

    /**
     * Send custom invoice payment confirmation.
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function sendCustomInvoicePaymentConfirmation(Invoice $invoice): bool
    {
        return $this->emailFacade->sendCustomInvoicePaymentConfirmation($invoice);
    }

    /**
     * Send admin custom invoice payment notification.
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function sendAdminCustomInvoicePaymentNotification(Invoice $invoice): bool
    {
        return $this->emailFacade->sendAdminCustomInvoicePaymentNotification($invoice);
    }

    /**
     * Send payment failure notification.
     *
     * @param Invoice $order
     * @return bool
     */
    public function sendPaymentFailureNotification(Invoice $order): bool
    {
        return $this->emailFacade->sendPaymentFailureNotification($order);
    }

    // Ticket email methods
    /**
     * Send ticket created email.
     *
     * @param User $user
     * @param array<string, mixed> $ticketData
     * @return bool
     */
    public function sendTicketCreated(User $user, array $ticketData): bool
    {
        return $this->emailFacade->sendTicketCreated($user, $ticketData);
    }

    /**
     * Send ticket status update email.
     *
     * @param User $user
     * @param array<string, mixed> $ticketData
     * @return bool
     */
    public function sendTicketStatusUpdate(User $user, array $ticketData): bool
    {
        return $this->emailFacade->sendTicketStatusUpdate($user, $ticketData);
    }

    /**
     * Send ticket reply email.
     *
     * @param User $user
     * @param array<string, mixed> $ticketData
     * @return bool
     */
    public function sendTicketReply(User $user, array $ticketData): bool
    {
        return $this->emailFacade->sendTicketReply($user, $ticketData);
    }

    /**
     * Send admin ticket created email.
     *
     * @param array<string, mixed> $ticketData
     * @return bool
     */
    public function sendAdminTicketCreated(array $ticketData): bool
    {
        return $this->emailFacade->sendAdminTicketCreated($ticketData);
    }

    /**
     * Send admin ticket reply email.
     *
     * @param array<string, mixed> $ticketData
     * @return bool
     */
    public function sendAdminTicketReply(array $ticketData): bool
    {
        return $this->emailFacade->sendAdminTicketReply($ticketData);
    }

    /**
     * Send admin ticket closed email.
     *
     * @param array<string, mixed> $ticketData
     * @return bool
     */
    public function sendAdminTicketClosed(array $ticketData): bool
    {
        return $this->emailFacade->sendAdminTicketClosed($ticketData);
    }

    // Additional methods for backward compatibility
    /**
     * Create or update email template.
     *
     * @param array<string, mixed> $templateData
     *
     * @return array<string, mixed>
     */
    public function createOrUpdateTemplate(array $templateData): array
    {
        // This method would need to be implemented based on your EmailTemplate model
        // For now, we'll throw an exception to indicate it needs implementation
        throw new \BadMethodCallException('createOrUpdateTemplate method needs to be implemented');
    }

    // Admin notification methods
    /**
     * Send admin license created email.
     *
     * @param array<string, mixed> $licenseData
     * @return bool
     */
    public function sendAdminLicenseCreated(array $licenseData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_license_created', $licenseData);
    }

    /**
     * Send admin license expiring email.
     *
     * @param array<string, mixed> $licenseData
     * @return bool
     */
    public function sendAdminLicenseExpiring(array $licenseData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_license_expiring', $licenseData);
    }

    /**
     * Send admin license renewed email.
     *
     * @param array<string, mixed> $licenseData
     * @return bool
     */
    public function sendAdminLicenseRenewed(array $licenseData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_license_renewed', $licenseData);
    }

    /**
     * Send renewal reminder email.
     *
     * @param User $user
     * @param array<string, mixed> $renewalData
     * @return bool
     */
    public function sendRenewalReminder(\App\Models\User $user, array $renewalData): bool
    {
        return $this->emailFacade->sendToUser($user, 'user_renewal_reminder', $renewalData);
    }

    /**
     * Send admin renewal reminder email.
     *
     * @param array<string, mixed> $renewalData
     * @return bool
     */
    public function sendAdminRenewalReminder(array $renewalData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_renewal_reminder', $renewalData);
    }

    /**
     * Send product version update email.
     *
     * @param User $user
     * @param array<string, mixed> $productData
     * @return bool
     */
    public function sendProductVersionUpdate(\App\Models\User $user, array $productData): bool
    {
        return $this->emailFacade->sendToUser($user, 'user_product_version_update', $productData);
    }

    /**
     * Send admin invoice approaching due email.
     *
     * @param array<string, mixed> $invoiceData
     * @return bool
     */
    public function sendAdminInvoiceApproachingDue(array $invoiceData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_invoice_approaching_due', $invoiceData);
    }

    /**
     * Send admin invoice cancelled email.
     *
     * @param array<string, mixed> $invoiceData
     * @return bool
     */
    public function sendAdminInvoiceCancelled(array $invoiceData): bool
    {
        return $this->emailFacade->sendToAdmin('admin_invoice_cancelled', $invoiceData);
    }
}