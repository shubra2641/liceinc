<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Generate Test License Request with comprehensive validation.
 *
 * This request class handles validation for generating test licenses
 * for products with proper security measures.
 *
 * Features:
 * - Comprehensive validation rules for test license generation
 * - Domain and email validation
 * - XSS protection and input sanitization
 * - Custom validation messages for better UX
 * - Security validation rules
 */
class GenerateTestLicenseRequest extends FormRequest
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
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_.]+\.[a-zA-Z]{2, }$/',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'name' => [
                'nullable',
                'string',
                'max:255',
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
            'domain.required' => 'Domain is required for test license generation.',
            'domain.max' => 'Domain cannot exceed 255 characters.',
            'domain.regex' => 'Domain must be a valid domain name (e.g., example.com).',
            'email.required' => 'Email is required for test license generation.',
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'name.regex' => 'Name contains invalid characters.',
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
            'domain' => 'domain name',
            'email' => 'email address',
            'name' => 'user name',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $domain = $this->input('domain');
        $email = $this->input('email');
        $name = $this->input('name');

        $this->merge([
            'domain' => $domain && is_string($domain) ? strtolower(trim($domain)) : null,
            'email' => $email && is_string($email) ? strtolower(trim($email)) : null,
            'name' => $name && is_string($name) ? trim($name) : null,
        ]);
    }
}
