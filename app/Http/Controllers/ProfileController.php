<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Unified Profile Controller.
 *
 * Handles profile management for both admin and user roles
 * with simple, clean code following PSR-12 standards.
 */
class ProfileController extends Controller
{
    /**
     * Show profile edit form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->is_admin ?? false;

        return view($isAdmin ? 'admin.profile.edit' : 'profile.index', [
            'user' => $user,
            'hasApiConfig' => $this->hasEnvatoConfig(),
        ]);
    }

    /**
     * Update profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $user->fill($request->validated());

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
                $user->save();
                $user->sendEmailVerificationNotification();

                DB::commit();
                return Redirect::route('verification.notice')
                    ->with('success', 'Please verify your email address.');
            }

            $user->save();
            DB::commit();

            $route = $user->is_admin ? 'admin.profile.edit' : 'profile.edit';
            return Redirect::route($route)->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile update failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            $route = $request->user()->is_admin ? 'admin.profile.edit' : 'profile.edit';
            return Redirect::route($route)->with('error', 'Failed to update profile.');
        }
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|current_password',
                'password' => 'required|string|min:8|confirmed',
            ]);

            DB::beginTransaction();

            $user = $request->user();
            $user->password = Hash::make($request->password);
            $user->save();

            DB::commit();

            $route = $user->is_admin ? 'admin.profile.edit' : 'profile.edit';
            return Redirect::route($route)->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password update failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            $route = $request->user()->is_admin ? 'admin.profile.edit' : 'profile.edit';
            return Redirect::route($route)->with('error', 'Failed to update password.');
        }
    }

    /**
     * Connect Envato account.
     */
    public function connectEnvato(Request $request): RedirectResponse
    {
        try {
            if (!$this->hasEnvatoConfig()) {
                $route = $request->user()->is_admin ? 'admin.profile.edit' : 'profile.edit';
                return Redirect::route($route)->with('error', 'Envato API not configured.');
            }

            DB::beginTransaction();

            $user = $request->user();
            $settings = \App\Models\Setting::first();

            $response = \Illuminate\Support\Facades\Http::withToken($settings->envato_personal_token)
                ->acceptJson()
                ->timeout(30)
                ->get('https://api.envato.com/v1/market/private/user/account.json');

            if ($response->successful()) {
                $data = $response->json();
                $user->envato_username = $data['username'] ?? null;
                $user->envato_id = $data['id'] ?? null;
                $user->save();

                DB::commit();

                $route = $user->is_admin ? 'admin.profile.edit' : 'profile.edit';
                return Redirect::route($route)->with('success', 'Envato account connected successfully.');
            }

            DB::rollBack();
            $route = $user->is_admin ? 'admin.profile.edit' : 'profile.edit';
            return Redirect::route($route)->with('error', 'Failed to connect to Envato.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Envato connection failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            $route = $request->user()->is_admin ? 'admin.profile.edit' : 'profile.edit';
            return Redirect::route($route)->with('error', 'Failed to connect to Envato.');
        }
    }

    /**
     * Disconnect Envato account.
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

            $route = $user->is_admin ? 'admin.profile.edit' : 'profile.edit';
            return Redirect::route($route)->with('success', 'Envato account disconnected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Envato disconnection failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            $route = $request->user()->is_admin ? 'admin.profile.edit' : 'profile.edit';
            return Redirect::route($route)->with('error', 'Failed to disconnect from Envato.');
        }
    }

    /**
     * Delete user account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'password' => 'required|current_password',
            ]);

            DB::beginTransaction();

            $user = $request->user();
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
                'user_id' => $request->user()?->id,
            ]);

            $route = $request->user()->is_admin ? 'admin.profile.edit' : 'profile.edit';
            return Redirect::route($route)->with('error', 'Failed to delete account.');
        }
    }

    /**
     * Check if Envato API is configured.
     */
    private function hasEnvatoConfig(): bool
    {
        $settings = \App\Models\Setting::first();
        return $settings && $settings->envato_personal_token;
    }
}
