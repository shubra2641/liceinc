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
        array<string, mixed> $data = [],
        ?string $recipientName = null
    ): bool {
        return $this->emailSender->sendEmail($templateName, $recipientEmail, $data, $recipientName);
    }

    /**
     * Send email to user.
     */
    public function sendToUser(User $user, string $templateName, array<string, mixed> $data = []): bool
    {
        return $this->emailSender->sendToUser($user, $templateName, $data);
    }

    /**
     * Send email to admin.
     */
    public function sendToAdmin(string $templateName, array<string, mixed> $data = []): bool
    {
        return $this->emailSender->sendToAdmin($templateName, $data);
    }

    /**
     * Send bulk emails to multiple users.
     */
    public function sendBulkEmails(array $users, string $templateName, array<string, mixed> $data = []): array<string, mixed>
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($users as $user) {
                if ($user instanceof User) {
                    $success = $this->sendToUser($user, $templateName, $data);
                } else {
                $success = $this->sendEmail($templateName, (string)$user, $data);
                }

                if ($success) {
                    $results['success']++;
                } else {
                $results['failed']++;
                $results['errors'][] = 'Failed to send to: ' . ($user instanceof User ? $user->email : (string)$user);
            }
        }

        return $results;
    }

    /**
     * Send renewal reminder email.
     */
    public function sendRenewalReminder(User $user, array<string, mixed> $data = []): bool
    {
        return $this->sendToUser($user, 'renewal_reminder', $data);
    }

    /**
     * Send admin renewal reminder email.
     */
    public function sendAdminRenewalReminder(array<string, mixed> $data = []): bool
    {
        return $this->sendToAdmin('admin_renewal_reminder', $data);
    }

    /**
     * Send license created email.
     */
    public function sendLicenseCreated(User $user, array<string, mixed> $data = []): bool
    {
        return $this->sendToUser($user, 'license_created', $data);
    }

    /**
     * Send admin license created email.
     */
    public function sendAdminLicenseCreated(array<string, mixed> $data = []): bool
    {
        return $this->sendToAdmin('admin_license_created', $data);
    }

    /**
     * Send ticket reply email.
     */
    public function sendTicketReply(User $user, array<string, mixed> $data = []): bool
    {
        return $this->sendToUser($user, 'ticket_reply', $data);
    }

    /**
     * Send ticket status update email.
     */
    public function sendTicketStatusUpdate(User $user, array<string, mixed> $data = []): bool
    {
        return $this->sendToUser($user, 'ticket_status_update', $data);
    }

    /**
     * Send welcome email.
     */
    public function sendWelcome(User $user, array<string, mixed> $data = []): bool
    {
        return $this->sendToUser($user, 'welcome', $data);
    }

    /**
     * Send new user notification email.
     */
    public function sendNewUserNotification(array<string, mixed> $data = []): bool
    {
        return $this->sendToAdmin('new_user_notification', $data);
    }

    /**
     * Send custom invoice payment confirmation email.
     */
    public function sendCustomInvoicePaymentConfirmation(User $user, array<string, mixed> $data = []): bool
    {
        return $this->sendToUser($user, 'custom_invoice_payment_confirmation', $data);
    }

    /**
     * Send admin custom invoice payment notification email.
     */
    public function sendAdminCustomInvoicePaymentNotification(array<string, mixed> $data = []): bool
    {
        return $this->sendToAdmin('admin_custom_invoice_payment_notification', $data);
    }

    /**
     * Send payment confirmation email.
     */
    public function sendPaymentConfirmation(User $user, array<string, mixed> $data = []): bool
    {
        return $this->sendToUser($user, 'payment_confirmation', $data);
    }

    /**
     * Send admin payment notification email.
     */
    public function sendAdminPaymentNotification(array<string, mixed> $data = []): bool
    {
        return $this->sendToAdmin('admin_payment_notification', $data);
    }

    /**
     * Send ticket created email.
     */
    public function sendTicketCreated(User $user, array<string, mixed> $data = []): bool
    {
        return $this->sendToUser($user, 'ticket_created', $data);
    }

    /**
     * Send admin ticket created email.
     */
    public function sendAdminTicketCreated(array<string, mixed> $data = []): bool
    {
        return $this->sendToAdmin('admin_ticket_created', $data);
    }

    /**
     * Send admin ticket reply email.
     */
    public function sendAdminTicketReply(array<string, mixed> $data = []): bool
    {
        return $this->sendToAdmin('admin_ticket_reply', $data);
    }
}
