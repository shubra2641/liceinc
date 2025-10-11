<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Ticket Index Request with comprehensive validation.
 *
 * This request class handles validation for ticket listing with filtering
 * and search capabilities with comprehensive security measures.
 *
 * Features:
 * - Comprehensive validation rules for all filter parameters
 * - XSS protection and input sanitization
 * - Custom validation messages for better UX
 * - Security validation rules
 * - Search and pagination validation
 */
class TicketIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'string',
                'in:open, closed, in_progress',
            ],
            'priority' => [
                'sometimes',
                'string',
                'in:low, medium, high',
            ],
            'category_id' => [
                'sometimes',
                'integer',
                'exists:ticket_categories,id',
            ],
            'user_id' => [
                'sometimes',
                'integer',
                'exists:users,id',
            ],
            'search' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
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
            'status.in' => 'Status must be one of: open, closed, in_progress.',
            'priority.in' => 'Priority must be one of: low, medium, high.',
            'category_id.exists' => 'Selected category does not exist.',
            'user_id.exists' => 'Selected user does not exist.',
            'search.regex' => 'Search contains invalid characters.',
            'search.max' => 'Search cannot exceed 255 characters.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100 items.',
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
            'status' => 'ticket status',
            'priority' => 'ticket priority',
            'category_id' => 'category',
            'user_id' => 'user',
            'search' => 'search term',
            'per_page' => 'items per page',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->status && is_string($this->status) ? trim($this->status) : null,
            'priority' => $this->priority && is_string($this->priority) ? trim($this->priority) : null,
            'search' => $this->search && is_string($this->search) ? trim($this->search) : null,
            'per_page' => $this->per_page && is_numeric($this->per_page) ? (int)$this->per_page : null,
        ]);
    }
}
