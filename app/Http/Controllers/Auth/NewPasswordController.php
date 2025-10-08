<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Controller for handling new password requests. *
 * This controller manages password reset functionality, allowing users * to reset their passwords using valid reset tokens sent via email. * @version 1.0.6 */
class NewPasswordController extends Controller
{
    /**   * Display the password reset view. *   * Shows the password reset form to users who have clicked on * a password reset link from their email. *   * @param Request $request The current request containing reset token *   * @return View The password reset view */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }
    /**   * Handle an incoming new password request. *   * Validates the reset token and new password, then updates the user's * password if the token is valid. Fires a password reset event upon * successful password update. *   * @param Request $request The password reset request *   * @return RedirectResponse Redirect to login on success or back with errors *   * @throws \Illuminate\Validation\ValidationException */
    public function store(Request $request): RedirectResponse
    {
        $this->validatePasswordResetRequest($request);
        $status = $this->resetPassword($request);
        return $this->handlePasswordResetResponse($request, $status);
    }
    /**   * Validate the password reset request. *   * @param Request $request The current request *   * @throws \Illuminate\Validation\ValidationException */
    private function validatePasswordResetRequest(Request $request): void
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    }
    /**   * Reset the user's password. *   * @param Request $request The current request *   * @return string The password reset status */
    private function resetPassword(Request $request): string
    {
        $result = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $this->updateUserPassword($user, is_string($request->password) ? $request->password : '');
                event(new PasswordReset($user));
            },
        );
        return is_string($result) ? $result : '';
    }
    /**   * Update user's password and remember token. *   * @param User $user The user to update * @param string $password The new password */
    private function updateUserPassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();
    }
    /**   * Handle the password reset response. *   * @param Request $request The current request * @param string $status The password reset status *   * @return RedirectResponse Appropriate redirect based on status */
    private function handlePasswordResetResponse(Request $request, string $status): RedirectResponse
    {
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', __($status));
        }
        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
