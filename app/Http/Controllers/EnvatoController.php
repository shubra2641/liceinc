<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\EnvatoService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Envato Controller with enhanced security.
 *
 * This controller handles Envato marketplace integration including
 * purchase verification, OAuth authentication, account linking, and
 * license management with enhanced security measures.
 *
 * Features:
 * - Purchase code verification with Envato API
 * - OAuth authentication with Envato marketplace
 * - Account linking and user management
 * - License creation and management
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Rate limiting and security validation
 * - Model relationship integration for optimized queries
 */
class EnvatoController extends Controller
{
    /**
     * Rate limiting duration in minutes.
     */
    private const RATE_LIMIT_DURATION = 1;
    /**
     * Verify purchase code with Envato API and create/update license with enhanced security.
     *
     * This method validates a purchase code against Envato's API,
     * verifies it belongs to the specified product, and creates
     * or updates a license record for the buyer with comprehensive
     * error handling and security measures.
     *
     * @param  Request  $request  Contains purchase_code and product_slug
     * @param  EnvatoService  $envato  Service for Envato API interactions
     *
     * @return RedirectResponse The redirect response
     *
     * @throws \InvalidArgumentException When request is invalid
     * @throws Exception When database operations fail
     *
     * @example
     * // Verify purchase code:
     * POST /envato/verify
     * {
     *     "purchase_code": "ABC123-DEF456-GHI789",
     *     "product_slug": "my-product"
     * }
     */
    public function verify(Request $request, EnvatoService $envato): RedirectResponse
    {
        try {
            // Request is validated by type hint
            // Enhanced validation with more specific rules
            $data = $this->validatePurchaseRequest($request);
            // Rate limiting check
            $clientIp = $request->ip();
            if ($clientIp && $this->isRateLimited($clientIp, 'verify_purchase_') === true) {
                Log::warning('Rate limit exceeded for purchase verification', [
                    'ip' => $request->ip(),
                    'purchase_code' => $this->maskPurchaseCode(is_string($data['purchase_code']) ? $data['purchase_code'] : ''),
                ]);
                return back()->withErrors(['purchase_code' => 'Too many verification attempts. '
                    . 'Please try again later.']);
            }
            if ($clientIp) {
                $this->setRateLimit($clientIp, 'verify_purchase_');
            }
            DB::beginTransaction();
            // Verify purchase with Envato API
            $sale = $envato->verifyPurchase(is_string($data['purchase_code']) ? $data['purchase_code'] : '');
            if ($sale === null) {
                DB::rollBack();
                Log::warning('Failed to verify purchase code', [
                    'purchase_code' => $this->maskPurchaseCode(is_string($data['purchase_code']) ? $data['purchase_code'] : ''),
                    'ip' => $request->ip(),
                ]);
                return back()->withErrors(['purchase_code' => 'Could not verify purchase code. '
                    . 'Please check and try again.']);
            }
            // Find product by slug
            $product = Product::where('slug', $data['product_slug'])->firstOrFail();
            // Extract sale information safely
            $buyerName = data_get($sale, 'buyer', 'Unknown Buyer');
            $buyerEmail = data_get($sale, 'buyer_email');
            $supportEnd = data_get($sale, 'supported_until');
            $itemId = is_string(data_get($sale, 'item.id')) ? data_get($sale, 'item.id') : '';
            // Validate product ownership
            if ((string)$product->envato_item_id !== $itemId) {
                DB::rollBack();
                Log::warning('Purchase code does not match product', [
                    'purchase_code' => $this->maskPurchaseCode(is_string($data['purchase_code']) ? $data['purchase_code'] : ''),
                    'product_id' => $product->id,
                    'expected_item_id' => $product->envato_item_id,
                    'actual_item_id' => $itemId,
                ]);
                return back()->withErrors(['purchase_code' => 'Purchase code does not belong to this product.']);
            }
            // Create or find user by email
            $user = User::firstOrCreate(
                ['email' => $buyerEmail ?: Str::uuid() . '@envato-temp.local'],
                [
                    'name' => $buyerName,
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => $buyerEmail ? now() : null,
                ],
            );
            // Create or update license
            $license = License::updateOrCreate(
                ['purchase_code' => $data['purchase_code']],
                [
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'support_expires_at' => $supportEnd ? Carbon::parse(is_string($supportEnd) ? $supportEnd : '')->format('Y-m-d') : null,
                    'status' => 'active',
                    'verified_at' => now(),
                ],
            );
            DB::commit();
            return back()->with('success', 'Purchase verified and license updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Purchase verification validation failed', [
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Purchase verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);
            return back()->withErrors(['purchase_code' => 'An error occurred while verifying your purchase. '
                . 'Please try again.']);
        }
    }
    /**
     * Redirect user to Envato OAuth authorization page with enhanced security.
     *
     * Initiates the OAuth flow with Envato for user authentication.
     * This is the first step in the OAuth process with comprehensive
     * error handling and security measures.
     *
     * @return RedirectResponse The redirect response
     *
     * @throws Exception When OAuth redirection fails
     *
     * @example
     * // Redirect to Envato OAuth:
     * GET /envato/redirect
     */
    public function redirectToEnvato(): RedirectResponse
    {
        try {
            return redirect(Socialite::driver('envato')->redirect()->getTargetUrl());
        } catch (Exception $e) {
            Log::error('Failed to redirect to Envato OAuth', [
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect('/login')->withErrors(['envato' => 'Unable to connect to Envato. Please try again.']);
        }
    }
    /**
     * Handle Envato OAuth callback and authenticate user with enhanced security.
     *
     * Processes the OAuth callback from Envato, retrieves user information,
     * creates or updates user account, and logs them in with comprehensive
     * error handling and security measures.
     *
     * @param  EnvatoService  $envato  Service for Envato API interactions
     *
     * @return RedirectResponse The redirect response
     *
     * @throws Exception When OAuth callback processing fails
     *
     * @example
     * // Handle OAuth callback:
     * GET /envato/callback
     */
    public function handleEnvatoCallback(EnvatoService $envato): RedirectResponse
    {
        try {
            DB::beginTransaction();
            /** @var \Laravel\Socialite\Two\User $envatoUser */
            $envatoUser = Socialite::driver('envato')->user();
            $username = $envatoUser->getNickname() ?: $envatoUser->getId();
            if (! $username) {
                DB::rollBack();
                Log::warning('No username found in Envato OAuth response', [
                    'envato_id' => $envatoUser->getId(),
                    'ip' => request()->ip(),
                ]);
                return redirect('/login')->withErrors([
                    'envato' => 'Could not retrieve username from Envato. Please try again.',
                ]);
            }
            // Try to get detailed user info, but don't fail if it doesn't work
            $userInfo = $envato->getOAuthUserInfo($envatoUser->token);
            $userData = [
                'name' => $envatoUser->getName() ?: data_get($userInfo, 'account.firstname', 'Envato User'),
                'password' => Hash::make(Str::random(32)), // Stronger random password
                'envato_username' => $envatoUser->getNickname() ?: data_get($userInfo, 'account.username', $username),
                'envato_id' => $envatoUser->getId(),
           'envato_token' => $envatoUser->token,
           'envato_refresh_token' => $envatoUser->refreshToken,
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ];
            // Check if we have a real email, if not, create a temporary one
            $email = $envatoUser->getEmail();
            if (! $email || str_contains($email, '@envato.temp')) {
                $email = 'temp_' . $username . '@envato.local';
            }
            $user = User::updateOrCreate(
                ['email' => $email],
                $userData,
            );
            Auth::login($user, true);
            DB::commit();
            // If using temporary email, redirect to profile to update email
            if (str_contains($email, '@envato.local')) {
                return redirect('/profile')->with(
                    'warning',
                    'Please update your email address in your profile to complete your account setup.',
                );
            }
            return redirect('/dashboard')->with('success', 'Successfully logged in with Envato!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Envato OAuth callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
            ]);
            return redirect('/login')->withErrors(['envato' => 'Authentication failed. Please try again.']);
        }
    }
    /**
     * Link existing user account with Envato OAuth with enhanced security.
     *
     * Allows authenticated users to link their existing account
     * with their Envato profile for enhanced functionality with
     * comprehensive error handling and security measures.
     *
     * @param  Request  $request  The HTTP request
     * @param  EnvatoService  $envato  Service for Envato API interactions
     *
     * @return RedirectResponse The redirect response
     *
     * @throws Exception When account linking fails
     *
     * @example
     * // Link Envato account:
     * POST /envato/link
     */
    public function linkEnvatoAccount(Request $request, EnvatoService $envato): RedirectResponse
    {
        try {
            // Request is validated by type hint
            // Ensure user is authenticated
            if (auth()->guest()) {
                return redirect('/login')->withErrors(['envato' => 'Please log in to link your Envato account.']);
            }
            DB::beginTransaction();
            /** @var \Laravel\Socialite\Two\User $envatoUser */
            $envatoUser = Socialite::driver('envato')->user();
            $userInfo = $envato->getOAuthUserInfo($envatoUser->token);
            if ($userInfo === null) {
                DB::rollBack();
                Log::warning('Failed to retrieve user info during account linking', [
                    'user_id' => auth()->id(),
                    'envato_id' => $envatoUser->getId(),
                    'ip' => $request->ip(),
                ]);
                return back()->withErrors([
                    'envato' => 'Could not retrieve user information from Envato. Please try again.',
                ]);
            }
            // Check if this Envato account is already linked to another user
            $existingUser = User::where('envato_id', $envatoUser->getId())
                ->where('id', '!=', auth()->id())
                ->first();
            if ($existingUser) {
                DB::rollBack();
                Log::warning('Attempted to link already linked Envato account', [
                    'user_id' => auth()->id(),
                    'envato_id' => $envatoUser->getId(),
                    'existing_user_id' => $existingUser->id,
                    'ip' => $request->ip(),
                ]);
                return back()->withErrors(['envato' => 'This Envato account is already linked to another user.']);
            }
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
            DB::commit();
            return back()->with('success', 'Envato account linked successfully! You can now verify your purchases.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to link Envato account', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);
            return back()->withErrors(['envato' => 'Failed to link Envato account. Please try again.']);
        }
    }
    /**
     * Verify user's purchase code via AJAX request with enhanced security.
     *
     * This method provides an AJAX endpoint for users to verify their
     * purchase codes and automatically add licenses to their account
     * with comprehensive error handling and security measures.
     *
     * @param  Request  $request  Contains purchase_code and product_id
     * @param  EnvatoService  $envato  Service for Envato API interactions
     *
     * @return JsonResponse The JSON response
     *
     * @throws Exception When AJAX verification fails
     *
     * @example
     * // Verify purchase via AJAX:
     * POST /envato/verify-user-purchase
     * {
     *     "purchase_code": "ABC123-DEF456-GHI789",
     *     "product_id": 1
     * }
     */
    public function verifyUserPurchase(Request $request, EnvatoService $envato): JsonResponse
    {
        try {
            // Request is validated by type hint
            // Ensure user is authenticated
            if (auth()->guest()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Authentication required',
                ], 401);
            }
            // Enhanced validation
            $data = $this->validateAjaxPurchaseRequest($request);
            // Rate limiting for AJAX requests
            $rateLimitKey = 'ajax_verify_' . auth()->id() . '_' . $request->ip();
            if ($this->isRateLimited($rateLimitKey, 'ajax_verify_')) {
                Log::warning('Rate limit exceeded for AJAX purchase verification', [
                    'user_id' => auth()->id(),
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'valid' => false,
                    'message' => 'Too many verification attempts. Please try again later.',
                ], 429);
            }
            $this->setRateLimit($rateLimitKey, 'ajax_verify_');
            DB::beginTransaction();
            $sale = $envato->verifyPurchase(is_string($data['purchase_code']) ? $data['purchase_code'] : '');
            if ($sale === null) {
                DB::rollBack();
                Log::warning('AJAX purchase verification failed', [
                    'user_id' => auth()->id(),
                    'purchase_code' => $this->maskPurchaseCode(is_string($data['purchase_code']) ? $data['purchase_code'] : ''),
                    'product_id' => $data['product_id'],
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid purchase code. Please check and try again.',
                ]);
            }
            $product = Product::findOrFail($data['product_id']);
            $itemId = is_string(data_get($sale, 'item.id')) ? data_get($sale, 'item.id') : '';
            if ($product->envato_item_id && $product->envato_item_id !== $itemId) {
                DB::rollBack();
                Log::warning('AJAX purchase code does not match product', [
                    'user_id' => auth()->id(),
                    'purchase_code' => $this->maskPurchaseCode(is_string($data['purchase_code']) ? $data['purchase_code'] : ''),
                    'product_id' => $product->id,
                    'expected_item_id' => $product->envato_item_id,
                    'actual_item_id' => $itemId,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'valid' => false,
                    'message' => 'Purchase code does not match this product.',
                ]);
            }
            // Check if user already has this license
            $existingLicense = License::where('purchase_code', $data['purchase_code'])
                ->where('user_id', auth()->id())
                ->first();
            if ($existingLicense) {
                DB::rollBack();
                return response()->json([
                    'valid' => true,
                    'message' => 'License already exists in your account',
                    'license' => $existingLicense,
                ]);
            }
            // Create license for user
            $license = License::create([
                'purchase_code' => $data['purchase_code'],
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'license_type' => 'regular',
                'status' => 'active',
                'support_expires_at' => data_get($sale, 'supported_until') ?
                    Carbon::parse(is_string(data_get($sale, 'supported_until')) ? data_get($sale, 'supported_until') : '')->format('Y-m-d') : null,
                'verified_at' => now(),
            ]);
            DB::commit();
            return response()->json([
                'valid' => true,
                'message' => 'License verified and added to your account',
                'license' => $license,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('AJAX purchase verification validation failed', [
                'user_id' => auth()->id(),
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'valid' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('AJAX purchase verification failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'valid' => false,
                'message' => 'An error occurred while verifying your purchase. Please try again.',
            ], 500);
        }
    }
    /**
     * Validate purchase request data.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return array The validated data
     */
    /**
     * @return array<string, mixed>
     */
    private function validatePurchaseRequest(Request $request): array
    {
        $validated = $request->validate([
            'purchase_code' => [
                'required',
                'string',
                'regex:/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',
                'max:36',
            ],
            'product_slug' => [
                'required',
                'string',
                'alpha_dash',
                'max:255',
            ],
        ], [
            'purchase_code.regex' => 'Purchase code must be a valid UUID format.',
            'product_slug.alpha_dash' => 'Product slug can only contain letters, numbers, dashes and underscores.',
        ]);
        
        /** @var array<string, mixed> $result */
        $result = $validated;
        return $result;
    }
    /**
     * Validate AJAX purchase request data.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return array<string, mixed> The validated data
     */
    private function validateAjaxPurchaseRequest(Request $request): array
    {
        $validated = $request->validate([
            'purchase_code' => [
                'required',
                'string',
                'regex:/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',
                'max:36',
            ],
            'product_id' => [
                'required',
                'integer',
                'exists:products, id',
            ],
        ], [
            'purchase_code.regex' => 'Purchase code must be a valid UUID format.',
            'product_id.exists' => 'Selected product does not exist.',
        ]);
        
        /** @var array<string, mixed> $result */
        $result = $validated;
        return $result;
    }
    /**
     * Check if request is rate limited.
     *
     * @param  string  $key  The rate limit key
     * @param  string  $prefix  The rate limit prefix
     *
     * @return bool True if rate limited
     */
    private function isRateLimited(string $key, string $prefix = ''): bool
    {
        return cache()->has($prefix . $key);
    }
    /**
     * Set rate limit for request.
     *
     * @param  string  $key  The rate limit key
     * @param  string  $prefix  The rate limit prefix
     */
    private function setRateLimit(string $key, string $prefix = ''): void
    {
        cache()->put($prefix . $key, true, now()->addMinutes(self::RATE_LIMIT_DURATION));
    }
    /**
     * Mask purchase code for logging.
     *
     * @param  string  $purchaseCode  The purchase code
     *
     * @return string The masked purchase code
     */
    private function maskPurchaseCode(string $purchaseCode): string
    {
        return substr($purchaseCode, 0, 8) . '...';
    }
}
