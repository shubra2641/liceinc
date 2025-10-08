<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Product Category Request with enhanced security.
 *
 * This unified request class handles validation for both creating and updating
 * product categories with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both store and update operations
 * - XSS protection and input sanitization
 * - File upload validation with security checks
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Unique validation with ignore for current record on updates
 * - Auto-slug generation and checkbox handling
 */
class ProductCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();

        return auth()->check() && $user && ($user->is_admin || $user->hasRole('admin'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->route('product_category');
        $categoryId = $category && is_object($category) && property_exists($category, 'id') ? $category->id : null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $isUpdate ? Rule::unique('product_categories', 'name')->ignore($categoryId)
                    : 'unique:product_categories, name',
                'regex:/^[a-zA-Z0-9\s\-_&]+$/',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                $isUpdate
                    ? Rule::unique('product_categories', 'slug')->ignore($categoryId)
                    : 'unique:product_categories, slug',
                'regex:/^[a-z0-9\-_]+$/',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[\p{L}\p{N}\p{P}\p{Z}]+$/u',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg, png, jpg, gif, webp',
                'max:2048',
                'dimensions:max_width=1920, max_height=1080',
            ],
            'is_active' => [
                'boolean',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
            'parent_id' => [
                'nullable',
                'exists:product_categories,id',
                'not_in:'.(is_string($categoryId) ? $categoryId : ''),
            ],
            'meta_title' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{N}\p{P}\p{Z}]+$/u',
            ],
            'meta_keywords' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[\p{L}\p{N}\p{P}\p{Z}]+$/u',
            ],
            'meta_description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[\p{L}\p{N}\p{P}\p{Z}]+$/u',
            ],
            'color' => [
                'nullable',
                'string',
                'max:7',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'text_color' => [
                'nullable',
                'string',
                'max:7',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
            'show_in_menu' => [
                'boolean',
            ],
            'is_featured' => [
                'boolean',
            ],
            'allow_subcategories' => [
                'boolean',
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
            'name.regex' => 'Category name contains invalid characters. Only letters, numbers, '
                .'spaces, hyphens, underscores, and ampersands are allowed.',
            'name.unique' => 'A category with this name already exists.',
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, hyphens, and underscores.',
            'slug.unique' => 'A category with this slug already exists.',
            'description.regex' => 'Description contains invalid characters.',
            'image.dimensions' => 'Image dimensions must not exceed 1920x1080 pixels.',
            'image.max' => 'Image size must not exceed 2MB.',
            'image.mimes' => 'Image must be a file of type: jpeg, png, jpg, gif, webp.',
            'color.regex' => 'Color must be a valid hex color code (e.g., #3b82f6).',
            'text_color.regex' => 'Text color must be a valid hex color code (e.g., #ffffff).',
            'icon.regex' => 'Icon contains invalid characters.',
            'meta_title.regex' => 'Meta title contains invalid characters.',
            'meta_keywords.regex' => 'Meta keywords contain invalid characters.',
            'meta_description.regex' => 'Meta description contains invalid characters.',
            'sort_order.min' => 'Sort order must be at least 0.',
            'sort_order.max' => 'Sort order must not exceed 9999.',
            'parent_id.exists' => 'Selected parent category does not exist.',
            'parent_id.not_in' => 'A category cannot be its own parent.',
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
            'name' => 'category name',
            'slug' => 'category slug',
            'description' => 'category description',
            'image' => 'category image',
            'is_active' => 'active status',
            'sort_order' => 'sort order',
            'meta_title' => 'meta title',
            'meta_keywords' => 'meta keywords',
            'meta_description' => 'meta description',
            'color' => 'category color',
            'text_color' => 'text color',
            'icon' => 'category icon',
            'show_in_menu' => 'show in menu',
            'is_featured' => 'featured status',
            'parent_id' => 'parent category',
            'allow_subcategories' => 'allow subcategories',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'name' => $this->sanitizeInput($this->input('name')),
            'description' => $this->input('description') ? $this->sanitizeInput($this->input('description')) : null,
            'meta_title' => $this->input('meta_title') ? $this->sanitizeInput($this->input('meta_title')) : null,
            'meta_keywords' => $this->input('meta_keywords') ? $this->sanitizeInput($this->input('meta_keywords')) : null,
            'meta_description' => $this->input('meta_description') ? $this->sanitizeInput($this->input('meta_description')) : null,
            'icon' => $this->input('icon') ? $this->sanitizeInput($this->input('icon')) : null,
        ]);
        // Auto-generate slug if not provided
        $name = $this->input('name');
        if (! $this->input('slug') && $name && is_string($name)) {
            $this->merge([
                'slug' => Str::slug($name),
            ]);
        }
        // Handle checkbox values
        $this->merge([
            'is_active' => $this->has('is_active'),
            'show_in_menu' => $this->has('show_in_menu'),
            'is_featured' => $this->has('is_featured'),
            'allow_subcategories' => $this->has('allow_subcategories'),
        ]);
    }

    /**
     * Sanitize input to prevent XSS attacks.
     *
     * @param  mixed  $input  The input to sanitize
     *
     * @return string|null The sanitized input
     */
    private function sanitizeInput(mixed $input): ?string
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (! is_string($input)) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
