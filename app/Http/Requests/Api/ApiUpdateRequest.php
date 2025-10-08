<?php

declare(strict_types=1);

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
 */
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
        $route = $this->route();
        $routeName = $route?->getName() ?? '';

        $isCheck = $this->isMethod('POST') && str_contains($routeName, 'check');
        $isHistory = $this->isMethod('POST') && str_contains($routeName, 'history');
        $isLatest = $this->isMethod('POST') && str_contains($routeName, 'latest');
        $isInfo = $this->isMethod('POST') && str_contains($routeName, 'info');
        // Check updates validation
        if ($isCheck) {
            return [
                'licenseKey' => [
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
                'productId' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'includeChangelog' => [
                    'boolean',
                ],
                'includeDependencies' => [
                    'boolean',
                ],
                'includeSecurityUpdates' => [
                    'boolean',
                ],
                'includeFeatureUpdates' => [
                    'boolean',
                ],
                'includeBugFixes' => [
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
                'licenseKey' => [
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
                'productId' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'includeChangelog' => [
                    'boolean',
                ],
                'includeDependencies' => [
                    'boolean',
                ],
                'includeSecurityUpdates' => [
                    'boolean',
                ],
                'includeFeatureUpdates' => [
                    'boolean',
                ],
                'includeBugFixes' => [
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
                'sortOrder' => [
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
                'filter_dateFrom' => [
                    'nullable',
                    'date',
                ],
                'filter_dateTo' => [
                    'nullable',
                    'date',
                    'after_or_equal:filter_dateFrom',
                ],
            ];
        }
        // Latest version validation
        if ($isLatest) {
            return [
                'licenseKey' => [
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
                'productId' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'includeChangelog' => [
                    'boolean',
                ],
                'includeDependencies' => [
                    'boolean',
                ],
                'includeSecurityUpdates' => [
                    'boolean',
                ],
                'includeFeatureUpdates' => [
                    'boolean',
                ],
                'includeBugFixes' => [
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
                'includeDownloadUrl' => [
                    'boolean',
                ],
                'includeChecksums' => [
                    'boolean',
                ],
                'includeFileList' => [
                    'boolean',
                ],
            ];
        }
        // Update info validation
        if ($isInfo) {
            return [
                'licenseKey' => [
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
                'productId' => [
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
                'includeChangelog' => [
                    'boolean',
                ],
                'includeDependencies' => [
                    'boolean',
                ],
                'includeSecurityUpdates' => [
                    'boolean',
                ],
                'includeFeatureUpdates' => [
                    'boolean',
                ],
                'includeBugFixes' => [
                    'boolean',
                ],
                'includeDownloadUrl' => [
                    'boolean',
                ],
                'includeChecksums' => [
                    'boolean',
                ],
                'includeFileList' => [
                    'boolean',
                ],
                'includeInstallationNotes' => [
                    'boolean',
                ],
                'includeRollbackInfo' => [
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
            'licenseKey.required' => 'License key is required.',
            'licenseKey.regex' => 'License key can only contain letters, numbers, hyphens, and underscores.',
            'current_version.required' => 'Current version is required.',
            'current_version.regex' => 'Current version must be in format: x.y or x.y.z or x.y.z-suffix ' .
                '(e.g., 1.0, 1.0.0, 1.0.0-beta).',
            'domain.required' => 'Domain is required.',
            'domain.regex' => 'Domain can only contain letters, numbers, hyphens, underscores, and dots.',
            'product_slug.required' => 'Product slug is required.',
            'product_slug.regex' => 'Product slug can only contain letters, numbers, hyphens, and underscores.',
            'productId.integer' => 'Product ID must be a valid integer.',
            'productId.min' => 'Product ID must be at least 1.',
            'version.required' => 'Version is required.',
            'version.regex' => 'Version must be in format: x.y or x.y.z or x.y.z-suffix ' .
                '(e.g., 1.0, 1.0.0, 1.0.0-beta).',
            'includeChangelog.boolean' => 'Include changelog must be true or false.',
            'includeDependencies.boolean' => 'Include dependencies must be true or false.',
            'includeSecurityUpdates.boolean' => 'Include security updates must be true or false.',
            'includeFeatureUpdates.boolean' => 'Include feature updates must be true or false.',
            'includeBugFixes.boolean' => 'Include bug fixes must be true or false.',
            'check_beta.boolean' => 'Check beta must be true or false.',
            'check_prerelease.boolean' => 'Check prerelease must be true or false.',
            'auto_install.boolean' => 'Auto install must be true or false.',
            'notify_on_available.boolean' => 'Notify on available must be true or false.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 100.',
            'offset.min' => 'Offset must be at least 0.',
            'sortOrder.regex' => 'Sort order contains invalid characters.',
            'filter_version.regex' => 'Filter version must be in format: x.y or x.y.z or x.y.z-suffix ' .
                '(e.g., 1.0, 1.0.0, 1.0.0-beta).',
            'filter_dateFrom.date' => 'Filter date from must be a valid date.',
            'filter_dateTo.date' => 'Filter date to must be a valid date.',
            'filter_dateTo.after_or_equal' => 'Filter date to must be after or equal to filter date from.',
            'compare_versions.boolean' => 'Compare versions must be true or false.',
            'includeDownloadUrl.boolean' => 'Include download URL must be true or false.',
            'includeChecksums.boolean' => 'Include checksums must be true or false.',
            'includeFileList.boolean' => 'Include file list must be true or false.',
            'includeInstallationNotes.boolean' => 'Include installation notes must be true or false.',
            'includeRollbackInfo.boolean' => 'Include rollback info must be true or false.',
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
            'licenseKey' => 'license key',
            'current_version' => 'current version',
            'domain' => 'domain',
            'product_slug' => 'product slug',
            'productId' => 'product ID',
            'version' => 'version',
            'includeChangelog' => 'include changelog',
            'includeDependencies' => 'include dependencies',
            'includeSecurityUpdates' => 'include security updates',
            'includeFeatureUpdates' => 'include feature updates',
            'includeBugFixes' => 'include bug fixes',
            'check_beta' => 'check beta versions',
            'check_prerelease' => 'check prerelease versions',
            'auto_install' => 'auto install',
            'notify_on_available' => 'notify on available',
            'limit' => 'limit',
            'offset' => 'offset',
            'sortOrder' => 'sort order',
            'filter_version' => 'filter version',
            'filter_dateFrom' => 'filter date from',
            'filter_dateTo' => 'filter date to',
            'compare_versions' => 'compare versions',
            'includeDownloadUrl' => 'include download URL',
            'includeChecksums' => 'include checksums',
            'includeFileList' => 'include file list',
            'includeInstallationNotes' => 'include installation notes',
            'includeRollbackInfo' => 'include rollback info',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'licenseKey' => $this->sanitizeInput($this->input('licenseKey')),
            'current_version' => $this->input('current_version')
                ? $this->sanitizeInput($this->input('current_version'))
                : null,
            'domain' => $this->sanitizeInput($this->input('domain')),
            'product_slug' => $this->sanitizeInput($this->input('product_slug')),
            'version' => $this->input('version')
                ? $this->sanitizeInput($this->input('version'))
                : null,
            'filter_version' => $this->input('filter_version')
                ? $this->sanitizeInput($this->input('filter_version'))
                : null,
            'sortOrder' => $this->input('sortOrder') ? $this->sanitizeInput($this->input('sortOrder')) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'includeChangelog' => $this->has('includeChangelog'),
            'includeDependencies' => $this->has('includeDependencies'),
            'includeSecurityUpdates' => $this->has('includeSecurityUpdates'),
            'includeFeatureUpdates' => $this->has('includeFeatureUpdates'),
            'includeBugFixes' => $this->has('includeBugFixes'),
            'check_beta' => $this->has('check_beta'),
            'check_prerelease' => $this->has('check_prerelease'),
            'auto_install' => $this->has('auto_install'),
            'notify_on_available' => $this->has('notify_on_available'),
            'compare_versions' => $this->has('compare_versions'),
            'includeDownloadUrl' => $this->has('includeDownloadUrl'),
            'includeChecksums' => $this->has('includeChecksums'),
            'includeFileList' => $this->has('includeFileList'),
            'includeInstallationNotes' => $this->has('includeInstallationNotes'),
            'includeRollbackInfo' => $this->has('includeRollbackInfo'),
        ]);
        // Set default values
        $this->merge([
            'includeChangelog' => $this->includeChangelog ?? true,
            'includeDependencies' => $this->includeDependencies ?? true,
            'includeSecurityUpdates' => $this->includeSecurityUpdates ?? true,
            'includeFeatureUpdates' => $this->includeFeatureUpdates ?? true,
            'includeBugFixes' => $this->includeBugFixes ?? true,
            'includeDownloadUrl' => $this->includeDownloadUrl ?? true,
            'includeChecksums' => $this->includeChecksums ?? false,
            'includeFileList' => $this->includeFileList ?? false,
            'includeInstallationNotes' => $this->includeInstallationNotes ?? false,
            'includeRollbackInfo' => $this->includeRollbackInfo ?? false,
            'limit' => $this->limit ?? 20,
            'offset' => $this->offset ?? 0,
            'sortOrder' => $this->sortOrder ?? 'desc',
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
