<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProductSearchRequest;
use App\Models\License;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProgrammingLanguage;
use App\Services\ProductFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Product Controller with enhanced security. *
 * This controller handles product listing, searching, filtering, and display * functionality for both authenticated users and guests with comprehensive * security measures and proper error handling. *
 * Features: * - Product listing with search and filtering * - Product details display with ownership checks * - Public product access for guests * - Download permission validation * - Related products suggestions * - Enhanced security measures (XSS protection, input validation) * - Comprehensive error handling and logging * - Proper logging for errors and warnings only * - Rate limiting for product access * - Authorization checks for product ownership */
class ProductController extends Controller
{
    /**   * Create a new controller instance. *   * @return void */
    public function __construct()
    {
        $this->middleware(['auth', 'user', 'verified'])->only(['index', 'show']);
    }
    /**   * Authenticated user product listing with enhanced security. *   * Shows active products for authenticated users with search and filtering * capabilities and proper authorization checks. *   * @param ProductSearchRequest $request The HTTP request containing search/filter parameters *   * @return View The products listing view *   * @throws \Exception When database operations fail *   * @example * // Access: GET /products?search=laravel&category=1&language=php * // Returns: View with filtered products */
    public function index(ProductSearchRequest $request): View
    {
        return $this->publicIndex($request);
    }
    /**   * Public product listing for users and guests with enhanced security. *   * Displays active products with comprehensive search, filtering, and sorting * capabilities for both authenticated users and guests. *   * @param  ProductSearchRequest|null  $request  The HTTP request containing search/filter parameters *   * @return View The products listing view *   * @throws \Exception When database operations fail *   * @example * // Access: GET /products/public?search=laravel&category=1&language=php&price_filter=paid&sort=price_low * // Returns: View with filtered and sorted products */
    public function publicIndex(ProductSearchRequest $request = null): View
    {
        try {
            // Rate limiting
            $key = 'products-index:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 30)) {
                Log::warning('Rate limit exceeded for products index', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(429, 'Too many requests');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            $productsQuery = Product::with(['category', 'programmingLanguage'])->where('is_active', true);
            // Search functionality
            if ($request && $request->filled('search')) {
                $search = $this->sanitizeInput($request->validated('search'));
                if ($search) {
                    $productsQuery->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%" . (is_string($search) ? $search : '') . "%")
                            ->orWhere('description', 'like', "%" . (is_string($search) ? $search : '') . "%");
                    });
                }
            }
            // Category filtering
            if ($request && $request->filled('category')) {
                $categoryId = $this->sanitizeInput($request->validated('category'));
                if (is_numeric($categoryId)) {
                    $productsQuery->where('category_id', (int)$categoryId);
                }
            }
            // Programming language filtering
            if ($request && $request->filled('language')) {
                $languageId = $this->sanitizeInput($request->validated('language'));
                if (is_numeric($languageId)) {
                    $productsQuery->where('programming_language', (int)$languageId);
                }
            }
            // Price filtering
            if ($request?->filled('price_filter')) {
                $priceFilter = $this->sanitizeInput($request->validated('price_filter'));
                if ($priceFilter === 'free') {
                    $productsQuery->where('price', 0);
                } elseif ($priceFilter === 'paid') {
                    $productsQuery->where('price', '>', 0);
                }
            }
            // Sorting
            $sort = $request ? $this->sanitizeInput($request->validated('sort', 'name')) : 'name';
            switch ($sort) {
                case 'price_low':
                    $productsQuery->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $productsQuery->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $productsQuery->orderBy('created_at', 'desc');
                    break;
                default:
                    $productsQuery->orderBy('name', 'asc');
            }
            $products = $productsQuery->paginate(15)->withQueryString();
            $categories = ProductCategory::where('is_active', true)->orderBy('sort_order')->get();
            $programmingLanguages = ProgrammingLanguage::where('is_active', true)->orderBy('sort_order')->get();
            return view('user.products.index', [
                'products' => $products,
                'categories' => $categories,
                'programmingLanguages' => $programmingLanguages
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load products index', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            abort(500, 'Failed to load products');
        }
    }
    /**   * Show product details for authenticated users with enhanced security. *   * Displays detailed product information with ownership checks, download * permissions, and related products for authenticated users. *   * @param Product $product The product to display *   * @return View The product details view *   * @throws \Exception When database operations fail *   * @example * // Access: GET /products/{product} * // Returns: View with product details and ownership information */
    public function show(Product $product): View
    {
        try {
            // Rate limiting
            $key = 'product-show:' . (Auth::id() ?? request()->ip()) . ':' . $product->id;
            if (RateLimiter::tooManyAttempts($key, 20)) {
                Log::warning('Rate limit exceeded for product show', [
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(429, 'Too many requests');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            if (! $product->is_active) {
                Log::warning('Inactive product access attempt', [
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                ]);
                abort(404);
            }
            // Check if user owns this product and has paid invoice
            $userOwnsProduct = false;
            $userCanDownload = false;
            $downloadMessage = '';
            $userHasPurchasedBefore = false;
            if (Auth::check()) {
                $user = Auth::user();
                $userOwnsProduct = $user ? $user->licenses()
                    ->where('product_id', $product->id)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('license_expires_at')
                            ->orWhere('license_expires_at', '>', now());
                    })
                    ->exists() : false;
                // Check if user has purchased this product before (any license, even expired)
                $userHasPurchasedBefore = $user?->licenses()
                    ->where('product_id', $product->id)
                    ->exists();
                // Check download permissions if product is downloadable
                if ($product->is_downloadable) {
                    $productFileService = app(ProductFileService::class);
                    $permissions = $productFileService->userCanDownloadFiles($product, Auth::id() ? (int)Auth::id() : 0);
                    $userCanDownload = $permissions['can_download'];
                    $downloadMessage = $permissions['message'];
                }
            }
            // Process content for HTML/text detection
            $product->description_has_html = false;
            $product->requirements_has_html = false;
            $product->installation_guide_has_html = false;

            // Check if fields are strings before processing
            $description = $product->description;
            $requirements = $product->requirements;
            $installationGuide = $product->installation_guide;

            if (is_string($description)) {
                $product->description_has_html = strip_tags($description) !== $description;
            }
            if (is_string($requirements)) {
                $product->requirements_has_html = strip_tags($requirements) !== $requirements;
            }
            if (is_string($installationGuide)) {
                $product->installation_guide_has_html = strip_tags($installationGuide) !== $installationGuide;
            }
            // Get license count for this product
            $licenseCount = License::where('product_id', $product->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('license_expires_at')
                        ->orWhere('license_expires_at', '>', now());
                })
                ->count();
            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->with(['category', 'programmingLanguage'])
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
            // Process screenshots data
            $screenshots = null;
            if ($product->screenshots && ! empty($product->screenshots)) {
                $screenshots = is_string($product->screenshots)
                    ? json_decode($product->screenshots, true)
                    : $product->screenshots;
            }
            return view('user.products.show', [
                'product' => $product,
                'relatedProducts' => $relatedProducts,
                'userOwnsProduct' => $userOwnsProduct,
                'licenseCount' => $licenseCount,
                'userCanDownload' => $userCanDownload,
                'downloadMessage' => $downloadMessage,
                'userHasPurchasedBefore' => $userHasPurchasedBefore,
                'screenshots' => $screenshots,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load product details', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            abort(500, 'Failed to load product details');
        }
    }
    /**   * Public product details (by slug) for guests with enhanced security. *   * Displays detailed product information for guests accessing products * via slug with comprehensive security measures. *   * @param string $slug The product slug *   * @return View The product details view *   * @throws \Exception When database operations fail *   * @example * // Access: GET /products/public/{slug} * // Returns: View with product details */
    public function publicShow(string $slug): View
    {
        try {
            // Rate limiting
            $key = 'product-public-show:' . (Auth::id() ?? request()->ip()) . ':' . $slug;
            if (RateLimiter::tooManyAttempts($key, 20)) {
                Log::warning('Rate limit exceeded for public product show', [
                    'slug' => $slug,
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(429, 'Too many requests');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            $sanitizedSlug = $this->sanitizeInput($slug);
            if (! $sanitizedSlug) {
                Log::warning('Invalid slug provided for public product show', [
                    'slug' => $slug,
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                ]);
                abort(404);
            }
            $product = Product::where('slug', $sanitizedSlug)
                ->where('is_active', true)
                ->with(['category', 'programmingLanguage'])
                ->firstOrFail();
            // Check if user owns this product and has paid invoice
            $userOwnsProduct = false;
            $userCanDownload = false;
            $downloadMessage = '';
            $userHasPurchasedBefore = false;
            if (Auth::check()) {
                $user = Auth::user();
                $userOwnsProduct = $user ? $user->licenses()
                    ->where('product_id', $product->id)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('license_expires_at')
                            ->orWhere('license_expires_at', '>', now());
                    })
                    ->exists() : false;
                // Check if user has purchased this product before (any license, even expired)
                $userHasPurchasedBefore = $user?->licenses()
                    ->where('product_id', $product->id)
                    ->exists();
                // Check download permissions if product is downloadable
                if ($product->is_downloadable) {
                    $productFileService = app(ProductFileService::class);
                    $permissions = $productFileService->userCanDownloadFiles($product, Auth::id() ? (int)Auth::id() : 0);
                    $userCanDownload = $permissions['can_download'];
                    $downloadMessage = $permissions['message'];
                }
            }
            // Process content for HTML/text detection
            $product->description_has_html = false;
            $product->requirements_has_html = false;
            $product->installation_guide_has_html = false;

            // Check if fields are strings before processing
            $description = $product->description;
            $requirements = $product->requirements;
            $installationGuide = $product->installation_guide;

            if (is_string($description)) {
                $product->description_has_html = strip_tags($description) !== $description;
            }
            if (is_string($requirements)) {
                $product->requirements_has_html = strip_tags($requirements) !== $requirements;
            }
            if (is_string($installationGuide)) {
                $product->installation_guide_has_html = strip_tags($installationGuide) !== $installationGuide;
            }
            // Get license count for this product
            $licenseCount = License::where('product_id', $product->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('license_expires_at')
                        ->orWhere('license_expires_at', '>', now());
                })
                ->count();
            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->with(['category', 'programmingLanguage'])
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
            return view('user.products.show', [
                'product' => $product,
                'relatedProducts' => $relatedProducts,
                'userOwnsProduct' => $userOwnsProduct,
                'licenseCount' => $licenseCount,
                'userCanDownload' => $userCanDownload,
                'downloadMessage' => $downloadMessage,
                'userHasPurchasedBefore' => $userHasPurchasedBefore,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Product not found for public show', [
                'slug' => $slug,
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            abort(404);
        } catch (\Exception $e) {
            Log::error('Failed to load public product details', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'slug' => $slug,
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            abort(500, 'Failed to load product details');
        }
    }
}
