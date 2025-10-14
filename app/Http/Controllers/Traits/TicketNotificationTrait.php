<?php

namespace App\Http\Controllers\Traits;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

trait TicketNotificationTrait
{
    /**
     * Send ticket creation notifications.
     *
     * @param Ticket $ticket The created ticket
     */
    protected function sendTicketNotifications(Ticket $ticket): void
    {
        try {
            // Send notification to user (if has user and email)
            if ($ticket->user && $ticket->user->email) {
                $this->emailService->sendTicketCreated($ticket->user, [
                    'ticket_id' => $ticket->id,
                    'ticket_subject' => $ticket->subject,
                    'ticket_status' => $ticket->status,
                ]);
            }
            
            // Send notification to admin
            $this->emailService->sendAdminTicketCreated([
                'ticket_id' => $ticket->id,
                'ticket_subject' => $ticket->subject,
                'customer_name' => $ticket->user ? $ticket->user->name : 'Guest User',
                'customer_email' => $ticket->user ? $ticket->user->email : 'No email provided',
                'ticket_priority' => $ticket->priority,
            ]);
        } catch (\Exception $e) {
            // Silent fail in production - log error for debugging
            Log::error('Failed to send ticket notifications', ['error' => $e->getMessage()]);
        }
    }
}
