<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Verify Article Serial Request with comprehensive validation.
 *
 * This request class handles validation for verifying serial codes
 * to access protected knowledge base articles.
 *
 * Features:
 * - Comprehensive serial code validation
 * - XSS protection and input sanitization
 * - Custom validation messages for better UX
 * - Security validation rules
 * - Serial format validation with regex patterns
 */
class VerifyArticleSerialRequest extends FormRequest
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
            'serial' => [
                'required',
                'string',
                'max:255',
                'min:3',
                'regex:/^[A-Za-z0-9\-_]+$/',
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
            'serial.required' => 'Serial code is required.',
            'serial.string' => 'Serial code must be a valid string.',
            'serial.max' => 'Serial code cannot exceed 255 characters.',
            'serial.min' => 'Serial code must be at least 3 characters long.',
            'serial.regex' => 'Serial code contains invalid characters. '.
                'Only letters, numbers, hyphens, and underscores are allowed.',
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
            'serial' => 'serial code',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'serial' => $this->serial && is_string($this->serial) ? trim($this->serial) : null,
        ]);
    }
}
