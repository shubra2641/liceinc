<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Services\LicenseAutoRegistrationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
    public function createTicket(array $data): Ticket
    {
        return DB::transaction(function () use ($data) {
            // Auto-register license if provided
            if (!empty($data['license_key'])) {
                $licenseKey = $data['license_key'];
                $domain = $data['domain'] ?? '';
                $this->licenseService->autoRegisterLicense(
                    $licenseKey,
                    $domain
                );
            }

            return Ticket::create([
                'user_id' => Auth::id(),
                'subject' => $data['subject'],
                'description' => $data['description'],
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
                'product_id' => $data['product_id'] ?? null,
                'license_key' => $data['license_key'] ?? null,
                'domain' => $data['domain'] ?? null,
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
