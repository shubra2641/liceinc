<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Auto Update Request with enhanced security.
 *
 * This unified request class handles validation for both auto update checking
 * and installation with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both check and install operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - License key validation
 * - Version format validation
 * - Domain validation
 */
class AutoUpdateRequest extends FormRequest
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
        $route = $this->route();
        $isInstall = $this->isMethod('POST') && $route && str_contains($route->getName() ?? '', 'install');
        // Install update validation
        if ($isInstall) {
            return [
                'license_key' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\-_]+$/',
                ],
                'product_slug' => [
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
                'version' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[0-9]+\.[0-9]+(\.[0-9]+)?(-[a-zA-Z0-9]+)?$/',
                ],
                'confirm' => [
                    'required',
                    'boolean',
                    'accepted',
                ],
                'backup_before_install' => [
                    'boolean',
                ],
                'force_install' => [
                    'boolean',
                ],
                'skip_license_check' => [
                    'boolean',
                ],
                'install_dependencies' => [
                    'boolean',
                ],
                'clear_caches' => [
                    'boolean',
                ],
                'restart_services' => [
                    'boolean',
                ],
                'notify_users' => [
                    'boolean',
                ],
                'maintenance_mode' => [
                    'boolean',
                ],
                'rollback_on_error' => [
                    'boolean',
                ],
                'test_mode' => [
                    'boolean',
                ],
                'dry_run' => [
                    'boolean',
                ],
            ];
        }

        // Check updates validation
        return [
            'license_key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_]+$/',
            ],
            'product_slug' => [
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
            'current_version' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9]+\.[0-9]+(\.[0-9]+)?(-[a-zA-Z0-9]+)?$/',
            ],
            'check_beta' => [
                'boolean',
            ],
            'check_prerelease' => [
                'boolean',
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
            'auto_install' => [
                'boolean',
            ],
            'notify_on_available' => [
                'boolean',
            ],
            'schedule_check' => [
                'boolean',
            ],
            'check_interval' => [
                'nullable',
                'integer',
                'min:1',
                'max:168', // 1 week in hours
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
            'license_key.required' => 'License key is required.',
            'license_key.regex' => 'License key can only contain letters, numbers, hyphens, and underscores.',
            'product_slug.required' => 'Product slug is required.',
            'product_slug.regex' => 'Product slug can only contain letters, numbers, hyphens, and underscores.',
            'domain.required' => 'Domain is required.',
            'domain.regex' => 'Domain can only contain letters, numbers, hyphens, underscores, and dots.',
            'version.required' => 'Version is required.',
            'version.regex' => 'Version must be in format: x.y or x.y.z or x.y.z-suffix '
                . '(e.g., 1.0, 1.0.0, 1.0.0-beta).',
            'current_version.required' => 'Current version is required.',
            'current_version.regex' => 'Current version must be in format: x.y or x.y.z or x.y.z-suffix '
                . '(e.g., 1.0, 1.0.0, 1.0.0-beta).',
            'confirm.required' => 'Confirmation is required for this operation.',
            'confirm.accepted' => 'You must confirm this operation to proceed.',
            'backup_before_install.boolean' => 'Backup before install must be true or false.',
            'force_install.boolean' => 'Force install must be true or false.',
            'skip_license_check.boolean' => 'Skip license check must be true or false.',
            'install_dependencies.boolean' => 'Install dependencies must be true or false.',
            'clear_caches.boolean' => 'Clear caches must be true or false.',
            'restart_services.boolean' => 'Restart services must be true or false.',
            'notify_users.boolean' => 'Notify users must be true or false.',
            'maintenance_mode.boolean' => 'Maintenance mode must be true or false.',
            'rollback_on_error.boolean' => 'Rollback on error must be true or false.',
            'test_mode.boolean' => 'Test mode must be true or false.',
            'dry_run.boolean' => 'Dry run must be true or false.',
            'check_beta.boolean' => 'Check beta must be true or false.',
            'check_prerelease.boolean' => 'Check prerelease must be true or false.',
            'include_changelog.boolean' => 'Include changelog must be true or false.',
            'include_dependencies.boolean' => 'Include dependencies must be true or false.',
            'include_security_updates.boolean' => 'Include security updates must be true or false.',
            'include_feature_updates.boolean' => 'Include feature updates must be true or false.',
            'include_bug_fixes.boolean' => 'Include bug fixes must be true or false.',
            'auto_install.boolean' => 'Auto install must be true or false.',
            'notify_on_available.boolean' => 'Notify on available must be true or false.',
            'schedule_check.boolean' => 'Schedule check must be true or false.',
            'check_interval.min' => 'Check interval must be at least 1 hour.',
            'check_interval.max' => 'Check interval cannot exceed 168 hours (1 week).',
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
            'product_slug' => 'product slug',
            'domain' => 'domain',
            'version' => 'target version',
            'current_version' => 'current version',
            'confirm' => 'confirmation',
            'backup_before_install' => 'backup before install',
            'force_install' => 'force install',
            'skip_license_check' => 'skip license check',
            'install_dependencies' => 'install dependencies',
            'clear_caches' => 'clear caches',
            'restart_services' => 'restart services',
            'notify_users' => 'notify users',
            'maintenance_mode' => 'maintenance mode',
            'rollback_on_error' => 'rollback on error',
            'test_mode' => 'test mode',
            'dry_run' => 'dry run',
            'check_beta' => 'check beta versions',
            'check_prerelease' => 'check prerelease versions',
            'include_changelog' => 'include changelog',
            'include_dependencies' => 'include dependencies',
            'include_security_updates' => 'include security updates',
            'include_feature_updates' => 'include feature updates',
            'include_bug_fixes' => 'include bug fixes',
            'auto_install' => 'auto install',
            'notify_on_available' => 'notify on available',
            'schedule_check' => 'schedule check',
            'check_interval' => 'check interval',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'license_key' => $this->sanitizeInput($this->input('license_key')),
            'product_slug' => $this->sanitizeInput($this->input('product_slug')),
            'domain' => $this->sanitizeInput($this->input('domain')),
            'version' => $this->sanitizeInput($this->input('version')),
            'current_version' => $this->sanitizeInput($this->input('current_version')),
        ]);
        // Handle checkbox values
        $this->merge([
            'confirm' => $this->has('confirm'),
            'backup_before_install' => $this->has('backup_before_install'),
            'force_install' => $this->has('force_install'),
            'skip_license_check' => $this->has('skip_license_check'),
            'install_dependencies' => $this->has('install_dependencies'),
            'clear_caches' => $this->has('clear_caches'),
            'restart_services' => $this->has('restart_services'),
            'notify_users' => $this->has('notify_users'),
            'maintenance_mode' => $this->has('maintenance_mode'),
            'rollback_on_error' => $this->has('rollback_on_error'),
            'test_mode' => $this->has('test_mode'),
            'dry_run' => $this->has('dry_run'),
            'check_beta' => $this->has('check_beta'),
            'check_prerelease' => $this->has('check_prerelease'),
            'include_changelog' => $this->has('include_changelog'),
            'include_dependencies' => $this->has('include_dependencies'),
            'include_security_updates' => $this->has('include_security_updates'),
            'include_feature_updates' => $this->has('include_feature_updates'),
            'include_bug_fixes' => $this->has('include_bug_fixes'),
            'auto_install' => $this->has('auto_install'),
            'notify_on_available' => $this->has('notify_on_available'),
            'schedule_check' => $this->has('schedule_check'),
        ]);
        // Set default values
        $this->merge([
            'backup_before_install' => $this->backup_before_install ?? true,
            'install_dependencies' => $this->install_dependencies ?? true,
            'clear_caches' => $this->clear_caches ?? true,
            'notify_users' => $this->notify_users ?? true,
            'maintenance_mode' => $this->maintenance_mode ?? true,
            'rollback_on_error' => $this->rollback_on_error ?? true,
            'include_changelog' => $this->include_changelog ?? true,
            'include_dependencies' => $this->include_dependencies ?? true,
            'include_security_updates' => $this->include_security_updates ?? true,
            'include_feature_updates' => $this->include_feature_updates ?? true,
            'include_bug_fixes' => $this->include_bug_fixes ?? true,
            'notify_on_available' => $this->notify_on_available ?? true,
            'check_interval' => $this->check_interval ?? 24,
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
