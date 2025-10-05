<?php
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
/
class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->is_admin || auth()->user()->hasRole('admin'));
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
                $isUpdate ? Rule::unique('products', 'slug')->ignore($productId) : 'unique:products, slug',
            ],
            'description' => [
                'required',
                'string',
                'max:2000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'short_description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'category_id' => [
                'required',
                'integer',
                'exists:product_categories, id',
            ],
            'programming_language_id' => [
                'nullable',
                'integer',
                'exists:programming_languages, id',
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
                'required',
                'string',
                'max:50',
                'regex:/^[0-9]+\.[0-9]+\.[0-9]+$/',
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
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'meta_description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'meta_keywords' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
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
            'name' => $this->sanitizeInput($this->name),
            'description' => $this->sanitizeInput($this->description),
            'short_description' => $this->short_description ? $this->sanitizeInput($this->short_description) : null,
            'meta_title' => $this->meta_title ? $this->sanitizeInput($this->meta_title) : null,
            'meta_description' => $this->meta_description ? $this->sanitizeInput($this->meta_description) : null,
            'meta_keywords' => $this->meta_keywords ? $this->sanitizeInput($this->meta_keywords) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'is_active' => $this->has('is_active'),
            'is_featured' => $this->has('is_featured'),
            'is_popular' => $this->has('is_popular'),
            'is_downloadable' => $this->has('is_downloadable'),
            'requires_domain' => $this->has('requires_domain'),
            'kb_access_required' => $this->has('kb_access_required'),
            'auto_renewal' => $this->has('auto_renewal'),
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
     * @param  string|null  $input  The input to sanitize
     *
     * @return string|null The sanitized input
     */
    private function sanitizeInput(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
