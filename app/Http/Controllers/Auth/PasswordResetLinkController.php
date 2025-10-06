<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Controller for handling password reset link requests.
 *
 * This controller manages the sending of password reset links to users
 * who have forgotten their passwords. It validates email addresses and
 * sends reset links via email.
 * @version 1.0.6
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     *
     * Shows the forgot password form where users can enter their
     * email address to request a password reset link.
     *
     * @return View The forgot password view
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }
    /**
     * Handle an incoming password reset link request.
     *
     * Validates the email address and sends a password reset link
     * to the user if the email exists in the system.
     *
     * @param  Request  $request  The password reset link request
     *
     * @return RedirectResponse Redirect back with success or error message
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(PasswordResetRequest $request): RedirectResponse
    {
        $status = $this->sendPasswordResetLink($request);
        return $this->handlePasswordResetResponse($request, $status);
    }
    /**
     * Send password reset link to user.
     *
     * @param  PasswordResetRequest  $request  The current request
     *
     * @return string The password reset status
     */
    private function sendPasswordResetLink(PasswordResetRequest $request): string
    {
        return Password::sendResetLink(
            $request->only('email'),
        );
    }
    /**
     * Handle the password reset response.
     *
     * @param  PasswordResetRequest  $request  The current request
     * @param  string  $status  The password reset status
     *
     * @return RedirectResponse Appropriate redirect based on status
     */
    private function handlePasswordResetResponse(PasswordResetRequest $request, string $status): RedirectResponse
    {
        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }
        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
