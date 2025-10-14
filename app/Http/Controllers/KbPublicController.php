<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Services\KbAccessService;
use App\Services\KbSearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Knowledge Base Public Controller - Simplified
 */
class KbPublicController extends Controller
{
    public function __construct(
        private KbAccessService $kbAccessService,
        private KbSearchService $kbSearchService
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

            if (!$this->kbAccessService->categoryRequiresAccess($category)) {
                $articles = $this->getCategoryArticles($category);
                $relatedCategories = $this->getRelatedCategories($category);
                return view('kb.category', compact('category', 'articles', 'relatedCategories'));
            }

            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'You must be logged in to access this category.');
            }

            $user = auth()->user();
            $hasAccess = $this->kbAccessService->checkCategoryAccess($category, $user);
            $accessSource = 'user_license';

            if (!$hasAccess && request()->query('raw_code')) {
                $result = $this->kbAccessService->handleRawCodeAccess($category, request()->query('raw_code'));
                if ($result['success']) {
                    return $result['redirect'];
                }
                return redirect()->route('kb.category', ['slug' => $category->slug])
                    ->with('error', $result['error']);
            }

            if (!$hasAccess && request()->query('token')) {
                $tokenResult = $this->kbAccessService->validateAccessToken(request()->query('token'), $category->id);
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

            if (!$this->kbAccessService->articleRequiresAccess($article)) {
                $this->incrementArticleViews($article);
                $related = $this->getRelatedArticles($article);
                return view('kb.article', compact('article', 'related'));
            }

            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'You must be logged in to access this article.');
            }

            $user = auth()->user();
            $hasAccess = $this->kbAccessService->checkArticleAccess($article, $user);
            $accessSource = 'user_license';

            if (!$hasAccess && request()->query('raw_code')) {
                $result = $this->kbAccessService->handleArticleRawCodeAccess($article, request()->query('raw_code'));
                if ($result['success']) {
                    return $result['redirect'];
                }
                return redirect()->route('kb.article', ['slug' => $article->slug])
                    ->with('error', $result['error']);
            }

            if (!$hasAccess && request()->query('token')) {
                $tokenResult = $this->kbAccessService->validateArticleAccessToken(request()->query('token'), $article->id);
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

            $q = $this->kbSearchService->sanitizeSearchQuery($request->get('q', ''));
            $results = collect();
            $resultsWithAccess = collect();
            $categoriesWithAccess = collect();

            $allCategories = $this->kbSearchService->getAllCategoriesWithAccess();

            if ($q !== '') {
                $searchResults = $this->kbSearchService->performSearch($q);
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
     * Validate slug
     */
    private function validateSlug(string $slug): void
    {
        if (empty($slug) || strlen($slug) > 255) {
            throw new InvalidArgumentException('Invalid slug');
        }
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
     * Highlight search terms
     */
    public static function highlightSearchTerm($text, $query)
    {
        $kbSearchService = app(KbSearchService::class);
        return $kbSearchService->highlightSearchTerm($text, $query);
    }
}
