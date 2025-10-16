<?php

declare(strict_types=1);

namespace App\Services\Email\Handlers;

use App\Models\Invoice;
use App\Models\User;
use App\Services\Email\Contracts\EmailServiceInterface;
use App\Services\Email\Contracts\EmailValidatorInterface;
use App\Services\Email\Traits\EmailLoggingTrait;
use App\Services\Email\Traits\EmailValidationTrait;

/**
 * Invoice Email Handler.
 *
 * Handles invoice-related email operations with enhanced security.
 *
 * @version 1.0.0
 */
class InvoiceEmailHandler
{
    use EmailValidationTrait;
    use EmailLoggingTrait;

    public function __construct(
        protected EmailServiceInterface $emailService,
        protected EmailValidatorInterface $validator,
    ) {
    }

    /**
     * Send invoice created notification to user.
     */
    public function sendInvoiceCreated(User $user, Invoice $invoice): bool
    {
        return $this->emailService->sendToUser($user, 'user_invoice_created', [
            'invoice_number' => $invoice->invoice_number,
            'invoice_amount' => $invoice->amount,
            'due_date' => $invoice->due_date,
            'currency' => $invoice->currency,
        ]);
    }

    /**
     * Send invoice approaching due date notification to user.
     *
     * @param array<string, mixed> $invoiceData
     */
    public function sendInvoiceApproachingDue(User $user, array $invoiceData): bool
    {
        $invoiceNumber = $invoiceData['invoice_number'] ?? '';
        $invoiceAmount = $invoiceData['invoice_amount'] ?? 0;
        $dueDate = $invoiceData['due_date'] ?? '';
        $daysRemaining = $invoiceData['days_remaining'] ?? 0;

        return $this->emailService->sendToUser($user, 'user_invoice_approaching_due', array_merge($invoiceData, [
            'invoice_number' => is_string($invoiceNumber) ? $invoiceNumber : '',
            'invoice_amount' => is_numeric($invoiceAmount) ? (float)$invoiceAmount : 0.0,
            'due_date' => is_string($dueDate) ? $dueDate : '',
            'days_remaining' => is_numeric($daysRemaining) ? (int)$daysRemaining : 0,
        ]));
    }

    /**
     * Send invoice paid notification to user.
     *
     * @param array<string, mixed> $invoiceData
     */
    public function sendInvoicePaid(User $user, array $invoiceData): bool
    {
        return $this->emailService->sendToUser($user, 'user_invoice_paid', array_merge($invoiceData, [
            'invoice_number' => $invoiceData['invoice_number'] ?? '',
            'invoice_amount' => $invoiceData['invoice_amount'] ?? 0,
            'payment_date' => $invoiceData['payment_date'] ?? '',
            'payment_method' => $invoiceData['payment_method'] ?? '',
        ]));
    }

    /**
     * Send invoice cancelled notification to user.
     *
     * @param array<string, mixed> $invoiceData
     */
    public function sendInvoiceCancelled(User $user, array $invoiceData): bool
    {
        return $this->emailService->sendToUser($user, 'user_invoice_cancelled', array_merge($invoiceData, [
            'invoice_number' => $invoiceData['invoice_number'] ?? '',
            'invoice_amount' => $invoiceData['invoice_amount'] ?? 0,
            'cancellation_reason' => $invoiceData['cancellation_reason'] ?? '',
        ]));
    }

    /**
     * Send custom invoice payment confirmation to user.
     */
    public function sendCustomInvoicePaymentConfirmation(Invoice $invoice): bool
    {
        return $this->emailService->sendToUser($invoice->user, 'custom_invoice_payment_confirmation', [
            'customer_name' => $invoice->user->name ?? '',
            'customer_email' => $invoice->user->email,
            'invoice_number' => $invoice->invoice_number,
            'service_description' => $invoice->notes ?? 'Custom Service',
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'payment_method' => ucfirst(
                is_string($invoice->metadata['gateway'] ?? null)
                    ? $invoice->metadata['gateway']
                    : 'Unknown',
            ),
            'payment_date' => $invoice->paid_at?->format('M d, Y \a\t g:i A') ?? 'Unknown',
            'transaction_id' => $invoice->metadata['transaction_id'] ?? 'N/A',
        ]);
    }

    /**
     * Send admin notification for custom invoice payment.
     */
    public function sendAdminCustomInvoicePaymentNotification(Invoice $invoice): bool
    {
        return $this->emailService->sendToAdmin('admin_custom_invoice_payment', [
            'customer_name' => $invoice->user->name ?? '',
            'customer_email' => $invoice->user->email,
            'invoice_number' => $invoice->invoice_number,
            'service_description' => $invoice->notes ?? 'Custom Service',
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'payment_method' => ucfirst(
                is_string($invoice->metadata['gateway'] ?? null)
                    ? $invoice->metadata['gateway']
                    : 'Unknown',
            ),
            'transaction_id' => $invoice->metadata['transaction_id'] ?? 'N/A',
            'payment_date' => $invoice->paid_at?->format('M d, Y \a\t g:i A') ?? 'Unknown',
        ]);
    }

    /**
     * Send payment failure notification to admin.
     */
    public function sendPaymentFailureNotification(Invoice $order): bool
    {
        return $this->emailService->sendToAdmin(
            'admin_payment_failure',
            [
                'customer_name' => $order->user->name,
                'customer_email' => $order->user->email,
                'product_name' => $order->product->name ?? '',
                'order_number' => $order->order_number,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'payment_method' => ucfirst((string)($order->payment_gateway ?? 'Unknown')),
                'failure_reason' => (is_array($order->gateway_response)
                    && isset($order->gateway_response['error']))
                    ? $order->gateway_response['error']
                    : 'Unknown error',
                'failure_date' => now()->format('M d, Y \a\t g:i A'),
            ],
        );
    }
}
