<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Mail\DynamicEmail;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\User;
use App\Services\Email\Contracts\EmailServiceInterface;
use App\Services\Email\Contracts\EmailValidatorInterface;
use App\Services\Email\Traits\EmailLoggingTrait;
use App\Services\Email\Traits\EmailValidationTrait;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Core Email Service with enhanced security.
 *
 * A simplified and well-organized email service that handles
 * dynamic email sending using database-stored templates.
 *
 * @version 1.0.0
 */
class CoreEmailService implements EmailServiceInterface
{
    use EmailValidationTrait;
    use EmailLoggingTrait;

    public function __construct(
        protected EmailValidatorInterface $validator
    ) {
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
        try {
            // Validate and sanitize inputs
            $templateName = $this->validateTemplateName($templateName);
            $recipientEmail = $this->validateEmail($recipientEmail);
            $recipientName = $this->sanitizeString($recipientName);
            $data = $this->sanitizeData($data);

            $template = EmailTemplate::getByName($templateName);
            if (!$template) {
                $this->logTemplateNotFound($templateName);
                return false;
            }

            // Add common variables with sanitization
            $data = array_merge($data, [
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName ?? 'User',
                'site_name' => config('app.name'),
                'site_url' => config('app.url'),
                'current_year' => date('Y'),
            ]);

            // Send email
            Mail::to($recipientEmail, $recipientName)->send(new DynamicEmail($template, $data));
            $this->logEmailSuccess($templateName, $recipientEmail);

            return true;
        } catch (Exception $e) {
            $this->logEmailError($templateName, $recipientEmail, $e->getMessage(), $e);
            return false;
        }
    }

    /**
     * Send email to user using template.
     *
     * @param array<string, mixed> $data
     */
    public function sendToUser(User $user, string $templateName, array $data = []): bool
    {
        if (!$user->email) {
            $this->logInvalidUser('email sending');
            return false;
        }

        $userData = [
            'user_name' => $this->sanitizeString($user->name),
            'user_firstname' => $this->sanitizeString($user->firstname ?? ''),
            'user_lastname' => $this->sanitizeString($user->lastname ?? ''),
            'user_id' => $user->id,
        ];

        return $this->sendEmail($templateName, $user->email, array_merge($data, $userData), $user->name);
    }

    /**
     * Send email to admin using template.
     *
     * @param array<string, mixed> $data
     */
    public function sendToAdmin(string $templateName, array $data = []): bool
    {
        // Get admin email from settings or use default
        $adminEmail = Setting::get('support_email', config('mail.from.address'));
        if (empty($adminEmail)) {
            $this->logAdminEmailNotConfigured();
            return false;
        }

        $adminData = [
            'admin_name' => 'Administrator',
            'site_name' => config('app.name'),
        ];

        return $this->sendEmail(
            $templateName,
            is_string($adminEmail) ? $adminEmail : 'admin@example.com',
            array_merge($data, $adminData),
            'Administrator'
        );
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
        if (empty($users)) {
            Log::error('Empty users array provided for bulk email sending');
            return ['total' => 0, 'success' => 0, 'failed' => 0, 'errors' => []];
        }

        $results = [
            'total' => count($users),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($users as $user) {
            try {
                if ($user instanceof User) {
                    $success = $this->sendToUser($user, $templateName, $data);
                } else {
                    $userString = is_string($user) ? $user : '';
                    $success = $this->sendEmail($templateName, $userString, $data);
                }

                if ($success) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $userEmail = $user instanceof User ? $user->email : $user;
                    $results['errors'][] = is_string($userEmail) ? $userEmail : '';
                }
            } catch (Exception $e) {
                $results['failed']++;
                $userEmail = $user instanceof User ? $user->email : $user;
                $results['errors'][] = is_string($userEmail) ? $userEmail : '';
                $this->logBulkEmailError($e->getMessage(), $e);
            }
        }

        return $results;
    }

    /**
     * Get available templates by type and category.
     *
     * @return Collection<int, EmailTemplate>
     */
    public function getTemplates(string $type, ?string $category = null): Collection
    {
        $type = $this->validateTemplateType($type);
        $category = $this->sanitizeString($category);

        $query = EmailTemplate::forType($type)->active();
        if ($category) {
            $query->forCategory($category);
        }

        return $query->get();
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
        $validatedTemplateName = $this->validateTemplateName($templateName);
        $sanitizedData = $this->sanitizeData($data);
        $template = EmailTemplate::getByName($validatedTemplateName);

        if (!$template) {
            throw new \InvalidArgumentException("Template not found: {$validatedTemplateName}");
        }

        return $template->render($sanitizedData);
    }
}
