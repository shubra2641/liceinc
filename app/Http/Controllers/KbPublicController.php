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
        private KbAccessService $accessService,
        private KbSearchService $searchService
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

            if (!$this->accessService->categoryRequiresAccess($category)) {
                return $this->showPublicCategory($category);
            }

            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'You must be logged in to access this category.');
            }

            $accessResult = $this->handleCategoryAccess($category);
            if ($accessResult['hasAccess']) {
                return $this->showProtectedCategory($category, $accessResult['accessSource']);
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

            if (!$this->accessService->articleRequiresAccess($article)) {
                return $this->showPublicArticle($article);
            }

            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'You must be logged in to access this article.');
            }

            $accessResult = $this->handleArticleAccess($article);
            if ($accessResult['hasAccess']) {
                return $this->showProtectedArticle($article, $accessResult['accessSource']);
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

            $q = $this->searchService->sanitizeSearchQuery($request->get('q', ''));
            $results = collect();
            $resultsWithAccess = collect();
            $categoriesWithAccess = collect();

            $allCategories = $this->searchService->getAllCategoriesWithAccess();

            if ($q !== '') {
                $searchResults = $this->searchService->performSearch($q);
                $results = $searchResults['results'];
                $resultsWithAccess = $searchResults['resultsWithAccess'];
                $categoriesWithAccess = $searchResults['categoriesWithAccess'];
            } else {
                $categoriesWithAccess = $allCategories;
            }

            $highlightQuery = htmlspecialchars($q, ENT_QUOTES, 'UTF-8');
            return view('kb.search', compact('q', 'results', 'resultsWithAccess', 'categoriesWithAccess', 'highlightQuery'));
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
     * Show public category (no access required)
     */
    private function showPublicCategory(KbCategory $category): View
    {
        $articles = $this->getCategoryArticles($category);
        $relatedCategories = $this->getRelatedCategories($category);
        return view('kb.category', compact('category', 'articles', 'relatedCategories'));
    }

    /**
     * Show protected category (access required)
     */
    private function showProtectedCategory(KbCategory $category, string $accessSource): View
    {
        $articles = $this->getCategoryArticles($category);
        $relatedCategories = $this->getRelatedCategories($category);
        return view('kb.category', compact('category', 'articles', 'relatedCategories', 'accessSource'));
    }

    /**
     * Handle category access logic
     */
    private function handleCategoryAccess(KbCategory $category): array
    {
        $user = auth()->user();
        $hasAccess = $this->accessService->checkCategoryAccess($category, $user);
        $accessSource = 'user_license';

        if (!$hasAccess && request()->query('raw_code')) {
            $result = $this->accessService->handleRawCodeAccess($category, request()->query('raw_code'));
            if ($result['success']) {
                return ['hasAccess' => true, 'accessSource' => 'raw_code', 'redirect' => $result['redirect']];
            }
            return ['hasAccess' => false, 'error' => $result['error']];
        }

        if (!$hasAccess && request()->query('token')) {
            $tokenResult = $this->accessService->validateAccessToken(request()->query('token'), $category->id);
            if ($tokenResult['valid']) {
                $hasAccess = true;
                $accessSource = 'token';
            }
        }

        return ['hasAccess' => $hasAccess, 'accessSource' => $accessSource];
    }

    /**
     * Show public article (no access required)
     */
    private function showPublicArticle(KbArticle $article): View
    {
        $this->incrementArticleViews($article);
        $related = $this->getRelatedArticles($article);
        return view('kb.article', compact('article', 'related'));
    }

    /**
     * Show protected article (access required)
     */
    private function showProtectedArticle(KbArticle $article, string $accessSource): View
    {
        $this->incrementArticleViews($article);
        $related = $this->getRelatedArticles($article);
        return view('kb.article', compact('article', 'related', 'accessSource'));
    }

    /**
     * Handle article access logic
     */
    private function handleArticleAccess(KbArticle $article): array
    {
        $user = auth()->user();
        $hasAccess = $this->accessService->checkArticleAccess($article, $user);
        $accessSource = 'user_license';

        if (!$hasAccess && request()->query('raw_code')) {
            $result = $this->accessService->handleArticleRawCodeAccess($article, request()->query('raw_code'));
            if ($result['success']) {
                return ['hasAccess' => true, 'accessSource' => 'raw_code', 'redirect' => $result['redirect']];
            }
            return ['hasAccess' => false, 'error' => $result['error']];
        }

        if (!$hasAccess && request()->query('token')) {
            $tokenResult = $this->accessService->validateArticleAccessToken(request()->query('token'), $article->id);
            if ($tokenResult['valid']) {
                $hasAccess = true;
                $accessSource = 'token';
            }
        }

        return ['hasAccess' => $hasAccess, 'accessSource' => $accessSource];
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


}
