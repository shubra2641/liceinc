<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TicketNotificationTrait;
use App\Http\Requests\Admin\TicketRequest;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\Ticket\TicketManagementService;
use App\Traits\TicketHelpers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Ticket Controller with enhanced security.
 *
 * This controller handles ticket management in the admin panel,
 * including CRUD operations, replies, status updates, and invoice creation.
 * It provides comprehensive ticket management with security measures.
 *
 * Features:
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Ticket CRUD operations
 * - Ticket replies and status management
 * - Invoice creation and management
 * - Email notifications
 * - User and category management
 */
class TicketController extends Controller
{
    use TicketHelpers;
    use TicketNotificationTrait;

    public function __construct(
        private TicketManagementService $ticketService
    ) {
    }

    /**
     * Display a listing of tickets with enhanced security.
     *
     * Shows a paginated list of tickets with relationships loaded
     * and proper error handling.
     *
     * @return View The tickets index view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access tickets list:
     * GET /admin/tickets
     *
     * // Returns view with:
     * // - Paginated tickets list
     * // - User, category, and invoice relationships
     * // - Ticket management options
     */
    public function index(): View
    {
        try {
            DB::beginTransaction();
            
            $tickets = $this->ticketService->getTicketsWithRelations();
            
            DB::commit();

            return view('admin.tickets.index', ['tickets' => $tickets]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tickets listing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('admin.tickets.index', [
                'tickets' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
            ]);
        }
    }

    /**
     * Show the form for creating a new ticket.
     *
     * Displays the ticket creation form with user selection,
     * categories, and products for invoice creation.
     *
     * @return View The ticket creation form view
     *
     * @example
     * // Access the create form:
     * GET /admin/tickets/create
     *
     * // Returns view with:
     * // - User selection with licenses
     * // - Active ticket categories
     * // - Available products for invoices
     * // - Ticket form fields
     */
    public function create(): View
    {
        $users = User::with(['licenses.product'])->get();
        $categories = TicketCategory::active()->ordered()->get();
        $products = Product::all();

        return view('admin.tickets.create', ['users' => $users, 'categories' => $categories, 'products' => $products]);
    }

    /**
     * Store a newly created ticket with enhanced security.
     *
     * Creates a new ticket with comprehensive validation including
     * optional invoice creation and proper error handling.
     *
     * @param  TicketRequest  $request  The validated request containing ticket data
     *
     * @return RedirectResponse Redirect to tickets index or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * POST /admin/tickets
     * {
     *     "user_id": 1,
     *     "category_id": 1,
     *     "subject": "Support Request",
     *     "priority": "medium",
     *     "content": "Need help with installation",
     *     "create_invoice": true,
     *     "invoice_product_id": "custom",
     *     "invoice_amount": 99.99
     * }
     *
     * // Success response: Redirect to tickets index
     * // "Ticket created successfully for user."
     */
    public function store(TicketRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            $ticket = $this->ticketService->createTicket($validated, $request);
            
            DB::commit();

            return redirect()->route('admin.tickets.index')->with('success', 'Ticket created successfully for user');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create ticket', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['content']),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create ticket. Please try again.'])
                ->withInput();
        }
    }


    /**
     * Display the specified ticket.
     *
     * Shows detailed information about a specific ticket including
     * its content, replies, and related data.
     *
     * @param  Ticket  $ticket  The ticket to display
     *
     * @return View The ticket show view
     *
     * @example
     * // Access ticket details:
     * GET /admin/tickets/123
     *
     * // Returns view with:
     * // - Ticket details and content
     * // - User information and licenses
     * // - Ticket replies and history
     * // - Category and invoice information
     */
    public function show(Ticket $ticket): View
    {
        return $this->showTicket($ticket, 'admin.tickets.show', true);
    }

    /**
     * Show the form for editing the specified ticket.
     *
     * Displays the ticket editing form with pre-populated data
     * and category selection for ticket modification.
     *
     * @param  Ticket  $ticket  The ticket to edit
     *
     * @return View The ticket edit form view
     *
     * @example
     * // Access the edit form:
     * GET /admin/tickets/123/edit
     *
     * // Returns view with:
     * // - Pre-populated ticket data
     * // - Editable fields (subject, priority, status, content)
     * // - Category selection
     * // - Update form
     */
    public function edit(Ticket $ticket): View
    {
        $categories = TicketCategory::active()->ordered()->get();

        return view('admin.tickets.edit', ['ticket' => $ticket, 'categories' => $categories]);
    }

    /**
     * Update the specified ticket with enhanced security.
     *
     * Updates an existing ticket with comprehensive validation and
     * proper error handling.
     *
     * @param  TicketRequest  $request  The validated request containing ticket data
     * @param  Ticket  $ticket  The ticket to update
     *
     * @return RedirectResponse Redirect back with success or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Update request:
     * PUT /admin/tickets/123
     * {
     *     "subject": "Updated Support Request",
     *     "priority": "high",
     *     "status": "pending",
     *     "content": "Updated ticket content"
     * }
     *
     * // Success response: Redirect back
     * // "Ticket updated"
     */
    public function update(TicketRequest $request, Ticket $ticket): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            $this->ticketService->updateTicket($ticket, $validated, $request);
            
            DB::commit();

            return redirect()->route('admin.tickets.index')->with('success', 'Ticket updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update ticket', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['content']),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update ticket. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified ticket with enhanced security.
     *
     * Deletes a ticket with proper error handling and database
     * transaction management to ensure data integrity.
     *
     * @param  Ticket  $ticket  The ticket to delete
     *
     * @return RedirectResponse Redirect to tickets index or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Delete ticket:
     * DELETE /admin/tickets/123
     *
     * // Success response: Redirect to tickets list
     * // "Ticket deleted"
     *
     * // Error response: Redirect back with error
     * // "Failed to delete ticket. Please try again."
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $this->ticketService->deleteTicket($ticket);
            
            DB::commit();

            return redirect()->route('admin.tickets.index')->with('success', 'Ticket deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete ticket', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ticket_id' => $ticket->id,
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete ticket. Please try again.']);
        }
    }

    /**
     * Add a reply to the specified ticket with enhanced security.
     *
     * Creates a new ticket reply with comprehensive validation and
     * email notification to the user.
     *
     * @param  TicketRequest  $request  The validated request containing reply data
     * @param  Ticket  $ticket  The ticket to reply to
     *
     * @return RedirectResponse Redirect back with success or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Reply request:
     * POST /admin/tickets/123/reply
     * {
     *     "message": "Thank you for your inquiry. We will help you resolve this issue."
     * }
     *
     * // Success response: Redirect back
     * // "Reply added"
     */
    public function reply(TicketRequest $request, Ticket $ticket): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            $this->ticketService->addReply($ticket, $validated);
            
            DB::commit();

            return redirect()->back()->with('success', 'Reply added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add reply', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ticket_id' => $ticket->id,
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to add reply. Please try again.']);
        }
    }

    /**
     * Update ticket status with enhanced security.
     *
     * Updates the status of a ticket with comprehensive validation and
     * email notification to the user.
     *
     * @param  TicketRequest  $request  The validated request containing status data
     * @param  Ticket  $ticket  The ticket to update
     *
     * @return RedirectResponse Redirect back with success or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Status update request:
     * POST /admin/tickets/123/status
     * {
     *     "status": "resolved"
     * }
     *
     * // Success response: Redirect back
     * // "Ticket status updated to Resolved"
     */
    public function updateStatus(TicketRequest $request, Ticket $ticket): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            $this->ticketService->updateTicketStatus($ticket, $validated);
            
            DB::commit();

            $status = $ticket->status ?? 'open';
            return back()->with('success', 'Ticket status updated to ' . ucfirst($status));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update ticket status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ticket_id' => $ticket->id,
                'request_data' => $request->all(),
            ]);

            return back()->withErrors(['status' => 'Error updating ticket status: ' . $e->getMessage()]);
        }
    }
}
