<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\Envato\EnvatoService;
use App\Services\License\PurchaseVerificationService;
use App\Services\User\UserManagementService;
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

    public function __construct(
        private PurchaseVerificationService $purchaseVerificationService,
        private UserManagementService $userManagementService
    ) {
    }
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
        return $this->purchaseVerificationService->verifyPurchase($request);
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
        return $this->userManagementService->redirectToEnvato();
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
        return $this->userManagementService->handleEnvatoCallback();
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
        return $this->userManagementService->linkEnvatoAccount();
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
        $result = $this->purchaseVerificationService->verifyUserPurchaseAjax($request);

        if ($result['valid']) {
            return response()->json($result);
        }

        $statusCode = isset($result['errors']) ? 422 : 500;
        return response()->json($result, $statusCode);
    }
}
