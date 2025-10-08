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
        return auth()->check() && $user && ($user->isAdmin || $user->hasRole('admin'));
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
                'licenseKey' => [
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
                'backupBeforeInstall' => [
                    'boolean',
                ],
                'force_install' => [
                    'boolean',
                ],
                'skip_license_check' => [
                    'boolean',
                ],
                'installDependencies' => [
                    'boolean',
                ],
                'clearCaches' => [
                    'boolean',
                ],
                'restart_services' => [
                    'boolean',
                ],
                'notifyUsers' => [
                    'boolean',
                ],
                'maintenanceMode' => [
                    'boolean',
                ],
                'rollbackOnError' => [
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
            'licenseKey' => [
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
            'auto_install' => [
                'boolean',
            ],
            'notifyOnAvailable' => [
                'boolean',
            ],
            'schedule_check' => [
                'boolean',
            ],
            'checkInterval' => [
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
            'licenseKey.required' => 'License key is required.',
            'licenseKey.regex' => 'License key can only contain letters, numbers, hyphens, and underscores.',
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
            'backupBeforeInstall.boolean' => 'Backup before install must be true or false.',
            'force_install.boolean' => 'Force install must be true or false.',
            'skip_license_check.boolean' => 'Skip license check must be true or false.',
            'installDependencies.boolean' => 'Install dependencies must be true or false.',
            'clearCaches.boolean' => 'Clear caches must be true or false.',
            'restart_services.boolean' => 'Restart services must be true or false.',
            'notifyUsers.boolean' => 'Notify users must be true or false.',
            'maintenanceMode.boolean' => 'Maintenance mode must be true or false.',
            'rollbackOnError.boolean' => 'Rollback on error must be true or false.',
            'test_mode.boolean' => 'Test mode must be true or false.',
            'dry_run.boolean' => 'Dry run must be true or false.',
            'check_beta.boolean' => 'Check beta must be true or false.',
            'check_prerelease.boolean' => 'Check prerelease must be true or false.',
            'includeChangelog.boolean' => 'Include changelog must be true or false.',
            'includeDependencies.boolean' => 'Include dependencies must be true or false.',
            'includeSecurityUpdates.boolean' => 'Include security updates must be true or false.',
            'includeFeatureUpdates.boolean' => 'Include feature updates must be true or false.',
            'includeBugFixes.boolean' => 'Include bug fixes must be true or false.',
            'auto_install.boolean' => 'Auto install must be true or false.',
            'notifyOnAvailable.boolean' => 'Notify on available must be true or false.',
            'schedule_check.boolean' => 'Schedule check must be true or false.',
            'checkInterval.min' => 'Check interval must be at least 1 hour.',
            'checkInterval.max' => 'Check interval cannot exceed 168 hours (1 week).',
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
            'product_slug' => 'product slug',
            'domain' => 'domain',
            'version' => 'target version',
            'current_version' => 'current version',
            'confirm' => 'confirmation',
            'backupBeforeInstall' => 'backup before install',
            'force_install' => 'force install',
            'skip_license_check' => 'skip license check',
            'installDependencies' => 'install dependencies',
            'clearCaches' => 'clear caches',
            'restart_services' => 'restart services',
            'notifyUsers' => 'notify users',
            'maintenanceMode' => 'maintenance mode',
            'rollbackOnError' => 'rollback on error',
            'test_mode' => 'test mode',
            'dry_run' => 'dry run',
            'check_beta' => 'check beta versions',
            'check_prerelease' => 'check prerelease versions',
            'includeChangelog' => 'include changelog',
            'includeDependencies' => 'include dependencies',
            'includeSecurityUpdates' => 'include security updates',
            'includeFeatureUpdates' => 'include feature updates',
            'includeBugFixes' => 'include bug fixes',
            'auto_install' => 'auto install',
            'notifyOnAvailable' => 'notify on available',
            'schedule_check' => 'schedule check',
            'checkInterval' => 'check interval',
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
            'product_slug' => $this->sanitizeInput($this->input('product_slug')),
            'domain' => $this->sanitizeInput($this->input('domain')),
            'version' => $this->sanitizeInput($this->input('version')),
            'current_version' => $this->sanitizeInput($this->input('current_version')),
        ]);
        // Handle checkbox values
        $this->merge([
            'confirm' => $this->has('confirm'),
            'backupBeforeInstall' => $this->has('backupBeforeInstall'),
            'force_install' => $this->has('force_install'),
            'skip_license_check' => $this->has('skip_license_check'),
            'installDependencies' => $this->has('installDependencies'),
            'clearCaches' => $this->has('clearCaches'),
            'restart_services' => $this->has('restart_services'),
            'notifyUsers' => $this->has('notifyUsers'),
            'maintenanceMode' => $this->has('maintenanceMode'),
            'rollbackOnError' => $this->has('rollbackOnError'),
            'test_mode' => $this->has('test_mode'),
            'dry_run' => $this->has('dry_run'),
            'check_beta' => $this->has('check_beta'),
            'check_prerelease' => $this->has('check_prerelease'),
            'includeChangelog' => $this->has('includeChangelog'),
            'includeDependencies' => $this->has('includeDependencies'),
            'includeSecurityUpdates' => $this->has('includeSecurityUpdates'),
            'includeFeatureUpdates' => $this->has('includeFeatureUpdates'),
            'includeBugFixes' => $this->has('includeBugFixes'),
            'auto_install' => $this->has('auto_install'),
            'notifyOnAvailable' => $this->has('notifyOnAvailable'),
            'schedule_check' => $this->has('schedule_check'),
        ]);
        // Set default values
        $this->merge([
            'backupBeforeInstall' => $this->backupBeforeInstall ?? true,
            'installDependencies' => $this->installDependencies ?? true,
            'clearCaches' => $this->clearCaches ?? true,
            'notifyUsers' => $this->notifyUsers ?? true,
            'maintenanceMode' => $this->maintenanceMode ?? true,
            'rollbackOnError' => $this->rollbackOnError ?? true,
            'includeChangelog' => $this->includeChangelog ?? true,
            'includeDependencies' => $this->includeDependencies ?? true,
            'includeSecurityUpdates' => $this->includeSecurityUpdates ?? true,
            'includeFeatureUpdates' => $this->includeFeatureUpdates ?? true,
            'includeBugFixes' => $this->includeBugFixes ?? true,
            'notifyOnAvailable' => $this->notifyOnAvailable ?? true,
            'checkInterval' => $this->checkInterval ?? 24,
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
