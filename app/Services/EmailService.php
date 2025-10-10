<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Services\Email\EmailSender;
use App\Services\Email\EmailValidator;
use App\Services\Email\EmailTemplateService;

/**
 * Simplified Email Service.
 *
 * A clean and maintainable email service that delegates
 * complex operations to specialized services.
 */
class EmailService
{
    public function __construct(
        private EmailSender $emailSender
    ) {
    }

    /**
     * Send email using template name and data.
     */
    public function sendEmail(
        string $templateName,
        string $recipientEmail,
        array $data = [],
        ?string $recipientName = null
    ): bool {
        return $this->emailSender->sendEmail($templateName, $recipientEmail, $data, $recipientName);
    }

    /**
     * Send email to user.
     */
    public function sendToUser(User $user, string $templateName, array $data = []): bool
    {
        return $this->emailSender->sendToUser($user, $templateName, $data);
    }

    /**
     * Send email to admin.
     */
    public function sendToAdmin(string $templateName, array $data = []): bool
    {
        return $this->emailSender->sendToAdmin($templateName, $data);
    }

    /**
     * Send bulk emails to multiple users.
     */
    public function sendBulkEmails(array $users, string $templateName, array $data = []): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($users as $user) {
            if ($user instanceof User) {
                $success = $this->sendToUser($user, $templateName, $data);
            } else {
                $userEmail = (string) $user;
                $success = $this->sendEmail($templateName, $userEmail, $data);
            }

            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
                $userInfo = $user instanceof User ? $user->email : (string) $user;
                $results['errors'][] = 'Failed to send to: ' . $userInfo;
            }
        }

        return $results;
    }

    /**
     * Send renewal reminder email.
     */
    public function sendRenewalReminder(User $user, array $data = []): bool
    {
        return $this->sendToUser($user, 'renewal_reminder', $data);
    }

    /**
     * Send admin renewal reminder email.
     */
    public function sendAdminRenewalReminder(array $data = []): bool
    {
        return $this->sendToAdmin('admin_renewal_reminder', $data);
    }

    /**
     * Send license created email.
     */
    public function sendLicenseCreated(User $user, array $data = []): bool
    {
        return $this->sendToUser($user, 'license_created', $data);
    }

    /**
     * Send admin license created email.
     */
    public function sendAdminLicenseCreated(array $data = []): bool
    {
        return $this->sendToAdmin('admin_license_created', $data);
    }

    /**
     * Send ticket reply email.
     */
    public function sendTicketReply(User $user, array $data = []): bool
    {
        return $this->sendToUser($user, 'ticket_reply', $data);
    }

    /**
     * Send ticket status update email.
     */
    public function sendTicketStatusUpdate(User $user, array $data = []): bool
    {
        return $this->sendToUser($user, 'ticket_status_update', $data);
    }

    /**
     * Send welcome email.
     */
    public function sendWelcome(User $user, array $data = []): bool
    {
        return $this->sendToUser($user, 'welcome', $data);
    }

    /**
     * Send new user notification email.
     */
    public function sendNewUserNotification(array $data = []): bool
    {
        return $this->sendToAdmin('new_user_notification', $data);
    }

    /**
     * Send custom invoice payment confirmation email.
     */
    public function sendCustomInvoicePaymentConfirmation(User $user, array $data = []): bool
    {
        return $this->sendToUser($user, 'custom_invoice_payment_confirmation', $data);
    }

    /**
     * Send admin custom invoice payment notification email.
     */
    public function sendAdminCustomInvoicePaymentNotification(array $data = []): bool
    {
        return $this->sendToAdmin('admin_custom_invoice_payment_notification', $data);
    }

    /**
     * Send payment confirmation email.
     */
    public function sendPaymentConfirmation(User $user, array $data = []): bool
    {
        return $this->sendToUser($user, 'payment_confirmation', $data);
    }

    /**
     * Send admin payment notification email.
     */
    public function sendAdminPaymentNotification(array $data = []): bool
    {
        return $this->sendToAdmin('admin_payment_notification', $data);
    }

    /**
     * Send ticket created email.
     */
    public function sendTicketCreated(User $user, array $data = []): bool
    {
        return $this->sendToUser($user, 'ticket_created', $data);
    }

    /**
     * Send admin ticket created email.
     */
    public function sendAdminTicketCreated(array $data = []): bool
    {
        return $this->sendToAdmin('admin_ticket_created', $data);
    }

    /**
     * Send admin ticket reply email.
     */
    public function sendAdminTicketReply(array $data = []): bool
    {
        return $this->sendToAdmin('admin_ticket_reply', $data);
    }
}
