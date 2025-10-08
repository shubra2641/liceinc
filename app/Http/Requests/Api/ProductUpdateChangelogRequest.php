<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Product Update Changelog Request with enhanced security.
 *
 * This request class handles validation for changelog operations
 * with comprehensive security measures and input sanitization.
 */
class ProductUpdateChangelogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'productId' => [
                'required',
                'integer',
                'min:1',
            ],
            'licenseKey' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_]+$/',
            ],
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_.]+$/',
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
            'productId.required' => 'Product ID is required.',
            'productId.integer' => 'Product ID must be a valid integer.',
            'productId.min' => 'Product ID must be at least 1.',
            'licenseKey.required' => 'License key is required.',
            'licenseKey.regex' => 'License key can only contain letters, numbers, hyphens, and underscores.',
            'domain.required' => 'Domain is required.',
            'domain.regex' => 'Domain can only contain letters, numbers, hyphens, underscores, and dots.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'licenseKey' => $this->sanitizeInput($this->input('licenseKey')),
            'domain' => $this->sanitizeInput($this->input('domain')),
        ]);
    }

    /**
     * Sanitize input to prevent XSS attacks.
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
