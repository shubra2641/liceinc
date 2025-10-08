<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketReply;
use App\Services\EmailService;
use App\Services\EnvatoService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * User Ticket Controller with enhanced security.
 *
 * This controller handles user ticket management functionality including
 * ticket creation, viewing, updating, and replying with enhanced security
 * measures and proper error handling.
 *
 * Features:
 * - User ticket listing with pagination
 * - Ticket creation with category validation
 * - Purchase code verification and license creation
 * - Ticket viewing with authorization
 * - Ticket updating and deletion
 * - Ticket replies with email notifications
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Model relationship integration for optimized queries
 */
class TicketController extends Controller
{
    /**
     * Pagination limit for ticket listing.
     */
    private const PAGINATION_LIMIT = 10;
    /**
     * Valid ticket priorities.
     */
    private const VALID_PRIORITIES = ['low', 'medium', 'high'];
    /**
     * Valid ticket statuses.
     */
    private const VALID_STATUSES = ['open', 'pending', 'resolved', 'closed'];
    /**
     * The email service instance.
     */
    protected EmailService $emailService;
    /**
     * Create a new controller instance.
     *
     * @param  EmailService  $emailService  The email service instance
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
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
     * GET /user/tickets
     *
     * // Returns view with:
     * // - Paginated tickets (10 per page)
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
            return view('user.tickets.index', ['tickets' => $tickets]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load user tickets: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return view('user.tickets.index', ['tickets' => collect()])
                ->with('error', 'Failed to load tickets. Please try again.');
        }
    }
    /**
     * Show the form for creating a new ticket with enhanced security.
     *
     * Displays ticket creation form with category validation, license
     * information, and proper authorization checking.
     *
     * @return View|RedirectResponse The ticket creation view or redirect
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Access ticket creation form:
     * GET /user/tickets/create
     *
     * // Returns view with:
     * // - Available categories
     * // - User licenses (if authenticated)
     * // - Category requirements validation
     */
    public function create(): View|RedirectResponse
    {
        try {
            DB::beginTransaction();
            $allCategories = TicketCategory::active()->ordered()->get();
            // If user is not logged in, show only categories that don't require login
            if (! auth()->check()) {
                $categories = $allCategories->where('requires_login', false);
            } else {
                $categories = $allCategories;
            }
            $requiresLoginCount = $allCategories->where('requires_login', true)->count();
            $totalCount = $allCategories->count();
            // If all categories require login and user is not logged in
            if ($totalCount > 0 && $requiresLoginCount === $totalCount && ! auth()->check()) {
                DB::commit();
                return redirect()->route('login')->with('error', __('app.You must login to create a ticket.'));
            }
            // Get user's licenses for the related license dropdown
            $user = auth()->user();
            $licenses = $user ? $user->licenses()->with('product')->get() : collect();
            DB::commit();
            return view('user.tickets.create', ['categories' => $categories, 'licenses' => $licenses]);
        } catch (Exception $e) {
            DB::rollBack();
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
     * Creates a new ticket with comprehensive validation, purchase code
     * verification, license creation, and email notifications.
     *
     * @param  Request  $request  The HTTP request containing ticket data
     *
     * @return RedirectResponse The redirect response
     *
     * @throws Exception When database operations fail
     *
     * @example
     * // Create a new ticket:
     * POST /user/tickets
     * {
     *     "subject": "Issue with product",
     *     "priority": "high",
     *     "content": "Description of the issue",
     *     "category_id": 1,
     *     "purchase_code": "ABC123-DEF456"
     * }
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Request is already validated by type hint
            $validated = $this->validateTicketData($request);
            DB::beginTransaction();
            $category = TicketCategory::find($validated['category_id']);
            if (! $category || $category instanceof \Illuminate\Database\Eloquent\Collection) {
                DB::rollBack();
                return back()->withErrors(['category_id' => 'Invalid category selected'])->withInput();
            }
            // If category requires login, ensure user is authenticated
            if ($category->requires_login && ! Auth::check()) {
                DB::rollBack();
                return redirect()->route('login');
            }
            // If category requires a valid purchase code, validate it
            $license = null;
            if ($category->requires_valid_purchase_code) {
                if (empty($validated['purchase_code'])) {
                    DB::rollBack();
                    return back()->withErrors(['purchase_code' => 'Purchase code is required for this category'])
                        ->withInput();
                }
                $license = $this->validateAndCreateLicense($validated);
                if (! $license) {
                    DB::rollBack();
                    return back()->withErrors(['purchase_code' => 'Invalid purchase code'])->withInput();
                }
            } else {
                // If purchase_code provided for non-requiring category, still attempt verification (optional)
                $user = Auth::user();
                if (! empty($validated['purchase_code']) && Auth::check() && $user && $user->hasEnvatoAccount()) {
                    $license = $this->validateAndCreateLicense($validated);
                }
            }
            $ticketData = $this->prepareTicketData($validated, $category, $license);
            // If created from an invoice, attach invoice and link license/product
            if (empty($validated['invoice_id']) === false) {
                $ticketData = $this->attachInvoiceData($ticketData, is_numeric($validated['invoice_id']) ? (int)$validated['invoice_id'] : 0);
            }
            if (! empty($validated['purchase_code'])) {
                $ticketData['purchase_code'] = $validated['purchase_code'];
            }
            $ticket = Ticket::create($ticketData);
            // Send email notifications
            $this->sendTicketNotifications($ticket);
            DB::commit();
            // Redirect based on user authentication status
            if (Auth::check()) {
                return redirect()->route('user.tickets.show', $ticket)->with('success', 'Ticket created successfully');
            } else {
                // For guests, redirect to support ticket view
                return redirect()->route('support.tickets.show', $ticket)
                    ->with('success', 'Ticket created successfully. You can view it using the ticket ID: ' . $ticket->id);
            }
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
     * GET /user/tickets/123
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
            // Ticket is already validated by type hint
            // Allow viewing ticket if it has no user_id (for guests) or if user is logged in
            // and is ticket owner or admin
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
            return view('user.tickets.show', ['ticket' => $ticket]);
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
     * PUT /user/tickets/123
     * {
     *     "subject": "Updated subject",
     *     "priority": "high",
     *     "status": "pending"
     * }
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        try {
            // Request and ticket are already validated by type hints
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
     * DELETE /user/tickets/123
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        try {
            // Ticket is validated by type hint
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
     * and email notifications.
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
     * POST /user/tickets/123/reply
     * {
     *     "message": "Thank you for the update"
     * }
     */
    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        try {
            // Request and ticket are already validated by type hints
            if (! $this->canModifyTicket($ticket)) {
                Log::warning('Unauthorized ticket reply attempt', [
                    'user_id' => Auth::id(),
                    'ticket_id' => $ticket->id,
                    'ticket_user_id' => $ticket->user_id,
                    'ip_address' => request()->ip(),
                ]);
                abort(403, 'Unauthorized access to reply to ticket');
            }
            $validated = $this->validateReplyData($request);
            DB::beginTransaction();
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $validated['message'],
            ]);
            // Send email notification to admin when user replies
            $this->sendReplyNotification($ticket, is_string($validated['message']) ? $validated['message'] : '');
            DB::commit();
            return back()->with('success', 'Reply added');
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
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:' . implode(', ', self::VALID_PRIORITIES)],
            'content' => ['required', 'string'],
            'purchase_code' => ['nullable', 'string'],
            'product_slug' => ['nullable', 'string'],
            'product_version' => ['nullable', 'string'],
            'browser_info' => ['nullable', 'string'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'category_id' => ['required', 'exists:ticket_categories,id'],
        ]);

        /**
 * @var array<string, mixed> $result
*/
        $result = $validated;
        return $result;
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
        $validated = $request->validate([
            'subject' => ['sometimes', 'string', 'max:255'],
            'priority' => ['sometimes', 'in:' . implode(', ', self::VALID_PRIORITIES)],
            'status' => ['sometimes', 'in:' . implode(', ', self::VALID_STATUSES)],
            'content' => ['sometimes', 'string'],
        ]);

        /**
 * @var array<string, mixed> $result
*/
        $result = $validated;
        return $result;
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
        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        /**
 * @var array<string, mixed> $result
*/
        $result = $validated;
        return $result;
    }
    /**
     * Validate and create license from purchase code.
     *
     * @param  array<string, mixed>  $validated  The validated request data
     *
     * @return License|null The created or existing license
     */
    private function validateAndCreateLicense(array $validated): ?License
    {
        try {
            // First try to find an existing license record
            $license = License::where('purchase_code', $validated['purchase_code'])->first();
            if (! $license) {
                // Try to verify via Envato service if available
                try {
                    $envatoService = app(EnvatoService::class);
                    $sale = $envatoService->verifyPurchase(is_string($validated['purchase_code']) ? $validated['purchase_code'] : '');
                } catch (\Throwable $e) {
                    Log::error('Envato verification failed: ' . $e->getMessage());
                    $sale = null;
                }
                if (! $sale) {
                    return null;
                }
                // Create license record for authenticated user
                if (Auth::check()) {
                    $product = null;
                    if (! empty($validated['product_slug'])) {
                        $product = Product::where('slug', $validated['product_slug'])->first();
                    }
                    if ($product) {
                        $license = License::create([
                            'purchase_code' => $validated['purchase_code'],
                            'product_id' => $product->id,
                            'user_id' => Auth::id(),
                            'license_type' => 'regular',
                            'status' => 'active',
                            'support_expires_at' => data_get($sale, 'supported_until')
                                ? date('Y-m-d', strtotime(is_string(data_get($sale, 'supported_until')) ? data_get($sale, 'supported_until') : '') ?: time())
                                : null,
                        ]);
                    }
                }
            }
            return $license;
        } catch (Exception $e) {
            Log::error('Failed to validate and create license: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Prepare ticket data for creation.
     *
     * @param  array<string, mixed>  $validated  The validated request data
     * @param  TicketCategory  $category  The ticket category
     * @param  License|null  $license  The license instance
     *
     * @return array<string, mixed> The prepared ticket data
     */
    private function prepareTicketData(array $validated, TicketCategory $category, ?License $license): array
    {
        return [
            'user_id' => Auth::check() === true ? Auth::id() : null, // Can be null for guests
            'subject' => $validated['subject'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'content' => $validated['content'],
            'license_id' => $license?->id,
            'category_id' => $category->id,
            'product_version' => $validated['product_version'] ?? null,
            'browser_info' => $validated['browser_info'] ?? null,
        ];
    }
    /**
     * Attach invoice data to ticket data.
     *
     * @param  array<string, mixed>  $ticketData  The ticket data
     * @param  int  $invoiceId  The invoice ID
     *
     * @return array<string, mixed> The updated ticket data
     */
    private function attachInvoiceData(array $ticketData, int $invoiceId): array
    {
        $invoice = Invoice::with('license', 'product')->find($invoiceId);
        if ($invoice) {
            $ticketData['invoice_id'] = $invoice->id;
            // prefer invoice license if exists
            if ($invoice->license) {
                $ticketData['license_id'] = $invoice->license->id;
            }
            // if purchase_code not provided, try to set from invoice license
            if (empty($ticketData['purchase_code']) && $invoice->license?->purchase_code) {
                $ticketData['purchase_code'] = $invoice->license->purchase_code;
            }
        }
        return $ticketData;
    }
    /**
     * Send ticket creation notifications.
     *
     * @param  Ticket  $ticket  The created ticket
     */
    private function sendTicketNotifications(Ticket $ticket): void
    {
        try {
            // Send notification to user (if authenticated)
            if (Auth::check() && $ticket->user) {
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
        } catch (Exception $e) {
            Log::error('Failed to send ticket notifications: ' . $e->getMessage());
        }
    }
    /**
     * Send reply notification.
     *
     * @param  Ticket  $ticket  The ticket
     * @param  string  $message  The reply message
     */
    private function sendReplyNotification(Ticket $ticket, string $message): void
    {
        try {
            $this->emailService->sendAdminTicketReply([
                'ticket_id' => $ticket->id,
                'ticket_subject' => $ticket->subject,
                'customer_name' => $ticket->user ? $ticket->user->name : 'Guest User',
                'customer_email' => $ticket->user ? $ticket->user->email : 'No email provided',
                'reply_message' => $message,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send reply notification: ' . $e->getMessage());
        }
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
        $user = Auth::user();
        return ! $ticket->user_id || // Guest ticket
               (Auth::check() && ($ticket->user_id === Auth::id() || ($user && $user->hasRole('admin')))); // Logged in user
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
