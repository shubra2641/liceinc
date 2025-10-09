<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Check Updates Request with enhanced security.
 *
 * This request class handles validation for license update checking operations
 * with comprehensive security measures and input sanitization.
 */
class CheckUpdatesRequest extends FormRequest
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
            'license_key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_]+$/',
            ],
            'current_version' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9]+\.[0-9]+(\.[0-9]+)?(-[a-zA-Z0-9]+)?$/',
            ],
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_.]+$/',
            ],
            'product_slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_]+$/',
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
            'license_key.required' => 'License key is required.',
            'license_key.regex' => 'License key can only contain letters, numbers, hyphens, and underscores.',
            'current_version.required' => 'Current version is required.',
            'current_version.regex' => 'Current version must be in format: x.y or x.y.z or x.y.z-suffix.',
            'domain.required' => 'Domain is required.',
            'domain.regex' => 'Domain can only contain letters, numbers, hyphens, underscores, and dots.',
            'product_slug.required' => 'Product slug is required.',
            'product_slug.regex' => 'Product slug can only contain letters, numbers, hyphens, and underscores.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'license_key' => $this->sanitizeInput($this->input('license_key')),
            'current_version' => $this->sanitizeInput($this->input('current_version')),
            'domain' => $this->sanitizeInput($this->input('domain')),
            'product_slug' => $this->sanitizeInput($this->input('product_slug')),
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
