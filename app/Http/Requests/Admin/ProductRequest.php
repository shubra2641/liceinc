<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Product Request with enhanced security.
 *
 * This unified request class handles validation for both creating and updating
 * products with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both store and update operations
 * - XSS protection and input sanitization
 * - File upload validation with security checks
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Unique validation with ignore for current record on updates
 * - SEO metadata validation
 * - Pricing and licensing validation
 */
class ProductRequest extends FormRequest
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
        $productId = $this->route('product')->id ?? null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-_]+$/',
                $isUpdate ? Rule::unique('products', 'slug')->ignore($productId) : 'unique:products,slug',
            ],
            'description' => [
                'required',
                'string',
                'max:2000',
            ],
            'short_description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'category_id' => [
                'required',
                'integer',
                'exists:product_categories,id',
            ],
            'programming_language' => [
                'nullable',
                'integer',
                'exists:programming_languages,id',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'currency' => [
                'required',
                'string',
                Rule::in(['USD', 'EUR', 'GBP', 'CAD', 'AUD']),
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120',
                'dimensions:max_width=2048,max_height=2048',
            ],
            'version' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9]+(\.[0-9]+)*$/',
            ],
            'is_active' => [
                'boolean',
            ],
            'is_featured' => [
                'boolean',
            ],
            'is_popular' => [
                'boolean',
            ],
            'is_downloadable' => [
                'boolean',
            ],
            'requires_domain' => [
                'boolean',
            ],
            'kb_access_required' => [
                'boolean',
            ],
            'auto_renewal' => [
                'boolean',
            ],
            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'meta_description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'meta_keywords' => [
                'nullable',
                'string',
                'max:500',
            ],
            'envato_item_id' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[0-9]+$/',
            ],
            'envato_url' => [
                'nullable',
                'url',
                'max:500',
            ],
            'demo_url' => [
                'nullable',
                'url',
                'max:500',
            ],
            'documentation_url' => [
                'nullable',
                'url',
                'max:500',
            ],
            'support_url' => [
                'nullable',
                'url',
                'max:500',
            ],
            'short_description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'support_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:3650', // 10 years
            ],
            'stock_quantity' => [
                'nullable',
                'integer',
                'min:-1', // -1 for unlimited
            ],
            'license_type' => [
                'nullable',
                'string',
                'in:single,multi,developer,unlimited,regular,extended',
            ],
            'renewal_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'renewal_period' => [
                'nullable',
                'string',
                'in:monthly,quarterly,semi_annual,annual,three_years,lifetime',
            ],
            'duration_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:3650', // 10 years
            ],
            'tax_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'extended_support_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'extended_support_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:3650',
            ],
            'renewal_reminder_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:365',
            ],
            'status' => [
                'nullable',
                'string',
                'in:active,inactive,draft,archived',
            ],
            'stock' => [
                'nullable',
                'integer',
                'min:-1',
            ],
            'supported_until' => [
                'nullable',
                'date',
                'after:today',
            ],
            'extended_supported_until' => [
                'nullable',
                'date',
                'after:today',
            ],
            'features' => [
                'nullable',
                'string',
            ],
            'requirements' => [
                'nullable',
                'string',
            ],
            'installation_guide' => [
                'nullable',
                'string',
            ],
            'tags' => [
                'nullable',
                'string',
            ],
            'gallery_images' => [
                'nullable',
                'array',
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
            'name.required' => 'Product name is required.',
            'name.regex' => 'Product name contains invalid characters.',
            'slug.required' => 'Product slug is required.',
            'slug.unique' => 'A product with this slug already exists.',
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, hyphens, and underscores.',
            'description.required' => 'Product description is required.',
            'description.regex' => 'Description contains invalid characters.',
            'short_description.regex' => 'Short description contains invalid characters.',
            'category_id.required' => 'Product category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'programming_language_id.exists' => 'Selected programming language does not exist.',
            'price.required' => 'Product price is required.',
            'price.min' => 'Price must be at least 0.',
            'price.max' => 'Price cannot exceed 999,999.99.',
            'currency.required' => 'Currency is required.',
            'currency.in' => 'Currency must be one of: USD, EUR, GBP, CAD, AUD.',
            'image.dimensions' => 'Image dimensions must not exceed 2048x2048 pixels.',
            'image.max' => 'Image size must not exceed 5MB.',
            'image.mimes' => 'Image must be a file of type: jpeg, png, jpg, gif, webp.',
            'version.required' => 'Product version is required.',
            'version.regex' => 'Version must be in format: x.y.z (e.g., 1.0.0).',
            'meta_title.regex' => 'Meta title contains invalid characters.',
            'meta_description.regex' => 'Meta description contains invalid characters.',
            'meta_keywords.regex' => 'Meta keywords contain invalid characters.',
            'envato_item_id.regex' => 'Envato item ID must contain only numbers.',
            'envato_url.url' => 'Envato URL must be a valid URL.',
            'demo_url.url' => 'Demo URL must be a valid URL.',
            'documentation_url.url' => 'Documentation URL must be a valid URL.',
            'support_url.url' => 'Support URL must be a valid URL.',
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
            'name' => 'product name',
            'slug' => 'product slug',
            'description' => 'product description',
            'short_description' => 'short description',
            'category_id' => 'product category',
            'programming_language_id' => 'programming language',
            'price' => 'product price',
            'currency' => 'currency',
            'image' => 'product image',
            'version' => 'product version',
            'is_active' => 'active status',
            'is_featured' => 'featured status',
            'is_popular' => 'popular status',
            'is_downloadable' => 'downloadable status',
            'requires_domain' => 'domain requirement',
            'kb_access_required' => 'KB access requirement',
            'auto_renewal' => 'auto renewal',
            'meta_title' => 'meta title',
            'meta_description' => 'meta description',
            'meta_keywords' => 'meta keywords',
            'envato_item_id' => 'Envato item ID',
            'envato_url' => 'Envato URL',
            'demo_url' => 'demo URL',
            'documentation_url' => 'documentation URL',
            'support_url' => 'support URL',
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
            'description' => $this->sanitizeInput($this->input('description')),
            'short_description' => $this->input('short_description')
                ? $this->sanitizeInput($this->input('short_description'))
                : null,
            'meta_title' => $this->input('meta_title') ? $this->sanitizeInput($this->input('meta_title')) : null,
            'meta_description' => $this->input('meta_description')
                ? $this->sanitizeInput($this->input('meta_description'))
                : null,
            'meta_keywords' => $this->input('meta_keywords')
                ? $this->sanitizeInput($this->input('meta_keywords'))
                : null,
            'features' => $this->input('features') ? $this->sanitizeInput($this->input('features')) : null,
            'requirements' => $this->input('requirements') ? $this->sanitizeInput($this->input('requirements')) : null,
            'installation_guide' => $this->input('installation_guide') 
                ? $this->sanitizeInput($this->input('installation_guide')) : null,
            'tags' => $this->input('tags') ? $this->sanitizeInput($this->input('tags')) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'is_popular' => $this->boolean('is_popular'),
            'is_downloadable' => $this->boolean('is_downloadable'),
            'requires_domain' => $this->boolean('requires_domain'),
            'kb_access_required' => $this->boolean('kb_access_required'),
            'auto_renewal' => $this->boolean('auto_renewal'),
        ]);
        // Set default values
        $this->merge([
            'is_active' => $this->is_active ?? true,
            'currency' => $this->currency ?? 'USD',
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

        if (!is_string($input)) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
