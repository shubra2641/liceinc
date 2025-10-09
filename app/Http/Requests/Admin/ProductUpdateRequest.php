<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Product Update Request with enhanced security.
 *
 * This unified request class handles validation for both creating and updating
 * product updates with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both store and update operations
 * - XSS protection and input sanitization
 * - File upload validation with security checks
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Version validation and file security
 * - Update type and status management
 */
class ProductUpdateRequest extends FormRequest
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
        $update = $this->route('product_update');
        $updateId = $update && is_object($update) && property_exists($update, 'id') ? $update->id : null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'version' => [
                'required',
                'string',
                'max:50',
                'regex:/^[0-9]+\.[0-9]+\.[0-9]+$/',
                $isUpdate
                    ? Rule::unique('product_updates', 'version')
                        ->where('product_id', $this->getProductId())
                        ->ignore($updateId)
                    : Rule::unique('product_updates', 'version')
                        ->where('product_id', $this->getProductId()),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'changelog' => [
                'nullable',
                'string',
                'max:5000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'update_file' => [
                $isUpdate ? 'nullable' : 'required',
                'file',
                'mimes:zip,rar,7z,tar,gz',
                'max:102400', // 100MB
            ],
            'is_major' => [
                'boolean',
            ],
            'is_required' => [
                'boolean',
            ],
            'is_active' => [
                'boolean',
            ],
            'release_notes' => [
                'nullable',
                'string',
                'max:10000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'download_url' => [
                'nullable',
                'url',
                'max:500',
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
            'version.required' => 'Version number is required.',
            'version.regex' => 'Version must be in format: x.y.z (e.g., 1.0.0).',
            'version.unique' => 'This version already exists for the selected product.',
            'title.required' => 'Update title is required.',
            'title.regex' => 'Title contains invalid characters.',
            'description.regex' => 'Description contains invalid characters.',
            'changelog.regex' => 'Changelog contains invalid characters.',
            'update_file.required' => 'Update file is required.',
            'update_file.mimes' => 'Update file must be a compressed archive (zip, rar, 7z, tar, gz).',
            'update_file.max' => 'Update file size must not exceed 100MB.',
            'release_notes.regex' => 'Release notes contain invalid characters.',
            'download_url.url' => 'Download URL must be a valid URL.',
            'file_size.min' => 'File size must be at least 1 byte.',
            'file_size.max' => 'File size must not exceed 1GB.',
            'checksum.regex' => 'Checksum must be a valid hexadecimal string.',
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
            'version' => 'version number',
            'title' => 'update title',
            'description' => 'update description',
            'changelog' => 'changelog',
            'update_file' => 'update file',
            'is_major' => 'major update',
            'is_required' => 'required update',
            'is_active' => 'active status',
            'release_notes' => 'release notes',
            'download_url' => 'download URL',
            'file_size' => 'file size',
            'checksum' => 'file checksum',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'title' => $this->sanitizeInput($this->input('title')),
            'description' => $this->input('description') ? $this->sanitizeInput($this->input('description')) : null,
            'changelog' => $this->input('changelog') ? $this->sanitizeInput($this->input('changelog')) : null,
            'release_notes' => $this->input('release_notes')
                ? $this->sanitizeInput($this->input('release_notes'))
                : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'is_major' => $this->has('is_major'),
            'is_required' => $this->has('is_required'),
            'is_active' => $this->has('is_active'),
        ]);
        // Set default values
        $this->merge([
            'is_active' => $this->is_active ?? true,
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

    /**
     * Get the product ID safely.
     *
     * @return int|null
     */
    private function getProductId(): ?int
    {
        $productId = $this->input('product_id');
        return is_numeric($productId) ? (int) $productId : null;
    }
}
