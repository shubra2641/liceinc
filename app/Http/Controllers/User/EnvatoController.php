<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\EnvatoVerificationRequest;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\EnvatoService;
use App\Services\LicenseAutoRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Envato Controller with enhanced security and comprehensive Envato integration.
 *
 * This controller provides comprehensive Envato marketplace integration including
 * purchase verification, OAuth authentication, account linking, and license management
 * with enhanced security measures and error handling.
 *
 * Features:
 * - Enhanced Envato purchase verification and validation
 * - OAuth authentication with Envato marketplace
 * - Account linking and user management
 * - License auto-registration and management
 * - Comprehensive error handling and logging
 * - Input validation and sanitization
 * - Enhanced security measures for Envato operations
 * - Database transaction support for data integrity
 * - Proper error responses for different scenarios
 * - Comprehensive logging for security monitoring
 *
 * @example
 * // Verify Envato purchase
 * POST /envato/verify
 * {
 *     "purchase_code": "ABC123-DEF456-GHI789",
 *     "product_slug": "my-product"
 * }
 */
class EnvatoController extends Controller
{
    /**
     * Verify Envato purchase with enhanced security and comprehensive validation.
     *
     * This method verifies an Envato purchase code and creates or updates
     * the corresponding license with comprehensive validation and error handling.
     *
     * @param  EnvatoVerificationRequest  $request  The current HTTP request instance
     * @param  EnvatoService  $envato  The Envato service instance
     *
     * @return RedirectResponse Redirect response with success or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Verify purchase code
     * $response = $envatoController->verify($request, $envatoService);
     */
    public function verify(EnvatoVerificationRequest $request, EnvatoService $envato): RedirectResponse
    {
        try {
            $this->validateVerifyRequest($request);
            return DB::transaction(function () use ($request, $envato) {
                $data = $request->validate([
                    'purchase_code' => ['required', 'string', 'min:10', 'max:100'],
                    'product_slug' => ['required', 'string', 'min:1', 'max:255'],
                ]);
                // Sanitize input data
                $dataArray = is_array($data) ? $data : [];
                $purchaseCode = $this->sanitizeInput($dataArray['purchase_code'] ?? '');
                $productSlug = $this->sanitizeInput($dataArray['product_slug'] ?? '');
                $sale = $envato->verifyPurchase(is_string($purchaseCode) ? $purchaseCode : '');
                if (! $sale) {
                    Log::warning('Envato purchase verification failed', [
                        'purchase_code' => $this->hashForLogging(is_string($purchaseCode) ? $purchaseCode : ''),
                        'product_slug' => $productSlug,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                    return back()->withErrors(['purchase_code' => 'Could not verify purchase.']);
                }
                $product = Product::where('slug', $productSlug)->firstOrFail();
                $buyerName = data_get($sale, 'buyer');
                $buyerEmail = data_get($sale, 'buyer_email');
                $supportEnd = data_get($sale, 'supported_until');
                $itemId = is_string(data_get($sale, 'item.id')) ? data_get($sale, 'item.id') : '';
                if ($product->envato_item_id && (string)$product->envato_item_id !== $itemId) {
                    Log::warning('Purchase code does not belong to product', [
                        'purchase_code' => $this->hashForLogging(is_string($purchaseCode) ? $purchaseCode : ''),
                        'product_slug' => $productSlug,
                        'product_envato_id' => $product->envato_item_id,
                        'sale_item_id' => $itemId,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                    return back()->withErrors(['purchase_code' => 'Purchase does not belong to this product.']);
                }
                $user = User::firstOrCreate(
                    ['email' => $buyerEmail ?: Str::uuid() . '@example.com'],
                    ['name' => $buyerName ?: 'Envato Buyer'],
                );
                $license = License::updateOrCreate(
                    ['purchase_code' => $purchaseCode],
                    [
                        'product_id' => $product->id,
                        'user_id' => $user->id,
                        'support_expires_at' => $supportEnd
                            ? date(
                                'Y-m-d',
                                strtotime(is_string($supportEnd) ? $supportEnd : '') ?: time()
                            )
                            : null,
                        'status' => 'active',
                    ],
                );
                Log::debug('Envato purchase verified successfully', [
                    'license_id' => $license->id,
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'purchase_code' => $this->hashForLogging(is_string($purchaseCode) ? $purchaseCode : ''),
                ]);
                return back()->with('success', 'Purchase verified and license updated.');
            });
        } catch (Throwable $e) {
            Log::error('Envato purchase verification error', [
                'error' => $e->getMessage(),
                'purchase_code' => $this->hashForLogging(
                    is_string($request->validated('purchase_code', ''))
                        ? $request->validated('purchase_code', '')
                        : ''
                ),
                'product_slug' => $request->validated('product_slug', ''),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['general' => 'An error occurred while verifying the purchase.']);
        }
    }
    /**
     * Redirect to Envato OAuth with enhanced security.
     *
     * This method redirects users to Envato OAuth for authentication
     * with enhanced security measures and error handling.
     *
     * @return RedirectResponse Redirect to Envato OAuth
     *
     * @throws \Exception When OAuth redirect fails
     *
     * @example
     * // Redirect to Envato OAuth
     * $response = $envatoController->redirectToEnvato();
     */
    public function redirectToEnvato(): RedirectResponse
    {
        try {
            // Check if Envato is configured from database
            $settings = \App\Models\Setting::first();
            if (!$settings || !$settings->envato_auth_enabled || !$settings->envato_client_id || !$settings->envato_client_secret) {
                return redirect('/login')->withErrors(['envato' => 'Envato authentication is not enabled or configured. Please contact administrator.']);
            }
            
            // Temporarily set environment variables for Socialite
            config(['services.envato.client_id' => $settings->envato_client_id]);
            config(['services.envato.client_secret' => $settings->envato_client_secret]);
            config(['services.envato.redirect' => url('/auth/envato/callback')]);
            
            return redirect(Socialite::driver('envato')->redirect()->getTargetUrl());
        } catch (Throwable $e) {
            Log::error('Envato OAuth redirect error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect('/login')->withErrors(['envato' => 'Failed to redirect to Envato authentication.']);
        }
    }
    /**
     * Handle Envato OAuth callback with enhanced security and comprehensive validation.
     *
     * This method handles the OAuth callback from Envato, creates or updates
     * user accounts, and manages authentication with comprehensive validation.
     *
     * @param  EnvatoService  $envato  The Envato service instance
     *
     * @return RedirectResponse Redirect response with success or error message
     *
     * @throws \Exception When OAuth callback processing fails
     *
     * @example
     * // Handle OAuth callback
     * $response = $envatoController->handleEnvatoCallback($envatoService);
     */
    public function handleEnvatoCallback(EnvatoService $envato): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($envato) {
                /**
 * @var \Laravel\Socialite\Two\User $envatoUser
*/
                $envatoUser = Socialite::driver('envato')->user();
                $username = $envatoUser->getNickname() ?: $envatoUser->getId();
                if (! $username) {
                    Log::warning('Envato OAuth callback failed: No username found', [
                        'envato_user_id' => $envatoUser->getId(),
                        'envato_user_name' => $envatoUser->getName(),
                        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                    ]);
                    return redirect('/login')->withErrors(['envato' => 'Could not retrieve username from Envato.']);
                }
                // Try to get detailed user info, but don't fail if it doesn't work
                $userInfo = $envato->getOAuthUserInfo($envatoUser->token);
                $userData = [
                    'name' => $envatoUser->getName()
                        ?: data_get($userInfo, 'account.firstname', 'Envato User'),
                    'password' => Hash::make(Str::random(16)), // Random password since OAuth
                    'envato_username' => $envatoUser->getNickname()
                        ?: data_get($userInfo, 'account.username', $username),
                    'envato_id' => $envatoUser->getId(),
                'envato_token' => $envatoUser->token,
                'envato_refresh_token' => $envatoUser->refreshToken,
                    'email_verified_at' => now(),
                ];
                // Check if we have a real email, if not, we need to handle this differently
                $email = $envatoUser->getEmail();
                if (! $email || str_contains($email, '@envato.temp')) {
                    // If we don't have a real email, create a temporary one
                    $email = 'temp_' . $username . '@envato.local';
                }
                $user = User::updateOrCreate(
                    ['email' => $email],
                    $userData,
                );
                // Ensure user has proper role (if it's a new user or doesn't have admin role)
                if (! $user->hasRole('admin')) {
                    // This is a regular user, no need to assign any specific role
                    // The default behavior will work fine
                    Log::debug('User does not have admin role, using default role assignment', [
                        'user_id' => $user->id,
                        'email' => $this->hashForLogging($email),
                    ]);
                }
                Auth::login($user, true);
                // Determine redirect route based on user role (same logic as AuthenticatedSessionController)
                $redirectRoute = $user->hasRole('admin')
                    ? route('admin.dashboard', absolute: false)
                    : route('dashboard', absolute: false);
                // If using temporary email, redirect to profile to update email
                // But preserve the intended redirect for after profile update
                if (str_contains($email, '@envato.local')) {
                    session(['url.intended' => $redirectRoute]);
                    return redirect('/profile')->with('warning', 'Please update your email address in your profile.');
                }
                Log::debug('Envato OAuth callback successful', [
                    'user_id' => $user->id,
                    'envato_username' => $user->envato_username,
                    'email' => $this->hashForLogging($email),
                    'is_temp_email' => str_contains($email, '@envato.local'),
                ]);
                return redirect()->intended($redirectRoute)->with('success', 'Successfully logged in with Envato!');
            });
        } catch (Throwable $e) {
            Log::error('Envato OAuth callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect('/login')->withErrors(['envato' => 'Failed to process Envato authentication.']);
        }
    }
    /**
     * Link Envato account with enhanced security and comprehensive validation.
     *
     * This method links an existing user account with an Envato account
     * using OAuth with comprehensive validation and error handling.
     *
     * @param  EnvatoVerificationRequest  $request  The current HTTP request instance
     * @param  EnvatoService  $envato  The Envato service instance
     *
     * @return RedirectResponse Redirect response with success or error message
     *
     * @throws \Exception When account linking fails
     *
     * @example
     * // Link Envato account
     * $response = $envatoController->linkEnvatoAccount($request, $envatoService);
     */
    public function linkEnvatoAccount(Request $request, EnvatoService $envato): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request, $envato) {
                /**
 * @var \Laravel\Socialite\Two\User $envatoUser
*/
                $envatoUser = Socialite::driver('envato')->user();
                $userInfo = $envato->getOAuthUserInfo($envatoUser->token);
                if ($userInfo === null) {
                    Log::warning('Envato account linking failed: Could not retrieve user info', [
                        'envato_user_id' => $envatoUser->getId(),
                        'envato_username' => $envatoUser->getNickname(),
                        'user_id' => auth()->id(),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                    return back()->withErrors(['envato' => 'Could not retrieve user information from Envato.']);
                }
                $user = auth()->user();
                if ($user) {
                    $user->update([
                    'envato_username' => $envatoUser->getNickname() ?: data_get($userInfo, 'account.username'),
                    'envato_id' => $envatoUser->getId(),
                    'envato_token' => $envatoUser->token,
                    'envato_refresh_token' => $envatoUser->refreshToken,
                    ]);
                    Log::debug('Envato account linked successfully', [
                    'user_id' => auth()->id(),
                    'envato_username' => $envatoUser->getNickname(),
                    'envato_id' => $envatoUser->getId(),
                    ]);
                    return back()->with('success', 'Envato account linked successfully!');
                }
            });
        } catch (Throwable $e) {
            Log::error('Envato account linking error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['envato' => 'Failed to link Envato account.']);
        }
    }
    /**
     * Verify user purchase with enhanced security and comprehensive validation.
     *
     * This method verifies a user's purchase using the license auto-registration
     * service with comprehensive validation and error handling.
     *
     * @param  EnvatoVerificationRequest  $request  The current HTTP request instance
     * @param  LicenseAutoRegistrationService  $licenseService  The license service instance
     *
     * @return JsonResponse JSON response with verification results
     *
     * @throws \Exception When purchase verification fails
     *
     * @example
     * // Verify user purchase
     * $response = $envatoController->verifyUserPurchase($request, $licenseService);
     */
    public function verifyUserPurchase(Request $request, LicenseAutoRegistrationService $licenseService): JsonResponse
    {
        try {
            $this->validateVerifyUserPurchaseRequest($request);
            return DB::transaction(function () use ($request, $licenseService) {
                $request->validate([
                    'purchase_code' => 'required|string|min:10|max:100',
                    'product_id' => 'required|exists:products, id',
                ]);
                // Sanitize input data
                $purchaseCode = $this->sanitizeInput($request->validated('purchase_code'));
                $productId = is_numeric($request->validated('product_id')) ? (int)$request->validated('product_id') : 0;
                // Use the license auto-registration service
                $registrationResult = $licenseService->autoRegisterLicense(
                    is_string($purchaseCode) ? $purchaseCode : '',
                    $productId
                );
                if (! $registrationResult['success']) {
                    Log::warning('User purchase verification failed', [
                        'purchase_code' => $this->hashForLogging(is_string($purchaseCode) ? $purchaseCode : ''),
                        'product_id' => $productId,
                        'user_id' => auth()->id(),
                        'error_message' => $registrationResult['message'],
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                    return response()->json([
                        'valid' => false,
                        'message' => $registrationResult['message'],
                    ]);
                }
                $license = $registrationResult['license'];
                Log::debug('User purchase verified successfully', [
                    'license_id' => is_object($license) && isset($license->id) ? $license->id : 'N/A',
                    'purchase_code' => $this->hashForLogging(is_string($purchaseCode) ? $purchaseCode : ''),
                    'product_id' => $productId,
                    'user_id' => auth()->id(),
                ]);
                return response()->json([
                    'valid' => true,
                    'message' => $registrationResult['message'],
                    'license' => $license,
                ]);
            });
        } catch (Throwable $e) {
            Log::error('User purchase verification error', [
                'error' => $e->getMessage(),
                'purchase_code' => $this->hashForLogging(
                    is_string($request->validated('purchase_code', ''))
                        ? $request->validated('purchase_code', '')
                        : ''
                ),
                'product_id' => $request->validated('product_id', ''),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'valid' => false,
                'message' => 'An error occurred while verifying the purchase.',
            ], 500);
        }
    }
    /**
     * Validate verify request parameters.
     *
     * @param  EnvatoVerificationRequest  $request  The current HTTP request instance
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateVerifyRequest(Request $request): void
    {
        if (! $request->has(['purchase_code', 'product_slug'])) {
            throw new \InvalidArgumentException('Missing required parameters: purchase_code, product_slug');
        }
    }
    /**
     * Validate verify user purchase request parameters.
     *
     * @param  EnvatoVerificationRequest  $request  The current HTTP request instance
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateVerifyUserPurchaseRequest(Request $request): void
    {
        if (! $request->has(['purchase_code', 'product_id'])) {
            throw new \InvalidArgumentException('Missing required parameters: purchase_code, product_id');
        }
    }
}
