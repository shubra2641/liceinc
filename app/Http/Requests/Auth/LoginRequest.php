<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Helpers\SecurityHelper;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login Request with enhanced security and validation.
 *
 * This request class handles authentication requests with comprehensive security
 * measures including rate limiting, input validation, and security logging.
 *
 * Features:
 * - Rate limiting protection against brute force attacks
 * - Input validation and sanitization
 * - Security event logging
 * - Custom validation messages
 * - Enhanced authentication with remember functionality
 * - IP-based throttling with email combination
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true for login requests
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
            'remember' => [
                'nullable',
                'in:on,1,true,false,0,',
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
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            'email.max' => 'البريد الإلكتروني لا يمكن أن يكون أكثر من 255 حرف.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 8 أحرف.',
            'password.max' => 'كلمة المرور لا يمكن أن تكون أكثر من 255 حرف.',
            'remember.in' => 'تذكرني يجب أن يكون صحيح أو خطأ.',
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
            'email' => 'email address',
            'password' => 'password',
            'remember' => 'remember me',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize email input to prevent XSS
        if ($this->has('email')) {
            $this->merge([
                'email' => $this->sanitizeEmail($this->input('email')),
            ]);
        }
    }

    /**
     * Sanitize email input with validation.
     *
     * @param  mixed  $email  The email to sanitize
     *
     * @return string|null The sanitized email
     */
    private function sanitizeEmail(mixed $email): ?string
    {
        if ($email === null || ! is_string($email)) {
            return null;
        }
        // Trim and convert to lowercase for consistency
        $email = trim(strtolower($email));

        // Don't use htmlspecialchars for email as it can break the format
        return $email;
    }

    /**
     * Attempt to authenticate the request's credentials with enhanced security.
     *
     * Performs authentication with rate limiting, security logging, and
     * comprehensive error handling for failed login attempts.
     *
     * @throws ValidationException When authentication fails
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $email = $this->input('email');
        $password = $this->input('password');
        $remember = $this->boolean('remember');
        if (! Auth::attempt($this->only('email', 'password'), $remember)) {
            // Log failed login attempt for security monitoring
            Log::warning('Failed login attempt', [
                'email' => $email,
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => SecurityHelper::escapeTranslation(trans('auth.failed')),
            ]);
        }
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited with enhanced security logging.
     *
     * Checks rate limiting status and logs security events for monitoring
     * and protection against brute force attacks.
     *
     * @throws ValidationException When rate limit is exceeded
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 20)) {
            return;
        }
        // Log rate limit exceeded for security monitoring
        Log::warning('Rate limit exceeded for login attempt', [
            'email' => $this->input('email'),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'throttle_key' => $this->throttleKey(),
            'timestamp' => now()->toISOString(),
        ]);
        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());
        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request with enhanced security.
     *
     * Creates a unique throttle key combining email and IP address for
     * rate limiting protection against brute force attacks.
     *
     * @return string The unique throttle key for rate limiting
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->toString()).'|'.$this->ip());
    }
}
