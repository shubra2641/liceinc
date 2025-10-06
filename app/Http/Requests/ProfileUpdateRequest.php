<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Profile Update Request with enhanced security.
 *
 * This request class handles validation for updating user profiles
 * with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Comprehensive validation rules for profile updates
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Email uniqueness validation with ignore for current user
 * - Password confirmation support
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
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
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()?->id),
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
                'max:50',
                'regex:/^[0-9\s\-\+\(\)]+$/',
            ],
            'address1' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'address2' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'city' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'state' => [
                'nullable',
                'string',
                'max:100',
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
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'envato_username' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
            'envato_id' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
            'current_password' => [
                'nullable',
                'string',
                'current_password',
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'password_confirmation' => [
                'nullable',
                'string',
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
            'name.regex' => 'Name contains invalid characters.',
            'email.unique' => 'This email address is already registered.',
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
            'envato_username.regex' => 'Envato username contains invalid characters.',
            'envato_id.regex' => 'Envato ID contains invalid characters.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
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
            'firstname' => 'first name',
            'lastname' => 'last name',
            'companyname' => 'company name',
            'phonenumber' => 'phone number',
            'address1' => 'address line 1',
            'address2' => 'address line 2',
            'postcode' => 'postal code',
            'envato_username' => 'Envato username',
            'envato_id' => 'Envato ID',
            'current_password' => 'current password',
            'password_confirmation' => 'password confirmation',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input to prevent XSS
        $this->merge([
            'name' => $this->sanitizeInput($this->name),
            'firstname' => $this->firstname ? $this->sanitizeInput($this->firstname) : null,
            'lastname' => $this->lastname ? $this->sanitizeInput($this->lastname) : null,
            'companyname' => $this->companyname ? $this->sanitizeInput($this->companyname) : null,
            'phonenumber' => $this->phonenumber ? $this->sanitizeInput($this->phonenumber) : null,
            'address1' => $this->address1 ? $this->sanitizeInput($this->address1) : null,
            'address2' => $this->address2 ? $this->sanitizeInput($this->address2) : null,
            'city' => $this->city ? $this->sanitizeInput($this->city) : null,
            'state' => $this->state ? $this->sanitizeInput($this->state) : null,
            'postcode' => $this->postcode ? $this->sanitizeInput($this->postcode) : null,
            'country' => $this->country ? $this->sanitizeInput($this->country) : null,
            'envato_username' => $this->envato_username ? $this->sanitizeInput($this->envato_username) : null,
            'envato_id' => $this->envato_id ? $this->sanitizeInput($this->envato_id) : null,
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
