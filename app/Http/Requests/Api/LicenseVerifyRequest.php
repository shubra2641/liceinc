<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * License Verify Request with enhanced security. *
 * This request class handles validation for license verification requests * with comprehensive security measures and input sanitization. *
 * Features: * - Comprehensive validation rules for license verification * - XSS protection and input sanitization * - Custom validation messages for better user experience * - Proper type hints and return types * - Security validation rules (XSS protection, SQL injection prevention) * - Purchase code, product slug, and domain validation */
class LicenseVerifyRequest extends FormRequest
{
    /**   * Determine if the user is authorized to make this request. */
    public function authorize(): bool
    {
        return true; // API endpoint - authorization handled by middleware
    }
    /**   * Get the validation rules that apply to the request. *   * @return array<string, mixed> */
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
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-\.]+$/',
            ],
            'verification_key' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_]+$/',
            ],
        ];
    }
    /**   * Get custom validation messages. *   * @return array<string, string> */
    public function messages(): array
    {
        return [
            'purchase_code.required' => 'Purchase code is required.',
            'purchase_code.regex' => 'Purchase code contains invalid characters. ' .
                'Only uppercase letters, numbers, and hyphens are allowed.',
            'purchase_code.min' => 'Purchase code must be at least 10 characters long.',
            'purchase_code.max' => 'Purchase code may not be greater than 100 characters.',
            'product_slug.required' => 'Product slug is required.',
            'product_slug.regex' => 'Product slug contains invalid characters. ' .
                'Only lowercase letters, numbers, and hyphens are allowed.',
            'domain.required' => 'Domain is required.',
            'domain.regex' => 'Domain contains invalid characters.',
            'verification_key.regex' => 'Verification key contains invalid characters.',
        ];
    }
    /**   * Get custom attributes for validator errors. *   * @return array<string, string> */
    public function attributes(): array
    {
        return [
            'purchase_code' => 'purchase code',
            'product_slug' => 'product slug',
            'domain' => 'domain',
            'verification_key' => 'verification key',
        ];
    }
    /**   * Prepare the data for validation. */
    protected function prepareForValidation(): void
    {
        // Sanitize inputs to prevent XSS
        $this->merge([
            'purchase_code' => $this->sanitizeInput($this->input('purchase_code')),
            'product_slug' => $this->sanitizeInput($this->input('product_slug')),
            'domain' => $this->sanitizeDomain($this->input('domain')),
            'verification_key' => $this->input('verification_key') ? $this->sanitizeInput($this->input('verification_key')) : null,
        ]);
    }
    /**   * Sanitize input to prevent XSS attacks. *   * @param mixed $input The input to sanitize *   * @return string|null The sanitized input */
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
    /**   * Sanitize domain input with special handling for URLs. *   * @param mixed $domain The domain to sanitize *   * @return string|null The sanitized domain */
    private function sanitizeDomain(mixed $domain): ?string
    {
        if ($domain === null || $domain === '') {
            return null;
        }

        if (!is_string($domain)) {
            return null;
        }

        // Extract host from URL if it's a full URL
        $host = parse_url($domain, PHP_URL_HOST);
        if ($host) {
            return htmlspecialchars(trim($host), ENT_QUOTES, 'UTF-8');
        }
        return htmlspecialchars(trim($domain), ENT_QUOTES, 'UTF-8');
    }
}
