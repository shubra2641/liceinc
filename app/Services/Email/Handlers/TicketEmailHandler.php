<?php

declare(strict_types=1);

namespace App\Services\Email\Handlers;

use App\Models\User;
use App\Services\Email\Contracts\EmailServiceInterface;
use App\Services\Email\Contracts\EmailValidatorInterface;
use App\Services\Email\Traits\EmailLoggingTrait;
use App\Services\Email\Traits\EmailValidationTrait;

/**
 * Ticket Email Handler.
 *
 * Handles ticket-related email operations with enhanced security.
 *
 * @version 1.0.0
 */
class TicketEmailHandler
{
    use EmailValidationTrait;
    use EmailLoggingTrait;

    public function __construct(
        protected EmailServiceInterface $emailService,
        protected EmailValidatorInterface $validator,
    ) {
    }

    /**
     * Send support ticket created notification to user.
     *
     * @param array<string, mixed> $ticketData
     */
    public function sendTicketCreated(User $user, array $ticketData): bool
    {
        $ticketId = $ticketData['ticket_id'] ?? '';
        $ticketSubject = $ticketData['ticket_subject'] ?? '';
        $ticketStatus = $ticketData['ticket_status'] ?? 'open';

        return $this->emailService->sendToUser($user, 'user_ticket_created', array_merge($ticketData, [
            'ticket_id' => is_string($ticketId) ? $ticketId : '',
            'ticket_subject' => is_string($ticketSubject) ? $ticketSubject : '',
            'ticket_status' => is_string($ticketStatus) ? $ticketStatus : 'open',
        ]));
    }

    /**
     * Send support ticket status update notification to user.
     *
     * @param array<string, mixed> $ticketData
     */
    public function sendTicketStatusUpdate(User $user, array $ticketData): bool
    {
        return $this->emailService->sendToUser($user, 'user_ticket_status_update', array_merge($ticketData, [
            'ticket_id' => $ticketData['ticket_id'] ?? '',
            'ticket_subject' => $ticketData['ticket_subject'] ?? '',
            'old_status' => $ticketData['old_status'] ?? '',
            'new_status' => $ticketData['new_status'] ?? '',
        ]));
    }

    /**
     * Send support ticket reply notification to user.
     *
     * @param array<string, mixed> $ticketData
     */
    public function sendTicketReply(User $user, array $ticketData): bool
    {
        return $this->emailService->sendToUser($user, 'user_ticket_reply', array_merge($ticketData, [
            'ticket_id' => $ticketData['ticket_id'] ?? '',
            'ticket_subject' => $ticketData['ticket_subject'] ?? '',
            'reply_message' => $ticketData['reply_message'] ?? '',
            'replied_by' => $ticketData['replied_by'] ?? 'Support Team',
        ]));
    }

    /**
     * Send admin notification for support ticket created.
     *
     * @param array<string, mixed> $ticketData
     */
    public function sendAdminTicketCreated(array $ticketData): bool
    {
        $ticketId = $ticketData['ticket_id'] ?? '';
        $ticketSubject = $ticketData['ticket_subject'] ?? '';
        $customerName = $ticketData['customer_name'] ?? '';
        $customerEmail = $ticketData['customer_email'] ?? '';
        $ticketPriority = $ticketData['ticket_priority'] ?? 'normal';

        return $this->emailService->sendToAdmin('admin_ticket_created', array_merge($ticketData, [
            'ticket_id' => is_string($ticketId) ? $ticketId : '',
            'ticket_subject' => is_string($ticketSubject) ? $ticketSubject : '',
            'customer_name' => is_string($customerName) ? $customerName : '',
            'customer_email' => is_string($customerEmail) ? $customerEmail : '',
            'ticket_priority' => is_string($ticketPriority) ? $ticketPriority : 'normal',
        ]));
    }

    /**
     * Send admin notification for ticket reply from user.
     *
     * @param array<string, mixed> $ticketData
     */
    public function sendAdminTicketReply(array $ticketData): bool
    {
        return $this->emailService->sendToAdmin('admin_ticket_reply', array_merge($ticketData, [
            'ticket_id' => $ticketData['ticket_id'] ?? '',
            'ticket_subject' => $ticketData['ticket_subject'] ?? '',
            'customer_name' => $ticketData['customer_name'] ?? '',
            'customer_email' => $ticketData['customer_email'] ?? '',
            'reply_message' => $ticketData['reply_message'] ?? '',
        ]));
    }

    /**
     * Send admin notification for ticket closed by user.
     *
     * @param array<string, mixed> $ticketData
     */
    public function sendAdminTicketClosed(array $ticketData): bool
    {
        return $this->emailService->sendToAdmin('admin_ticket_closed', array_merge($ticketData, [
            'ticket_id' => $ticketData['ticket_id'] ?? '',
            'ticket_subject' => $ticketData['ticket_subject'] ?? '',
            'customer_name' => $ticketData['customer_name'] ?? '',
            'customer_email' => $ticketData['customer_email'] ?? '',
            'closure_reason' => $ticketData['closure_reason'] ?? '',
        ]));
    }
}
