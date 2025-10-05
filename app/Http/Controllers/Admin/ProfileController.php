<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileAdvancedRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
/**
 * Profile Controller with enhanced security.
 *
 * This controller handles user profile management functionality including
 * profile updates, password changes, and Envato account integration.
 *
 * Features:
 * - Profile information updates with validation
 * - Password change functionality with security
 * - Envato account connection and disconnection
 * - Email verification handling
 * - Comprehensive error handling with database transactions
 * - Enhanced security measures (input validation, password security)
 * - Proper logging for errors and warnings only
 */
class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     *
     * Displays the user profile editing form with current user data.
     *
     * @param  Request  $request  The HTTP request containing user information
     *
     * @return View The profile edit form view
     *
     * @example
     * // Access the profile edit form:
     * GET /admin/profile/edit
     *
     * // Returns view with:
     * // - Current user profile data
     * // - Profile editing form
     * // - Password change form
     * // - Envato connection status
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        // Get Envato integration settings
        $settings = \App\Models\Setting::first();
        $hasApiConfig = $settings && $settings->envato_personal_token;
        return view('admin.profile.edit', compact('user', 'hasApiConfig'));
    }
    /**
     * Update the user's profile information.
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
     * PUT /admin/profile
     * {
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "firstname": "John",
     *     "lastname": "Doe",
     *     "companyname": "Acme Corp"
     * }
     *
     * // Response: Redirect to profile edit with success message
     * // "Profile updated successfully"
     */
    public function update(ProfileAdvancedRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            $validated = $request->validated();
            // Remove password fields from fillable data (password updates are handled separately)
            unset($validated['current_password'], $validated['password'], $validated['password_confirmation']);
            $user->fill($validated);
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
                $user->save();
                $user->sendEmailVerificationNotification();
                DB::commit();
                return Redirect::route('verification.notice')
                    ->with('success', 'Please verify your email address. A verification link has been sent to your '
                        .'email.');
            }
            $user->save();
            DB::commit();
            return Redirect::route('admin.profile.edit')->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
            ]);
            return Redirect::route('admin.profile.edit')
                ->with('error', 'Failed to update profile. Please try again.');
        }
    }
    /**
     * Update the user's password.
     *
     * Updates user password with comprehensive validation including
     * current password verification and strong password requirements.
     *
     * @param  UpdatePasswordRequest  $request  The validated request containing password data
     *
     * @return RedirectResponse Redirect to profile edit with success/error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Update password:
     * POST /admin/profile/password
     * {
     *     "current_password": "oldpassword123",
     *     "password": "NewSecurePassword123!",
     *     "password_confirmation": "NewSecurePassword123!"
     * }
     *
     * // Response: Redirect to profile edit with success message
     * // "Password updated successfully"
     */
    public function updatePassword(ProfileAdvancedRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            $validated = $request->validated();
            $user->password = Hash::make($validated['password']);
            $user->save();
            DB::commit();
            return Redirect::route('admin.profile.edit')->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
            ]);
            return Redirect::route('admin.profile.edit')
                ->with('error', 'Failed to update password. Please try again.');
        }
    }
    /**
     * Connect user's Envato account.
     *
     * Connects the user's profile to their Envato account using the
     * configured API token and retrieves account information.
     *
     * @param  Request  $request  The HTTP request containing user information
     *
     * @return RedirectResponse Redirect to profile edit with success/error message
     *
     * @throws \Exception When API connection fails
     *
     * @example
     * // Connect Envato account:
     * POST /admin/profile/connect-envato
     *
     * // Response: Redirect to profile edit with success message
     * // "Successfully connected to Envato account: username"
     */
    public function connectEnvato(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            // Get Envato settings from database
            $settings = \App\Models\Setting::first();
            if (! $settings || ! $settings->envato_personal_token) {
                DB::rollBack();
                return Redirect::route('admin.profile.edit')
                    ->with('error', 'Envato API is not configured. Please configure it in Settings first.');
            }
            // Test the API connection and get user info
            $response = \Illuminate\Support\Facades\Http::withToken($settings->envato_personal_token)
                ->acceptJson()
                ->timeout(30)
                ->get('https://api.envato.com/v1/market/private/user/account.json');
            if ($response->successful()) {
                $data = $response->json();
                // Update user with Envato info
                $user->envato_username = $data['username'] ?? null;
                $user->envato_id = $data['id'] ?? null;
                $user->save();
                DB::commit();
                return Redirect::route('admin.profile.edit')
                    ->with('success', 'Successfully connected to Envato account: '.($data['username'] ?? 'Unknown'));
            } else {
                DB::rollBack();
                return Redirect::route('admin.profile.edit')
                    ->with('error', 'Failed to connect to Envato. Please check your API token.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Envato connection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
            ]);
            return Redirect::route('admin.profile.edit')
                ->with('error', 'Failed to connect to Envato: '.$e->getMessage());
        }
    }
    /**
     * Disconnect user's Envato account.
     *
     * Disconnects the user's profile from their Envato account by
     * clearing all Envato-related data from the user record.
     *
     * @param  Request  $request  The HTTP request containing user information
     *
     * @return RedirectResponse Redirect to profile edit with success/error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Disconnect Envato account:
     * POST /admin/profile/disconnect-envato
     *
     * // Response: Redirect to profile edit with success message
     * // "Successfully disconnected from Envato account"
     */
    public function disconnectEnvato(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            $user->envato_username = null;
            $user->envato_id = null;
            $user->envato_token = null;
            $user->envato_refresh_token = null;
            $user->envato_token_expires_at = null;
            $user->save();
            DB::commit();
            return Redirect::route('admin.profile.edit')
                ->with('success', 'Successfully disconnected from Envato account.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Envato disconnection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id,
            ]);
            return Redirect::route('admin.profile.edit')
                ->with('error', 'Failed to disconnect from Envato account. Please try again.');
        }
    }
}
