<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\Traits\ProfileDataSanitization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * User Request with enhanced security.
 *
 * This unified request class handles validation for both creating and updating
 * users with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both store and update operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Unique validation with ignore for current record on updates
 * - Role validation and password confirmation
 * - Profile information validation
 */
class UserRequest extends FormRequest
{
    use ProfileDataSanitization;

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
        $userId = $this->route('user')->id ?? null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                $isUpdate ? Rule::unique('users')->ignore($userId) : 'unique:users,email',
            ],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'password_confirmation' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
            ],
            'role' => [
                'required',
                'string',
                Rule::in(['user', 'admin']),
            ],
            'firstname' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'lastname' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'companyname' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'phonenumber' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9\s\-\+\(\)]+$/',
            ],
            'address1' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address2' => [
                'nullable',
                'string',
                'max:255',
            ],
            'city' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'state' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'postcode' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
            'country' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'send_welcome_email' => [
                'boolean',
            ],
            'is_active' => [
                'boolean',
            ],
            'email_verified_at' => [
                'nullable',
                'date',
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
            'name.required' => 'Full name is required.',
            'name.regex' => 'Name contains invalid characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'role.required' => 'User role is required.',
            'role.in' => 'Role must be either user or admin.',
            'firstname.regex' => 'First name contains invalid characters.',
            'lastname.regex' => 'Last name contains invalid characters.',
            'companyname.regex' => 'Company name contains invalid characters.',
            'phonenumber.regex' => 'Phone number contains invalid characters.',
            'address1.regex' => 'Address contains invalid characters.',
            'address2.regex' => 'Address contains invalid characters.',
            'city.regex' => 'City contains invalid characters.',
            'state.regex' => 'State contains invalid characters.',
            'postcode.regex' => 'Postcode contains invalid characters.',
            'country.regex' => 'Country contains invalid characters.',
            'email_verified_at.date' => 'Email verification date must be a valid date.',
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
            'name' => 'full name',
            'email' => 'email address',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
            'role' => 'user role',
            'firstname' => 'first name',
            'lastname' => 'last name',
            'companyname' => 'company name',
            'phonenumber' => 'phone number',
            'address1' => 'address line 1',
            'address2' => 'address line 2',
            'city' => 'city',
            'state' => 'state',
            'postcode' => 'postal code',
            'country' => 'country',
            'send_welcome_email' => 'send welcome email',
            'is_active' => 'active status',
            'email_verified_at' => 'email verification date',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->sanitizeProfileFields();
        // Handle checkbox values
        $this->merge([
            'send_welcome_email' => $this->has('send_welcome_email'),
            'is_active' => $this->has('is_active'),
        ]);
        // Set default values
        $this->merge([
            'is_active' => $this->is_active ?? true,
            'email_verified_at' => $this->email_verified_at ?? now(),
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
