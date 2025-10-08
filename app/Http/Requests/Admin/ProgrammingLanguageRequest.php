<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Programming Language Request with enhanced security.
 *
 * This unified request class handles validation for both creating and updating
 * programming languages with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both store and update operations
 * - XSS protection and input sanitization
 * - File upload validation with security checks
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Unique validation with ignore for current record on updates
 * - Template file validation
 * - Language configuration validation
 */
class ProgrammingLanguageRequest extends FormRequest
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
        $language = $this->route('programming_language');
        $languageId = $language && is_object($language) && property_exists($language, 'id') ? $language->id : null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                $isUpdate
                    ? Rule::unique('programming_languages', 'name')->ignore($languageId)
                    : 'unique:programming_languages, name',
            ],
            'extension' => [
                'required',
                'string',
                'max:10',
                'regex:/^[a-zA-Z0-9]+$/',
                $isUpdate
                    ? Rule::unique('programming_languages', 'extension')->ignore($languageId)
                    : 'unique:programming_languages, extension',
            ],
            'mime_type' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-\/]+$/',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'template_file' => [
                'nullable',
                'file',
                'mimes:java,cpp,c,cs,go,rb,swift,kt,scala,rs,html,css,json,xml,yaml,yml,md,txt',
                'max:10240', // 10MB
            ],
            'template_content' => [
                'nullable',
                'string',
                'max:50000',
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
            'icon' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'color' => [
                'nullable',
                'string',
                'max:7',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'syntax_highlighting' => [
                'boolean',
            ],
            'auto_completion' => [
                'boolean',
            ],
            'error_detection' => [
                'boolean',
            ],
            'version' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9]+\.[0-9]+(\.[0-9]+)?$/',
            ],
            'website_url' => [
                'nullable',
                'url',
                'max:500',
            ],
            'documentation_url' => [
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
            'name.required' => 'Programming language name is required.',
            'name.unique' => 'A programming language with this name already exists.',
            'name.regex' => 'Name contains invalid characters.',
            'extension.required' => 'File extension is required.',
            'extension.unique' => 'A programming language with this extension already exists.',
            'extension.regex' => 'Extension can only contain letters and numbers.',
            'mime_type.required' => 'MIME type is required.',
            'mime_type.regex' => 'MIME type contains invalid characters.',
            'description.regex' => 'Description contains invalid characters.',
            'template_file.mimes' => 'Template file must be a valid programming language file.',
            'template_file.max' => 'Template file size must not exceed 10MB.',
            'template_content.max' => 'Template content must not exceed 50,000 characters.',
            'sort_order.min' => 'Sort order must be at least 0.',
            'sort_order.max' => 'Sort order must not exceed 9999.',
            'icon.regex' => 'Icon contains invalid characters.',
            'color.regex' => 'Color must be a valid hex color code (e.g., #3b82f6).',
            'version.regex' => 'Version must be in format: x.y or x.y.z (e.g., 1.0 or 1.0.0).',
            'website_url.url' => 'Website URL must be a valid URL.',
            'documentation_url.url' => 'Documentation URL must be a valid URL.',
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
            'name' => 'programming language name',
            'extension' => 'file extension',
            'mime_type' => 'MIME type',
            'description' => 'language description',
            'template_file' => 'template file',
            'template_content' => 'template content',
            'is_active' => 'active status',
            'sort_order' => 'sort order',
            'icon' => 'language icon',
            'color' => 'language color',
            'syntax_highlighting' => 'syntax highlighting',
            'auto_completion' => 'auto completion',
            'error_detection' => 'error detection',
            'version' => 'language version',
            'website_url' => 'website URL',
            'documentation_url' => 'documentation URL',
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
            'icon' => $this->input('icon') ? $this->sanitizeInput($this->input('icon')) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'is_active' => $this->has('is_active'),
            'syntax_highlighting' => $this->has('syntax_highlighting'),
            'auto_completion' => $this->has('auto_completion'),
            'error_detection' => $this->has('error_detection'),
        ]);
        // Set default values
        $this->merge([
            'is_active' => $this->is_active ?? true,
            'sort_order' => $this->sort_order ?? 0,
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
