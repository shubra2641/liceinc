<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ticket Category Request with enhanced security.
 *
 * This unified request class handles validation for both creating and updating
 * ticket categories with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both store and update operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Unique validation with ignore for current record on updates
 * - Color and icon validation
 * - Category organization validation
 */
class TicketCategoryRequest extends FormRequest
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
        $category = $this->route('ticket_category');
        $categoryId = $category && is_object($category) && property_exists($category, 'id') ? $category->id : null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                $isUpdate ? Rule::unique('ticket_categories', 'name')->ignore($categoryId) : 'unique:ticket_categories, name',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'color' => [
                'required',
                'string',
                'max:7',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
            'is_active' => [
                'boolean',
            ],
            'auto_assign' => [
                'boolean',
            ],
            'auto_assign_user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'response_time_hours' => [
                'nullable',
                'integer',
                'min:1',
                'max:168', // 1 week
            ],
            'escalation_hours' => [
                'nullable',
                'integer',
                'min:1',
                'max:720', // 1 month
            ],
            'priority' => [
                'nullable',
                'string',
                Rule::in(['low', 'medium', 'high', 'urgent']),
            ],
            'requires_approval' => [
                'boolean',
            ],
            'approval_user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'template_subject' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'template_content' => [
                'nullable',
                'string',
                'max:2000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
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
            'name.required' => 'Category name is required.',
            'name.unique' => 'A category with this name already exists.',
            'name.regex' => 'Category name contains invalid characters.',
            'description.regex' => 'Description contains invalid characters.',
            'color.required' => 'Category color is required.',
            'color.regex' => 'Color must be a valid hex color code (e.g., #3b82f6).',
            'icon.regex' => 'Icon contains invalid characters.',
            'sort_order.min' => 'Sort order must be at least 0.',
            'sort_order.max' => 'Sort order must not exceed 9999.',
            'auto_assign_user_id.exists' => 'Selected auto-assign user does not exist.',
            'response_time_hours.min' => 'Response time must be at least 1 hour.',
            'response_time_hours.max' => 'Response time cannot exceed 168 hours (1 week).',
            'escalation_hours.min' => 'Escalation time must be at least 1 hour.',
            'escalation_hours.max' => 'Escalation time cannot exceed 720 hours (1 month).',
            'priority.in' => 'Priority must be one of: low, medium, high, urgent.',
            'approval_user_id.exists' => 'Selected approval user does not exist.',
            'template_subject.regex' => 'Template subject contains invalid characters.',
            'template_content.regex' => 'Template content contains invalid characters.',
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
            'name' => 'category name',
            'description' => 'category description',
            'color' => 'category color',
            'icon' => 'category icon',
            'sort_order' => 'sort order',
            'is_active' => 'active status',
            'auto_assign' => 'auto assign',
            'auto_assign_user_id' => 'auto assign user',
            'response_time_hours' => 'response time (hours)',
            'escalation_hours' => 'escalation time (hours)',
            'priority' => 'default priority',
            'requires_approval' => 'requires approval',
            'approval_user_id' => 'approval user',
            'template_subject' => 'template subject',
            'template_content' => 'template content',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'name' => $this->sanitizeInput($this->input('name')),
            'description' => $this->input('description') ? $this->sanitizeInput($this->input('description')) : null,
            'icon' => $this->input('icon') ? $this->sanitizeInput($this->input('icon')) : null,
            'template_subject' => $this->input('template_subject') ? $this->sanitizeInput($this->input('template_subject')) : null,
            'template_content' => $this->input('template_content') ? $this->sanitizeInput($this->input('template_content')) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'is_active' => $this->has('is_active'),
            'auto_assign' => $this->has('auto_assign'),
            'requires_approval' => $this->has('requires_approval'),
        ]);
        // Set default values
        $this->merge([
            'is_active' => $this->is_active ?? true,
            'sort_order' => $this->sort_order ?? 0,
            'priority' => $this->priority ?? 'medium',
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
