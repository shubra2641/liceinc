<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * License Status Request with enhanced security.
 *
 * This request class handles validation for license status checking
 * with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Enhanced security measures (XSS protection, input validation)
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Input sanitization and validation
 */
class LicenseStatusRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'license_key.required' => 'License key is required.',
            'license_key.string' => 'License key must be a string.',
            'license_key.max' => 'License key must not exceed 255 characters.',
            'license_key.regex' => 'License key contains invalid characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email address must not exceed 255 characters.',
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
            'license_key' => 'license key',
            'email' => 'email address',
        ];
    }
}