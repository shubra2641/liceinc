<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * System Update Request with enhanced security.
 *
 * This unified request class handles validation for both system updates
 * and rollbacks with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both update and rollback operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Version format validation
 * - Confirmation requirement for critical operations
 */
class SystemUpdateRequest extends FormRequest
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
        $isRollback = $this->isMethod('POST') && $route && str_contains($route->getName() ?? '', 'rollback');
        // Rollback validation
        if ($isRollback) {
            return [
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
                'backup_required' => [
                    'boolean',
                ],
                'force_rollback' => [
                    'boolean',
                ],
                'rollback_reason' => [
                    'nullable',
                    'string',
                    'max:500',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'notifyUsers' => [
                    'boolean',
                ],
                'maintenanceMode' => [
                    'boolean',
                ],
            ];
        }
        // Update validation
        return [
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
            'autoBackup' => [
                'boolean',
            ],
            'force_update' => [
                'boolean',
            ],
            'update_notes' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'notifyUsers' => [
                'boolean',
            ],
            'maintenanceMode' => [
                'boolean',
            ],
            'skip_migrations' => [
                'boolean',
            ],
            'clearCaches' => [
                'boolean',
            ],
            'restart_services' => [
                'boolean',
            ],
            'update_database' => [
                'boolean',
            ],
            'update_files' => [
                'boolean',
            ],
            'update_config' => [
                'boolean',
            ],
            'backupDatabase' => [
                'boolean',
            ],
            'backupFiles' => [
                'boolean',
            ],
            'backupConfig' => [
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
    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'version.required' => 'Version is required.',
            'version.regex' => 'Version must be in format: x.y or x.y.z or x.y.z-suffix ' .
                '(e.g., 1.0, 1.0.0, 1.0.0-beta).',
            'confirm.required' => 'Confirmation is required for this operation.',
            'confirm.accepted' => 'You must confirm this operation to proceed.',
            'backup_required.boolean' => 'Backup required must be true or false.',
            'force_rollback.boolean' => 'Force rollback must be true or false.',
            'rollback_reason.regex' => 'Rollback reason contains invalid characters.',
            'notifyUsers.boolean' => 'Notify users must be true or false.',
            'maintenanceMode.boolean' => 'Maintenance mode must be true or false.',
            'autoBackup.boolean' => 'Auto backup must be true or false.',
            'force_update.boolean' => 'Force update must be true or false.',
            'update_notes.regex' => 'Update notes contain invalid characters.',
            'skip_migrations.boolean' => 'Skip migrations must be true or false.',
            'clearCaches.boolean' => 'Clear caches must be true or false.',
            'restart_services.boolean' => 'Restart services must be true or false.',
            'update_database.boolean' => 'Update database must be true or false.',
            'update_files.boolean' => 'Update files must be true or false.',
            'update_config.boolean' => 'Update config must be true or false.',
            'backupDatabase.boolean' => 'Backup database must be true or false.',
            'backupFiles.boolean' => 'Backup files must be true or false.',
            'backupConfig.boolean' => 'Backup config must be true or false.',
            'rollbackOnError.boolean' => 'Rollback on error must be true or false.',
            'test_mode.boolean' => 'Test mode must be true or false.',
            'dry_run.boolean' => 'Dry run must be true or false.',
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
            'version' => 'target version',
            'confirm' => 'confirmation',
            'backup_required' => 'backup required',
            'force_rollback' => 'force rollback',
            'rollback_reason' => 'rollback reason',
            'notifyUsers' => 'notify users',
            'maintenanceMode' => 'maintenance mode',
            'autoBackup' => 'auto backup',
            'force_update' => 'force update',
            'update_notes' => 'update notes',
            'skip_migrations' => 'skip migrations',
            'clearCaches' => 'clear caches',
            'restart_services' => 'restart services',
            'update_database' => 'update database',
            'update_files' => 'update files',
            'update_config' => 'update config',
            'backupDatabase' => 'backup database',
            'backupFiles' => 'backup files',
            'backupConfig' => 'backup config',
            'rollbackOnError' => 'rollback on error',
            'test_mode' => 'test mode',
            'dry_run' => 'dry run',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'version' => $this->sanitizeInput($this->input('version')),
            'rollback_reason' => $this->input('rollback_reason')
                ? $this->sanitizeInput($this->input('rollback_reason'))
                : null,
            'update_notes' => $this->input('update_notes') ? $this->sanitizeInput($this->input('update_notes')) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'confirm' => $this->has('confirm'),
            'backup_required' => $this->has('backup_required'),
            'force_rollback' => $this->has('force_rollback'),
            'notifyUsers' => $this->has('notifyUsers'),
            'maintenanceMode' => $this->has('maintenanceMode'),
            'autoBackup' => $this->has('autoBackup'),
            'force_update' => $this->has('force_update'),
            'skip_migrations' => $this->has('skip_migrations'),
            'clearCaches' => $this->has('clearCaches'),
            'restart_services' => $this->has('restart_services'),
            'update_database' => $this->has('update_database'),
            'update_files' => $this->has('update_files'),
            'update_config' => $this->has('update_config'),
            'backupDatabase' => $this->has('backupDatabase'),
            'backupFiles' => $this->has('backupFiles'),
            'backupConfig' => $this->has('backupConfig'),
            'rollbackOnError' => $this->has('rollbackOnError'),
            'test_mode' => $this->has('test_mode'),
            'dry_run' => $this->has('dry_run'),
        ]);
        // Set default values
        $this->merge([
            'autoBackup' => $this->autoBackup ?? true,
            'notifyUsers' => $this->notifyUsers ?? true,
            'maintenanceMode' => $this->maintenanceMode ?? true,
            'clearCaches' => $this->clearCaches ?? true,
            'backupDatabase' => $this->backupDatabase ?? true,
            'backupFiles' => $this->backupFiles ?? true,
            'backupConfig' => $this->backupConfig ?? true,
            'rollbackOnError' => $this->rollbackOnError ?? true,
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
