<?php
namespace App\Http\Requests\Api;
use Illuminate\Foundation\Http\FormRequest;
/**
 * API Update Request with enhanced security.
 *
 * This unified request class handles validation for all API update operations
 * including update checking, version history, latest version, and update info
 * with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for all API update operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - License key validation
 * - Version format validation
 * - Domain validation
/
class ApiUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // API requests are generally public but validated by license
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isCheck = $this->isMethod('POST') && str_contains($this->route()->getName(), 'check');
        $isHistory = $this->isMethod('POST') && str_contains($this->route()->getName(), 'history');
        $isLatest = $this->isMethod('POST') && str_contains($this->route()->getName(), 'latest');
        $isInfo = $this->isMethod('POST') && str_contains($this->route()->getName(), 'info');
        // Check updates validation
        if ($isCheck) {
            return [
                'license_key' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_]+$/',
                ],
                'current_version' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[0-9]+\.[0-9]+(\.[0-9]+)?(-[a-zA-Z0-9]+)?$/',
                ],
                'domain' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_.]+$/',
                ],
                'product_slug' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_]+$/',
                ],
                'product_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'include_changelog' => [
                    'boolean',
                ],
                'include_dependencies' => [
                    'boolean',
                ],
                'include_security_updates' => [
                    'boolean',
                ],
                'include_feature_updates' => [
                    'boolean',
                ],
                'include_bug_fixes' => [
                    'boolean',
                ],
                'check_beta' => [
                    'boolean',
                ],
                'check_prerelease' => [
                    'boolean',
                ],
                'auto_install' => [
                    'boolean',
                ],
                'notify_on_available' => [
                    'boolean',
                ],
            ];
        }
        // Version history validation
        if ($isHistory) {
            return [
                'license_key' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_]+$/',
                ],
                'domain' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_.]+$/',
                ],
                'product_slug' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_]+$/',
                ],
                'product_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'include_changelog' => [
                    'boolean',
                ],
                'include_dependencies' => [
                    'boolean',
                ],
                'include_security_updates' => [
                    'boolean',
                ],
                'include_feature_updates' => [
                    'boolean',
                ],
                'include_bug_fixes' => [
                    'boolean',
                ],
                'limit' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],
                'offset' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
                'sort_order' => [
                    'nullable',
                    'string',
                    'max:10',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'filter_version' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[0-9]+\.[0-9]+(\.[0-9]+)?(-[a-zA-Z0-9]+)?$/',
                ],
                'filter_date_from' => [
                    'nullable',
                    'date',
                ],
                'filter_date_to' => [
                    'nullable',
                    'date',
                    'after_or_equal:filter_date_from',
                ],
            ];
        }
        // Latest version validation
        if ($isLatest) {
            return [
                'license_key' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_]+$/',
                ],
                'domain' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_.]+$/',
                ],
                'product_slug' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_]+$/',
                ],
                'product_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'include_changelog' => [
                    'boolean',
                ],
                'include_dependencies' => [
                    'boolean',
                ],
                'include_security_updates' => [
                    'boolean',
                ],
                'include_feature_updates' => [
                    'boolean',
                ],
                'include_bug_fixes' => [
                    'boolean',
                ],
                'check_beta' => [
                    'boolean',
                ],
                'check_prerelease' => [
                    'boolean',
                ],
                'current_version' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[0-9]+\.[0-9]+(\.[0-9]+)?(-[a-zA-Z0-9]+)?$/',
                ],
                'compare_versions' => [
                    'boolean',
                ],
                'include_download_url' => [
                    'boolean',
                ],
                'include_checksums' => [
                    'boolean',
                ],
                'include_file_list' => [
                    'boolean',
                ],
            ];
        }
        // Update info validation
        if ($isInfo) {
            return [
                'license_key' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_]+$/',
                ],
                'domain' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_.]+$/',
                ],
                'product_slug' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_]+$/',
                ],
                'product_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'version' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[0-9]+\.[0-9]+(\.[0-9]+)?(-[a-zA-Z0-9]+)?$/',
                ],
                'include_changelog' => [
                    'boolean',
                ],
                'include_dependencies' => [
                    'boolean',
                ],
                'include_security_updates' => [
                    'boolean',
                ],
                'include_feature_updates' => [
                    'boolean',
                ],
                'include_bug_fixes' => [
                    'boolean',
                ],
                'include_download_url' => [
                    'boolean',
                ],
                'include_checksums' => [
                    'boolean',
                ],
                'include_file_list' => [
                    'boolean',
                ],
                'include_installation_notes' => [
                    'boolean',
                ],
                'include_rollback_info' => [
                    'boolean',
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
            'license_key.required' => 'License key is required.',
            'license_key.regex' => 'License key can only contain letters, numbers, hyphens, and underscores.',
            'current_version.required' => 'Current version is required.',
            'current_version.regex' => 'Current version must be in format: x.y or x.y.z or x.y.z-suffix '.
                '(e.g., 1.0, 1.0.0, 1.0.0-beta).',
            'domain.required' => 'Domain is required.',
            'domain.regex' => 'Domain can only contain letters, numbers, hyphens, underscores, and dots.',
            'product_slug.required' => 'Product slug is required.',
            'product_slug.regex' => 'Product slug can only contain letters, numbers, hyphens, and underscores.',
            'product_id.integer' => 'Product ID must be a valid integer.',
            'product_id.min' => 'Product ID must be at least 1.',
            'version.required' => 'Version is required.',
            'version.regex' => 'Version must be in format: x.y or x.y.z or x.y.z-suffix '.
                '(e.g., 1.0, 1.0.0, 1.0.0-beta).',
            'include_changelog.boolean' => 'Include changelog must be true or false.',
            'include_dependencies.boolean' => 'Include dependencies must be true or false.',
            'include_security_updates.boolean' => 'Include security updates must be true or false.',
            'include_feature_updates.boolean' => 'Include feature updates must be true or false.',
            'include_bug_fixes.boolean' => 'Include bug fixes must be true or false.',
            'check_beta.boolean' => 'Check beta must be true or false.',
            'check_prerelease.boolean' => 'Check prerelease must be true or false.',
            'auto_install.boolean' => 'Auto install must be true or false.',
            'notify_on_available.boolean' => 'Notify on available must be true or false.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 100.',
            'offset.min' => 'Offset must be at least 0.',
            'sort_order.regex' => 'Sort order contains invalid characters.',
            'filter_version.regex' => 'Filter version must be in format: x.y or x.y.z or x.y.z-suffix '.
                '(e.g., 1.0, 1.0.0, 1.0.0-beta).',
            'filter_date_from.date' => 'Filter date from must be a valid date.',
            'filter_date_to.date' => 'Filter date to must be a valid date.',
            'filter_date_to.after_or_equal' => 'Filter date to must be after or equal to filter date from.',
            'compare_versions.boolean' => 'Compare versions must be true or false.',
            'include_download_url.boolean' => 'Include download URL must be true or false.',
            'include_checksums.boolean' => 'Include checksums must be true or false.',
            'include_file_list.boolean' => 'Include file list must be true or false.',
            'include_installation_notes.boolean' => 'Include installation notes must be true or false.',
            'include_rollback_info.boolean' => 'Include rollback info must be true or false.',
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
            'license_key' => 'license key',
            'current_version' => 'current version',
            'domain' => 'domain',
            'product_slug' => 'product slug',
            'product_id' => 'product ID',
            'version' => 'version',
            'include_changelog' => 'include changelog',
            'include_dependencies' => 'include dependencies',
            'include_security_updates' => 'include security updates',
            'include_feature_updates' => 'include feature updates',
            'include_bug_fixes' => 'include bug fixes',
            'check_beta' => 'check beta versions',
            'check_prerelease' => 'check prerelease versions',
            'auto_install' => 'auto install',
            'notify_on_available' => 'notify on available',
            'limit' => 'limit',
            'offset' => 'offset',
            'sort_order' => 'sort order',
            'filter_version' => 'filter version',
            'filter_date_from' => 'filter date from',
            'filter_date_to' => 'filter date to',
            'compare_versions' => 'compare versions',
            'include_download_url' => 'include download URL',
            'include_checksums' => 'include checksums',
            'include_file_list' => 'include file list',
            'include_installation_notes' => 'include installation notes',
            'include_rollback_info' => 'include rollback info',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'license_key' => $this->sanitizeInput($this->license_key),
            'current_version' => $this->current_version ? $this->sanitizeInput($this->current_version) : null,
            'domain' => $this->sanitizeInput($this->domain),
            'product_slug' => $this->sanitizeInput($this->product_slug),
            'version' => $this->version ? $this->sanitizeInput($this->version) : null,
            'filter_version' => $this->filter_version ? $this->sanitizeInput($this->filter_version) : null,
            'sort_order' => $this->sort_order ? $this->sanitizeInput($this->sort_order) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'include_changelog' => $this->has('include_changelog'),
            'include_dependencies' => $this->has('include_dependencies'),
            'include_security_updates' => $this->has('include_security_updates'),
            'include_feature_updates' => $this->has('include_feature_updates'),
            'include_bug_fixes' => $this->has('include_bug_fixes'),
            'check_beta' => $this->has('check_beta'),
            'check_prerelease' => $this->has('check_prerelease'),
            'auto_install' => $this->has('auto_install'),
            'notify_on_available' => $this->has('notify_on_available'),
            'compare_versions' => $this->has('compare_versions'),
            'include_download_url' => $this->has('include_download_url'),
            'include_checksums' => $this->has('include_checksums'),
            'include_file_list' => $this->has('include_file_list'),
            'include_installation_notes' => $this->has('include_installation_notes'),
            'include_rollback_info' => $this->has('include_rollback_info'),
        ]);
        // Set default values
        $this->merge([
            'include_changelog' => $this->include_changelog ?? true,
            'include_dependencies' => $this->include_dependencies ?? true,
            'include_security_updates' => $this->include_security_updates ?? true,
            'include_feature_updates' => $this->include_feature_updates ?? true,
            'include_bug_fixes' => $this->include_bug_fixes ?? true,
            'include_download_url' => $this->include_download_url ?? true,
            'include_checksums' => $this->include_checksums ?? false,
            'include_file_list' => $this->include_file_list ?? false,
            'include_installation_notes' => $this->include_installation_notes ?? false,
            'include_rollback_info' => $this->include_rollback_info ?? false,
            'limit' => $this->limit ?? 20,
            'offset' => $this->offset ?? 0,
            'sort_order' => $this->sort_order ?? 'desc',
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
