<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\License;
use App\Models\Product;
use App\Services\EnvatoService;
use App\Services\PurchaseCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Knowledge Base Public Controller with enhanced security and comprehensive KB management.
 *
 * This controller handles public knowledge base operations including viewing articles,
 * categories, and search functionality. It implements comprehensive security measures,
 * input validation, and error handling for reliable KB operations.
 */
class KbPublicController extends Controller
{
    protected EnvatoService $envatoService;
    protected PurchaseCodeService $purchaseCodeService;
    /**
     * Constructor with enhanced security and dependency injection.
     *
     * @param  EnvatoService  $envatoService  Envato service for license verification
     * @param  PurchaseCodeService  $purchaseCodeService  Purchase code service for verification
     */
    public function __construct(EnvatoService $envatoService, PurchaseCodeService $purchaseCodeService)
    {
        $this->envatoService = $envatoService;
        $this->purchaseCodeService = $purchaseCodeService;
    }
    /**
     * Display the knowledge base index with enhanced security and error handling.
     *
     * Shows the main knowledge base page with categories and latest articles with
     * comprehensive validation, security measures, and error handling for reliable
     * KB operations.
     *
     * @return View The KB index view
     *
     * @throws \Exception When data retrieval fails
     *
     * @example
     * // Access via GET /kb
     * // Returns KB index with categories and latest articles
     */
    public function index(): View
    {
        try {
            DB::beginTransaction();
            // Get active categories with enhanced security
            $categories = $this->getActiveCategories();
            // Get latest articles with enhanced security
            $latest = $this->getLatestArticles();
            DB::commit();
            return view('kb.index', compact('categories', 'latest'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to display KB index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return KB index with empty data on error
            return view('kb.index', [
                'categories' => collect(),
                'latest' => collect(),
            ]);
        }
    }
    /**
     * Display a knowledge base category with enhanced security and error handling.
     *
     * Shows a specific KB category with articles and access control with comprehensive
     * validation, security measures, and error handling for reliable KB operations.
     *
     * @param  string  $slug  The category slug
     *
     * @return View|RedirectResponse The category view or redirect on error
     *
     * @throws InvalidArgumentException When slug is invalid
     * @throws \Exception When category retrieval fails
     *
     * @example
     * // Access via GET /kb/category/{slug}
     * // Returns category view with articles and access control
     */
    public function category(string $slug): View|RedirectResponse
    {
        try {
            // Validate input parameters
            $this->validateSlug($slug);
            DB::beginTransaction();
            // Get category with enhanced security
            $category = $this->getCategoryBySlug($slug);
            // Check if access is required
            $requiresAccess = $this->categoryRequiresAccess($category);
            // If access is not required, show the category directly
            if (! $requiresAccess) {
                $articles = $this->getCategoryArticles($category);
                $relatedCategories = $this->getRelatedCategories($category);
                DB::commit();
                return view('kb.category', compact('category', 'articles', 'relatedCategories'));
            }
            // For protected categories, require authentication
            if (! auth()->check()) {
                DB::rollBack();
                return redirect()->route('login')->with('error', 'You must be logged in to access this category.');
            }
            $user = auth()->user();
            $hasAccess = false;
            $accessSource = '';
            $providedRawCode = request()->query('raw_code');
            $error = null;
            // If raw code provided, verify it and ensure the license's product actually grants access
            if ($providedRawCode) {
                $accessResult = $this->handleRawCodeAccess($category, $providedRawCode);
                if ($accessResult['success']) {
                    return $accessResult['redirect'];
                } else {
                    $error = $accessResult['error'];
                    DB::rollBack();
                    return redirect()->route('kb.category', ['slug' => $category->slug])->with('error', $error);
                }
            }
            // Check for access token in URL or session
            $accessToken = request()->query('token');
            if ($accessToken && session()->has($accessToken)) {
                $tokenResult = $this->validateAccessToken($accessToken, $category->id);
                if ($tokenResult['valid']) {
                    $hasAccess = true;
                    $accessSource = 'token';
                }
            }
            // If access granted, show the category
            if ($hasAccess) {
                $articles = $this->getCategoryArticles($category);
                $relatedCategories = $this->getRelatedCategories($category);
                DB::commit();
                return view('kb.category', compact('category', 'articles', 'relatedCategories', 'accessSource'));
            }
            // No access - show purchase prompt
            DB::rollBack();
            return view('kb.category-purchase', compact('category'))->with('error', $error);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to display KB category', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'slug' => $slug,
            ]);
            return redirect()->route('kb.index')->with('error', 'Category not found or access denied.');
        }
    }
    /**
     * Display a knowledge base article with enhanced security and error handling.
     *
     * Shows a specific KB article with access control and related articles with
     * comprehensive validation, security measures, and error handling for reliable
     * KB operations.
     *
     * @param  string  $slug  The article slug
     *
     * @return View|RedirectResponse The article view or redirect on error
     *
     * @throws InvalidArgumentException When slug is invalid
     * @throws \Exception When article retrieval fails
     *
     * @example
     * // Access via GET /kb/article/{slug}
     * // Returns article view with access control and related articles
     */
    public function article(string $slug): View|RedirectResponse
    {
        try {
            // Validate input parameters
            $this->validateSlug($slug);
            DB::beginTransaction();
            // Get article with enhanced security
            $article = $this->getArticleBySlug($slug);
            // Check if article requires access
            $requiresAccess = $this->articleRequiresAccess($article);
            // If no access required, show article
            if (! $requiresAccess) {
                $this->incrementArticleViews($article);
                $relatedArticles = $this->getRelatedArticles($article);
                DB::commit();
                return view('kb.article', compact('article', 'relatedArticles'));
            }
            // For protected articles, require authentication
            if (! auth()->check()) {
                DB::rollBack();
                return redirect()->route('login')->with('error', 'You must be logged in to access this article.');
            }
            $user = auth()->user();
            $hasAccess = false;
            $accessSource = '';
            $providedRawCode = request()->query('raw_code');
            $error = null;
            // If user is logged in, first check their licenses (no raw code needed)
            if ($this->checkArticleAccess($article, $user) === true) {
                $hasAccess = true;
                $accessSource = 'user_license';
            }
            // If a raw code was provided, also allow verification via raw code (Envato/db)
            if (! $hasAccess && $providedRawCode) {
                $accessResult = $this->handleArticleRawCodeAccess($article, $providedRawCode);
                if ($accessResult['success']) {
                    return $accessResult['redirect'];
                } else {
                    $error = $accessResult['error'];
                    DB::rollBack();
                    return redirect()->route('kb.article', ['slug' => $article->slug])->with('error', $error);
                }
            }
            // Check for access token in URL or session
            $accessToken = request()->query('token');
            if ($accessToken && session()->has($accessToken)) {
                $tokenResult = $this->validateArticleAccessToken($accessToken, $article->id);
                if ($tokenResult['valid']) {
                    $hasAccess = true;
                    $accessSource = 'token';
                }
            }
            if ($hasAccess) {
                $this->incrementArticleViews($article);
                $relatedArticles = $this->getRelatedArticles($article);
                DB::commit();
                return view('kb.article', compact('article', 'relatedArticles', 'accessSource'));
            }
            // No access - show purchase prompt
            DB::rollBack();
            return view('kb.article-purchase', compact('article'))->with('error', $error);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to display KB article', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'slug' => $slug,
            ]);
            return redirect()->route('kb.index')->with('error', 'Article not found or access denied.');
        }
    }
    /**
     * Search knowledge base with enhanced security and error handling.
     *
     * Performs secure search across KB articles and categories with comprehensive
     * validation, security measures, and error handling for reliable KB operations.
     *
     * @param  Request  $request  The search request
     *
     * @return View The search results view
     *
     * @throws InvalidArgumentException When search query is invalid
     * @throws \Exception When search operation fails
     *
     * @example
     * // Access via GET /kb/search?q=search_term
     * // Returns search results with access control
     */
    public function search(Request $request): View
    {
        try {
            // Validate request with search rules
            $this->validateRequest($request, [
                'q' => 'sometimes|string|max:255',
                'category' => 'sometimes|string|max:255',
                'page' => 'sometimes|integer|min:1',
            ]);
            DB::beginTransaction();
            // Sanitize and validate search query
            $q = $this->sanitizeSearchQuery($request->get('q', ''));
            $results = collect();
            $resultsWithAccess = collect();
            $categoriesWithAccess = collect();
            // Get all categories with access information for display
            $allCategories = $this->getAllCategoriesWithAccess();
            if ($q !== '') {
                // Perform secure search
                $searchResults = $this->performSecureSearch($q);
                $results = $searchResults['results'];
                $resultsWithAccess = $searchResults['resultsWithAccess'];
                $categoriesWithAccess = $searchResults['categoriesWithAccess'];
            } else {
                // If no search query, use all categories for display
                $categoriesWithAccess = $allCategories;
            }
            DB::commit();
            // Add highlighting helper for search terms
            $highlightQuery = htmlspecialchars($q, ENT_QUOTES, 'UTF-8');
            return view('kb.search', compact(
                'q',
                'results',
                'resultsWithAccess',
                'categoriesWithAccess',
                'highlightQuery',
            ));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to perform KB search', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query' => $request->get('q', ''),
            ]);
            // Return search page with empty results on error
            return view('kb.search', [
                'q' => '',
                'results' => collect(),
                'resultsWithAccess' => collect(),
                'categoriesWithAccess' => collect(),
                'highlightQuery' => '',
            ]);
        }
    }
    /**
     * Check if user has access to a category with enhanced security and error handling.
     *
     * @param  KbCategory  $category  The category to check access for
     * @param  mixed  $user  The user to check access for
     *
     * @return bool True if user has access, false otherwise
     *
     * @throws \Exception When access check fails
     */
    private function checkCategoryAccess($category, $user): bool
    {
        try {
            $requiresAccess = (bool)($category->requires_serial || $category->product_id);
            if (! $requiresAccess) {
                return true; // No access required
            }
            if (! $user) {
                return false; // User not logged in
            }
            // If category is linked to a product, user must have an active license for that product
            if ($category->product_id) {
                // Check if user has an active license for that specific product
                $hasLicense = $user->licenses()
                    ->where('product_id', $category->product_id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('license_expires_at')
                            ->orWhere('license_expires_at', '>', now());
                    })
                    ->exists();
                return $hasLicense;
            }
            return false; // Requires access but no product_id
        } catch (\Exception $e) {
            Log::error('Failed to check category access', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $category->id ?? null,
                'user_id' => $user->id ?? null,
            ]);
            return false;
        }
    }
    /**
     * Check if user has access to an article with enhanced security and error handling.
     *
     * @param  KbArticle  $article  The article to check access for
     * @param  mixed  $user  The user to check access for
     *
     * @return bool True if user has access, false otherwise
     *
     * @throws \Exception When access check fails
     */
    private function checkArticleAccess($article, $user): bool
    {
        try {
            $requiresAccess = (bool)($article->requires_serial ||
                                     $article->requires_purchase_code ||
                                     $article->product_id ||
                                     ($article->category &&
                                      ($article->category->requires_serial ||
                                       $article->category->product_id)));
            if (! $requiresAccess) {
                return true; // No access required
            }
            if (! $user) {
                return false; // User not logged in
            }
            // If article is linked directly to a product, user must have an active license for that product
            if ($article->product_id) {
                $hasLicense = $user->licenses()
                    ->where('product_id', $article->product_id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('license_expires_at')
                            ->orWhere('license_expires_at', '>', now());
                    })
                    ->exists();
                return $hasLicense;
            }
            // Otherwise, if category defines a product_id, check that product
            if ($article->category && $article->category->product_id) {
                $catProductId = $article->category->product_id;
                $hasLicense = $user->licenses()
                    ->where('product_id', $catProductId)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('license_expires_at')
                            ->orWhere('license_expires_at', '>', now());
                    })
                    ->exists();
                return $hasLicense;
            }
            return false; // Requires access but no product mapping found
        } catch (\Exception $e) {
            Log::error('Failed to check article access', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'article_id' => $article->id ?? null,
                'user_id' => $user->id ?? null,
            ]);
            return false;
        }
    }
    /**
     * Validate slug with enhanced security and comprehensive validation.
     *
     * @param  string  $slug  The slug to validate
     *
     * @throws InvalidArgumentException When slug is invalid
     */
    private function validateSlug(string $slug): void
    {
        if (empty($slug) || strlen($slug) > 255) {
            throw new InvalidArgumentException('Invalid slug provided');
        }
        // XSS protection
        $slug = htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');
    }
    /**
     * Get active categories with enhanced security and error handling.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @throws \Exception When category retrieval fails
     */
    private function getActiveCategories()
    {
        try {
            return KbCategory::where('is_active', true)
                ->withCount('children')
                ->with('product')
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve active categories', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get latest articles with enhanced security and error handling.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @throws \Exception When article retrieval fails
     */
    private function getLatestArticles()
    {
        try {
            return KbArticle::where('is_published', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_active', true);
                })
                ->with('category', 'product')
                ->latest()
                ->limit(6)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve latest articles', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get category by slug with enhanced security and error handling.
     *
     * @param  string  $slug  The category slug
     *
     * @return KbCategory The category
     *
     * @throws \Exception When category retrieval fails
     */
    private function getCategoryBySlug(string $slug): KbCategory
    {
        try {
            return KbCategory::where('slug', $slug)
                ->with('product')
                ->firstOrFail();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve category by slug', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'slug' => $slug,
            ]);
            throw $e;
        }
    }
    /**
     * Check if category requires access with enhanced security.
     *
     * @param  KbCategory  $category  The category to check
     *
     * @return bool True if access is required, false otherwise
     */
    private function categoryRequiresAccess(KbCategory $category): bool
    {
        return (bool)($category->requires_serial || $category->product_id);
    }
    /**
     * Get category articles with enhanced security and error handling.
     *
     * @param  KbCategory  $category  The category
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @throws \Exception When article retrieval fails
     */
    private function getCategoryArticles(KbCategory $category)
    {
        try {
            return KbArticle::where('kb_category_id', $category->id)
                ->where('is_published', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_active', true);
                })
                ->with('category', 'product')
                ->latest()
                ->paginate(10);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve category articles', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $category->id,
            ]);
            throw $e;
        }
    }
    /**
     * Get related categories with enhanced security and error handling.
     *
     * @param  KbCategory  $category  The category
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @throws \Exception When category retrieval fails
     */
    private function getRelatedCategories(KbCategory $category)
    {
        try {
            return KbCategory::where('id', '!=', $category->id)
                ->where('is_published', true)
                ->with('product')
                ->inRandomOrder()
                ->limit(4)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve related categories', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $category->id,
            ]);
            throw $e;
        }
    }
    /**
     * Handle raw code access for category with enhanced security.
     *
     * @param  KbCategory  $category  The category
     * @param  string  $rawCode  The raw code
     *
     * @return array Access result
     *
     * @throws \Exception When access handling fails
     */
    private function handleRawCodeAccess(KbCategory $category, string $rawCode): array
    {
        try {
            $rawResult = $this->purchaseCodeService->verifyRawCode(
                $rawCode,
                $category->product_id,
            );
            if ($rawResult['success']) {
                $license = $rawResult['license'] ?? null;
                $productId = $rawResult['product_id'] ?? ($license->product_id ?? null);
                $product = $productId ? Product::find($productId) : null;
                if ($product && $product->id == $category->product_id) {
                    $accessToken = 'kb_access_' . $category->id . '_' . time() . '_' . substr(md5($license->license_key), 0, 8);
                    session([$accessToken => [
                        'license_id' => $license->id,
                        'product_id' => $product->id,
                        'category_id' => $category->id,
                        'expires_at' => now()->addHours(24),
                    ]]);
                    return [
                        'success' => true,
                        'redirect' => redirect()->route('kb.category', [
                            'slug' => $category->slug,
                            'token' => $accessToken,
                        ]),
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => $rawResult['message'] ?? trans('license_status.license_code_not_for_product'),
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error' => $rawResult['message'] ?? trans('app.invalid_license_code_or_not_belong_to_product'),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to handle raw code access for category', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $category->id,
            ]);
            return [
                'success' => false,
                'error' => 'Access verification failed',
            ];
        }
    }
    /**
     * Validate access token with enhanced security.
     *
     * @param  string  $accessToken  The access token
     * @param  int  $categoryId  The category ID
     *
     * @return array Token validation result
     */
    private function validateAccessToken(string $accessToken, int $categoryId): array
    {
        try {
            if (session()->has($accessToken)) {
                $tokenData = session($accessToken);
                if ($tokenData['expires_at'] > now() && $tokenData['category_id'] == $categoryId) {
                    return ['valid' => true];
                } else {
                    session()->forget($accessToken);
                }
            }
            return ['valid' => false];
        } catch (\Exception $e) {
            Log::error('Failed to validate access token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $categoryId,
            ]);
            return ['valid' => false];
        }
    }
    /**
     * Get article by slug with enhanced security and error handling.
     *
     * @param  string  $slug  The article slug
     *
     * @return KbArticle The article
     *
     * @throws \Exception When article retrieval fails
     */
    private function getArticleBySlug(string $slug): KbArticle
    {
        try {
            return KbArticle::where('slug', $slug)
                ->where('is_published', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_active', true);
                })
                ->with('category', 'product')
                ->firstOrFail();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve article by slug', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'slug' => $slug,
            ]);
            throw $e;
        }
    }
    /**
     * Check if article requires access with enhanced security.
     *
     * @param  KbArticle  $article  The article to check
     *
     * @return bool True if access is required, false otherwise
     */
    private function articleRequiresAccess(KbArticle $article): bool
    {
        return (bool)($article->requires_serial ||
                                 $article->product_id ||
                                 ($article->category &&
                                  ($article->category->requires_serial ||
                                   $article->category->product_id)));
    }
    /**
     * Increment article views with enhanced security and error handling.
     *
     * @param  KbArticle  $article  The article
     *
     * @throws \Exception When view increment fails
     */
    private function incrementArticleViews(KbArticle $article): void
    {
        try {
            $article->increment('views');
        } catch (\Exception $e) {
            Log::error('Failed to increment article views', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'article_id' => $article->id,
            ]);
            throw $e;
        }
    }
    /**
     * Get related articles with enhanced security and error handling.
     *
     * @param  KbArticle  $article  The article
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @throws \Exception When article retrieval fails
     */
    private function getRelatedArticles(KbArticle $article)
    {
        try {
            return KbArticle::where('kb_category_id', $article->kb_category_id)
                ->where('id', '!=', $article->id)
                ->where('is_published', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_active', true);
                })
                ->with('category', 'product')
                ->latest()
                ->limit(3)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve related articles', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'article_id' => $article->id,
            ]);
            throw $e;
        }
    }
    /**
     * Handle raw code access for article with enhanced security.
     *
     * @param  KbArticle  $article  The article
     * @param  string  $rawCode  The raw code
     *
     * @return array Access result
     *
     * @throws \Exception When access handling fails
     */
    private function handleArticleRawCodeAccess(KbArticle $article, string $rawCode): array
    {
        try {
            $productIdToVerify = $article->product_id ?: ($article->category ? $article->category->product_id : null);
            $rawResult = $this->purchaseCodeService->verifyRawCode(
                $rawCode,
                $productIdToVerify,
            );
            if ($rawResult['success']) {
                $license = $rawResult['license'] ?? null;
                $productId = $rawResult['product_id'] ?? ($license->product_id ?? null);
                $product = $productId ? Product::find($productId) : null;
                $articleProductId = $article->product_id ?:
                    ($article->category ? $article->category->product_id : null);
                if ($product && $product->id == $articleProductId) {
                    $accessToken = 'kb_article_access_' . $article->id . '_' . time() . '_' .
                        substr(md5($license->license_key), 0, 8);
                    session([$accessToken => [
                        'license_id' => $license->id,
                        'product_id' => $product->id,
                        'article_id' => $article->id,
                        'expires_at' => now()->addHours(24),
                    ]]);
                    return [
                        'success' => true,
                        'redirect' => redirect()->route('kb.article', [
                            'slug' => $article->slug,
                            'token' => $accessToken,
                        ]),
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => $rawResult['message'] ?? trans('license_status.license_code_not_for_product'),
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error' => $rawResult['message'] ?? trans('app.invalid_license_code_or_not_belong_to_product'),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to handle raw code access for article', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'article_id' => $article->id,
            ]);
            return [
                'success' => false,
                'error' => 'Access verification failed',
            ];
        }
    }
    /**
     * Validate article access token with enhanced security.
     *
     * @param  string  $accessToken  The access token
     * @param  int  $articleId  The article ID
     *
     * @return array Token validation result
     */
    private function validateArticleAccessToken(string $accessToken, int $articleId): array
    {
        try {
            if (session()->has($accessToken)) {
                $tokenData = session($accessToken);
                if ($tokenData['expires_at'] > now() && $tokenData['article_id'] == $articleId) {
                    return ['valid' => true];
                } else {
                    session()->forget($accessToken);
                }
            }
            return ['valid' => false];
        } catch (\Exception $e) {
            Log::error('Failed to validate article access token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'article_id' => $articleId,
            ]);
            return ['valid' => false];
        }
    }
    /**
     * Sanitize search query with enhanced security and XSS protection.
     *
     * @param  string  $query  The search query
     *
     * @return string Sanitized search query
     */
    private function sanitizeSearchQuery(string $query): string
    {
        $query = trim($query);
        // XSS protection
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
        // Limit length
        if (strlen($query) > 255) {
            $query = substr($query, 0, 255);
        }
        return $query;
    }
    /**
     * Get all categories with access information for display.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @throws \Exception When category retrieval fails
     */
    private function getAllCategoriesWithAccess()
    {
        try {
            $allCategories = KbCategory::where('is_active', true)
                ->with('product', 'articles')
                ->get();
            $user = auth()->user();
            $allCategories->each(function ($category) use ($user) {
                $category->hasAccess = $this->checkCategoryAccess($category, $user);
            });
            return $allCategories;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve all categories with access', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Perform secure search with enhanced security and error handling.
     *
     * @param  string  $q  The search query
     *
     * @return array Search results
     *
     * @throws \Exception When search fails
     */
    private function performSecureSearch(string $q): array
    {
        try {
            $searchTerm = '%' . strtolower($q) . '%';
            $user = auth()->user();
            // Search articles with case-insensitive search (secure)
            $articles = KbArticle::where('is_published', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_active', true);
                })
                ->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(title) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(content) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(excerpt) LIKE ?', [$searchTerm]);
                })
                ->with('category', 'product')
                ->get();
            // Search categories with case-insensitive search (secure)
            $categories = KbCategory::where('is_active', true)
                ->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$searchTerm]);
                })
                ->with('product')
                ->get();
            // Add search_type to results
            $articles->each(function ($article) {
                $article->search_type = 'article';
            });
            $categories->each(function ($category) {
                $category->search_type = 'category';
            });
            // Combine results
            $results = $articles->concat($categories);
            $resultsWithAccess = collect();
            $categoriesWithAccess = collect();
            // Filter results based on access
            foreach ($results as $item) {
                $hasAccess = true; // Default to accessible
                if ($item instanceof KbArticle) {
                    $hasAccess = $this->checkArticleAccess($item, $user);
                } elseif ($item instanceof KbCategory) {
                    $hasAccess = $this->checkCategoryAccess($item, $user);
                }
                // Add hasAccess property for view
                $item->hasAccess = $hasAccess;
                // Always add to results, regardless of access (but mark locked items)
                if ($item instanceof KbArticle) {
                    $resultsWithAccess->push($item);
                } else {
                    $categoriesWithAccess->push($item);
                    $resultsWithAccess->push($item); // Also add to main results for display
                }
            }
            // Add pagination for results (limit to 10 per page)
            $resultsWithAccess = $resultsWithAccess->forPage(request('page', 1), 10);
            return [
                'results' => $results,
                'resultsWithAccess' => $resultsWithAccess,
                'categoriesWithAccess' => $categoriesWithAccess,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to perform secure search', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query' => $q,
            ]);
            throw $e;
        }
    }
    /**
     * Highlight search terms in text.
     *
     * @param string $text The text to highlight
     * @param string $query The search query
     *
     * @return string The highlighted text
     */
    public static function highlightSearchTerm($text, $query)
    {
        if (empty($query)) {
            return $text;
        }
        return preg_replace('/(' . preg_quote($query, '/') . ')/i', '<mark class="search-highlight">$1</mark>', $text);
    }
}
