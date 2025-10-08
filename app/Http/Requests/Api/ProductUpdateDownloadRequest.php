<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Product Update Download Request with enhanced security. *
 * This request class handles validation for update download operations * with comprehensive security measures and input sanitization. */
class ProductUpdateDownloadRequest extends FormRequest
{
    /**   * Determine if the user is authorized to make this request. */
    public function authorize(): bool
    {
        return true;
    }

    /**   * Get the validation rules that apply to the request. *   * @return array<string, mixed> */
    public function rules(): array
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

    /**   * Get custom validation messages. *   * @return array<string, string> */
    public function messages(): array
    {
        return [
            'license_key.required' => 'License key is required.',
            'license_key.regex' => 'License key can only contain letters, numbers, hyphens, and underscores.',
            'domain.required' => 'Domain is required.',
            'domain.regex' => 'Domain can only contain letters, numbers, hyphens, underscores, and dots.',
        ];
    }

    /**   * Prepare the data for validation. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'license_key' => $this->sanitizeInput($this->input('license_key')),
            'domain' => $this->sanitizeInput($this->input('domain')),
        ]);
    }

    /**   * Sanitize input to prevent XSS attacks. */
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
