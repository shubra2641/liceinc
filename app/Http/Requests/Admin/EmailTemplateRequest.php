<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Email Template Request with enhanced security.
 *
 * This unified request class handles validation for creating, updating, and testing
 * email templates with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for store, update, and test operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Template type and category validation
 * - Test email functionality validation
 * - Template variable validation
 */
class EmailTemplateRequest extends FormRequest
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
        $template = $this->route('email_template');
        $templateId = $template && is_object($template) && property_exists($template, 'id') ? $template->id : null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $route = $this->route();
        $isTest = $this->isMethod('POST') && $route && str_contains($route->getName() ?? '', 'test');
        // Test email validation
        if ($isTest) {
            return [
                'test_email' => [
                    'required',
                    'email',
                    'max:255',
                ],
                'test_data' => [
                    'nullable',
                    'array',
                ],
                'test_data.*' => [
                    'string',
                    'max:255',
                ],
            ];
        }
        // Store/Update validation
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $isUpdate ?
                    Rule::unique('email_templates', 'name')->ignore($templateId) :
                    'unique:email_templates, name',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'subject' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'body' => [
                'required',
                'string',
                'max:50000',
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['user', 'admin', 'system', 'notification', 'marketing']),
            ],
            'category' => [
                'required',
                'string',
                Rule::in([
                    'registration', 'license', 'ticket', 'invoice',
                    'update', 'notification', 'marketing', 'support',
                ]),
            ],
            'is_active' => [
                'boolean',
            ],
            'variables' => [
                'nullable',
                'array',
            ],
            'variables.*' => [
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'priority' => [
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
            'send_immediately' => [
                'boolean',
            ],
            'delay_minutes' => [
                'nullable',
                'integer',
                'min:0',
                'max:10080', // 1 week
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
            'name.required' => 'Template name is required.',
            'name.unique' => 'A template with this name already exists.',
            'name.regex' => 'Template name contains invalid characters.',
            'subject.required' => 'Email subject is required.',
            'subject.regex' => 'Email subject contains invalid characters.',
            'body.required' => 'Email body is required.',
            'body.max' => 'Email body must not exceed 50, 000 characters.',
            'type.required' => 'Template type is required.',
            'type.in' => 'Template type must be one of: user, admin, system, ' .
                'notification, marketing.',
            'category.required' => 'Template category is required.',
            'category.in' => 'Template category must be one of: registration, ' .
                'license, ticket, invoice, update, notification, marketing, support.',
            'variables.*.regex' => 'Variable names can only contain letters, numbers, spaces, '
                . 'hyphens, and underscores.',
            'description.regex' => 'Description contains invalid characters.',
            'priority.min' => 'Priority must be at least 1.',
            'priority.max' => 'Priority must not exceed 10.',
            'delay_minutes.min' => 'Delay must be at least 0 minutes.',
            'delay_minutes.max' => 'Delay must not exceed 1 week (10, 080 minutes).',
            'test_email.required' => 'Test email address is required.',
            'test_email.email' => 'Test email must be a valid email address.',
            'test_data.*.max' => 'Test data values must not exceed 255 characters.',
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
            'name' => 'template name',
            'subject' => 'email subject',
            'body' => 'email body',
            'type' => 'template type',
            'category' => 'template category',
            'is_active' => 'active status',
            'variables' => 'template variables',
            'description' => 'template description',
            'priority' => 'template priority',
            'send_immediately' => 'send immediately',
            'delay_minutes' => 'delay in minutes',
            'test_email' => 'test email address',
            'test_data' => 'test data',
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
            'subject' => $this->sanitizeInput($this->input('subject')),
            'description' => $this->input('description') ? $this->sanitizeInput($this->input('description')) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'is_active' => $this->has('is_active'),
            'send_immediately' => $this->has('send_immediately'),
        ]);
        // Set default values
        $this->merge([
            'is_active' => $this->is_active ?? true,
            'priority' => $this->priority ?? 5,
        ]);
        // Sanitize test data if present
        if ($this->test_data && is_array($this->test_data)) {
            $sanitizedTestData = [];
            foreach ($this->test_data as $key => $value) {
                $sanitizedTestData[$key] = $this->sanitizeInput($value);
            }
            $this->merge(['test_data' => $sanitizedTestData]);
        }
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

        if (!is_string($input)) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
