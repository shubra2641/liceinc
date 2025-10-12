<?php

declare(strict_types=1);

namespace App\Services\Email\Facades;

use App\Models\User;
use App\Services\Email\EmailFacade;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * Email Facade.
 *
 * Provides a simplified interface to all email services.
 *
 * @method static bool sendEmail(string $templateName, string $recipientEmail, array<string, mixed> $data = [], ?string $recipientName = null)
 * @method static bool sendToUser(User $user, string $templateName, array<string, mixed> $data = [])
 * @method static bool sendToAdmin(string $templateName, array<string, mixed> $data = [])
 * @method static array<string, mixed> sendBulkEmail(array<string, mixed> $users, string $templateName, array<string, mixed> $data = [])
 * @method static Collection<int, \App\Models\EmailTemplate> getTemplates(string $type, ?string $category = null)
 * @method static array<string, mixed> testTemplate(string $templateName, array<string, mixed> $data = [])
 * @method static bool sendUserWelcome(User $user)
 * @method static bool sendEmailVerification(User $user, string $verificationUrl)
 * @method static bool sendPasswordReset(User $user, string $resetUrl)
 * @method static bool sendNewUserNotification(User $user)
 * @method static bool sendPaymentConfirmation(\App\Models\License $license, \App\Models\Invoice $invoice)
 * @method static bool sendLicenseExpiring(User $user, array<string, mixed> $licenseData)
 * @method static bool sendLicenseUpdated(User $user, array<string, mixed> $licenseData)
 * @method static bool sendLicenseCreated(\App\Models\License $license, ?User $user = null)
 * @method static bool sendAdminPaymentNotification(\App\Models\License $license, \App\Models\Invoice $invoice)
 * @method static bool sendInvoiceApproachingDue(User $user, array<string, mixed> $invoiceData)
 * @method static bool sendInvoicePaid(User $user, array<string, mixed> $invoiceData)
 * @method static bool sendInvoiceCancelled(User $user, array<string, mixed> $invoiceData)
 * @method static bool sendCustomInvoicePaymentConfirmation(\App\Models\Invoice $invoice)
 * @method static bool sendAdminCustomInvoicePaymentNotification(\App\Models\Invoice $invoice)
 * @method static bool sendPaymentFailureNotification(\App\Models\Invoice $order)
 * @method static bool sendTicketCreated(User $user, array<string, mixed> $ticketData)
 * @method static bool sendTicketStatusUpdate(User $user, array<string, mixed> $ticketData)
 * @method static bool sendTicketReply(User $user, array<string, mixed> $ticketData)
 * @method static bool sendAdminTicketCreated(array<string, mixed> $ticketData)
 * @method static bool sendAdminTicketReply(array<string, mixed> $ticketData)
 * @method static bool sendAdminTicketClosed(array<string, mixed> $ticketData)
 *
 * @version 1.0.0
 */
class Email extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return EmailFacade::class;
    }
}
