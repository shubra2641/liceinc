<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * License Status Request with comprehensive validation.
 *
 * This request class handles validation for license status check API endpoints
 * with enhanced security measures and proper input sanitization.
 */
class LicenseStatusRequest extends FormRequest
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
            'licenseKey' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9\-]+$/',
            ],
            'product_slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
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
            'licenseKey.required' => 'License key is required.',
            'licenseKey.regex' => 'License key contains invalid characters.',
            'product_slug.required' => 'Product slug is required.',
            'product_slug.regex' => 'Product slug contains invalid characters.',
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
            'licenseKey' => 'license key',
            'product_slug' => 'product slug',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize inputs to prevent XSS
        if ($this->has('licenseKey')) {
            $this->merge([
                'licenseKey' => $this->sanitizeInput($this->input('licenseKey')),
            ]);
        }
        if ($this->has('product_slug')) {
            $this->merge([
                'product_slug' => $this->sanitizeInput($this->input('product_slug')),
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
