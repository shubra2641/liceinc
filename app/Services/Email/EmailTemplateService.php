<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Email template service for handling template operations.
 */
class EmailTemplateService
{
    public function __construct(
        private EmailValidator $validator
    ) {}

    /**
     * Get template by name.
     */
    public function getTemplate(string $templateName): ?EmailTemplate
    {
        try {
            $templateName = $this->validator->validateTemplateName($templateName);
            return EmailTemplate::getByName($templateName);
        } catch (\Exception $e) {
            Log::error('Failed to get template: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Prepare common variables.
     */
    public function prepareCommonVariables(array $data = []): array
    {
        return array_merge($data, [
            'site_name' => config('app.name'),
            'site_url' => config('app.url'),
            'current_year' => date('Y'),
        ]);
    }

    /**
     * Prepare user-specific variables.
     */
    public function prepareUserVariables(User $user, array $data = []): array
    {
        $userData = [
            'user_name' => $this->validator->sanitizeString($user->name),
            'user_firstname' => $this->validator->sanitizeString($user->firstname ?? ''),
            'user_lastname' => $this->validator->sanitizeString($user->lastname ?? ''),
            'user_id' => $user->id,
        ];

        return array_merge($data, $userData);
    }

    /**
     * Prepare admin email.
     */
    public function getAdminEmail(): string
    {
        return config('mail.admin_email', config('mail.from.address'));
    }
}
