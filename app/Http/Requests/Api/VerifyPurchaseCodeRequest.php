<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Verify Purchase Code Request with comprehensive validation.
 *
 * This request class handles validation for purchase code verification
 * with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Comprehensive purchase code validation
 * - XSS protection and input sanitization
 * - Custom validation messages for better UX
 * - Security validation rules
 * - Purchase code format validation with regex patterns
 */
class VerifyPurchaseCodeRequest extends FormRequest
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
            'purchase_code' => [
                'required',
                'string',
                'min:10',
                'max:100',
                'regex:/^[A-Z0-9\-]+$/',
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
            'purchase_code.required' => 'Purchase code is required.',
            'purchase_code.string' => 'Purchase code must be a valid string.',
            'purchase_code.min' => 'Purchase code must be at least 10 characters long.',
            'purchase_code.max' => 'Purchase code cannot exceed 100 characters.',
            'purchase_code.regex' => 'Purchase code contains invalid characters. '.
                'Only uppercase letters, numbers, and hyphens are allowed.',
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
            'purchase_code' => 'purchase code',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'purchase_code' => $this->purchase_code && is_string($this->purchase_code) ? trim($this->purchase_code) : null,
        ]);
    }
}
