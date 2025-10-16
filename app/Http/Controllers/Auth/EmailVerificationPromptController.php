<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for displaying email verification prompts.
 *
 * This controller handles the display of email verification prompts
 * to users who need to verify their email addresses. It redirects
 * already verified users to the dashboard.
 *
 * @version 1.0.6
 */
class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     *
     * Shows the email verification prompt to unverified users or
     * redirects verified users to the dashboard.
     *
     * @param  Request  $request  The current request
     *
     * @return RedirectResponse|View Redirect to dashboard or show verification prompt
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();
        if ($user === null) {
            return redirect()->route('login');
        }
        if ($user->hasVerifiedEmail() === true) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return view('auth.verify-email');
    }
}
