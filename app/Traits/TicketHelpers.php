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
     * 
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
     * 
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
     * Handle ticket update with authorization check.
     *
     * @param \Illuminate\Http\Request $request
     * @param Ticket $ticket
     * @param bool $isAdmin
     * @return RedirectResponse
     */
    protected function updateTicket(\Illuminate\Http\Request $request, Ticket $ticket, bool $isAdmin = false): RedirectResponse
    {
        try {
            if (!$isAdmin && !$this->canModifyTicket($ticket)) {
                Log::warning('Unauthorized ticket modification attempt', [
                    'user_id' => Auth::id(),
                    'ticket_id' => $ticket->id,
                    'ticket_user_id' => $ticket->user_id,
                    'ip_address' => request()->ip(),
                ]);
                abort(403, 'Unauthorized access to modify ticket');
            }

            $validated = $this->validateTicketUpdateData($request);
            return $this->handleTicketUpdate($ticket, $validated);
        } catch (Exception $e) {
            Log::error('Failed to update ticket: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'ticket_id' => $ticket->id ?? null,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to update ticket. Please try again.');
        }
    }

    /**
     * Handle ticket deletion with authorization check.
     *
     * @param Ticket $ticket
     * @param bool $isAdmin
     * @param string $redirectRoute
     * @return RedirectResponse
     */
    protected function destroyTicket(Ticket $ticket, bool $isAdmin = false, string $redirectRoute = 'tickets.index'): RedirectResponse
    {
        try {
            if (!$isAdmin && !$this->canModifyTicket($ticket)) {
                Log::warning('Unauthorized ticket deletion attempt', [
                    'user_id' => Auth::id(),
                    'ticket_id' => $ticket->id,
                    'ticket_user_id' => $ticket->user_id,
                    'ip_address' => request()->ip(),
                ]);
                abort(403, 'Unauthorized access to delete ticket');
            }
            DB::beginTransaction();
            $ticket->delete();
            DB::commit();
            return redirect()->route($redirectRoute)->with('success', 'Ticket deleted');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete ticket: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'ticket_id' => $ticket->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to delete ticket. Please try again.');
        }
    }

    /**
     * Handle ticket reply with authorization check.
     *
     * @param \Illuminate\Http\Request $request
     * @param Ticket $ticket
     * @param bool $sendNotification
     * @param bool $allowClose
     * @param bool $isAdmin
     * @return RedirectResponse
     */
    protected function replyToTicket(
        \Illuminate\Http\Request $request,
        Ticket $ticket,
        bool $sendNotification = false,
        bool $allowClose = false,
        bool $isAdmin = false
    ): RedirectResponse {
        try {
            if (!$isAdmin && !$this->canModifyTicket($ticket)) {
                Log::warning('Unauthorized ticket reply attempt', [
                    'user_id' => Auth::id(),
                    'ticket_id' => $ticket->id,
                    'ticket_user_id' => $ticket->user_id,
                    'ip_address' => request()->ip(),
                ]);
                abort(403, 'Unauthorized access to reply to ticket');
            }

            if ($ticket->status === 'closed') {
                return back()->with('error', 'Cannot reply to a closed ticket');
            }

            $validated = $this->validateReplyData($request);
            DB::beginTransaction();

            $reply = \App\Models\TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $validated['message'],
            ]);

            $shouldClose = $allowClose && $this->shouldCloseTicket($request);
            $message = $shouldClose ? 'Reply added and ticket closed' : 'Reply added';

            if ($shouldClose) {
                $ticket->update(['status' => 'closed']);
            }

            if ($sendNotification && method_exists($this, 'sendReplyNotification')) {
                $this->sendReplyNotification($ticket, $validated['message']);
            }

            // Send email notification for admin replies
            if ($isAdmin && $ticket->user && method_exists($this, 'emailService')) {
                try {
                    $user = $request->user();
                    $this->emailService->sendTicketReply($ticket->user, [
                        'ticket_id' => $ticket->id,
                        'ticket_subject' => $ticket->subject,
                        'reply_message' => $validated['message'],
                        'replied_by' => $user->name ?? 'Admin',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send ticket reply email', [
                        'error' => $e->getMessage(),
                        'ticket_id' => $ticket->id,
                        'user_id' => $ticket->user_id,
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', $message);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to add ticket reply: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'ticket_id' => $ticket->id ?? null,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to add reply. Please try again.');
        }
    }

    /**
     * Handle ticket display with security checks.
     *
     * @param Ticket $ticket
     * @param string $viewName
     * @param bool $isAdmin
     * @return View
     */
    protected function showTicket(Ticket $ticket, string $viewName, bool $isAdmin = false): View
    {
        try {
            if (!$isAdmin && !$this->canViewTicket($ticket)) {
                Log::warning('Unauthorized ticket access attempt', [
                    'user_id' => Auth::id(),
                    'ticket_id' => $ticket->id,
                    'ticket_user_id' => $ticket->user_id,
                    'ip_address' => request()->ip(),
                ]);
                abort(403, 'Unauthorized access to ticket');
            }

            DB::beginTransaction();
            if ($isAdmin) {
                $ticket->load(['user.licenses.product', 'replies.user', 'category', 'invoice.product']);
            } else {
                $ticket->load(['user', 'replies.user']);
            }
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
     * Validate ticket update data.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function validateTicketUpdateData(\Illuminate\Http\Request $request): array
    {
        $validated = $request->validate([
            'subject' => ['sometimes', 'string', 'max:255'],
            'priority' => ['sometimes', 'in:' . implode(', ', $this->getValidPriorities())],
            'status' => ['sometimes', 'in:' . implode(', ', $this->getValidStatuses())],
            'content' => ['sometimes', 'string'],
        ]);

        return $validated;
    }

    /**
     * Get valid ticket priorities.
     *
     * @return array
     */
    protected function getValidPriorities(): array
    {
        return ['low', 'medium', 'high', 'urgent'];
    }

    /**
     * Get valid ticket statuses.
     *
     * @return array
     */
    protected function getValidStatuses(): array
    {
        return ['open', 'pending', 'closed'];
    }

    /**
     * Validate reply data.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function validateReplyData(\Illuminate\Http\Request $request): array
    {
        return $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:10000'],
            'action' => ['nullable', 'in:reply_and_close'],
        ]);
    }

    /**
     * Check if ticket should be closed based on request.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function shouldCloseTicket(\Illuminate\Http\Request $request): bool
    {
        return $request->input('action') === 'reply_and_close';
    }

    /**
     * Check if user can modify the ticket.
     *
     * @param Ticket $ticket
     * @return bool
     */
    protected function canModifyTicket(Ticket $ticket): bool
    {
        return Auth::id() === $ticket->user_id || Auth::user()?->hasRole('admin');
    }

    /**
     * Check if user can view the ticket.
     *
     * @param Ticket $ticket
     * @return bool
     */
    protected function canViewTicket(Ticket $ticket): bool
    {
        // Allow viewing if user owns the ticket or is admin
        // For guest tickets (user_id is null), allow viewing
        return !$ticket->user_id || // Guest ticket
               Auth::id() === $ticket->user_id ||
               Auth::user()?->hasRole('admin');
    }

    /**
     * Get tickets with pagination and filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return Collection
     * 
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
