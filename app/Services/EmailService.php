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
    ) {}

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
                $success = $this->sendEmail($templateName, $user, $data);
                }

                if ($success) {
                    $results['success']++;
                } else {
                $results['failed']++;
                $results['errors'][] = 'Failed to send to: ' . ($user instanceof User ? $user->email : $user);
            }
        }

        return $results;
    }
}