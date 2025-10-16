<?php

declare(strict_types=1);

namespace App\Services\Email\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Email Service Interface.
 *
 * Defines the contract for email services with comprehensive
 * type safety and clear method signatures.
 *
 * @version 1.0.0
 */
interface EmailServiceInterface
{
    /**
     * Send email using template name and data.
     *
     * @param array<string, mixed> $data
     */
    public function sendEmail(
        string $templateName,
        string $recipientEmail,
        array $data = [],
        ?string $recipientName = null,
    ): bool;

    /**
     * Send email to user using template.
     *
     * @param array<string, mixed> $data
     */
    public function sendToUser(User $user, string $templateName, array $data = []): bool;

    /**
     * Send email to admin using template.
     *
     * @param array<string, mixed> $data
     */
    public function sendToAdmin(string $templateName, array $data = []): bool;

    /**
     * Send bulk emails to multiple users.
     *
     * @param array<string, mixed> $users
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function sendBulkEmail(array $users, string $templateName, array $data = []): array;

    /**
     * Get available templates by type and category.
     *
     * @return Collection<int, \App\Models\EmailTemplate>
     */
    public function getTemplates(string $type, ?string $category = null): Collection;

    /**
     * Test email template rendering.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function testTemplate(string $templateName, array $data = []): array;
}
