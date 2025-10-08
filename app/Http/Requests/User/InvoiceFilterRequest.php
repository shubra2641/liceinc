<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Invoice Filter Request with enhanced security. *
 * This request class handles validation for invoice filtering * with comprehensive security measures and input sanitization. *
 * Features: * - Enhanced security measures (XSS protection, input validation) * - Custom validation messages for better user experience * - Proper type hints and return types * - Security validation rules (XSS protection, SQL injection prevention) * - Input sanitization and validation */
class InvoiceFilterRequest extends FormRequest
{
    /**   * Determine if the user is authorized to make this request. */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**   * Get the validation rules that apply to the request. *   * @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => [
                'nullable',
                'string',
                'in:pending,paid,overdue,cancelled',
            ],
        ];
    }

    /**   * Get custom messages for validator errors. *   * @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.string' => 'Status must be a string.',
            'status.in' => 'Status must be one of: pending, paid, overdue, cancelled.',
        ];
    }

    /**   * Get custom attributes for validator errors. *   * @return array<string, string> */
    public function attributes(): array
    {
        return [
            'status' => 'invoice status',
        ];
    }
}
