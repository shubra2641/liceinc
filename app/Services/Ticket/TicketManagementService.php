<?php

declare(strict_types=1);

namespace App\Services\Ticket;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Ticket;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketManagementService
{
    public function __construct(
        private EmailService $emailService
    ) {
    }

    public function getTicketsWithRelations()
    {
        return Ticket::with(['user', 'category'])
            ->latest()
            ->paginate(10);
    }

    public function createTicket(array $validated, Request $request): Ticket
    {
        $ticketData = [
            'user_id' => $validated['user_id'],
            'category_id' => $validated['category_id'],
            'subject' => $validated['subject'],
            'priority' => $validated['priority'],
            'content' => $validated['content'],
            'status' => 'open',
        ];

        if ($request->filled('create_invoice') && $request->boolean('create_invoice')) {
            $invoice = $this->createInvoice($validated, $request);
            $ticketData['invoice_id'] = $invoice->id;
        }

        $ticket = Ticket::create($ticketData);

        $this->sendTicketNotifications($ticket);

        if ($ticket->invoice_id && $ticket->user && $ticket->user->email) {
            $this->emailService->sendInvoiceCreated($ticket->user, $ticket->invoice);
        }

        return $ticket;
    }

    public function updateTicket(Ticket $ticket, array $validated, Request $request): void
    {
        $ticket->update([
            'user_id' => $validated['user_id'],
            'category_id' => $validated['category_id'],
            'subject' => $validated['subject'],
            'priority' => $validated['priority'],
            'content' => $validated['content'],
        ]);

        if ($request->filled('create_invoice') && $request->boolean('create_invoice')) {
            $invoice = $this->createInvoice($validated, $request);
            $ticket->update(['invoice_id' => $invoice->id]);
        }

        $this->sendTicketNotifications($ticket);

        if ($ticket->invoice_id && $ticket->user && $ticket->user->email) {
            $this->emailService->sendInvoiceCreated($ticket->user, $ticket->invoice);
        }
    }

    public function deleteTicket(Ticket $ticket): void
    {
        $ticket->delete();
    }

    public function addReply(Ticket $ticket, array $validated): void
    {
        $ticket->replies()->create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        $this->sendTicketNotifications($ticket);
    }

    public function updateTicketStatus(Ticket $ticket, array $validated): void
    {
        $oldStatus = $ticket->status;
        $ticket->status = is_string($validated['status'] ?? null) ? $validated['status'] : 'open';
        
        if (!$ticket->save()) {
            throw new \Exception('Failed to update ticket status');
        }

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
                'user_id' => $ticket->user_id,
            ]);
        }
    }

    private function createInvoice(array $validated, Request $request): Invoice
    {
        $invoiceProductId = $request->input('invoice_product_id');
        $billingType = $request->input('billing_type', 'one_time');

        if ($invoiceProductId === 'custom' || empty($invoiceProductId)) {
            return $this->createCustomInvoice($validated, $request, $billingType);
        }

        return $this->createProductBasedInvoice($validated, $request, $invoiceProductId, $billingType);
    }

    private function createCustomInvoice(array $validated, Request $request, string $billingType): Invoice
    {
        $amount = $validated['invoice_amount'] ?? 0;
        $duration = $validated['invoice_duration_days'] ?? 0;
        $dueDate = now()->addDays(is_numeric($duration) ? (int)$duration : 0)->toDateString();
        
        $metadata = $this->buildMetadata($billingType, $amount, $duration, $request);

        return Invoice::create([
            'user_id' => $validated['user_id'],
            'product_id' => null,
            'amount' => $amount,
            'status' => $request->input('invoice_status') ?? 'pending',
            'due_date' => $dueDate,
            'notes' => $request->input('invoice_notes') ?? null,
            'currency' => config('app.currency', 'USD'),
            'type' => ($billingType && $billingType !== 'one_time') ? 'recurring' : 'one_time',
            'metadata' => $metadata,
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
        ]);
    }

    private function createProductBasedInvoice(array $validated, Request $request, string $invoiceProductId, string $billingType): Invoice
    {
        $product = Product::find($invoiceProductId);
        
        if (!$product) {
            throw new \Exception('Invalid product selected');
        }

        $amount = $request->input('invoice_amount') ?: $product->price;
        $duration = $request->input('invoice_duration_days') ?: $product->duration_days ?: null;
        $dueDate = $request->input('invoice_due_date')
            ?: ($duration
                ? now()->addDays(is_numeric($duration) ? (int)$duration : 0)->toDateString()
                : null);

        $metadata = $this->buildMetadata($billingType, $amount, $duration, $request, $product);

        return Invoice::create([
            'user_id' => $validated['user_id'],
            'product_id' => is_numeric($invoiceProductId) ? $invoiceProductId : null,
            'amount' => $amount,
            'status' => $request->input('invoice_status') ?? 'pending',
            'due_date' => $dueDate,
            'notes' => $request->input('invoice_notes') ?? null,
            'currency' => config('app.currency', 'USD'),
            'type' => ($billingType && $billingType !== 'one_time') ? 'recurring' : 'one_time',
            'metadata' => $metadata,
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
        ]);
    }

    private function buildMetadata(string $billingType, float $amount, int $duration, Request $request, ?Product $product = null): array
    {
        if ($billingType === 'one_time') {
            return [];
        }

        $map = [
            'monthly' => 30,
            'quarterly' => 90,
            'semi_annual' => 182,
            'annual' => 365,
        ];

        if ($billingType === 'custom_recurring') {
            return [
                'recurrence' => 'custom',
                'renewal_price' => $request->input('invoice_renewal_price')
                    ?: ($product?->renewal_price ?? $amount),
                'renewal_period_days' => $request->input('invoice_renewal_period_days')
                    ?: ($product?->renewal_period ?? ($duration ?: 30)),
            ];
        }

        return [
            'recurrence' => $billingType,
            'renewal_period_days' => (is_string($billingType) && isset($map[$billingType]))
                ? $map[$billingType]
                : ($product?->renewal_period ?? $duration),
            'renewal_price' => $product?->renewal_price ?? $amount,
        ];
    }

    private function sendTicketNotifications(Ticket $ticket): void
    {
        try {
            if ($ticket->user && $ticket->user->email) {
                $this->emailService->sendTicketCreated($ticket->user, $ticket);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send ticket notification', [
                'error' => $e->getMessage(),
                'ticket_id' => $ticket->id,
            ]);
        }
    }
}
