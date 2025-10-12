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
 * @method static bool sendEmail(string $templateName, string $recipientEmail, array $data = [], ?string $recipientName = null)
 * @method static bool sendToUser(User $user, string $templateName, array $data = [])
 * @method static bool sendToAdmin(string $templateName, array $data = [])
 * @method static array sendBulkEmail(array $users, string $templateName, array $data = [])
 * @method static Collection getTemplates(string $type, ?string $category = null)
 * @method static array testTemplate(string $templateName, array $data = [])
 * @method static bool sendUserWelcome(User $user)
 * @method static bool sendEmailVerification(User $user, string $verificationUrl)
 * @method static bool sendPasswordReset(User $user, string $resetUrl)
 * @method static bool sendNewUserNotification(User $user)
 * @method static bool sendPaymentConfirmation($license, $invoice)
 * @method static bool sendLicenseExpiring(User $user, array $licenseData)
 * @method static bool sendLicenseUpdated(User $user, array $licenseData)
 * @method static bool sendLicenseCreated($license, ?User $user = null)
 * @method static bool sendAdminPaymentNotification($license, $invoice)
 * @method static bool sendInvoiceApproachingDue(User $user, array $invoiceData)
 * @method static bool sendInvoicePaid(User $user, array $invoiceData)
 * @method static bool sendInvoiceCancelled(User $user, array $invoiceData)
 * @method static bool sendCustomInvoicePaymentConfirmation($invoice)
 * @method static bool sendAdminCustomInvoicePaymentNotification($invoice)
 * @method static bool sendPaymentFailureNotification($order)
 * @method static bool sendTicketCreated(User $user, array $ticketData)
 * @method static bool sendTicketStatusUpdate(User $user, array $ticketData)
 * @method static bool sendTicketReply(User $user, array $ticketData)
 * @method static bool sendAdminTicketCreated(array $ticketData)
 * @method static bool sendAdminTicketReply(array $ticketData)
 * @method static bool sendAdminTicketClosed(array $ticketData)
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
