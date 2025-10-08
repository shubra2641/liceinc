<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Helpers\SecurityHelper;

/**
 * Controller for handling password confirmation requests with enhanced security.
 *
 * This controller manages password confirmation for sensitive operations
 * that require additional security verification. It ensures users confirm
 * their password before accessing protected areas.
 *
 * Features:
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Session-based password confirmation tracking
 *
 * @version 1.0.6
 */
class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     *
     * Displays the password confirmation form to users who need to
     * verify their identity before accessing sensitive operations.
     *
     * @return View The password confirmation view
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }
    /**
     * Confirm the user's password with enhanced security.
     *
     * Validates the user's password and stores the confirmation timestamp
     * in the session for subsequent sensitive operations.
     *
     * @param  ConfirmPasswordRequest  $request  The password confirmation request
     *
     * @return RedirectResponse Redirect to intended destination or dashboard
     *
     * @throws ValidationException When password validation fails
     * @throws \Exception When database operations fail
     */
    public function store(ConfirmPasswordRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            if ($user === null) {
                throw new \Illuminate\Auth\AuthenticationException('User not authenticated');
            }
            if (! $this->validatePassword($request)) {
                Log::warning('Password confirmation failed', [
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                throw ValidationException::withMessages([
                    'password' => SecurityHelper::escapeTranslation(__('auth.password')),
                ]);
            }
            $this->storePasswordConfirmation($request);
            DB::commit();
            return redirect()->intended(route('dashboard', absolute: false));
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            $user = $request->user();
            Log::error('Password confirmation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user?->id,
                'ip' => $request->ip(),
            ]);
            throw ValidationException::withMessages([
                'password' => SecurityHelper::escapeTranslation(__('auth.password')),
            ]);
        }
    }
    /**
     * Validate the user's password.
     *
     * @param  Request  $request  The current request
     *
     * @return bool True if password is valid, false otherwise
     */
    private function validatePassword(Request $request): bool
    {
        $user = $request->user();
        if ($user === null) {
            return false;
        }
        return Auth::guard('web')->validate([
            'email' => $user->email,
            'password' => $request->password,
        ]);
    }
    /**
     * Store password confirmation timestamp in session.
     *
     * @param  Request  $request  The current request
     */
    private function storePasswordConfirmation(Request $request): void
    {
        $request->session()->put('auth.password_confirmed_at', time());
    }
}
