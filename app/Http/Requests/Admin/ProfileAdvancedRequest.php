<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\Traits\ProfileDataSanitization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Profile Advanced Request with enhanced security.
 *
 * This unified request class handles validation for both profile updates
 * and password changes with comprehensive security measures and input sanitization.
 *
 * Features:
 * - Unified validation for both profile update and password change operations
 * - XSS protection and input sanitization
 * - Custom validation messages for better user experience
 * - Proper type hints and return types
 * - Security validation rules (XSS protection, SQL injection prevention)
 * - Password strength validation
 * - Email uniqueness validation
 */
class ProfileAdvancedRequest extends FormRequest
{
    use ProfileDataSanitization;

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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $route = $this->route();
        $isPasswordUpdate = $this->isMethod('POST') && $route && str_contains($route->getName() ?? '', 'password');
        $userId = auth()->id();
        // Password update validation
        if ($isPasswordUpdate) {
            return [
                'current_password' => [
                    'required',
                    'current_password',
                ],
                'password' => [
                    'required',
                    'confirmed',
                    Password::defaults(),
                ],
                'password_confirmation' => [
                    'required',
                    'same:password',
                ],
            ];
        }

        // Profile update validation
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
                'unique:users,email,' . $userId,
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
            'timezone' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\/_\-]+$/',
            ],
            'language' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^[a-z]{2}(_[A-Z]{2})?$/',
            ],
            'date_format' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'time_format' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'currency' => [
                'nullable',
                'string',
                'max:3',
                'regex:/^[A-Z]{3}$/',
            ],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048', // 2MB
                'dimensions:max_width=512,max_height=512',
            ],
            'bio' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\-_., !?@#$%&*()]+$/',
            ],
            'website' => [
                'nullable',
                'url',
                'max:500',
            ],
            'social_facebook' => [
                'nullable',
                'url',
                'max:500',
            ],
            'social_twitter' => [
                'nullable',
                'url',
                'max:500',
            ],
            'social_linkedin' => [
                'nullable',
                'url',
                'max:500',
            ],
            'social_instagram' => [
                'nullable',
                'url',
                'max:500',
            ],
            'social_github' => [
                'nullable',
                'url',
                'max:500',
            ],
            'notifications_email' => [
                'boolean',
            ],
            'notifications_sms' => [
                'boolean',
            ],
            'notifications_push' => [
                'boolean',
            ],
            'privacy_public_profile' => [
                'boolean',
            ],
            'privacy_show_email' => [
                'boolean',
            ],
            'privacy_show_phone' => [
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
            'name.required' => 'Name is required.',
            'name.regex' => 'Name contains invalid characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'firstname.regex' => 'First name contains invalid characters.',
            'lastname.regex' => 'Last name contains invalid characters.',
            'companyname.regex' => 'Company name contains invalid characters.',
            'phonenumber.regex' => 'Phone number contains invalid characters.',
            'address1.regex' => 'Address 1 contains invalid characters.',
            'address2.regex' => 'Address 2 contains invalid characters.',
            'city.regex' => 'City contains invalid characters.',
            'state.regex' => 'State contains invalid characters.',
            'postcode.regex' => 'Postal code contains invalid characters.',
            'country.regex' => 'Country contains invalid characters.',
            'timezone.regex' => 'Timezone contains invalid characters.',
            'language.regex' => 'Language must be in format: en or en_US.',
            'date_format.regex' => 'Date format contains invalid characters.',
            'time_format.regex' => 'Time format contains invalid characters.',
            'currency.regex' => 'Currency must be a 3-letter code (e.g., USD).',
            'avatar.dimensions' => 'Avatar dimensions must not exceed 512x512 pixels.',
            'avatar.max' => 'Avatar size must not exceed 2MB.',
            'avatar.mimes' => 'Avatar must be a file of type: jpeg, png, jpg, gif, webp.',
            'bio.regex' => 'Bio contains invalid characters.',
            'website.url' => 'Website must be a valid URL.',
            'social_facebook.url' => 'Facebook URL must be a valid URL.',
            'social_twitter.url' => 'Twitter URL must be a valid URL.',
            'social_linkedin.url' => 'LinkedIn URL must be a valid URL.',
            'social_instagram.url' => 'Instagram URL must be a valid URL.',
            'social_github.url' => 'GitHub URL must be a valid URL.',
            'current_password.required' => 'Current password is required.',
            'current_password.current_password' => 'The current password is incorrect.',
            'password.required' => 'New password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_confirmation.same' => 'Password confirmation does not match.',
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
            'firstname' => 'first name',
            'lastname' => 'last name',
            'companyname' => 'company name',
            'phonenumber' => 'phone number',
            'address1' => 'address line 1',
            'address2' => 'address line 2',
            'city' => 'city',
            'state' => 'state/province',
            'postcode' => 'postal code',
            'country' => 'country',
            'timezone' => 'timezone',
            'language' => 'language',
            'date_format' => 'date format',
            'time_format' => 'time format',
            'currency' => 'currency',
            'avatar' => 'profile picture',
            'bio' => 'biography',
            'website' => 'website URL',
            'social_facebook' => 'Facebook URL',
            'social_twitter' => 'Twitter URL',
            'social_linkedin' => 'LinkedIn URL',
            'social_instagram' => 'Instagram URL',
            'social_github' => 'GitHub URL',
            'notifications_email' => 'email notifications',
            'notifications_sms' => 'SMS notifications',
            'notifications_push' => 'push notifications',
            'privacy_public_profile' => 'public profile',
            'privacy_show_email' => 'show email',
            'privacy_show_phone' => 'show phone',
            'current_password' => 'current password',
            'password' => 'new password',
            'password_confirmation' => 'password confirmation',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->sanitizeProfileFields();
        $this->sanitizeAdditionalFields();
        // Handle checkbox values
        $this->merge([
            'notifications_email' => $this->has('notifications_email'),
            'notifications_sms' => $this->has('notifications_sms'),
            'notifications_push' => $this->has('notifications_push'),
            'privacy_public_profile' => $this->has('privacy_public_profile'),
            'privacy_show_email' => $this->has('privacy_show_email'),
            'privacy_show_phone' => $this->has('privacy_show_phone'),
        ]);
        // Set default values
        $this->merge([
            'timezone' => $this->timezone ?? 'UTC',
            'language' => $this->language ?? 'en',
            'date_format' => $this->date_format ?? 'Y-m-d',
            'time_format' => $this->time_format ?? 'H:i:s',
            'currency' => $this->currency ?? 'USD',
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
