<?php

declare(strict_types=1);

namespace App\Services\Email\Handlers;

use App\Models\User;
use App\Services\Email\Contracts\EmailHandlerInterface;
use App\Services\Email\Contracts\EmailServiceInterface;
use App\Services\Email\Contracts\EmailValidatorInterface;
use App\Services\Email\Traits\EmailLoggingTrait;
use App\Services\Email\Traits\EmailValidationTrait;

/**
 * User Email Handler.
 *
 * Handles user-specific email operations with enhanced security.
 *
 * @version 1.0.0
 */
class UserEmailHandler implements EmailHandlerInterface
{
    use EmailValidationTrait;
    use EmailLoggingTrait;

    public function __construct(
        protected EmailServiceInterface $emailService,
        protected EmailValidatorInterface $validator,
    ) {
    }

    /**
     * Send user registration welcome email.
     */
    public function sendUserWelcome(User $user): bool
    {
        if (! $user->created_at) {
            $this->logInvalidUser('welcome email');

            return false;
        }

        return $this->emailService->sendToUser($user, 'user_welcome', [
            'registration_date' => $user->created_at->format('M d, Y'),
        ]);
    }

    /**
     * Send welcome email to new user.
     */
    public function sendWelcomeEmail(User $user): bool
    {
        return $this->sendUserWelcome($user);
    }

    /**
     * Send email verification email.
     */
    public function sendEmailVerification(User $user, string $verificationUrl): bool
    {
        if (empty($verificationUrl)) {
            $this->logInvalidUser('email verification');

            return false;
        }

        return $this->emailService->sendToUser($user, 'user_email_verification', [
            'verification_url' => $this->sanitizeString($verificationUrl),
            'verification_expires' => now()->addHours(24)->format('M d, Y \a\t g:i A'),
        ]);
    }

    /**
     * Send password reset email.
     */
    public function sendPasswordReset(User $user, string $resetUrl): bool
    {
        return $this->emailService->sendToUser($user, 'user_password_reset', [
            'reset_url' => $this->sanitizeString($resetUrl),
            'reset_expires' => now()->addHours(1)->format('M d, Y \a\t g:i A'),
        ]);
    }

    /**
     * Send admin notification when a new user registers.
     */
    public function sendNewUserNotification(User $user): bool
    {
        return $this->emailService->sendToAdmin('admin_new_user_registration', [
            'user_name' => $this->sanitizeString($user->name),
            'user_email' => $this->sanitizeString($user->email),
            'user_firstname' => $this->sanitizeString($user->firstname ?? ''),
            'user_lastname' => $this->sanitizeString($user->lastname ?? ''),
            'user_phone' => $this->sanitizeString($user->phonenumber ?? 'Not provided'),
            'user_country' => $this->sanitizeString($user->country ?? 'Not provided'),
            'registration_date' => $user->created_at ? $user->created_at->format('M d, Y \a\t g:i A') : 'Unknown',
            'registration_ip' => $this->sanitizeString(request()->ip() ?? 'Unknown'),
            'user_agent' => $this->sanitizeString(request()->userAgent() ?? 'Unknown'),
        ]);
    }
}
