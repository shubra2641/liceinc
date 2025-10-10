<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Services\LicenseAutoRegistrationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Ticket Service for handling ticket operations.
 */
class TicketService
{
    public function __construct(
        private LicenseAutoRegistrationService $licenseService
    ) {
    }

    /**
     * Create a new ticket.
     */
    public function createTicket(array<string, mixed> $data): Ticket
    {
        return DB::transaction(function () use ($data) {
            // Auto-register license if provided
            if (!empty($data['license_key'])) {
                $this->licenseService->autoRegisterLicense(
                    (string) $data['license_key'],
                    (string) ($data['domain'] ?? ''),
                    Auth::id()
                );
            }

            return Ticket::create([
                'user_id' => Auth::id(),
                'subject' => (string) $data['subject'],
                'description' => (string) $data['description'],
                'priority' => (string) ($data['priority'] ?? 'medium'),
                'status' => 'open',
                'product_id' => isset($data['product_id']) ? (int) $data['product_id'] : null,
                'license_key' => isset($data['license_key']) ? (string) $data['license_key'] : null,
                'domain' => isset($data['domain']) ? (string) $data['domain'] : null,
            ]);
        });
    }

    /**
     * Update ticket status.
     */
    public function updateTicketStatus(Ticket $ticket, string $status): bool
    {
        $validStatuses = ['open', 'in_progress', 'closed', 'resolved'];

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $ticket->update(['status' => $status]);
        return true;
    }

    /**
     * Add reply to ticket.
     */
    public function addReply(Ticket $ticket, string $message, bool $isAdmin = false): TicketReply
    {
        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $message,
            'is_admin' => $isAdmin,
        ]);

        // Update ticket status if admin replied
        if ($isAdmin && $ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return $reply;
    }

    /**
     * Get user tickets with pagination.
     */
    public function getUserTickets(int $userId, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Ticket::where('user_id', $userId)
            ->with(['replies'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get ticket with authorization check.
     */
    public function getTicket(int $ticketId, int $userId): ?Ticket
    {
        return Ticket::where('id', $ticketId)
            ->where('user_id', $userId)
            ->with(['replies.user'])
            ->first();
    }

    /**
     * Delete ticket.
     */
    public function deleteTicket(Ticket $ticket, int $userId): bool
    {
        if ($ticket->user_id !== $userId) {
            return false;
        }

        return (bool) $ticket->delete();
    }
}
