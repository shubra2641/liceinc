<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller for handling email verification notification requests.
 *
 * This controller manages the sending of email verification notifications
 * to users who need to verify their email addresses. It prevents sending
 * notifications to already verified users.
 * @version 1.0.6
 */
class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * Sends an email verification notification to the authenticated user
     * if their email address is not already verified. Redirects verified
     * users to the dashboard.
     *
     * @param  Request  $request  The current request
     *
     * @return RedirectResponse Redirect to dashboard or back with success message
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            return redirect()->route('login');
        }
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }
        $this->sendVerificationNotification($user);
        return back()->with('success', 'verification-link-sent');
    }
    /**
     * Send email verification notification to user.
     *
     * @param  \App\Models\User  $user  The user to send verification email to
     */
    private function sendVerificationNotification($user): void
    {
        $user->sendEmailVerificationNotification();
    }
}
