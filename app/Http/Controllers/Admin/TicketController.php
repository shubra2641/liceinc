<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketRequest;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\EmailService;
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
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
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
            $tickets = Ticket::with(['user', 'category', 'invoice.product'])->latest()->paginate(10);
            DB::commit();

            return view('admin.tickets.index', ['tickets' => $tickets]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tickets listing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty results on error
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
     *     "userId": 1,
     *     "category_id": 1,
     *     "subject": "Support Request",
     *     "priority": "medium",
     *     "content": "Need help with installation",
     *     "create_invoice": true,
     *     "invoice_productId": "custom",
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
            // Build ticket data
            $ticketData = [
                'userId' => $validated['userId'],
                'category_id' => $validated['category_id'],
                'subject' => $validated['subject'],
                'priority' => $validated['priority'],
                'content' => $validated['content'],
                'status' => 'open',
            ];
            // If admin requested to create an invoice for the user
            if ($request->filled('create_invoice') && $request->boolean('create_invoice')) {
                $invoiceProductId = $request->input('invoice_productId');
                $billingType = $request->input('billing_type', 'one_time');
                // If custom invoice selected
                if ($invoiceProductId === 'custom' || empty($invoiceProductId)) {
                    $amount = $validated['invoice_amount'] ?? 0;
                    $duration = $validated['invoice_durationDays'] ?? 0;
                    $dueDate = now()->addDays(is_numeric($duration) ? (int)$duration : 0)->toDateString();
                    $metadata = [];
                    if ($billingType !== 'one_time') {
                        if ($billingType === 'custom_recurring') {
                            $metadata['renewalPrice'] = $request->input('invoice_renewalPrice')
                                ?: $amount;
                            $metadata['renewalPeriod_days'] = $request->input('invoice_renewalPeriod_days')
                                ?: $duration;
                            $metadata['recurrence'] = 'custom';
                        } else {
                            $map = [
                                'monthly' => 30,
                                'quarterly' => 90,
                                'semi_annual' => 182,
                                'annual' => 365,
                            ];
                            $metadata['recurrence'] = $billingType;
                            $metadata['renewalPeriod_days'] = (is_string($billingType) && isset($map[$billingType]))
                                ? $map[$billingType]
                                : $duration;
                            $metadata['renewalPrice'] = $amount;
                        }
                    }
                } else {
                    // Product-based invoice
                    $product = Product::find($invoiceProductId);
                    if (! $product) {
                        DB::rollBack();

                        return back()->withErrors(['invoice_productId' => 'Invalid product selected'])->withInput();
                    }
                    $amount = $request->input('invoice_amount') ?: $product->price;
                    $duration = $request->input('invoice_durationDays') ?: $product->durationDays ?: null;
                    $dueDate = $request->input('invoice_due_date')
                        ?: ($duration
                            ? now()->addDays(is_numeric($duration) ? (int)$duration : 0)->toDateString()
                            : null);
                    $metadata = [];
                    if ($billingType !== 'one_time') {
                        $map = [
                            'monthly' => 30,
                            'quarterly' => 90,
                            'semi_annual' => 182,
                            'annual' => 365,
                        ];
                        if ($billingType === 'custom_recurring') {
                            $metadata['recurrence'] = 'custom';
                            $metadata['renewalPrice'] = $request->input('invoice_renewalPrice')
                                ?: $product->renewalPrice ?? $amount;
                            $metadata['renewalPeriod_days'] = $request->input('invoice_renewalPeriod_days')
                                ?: $product->renewalPeriod ?: ($duration ?: 30);
                        } else {
                            $metadata['recurrence'] = $billingType;
                            $metadata['renewalPeriod_days'] = (is_string($billingType)
                                && array_key_exists($billingType, $map))
                                ? $map[$billingType] : ($product->renewalPeriod ?? $duration);
                            $metadata['renewalPrice'] = $product->renewalPrice ?? $amount;
                        }
                    }
                }
                $invoice = Invoice::create([
                    'userId' => $validated['userId'],
                    'productId' => is_numeric($invoiceProductId) ? $invoiceProductId : null,
                    'amount' => $amount,
                    'status' => $request->input('invoice_status') ?? 'pending',
                    'due_date' => $dueDate,
                    'notes' => $request->input('invoice_notes') ?? null,
                    'currency' => config('app.currency', 'USD'),
                    'type' => ($billingType && $billingType !== 'one_time') ? 'recurring' : 'one_time',
                    'metadata' => $metadata,
                ]);
                $ticketData['invoice_id'] = $invoice->id;
            }
            Ticket::create($ticketData);
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
        $ticket->load(['user.licenses.product', 'replies.user', 'category', 'invoice.product']);

        return view('admin.tickets.show', ['ticket' => $ticket]);
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
            $ticket->update($validated);
            DB::commit();

            return back()->with('success', 'Ticket updated');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update ticket', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ticket_id' => $ticket->id,
                'request_data' => $request->except(['content']),
            ]);

            return back()->withErrors(['error' => 'Failed to update ticket. Please try again.']);
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
            $ticket->delete();
            DB::commit();

            return redirect()->route('admin.tickets.index')->with('success', 'Ticket deleted');
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
            $user = $request->user();
            if ($user) {
                $reply = TicketReply::create([
                    'ticket_id' => $ticket->id,
                    'userId' => $user->id,
                    'message' => $validated['message'],
                ]);
            }
            // Send email notification to user when admin replies
            try {
                if ($ticket->user) {
                    $this->emailService->sendTicketReply($ticket->user, [
                        'ticket_id' => $ticket->id,
                        'ticket_subject' => $ticket->subject,
                        'reply_message' => $validated['message'],
                        'replied_by' => $user->name ?? 'Admin',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send ticket reply email', [
                    'error' => $e->getMessage(),
                    'ticket_id' => $ticket->id,
                    'userId' => $ticket->userId,
                ]);
            }
            DB::commit();

            return back()->with('success', 'Reply added');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add ticket reply', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ticket_id' => $ticket->id,
                'request_data' => $request->except(['message']),
            ]);

            return back()->withErrors(['error' => 'Failed to add reply. Please try again.']);
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
            $oldStatus = $ticket->status;
            $ticket->status = is_string($validated['status'] ?? null) ? $validated['status'] : 'open';
            $saved = $ticket->save();
            if (! $saved) {
                DB::rollBack();

                return back()->withErrors(['status' => 'Failed to update ticket status']);
            }
            // Send email notification to user when status is updated
            try {
                if ($ticket->user) {
                    $this->emailService->sendTicketStatusUpdate($ticket->user, [
                        'ticket_id' => $ticket->id,
                        'ticket_subject' => $ticket->subject,
                        'old_status' => $oldStatus,
                        'new_status' => $ticket->status,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send ticket status update email', [
                    'error' => $e->getMessage(),
                    'ticket_id' => $ticket->id,
                    'userId' => $ticket->userId,
                ]);
            }
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
