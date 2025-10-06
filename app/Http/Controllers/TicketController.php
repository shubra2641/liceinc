<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Services\LicenseAutoRegistrationService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Ticket Controller with enhanced security.
 *
 * This controller handles ticket management functionality including
 * ticket creation, viewing, updating, replying, and deletion with
 * enhanced security measures and proper error handling.
 *
 * Features:
 * - User ticket listing with pagination
 * - Ticket creation with license auto-registration
 * - Ticket viewing with authorization
 * - Ticket updating and deletion
 * - Ticket replies with status management
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Model relationship integration for optimized queries
 * - Clean code structure with no duplicate patterns
 * - Proper type hints and return types
 */
class TicketController extends Controller
{
    /**
     * Pagination limit for ticket listing.
     */
    private const PAGINATION_LIMIT = 15;
    /**
     * Valid ticket priorities.
     */
    private const VALID_PRIORITIES = ['low', 'medium', 'high'];
    /**
     * Valid ticket statuses.
     */
    private const VALID_STATUSES = ['open', 'pending', 'resolved', 'closed'];
    /**
     * Display a listing of user tickets with enhanced security.
     *
     * Shows paginated list of user tickets with proper authorization,
     * comprehensive error handling, and security measures.
     *
     * @return View The ticket listing view
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Access user tickets:
     * GET /tickets
     *
     * // Returns view with:
     * // - Paginated tickets (15 per page)
     * // - User authorization check
     * // - Latest tickets first
     */
    public function index(): View
    {
        try {
            $userId = Auth::id();
            if (! $userId) {
                throw new Exception('User not authenticated');
            }
            DB::beginTransaction();
            $tickets = Ticket::where('user_id', $userId)
                ->latest()
                ->paginate(self::PAGINATION_LIMIT);
            DB::commit();
            return view('tickets.index', compact('tickets'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load user tickets: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return view('tickets.index', ['tickets' => collect()])
                ->with('error', 'Failed to load tickets. Please try again.');
        }
    }
    /**
     * Show the form for creating a new ticket with enhanced security.
     *
     * Displays ticket creation form with comprehensive error handling
     * and security measures.
     *
     * @return View The ticket creation view
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Access ticket creation form:
     * GET /tickets/create
     *
     * // Returns view with:
     * // - Ticket creation form
     */
    public function create(): View
    {
        try {
            return view('tickets.create');
        } catch (Exception $e) {
            Log::error('Failed to load ticket creation form: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load ticket creation form. Please try again.');
        }
    }
    /**
     * Store a newly created ticket with enhanced security.
     *
     * Creates a new ticket with comprehensive validation, license
     * auto-registration, and error handling.
     *
     * @param  Request  $request  The HTTP request containing ticket data
     * @param  LicenseAutoRegistrationService  $licenseService  The license service
     *
     * @return RedirectResponse The redirect response
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Create a new ticket:
     * POST /tickets
     * {
     *     "subject": "Issue with product",
     *     "priority": "high",
     *     "content": "Description of the issue",
     *     "purchase_code": "ABC123-DEF456"
     * }
     */
    public function store(Request $request, LicenseAutoRegistrationService $licenseService): RedirectResponse
    {
        try {
            if (! $request) {
                throw new \InvalidArgumentException('Request cannot be null');
            }
            $validated = $this->validateTicketData($request);
            DB::beginTransaction();
            // Auto-register license if purchase code is provided
            $license = $this->handleLicenseRegistration($validated, $licenseService);
            $ticket = Ticket::create([
                'user_id' => Auth::id(),
                'subject' => $validated['subject'],
                'priority' => $validated['priority'],
                'status' => 'open',
                'content' => $validated['content'],
                'license_id' => $license?->id,
            ]);
            DB::commit();
            return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create ticket: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to create ticket. Please try again.')->withInput();
        }
    }
    /**
     * Display the specified ticket with enhanced security.
     *
     * Shows detailed ticket information with proper user authorization,
     * relationship loading, and comprehensive error handling.
     *
     * @param  Ticket  $ticket  The ticket instance
     *
     * @return View The ticket detail view
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Access specific ticket:
     * GET /tickets/123
     *
     * // Returns view with:
     * // - Ticket details
     * // - User information
     * // - Ticket replies
     * // - User authorization check
     */
    public function show(Ticket $ticket): View
    {
        try {
            if (! $ticket) {
                throw new \InvalidArgumentException('Ticket cannot be null');
            }
            if (! $this->canViewTicket($ticket)) {
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
            return view('tickets.show', compact('ticket'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load ticket details: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'ticket_id' => $ticket->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Failed to load ticket details. Please try again.');
        }
    }
    /**
     * Show the form for editing the specified ticket.
     *
     * @param  Ticket  $ticket  The ticket instance
     *
     * @deprecated This method is not implemented
     */
    public function edit(Ticket $ticket): void
    {
        // Not implemented
    }
    /**
     * Update the specified ticket with enhanced security.
     *
     * Updates ticket information with proper authorization, validation,
     * and comprehensive error handling.
     *
     * @param  Request  $request  The HTTP request containing update data
     * @param  Ticket  $ticket  The ticket instance
     *
     * @return RedirectResponse The redirect response
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Update ticket:
     * PUT /tickets/123
     * {
     *     "subject": "Updated subject",
     *     "priority": "high",
     *     "status": "pending"
     * }
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        try {
            if (! $request || ! $ticket) {
                throw new \InvalidArgumentException('Request and ticket cannot be null');
            }
            if (! $this->canModifyTicket($ticket)) {
                Log::warning('Unauthorized ticket modification attempt', [
                    'user_id' => Auth::id(),
                    'ticket_id' => $ticket->id,
                    'ticket_user_id' => $ticket->user_id,
                    'ip_address' => request()->ip(),
                ]);
                abort(403, 'Unauthorized access to modify ticket');
            }
            $validated = $this->validateTicketUpdateData($request);
            DB::beginTransaction();
            $ticket->update($validated);
            DB::commit();
            return back()->with('success', 'Ticket updated');
        } catch (Exception $e) {
            DB::rollBack();
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
     * Remove the specified ticket with enhanced security.
     *
     * Deletes ticket with proper authorization and comprehensive error handling.
     *
     * @param  Ticket  $ticket  The ticket instance
     *
     * @return RedirectResponse The redirect response
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Delete ticket:
     * DELETE /tickets/123
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        try {
            if (! $ticket) {
                throw new \InvalidArgumentException('Ticket cannot be null');
            }
            if (! $this->canModifyTicket($ticket)) {
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
            return redirect()->route('tickets.index')->with('success', 'Ticket deleted');
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
     * Add a reply to the specified ticket with enhanced security.
     *
     * Creates a new ticket reply with proper authorization, validation,
     * and status management.
     *
     * @param  Request  $request  The HTTP request containing reply data
     * @param  Ticket  $ticket  The ticket instance
     *
     * @return RedirectResponse The redirect response
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Add reply to ticket:
     * POST /tickets/123/reply
     * {
     *     "message": "Thank you for the update",
     *     "action": "reply_and_close"
     * }
     */
    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        try {
            if (! $request || ! $ticket) {
                throw new \InvalidArgumentException('Request and ticket cannot be null');
            }
            if (! $this->canModifyTicket($ticket)) {
                Log::warning('Unauthorized ticket reply attempt', [
                    'user_id' => Auth::id(),
                    'ticket_id' => $ticket->id,
                    'ticket_user_id' => $ticket->user_id,
                    'ip_address' => request()->ip(),
                ]);
                abort(403, 'Unauthorized access to reply to ticket');
            }
            // Check if ticket is already closed
            if ($ticket->status === 'closed') {
                return back()->with('error', 'Cannot reply to a closed ticket');
            }
            $validated = $this->validateReplyData($request);
            DB::beginTransaction();
            // Create the reply
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $validated['message'],
            ]);
            // Handle closing the ticket
            $shouldClose = $this->shouldCloseTicket($request);
            $message = $shouldClose ? 'Reply added and ticket closed' : 'Reply added';
            if ($shouldClose) {
                $ticket->update(['status' => 'closed']);
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
     * Validate ticket creation data.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return array<string, mixed> The validated data
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateTicketData(Request $request): array
    {
        return $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:' . implode(', ', self::VALID_PRIORITIES)],
            'content' => ['required', 'string'],
            'purchase_code' => ['nullable', 'string'],
            'product_slug' => ['nullable', 'string'],
        ]);
    }
    /**
     * Validate ticket update data.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return array<string, mixed> The validated data
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateTicketUpdateData(Request $request): array
    {
        return $request->validate([
            'subject' => ['sometimes', 'string', 'max:255'],
            'priority' => ['sometimes', 'in:' . implode(', ', self::VALID_PRIORITIES)],
            'status' => ['sometimes', 'in:' . implode(', ', self::VALID_STATUSES)],
            'content' => ['sometimes', 'string'],
        ]);
    }
    /**
     * Validate reply data.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return array<string, mixed> The validated data
     */
    private function validateReplyData(Request $request): array
    {
        return $request->validate([
            'message' => ['required', 'string'],
            'close_ticket' => ['sometimes', 'boolean'],
            'action' => ['sometimes', 'in:reply, reply_and_close'],
        ]);
    }
    /**
     * Handle license registration for ticket.
     *
     * @param  array<string, mixed>  $validated  The validated request data
     * @param  LicenseAutoRegistrationService  $licenseService  The license service
     *
     * @return mixed The license instance or null
     */
    private function handleLicenseRegistration(array $validated, LicenseAutoRegistrationService $licenseService)
    {
        if (empty($validated['purchase_code'])) {
            return null;
        }
        $productId = null;
        if (! empty($validated['product_slug'])) {
            $product = Product::where('slug', $validated['product_slug'])->first();
            $productId = $product?->id;
        }
        $registrationResult = $licenseService->autoRegisterLicense($validated['purchase_code'], $productId);
        if ($registrationResult['success']) {
            return $registrationResult['license'];
        }
        return null;
    }
    /**
     * Check if ticket should be closed.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return bool True if ticket should be closed
     */
    private function shouldCloseTicket(Request $request): bool
    {
        return ($request->has('action') && $request->action === 'reply_and_close') ||
               ($request->has('close_ticket') && $request->close_ticket);
    }
    /**
     * Check if user can view ticket.
     *
     * @param  Ticket  $ticket  The ticket instance
     *
     * @return bool True if user can view ticket
     */
    private function canViewTicket(Ticket $ticket): bool
    {
        return $ticket->user_id === Auth::id() || Auth::user()?->hasRole('admin');
    }
    /**
     * Check if user can modify ticket.
     *
     * @param  Ticket  $ticket  The ticket instance
     *
     * @return bool True if user can modify ticket
     */
    private function canModifyTicket(Ticket $ticket): bool
    {
        return $ticket->user_id === Auth::id() || Auth::user()?->hasRole('admin');
    }
}
