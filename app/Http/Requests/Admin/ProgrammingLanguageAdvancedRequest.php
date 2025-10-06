<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
/**
 * Programming Language Advanced Request with enhanced security.
 *
 * This unified request class handles validation for template management,
 * content creation, and advanced programming language operations with
 * comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for template and content operations
 * - XSS protection and input sanitization
 * - File upload validation with security checks
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Template file validation and content management
 */
class ProgrammingLanguageAdvancedRequest extends FormRequest
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
        $isTemplate = $this->isMethod('POST') && str_contains($this->route()->getName(), 'template');
        $isContent = $this->isMethod('POST') && str_contains($this->route()->getName(), 'content');
        // Template file validation
        if ($isTemplate) {
            return [
                'template_file' => [
                    'required',
                    'file',
                    'mimes:java,cpp,c,cs,go,rb,swift,kt,scala,rs,html,css,json,xml,yaml,yml,md,txt',
                    'max:10240', // 10MB
                ],
                'template_type' => [
                    'required',
                    'string',
                    'max:50',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'template_description' => [
                    'nullable',
                    'string',
                    'max:500',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'is_default' => [
                    'boolean',
                ],
                'version' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[0-9]+\.[0-9]+(\.[0-9]+)?$/',
                ],
                'compatibility' => [
                    'nullable',
                    'string',
                    'max:200',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
            ];
        }
        // Content creation validation
        if ($isContent) {
            return [
                'template_content' => [
                    'required',
                    'string',
                    'max:50000',
                    'min:10',
                ],
                'template_name' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'template_type' => [
                    'required',
                    'string',
                    'max:50',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'template_description' => [
                    'nullable',
                    'string',
                    'max:500',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'is_active' => [
                    'boolean',
                ],
                'version' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[0-9]+\.[0-9]+(\.[0-9]+)?$/',
                ],
                'author' => [
                    'nullable',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'license' => [
                    'nullable',
                    'string',
                    'max:100',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'tags' => [
                    'nullable',
                    'array',
                ],
                'tags.*' => [
                    'string',
                    'max:50',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
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
                'indentation_size' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:8',
                ],
                'line_ending' => [
                    'nullable',
                    'string',
                    Rule::in(['lf', 'crlf', 'cr']),
                ],
                'encoding' => [
                    'nullable',
                    'string',
                    Rule::in(['utf-8', 'utf-16', 'ascii', 'latin1']),
                ],
            ];
        }
        // Default validation (should not reach here)
        return [];
    }
    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'template_file.required' => 'Template file is required.',
            'template_file.mimes' => 'Template file must be a valid programming language file.',
            'template_file.max' => 'Template file size must not exceed 10MB.',
            'template_type.required' => 'Template type is required.',
            'template_type.regex' => 'Template type contains invalid characters.',
            'template_description.regex' => 'Template description contains invalid characters.',
            'template_content.required' => 'Template content is required.',
            'template_content.min' => 'Template content must be at least 10 characters.',
            'template_content.max' => 'Template content must not exceed 50,000 characters.',
            'template_name.required' => 'Template name is required.',
            'template_name.regex' => 'Template name contains invalid characters.',
            'version.regex' => 'Version must be in format: x.y or x.y.z (e.g., 1.0 or 1.0.0).',
            'compatibility.regex' => 'Compatibility information contains invalid characters.',
            'author.regex' => 'Author name contains invalid characters.',
            'license.regex' => 'License information contains invalid characters.',
            'tags.*.regex' => 'Tag contains invalid characters.',
            'indentation_size.min' => 'Indentation size must be at least 1.',
            'indentation_size.max' => 'Indentation size must not exceed 8.',
            'line_ending.in' => 'Line ending must be one of: lf, crlf, cr.',
            'encoding.in' => 'Encoding must be one of: utf-8, utf-16, ascii, latin1.',
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
            'template_file' => 'template file',
            'template_type' => 'template type',
            'template_description' => 'template description',
            'is_default' => 'default template',
            'version' => 'template version',
            'compatibility' => 'compatibility',
            'template_content' => 'template content',
            'template_name' => 'template name',
            'is_active' => 'active status',
            'author' => 'template author',
            'license' => 'template license',
            'tags' => 'template tags',
            'syntax_highlighting' => 'syntax highlighting',
            'auto_completion' => 'auto completion',
            'error_detection' => 'error detection',
            'indentation_size' => 'indentation size',
            'line_ending' => 'line ending',
            'encoding' => 'file encoding',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'template_type' => $this->sanitizeInput($this->template_type),
            'template_description' => $this->template_description
                ? $this->sanitizeInput($this->template_description)
                : null,
            'template_name' => $this->template_name ? $this->sanitizeInput($this->template_name) : null,
            'version' => $this->version === true ? $this->sanitizeInput($this->version) : null,
            'compatibility' => $this->compatibility ? $this->sanitizeInput($this->compatibility) : null,
            'author' => $this->author ? $this->sanitizeInput($this->author) : null,
            'license' => $this->license ? $this->sanitizeInput($this->license) : null,
        ]);
        // Sanitize tags array
        if ($this->tags && is_array($this->tags)) {
            $sanitizedTags = [];
            foreach ($this->tags as $tag) {
                $sanitizedTags[] = $this->sanitizeInput($tag);
            }
            $this->merge(['tags' => $sanitizedTags]);
        }
        // Handle checkbox values
        $this->merge([
            'is_default' => $this->has('is_default'),
            'is_active' => $this->has('is_active'),
            'syntax_highlighting' => $this->has('syntax_highlighting'),
            'auto_completion' => $this->has('auto_completion'),
            'error_detection' => $this->has('error_detection'),
        ]);
        // Set default values
        $this->merge([
            'is_active' => $this->is_active ?? true,
            'indentation_size' => $this->indentation_size ?? 4,
            'line_ending' => $this->line_ending ?? 'lf',
            'encoding' => $this->encoding ?? 'utf-8',
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
