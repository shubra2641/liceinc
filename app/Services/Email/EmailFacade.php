<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Models\User;
use App\Models\License;
use App\Models\Invoice;
use App\Services\Email\Contracts\EmailServiceInterface;
use App\Services\Email\Handlers\InvoiceEmailHandler;
use App\Services\Email\Handlers\LicenseEmailHandler;
use App\Services\Email\Handlers\TicketEmailHandler;
use App\Services\Email\Handlers\UserEmailHandler;
use Illuminate\Database\Eloquent\Collection;

/**
 * Email Facade.
 *
 * Provides a simplified interface to all email services.
 *
 * @version 1.0.0
 */
class EmailFacade
{
    public function __construct(
        protected EmailServiceInterface $emailService,
        protected UserEmailHandler $userHandler,
        protected LicenseEmailHandler $licenseHandler,
        protected InvoiceEmailHandler $invoiceHandler,
        protected TicketEmailHandler $ticketHandler
    ) {
    }

    // Core email methods

    /**
     * Send email to a specific recipient.
     *
     * @param string $templateName The email template name
     * @param string $recipientEmail The recipient email address
     * @param array<string, mixed> $data Template data variables
     * @param string|null $recipientName Optional recipient name
     *
     * @return bool True if email sent successfully
     */
    public function sendEmail(
        string $templateName,
        string $recipientEmail,
        array $data = [],
        ?string $recipientName = null
    ): bool {
        return $this->emailService->sendEmail($templateName, $recipientEmail, $data, $recipientName);
    }

    /**
     * Send email to a specific user.
     *
     * @param User $user The user to send email to
     * @param string $templateName The email template name
     * @param array<string, mixed> $data Template data variables
     *
     * @return bool True if email sent successfully
     */
    public function sendToUser(
        User $user,
        string $templateName,
        array $data = []
    ): bool {
        return $this->emailService->sendToUser($user, $templateName, $data);
    }

    /**
     * Send email to admin.
     *
     * @param string $templateName The email template name
     * @param array<string, mixed> $data Template data variables
     * @return bool True if email sent successfully
     */
    public function sendToAdmin(
        string $templateName,
        array $data = []
    ): bool {
        return $this->emailService->sendToAdmin($templateName, $data);
    }

    /**
     * Send bulk emails to multiple users.
     *
     * @param array<string, mixed> $users Array of users to send emails to
     * @param string $templateName The email template name
     * @param array<string, mixed> $data Template data variables
     *
     * @return array<string, mixed> Results of bulk email sending
     */
    public function sendBulkEmail(
        array $users,
        string $templateName,
        array $data = []
    ): array {
        return $this->emailService->sendBulkEmail($users, $templateName, $data);
    }

    /**
     * Get email templates by type and category.
     *
     * @param string $type Template type
     * @param string|null $category Optional template category
     *
     * @return Collection<int, \App\Models\EmailTemplate> Collection of email templates
     */
    public function getTemplates(
        string $type,
        ?string $category = null
    ): Collection {
        return $this->emailService->getTemplates($type, $category);
    }

    /**
     * Test email template with sample data.
     *
     * @param string $templateName The email template name
     * @param array<string, mixed> $data Template data variables
     *
     * @return array<string, mixed> Test results
     */
    public function testTemplate(
        string $templateName,
        array $data = []
    ): array {
        return $this->emailService->testTemplate($templateName, $data);
    }

    // User email methods

    /**
     * Send welcome email to new user.
     *
     * @param User $user The user to send welcome email to
     * @return bool True if email sent successfully
     */
    public function sendUserWelcome(User $user): bool
    {
        return $this->userHandler->sendUserWelcome($user);
    }

    /**
     * Send email verification email to user.
     *
     * @param User $user The user to send verification email to
     * @param string $verificationUrl The email verification URL
     * @return bool True if email sent successfully
     */
    public function sendEmailVerification(
        User $user,
        string $verificationUrl
    ): bool {
        return $this->userHandler->sendEmailVerification($user, $verificationUrl);
    }

    /**
     * Send password reset email to user.
     *
     * @param User $user The user to send password reset email to
     * @param string $resetUrl The password reset URL
     * @return bool True if email sent successfully
     */
    public function sendPasswordReset(
        User $user,
        string $resetUrl
    ): bool {
        return $this->userHandler->sendPasswordReset($user, $resetUrl);
    }

    /**
     * Send new user notification to admin.
     *
     * @param User $user The new user
     * @return bool True if email sent successfully
     */
    public function sendNewUserNotification(User $user): bool
    {
        return $this->userHandler->sendNewUserNotification($user);
    }

    // License email methods

    /**
     * Send payment confirmation email.
     *
     * @param License $license The license object
     * @param Invoice $invoice The invoice object
     * @return bool True if email sent successfully
     */
    public function sendPaymentConfirmation(
        License $license,
        Invoice $invoice
    ): bool {
        return $this->licenseHandler->sendPaymentConfirmation($license, $invoice);
    }

    /**
     * Send license expiring notification email.
     *
     * @param User $user The user to send email to
     * @param array<string, mixed> $licenseData License data
     * @return bool True if email sent successfully
     */
    public function sendLicenseExpiring(
        User $user,
        array $licenseData
    ): bool {
        return $this->licenseHandler->sendLicenseExpiring($user, $licenseData);
    }

