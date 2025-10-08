<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for handling email verification requests.
 *
 * This controller handles the verification of user email addresses
 * through the email verification process. It ensures that users
 * can only verify their email addresses through secure, signed URLs.
 *
 * @version 1.0.6
 */
class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * This method processes the email verification request and marks
     * the user's email as verified if the request is valid. It handles
     * both cases where the email is already verified and where it needs
     * to be verified for the first time.
     *
     * @param  EmailVerificationRequest  $request  The email verification request
     *
     * @return RedirectResponse Redirect to dashboard with verification status
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            return redirect()->route('login');
        }
        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return $this->redirectToDashboard();
        }
        // Mark email as verified and fire verification event
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->redirectToDashboard();
    }

    /**
     * Redirect to dashboard with verification status.
     *
     * This method provides a centralized way to redirect users
     * to the dashboard after email verification, eliminating
     * code duplication.
     *
     * @return RedirectResponse Redirect to dashboard with verified parameter
     */
    private function redirectToDashboard(): RedirectResponse
    {
        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
