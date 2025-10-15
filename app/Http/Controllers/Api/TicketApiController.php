<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TicketIndexRequest;
use App\Http\Requests\Api\VerifyPurchaseCodeRequest;
use App\Models\License;
use App\Models\Product;
use App\Models\Ticket;
use App\Services\Envato\EnvatoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Ticket API Controller.
 *
 * This controller handles ticket-related API endpoints including ticket listing,
 * purchase code verification for ticket creation, and support ticket management.
 * It provides comprehensive ticket management functionality with security measures.
 *
 * Features:
 * - Ticket listing with filtering and pagination
 * - Purchase code verification for ticket creation
 * - Database and Envato API integration
 * - Search functionality across tickets and users
 * - Comprehensive error handling with database transactions
 * - Support for multiple ticket statuses and priorities
 * - Enhanced security measures (XSS protection, input validation)
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
 *
 * @example
 * // List tickets with filters
 * GET /api/tickets?status=open&priority=high&search=bug
 *
 * // Verify purchase code
 * GET /api/verify-purchase-code/ABC123-DEF456-GHI789
 */
class TicketApiController extends Controller
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
     * Display a listing of tickets with enhanced security.
     *
     * Retrieves a paginated list of tickets with optional filtering and search
     * capabilities. Supports filtering by status, priority, category, and user,
     * as well as searching across ticket content and user information.
     *
     * @param  TicketIndexRequest  $request  The validated request containing filter parameters
     *
     * @return JsonResponse JSON response with tickets and pagination information
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request with filters:
     * GET /api/tickets?status=open&priority=high&search=bug&per_page=20
     *
     * // Success response:
     * {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 1,
     *             "subject": "Bug Report",
     *             "status": "open",
     *             "priority": "high",
     *             "user": {...},
     *             "category": {...}
     *         }
     *     ],
     *     "pagination": {
     *         "current_page": 1,
     *         "last_page": 5,
     *         "per_page": 15,
     *         "total": 75
     *     }
     * }
     */
    public function index(TicketIndexRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $query = Ticket::with(['user', 'category', 'invoice.product']);
            // Apply filters with sanitized input
            if (isset($validated['status'])) {
                $query->where('status', $this->sanitizeInput($validated['status']));
            }
            if (isset($validated['priority'])) {
                $query->where('priority', $this->sanitizeInput($validated['priority']));
            }
            if (isset($validated['category_id'])) {
                $query->where('category_id', $validated['category_id']);
            }
            if (isset($validated['user_id'])) {
                $query->where('user_id', $validated['user_id']);
            }
            // Search with sanitized input
            if (isset($validated['search'])) {
                $search = $this->sanitizeInput($validated['search']);
                $searchStr = is_string($search) ? $search : '';
                $query->where(function ($q) use ($searchStr) {
                    $q->where('subject', 'like', "%{$searchStr}%")
                        ->orWhere('content', 'like', "%{$searchStr}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchStr) {
                            $userQuery->where('name', 'like', "%{$searchStr}%")
                                ->orWhere('email', 'like', "%{$searchStr}%");
                        });
                });
            }
            // Pagination with validated input
            $perPage = $validated['per_page'] ?? 15;
            $tickets = $query->latest()->paginate(is_numeric($perPage) ? (int)$perPage : 15);
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ticket listing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch tickets. Please try again later.',
            ], 500);
        }
    }

    /**
     * Verify purchase code for ticket creation with enhanced security.
     *
     * Verifies a purchase code by first checking the local database for existing
     * licenses, then falling back to Envato API verification. This method is
     * primarily used for ticket creation and support purposes.
     *
     * @param  VerifyPurchaseCodeRequest  $request  The validated request containing purchase code
     *
     * @return JsonResponse JSON response with product information or error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * POST /api/verify-purchase-code
     * {
     *     "purchase_code": "ABC123-DEF456-GHI789"
     * }
     *
     * // Success response (license exists in database):
     * {
     *     "success": true,
     *     "product": {
     *         "slug": "my-product",
     *         "name": "My Product",
     *         "version": "1.0.0"
     *     },
     *     "license_exists": true,
     *     "license_id": 123
     * }
     *
     * // Success response (license found via Envato API):
     * {
     *     "success": true,
     *     "product": {
     *         "slug": "my-product",
     *         "name": "My Product",
     *         "version": "1.0.0"
     *     },
     *     "license_exists": false,
     *     "sale": {...}
     * }
     *
     * // Error response:
     * {
     *     "success": false,
     *     "message": "Purchase code not found or invalid"
     * }
     */
    public function verifyPurchaseCode(VerifyPurchaseCodeRequest $request): JsonResponse
    {
        $purchaseCode = null;
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $purchaseCode = $this->sanitizeInput($validated['purchase_code']);
            // First, check if this purchase code exists in our database
            $existingLicense = License::with('product')->where('purchase_code', $purchaseCode)->first();
            if ($existingLicense && $existingLicense->product) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'product' => [
                        'slug' => $this->sanitizeOutput($existingLicense->product->slug),
                        'name' => $this->sanitizeOutput($existingLicense->product->name),
                        'version' => $this->sanitizeOutput($existingLicense->product->version ?? '1.0.0'),
                    ],
                    'license_exists' => true,
                    'license_id' => $existingLicense->id,
                ]);
            }
            // If not found in database, try Envato API
            try {
                $sale = $this->envatoService->verifyPurchase(is_string($purchaseCode) ? $purchaseCode : '');
                if ($sale && isset($sale['item'])) {
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
                        'product' => [
                            'slug' => $productSlug,
                            'name' => $productName,
                            'version' => '1.0.0',
                        ],
                        'license_exists' => false,
                        'sale' => $sale,
                    ]);
                }
            } catch (\Throwable $envatoError) {
                Log::warning('Envato API error during purchase code verification', [
                    'purchase_code' => substr(is_string($purchaseCode) ? $purchaseCode : '', 0, 4) . '...',
                    'error' => $envatoError->getMessage(),
                ]);
            }
            Log::warning('Purchase code not found in ticket verification', [
                'purchase_code' => substr(is_string($purchaseCode) ? $purchaseCode : '', 0, 4) . '...',
            ]);
            DB::commit();

            return response()->json([
                'success' => false,
                'message' => 'Purchase code not found or invalid',
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase code verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'purchase_code' => substr(is_string($purchaseCode) ? $purchaseCode : '', 0, 4) . '...',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify purchase code. Please try again later.',
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
