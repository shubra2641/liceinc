<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Profile Controller with enhanced security.
 *
 * This controller handles user profile management including viewing, editing,
 * updating, and account deletion with comprehensive security measures and
 * proper error handling.
 *
 * Features:
 * - User profile display with related data
 * - Profile editing form display
 * - Profile information updates
 * - Account deletion with password confirmation
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling and logging
 * - Proper logging for errors and warnings only
 * - Rate limiting for profile operations
 * - Authorization checks for profile access
 */
class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'user', 'verified']);
    }

    /**
     * Display the user's profile with enhanced security.
     *
     * Shows the authenticated user's profile information including
     * licenses, domains, and tickets with proper authorization checks.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return View The profile index view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access: GET /profile
     * // Returns: View with user profile data
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'profile-index:'.(Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 20)) {
                Log::warning('Rate limit exceeded for profile index', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(429, 'Too many requests');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Authorization check
            if (! Auth::check()) {
                Log::warning('Unauthenticated access attempt to profile', [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return redirect()->route('login');
            }
            $user = $request->user();
            if ($user) {
                $user->load(['licenses.product', 'licenses.domains', 'tickets']);
            }

            return view('profile.index', [
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load profile index', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            abort(500, 'Failed to load profile');
        }
    }

    /**
     * Display the user's profile editing form with enhanced security.
     *
     * Shows the profile editing form for the authenticated user
     * with proper authorization checks.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return View The profile editing form view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access: GET /profile/edit
     * // Returns: View with profile editing form
     */
    public function edit(Request $request): View|RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'profile-edit:'.(Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for profile edit form', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(429, 'Too many requests');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Authorization check
            if (! Auth::check()) {
                Log::warning('Unauthenticated access attempt to profile edit form', [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return redirect()->route('login');
            }
            /** @var view-string $viewName */
            $viewName = 'user.profile.edit';

            return view($viewName, ['user' => $request->user()]);
        } catch (\Exception $e) {
            Log::error('Failed to load profile edit form', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            abort(500, 'Failed to load profile edit form');
        }
    }

    /**
     * Update the user's profile information with enhanced security.
     *
     * Updates the authenticated user's profile information with
     * comprehensive validation and security measures.
     *
     * @param  ProfileUpdateRequest  $request  The validated request containing profile data
     *
     * @return RedirectResponse Redirect to profile edit page with success message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * PUT /profile
     * {
     *     "name": "John Doe",
     *     "email": "john@example.com"
     * }
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'profile-update:'.(Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 5)) {
                Log::warning('Rate limit exceeded for profile update', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return redirect()->back()->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Authorization check
            if (! Auth::check()) {
                Log::warning('Unauthenticated attempt to update profile', [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return redirect()->route('login');
            }
            DB::beginTransaction();
            $user = $request->user();
            $validatedData = $request->validated();
            // Sanitize input data
            if (isset($validatedData['name'])) {
                $validatedData['name'] = $this->sanitizeInput($validatedData['name']);
            }
            if (isset($validatedData['email'])) {
                $validatedData['email'] = $this->sanitizeInput($validatedData['email']);
            }
            if ($user) {
                $user->fill($validatedData);
                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                    Log::warning('User email changed, verification required', [
                        'user_id' => $user->id,
                        'old_email' => $user->getOriginal('email'),
                        'new_email' => $user->email,
                    'ip' => request()->ip(),
                ]);
                }
                $user->save();
            }
            DB::commit();

            return Redirect::route('profile.edit')->with('success', 'profile-updated');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Profile update validation failed', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update profile', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);

            return redirect()->back()->with('error', 'Failed to update profile. Please try again.');
        }
    }

    /**
     * Delete the user's account with enhanced security.
     *
     * Deletes the authenticated user's account with password confirmation
     * and proper session cleanup.
     *
     * @param  Request  $request  The HTTP request containing password confirmation
     *
     * @return RedirectResponse Redirect to home page after account deletion
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * DELETE /profile
     * {
     *     "password": "current_password"
     * }
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'profile-destroy:'.(Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 3)) {
                Log::warning('Rate limit exceeded for profile deletion', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return redirect()->back()->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Authorization check
            if (! Auth::check()) {
                Log::warning('Unauthenticated attempt to delete profile', [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return redirect()->route('login');
            }
            $validated = $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);
            DB::beginTransaction();
            $user = $request->user();
            if ($user) {
                Log::warning('User account deletion initiated', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                ]);
                Auth::logout();
                $user->delete();
            }
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            DB::commit();
            Log::warning('User account deleted successfully', [
                'deleted_user_id' => $user?->id,
                'ip' => request()->ip(),
            ]);

            return Redirect::to('/');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Profile deletion validation failed', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete profile', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);

            return redirect()->back()->with('error', 'Failed to delete account. Please try again.');
        }
    }
}
