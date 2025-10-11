<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Product Search Request with enhanced security.
 *
 * This request class handles validation for product search and filtering
 * with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Enhanced security measures (XSS protection, input validation)
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Input sanitization and validation
 */
class ProductSearchRequest extends FormRequest
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
            'search' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_&]+$/',
            ],
            'category' => [
                'nullable',
                'integer',
                'exists:product_categories,id',
            ],
            'language' => [
                'nullable',
                'integer',
                'exists:programming_languages,id',
            ],
            'price_filter' => [
                'nullable',
                'string',
                'in:free,paid,all',
            ],
            'sort' => [
                'nullable',
                'string',
                'in:name,price,created_at,updated_at',
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
            'search.string' => 'Search term must be a string.',
            'search.max' => 'Search term must not exceed 255 characters.',
            'search.regex' => 'Search term contains invalid characters.',
            'category.integer' => 'Category must be a valid ID.',
            'category.exists' => 'Selected category does not exist.',
            'language.integer' => 'Language must be a valid ID.',
            'language.exists' => 'Selected language does not exist.',
            'price_filter.in' => 'Price filter must be one of: free, paid, all.',
            'sort.in' => 'Sort must be one of: name, price, created_at, updated_at.',
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
            'search' => 'search term',
            'category' => 'category',
            'language' => 'programming language',
            'price_filter' => 'price filter',
            'sort' => 'sort option',
        ];
    }
}
