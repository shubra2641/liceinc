<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * License Verification Log Request with enhanced security.
 *
 * This unified request class handles validation for license verification log
 * operations including filtering, statistics, suspicious activity detection,
 * and cleanup with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for all log operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Date range validation
 * - IP address validation
 * - Domain validation
 */
class LicenseVerificationLogRequest extends FormRequest
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
        $isStats = $this->isMethod('GET') && $route && str_contains($route->getName() ?? '', 'stats');
        $isSuspicious = $this->isMethod('GET') && $route && str_contains($route->getName() ?? '', 'suspicious');
        $route = $this->route();
        $isCleanup = $this->isMethod('POST') && $route && str_contains($route->getName() ?? '', 'clean');
        // Statistics validation
        if ($isStats) {
            return [
                'days' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:365',
                ],
                'group_by' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'include_breakdown' => [
                    'boolean',
                ],
                'include_trends' => [
                    'boolean',
                ],
                'include_comparison' => [
                    'boolean',
                ],
                'dateFrom' => [
                    'nullable',
                    'date',
                ],
                'dateTo' => [
                    'nullable',
                    'date',
                    'after_or_equal:dateFrom',
                ],
            ];
        }
        // Suspicious activity validation
        if ($isSuspicious) {
            return [
                'hours' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:168', // 1 week
                ],
                'min_attempts' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:1000',
                ],
                'include_details' => [
                    'boolean',
                ],
                'include_risk_assessment' => [
                    'boolean',
                ],
                'include_geolocation' => [
                    'boolean',
                ],
                'include_user_agents' => [
                    'boolean',
                ],
                'threshold_type' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'risk_level' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
            ];
        }
        // Cleanup validation
        if ($isCleanup) {
            return [
                'days' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:3650', // 10 years
                ],
                'confirm' => [
                    'required',
                    'boolean',
                    'accepted',
                ],
                'backup_before_cleanup' => [
                    'boolean',
                ],
                'cleanup_type' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'include_failed_verifications' => [
                    'boolean',
                ],
                'include_successful_verifications' => [
                    'boolean',
                ],
                'include_suspicious_activity' => [
                    'boolean',
                ],
                'dry_run' => [
                    'boolean',
                ],
            ];
        }
        // Filter validation (default)
        return [
            'status' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\s\-_.,!?@#$%&*()]+$/',
            ],
            'source' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-_.,!?@#$%&*()]+$/',
            ],
            'domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_.]+$/',
            ],
            'ipAddress' => [
                'nullable',
                'ip',
            ],
            'licenseKey' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_]+$/',
            ],
            'dateFrom' => [
                'nullable',
                'date',
            ],
            'dateTo' => [
                'nullable',
                'date',
                'after_or_equal:dateFrom',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'sort_by' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-_.,!?@#$%&*()]+$/',
            ],
            'sortOrder' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^[a-zA-Z0-9\s\-_.,!?@#$%&*()]+$/',
            ],
            'include_details' => [
                'boolean',
            ],
            'include_metadata' => [
                'boolean',
            ],
            'include_response_data' => [
                'boolean',
            ],
            'include_error_details' => [
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
            'days.required' => 'Days parameter is required.',
            'days.min' => 'Days must be at least 1.',
            'days.max' => 'Days cannot exceed 365.',
            'hours.min' => 'Hours must be at least 1.',
            'hours.max' => 'Hours cannot exceed 168 (1 week).',
            'min_attempts.min' => 'Minimum attempts must be at least 1.',
            'min_attempts.max' => 'Minimum attempts cannot exceed 1000.',
            'confirm.required' => 'Confirmation is required for this operation.',
            'confirm.accepted' => 'You must confirm this operation to proceed.',
            'status.regex' => 'Status contains invalid characters.',
            'source.regex' => 'Source contains invalid characters.',
            'domain.regex' => 'Domain can only contain letters, numbers, hyphens, underscores, and dots.',
            'ipAddress.ip' => 'IP address must be a valid IP address.',
            'licenseKey.regex' => 'License key can only contain letters, numbers, hyphens, and underscores.',
            'dateFrom.date' => 'Date from must be a valid date.',
            'dateTo.date' => 'Date to must be a valid date.',
            'dateTo.after_or_equal' => 'Date to must be after or equal to date from.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
            'sort_by.regex' => 'Sort by contains invalid characters.',
            'sortOrder.regex' => 'Sort order contains invalid characters.',
            'group_by.regex' => 'Group by contains invalid characters.',
            'threshold_type.regex' => 'Threshold type contains invalid characters.',
            'risk_level.regex' => 'Risk level contains invalid characters.',
            'cleanup_type.regex' => 'Cleanup type contains invalid characters.',
            'include_breakdown.boolean' => 'Include breakdown must be true or false.',
            'include_trends.boolean' => 'Include trends must be true or false.',
            'include_comparison.boolean' => 'Include comparison must be true or false.',
            'include_details.boolean' => 'Include details must be true or false.',
            'include_risk_assessment.boolean' => 'Include risk assessment must be true or false.',
            'include_geolocation.boolean' => 'Include geolocation must be true or false.',
            'include_user_agents.boolean' => 'Include user agents must be true or false.',
            'backup_before_cleanup.boolean' => 'Backup before cleanup must be true or false.',
            'include_failed_verifications.boolean' => 'Include failed verifications must be true or false.',
            'include_successful_verifications.boolean' => 'Include successful verifications must be true or false.',
            'include_suspicious_activity.boolean' => 'Include suspicious activity must be true or false.',
            'dry_run.boolean' => 'Dry run must be true or false.',
            'include_metadata.boolean' => 'Include metadata must be true or false.',
            'include_response_data.boolean' => 'Include response data must be true or false.',
            'include_error_details.boolean' => 'Include error details must be true or false.',
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
            'days' => 'number of days',
            'hours' => 'number of hours',
            'min_attempts' => 'minimum attempts',
            'confirm' => 'confirmation',
            'status' => 'verification status',
            'source' => 'verification source',
            'domain' => 'domain',
            'ipAddress' => 'IP address',
            'licenseKey' => 'license key',
            'dateFrom' => 'date from',
            'dateTo' => 'date to',
            'per_page' => 'per page',
            'sort_by' => 'sort by',
            'sortOrder' => 'sort order',
            'group_by' => 'group by',
            'include_breakdown' => 'include breakdown',
            'include_trends' => 'include trends',
            'include_comparison' => 'include comparison',
            'include_details' => 'include details',
            'include_risk_assessment' => 'include risk assessment',
            'include_geolocation' => 'include geolocation',
            'include_user_agents' => 'include user agents',
            'threshold_type' => 'threshold type',
            'risk_level' => 'risk level',
            'backup_before_cleanup' => 'backup before cleanup',
            'cleanup_type' => 'cleanup type',
            'include_failed_verifications' => 'include failed verifications',
            'include_successful_verifications' => 'include successful verifications',
            'include_suspicious_activity' => 'include suspicious activity',
            'dry_run' => 'dry run',
            'include_metadata' => 'include metadata',
            'include_response_data' => 'include response data',
            'include_error_details' => 'include error details',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'status' => $this->input('status') ? $this->sanitizeInput($this->input('status')) : null,
            'source' => $this->input('source') ? $this->sanitizeInput($this->input('source')) : null,
            'domain' => $this->input('domain') ? $this->sanitizeInput($this->input('domain')) : null,
            'licenseKey' => $this->input('licenseKey') ? $this->sanitizeInput($this->input('licenseKey')) : null,
            'sort_by' => $this->input('sort_by') ? $this->sanitizeInput($this->input('sort_by')) : null,
            'sortOrder' => $this->input('sortOrder') ? $this->sanitizeInput($this->input('sortOrder')) : null,
            'group_by' => $this->input('group_by') ? $this->sanitizeInput($this->input('group_by')) : null,
            'threshold_type' => $this->input('threshold_type') ? $this->sanitizeInput($this->input('threshold_type')) : null,
            'risk_level' => $this->input('risk_level') ? $this->sanitizeInput($this->input('risk_level')) : null,
            'cleanup_type' => $this->input('cleanup_type') ? $this->sanitizeInput($this->input('cleanup_type')) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'confirm' => $this->has('confirm'),
            'include_breakdown' => $this->has('include_breakdown'),
            'include_trends' => $this->has('include_trends'),
            'include_comparison' => $this->has('include_comparison'),
            'include_details' => $this->has('include_details'),
            'include_risk_assessment' => $this->has('include_risk_assessment'),
            'include_geolocation' => $this->has('include_geolocation'),
            'include_user_agents' => $this->has('include_user_agents'),
            'backup_before_cleanup' => $this->has('backup_before_cleanup'),
            'include_failed_verifications' => $this->has('include_failed_verifications'),
            'include_successful_verifications' => $this->has('include_successful_verifications'),
            'include_suspicious_activity' => $this->has('include_suspicious_activity'),
            'dry_run' => $this->has('dry_run'),
            'include_metadata' => $this->has('include_metadata'),
            'include_response_data' => $this->has('include_response_data'),
            'include_error_details' => $this->has('include_error_details'),
        ]);
        // Set default values
        $this->merge([
            'days' => $this->days ?? 30,
            'hours' => $this->hours ?? 24,
            'min_attempts' => $this->min_attempts ?? 3,
            'per_page' => $this->per_page ?? 20,
            'sort_by' => $this->sort_by ?? 'createdAt',
            'sortOrder' => $this->sortOrder ?? 'desc',
            'include_breakdown' => $this->include_breakdown ?? true,
            'include_trends' => $this->include_trends ?? true,
            'include_comparison' => $this->include_comparison ?? false,
            'include_details' => $this->include_details ?? true,
            'include_risk_assessment' => $this->include_risk_assessment ?? true,
            'include_geolocation' => $this->include_geolocation ?? false,
            'include_user_agents' => $this->include_user_agents ?? false,
            'backup_before_cleanup' => $this->backup_before_cleanup ?? true,
            'include_failed_verifications' => $this->include_failed_verifications ?? true,
            'include_successful_verifications' => $this->include_successful_verifications ?? true,
            'include_suspicious_activity' => $this->include_suspicious_activity ?? true,
            'include_metadata' => $this->include_metadata ?? false,
            'include_response_data' => $this->include_response_data ?? false,
            'include_error_details' => $this->include_error_details ?? false,
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
