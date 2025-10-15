<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Services\PurchaseCodeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

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
            $category = $this->getCategoryBySlug($slug);
            
            if ($this->canAccessCategory($category)) {
                return $this->showCategory($category);
            }
            
            return $this->handleCategoryAccess($category);
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
            $article = $this->getArticleBySlug($slug);
            
            if ($this->canAccessArticle($article)) {
                return $this->showArticle($article);
            }
            
            return $this->handleArticleAccess($article);
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

            $q = $this->sanitizeQuery($request->get('q', ''));
            $results = $this->performSearch($q);
            
            return view('kb.search', compact('q', 'results'));
        } catch (\Exception $e) {
            Log::error('KB search failed', ['error' => $e->getMessage()]);
            return view('kb.search', ['q' => '', 'results' => collect()]);
        }
    }

    /**
     * Get category by slug
     */
    private function getCategoryBySlug(string $slug): KbCategory
    {
        return KbCategory::where('slug', $slug)
            ->with('product')
            ->firstOrFail();
    }

    /**
     * Get article by slug
     */
    private function getArticleBySlug(string $slug): KbArticle
    {
        return KbArticle::where('slug', $slug)
            ->where('is_published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category', 'product')
            ->firstOrFail();
    }

    /**
     * Check if user can access category
     */
    private function canAccessCategory(KbCategory $category): bool
    {
        if (!$category->requires_serial && !$category->product_id) {
            return true;
        }

        if (!auth()->check()) {
            return false;
        }

        return $this->checkUserLicense($category->product_id);
    }

    /**
     * Check if user can access article
     */
    private function canAccessArticle(KbArticle $article): bool
    {
        $requiresAccess = $article->requires_serial ||
                         $article->requires_purchase_code ||
                         $article->product_id ||
                         $article->category->requires_serial ||
                         $article->category->product_id;

        if (!$requiresAccess) {
            return true;
        }

        if (!auth()->check()) {
            return false;
        }

        $productId = $article->product_id ?: $article->category->product_id;
        return $this->checkUserLicense($productId);
    }

    /**
     * Check user license for product
     */
    private function checkUserLicense(?int $productId): bool
    {
        if (!$productId) {
            return false;
        }

        return auth()->user()->licenses()
            ->where('product_id', $productId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('license_expires_at')
                    ->orWhere('license_expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Show category page
     */
    private function showCategory(KbCategory $category): View
    {
        $articles = KbArticle::where('kb_category_id', $category->id)
            ->where('is_published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category', 'product')
            ->latest()
            ->paginate(10);

        $relatedCategories = KbCategory::where('id', '!=', $category->id)
            ->where('is_active', true)
            ->with('product')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('kb.category', compact('category', 'articles', 'relatedCategories'));
    }

    /**
     * Show article page
     */
    private function showArticle(KbArticle $article): View
    {
        $article->increment('views');
        
        $related = KbArticle::where('kb_category_id', $article->kb_category_id)
            ->where('id', '!=', $article->id)
            ->where('is_published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category', 'product')
            ->latest()
            ->limit(3)
            ->get();

        return view('kb.article', compact('article', 'related'));
    }

    /**
     * Handle category access
     */
    private function handleCategoryAccess(KbCategory $category): View|RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to access this category.');
        }

        return view('kb.category-purchase', compact('category'));
    }

    /**
     * Handle article access
     */
    private function handleArticleAccess(KbArticle $article): View|RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to access this article.');
        }

        return view('kb.article-purchase', compact('article'));
    }

    /**
     * Sanitize search query
     */
    private function sanitizeQuery(string $query): string
    {
        $query = trim($query);
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
        return strlen($query) > 255 ? substr($query, 0, 255) : $query;
    }

    /**
     * Perform search
     */
    private function performSearch(string $query): Collection
    {
        if (empty($query)) {
            return KbCategory::where('is_active', true)
                ->with('product', 'articles')
                ->get();
        }

        $searchTerm = '%' . strtolower($query) . '%';
        
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

        return $articles->concat($categories);
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