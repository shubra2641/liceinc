<?php

declare(strict_types=1);

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
        $user = auth()->user();
        return auth()->check() && $user && ($user->isAdmin || $user->hasRole('admin'));
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
            'productId' => [
                'required',
                'integer',
                'exists:products,id',
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
                    . 'html,css,json,xml,yaml,yml,md,txt,pdf,doc,docx,xls,xlsx,ppt,pptx',
                'max:102400', // 100MB
            ],
            'version' => [
                'required',
                'string',
                'max:50',
                'regex:/^[0-9]+\.[0-9]+\.[0-9]+$/',
            ],
            'fileType' => [
                'required',
                'string',
                Rule::in([
                    'source', 'binary', 'documentation', 'demo', 'template',
                    'plugin', 'theme', 'library', 'other',
                ]),
            ],
            'isActive' => [
                'boolean',
            ],
            'isRequired' => [
                'boolean',
            ],
            'is_premium' => [
                'boolean',
            ],
            'downloadCount' => [
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
            'sortOrder' => [
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
            'productId.required' => 'Product selection is required.',
            'productId.exists' => 'Selected product does not exist.',
            'name.required' => 'File name is required.',
            'name.regex' => 'File name contains invalid characters.',
            'description.regex' => 'Description contains invalid characters.',
            'file.required' => 'File upload is required.',
            'file.mimes' => 'File must be a valid file type (zip, rar, 7z, tar, gz, java, cpp, c, cs, go, rb, ' .
                'swift, kt, scala, rs, html, css, json, xml, yaml, yml, md, txt, pdf, doc, docx, xls, xlsx, ' .
                'ppt, pptx).',
            'file.max' => 'File size must not exceed 100MB.',
            'version.required' => 'File version is required.',
            'version.regex' => 'Version must be in format: x.y.z (e.g., 1.0.0).',
            'fileType.required' => 'File type is required.',
            'fileType.in' => 'File type must be one of: source, binary, documentation, demo, '
                . 'template, plugin, theme, library, other.',
            'downloadCount.min' => 'Download count cannot be negative.',
            'file_size.min' => 'File size must be at least 1 byte.',
            'file_size.max' => 'File size must not exceed 1GB.',
            'checksum.regex' => 'Checksum must be a valid hexadecimal string.',
            'release_notes.regex' => 'Release notes contain invalid characters.',
            'changelog.regex' => 'Changelog contains invalid characters.',
            'compatibility.regex' => 'Compatibility information contains invalid characters.',
            'requirements.regex' => 'Requirements contain invalid characters.',
            'installation_instructions.regex' => 'Installation instructions contain invalid characters.',
            'sortOrder.min' => 'Sort order must be at least 0.',
            'sortOrder.max' => 'Sort order must not exceed 9999.',
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
            'productId' => 'product',
            'name' => 'file name',
            'description' => 'file description',
            'file' => 'file upload',
            'version' => 'file version',
            'fileType' => 'file type',
            'isActive' => 'active status',
            'isRequired' => 'required file',
            'is_premium' => 'premium file',
            'downloadCount' => 'download count',
            'file_size' => 'file size',
            'checksum' => 'file checksum',
            'release_notes' => 'release notes',
            'changelog' => 'changelog',
            'compatibility' => 'compatibility',
            'requirements' => 'system requirements',
            'installation_instructions' => 'installation instructions',
            'sortOrder' => 'sort order',
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
            'release_notes' => $this->input('release_notes')
                ? $this->sanitizeInput($this->input('release_notes'))
                : null,
            'changelog' => $this->input('changelog') ? $this->sanitizeInput($this->input('changelog')) : null,
            'compatibility' => $this->input('compatibility')
                ? $this->sanitizeInput($this->input('compatibility'))
                : null,
            'requirements' => $this->input('requirements') ? $this->sanitizeInput($this->input('requirements')) : null,
            'installation_instructions' => $this->input('installation_instructions')
                ? $this->sanitizeInput($this->input('installation_instructions'))
                : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'isActive' => $this->has('isActive'),
            'isRequired' => $this->has('isRequired'),
            'is_premium' => $this->has('is_premium'),
        ]);
        // Set default values
        $this->merge([
            'isActive' => $this->isActive ?? true,
            'downloadCount' => $this->downloadCount ?? 0,
            'sortOrder' => $this->sortOrder ?? 0,
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
