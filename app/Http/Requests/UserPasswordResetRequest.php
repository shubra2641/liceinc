<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * User Password Reset Request with enhanced security.
 *
 * This request class handles validation and authorization for sending
 * password reset emails to users in the admin panel.
 */
class UserPasswordResetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'userId' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'confirm_reset' => [
                'required',
                'boolean',
                'accepted',
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
            'userId.required' => 'User ID is required.',
            'userId.integer' => 'User ID must be a valid integer.',
            'userId.exists' => 'The specified user does not exist.',
            'confirm_reset.required' => 'Password reset confirmation is required.',
            'confirm_reset.accepted' => 'You must confirm the password reset action.',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure userId is properly cast to integer
        if ($this->has('userId')) {
            $userId = $this->input('userId');
            if (is_numeric($userId)) {
                $this->merge([
                    'userId' => (int)$userId,
                ]);
            }
        }
    }
}
