<?php

declare(strict_types=1);

namespace App\Services\Email\Contracts;

use App\Models\User;

/**
 * Email Handler Interface.
 *
 * Defines the contract for specific email handlers.
 *
 * @version 1.0.0
 */
interface EmailHandlerInterface
{
    /**
     * Send user registration welcome email.
     */
    public function sendUserWelcome(User $user): bool;

    /**
     * Send email verification email.
     */
    public function sendEmailVerification(User $user, string $verificationUrl): bool;

    /**
     * Send password reset email.
     */
    public function sendPasswordReset(User $user, string $resetUrl): bool;

    /**
     * Send admin notification when a new user registers.
     */
    public function sendNewUserNotification(User $user): bool;
}
