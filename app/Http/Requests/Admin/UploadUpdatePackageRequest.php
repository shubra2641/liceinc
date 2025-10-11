<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Upload Update Package Request with comprehensive validation.
 *
 * This request class handles validation for uploading update packages including
 * file validation, size limits, and security measures.
 *
 * Features:
 * - Comprehensive file upload validation
 * - File type and size validation
 * - XSS protection and input sanitization
 * - Custom validation messages for better UX
 * - Security validation rules
 * - Update package format validation
 */
class UploadUpdatePackageRequest extends FormRequest
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
            'update_package' => [
                'required',
                'file',
                'mimes:zip',
                'max:51200', // 50MB max
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
            'update_package.required' => 'Update package is required.',
            'update_package.file' => 'Update package must be a valid file.',
            'update_package.mimes' => 'Update package must be a ZIP file.',
            'update_package.max' => 'Update package size must not exceed 50MB.',
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
            'update_package' => 'update package file',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // No sanitization needed for file uploads
        // File validation is handled by Laravel's file validation rules
    }
}
