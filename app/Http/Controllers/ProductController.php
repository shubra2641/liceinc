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
use Illuminate\View\View;

/**
 * Product Controller - Simplified
 * 
 * Handles product listing, searching, filtering, and display functionality.
 * Supports both authenticated users and guests.
 */
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'user', 'verified'])->only(['index', 'show']);
    }

    /**
     * Show products for authenticated users
     */
    public function index(ProductSearchRequest $request): View
    {
        return $this->publicIndex($request);
    }

    /**
     * Show products for public access
     */
    public function publicIndex(ProductSearchRequest $request = null): View
    {
        $query = Product::with(['category', 'programmingLanguage'])->where('is_active', true);

        // Apply search filter
        if ($request && $request->filled('search')) {
            $search = $this->sanitizeInput($request->validated('search'));
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
        }

        // Apply category filter
        if ($request && $request->filled('category')) {
            $categoryId = $request->validated('category');
            if (is_numeric($categoryId)) {
                $query->where('category_id', (int)$categoryId);
            }
        }

        // Apply language filter
        if ($request && $request->filled('language')) {
            $languageId = $request->validated('language');
            if (is_numeric($languageId)) {
                $query->where('programming_language', (int)$languageId);
            }
        }

        // Apply price filter
        if ($request?->filled('price_filter')) {
            $priceFilter = $request->validated('price_filter');
            if ($priceFilter === 'free') {
                $query->where('price', 0);
            } elseif ($priceFilter === 'paid') {
                $query->where('price', '>', 0);
            }
        }

        // Apply sorting
        $sort = $request ? $request->validated('sort', 'name') : 'name';
        $this->applySorting($query, $sort);

        $products = $query->paginate(15)->withQueryString();
        $categories = ProductCategory::where('is_active', true)->orderBy('sort_order')->get();
        $programmingLanguages = ProgrammingLanguage::where('is_active', true)->orderBy('sort_order')->get();

        return view('user.products.index', [
            'products' => $products,
            'categories' => $categories,
            'programmingLanguages' => $programmingLanguages
        ]);
    }

    /**
     * Show product details for authenticated users
     */
    public function show(Product $product): View
    {
        if (!$product->is_active) {
            abort(404);
        }

        $productData = $this->getProductData($product);
        $screenshots = $this->getScreenshots($product);

        return view('user.products.show', [
            'product' => $product,
            'relatedProducts' => $productData['relatedProducts'],
            'userOwnsProduct' => $productData['userOwnsProduct'],
            'licenseCount' => $productData['licenseCount'],
            'userCanDownload' => $productData['userCanDownload'],
            'downloadMessage' => $productData['downloadMessage'],
            'userHasPurchasedBefore' => $productData['userHasPurchasedBefore'],
            'screenshots' => $screenshots,
        ]);
    }

    /**
     * Show product details for public access
     */
    public function publicShow(string $slug): View
    {
        $sanitizedSlug = $this->sanitizeInput($slug);
        if (!$sanitizedSlug) {
            abort(404);
        }

        $product = Product::where('slug', $sanitizedSlug)
            ->where('is_active', true)
            ->with(['category', 'programmingLanguage'])
            ->firstOrFail();

        $productData = $this->getProductData($product);

        return view('user.products.show', [
            'product' => $product,
            'relatedProducts' => $productData['relatedProducts'],
            'userOwnsProduct' => $productData['userOwnsProduct'],
            'licenseCount' => $productData['licenseCount'],
            'userCanDownload' => $productData['userCanDownload'],
            'downloadMessage' => $productData['downloadMessage'],
            'userHasPurchasedBefore' => $productData['userHasPurchasedBefore'],
        ]);
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, string $sort): void
    {
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }
    }

    /**
     * Get product data for display
     */
    private function getProductData(Product $product): array
    {
        $userOwnsProduct = false;
        $userCanDownload = false;
        $downloadMessage = '';
        $userHasPurchasedBefore = false;

        if (Auth::check()) {
            $user = Auth::user();
            $userOwnsProduct = $user ? $this->checkUserOwnsProduct($user, $product) : false;
            $userHasPurchasedBefore = $user ? $this->checkUserHasPurchased($user, $product) : false;

            if ($product->is_downloadable) {
                $downloadData = $this->getDownloadPermissions($product);
                $userCanDownload = $downloadData['can_download'];
                $downloadMessage = $downloadData['message'];
            }
        }

        $this->processProductContent($product);

        $licenseCount = $this->getLicenseCount($product);
        $relatedProducts = $this->getRelatedProducts($product);

        return [
            'userOwnsProduct' => $userOwnsProduct,
            'userCanDownload' => $userCanDownload,
            'downloadMessage' => $downloadMessage,
            'userHasPurchasedBefore' => $userHasPurchasedBefore,
            'licenseCount' => $licenseCount,
            'relatedProducts' => $relatedProducts,
        ];
    }

    /**
     * Check if user owns product
     */
    private function checkUserOwnsProduct($user, Product $product): bool
    {
        return $user->licenses()
            ->where('product_id', $product->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('license_expires_at')
                  ->orWhere('license_expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Check if user has purchased before
     */
    private function checkUserHasPurchased($user, Product $product): bool
    {
        return $user->licenses()
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * Get download permissions
     */
    private function getDownloadPermissions(Product $product): array
    {
        $productFileService = app(ProductFileService::class);
        return $productFileService->userCanDownloadFiles($product, Auth::id() ?: 0);
    }

    /**
     * Process product content for HTML detection
     */
    private function processProductContent(Product $product): void
    {
        $product->description_has_html = $this->hasHtml($product->description);
        $product->requirements_has_html = $this->hasHtml($product->requirements);
        $product->installation_guide_has_html = $this->hasHtml($product->installation_guide);
    }

    /**
     * Check if content has HTML
     */
    private function hasHtml($content): bool
    {
        if (!is_string($content)) {
            return false;
        }
        return strip_tags($content) !== $content;
    }

    /**
     * Get license count for product
     */
    private function getLicenseCount(Product $product): int
    {
        return License::where('product_id', $product->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('license_expires_at')
                  ->orWhere('license_expires_at', '>', now());
            })
            ->count();
    }

    /**
     * Get related products
     */
    private function getRelatedProducts(Product $product)
    {
        return Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['category', 'programmingLanguage'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Get screenshots for product
     */
    private function getScreenshots(Product $product)
    {
        if (!$product->screenshots || empty($product->screenshots)) {
            return null;
        }

        return is_string($product->screenshots)
            ? json_decode($product->screenshots, true)
            : $product->screenshots;
    }

    /**
     * Sanitize input data
     */
    private function sanitizeInput(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}