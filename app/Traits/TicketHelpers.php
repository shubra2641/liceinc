<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;

/**
 * Ticket Helpers Trait
 *
 * Provides common ticket functionality to eliminate code duplication
 * across different ticket controllers.
 */
trait TicketHelpers
{
    /**
     * Handle ticket creation with error handling.
     *
     * @param array $ticketData
     * @param string $successRoute
     * @param string $successMessage
     * @return RedirectResponse
     */
    protected function handleTicketCreation(
        array $ticketData,
        string $successRoute,
        string $successMessage = 'Ticket created successfully'
    ): RedirectResponse {
        try {
            DB::beginTransaction();
            $ticket = Ticket::create($ticketData);
            DB::commit();
            return redirect()->route($successRoute, $ticket)
                ->with('success', $successMessage);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create ticket: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'ticket_data' => $ticketData,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to create ticket. Please try again.')
                ->withInput();
        }
    }

    /**
     * Handle ticket display with security checks.
     *
     * @param Ticket $ticket
     * @param string $viewName
     * @return View
     */
    protected function handleTicketDisplay(Ticket $ticket, string $viewName): View
    {
        try {
            if (!$this->canViewTicket($ticket)) {
                Log::warning('Unauthorized ticket access attempt', [
                    'user_id' => Auth::id(),
                    'ticket_id' => $ticket->id,
                    'ticket_user_id' => $ticket->user_id,
                    'ip_address' => request()->ip(),
                ]);
                abort(403, 'Unauthorized access to ticket');
            }

            DB::beginTransaction();
            $ticket->load(['user', 'replies.user']);
            DB::commit();
            return view($viewName, ['ticket' => $ticket]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load ticket details: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'ticket_id' => $ticket->id,
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Failed to load ticket details');
        }
    }

    /**
     * Handle ticket update with error handling.
     *
     * @param Ticket $ticket
     * @param array $updateData
     * @param string $successMessage
     * @return RedirectResponse
     */
    protected function handleTicketUpdate(
        Ticket $ticket,
        array $updateData,
        string $successMessage = 'Ticket updated successfully'
    ): RedirectResponse {
        try {
            DB::beginTransaction();
            $ticket->update($updateData);
            DB::commit();
            return back()->with('success', $successMessage);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update ticket: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'ticket_id' => $ticket->id,
                'update_data' => $updateData,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to update ticket. Please try again.');
        }
    }

    /**
     * Get tickets with pagination and filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return Collection
     */
    protected function getTicketsWithFilters(array $filters = [], int $perPage = 15): Collection
    {
        $query = Ticket::with(['user', 'replies'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }
}
