<?php

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
        return auth()->check() && (auth()->user()->is_admin || auth()->user()->hasRole('admin'));
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isRollback = $this->isMethod('POST') && str_contains($this->route()->getName(), 'rollback');
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
                'notify_users' => [
                    'boolean',
                ],
                'maintenance_mode' => [
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
            'auto_backup' => [
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
            'notify_users' => [
                'boolean',
            ],
            'maintenance_mode' => [
                'boolean',
            ],
            'skip_migrations' => [
                'boolean',
            ],
            'clear_caches' => [
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
            'backup_database' => [
                'boolean',
            ],
            'backup_files' => [
                'boolean',
            ],
            'backup_config' => [
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
    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'version.required' => 'Version is required.',
            'version.regex' => 'Version must be in format: x.y or x.y.z or x.y.z-suffix (e.g., 1.0, 1.0.0, 1.0.0-beta).',
            'confirm.required' => 'Confirmation is required for this operation.',
            'confirm.accepted' => 'You must confirm this operation to proceed.',
            'backup_required.boolean' => 'Backup required must be true or false.',
            'force_rollback.boolean' => 'Force rollback must be true or false.',
            'rollback_reason.regex' => 'Rollback reason contains invalid characters.',
            'notify_users.boolean' => 'Notify users must be true or false.',
            'maintenance_mode.boolean' => 'Maintenance mode must be true or false.',
            'auto_backup.boolean' => 'Auto backup must be true or false.',
            'force_update.boolean' => 'Force update must be true or false.',
            'update_notes.regex' => 'Update notes contain invalid characters.',
            'skip_migrations.boolean' => 'Skip migrations must be true or false.',
            'clear_caches.boolean' => 'Clear caches must be true or false.',
            'restart_services.boolean' => 'Restart services must be true or false.',
            'update_database.boolean' => 'Update database must be true or false.',
            'update_files.boolean' => 'Update files must be true or false.',
            'update_config.boolean' => 'Update config must be true or false.',
            'backup_database.boolean' => 'Backup database must be true or false.',
            'backup_files.boolean' => 'Backup files must be true or false.',
            'backup_config.boolean' => 'Backup config must be true or false.',
            'rollback_on_error.boolean' => 'Rollback on error must be true or false.',
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
            'notify_users' => 'notify users',
            'maintenance_mode' => 'maintenance mode',
            'auto_backup' => 'auto backup',
            'force_update' => 'force update',
            'update_notes' => 'update notes',
            'skip_migrations' => 'skip migrations',
            'clear_caches' => 'clear caches',
            'restart_services' => 'restart services',
            'update_database' => 'update database',
            'update_files' => 'update files',
            'update_config' => 'update config',
            'backup_database' => 'backup database',
            'backup_files' => 'backup files',
            'backup_config' => 'backup config',
            'rollback_on_error' => 'rollback on error',
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
            'version' => $this->sanitizeInput($this->version),
            'rollback_reason' => $this->rollback_reason ? $this->sanitizeInput($this->rollback_reason) : null,
            'update_notes' => $this->update_notes ? $this->sanitizeInput($this->update_notes) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'confirm' => $this->has('confirm'),
            'backup_required' => $this->has('backup_required'),
            'force_rollback' => $this->has('force_rollback'),
            'notify_users' => $this->has('notify_users'),
            'maintenance_mode' => $this->has('maintenance_mode'),
            'auto_backup' => $this->has('auto_backup'),
            'force_update' => $this->has('force_update'),
            'skip_migrations' => $this->has('skip_migrations'),
            'clear_caches' => $this->has('clear_caches'),
            'restart_services' => $this->has('restart_services'),
            'update_database' => $this->has('update_database'),
            'update_files' => $this->has('update_files'),
            'update_config' => $this->has('update_config'),
            'backup_database' => $this->has('backup_database'),
            'backup_files' => $this->has('backup_files'),
            'backup_config' => $this->has('backup_config'),
            'rollback_on_error' => $this->has('rollback_on_error'),
            'test_mode' => $this->has('test_mode'),
            'dry_run' => $this->has('dry_run'),
        ]);
        // Set default values
        $this->merge([
            'auto_backup' => $this->auto_backup ?? true,
            'notify_users' => $this->notify_users ?? true,
            'maintenance_mode' => $this->maintenance_mode ?? true,
            'clear_caches' => $this->clear_caches ?? true,
            'backup_database' => $this->backup_database ?? true,
            'backup_files' => $this->backup_files ?? true,
            'backup_config' => $this->backup_config ?? true,
            'rollback_on_error' => $this->rollback_on_error ?? true,
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
