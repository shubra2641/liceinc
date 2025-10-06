<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
/**
 * Product File Request with enhanced security.
 *
 * This unified request class handles validation for both creating and updating
 * product files with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both store and update operations
 * - XSS protection and input sanitization
 * - File upload validation with security checks
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - File type and size validation
 * - Version and release management
 */
class ProductFileRequest extends FormRequest
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
        $fileId = $this->route('file')->id ?? null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products, id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'file' => [
                $isUpdate ? 'nullable' : 'required',
                'file',
                'mimes:zip,rar,7z,tar,gz,java,cpp,c,cs,go,rb,swift,kt,scala,rs,'
                    .'html,css,json,xml,yaml,yml,md,txt,pdf,doc,docx,xls,xlsx,ppt,pptx',
                'max:102400', // 100MB
            ],
            'version' => [
                'required',
                'string',
                'max:50',
                'regex:/^[0-9]+\.[0-9]+\.[0-9]+$/',
            ],
            'file_type' => [
                'required',
                'string',
                Rule::in([
                    'source', 'binary', 'documentation', 'demo', 'template',
                    'plugin', 'theme', 'library', 'other',
                ]),
            ],
            'is_active' => [
                'boolean',
            ],
            'is_required' => [
                'boolean',
            ],
            'is_premium' => [
                'boolean',
            ],
            'download_count' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'file_size' => [
                'nullable',
                'integer',
                'min:1',
                'max:1073741824', // 1GB
            ],
            'checksum' => [
                'nullable',
                'string',
                'max:128',
                'regex:/^[a-fA-F0-9]+$/',
            ],
            'release_notes' => [
                'nullable',
                'string',
                'max:5000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'changelog' => [
                'nullable',
                'string',
                'max:10000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'compatibility' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'requirements' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'installation_instructions' => [
                'nullable',
                'string',
                'max:5000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
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
            'product_id.required' => 'Product selection is required.',
            'product_id.exists' => 'Selected product does not exist.',
            'name.required' => 'File name is required.',
            'name.regex' => 'File name contains invalid characters.',
            'description.regex' => 'Description contains invalid characters.',
            'file.required' => 'File upload is required.',
            'file.mimes' => 'File must be a valid file type (zip, rar, 7z, tar, gz, java, cpp, c, cs, go, rb, '
                .'swift, kt, scala, rs, html, css, json, xml, yaml, yml, md, txt, pdf, doc, docx, xls, xlsx, ppt, pptx).',
            'file.max' => 'File size must not exceed 100MB.',
            'version.required' => 'File version is required.',
            'version.regex' => 'Version must be in format: x.y.z (e.g., 1.0.0).',
            'file_type.required' => 'File type is required.',
            'file_type.in' => 'File type must be one of: source, binary, documentation, demo, '
                .'template, plugin, theme, library, other.',
            'download_count.min' => 'Download count cannot be negative.',
            'file_size.min' => 'File size must be at least 1 byte.',
            'file_size.max' => 'File size must not exceed 1GB.',
            'checksum.regex' => 'Checksum must be a valid hexadecimal string.',
            'release_notes.regex' => 'Release notes contain invalid characters.',
            'changelog.regex' => 'Changelog contains invalid characters.',
            'compatibility.regex' => 'Compatibility information contains invalid characters.',
            'requirements.regex' => 'Requirements contain invalid characters.',
            'installation_instructions.regex' => 'Installation instructions contain invalid characters.',
            'sort_order.min' => 'Sort order must be at least 0.',
            'sort_order.max' => 'Sort order must not exceed 9999.',
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
            'product_id' => 'product',
            'name' => 'file name',
            'description' => 'file description',
            'file' => 'file upload',
            'version' => 'file version',
            'file_type' => 'file type',
            'is_active' => 'active status',
            'is_required' => 'required file',
            'is_premium' => 'premium file',
            'download_count' => 'download count',
            'file_size' => 'file size',
            'checksum' => 'file checksum',
            'release_notes' => 'release notes',
            'changelog' => 'changelog',
            'compatibility' => 'compatibility',
            'requirements' => 'system requirements',
            'installation_instructions' => 'installation instructions',
            'sort_order' => 'sort order',
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
            'description' => $this->description ? $this->sanitizeInput($this->description) : null,
            'release_notes' => $this->release_notes
                ? $this->sanitizeInput($this->release_notes)
                : null,
            'changelog' => $this->changelog ? $this->sanitizeInput($this->changelog) : null,
            'compatibility' => $this->compatibility ? $this->sanitizeInput($this->compatibility) : null,
            'requirements' => $this->requirements ? $this->sanitizeInput($this->requirements) : null,
            'installation_instructions' => $this->installation_instructions
                ? $this->sanitizeInput($this->installation_instructions)
                : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'is_active' => $this->has('is_active'),
            'is_required' => $this->has('is_required'),
            'is_premium' => $this->has('is_premium'),
        ]);
        // Set default values
        $this->merge([
            'is_active' => $this->is_active ?? true,
            'download_count' => $this->download_count ?? 0,
            'sort_order' => $this->sort_order ?? 0,
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
