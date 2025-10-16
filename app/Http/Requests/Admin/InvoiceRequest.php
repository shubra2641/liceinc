<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Invoice Request with enhanced security.
 *
 * This unified request class handles validation for both creating and updating
 * invoices with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both store and update operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Unique validation with ignore for current record on updates
 * - Payment and billing validation
 * - Invoice status management
 */
class InvoiceRequest extends FormRequest
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
        $invoiceId = $this->route('invoice')->id ?? null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['initial', 'renewal', 'upgrade', 'custom']),
            ],
            'custom_invoice_type' => [
                'nullable',
                'string',
                Rule::in(['one_time', 'monthly', 'quarterly', 'semi_annual', 'annual', 'three_years', 'lifetime']),
            ],
            'custom_product_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'expiration_date' => [
                'nullable',
                'date',
                'after:today',
            ],
            'invoice_number' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[A-Z0-9\-]+$/',
                $isUpdate
                    ? Rule::unique('invoices', 'invoice_number')->ignore($invoiceId)
                    : 'unique:invoices, invoice_number',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'currency' => [
                'required',
                'string',
                Rule::in(['USD', 'EUR', 'GBP', 'CAD', 'AUD']),
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'paid', 'cancelled', 'refunded', 'overdue']),
            ],
            'due_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'paid_at' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'payment_method' => [
                'nullable',
                'string',
                Rule::in(['credit_card', 'paypal', 'bank_transfer', 'cash', 'other']),
            ],
            'payment_reference' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'billing_type' => [
                'required',
                'string',
                Rule::in(['one_time', 'recurring']),
            ],
            'billing_cycle' => [
                'required_if:billing_type, recurring',
                'string',
                Rule::in(['monthly', 'quarterly', 'yearly']),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'tax_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'discount_type' => [
                'nullable',
                'string',
                Rule::in(['fixed', 'percentage']),
            ],
            'is_recurring' => [
                'boolean',
            ],
            'next_billing_date' => [
                'nullable',
                'date',
                'after:today',
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
            'product_id.exists' => 'Selected product does not exist.',
            'type.required' => 'Invoice type is required.',
            'type.in' => 'Invoice type must be one of: initial, renewal, upgrade, custom.',
            'custom_invoice_type.in' => 'Custom invoice type must be one of: one_time, monthly, quarterly, '.
                'semi_annual, annual, three_years, lifetime.',
            'custom_product_name.max' => 'Product name cannot exceed 255 characters.',
            'expiration_date.date' => 'Expiration date must be a valid date.',
            'expiration_date.after' => 'Expiration date must be in the future.',
            'invoice_number.required' => 'Invoice number is required.',
            'invoice_number.unique' => 'This invoice number already exists.',
            'invoice_number.regex' => 'Invoice number can only contain uppercase letters, numbers, and hyphens.',
            'amount.required' => 'Invoice amount is required.',
            'amount.min' => 'Amount must be at least 0.01.',
            'amount.max' => 'Amount cannot exceed 999, 999.99.',
            'currency.required' => 'Currency is required.',
            'currency.in' => 'Currency must be one of: USD, EUR, GBP, CAD, AUD.',
            'status.required' => 'Invoice status is required.',
            'status.in' => 'Status must be one of: pending, paid, cancelled, refunded, overdue.',
            'due_date.required' => 'Due date is required.',
            'due_date.after' => 'Due date must be in the future.',
            'paid_at.before_or_equal' => 'Payment date cannot be in the future.',
            'payment_method.in' => 'Payment method must be one of: credit_card, paypal, bank_transfer, cash, other.',
            'payment_reference.regex' => 'Payment reference contains invalid characters.',
            'billing_type.required' => 'Billing type is required.',
            'billing_type.in' => 'Billing type must be one of: one_time, recurring.',
            'billing_cycle.required_if' => 'Billing cycle is required for recurring invoices.',
            'billing_cycle.in' => 'Billing cycle must be one of: monthly, quarterly, yearly.',
            'description.regex' => 'Description contains invalid characters.',
            'notes.regex' => 'Notes contain invalid characters.',
            'tax_rate.min' => 'Tax rate must be at least 0%.',
            'tax_rate.max' => 'Tax rate cannot exceed 100%.',
            'discount_amount.min' => 'Discount amount must be at least 0.',
            'discount_amount.max' => 'Discount amount cannot exceed 999, 999.99.',
            'discount_type.in' => 'Discount type must be either fixed or percentage.',
            'next_billing_date.after' => 'Next billing date must be in the future.',
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
            'product_id' => 'product',
            'invoice_number' => 'invoice number',
            'amount' => 'invoice amount',
            'currency' => 'currency',
            'status' => 'invoice status',
            'due_date' => 'due date',
            'paid_at' => 'payment date',
            'payment_method' => 'payment method',
            'payment_reference' => 'payment reference',
            'billing_type' => 'billing type',
            'billing_cycle' => 'billing cycle',
            'description' => 'invoice description',
            'notes' => 'invoice notes',
            'tax_rate' => 'tax rate',
            'discount_amount' => 'discount amount',
            'discount_type' => 'discount type',
            'is_recurring' => 'recurring invoice',
            'next_billing_date' => 'next billing date',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'invoice_number' => $this->sanitizeInput($this->input('invoice_number')),
            'payment_reference' => $this->input('payment_reference')
                ? $this->sanitizeInput($this->input('payment_reference'))
                : null,
            'description' => $this->input('description') ? $this->sanitizeInput($this->input('description')) : null,
            'notes' => $this->input('notes') ? $this->sanitizeInput($this->input('notes')) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'is_recurring' => $this->has('is_recurring'),
        ]);
        // Set default values
        $this->merge([
            'status' => $this->status ?? 'pending',
            'currency' => $this->currency ?? 'USD',
            'billing_type' => $this->billing_type ?? 'one_time',
        ]);
    }

    /**
     * Sanitize input to prevent XSS attacks.
     *
     * @param  mixed  $input  The input to sanitize
     *
     * @return string|null The sanitized input
     */
    private function sanitizeInput(mixed $input): ?string
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (! is_string($input)) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
