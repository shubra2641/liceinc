<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * License Register Request with comprehensive validation.
 *
 * This request class handles validation for license registration API endpoints
 * with enhanced security measures and proper input sanitization.
 */
class LicenseRegisterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purchase_code' => [
                'required',
                'string',
                'min:10',
                'max:100',
                'regex:/^[A-Z0-9\-]+$/',
            ],
            'product_slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
            ],
            'domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-\.]+$/',
            ],
            'envato_data' => 'nullable|array',
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
            'purchase_code.required' => 'Purchase code is required.',
            'purchase_code.regex' => 'Purchase code contains invalid characters.',
            'product_slug.required' => 'Product slug is required.',
            'product_slug.regex' => 'Product slug contains invalid characters.',
            'domain.regex' => 'Domain contains invalid characters.',
        ];
    }
    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'purchase_code' => 'purchase code',
            'product_slug' => 'product slug',
            'domain' => 'domain',
            'envato_data' => 'envato data',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize inputs to prevent XSS
        if ($this->has('purchase_code')) {
            $this->merge([
                'purchase_code' => $this->sanitizeInput($this->input('purchase_code')),
            ]);
        }
        if ($this->has('product_slug')) {
            $this->merge([
                'product_slug' => $this->sanitizeInput($this->input('product_slug')),
            ]);
        }
        if ($this->has('domain')) {
            $this->merge([
                'domain' => $this->sanitizeInput($this->input('domain')),
            ]);
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
