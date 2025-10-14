<?php

namespace App\Http\Requests\Api;

use App\Traits\RequestHelpers;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base API Request with enhanced security.
 *
 * This base request class handles common validation for API operations
 * with comprehensive security measures and input sanitization.
 */
abstract class BaseApiRequest extends FormRequest
{
    use RequestHelpers;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get common validation rules for API requests.
     *
     * @return array<string, mixed>
     */
    protected function getCommonRules(): array
    {
        return [
            'license_key' => [
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
     * Get common validation messages for API requests.
     *
     * @return array<string, string>
     */
    protected function getCommonMessages(): array
    {
        return [
            'license_key.required' => 'License key is required.',
            'license_key.regex' => 'License key can only contain letters, numbers, hyphens, and underscores.',
            'domain.required' => 'Domain is required.',
            'domain.regex' => 'Domain can only contain letters, numbers, hyphens, underscores, and dots.',
        ];
    }

    /**
     * Get product slug validation rules.
     *
     * @return array<string, mixed>
     */
    protected function getProductSlugRules(): array
    {
        return [
            'product_slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_]+$/',
            ],
        ];
    }

    /**
     * Get product slug validation messages.
     *
     * @return array<string, string>
     */
    protected function getProductSlugMessages(): array
    {
        return [
            'product_slug.required' => 'Product slug is required.',
            'product_slug.regex' => 'Product slug can only contain letters, numbers, hyphens, and underscores.',
        ];
    }

    /**
     * Get product ID validation rules.
     *
     * @return array<string, mixed>
     */
    protected function getProductIdRules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    /**
     * Get product ID validation messages.
     *
     * @return array<string, string>
     */
    protected function getProductIdMessages(): array
    {
        return [
            'product_id.required' => 'Product ID is required.',
            'product_id.integer' => 'Product ID must be a valid integer.',
            'product_id.min' => 'Product ID must be at least 1.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'license_key' => $this->sanitizeInput($this->input('license_key')),
            'domain' => $this->sanitizeInput($this->input('domain')),
        ]);
    }
}
