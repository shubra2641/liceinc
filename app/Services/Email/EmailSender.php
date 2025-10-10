<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Mail\DynamicEmail;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Email sender service for handling email dispatch.
 */
class EmailSender
{
    public function __construct(
        private EmailValidator $validator,
        private EmailTemplateService $templateService
    ) {
    }

    /**
     * Send email using template.
     */
    public function sendEmail(
        string $templateName,
        string $recipientEmail,
        array $data = [],
        ?string $recipientName = null
    ): bool {
        try {
            // Validate inputs
            $templateName = $this->validator->validateTemplateName($templateName);
            $recipientEmail = $this->validator->validateEmail($recipientEmail);
            $recipientName = $this->validator->sanitizeString($recipientName);
            $data = $this->validator->sanitizeData($data);

            // Get template
            $template = $this->templateService->getTemplate($templateName);
            if (!$template) {
                Log::error('Email template not found: ' . $templateName);
                return false;
            }

            // Prepare data
            $data = $this->templateService->prepareCommonVariables($data);
            $data['recipient_email'] = $recipientEmail;
            $data['recipient_name'] = $recipientName ?? 'User';

            // Send email
            Mail::to($recipientEmail, $recipientName)->send(new DynamicEmail($template, $data));
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage(), [
                'template' => $templateName,
                'recipient' => $recipientEmail,
                'exception' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Send email to user.
     */
    public function sendToUser(User $user, string $templateName, array $data = []): bool
    {
        if (!$user->email) {
            Log::error('Invalid user provided for email sending');
            return false;
        }

        $userData = $this->templateService->prepareUserVariables($user, $data);
        return $this->sendEmail($templateName, $user->email, $userData, $user->name);
    }

    /**
     * Send email to admin.
     */
    public function sendToAdmin(string $templateName, array $data = []): bool
    {
        $adminEmail = $this->templateService->getAdminEmail();
        return $this->sendEmail($templateName, $adminEmail, $data, 'Admin');
    }
}
