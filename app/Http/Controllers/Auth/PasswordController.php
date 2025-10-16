<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Controller for handling password updates.
 *
 * This controller manages password updates for authenticated users,
 * ensuring proper validation of current password and new password
 * requirements before updating the user's password.
 *
 * @version 1.0.6
 */
class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * Validates the current password and new password requirements,
     * then updates the user's password with proper hashing.
     *
     * @param  Request  $request  The password update request
     *
     * @return RedirectResponse Redirect back with success message
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $this->validatePasswordUpdate($request);
        $user = $request->user();
        if ($user) {
            $this->updateUserPassword($user, is_string($validated['password']) ? $validated['password'] : '');
        }

        return back()->with('success', 'password-updated');
    }

    /**
     * Validate the password update request.
     *
     * @param  Request  $request  The current request
     *
     * @return array The validated data
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    /**
     * @return array<string, mixed>
     */
    private function validatePasswordUpdate(Request $request): array
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        /**
         * @var array<string, mixed> $result
         */
        $result = $validated;

        return $result;
    }

    /**
     * Update the user's password.
     *
     * @param  \App\Models\User  $user  The user to update
     * @param  string  $password  The new password
     */
    private function updateUserPassword($user, string $password): void
    {
        $user->update([
            'password' => Hash::make($password),
        ]);
    }
}
