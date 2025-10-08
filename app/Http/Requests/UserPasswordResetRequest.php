<?php

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
            'user_id' => [
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
            'user_id.required' => 'User ID is required.',
            'user_id.integer' => 'User ID must be a valid integer.',
            'user_id.exists' => 'The specified user does not exist.',
            'confirm_reset.required' => 'Password reset confirmation is required.',
            'confirm_reset.accepted' => 'You must confirm the password reset action.',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure user_id is properly cast to integer
        if ($this->has('user_id')) {
            $userId = $this->input('user_id');
            if (is_numeric($userId)) {
                $this->merge([
                    'user_id' => (int)$userId,
                ]);
            }
        }
    }
}
