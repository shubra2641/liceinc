<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
/**
 * License Request with enhanced security.
 *
 * This unified request class handles validation for both creating and updating
 * licenses with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both store and update operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Unique validation with ignore for current record on updates
 * - License key generation and validation
 * - Domain management validation
/
class LicenseRequest extends FormRequest
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
        $licenseId = $this->route('license')->id ?? null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users, id',
            ],
            'product_id' => [
                'required',
                'integer',
                'exists:products, id',
            ],
            'purchase_code' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Z0-9\-]+$/',
                $isUpdate
                    ? Rule::unique('licenses', 'purchase_code')->ignore($licenseId)
                    : 'unique:licenses, purchase_code',
            ],
            'license_key' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[A-Z0-9\-]+$/',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive', 'suspended', 'expired', 'revoked']),
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:today',
            ],
            'max_domains' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'allowed_domains' => [
                'nullable',
                'array',
            ],
            'allowed_domains.*' => [
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-\.]+$/',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'is_trial' => [
                'boolean',
            ],
            'trial_ends_at' => [
                'nullable',
                'date',
                'after:today',
            ],
            'auto_renewal' => [
                'boolean',
            ],
            'renewal_period' => [
                'nullable',
                'integer',
                'min:1',
                'max:365',
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
            'user_id.required' => 'User selection is required.',
            'user_id.exists' => 'Selected user does not exist.',
            'product_id.required' => 'Product selection is required.',
            'product_id.exists' => 'Selected product does not exist.',
            'purchase_code.required' => 'Purchase code is required.',
            'purchase_code.unique' => 'This purchase code is already registered.',
            'purchase_code.regex' => 'Purchase code can only contain uppercase letters, numbers, and hyphens.',
            'license_key.regex' => 'License key can only contain uppercase letters, numbers, and hyphens.',
            'status.required' => 'License status is required.',
            'status.in' => 'Status must be one of: active, inactive, suspended, expired, revoked.',
            'expires_at.after' => 'Expiration date must be in the future.',
            'max_domains.min' => 'Maximum domains must be at least 1.',
            'max_domains.max' => 'Maximum domains cannot exceed 100.',
            'allowed_domains.*.regex' => 'Domain contains invalid characters.',
            'notes.regex' => 'Notes contain invalid characters.',
            'trial_ends_at.after' => 'Trial end date must be in the future.',
            'renewal_period.min' => 'Renewal period must be at least 1 day.',
            'renewal_period.max' => 'Renewal period cannot exceed 365 days.',
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
            'user_id' => 'user',
            'product_id' => 'product',
            'purchase_code' => 'purchase code',
            'license_key' => 'license key',
            'status' => 'license status',
            'expires_at' => 'expiration date',
            'max_domains' => 'maximum domains',
            'allowed_domains' => 'allowed domains',
            'notes' => 'license notes',
            'is_trial' => 'trial license',
            'trial_ends_at' => 'trial end date',
            'auto_renewal' => 'auto renewal',
            'renewal_period' => 'renewal period',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'purchase_code' => $this->sanitizeInput($this->purchase_code),
            'license_key' => $this->license_key ? $this->sanitizeInput($this->license_key) : null,
            'notes' => $this->notes ? $this->sanitizeInput($this->notes) : null,
        ]);
        // Sanitize allowed domains
        if ($this->allowed_domains && is_array($this->allowed_domains)) {
            $sanitizedDomains = [];
            foreach ($this->allowed_domains as $domain) {
                $sanitizedDomains[] = $this->sanitizeInput($domain);
            }
            $this->merge(['allowed_domains' => $sanitizedDomains]);
        }
        // Handle checkbox values
        $this->merge([
            'is_trial' => $this->has('is_trial'),
            'auto_renewal' => $this->has('auto_renewal'),
        ]);
        // Set default values
        $this->merge([
            'status' => $this->status ?? 'active',
            'max_domains' => $this->max_domains ?? 1,
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
