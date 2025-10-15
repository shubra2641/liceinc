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
 * Knowledge Base Public Controller - Simplified
 */
class KbPublicController extends Controller
{
    public function __construct(
        private PurchaseCodeService $purchaseCodeService
    ) {
    }

    /**
     * Display KB index
     */
    public function index(): View
    {
        try {
            $categories = KbCategory::where('is_active', true)
                ->withCount('children')
                ->with('product')
                ->orderBy('name')
                ->get();

            $latest = KbArticle::where('is_published', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_active', true);
                })
                ->with('category', 'product')
                ->latest()
                ->limit(6)
                ->get();

            return view('kb.index', compact('categories', 'latest'));
        } catch (\Exception $e) {
            Log::error('KB index failed', ['error' => $e->getMessage()]);
            return view('kb.index', ['categories' => collect(), 'latest' => collect()]);
        }
    }

    /**
     * Display KB category
     */
    public function category(string $slug): View|RedirectResponse
    {
        try {
            $this->validateSlug($slug);
            $category = KbCategory::where('slug', $slug)
                ->with('product')
                ->firstOrFail();

            if (!$this->categoryRequiresAccess($category)) {
                $articles = $this->getCategoryArticles($category);
                $relatedCategories = $this->getRelatedCategories($category);
                return view('kb.category', compact('category', 'articles', 'relatedCategories'));
            }

            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'You must be logged in to access this category.');
            }

            $user = auth()->user();
            $hasAccess = $this->checkCategoryAccess($category, $user);
            $accessSource = 'user_license';

            if (!$hasAccess && request()->query('raw_code')) {
                $result = $this->handleRawCodeAccess($category, request()->query('raw_code'));
                if ($result['success']) {
                    return $result['redirect'];
                }
                return redirect()->route('kb.category', ['slug' => $category->slug])
                    ->with('error', $result['error']);
            }

            if (!$hasAccess && request()->query('token')) {
                $tokenResult = $this->validateAccessToken(request()->query('token'), $category->id);
                if ($tokenResult['valid']) {
                    $hasAccess = true;
                    $accessSource = 'token';
                }
            }

            if ($hasAccess) {
                $articles = $this->getCategoryArticles($category);
                $relatedCategories = $this->getRelatedCategories($category);
                return view('kb.category', compact('category', 'articles', 'relatedCategories', 'accessSource'));
            }

            return view('kb.category-purchase', compact('category'));
        } catch (\Exception $e) {
            Log::error('KB category failed', ['error' => $e->getMessage(), 'slug' => $slug]);
            return redirect()->route('kb.index')->with('error', 'Category not found.');
        }
    }

    /**
     * Display KB article
     */
    public function article(string $slug): View|RedirectResponse
    {
        try {
            $this->validateSlug($slug);

            $article = KbArticle::where('slug', $slug)
                ->where('is_published', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_active', true);
                })
                ->with('category', 'product')
                ->firstOrFail();

            if (!$this->articleRequiresAccess($article)) {
                $this->incrementArticleViews($article);
                $related = $this->getRelatedArticles($article);
                return view('kb.article', compact('article', 'related'));
            }

            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'You must be logged in to access this article.');
            }

            $user = auth()->user();
            $hasAccess = $this->checkArticleAccess($article, $user);
            $accessSource = 'user_license';

            if (!$hasAccess && request()->query('raw_code')) {
                $result = $this->handleArticleRawCodeAccess($article, request()->query('raw_code'));
                if ($result['success']) {
                    return $result['redirect'];
                }
                return redirect()->route('kb.article', ['slug' => $article->slug])
                    ->with('error', $result['error']);
            }

            if (!$hasAccess && request()->query('token')) {
                $tokenResult = $this->validateArticleAccessToken(request()->query('token'), $article->id);
                if ($tokenResult['valid']) {
                    $hasAccess = true;
                    $accessSource = 'token';
                }
            }

            if ($hasAccess) {
                $this->incrementArticleViews($article);
                $related = $this->getRelatedArticles($article);
                return view('kb.article', compact('article', 'related', 'accessSource'));
            }

            return view('kb.article-purchase', compact('article'));
        } catch (\Exception $e) {
            Log::error('KB article failed', ['error' => $e->getMessage(), 'slug' => $slug]);
            return redirect()->route('kb.index')->with('error', 'Article not found.');
        }
    }

    /**
     * Search KB
     */
    public function search(Request $request): View
    {
        try {
            $request->validate([
                'q' => 'sometimes|string|max:255',
                'category' => 'sometimes|string|max:255',
                'page' => 'sometimes|integer|min:1',
            ]);

            $q = $this->sanitizeSearchQuery($request->get('q', ''));
            $results = collect();
            $resultsWithAccess = collect();
            $categoriesWithAccess = collect();

            $allCategories = $this->getAllCategoriesWithAccess();

            if ($q !== '') {
                $searchResults = $this->performSearch($q);
                $results = $searchResults['results'];
                $resultsWithAccess = $searchResults['resultsWithAccess'];
                $categoriesWithAccess = $searchResults['categoriesWithAccess'];
            } else {
                $categoriesWithAccess = $allCategories;
            }

            $highlightQuery = htmlspecialchars($q, ENT_QUOTES, 'UTF-8');
            return view('kb.search', compact(
                'q',
                'results',
                'resultsWithAccess',
                'categoriesWithAccess',
                'highlightQuery'
            ));
        } catch (\Exception $e) {
            Log::error('KB search failed', ['error' => $e->getMessage()]);
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
     * Check category access
     */
    private function checkCategoryAccess($category, $user): bool
    {
        if (!$category->requires_serial && !$category->product_id) {
            return true;
        }

        if (!$user || !$category->product_id) {
            return false;
        }

        return $user->licenses()
            ->where('product_id', $category->product_id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('license_expires_at')
                    ->orWhere('license_expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Check article access
     */
    private function checkArticleAccess($article, $user): bool
    {
        $requiresAccess = $article->requires_serial ||
                         $article->requires_purchase_code ||
                         $article->product_id ||
                         $article->category->requires_serial ||
                         $article->category->product_id;

        if (!$requiresAccess) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $productId = $article->product_id ?: $article->category->product_id;
        if (!$productId) {
            return false;
        }

        return $user->licenses()
            ->where('product_id', $productId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('license_expires_at')
                    ->orWhere('license_expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Validate slug
     */
    private function validateSlug(string $slug): void
    {
        if (empty($slug) || strlen($slug) > 255) {
            throw new InvalidArgumentException('Invalid slug');
        }
    }

    /**
     * Check if category requires access
     */
    private function categoryRequiresAccess($category): bool
    {
        return $category->requires_serial || $category->product_id;
    }

    /**
     * Check if article requires access
     */
    private function articleRequiresAccess($article): bool
    {
        return $article->requires_serial ||
               $article->requires_purchase_code ||
               $article->product_id ||
               $article->category->requires_serial ||
               $article->category->product_id;
    }

    /**
     * Get category articles
     */
    private function getCategoryArticles($category)
    {
        return KbArticle::where('kb_category_id', $category->id)
            ->where('is_published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category', 'product')
            ->latest()
            ->paginate(10);
    }

    /**
     * Get related categories
     */
    private function getRelatedCategories($category)
    {
        return KbCategory::where('id', '!=', $category->id)
            ->where('is_active', true)
            ->with('product')
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    /**
     * Get related articles
     */
    private function getRelatedArticles($article)
    {
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
    }

    /**
     * Increment article views
     */
    private function incrementArticleViews($article): void
    {
        $article->increment('views');
    }

    /**
     * Handle raw code access for category
     */
    private function handleRawCodeAccess($category, string $rawCode): array
    {
        try {
            $result = $this->purchaseCodeService->verifyRawCode($rawCode, $category->product_id);

            if ($result['success']) {
                $license = $result['license'] ?? null;
                $productId = $result['product_id'] ?? ($license?->product_id);
                $product = $productId ? Product::find($productId) : null;

                if ($product && $product->id == $category->product_id) {
                    $accessToken = 'kb_access_' . $category->id . '_' . time() . '_' .
                        substr(md5($license?->license_key ?? ''), 0, 8);
                    session([$accessToken => [
                        'license_id' => $license?->id,
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
                }
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Invalid license code',
            ];
        } catch (\Exception $e) {
            Log::error('Raw code access failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Access verification failed'];
        }
    }

    /**
     * Handle raw code access for article
     */
    private function handleArticleRawCodeAccess($article, string $rawCode): array
    {
        try {
            $productId = $article->product_id ?: $article->category->product_id;
            $result = $this->purchaseCodeService->verifyRawCode($rawCode, $productId);

            if ($result['success']) {
                $license = $result['license'] ?? null;
                $productId = $result['product_id'] ?? ($license?->product_id);
                $product = $productId ? Product::find($productId) : null;

                if ($product && $product->id == $productId) {
                    $accessToken = 'kb_article_access_' . $article->id . '_' . time() . '_' .
                        substr(md5($license?->license_key ?? ''), 0, 8);
                    session([$accessToken => [
                        'license_id' => $license?->id,
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
                }
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Invalid license code',
            ];
        } catch (\Exception $e) {
            Log::error('Article raw code access failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Access verification failed'];
        }
    }

    /**
     * Validate access token
     */
    private function validateAccessToken(string $accessToken, int $categoryId): array
    {
        if (session()->has($accessToken)) {
            $tokenData = session($accessToken);
            if (
                is_array($tokenData) &&
                isset($tokenData['expires_at']) &&
                isset($tokenData['category_id']) &&
                $tokenData['expires_at'] > now() &&
                $tokenData['category_id'] == $categoryId
            ) {
                return ['valid' => true];
            }
            session()->forget($accessToken);
        }
        return ['valid' => false];
    }

    /**
     * Validate article access token
     */
    private function validateArticleAccessToken(string $accessToken, int $articleId): array
    {
        if (session()->has($accessToken)) {
            $tokenData = session($accessToken);
            if (
                is_array($tokenData) &&
                isset($tokenData['expires_at']) &&
                isset($tokenData['article_id']) &&
                $tokenData['expires_at'] > now() &&
                $tokenData['article_id'] == $articleId
            ) {
                return ['valid' => true];
            }
            session()->forget($accessToken);
        }
        return ['valid' => false];
    }

    /**
     * Sanitize search query
     */
    private function sanitizeSearchQuery(string $query): string
    {
        $query = trim($query);
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
        return strlen($query) > 255 ? substr($query, 0, 255) : $query;
    }

    /**
     * Get all categories with access
     */
    private function getAllCategoriesWithAccess()
    {
        $categories = KbCategory::where('is_active', true)
            ->with('product', 'articles')
            ->get();

        $user = auth()->user();
        $categories->each(function ($category) use ($user) {
            $category->hasAccess = $this->checkCategoryAccess($category, $user);
        });

        return $categories;
    }

    /**
     * Perform search
     */
    private function performSearch(string $q): array
    {
        $searchTerm = '%' . strtolower($q) . '%';
        $user = auth()->user();

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

        $categories = KbCategory::where('is_active', true)
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$searchTerm]);
            })
            ->with('product')
            ->get();

        $articles->each(fn($article) => $article->search_type = 'article');
        $categories->each(fn($category) => $category->search_type = 'category');

        $results = $articles->concat($categories);
        $resultsWithAccess = collect();
        $categoriesWithAccess = collect();

        foreach ($results as $item) {
            $hasAccess = true;
            if ($item instanceof KbArticle) {
                $hasAccess = $this->checkArticleAccess($item, $user);
            } elseif ($item instanceof KbCategory) {
                $hasAccess = $this->checkCategoryAccess($item, $user);
            }

            $item->hasAccess = $hasAccess;

            if ($item instanceof KbArticle) {
                $resultsWithAccess->push($item);
            } else {
                $categoriesWithAccess->push($item);
                $resultsWithAccess->push($item);
            }
        }

        return [
            'results' => $results,
            'resultsWithAccess' => $resultsWithAccess,
            'categoriesWithAccess' => $categoriesWithAccess,
        ];
    }

    /**
     * Highlight search terms
     */
    public static function highlightSearchTerm($text, $query)
    {
        if (empty($query)) {
            return $text;
        }
        return preg_replace(
            '/(' . preg_quote($query, '/') . ')/i',
            '<mark class="search-highlight">$1</mark>',
            $text
        ) ?? $text;
    }
}