    /**
     * Send license updated notification email.
     *
     * @param User $user The user to send email to
     * @param array<string, mixed> $licenseData License data
     * @return bool True if email sent successfully
     */
    public function sendLicenseUpdated(
        User $user,
        array $licenseData
    ): bool {
        return $this->licenseHandler->sendLicenseUpdated($user, $licenseData);
    }

    /**
     * Send license created notification email.
     *
     * @param License $license The license object
     * @param User|null $user Optional user to send email to
     * @return bool True if email sent successfully
     */
    public function sendLicenseCreated(
        License $license,
        ?User $user = null
    ): bool {
        return $this->licenseHandler->sendLicenseCreated($license, $user);
    }

    /**
     * Send admin payment notification email.
     *
     * @param License $license The license object
     * @param Invoice $invoice The invoice object
     * @return bool True if email sent successfully
     */
    public function sendAdminPaymentNotification(
        License $license,
        Invoice $invoice
    ): bool {
        return $this->licenseHandler->sendAdminPaymentNotification($license, $invoice);
    }

    // Invoice email methods

    /**
     * Send invoice approaching due notification email.
     *
     * @param User $user The user to send email to
     * @param array<string, mixed> $invoiceData Invoice data
     * @return bool True if email sent successfully
     */
    public function sendInvoiceApproachingDue(
        User $user,
        array $invoiceData
    ): bool {
        return $this->invoiceHandler->sendInvoiceApproachingDue($user, $invoiceData);
    }

    /**
     * Send invoice paid notification email.
     *
     * @param User $user The user to send email to
     * @param array<string, mixed> $invoiceData Invoice data
     * @return bool True if email sent successfully
     */
    public function sendInvoicePaid(
        User $user,
        array $invoiceData
    ): bool {
        return $this->invoiceHandler->sendInvoicePaid($user, $invoiceData);
    }

    /**
     * Send invoice cancelled notification email.
     *
     * @param User $user The user to send email to
     * @param array<string, mixed> $invoiceData Invoice data
     * @return bool True if email sent successfully
     */
    public function sendInvoiceCancelled(
        User $user,
        array $invoiceData
    ): bool {
        return $this->invoiceHandler->sendInvoiceCancelled($user, $invoiceData);
    }

    /**
     * Send custom invoice payment confirmation email.
     *
     * @param Invoice $invoice The invoice object
     * @return bool True if email sent successfully
     */
    public function sendCustomInvoicePaymentConfirmation(
        Invoice $invoice
    ): bool {
        return $this->invoiceHandler->sendCustomInvoicePaymentConfirmation($invoice);
    }

    /**
     * Send admin custom invoice payment notification email.
     *
     * @param Invoice $invoice The invoice object
     * @return bool True if email sent successfully
     */
    public function sendAdminCustomInvoicePaymentNotification(
        Invoice $invoice
    ): bool {
        return $this->invoiceHandler->sendAdminCustomInvoicePaymentNotification($invoice);
    }

    /**
     * Send payment failure notification email.
     *
     * @param Invoice $order The invoice/order object
     * @return bool True if email sent successfully
     */
    public function sendPaymentFailureNotification(
        Invoice $order
    ): bool {
        return $this->invoiceHandler->sendPaymentFailureNotification($order);
    }

    // Ticket email methods

    /**
     * Send ticket created notification email.
     *
     * @param User $user The user to send email to
     * @param array<string, mixed> $ticketData Ticket data
     * @return bool True if email sent successfully
     */
    public function sendTicketCreated(
        User $user,
        array $ticketData
    ): bool {
        return $this->ticketHandler->sendTicketCreated($user, $ticketData);
    }

    /**
     * Send ticket status update notification email.
     *
     * @param User $user The user to send email to
     * @param array<string, mixed> $ticketData Ticket data
     * @return bool True if email sent successfully
     */
    public function sendTicketStatusUpdate(
        User $user,
        array $ticketData
    ): bool {
        return $this->ticketHandler->sendTicketStatusUpdate($user, $ticketData);
    }

    /**
     * Send ticket reply notification email.
     *
     * @param User $user The user to send email to
     * @param array<string, mixed> $ticketData Ticket data
     * @return bool True if email sent successfully
     */
    public function sendTicketReply(
        User $user,
        array $ticketData
    ): bool {
        return $this->ticketHandler->sendTicketReply($user, $ticketData);
    }

    /**
     * Send admin ticket created notification email.
     *
     * @param array<string, mixed> $ticketData Ticket data
     * @return bool True if email sent successfully
     */
    public function sendAdminTicketCreated(
        array $ticketData
    ): bool {
        return $this->ticketHandler->sendAdminTicketCreated($ticketData);
    }

    /**
     * Send admin ticket reply notification email.
     *
     * @param array<string, mixed> $ticketData Ticket data
     * @return bool True if email sent successfully
     */
    public function sendAdminTicketReply(
        array $ticketData
    ): bool {
        return $this->ticketHandler->sendAdminTicketReply($ticketData);
    }

    /**
     * Send admin ticket closed notification email.
     *
     * @param array<string, mixed> $ticketData Ticket data
     * @return bool True if email sent successfully
     */
    public function sendAdminTicketClosed(
        array $ticketData
    ): bool {
        return $this->ticketHandler->sendAdminTicketClosed($ticketData);
    }
}
