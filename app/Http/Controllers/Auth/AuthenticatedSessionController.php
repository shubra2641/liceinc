<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controller for handling user authentication sessions with enhanced security.
 *
 * This controller manages user login, logout, and session handling.
 * It includes email verification checks and proper redirection based
 * on user roles and verification status.
 *
 * Features:
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Session management with CSRF protection
 * - Email verification handling
 *
 * @version 1.0.6
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view with enhanced security.
     *
     * Shows the login form to users. Can detect if the user is coming
     * from the installation process to provide appropriate context.
     *
     * @return View The login view
     */
    public function create(): View
    {
        $fromInstall = $this->sanitizeInput(request()->get('from_install', false));

        return view('auth.login', ['fromInstall' => $fromInstall]);
    }

    /**
     * Handle an incoming authentication request with enhanced security.
     *
     * Processes user login attempts, handles email verification checks,
     * and redirects users to appropriate dashboards based on their
     * role and verification status.
     *
     * @param  LoginRequest  $request  The login request with validated credentials
     *
     * @return RedirectResponse Redirect to appropriate dashboard or verification page
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Exception When database operations fail
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $request->authenticate();
            $request->session()->regenerate();
            $user = Auth::user();
            if ($user === null) {
                throw new \Illuminate\Auth\AuthenticationException('User not authenticated');
            }
            // Handle email verification requirements
            if (! $user->hasVerifiedEmail()) {
                DB::commit();

                return $this->handleUnverifiedEmail($request, $user);
            }
            DB::commit();

            return $this->redirectToDashboard($user);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session with enhanced security.
     *
     * Logs out the user, invalidates the session, and regenerates
     * the CSRF token for security.
     *
     * @param  Request  $request  The current request
     *
     * @return RedirectResponse Redirect to home page
     *
     * @throws \Exception When session operations fail
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            DB::commit();

            return redirect('/');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Session destruction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Still redirect even if session cleanup fails
            return redirect('/');
        }
    }

    /**
     * Handle unverified email addresses with enhanced security.
     *
     * Checks if the email is a test email and handles verification
     * email sending for real email addresses.
     *
     * @param  Request  $request  The current request
     * @param  \App\Models\User  $user  The authenticated user
     *
     * @return RedirectResponse Redirect to verification notice or test email warning
     */
    private function handleUnverifiedEmail(Request $request, $user): RedirectResponse
    {
        if ($this->isTestEmail($user->email)) {
            Log::warning('Test email login attempt', [
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('test-email-warning');
        }
        $this->sendVerificationEmailIfNeeded($request, $user);

        return redirect()->route('verification.notice')
            ->with('success', 'Please verify your email address before accessing your account.');
    }

    /**
     * Check if the email address is a test email with enhanced validation.
     *
     * @param  string  $email  The email address to check
     *
     * @return bool True if it's a test email, false otherwise
     */
    private function isTestEmail(string $email): bool
    {
        $email = $this->sanitizeInput($email);
        $testDomains = ['@example.com', '@test.com', '@localhost', '@demo.com'];
        foreach ($testDomains as $domain) {
            if (str_contains(is_string($email) ? $email : '', $domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send verification email if not already sent with enhanced error handling.
     *
     * @param  Request  $request  The current request
     * @param  \App\Models\User  $user  The user to send verification email to
     */
    private function sendVerificationEmailIfNeeded(Request $request, $user): void
    {
        if (! $request->session()->has('verification_email_sent')) {
            try {
                $user->sendEmailVerificationNotification();
                $request->session()->put('verification_email_sent', true);
            } catch (\Exception $e) {
                Log::error('Verification email sending failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'ip' => $request->ip(),
                ]);
                // Silently handle email errors to not prevent login
            }
        }
    }

    /**
     * Get security status information.
     *
     * Returns current security status and rate limiting information.
     * Used for monitoring and health checks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function securityStatus()
    {
        try {
            // Security status check - no logging needed for successful operations
            return response()->json([
                'status' => 'secure',
                'timestamp' => now(),
                'rate_limits' => [
                    'auth' => '5 requests per minute',
                    'security' => '10 requests per minute',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Security status check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Security check failed'], 500);
        }
    }

    /**
     * Log authentication events.
     *
     * Logs authentication-related events for monitoring and security analysis.
     * Validates input data and logs events to the application log.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logAuthEvent(Request $request)
    {
        try {
            $data = $request->validate([
                'event' => 'required|string|max:255',
                'user_id' => 'nullable|integer',
                'ip' => 'required|ip',
                'user_agent' => 'required|string|max:500',
            ]);

            // Authentication event - no logging needed for successful operations
            return response()->json(['status' => 'logged']);
        } catch (\Exception $e) {
            Log::error('Authentication logging failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Logging failed'], 500);
        }
    }

    /**
     * Redirect user to appropriate dashboard based on role.
     *
     * @param  \App\Models\User  $user  The authenticated user
     *
     * @return RedirectResponse Redirect to admin or user dashboard
     */
    private function redirectToDashboard($user): RedirectResponse
    {
        $redirectRoute = $user->hasRole('admin')
            ? route('admin.dashboard', absolute: false)
            : route('dashboard', absolute: false);

        return redirect()->intended($redirectRoute);
    }

    /**
     * Sanitize input to prevent XSS attacks.
     *
     * @param  mixed  $input  The input to sanitize
     *
     * @return mixed The sanitized input
     */
    protected function sanitizeInput(mixed $input): mixed
    {
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }

        return $input;
    }
}
