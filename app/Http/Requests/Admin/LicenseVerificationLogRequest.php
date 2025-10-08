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
                'includeBreakdown' => [
                    'boolean',
                ],
                'includeTrends' => [
                    'boolean',
                ],
                'includeComparison' => [
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
                'minAttempts' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:1000',
                ],
                'includeDetails' => [
                    'boolean',
                ],
                'includeRiskAssessment' => [
                    'boolean',
                ],
                'includeGeolocation' => [
                    'boolean',
                ],
                'includeUserAgents' => [
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
                'backupBeforeCleanup' => [
                    'boolean',
                ],
                'cleanup_type' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'includeFailedVerifications' => [
                    'boolean',
                ],
                'includeSuccessfulVerifications' => [
                    'boolean',
                ],
                'includeSuspiciousActivity' => [
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
            'perPage' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'sortBy' => [
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
            'includeDetails' => [
                'boolean',
            ],
            'includeMetadata' => [
                'boolean',
            ],
            'includeResponseData' => [
                'boolean',
            ],
            'includeErrorDetails' => [
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
            'minAttempts.min' => 'Minimum attempts must be at least 1.',
            'minAttempts.max' => 'Minimum attempts cannot exceed 1000.',
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
            'perPage.min' => 'Per page must be at least 1.',
            'perPage.max' => 'Per page cannot exceed 100.',
            'sortBy.regex' => 'Sort by contains invalid characters.',
            'sortOrder.regex' => 'Sort order contains invalid characters.',
            'group_by.regex' => 'Group by contains invalid characters.',
            'threshold_type.regex' => 'Threshold type contains invalid characters.',
            'risk_level.regex' => 'Risk level contains invalid characters.',
            'cleanup_type.regex' => 'Cleanup type contains invalid characters.',
            'includeBreakdown.boolean' => 'Include breakdown must be true or false.',
            'includeTrends.boolean' => 'Include trends must be true or false.',
            'includeComparison.boolean' => 'Include comparison must be true or false.',
            'includeDetails.boolean' => 'Include details must be true or false.',
            'includeRiskAssessment.boolean' => 'Include risk assessment must be true or false.',
            'includeGeolocation.boolean' => 'Include geolocation must be true or false.',
            'includeUserAgents.boolean' => 'Include user agents must be true or false.',
            'backupBeforeCleanup.boolean' => 'Backup before cleanup must be true or false.',
            'includeFailedVerifications.boolean' => 'Include failed verifications must be true or false.',
            'includeSuccessfulVerifications.boolean' => 'Include successful verifications must be true or false.',
            'includeSuspiciousActivity.boolean' => 'Include suspicious activity must be true or false.',
            'dry_run.boolean' => 'Dry run must be true or false.',
            'includeMetadata.boolean' => 'Include metadata must be true or false.',
            'includeResponseData.boolean' => 'Include response data must be true or false.',
            'includeErrorDetails.boolean' => 'Include error details must be true or false.',
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
            'minAttempts' => 'minimum attempts',
            'confirm' => 'confirmation',
            'status' => 'verification status',
            'source' => 'verification source',
            'domain' => 'domain',
            'ipAddress' => 'IP address',
            'licenseKey' => 'license key',
            'dateFrom' => 'date from',
            'dateTo' => 'date to',
            'perPage' => 'per page',
            'sortBy' => 'sort by',
            'sortOrder' => 'sort order',
            'group_by' => 'group by',
            'includeBreakdown' => 'include breakdown',
            'includeTrends' => 'include trends',
            'includeComparison' => 'include comparison',
            'includeDetails' => 'include details',
            'includeRiskAssessment' => 'include risk assessment',
            'includeGeolocation' => 'include geolocation',
            'includeUserAgents' => 'include user agents',
            'threshold_type' => 'threshold type',
            'risk_level' => 'risk level',
            'backupBeforeCleanup' => 'backup before cleanup',
            'cleanup_type' => 'cleanup type',
            'includeFailedVerifications' => 'include failed verifications',
            'includeSuccessfulVerifications' => 'include successful verifications',
            'includeSuspiciousActivity' => 'include suspicious activity',
            'dry_run' => 'dry run',
            'includeMetadata' => 'include metadata',
            'includeResponseData' => 'include response data',
            'includeErrorDetails' => 'include error details',
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
            'sortBy' => $this->input('sortBy') ? $this->sanitizeInput($this->input('sortBy')) : null,
            'sortOrder' => $this->input('sortOrder') ? $this->sanitizeInput($this->input('sortOrder')) : null,
            'group_by' => $this->input('group_by') ? $this->sanitizeInput($this->input('group_by')) : null,
            'threshold_type' => $this->input('threshold_type')
                ? $this->sanitizeInput($this->input('threshold_type'))
                : null,
            'risk_level' => $this->input('risk_level') ? $this->sanitizeInput($this->input('risk_level')) : null,
            'cleanup_type' => $this->input('cleanup_type') ? $this->sanitizeInput($this->input('cleanup_type')) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'confirm' => $this->has('confirm'),
            'includeBreakdown' => $this->has('includeBreakdown'),
            'includeTrends' => $this->has('includeTrends'),
            'includeComparison' => $this->has('includeComparison'),
            'includeDetails' => $this->has('includeDetails'),
            'includeRiskAssessment' => $this->has('includeRiskAssessment'),
            'includeGeolocation' => $this->has('includeGeolocation'),
            'includeUserAgents' => $this->has('includeUserAgents'),
            'backupBeforeCleanup' => $this->has('backupBeforeCleanup'),
            'includeFailedVerifications' => $this->has('includeFailedVerifications'),
            'includeSuccessfulVerifications' => $this->has('includeSuccessfulVerifications'),
            'includeSuspiciousActivity' => $this->has('includeSuspiciousActivity'),
            'dry_run' => $this->has('dry_run'),
            'includeMetadata' => $this->has('includeMetadata'),
            'includeResponseData' => $this->has('includeResponseData'),
            'includeErrorDetails' => $this->has('includeErrorDetails'),
        ]);
        // Set default values
        $this->merge([
            'days' => $this->days ?? 30,
            'hours' => $this->hours ?? 24,
            'minAttempts' => $this->minAttempts ?? 3,
            'perPage' => $this->perPage ?? 20,
            'sortBy' => $this->sortBy ?? 'createdAt',
            'sortOrder' => $this->sortOrder ?? 'desc',
            'includeBreakdown' => $this->includeBreakdown ?? true,
            'includeTrends' => $this->includeTrends ?? true,
            'includeComparison' => $this->includeComparison ?? false,
            'includeDetails' => $this->includeDetails ?? true,
            'includeRiskAssessment' => $this->includeRiskAssessment ?? true,
            'includeGeolocation' => $this->includeGeolocation ?? false,
            'includeUserAgents' => $this->includeUserAgents ?? false,
            'backupBeforeCleanup' => $this->backupBeforeCleanup ?? true,
            'includeFailedVerifications' => $this->includeFailedVerifications ?? true,
            'includeSuccessfulVerifications' => $this->includeSuccessfulVerifications ?? true,
            'includeSuspiciousActivity' => $this->includeSuspiciousActivity ?? true,
            'includeMetadata' => $this->includeMetadata ?? false,
            'includeResponseData' => $this->includeResponseData ?? false,
            'includeErrorDetails' => $this->includeErrorDetails ?? false,
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
