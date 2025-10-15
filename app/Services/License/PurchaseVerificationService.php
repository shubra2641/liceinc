<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Purchase Verification Service with enhanced security.
 *
 * This service handles purchase code verification, user management,
 * and license creation with comprehensive error handling and security measures.
 *
 * Features:
 * - Purchase code verification with Envato API
 * - User creation and management
 * - License creation and updates
 * - Rate limiting and security validation
 * - Comprehensive error handling
 * - Database transaction support
 */
class PurchaseVerificationService
{
    private const RATE_LIMIT_DURATION = 1;

    public function __construct(
        private \App\Services\Envato\EnvatoService $envatoService,
        private LicenseService $licenseService,
        private PurchaseCodeService $purchaseCodeService,
        private \App\Services\System\ErrorHandlingService $errorHandlingService
    ) {
    }

    /**
     * Verify purchase code and create/update license with enhanced security.
     */
    public function verifyPurchase(Request $request): RedirectResponse
    {
        try {
            $data = $this->validatePurchaseRequest($request);

            if ($this->isRateLimited($request->ip(), 'verify_purchase_')) {
                return $this->handleRateLimit($request, $data);
            }

            $this->setRateLimit($request->ip(), 'verify_purchase_');

            return $this->processPurchaseVerification($request, $data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorHandlingService->handleValidationError($e, $request);
        } catch (Exception $e) {
            return $this->errorHandlingService->handleGeneralError($e, $request);
        }
    }

    /**
     * Process purchase verification with database transaction.
     */
    private function processPurchaseVerification(Request $request, array $data): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $sale = $this->verifyWithEnvato($data['purchase_code']);
            if (!$sale) {
                return $this->errorHandlingService->handleVerificationFailure($request, $data);
            }

            $product = $this->findAndValidateProduct($data['product_slug'], $sale);
            if (!$product) {
                return $this->errorHandlingService->handleProductMismatch($request, $data);
            }

            $user = $this->createOrFindUser($sale);
            $this->createOrUpdateLicense($data['purchase_code'], $product, $user, $sale);

            DB::commit();
            return back()->with('success', 'Purchase verified and license updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle rate limit exceeded.
     */
    private function handleRateLimit(Request $request, array $data): RedirectResponse
    {
        $this->errorHandlingService->logRateLimitExceeded($request, $data);
        return back()->withErrors(['purchase_code' => 'Too many verification attempts. Please try again later.']);
    }

    /**
     * Verify user purchase via AJAX with enhanced security.
     */
    public function verifyUserPurchaseAjax(Request $request): array
    {
        try {
            if (auth()->guest()) {
                return ['valid' => false, 'message' => 'Authentication required'];
            }

            $data = $this->validateAjaxPurchaseRequest($request);

            if ($this->isAjaxRateLimited($request)) {
                return $this->handleAjaxRateLimit($request);
            }

            return $this->processAjaxVerification($request, $data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorHandlingService->handleAjaxValidationError($e, $request);
        } catch (Exception $e) {
            return $this->errorHandlingService->handleAjaxGeneralError($e, $request);
        }
    }

    /**
     * Check if AJAX request is rate limited.
     */
    private function isAjaxRateLimited(Request $request): bool
    {
        $rateLimitKey = 'ajax_verify_' . auth()->id() . '_' . $request->ip();
        return $this->isRateLimited($rateLimitKey, 'ajax_verify_');
    }

    /**
     * Handle AJAX rate limit.
     */
    private function handleAjaxRateLimit(Request $request): array
    {
        $this->errorHandlingService->logAjaxRateLimitExceeded($request);
        return ['valid' => false, 'message' => 'Too many verification attempts. Please try again later.'];
    }

    /**
     * Process AJAX verification with database transaction.
     */
    private function processAjaxVerification(Request $request, array $data): array
    {
        $rateLimitKey = 'ajax_verify_' . auth()->id() . '_' . $request->ip();
        $this->setRateLimit($rateLimitKey, 'ajax_verify_');

        DB::beginTransaction();

        try {
            $sale = $this->verifyWithEnvato($data['purchase_code']);
            if (!$sale) {
                return $this->errorHandlingService->handleAjaxVerificationFailure($request, $data);
            }

            $product = Product::findOrFail($data['product_id']);
            if (!$this->validateProductOwnership($product, $sale)) {
                return $this->errorHandlingService->handleAjaxProductMismatch($request, $data, $product);
            }

            $existingLicense = $this->checkExistingLicense($data['purchase_code']);
            if ($existingLicense) {
                DB::rollBack();
                return [
                    'valid' => true,
                    'message' => 'License already exists in your account',
                    'license' => $existingLicense
                ];
            }

            $license = $this->createUserLicense($data['purchase_code'], $product, $sale);

            DB::commit();
            return [
                'valid' => true,
                'message' => 'License verified and added to your account',
                'license' => $license
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate purchase request data.
     */
    private function validatePurchaseRequest(Request $request): array
    {
        return $request->validate([
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
    }

    /**
     * Validate AJAX purchase request data.
     */
    private function validateAjaxPurchaseRequest(Request $request): array
    {
        return $request->validate([
            'purchase_code' => [
                'required',
                'string',
                'regex:/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',
                'max:36',
            ],
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
        ], [
            'purchase_code.regex' => 'Purchase code must be a valid UUID format.',
            'product_id.exists' => 'Selected product does not exist.',
        ]);
    }

    /**
     * Verify purchase with Envato API.
     */
    private function verifyWithEnvato(string $purchaseCode): ?array
    {
        return $this->envatoService->verifyPurchase($purchaseCode);
    }

    /**
     * Find product and validate ownership.
     */
    private function findAndValidateProduct(string $productSlug, array $sale): ?Product
    {
        $product = Product::where('slug', $productSlug)->firstOrFail();
        $itemId = data_get($sale, 'item.id');

        if ((string)$product->envato_item_id !== $itemId) {
            return null;
        }

        return $product;
    }

    /**
     * Validate product ownership.
     */
    private function validateProductOwnership(Product $product, array $sale): bool
    {
        $itemId = data_get($sale, 'item.id');
        return $product->envato_item_id && $product->envato_item_id === $itemId;
    }

    /**
     * Create or find user from sale data.
     */
    private function createOrFindUser(array $sale): User
    {
        $buyerName = data_get($sale, 'buyer', 'Unknown Buyer');
        $buyerEmail = data_get($sale, 'buyer_email');

        return User::firstOrCreate(
            ['email' => $buyerEmail ?: Str::uuid() . '@envato-temp.local'],
            [
                'name' => $buyerName,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => $buyerEmail ? now() : null,
            ]
        );
    }

    /**
     * Create or update license.
     */
    private function createOrUpdateLicense(string $purchaseCode, Product $product, User $user, array $sale): void
    {
        $supportEnd = data_get($sale, 'supported_until');

        License::updateOrCreate(
            ['purchase_code' => $purchaseCode],
            [
                'product_id' => $product->id,
                'user_id' => $user->id,
                'support_expires_at' => $supportEnd ? Carbon::parse($supportEnd)->format('Y-m-d') : null,
                'status' => 'active',
                'verified_at' => now(),
            ]
        );
    }

    /**
     * Create license for user.
     */
    private function createUserLicense(string $purchaseCode, Product $product, array $sale): License
    {
        return License::create([
            'purchase_code' => $purchaseCode,
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'license_type' => 'regular',
            'status' => 'active',
            'support_expires_at' => data_get($sale, 'supported_until') ?
                Carbon::parse(data_get($sale, 'supported_until'))->format('Y-m-d') : null,
            'verified_at' => now(),
        ]);
    }

    /**
     * Check if user already has this license.
     */
    private function checkExistingLicense(string $purchaseCode): ?License
    {
        return License::where('purchase_code', $purchaseCode)
            ->where('user_id', auth()->id())
            ->first();
    }

    /**
     * Check if request is rate limited.
     */
    private function isRateLimited(string $key, string $prefix = ''): bool
    {
        return cache()->has($prefix . $key);
    }

    /**
     * Set rate limit for request.
     */
    private function setRateLimit(string $key, string $prefix = ''): void
    {
        cache()->put($prefix . $key, true, now()->addMinutes(self::RATE_LIMIT_DURATION));
    }

    /**
     * Mask purchase code for logging.
     */
    private function maskPurchaseCode(string $purchaseCode): string
    {
        return substr($purchaseCode, 0, 8) . '...';
    }

}
