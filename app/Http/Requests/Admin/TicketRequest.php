<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ticket Request with enhanced security.
 *
 * This unified request class handles validation for creating, updating, replying to,
 * and updating status of tickets with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for store, update, reply, and status operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Priority and status validation
 * - Invoice creation validation
 * - User and category validation
 */
class TicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();

        return auth()->check() && $user && ($user->is_admin || $user->hasRole('admin'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $route = $this->route();
        $routeName = $route?->getName() ?? '';

        $isReply = $this->isMethod('POST') && str_contains($routeName, 'reply');
        $isStatusUpdate = $this->isMethod('PATCH') && str_contains($routeName, 'status');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        // Reply validation
        if ($isReply) {
            return [
                'message' => [
                    'required',
                    'string',
                    'min:10',
                    'max:5000',
                ],
            ];
        }
        // Status update validation
        if ($isStatusUpdate) {
            return [
                'status' => [
                    'required',
                    'string',
                    Rule::in(['open', 'pending', 'resolved', 'closed', 'cancelled']),
                ],
            ];
        }

        // Store/Update validation
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'category_id' => [
                'required',
                'integer',
                'exists:ticket_categories,id',
            ],
            'subject' => [
                'required',
                'string',
                'max:255',
            ],
            'priority' => [
                'required',
                'string',
                Rule::in(['low', 'medium', 'high', 'urgent']),
            ],
            'content' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['open', 'pending', 'resolved', 'closed', 'cancelled']),
            ],
            'create_invoice' => [
                'boolean',
            ],
            'invoice_product_id' => [
                'nullable',
                'required_if:create_invoice,1',
                'string',
                'in:custom',
            ],
            'invoice_amount' => [
                'nullable',
                'required_if:create_invoice,1',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'billing_type' => [
                'nullable',
                'required_if:create_invoice,1',
                'string',
                Rule::in([
                    'one_time', 'recurring', 'monthly', 'quarterly',
                    'semi_annual', 'annual', 'custom_recurring',
                ]),
            ],
            'billing_cycle' => [
                'nullable',
                'required_if:billing_type,recurring',
                'string',
                Rule::in(['monthly', 'quarterly', 'yearly']),
            ],
            'due_date' => [
                'nullable',
                'date',
                'after:today',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User selection is required.',
            'user_id.exists' => 'Selected user does not exist.',
            'category_id.required' => 'Category selection is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'subject.required' => 'Ticket subject is required.',
            'subject.regex' => 'Subject contains invalid characters.',
            'priority.required' => 'Priority selection is required.',
            'priority.in' => 'Priority must be one of: low, medium, high, urgent.',
            'content.required' => 'Ticket content is required.',
            'content.min' => 'Content must be at least 10 characters.',
            'content.max' => 'Content must not exceed 10,000 characters.',
            'content.regex' => 'Content contains invalid characters.',
            'status.in' => 'Status must be one of: open, pending, resolved, closed, cancelled.',
            'invoice_product_id.required_if' => 'Product selection is required when creating an invoice.',
            'invoice_amount.required_if' => 'Invoice amount is required when creating an invoice.',
            'invoice_amount.min' => 'Invoice amount must be at least 0.01.',
            'invoice_amount.max' => 'Invoice amount must not exceed 999,999.99.',
            'billing_type.required_if' => 'Billing type is required when creating an invoice.',
            'billing_type.in' => 'Billing type must be one of: one_time, recurring, monthly, ' .
                'quarterly, semi_annual, annual, custom_recurring.',
            'billing_cycle.required_if' => 'Billing cycle is required for recurring invoices.',
            'billing_cycle.in' => 'Billing cycle must be one of: monthly, quarterly, yearly.',
            'due_date.after' => 'Due date must be in the future.',
            'notes.regex' => 'Notes contain invalid characters.',
            'message.required' => 'Reply message is required.',
            'message.min' => 'Reply message must be at least 10 characters.',
            'message.max' => 'Reply message must not exceed 5,000 characters.',
            'message.regex' => 'Reply message contains invalid characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'user',
            'category_id' => 'category',
            'subject' => 'ticket subject',
            'priority' => 'ticket priority',
            'content' => 'ticket content',
            'status' => 'ticket status',
            'create_invoice' => 'create invoice',
            'invoice_product_id' => 'invoice product',
            'invoice_amount' => 'invoice amount',
            'billing_type' => 'billing type',
            'billing_cycle' => 'billing cycle',
            'due_date' => 'due date',
            'notes' => 'ticket notes',
            'message' => 'reply message',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'subject' => $this->sanitizeInput(is_string($this->subject) ? $this->subject : null),
            'content' => $this->sanitizeInput(is_string($this->content) ? $this->content : null),
            'notes' => $this->notes && is_string($this->notes) ? $this->sanitizeInput($this->notes) : null,
            'message' => $this->message && is_string($this->message) ? $this->sanitizeInput($this->message) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'create_invoice' => $this->has('create_invoice'),
        ]);
        // Set default values
        $this->merge([
            'status' => $this->status ?? 'open',
            'priority' => $this->priority ?? 'medium',
        ]);
    }

    /**
     * Sanitize input to prevent XSS attacks.
     *
     * @param  string|null  $input  The input to sanitize
     *
     * @return string|null The sanitized input
     */
    private function sanitizeInput(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
