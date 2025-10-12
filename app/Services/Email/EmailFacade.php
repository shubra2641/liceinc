<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Models\User;
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
    public function sendEmail(string $templateName, string $recipientEmail, array $data = [], ?string $recipientName = null): bool
    {
        return $this->emailService->sendEmail($templateName, $recipientEmail, $data, $recipientName);
    }

    public function sendToUser(User $user, string $templateName, array $data = []): bool
    {
        return $this->emailService->sendToUser($user, $templateName, $data);
    }

    public function sendToAdmin(string $templateName, array $data = []): bool
    {
        return $this->emailService->sendToAdmin($templateName, $data);
    }

    public function sendBulkEmail(array $users, string $templateName, array $data = []): array
    {
        return $this->emailService->sendBulkEmail($users, $templateName, $data);
    }

    public function getTemplates(string $type, ?string $category = null): Collection
    {
        return $this->emailService->getTemplates($type, $category);
    }

    public function testTemplate(string $templateName, array $data = []): array
    {
        return $this->emailService->testTemplate($templateName, $data);
    }

    // User email methods
    public function sendUserWelcome(User $user): bool
    {
        return $this->userHandler->sendUserWelcome($user);
    }

    public function sendEmailVerification(User $user, string $verificationUrl): bool
    {
        return $this->userHandler->sendEmailVerification($user, $verificationUrl);
    }

    public function sendPasswordReset(User $user, string $resetUrl): bool
    {
        return $this->userHandler->sendPasswordReset($user, $resetUrl);
    }

    public function sendNewUserNotification(User $user): bool
    {
        return $this->userHandler->sendNewUserNotification($user);
    }

    // License email methods
    public function sendPaymentConfirmation($license, $invoice): bool
    {
        return $this->licenseHandler->sendPaymentConfirmation($license, $invoice);
    }

    public function sendLicenseExpiring(User $user, array $licenseData): bool
    {
        return $this->licenseHandler->sendLicenseExpiring($user, $licenseData);
    }

    public function sendLicenseUpdated(User $user, array $licenseData): bool
    {
        return $this->licenseHandler->sendLicenseUpdated($user, $licenseData);
    }

    public function sendLicenseCreated($license, ?User $user = null): bool
    {
        return $this->licenseHandler->sendLicenseCreated($license, $user);
    }

    public function sendAdminPaymentNotification($license, $invoice): bool
    {
        return $this->licenseHandler->sendAdminPaymentNotification($license, $invoice);
    }

    // Invoice email methods
    public function sendInvoiceApproachingDue(User $user, array $invoiceData): bool
    {
        return $this->invoiceHandler->sendInvoiceApproachingDue($user, $invoiceData);
    }

    public function sendInvoicePaid(User $user, array $invoiceData): bool
    {
        return $this->invoiceHandler->sendInvoicePaid($user, $invoiceData);
    }

    public function sendInvoiceCancelled(User $user, array $invoiceData): bool
    {
        return $this->invoiceHandler->sendInvoiceCancelled($user, $invoiceData);
    }

    public function sendCustomInvoicePaymentConfirmation($invoice): bool
    {
        return $this->invoiceHandler->sendCustomInvoicePaymentConfirmation($invoice);
    }

    public function sendAdminCustomInvoicePaymentNotification($invoice): bool
    {
        return $this->invoiceHandler->sendAdminCustomInvoicePaymentNotification($invoice);
    }

    public function sendPaymentFailureNotification($order): bool
    {
        return $this->invoiceHandler->sendPaymentFailureNotification($order);
    }

    // Ticket email methods
    public function sendTicketCreated(User $user, array $ticketData): bool
    {
        return $this->ticketHandler->sendTicketCreated($user, $ticketData);
    }

    public function sendTicketStatusUpdate(User $user, array $ticketData): bool
    {
        return $this->ticketHandler->sendTicketStatusUpdate($user, $ticketData);
    }

    public function sendTicketReply(User $user, array $ticketData): bool
    {
        return $this->ticketHandler->sendTicketReply($user, $ticketData);
    }

    public function sendAdminTicketCreated(array $ticketData): bool
    {
        return $this->ticketHandler->sendAdminTicketCreated($ticketData);
    }

    public function sendAdminTicketReply(array $ticketData): bool
    {
        return $this->ticketHandler->sendAdminTicketReply($ticketData);
    }

    public function sendAdminTicketClosed(array $ticketData): bool
    {
        return $this->ticketHandler->sendAdminTicketClosed($ticketData);
    }
}
