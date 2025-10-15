<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Services\LicenseAutoRegistrationService;
use App\Traits\TicketHelpers;
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
    use TicketHelpers;

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
        /**
* @var view-string $viewName
*/
        $viewName = 'tickets.index';
        return view($viewName, ['tickets' => $tickets]);
    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Failed to load user tickets: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'trace' => $e->getTraceAsString(),
        ]);
        /**
* @var view-string $viewName
*/
        $viewName = 'tickets.index';
        return view($viewName, ['tickets' => collect()])
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
public function create(): View|RedirectResponse
{
    try {
        /**
* @var view-string $viewName
*/
        $viewName = 'tickets.create';
        return view($viewName);
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
        $validated = $this->validateTicketData($request);
        $license = $this->handleLicenseRegistration($validated, $licenseService);

        $ticketData = [
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'content' => $validated['content'],
            'license_id' => $license instanceof \App\Models\License ? $license->id : null,
        ];

        return $this->handleTicketCreation($ticketData, 'tickets.show');
    } catch (Exception $e) {
        Log::error('Failed to create ticket: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString(),
        ]);
        return back()->with('error', 'Failed to create ticket. Please try again.')
            ->withInput();
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
    return $this->showTicket($ticket, 'tickets.show', false);
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
    return $this->updateTicket($request, $ticket, false);
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
    return $this->destroyTicket($ticket, false, 'tickets.index');
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
    return $this->replyToTicket($request, $ticket, false, true, false);
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
    $registrationResult = $licenseService->autoRegisterLicense(
        is_string($validated['purchase_code']) ? $validated['purchase_code'] : '',
        $productId
    );
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
