<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
/**
 * User Profile Controller with enhanced security.
 *
 * This controller handles user profile management functionality including
 * profile viewing, editing, updating, and Envato account integration
 * with enhanced security measures and proper error handling.
 *
 * Features:
 * - User profile display and editing
 * - Profile information updates with validation
 * - Envato account linking and unlinking
 * - Account deletion with security confirmation
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Session management and security
 */
class ProfileController extends Controller
{
    /**
     * Display the user's profile with enhanced security.
     *
     * Shows comprehensive user profile with licenses, domains, and tickets
     * with proper error handling and security measures.
     *
     * @param  Request  $request  The HTTP request containing user information
     *
     * @return View The user profile view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access the user profile:
     * GET /profile
     *
     * // Returns view with:
     * // - User profile data
     * // - User licenses with products and domains
     * // - User tickets
     */
    public function index(Request $request): View
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }
            // Load user with related data
            $user->load(['licenses.product', 'licenses.domains', 'tickets']);
            DB::commit();
            return view('profile.index', [
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User profile display failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            // Return empty profile with error message
            return view('profile.index', [
                'user' => $request->user(),
                'error' => 'Failed to load profile data. Please try again.',
            ]);
        }
    }
    /**
     * Display the user's profile form with enhanced security.
     *
     * Shows the profile editing form with current user data
     * and proper error handling.
     *
     * @param  Request  $request  The HTTP request containing user information
     *
     * @return View The profile edit form view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access the profile edit form:
     * GET /profile/edit
     *
     * // Returns view with:
     * // - Current user profile data
     * // - Profile editing form
     */
    public function edit(Request $request): View
    {
        try {
            $user = $request->user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }
            return view('profile.index', [
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Profile edit form display failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return view('profile.index', [
                'user' => $request->user(),
                'error' => 'Failed to load profile form. Please try again.',
            ]);
        }
    }
    /**
     * Update the user's profile information with enhanced security.
     *
     * Updates user profile data with comprehensive validation and proper
     * email verification handling when email is changed.
     *
     * @param  ProfileUpdateRequest  $request  The validated request containing profile data
     *
     * @return RedirectResponse Redirect to profile edit with success/error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Update profile:
     * PUT /profile
     * {
     *     "name": "John Doe",
     *     "email": "john@example.com"
     * }
     *
     * // Response: Redirect to profile edit with success message
     * // "Profile updated successfully"
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }
            $user->fill($request->validated());
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            $user->save();
            DB::commit();
            // Redirect to intended URL if exists, otherwise to profile edit
            $intendedUrl = session('url.intended');
            if ($intendedUrl) {
                session()->forget('url.intended');
                return redirect($intendedUrl)->with('success', 'profile-updated');
            }
            return Redirect::route('profile.edit')->with('success', 'profile-updated');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return Redirect::route('profile.edit')
                ->with('error', 'Failed to update profile. Please try again.');
        }
    }
    /**
     * Unlink Envato account from user profile with enhanced security.
     *
     * Removes Envato account connection from user profile with proper
     * error handling and security measures.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return RedirectResponse Redirect to profile edit with success/error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Unlink Envato account:
     * POST /profile/unlink-envato
     *
     * // Response: Redirect to profile edit with success message
     * // "Envato account unlinked successfully"
     */
    public function unlinkEnvato(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }
            $user->envato_username = null;
            $user->envato_id = null;
            $user->envato_token = null;
            $user->envato_refresh_token = null;
            $user->envato_token_expires_at = null;
            $user->save();
            DB::commit();
            return Redirect::route('profile.edit')
                ->with('success', 'envato-unlinked');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Envato account unlink failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return Redirect::route('profile.edit')
                ->with('error', 'Failed to unlink Envato account. Please try again.');
        }
    }
    /**
     * Delete the user's account with enhanced security.
     *
     * Deletes user account with password confirmation and proper
     * session cleanup with comprehensive error handling.
     *
     * @param  Request  $request  The HTTP request containing password confirmation
     *
     * @return RedirectResponse Redirect to home page
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Delete account:
     * DELETE /profile
     * {
     *     "password": "current_password"
     * }
     *
     * // Response: Redirect to home page
     * // User logged out and account deleted
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);
            $user = $request->user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }
            Auth::logout();
            $user->delete();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            DB::commit();
            return Redirect::to('/');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return Redirect::route('profile.edit')
                ->with('error', 'Failed to delete account. Please try again.');
        }
    }
    /**
     * Link Envato account to user profile with enhanced security.
     *
     * Links Envato account to user profile with comprehensive validation
     * and proper error handling.
     *
     * @param  Request  $request  The HTTP request containing Envato username
     *
     * @return RedirectResponse Redirect to profile edit with success/error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Link Envato account:
     * POST /profile/link-envato
     * {
     *     "envato_username": "username"
     * }
     *
     * // Response: Redirect to profile edit with success message
     * // "Envato account linked successfully"
     */
    public function linkEnvato(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'envato_username' => 'required|string|max:255',
            ]);
            $user = $request->user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }
            $user->update([
                'envato_username' => $this->sanitizeInput($request->envato_username),
            ]);
            DB::commit();
            return redirect()->route('profile.edit')->with('success', 'envato-linked');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Envato account link failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return redirect()->route('profile.edit')
                ->with('error', 'Failed to link Envato account. Please try again.');
        }
    }
}
