<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
/**
 * Update Notification Request with enhanced security.
 *
 * This unified request class handles validation for update notification
 * management including dismissal and status updates with comprehensive
 * security measures and input sanitization.
 *
 * Features:
 * - Unified validation for notification operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Time-based dismissal validation
 * - Notification type validation
/
class UpdateNotificationRequest extends FormRequest
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
        $isDismiss = $this->isMethod('POST') && str_contains($this->route()->getName(), 'dismiss');
        // Dismiss notification validation
        if ($isDismiss) {
            return [
                'dismiss_until' => [
                    'nullable',
                    'date',
                    'after:now',
                ],
                'dismiss_type' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'dismiss_reason' => [
                    'nullable',
                    'string',
                    'max:500',
                    'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
                ],
                'permanent_dismiss' => [
                    'boolean',
                ],
                'notify_others' => [
                    'boolean',
                ],
                'dismiss_all_types' => [
                    'boolean',
                ],
            ];
        }
        // Default validation (for other notification operations)
        return [
            'notification_type' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'message' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'priority' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'target_users' => [
                'nullable',
                'array',
            ],
            'target_users.*' => [
                'integer',
                'exists:users, id',
            ],
            'send_email' => [
                'boolean',
            ],
            'send_push' => [
                'boolean',
            ],
            'send_sms' => [
                'boolean',
            ],
            'schedule_time' => [
                'nullable',
                'date',
                'after:now',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now',
            ],
            'auto_dismiss' => [
                'boolean',
            ],
            'dismiss_after_hours' => [
                'nullable',
                'integer',
                'min:1',
                'max:168', // 1 week
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
            'dismiss_until.date' => 'Dismiss until must be a valid date.',
            'dismiss_until.after' => 'Dismiss until must be in the future.',
            'dismiss_type.regex' => 'Dismiss type contains invalid characters.',
            'dismiss_reason.regex' => 'Dismiss reason contains invalid characters.',
            'permanent_dismiss.boolean' => 'Permanent dismiss must be true or false.',
            'notify_others.boolean' => 'Notify others must be true or false.',
            'dismiss_all_types.boolean' => 'Dismiss all types must be true or false.',
            'notification_type.required' => 'Notification type is required.',
            'notification_type.regex' => 'Notification type contains invalid characters.',
            'message.regex' => 'Message contains invalid characters.',
            'priority.regex' => 'Priority contains invalid characters.',
            'target_users.*.integer' => 'Target user must be a valid user ID.',
            'target_users.*.exists' => 'Target user does not exist.',
            'send_email.boolean' => 'Send email must be true or false.',
            'send_push.boolean' => 'Send push must be true or false.',
            'send_sms.boolean' => 'Send SMS must be true or false.',
            'schedule_time.date' => 'Schedule time must be a valid date.',
            'schedule_time.after' => 'Schedule time must be in the future.',
            'expires_at.date' => 'Expires at must be a valid date.',
            'expires_at.after' => 'Expires at must be in the future.',
            'auto_dismiss.boolean' => 'Auto dismiss must be true or false.',
            'dismiss_after_hours.min' => 'Dismiss after hours must be at least 1.',
            'dismiss_after_hours.max' => 'Dismiss after hours cannot exceed 168 (1 week).',
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
            'dismiss_until' => 'dismiss until date',
            'dismiss_type' => 'dismiss type',
            'dismiss_reason' => 'dismiss reason',
            'permanent_dismiss' => 'permanent dismiss',
            'notify_others' => 'notify others',
            'dismiss_all_types' => 'dismiss all types',
            'notification_type' => 'notification type',
            'message' => 'notification message',
            'priority' => 'notification priority',
            'target_users' => 'target users',
            'target_users.*' => 'target user',
            'send_email' => 'send email',
            'send_push' => 'send push notification',
            'send_sms' => 'send SMS',
            'schedule_time' => 'schedule time',
            'expires_at' => 'expiration date',
            'auto_dismiss' => 'auto dismiss',
            'dismiss_after_hours' => 'dismiss after hours',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'dismiss_type' => $this->dismiss_type ? $this->sanitizeInput($this->dismiss_type) : null,
            'dismiss_reason' => $this->dismiss_reason ? $this->sanitizeInput($this->dismiss_reason) : null,
            'notification_type' => $this->notification_type ? $this->sanitizeInput($this->notification_type) : null,
            'message' => $this->message ? $this->sanitizeInput($this->message) : null,
            'priority' => $this->priority ? $this->sanitizeInput($this->priority) : null,
        ]);
        // Handle checkbox values
        $this->merge([
            'permanent_dismiss' => $this->has('permanent_dismiss'),
            'notify_others' => $this->has('notify_others'),
            'dismiss_all_types' => $this->has('dismiss_all_types'),
            'send_email' => $this->has('send_email'),
            'send_push' => $this->has('send_push'),
            'send_sms' => $this->has('send_sms'),
            'auto_dismiss' => $this->has('auto_dismiss'),
        ]);
        // Set default values
        $this->merge([
            'notify_others' => $this->notify_others ?? false,
            'send_email' => $this->send_email ?? true,
            'send_push' => $this->send_push ?? false,
            'send_sms' => $this->send_sms ?? false,
            'auto_dismiss' => $this->auto_dismiss ?? false,
            'dismiss_after_hours' => $this->dismiss_after_hours ?? 24,
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
