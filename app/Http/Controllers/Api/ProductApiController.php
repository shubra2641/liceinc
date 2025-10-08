<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductLookupRequest;
use App\Models\License;
use App\Services\EnvatoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Product API Controller.
 *
 * This controller handles product-related API endpoints including purchase code
 * lookup and product information retrieval for ticket creation and support.
 * It provides integration with both local database and Envato API for comprehensive
 * product and license management.
 *
 * Features:
 * - Purchase code lookup with database and Envato API fallback
 * - Product information retrieval for support tickets
 * - License verification and validation
 * - Comprehensive error handling with database transactions
 * - Integration with Envato Market API
 * - Enhanced security measures (XSS protection, input validation)
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
 *
 * @example
 * // Lookup product by purchase code
 * POST /api/product/lookup
 * {
 *     "purchase_code": "ABC123-DEF456-GHI789"
 * }
 */
class ProductApiController extends Controller
{
    protected EnvatoService $envatoService;
    /**
     * Create a new controller instance.
     *
     * @param  EnvatoService  $envatoService  The Envato service for API integration
     *
     * @version 1.0.6
     */
    public function __construct(EnvatoService $envatoService)
    {
        $this->envatoService = $envatoService;
    }
    /**
     * Product lookup by purchase code with enhanced security.
     *
     * Looks up product information using a purchase code, first checking the local
     * database for existing licenses, then falling back to Envato API verification.
     * This method is primarily used for ticket creation and support purposes.
     *
     * @param  ProductLookupRequest  $request  The validated request containing purchase code
     *
     * @return JsonResponse JSON response with product information or error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "purchase_code": "ABC123-DEF456-GHI789"
     * }
     *
     * // Success response (license exists in database):
     * {
     *     "success": true,
     *     "product_slug": "my-product",
     *     "product_name": "My Product",
     *     "license_exists": true,
     *     "licenseId": 123
     * }
     *
     * // Success response (license found via Envato API):
     * {
     *     "success": true,
     *     "product_slug": "my-product",
     *     "product_name": "My Product",
     *     "license_exists": false,
     *     "sale": {...}
     * }
     *
     * // Error response:
     * {
     *     "success": false,
     *     "message": "Invalid purchase code"
     * }
     */
    public function lookupByPurchaseCode(ProductLookupRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Sanitize input to prevent XSS
            $purchaseCode = $this->sanitizeInput($validated['purchase_code']);
            // First, check if this purchase code exists in our database
            $existingLicense = License::with('product')->where('purchase_code', $purchaseCode)->first();
            if ($existingLicense && $existingLicense->product) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'product_slug' => $this->sanitizeOutput($existingLicense->product->slug),
                    'product_name' => $this->sanitizeOutput($existingLicense->product->name),
                    'license_exists' => true,
                    'licenseId' => $existingLicense->id,
                ]);
            }
            // If not found in database, try Envato API
            $sale = $this->envatoService->verifyPurchase(is_string($purchaseCode) ? $purchaseCode : '');
            if ($sale) {
                $productSlug = $this->sanitizeOutput(
                    is_string(data_get($sale, 'item.slug'))
                        ? data_get($sale, 'item.slug')
                        : null
                );
                $productName = $this->sanitizeOutput(
                    is_string(data_get($sale, 'item.name'))
                        ? data_get($sale, 'item.name')
                        : null
                );
                DB::commit();
                return response()->json([
                    'success' => true,
                    'product_slug' => $productSlug,
                    'product_name' => $productName,
                    'license_exists' => false,
                    'sale' => $sale,
                ]);
            }
            Log::warning('Invalid purchase code lookup attempt', [
                'purchase_code' => substr(is_string($purchaseCode) ? $purchaseCode : '', 0, 4) . '...',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            DB::commit();
            return response()->json([
                'success' => false,
                'message' => 'Invalid purchase code',
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product lookup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify purchase code',
            ], 500);
        }
    }
    /**
     * Sanitize output to prevent XSS attacks.
     *
     * @param  string|null  $output  The output to sanitize
     *
     * @return string The sanitized output
     */
    private function sanitizeOutput(?string $output): string
    {
        if ($output === null) {
            return '';
        }
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
}
