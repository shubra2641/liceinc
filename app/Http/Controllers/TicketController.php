<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Simplified Ticket Controller.
 */
class TicketController extends Controller
{
    public function __construct(
        private TicketService $ticketService
    ) {
    }

    /**
     * Display user tickets.
     */
    public function index(Request $request): View
    {
        $tickets = $this->ticketService->getUserTickets((int) Auth::id());

        return view('user.tickets.index', compact('tickets'));
    }

    /**
     * Show ticket creation form.
     */
    public function create(): View
    {
        $products = Product::where('status', 'active')->get();

        return view('user.tickets.create', compact('products'));
    }

    /**
     * Store new ticket.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'in:low,medium,high',
            'product_id' => 'nullable|exists:products,id',
            'license_key' => 'nullable|string',
            'domain' => 'nullable|string',
        ]);

        try {
            $ticket = $this->ticketService->createTicket($request->all());

            return redirect()
                ->route('user.tickets.show', $ticket->id)
                ->with('success', 'Ticket created successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create ticket');
        }
    }

    /**
     * Show ticket details.
     */
    public function show(int $id): View
    {
        $ticket = $this->ticketService->getTicket($id, (int) Auth::id());

        if (!$ticket) {
            abort(404);
        }

        return view('user.tickets.show', compact('ticket'));
    }

    /**
     * Update ticket.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $ticket = $this->ticketService->getTicket($id, (int) Auth::id());

        if (!$ticket) {
            abort(404);
        }

        $request->validate([
            'status' => 'in:open,in_progress,closed,resolved'
        ]);

        $this->ticketService->updateTicketStatus($ticket, (string) $request->status);

        return redirect()
            ->route('user.tickets.show', $ticket->id)
            ->with('success', 'Ticket updated successfully');
    }

    /**
     * Add reply to ticket.
     */
    public function reply(Request $request, int $id): RedirectResponse
    {
        $ticket = $this->ticketService->getTicket($id, (int) Auth::id());

        if (!$ticket) {
            abort(404);
        }

        $request->validate([
            'message' => 'required|string'
        ]);

        $this->ticketService->addReply($ticket, (string) $request->message);

        return redirect()
            ->route('user.tickets.show', $ticket->id)
            ->with('success', 'Reply added successfully');
    }

    /**
     * Delete ticket.
     */
    public function destroy(int $id): RedirectResponse
    {
        $ticket = $this->ticketService->getTicket($id, (int) Auth::id());

        if (!$ticket) {
            abort(404);
        }

        $this->ticketService->deleteTicket($ticket, (int) Auth::id());

        return redirect()
            ->route('user.tickets.index')
            ->with('success', 'Ticket deleted successfully');
    }
}
