<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * User Management Service with enhanced security.
 *
 * This service handles user creation, authentication, and account linking
 * with comprehensive error handling and security measures.
 *
 * Features:
 * - User creation and authentication
 * - OAuth account linking
 * - User data validation and sanitization
 * - Enhanced security measures
 * - Comprehensive error handling
 */
class UserManagementService
{
    /**
     * Handle Envato OAuth callback and authenticate user.
     */
    public function handleEnvatoCallback(): \Illuminate\Http\RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $envatoUser = Socialite::driver('envato')->user();
            $username = $this->extractUsername($envatoUser);
            
            if (!$username) {
                return $this->handleUsernameNotFound($envatoUser);
            }
            
            $userInfo = app(EnvatoService::class)->getOAuthUserInfo($envatoUser->token);
            $userData = $this->prepareUserData($envatoUser, $userInfo, $username);
            $email = $this->prepareUserEmail($envatoUser, $username);
            
            $user = User::updateOrCreate(['email' => $email], $userData);
            Auth::login($user, true);
            
            DB::commit();
            
            return $this->handleSuccessfulLogin($email);
            
        } catch (\Exception $e) {
            return $this->handleOAuthError($e);
        }
    }

    /**
     * Link existing user account with Envato OAuth.
     */
    public function linkEnvatoAccount(): \Illuminate\Http\RedirectResponse
    {
        try {
            if (auth()->guest()) {
                return redirect('/login')->withErrors(['envato' => 'Please log in to link your Envato account.']);
            }
            
            DB::beginTransaction();
            
            $envatoUser = Socialite::driver('envato')->user();
            $userInfo = app(EnvatoService::class)->getOAuthUserInfo($envatoUser->token);
            
            if (!$userInfo) {
                return $this->handleUserInfoRetrievalFailure($envatoUser);
            }
            
            if ($this->isAccountAlreadyLinked($envatoUser->getId())) {
                return $this->handleAccountAlreadyLinked($envatoUser);
            }
            
            $this->updateUserWithEnvatoData($envatoUser, $userInfo);
            
            DB::commit();
            return back()->with('success', 'Envato account linked successfully! You can now verify your purchases.');
            
        } catch (\Exception $e) {
            return $this->handleLinkingError($e);
        }
    }

    /**
     * Redirect user to Envato OAuth authorization page.
     */
    public function redirectToEnvato(): \Illuminate\Http\RedirectResponse
    {
        try {
            return redirect(Socialite::driver('envato')->redirect()->getTargetUrl());
        } catch (\Exception $e) {
            Log::error('Failed to redirect to Envato OAuth', [
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect('/login')->withErrors(['envato' => 'Unable to connect to Envato. Please try again.']);
        }
    }

    /**
     * Extract username from Envato user data.
     */
    private function extractUsername($envatoUser): ?string
    {
        return $envatoUser->getNickname() ?: $envatoUser->getId();
    }

    /**
     * Handle case when username is not found.
     */
    private function handleUsernameNotFound($envatoUser): \Illuminate\Http\RedirectResponse
    {
        DB::rollBack();
        Log::warning('No username found in Envato OAuth response', [
            'envato_id' => $envatoUser->getId(),
            'ip' => request()->ip(),
        ]);
        return redirect('/login')->withErrors([
            'envato' => 'Could not retrieve username from Envato. Please try again.',
        ]);
    }

    /**
     * Prepare user data for creation/update.
     */
    private function prepareUserData($envatoUser, $userInfo, string $username): array
    {
        return [
            'name' => $envatoUser->getName() ?: data_get($userInfo, 'account.firstname', 'Envato User'),
            'password' => Hash::make(Str::random(32)),
            'envato_username' => $envatoUser->getNickname() ?: data_get($userInfo, 'account.username', $username),
            'envato_id' => $envatoUser->getId(),
            'envato_token' => $envatoUser->token,
            'envato_refresh_token' => $envatoUser->refreshToken,
            'email_verified_at' => now(),
            'last_login_at' => now(),
        ];
    }

    /**
     * Prepare user email with fallback.
     */
    private function prepareUserEmail($envatoUser, string $username): string
    {
        $email = $envatoUser->getEmail();
        if (!$email || str_contains($email, '@envato.temp')) {
            $email = 'temp_' . $username . '@envato.local';
        }
        return $email;
    }

    /**
     * Handle successful login with appropriate redirect.
     */
    private function handleSuccessfulLogin(string $email): \Illuminate\Http\RedirectResponse
    {
        if (str_contains($email, '@envato.local')) {
            return redirect('/profile')->with(
                'warning',
                'Please update your email address in your profile to complete your account setup.'
            );
        }
        return redirect('/dashboard')->with('success', 'Successfully logged in with Envato!');
    }

    /**
     * Handle OAuth errors.
     */
    private function handleOAuthError(\Exception $e): \Illuminate\Http\RedirectResponse
    {
        DB::rollBack();
        Log::error('Envato OAuth callback failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'ip' => request()->ip(),
        ]);
        return redirect('/login')->withErrors(['envato' => 'Authentication failed. Please try again.']);
    }

    /**
     * Handle user info retrieval failure.
     */
    private function handleUserInfoRetrievalFailure($envatoUser): \Illuminate\Http\RedirectResponse
    {
        DB::rollBack();
        Log::warning('Failed to retrieve user info during account linking', [
            'user_id' => auth()->id(),
            'envato_id' => $envatoUser->getId(),
            'ip' => request()->ip(),
        ]);
        return back()->withErrors([
            'envato' => 'Could not retrieve user information from Envato. Please try again.',
        ]);
    }

    /**
     * Check if Envato account is already linked to another user.
     */
    private function isAccountAlreadyLinked(string $envatoId): bool
    {
        return User::where('envato_id', $envatoId)
            ->where('id', '!=', auth()->id())
            ->exists();
    }

    /**
     * Handle case when account is already linked.
     */
    private function handleAccountAlreadyLinked($envatoUser): \Illuminate\Http\RedirectResponse
    {
        DB::rollBack();
        Log::warning('Attempted to link already linked Envato account', [
            'user_id' => auth()->id(),
            'envato_id' => $envatoUser->getId(),
            'ip' => request()->ip(),
        ]);
        return back()->withErrors(['envato' => 'This Envato account is already linked to another user.']);
    }

    /**
     * Update user with Envato data.
     */
    private function updateUserWithEnvatoData($envatoUser, $userInfo): void
    {
        $user = auth()->user();
        if ($user) {
            $user->update([
                'envato_username' => $envatoUser->getNickname() ?: data_get($userInfo, 'account.username'),
                'envato_id' => $envatoUser->getId(),
                'envato_token' => $envatoUser->token,
                'envato_refresh_token' => $envatoUser->refreshToken,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Handle account linking errors.
     */
    private function handleLinkingError(\Exception $e): \Illuminate\Http\RedirectResponse
    {
        DB::rollBack();
        Log::error('Failed to link Envato account', [
            'user_id' => auth()->id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'ip' => request()->ip(),
        ]);
        return back()->withErrors(['envato' => 'Failed to link Envato account. Please try again.']);
    }
}
