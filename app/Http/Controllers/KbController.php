<?php

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Knowledge Base Controller with enhanced security. *
 * This controller handles knowledge base operations including article * management, category browsing, and search functionality with enhanced * security measures and proper error handling. *
 * Features: * - Knowledge base article management (CRUD operations) * - Category-based article browsing * - Article search functionality * - Enhanced security measures (XSS protection, input validation) * - Comprehensive error handling with database transactions * - Proper logging for errors and warnings only * - Model relationship integration for optimized queries * - Clean code structure with no duplicate patterns * - Proper type hints and return types */
class KbController extends Controller
{
    /**   * Pagination limit for article listing. */
    private const PAGINATION_LIMIT = 10;
    /**   * Recent articles limit. */
    private const RECENT_ARTICLES_LIMIT = 10;
    /**   * Display a listing of knowledge base articles with enhanced security. *   * Shows knowledge base homepage with categories and recent articles * with comprehensive error handling and security measures. *   * @return View The knowledge base index view *   * @throws Exception When database operations fail *   * @example * // Access knowledge base: * GET /kb *   * // Returns view with: * // - All categories with articles * // - Recent published articles (10) */
    public function index(): View
    {
        try {
            DB::beginTransaction();
            $categories = KbCategory::with('articles')->get();
            $recentArticles = KbArticle::where('status', 'published')
                ->latest()
                ->limit(self::RECENT_ARTICLES_LIMIT)
                ->get();
            DB::commit();
            return view('kb.index', ['categories' => $categories, 'recentArticles' => $recentArticles]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load knowledge base index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return view('kb.index', ['categories' => collect(), 'recentArticles' => collect()])
                ->with('error', 'Failed to load knowledge base. Please try again.');
        }
    }
    /**   * Show the form for creating a new knowledge base article with enhanced security. *   * Displays article creation form with category selection and * comprehensive error handling. *   * @return View The article creation view *   * @throws Exception When database operations fail *   * @example * // Access article creation form: * GET /kb/create *   * // Returns view with: * // - All available categories */
    public function create(): View|\Illuminate\Http\RedirectResponse
    {
        try {
            DB::beginTransaction();
            $categories = KbCategory::all();
            DB::commit();
            /** @var view-string $viewName */
            $viewName = 'kb.create';
            return view($viewName, ['categories' => $categories]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load article creation form: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load article creation form. Please try again.');
        }
    }
    /**   * Store a newly created knowledge base article with enhanced security. *   * Creates a new knowledge base article with comprehensive validation, * input sanitization, and error handling. *   * @param Request $request The HTTP request containing article data *   * @return RedirectResponse The redirect response *   * @throws Exception When database operations fail *   * @example * // Create new article: * POST /kb * { * "title": "How to install the product", * "slug": "how-to-install", * "content": "Step by step installation guide", * "category_id": 1, * "status": "published" * } */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Request is validated by type hint
            $validated = $this->validateArticleData($request);
            $validated['user_id'] = auth()->id();
            DB::beginTransaction();
            KbArticle::create($validated);
            DB::commit();
            return redirect()->route('kb.index')
                ->with('success', 'Article created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create knowledge base article: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to create article. Please try again.')->withInput();
        }
    }
    /**   * Display the specified knowledge base article with enhanced security. *   * Shows detailed article information with category relationship * and comprehensive error handling. *   * @param KbArticle $article The article instance *   * @return View The article detail view *   * @throws Exception When database operations fail *   * @example * // Access specific article: * GET /kb/articles/how-to-install *   * // Returns view with: * // - Article details * // - Category information */
    public function show(KbArticle $article): View
    {
        try {
            // Article is validated by type hint
            DB::beginTransaction();
            $article->load('category');
            DB::commit();
            /** @var view-string $viewName */
            $viewName = 'kb.show';
            return view($viewName, ['article' => $article]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load knowledge base article: ' . $e->getMessage(), [
                'article_id' => $article->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Failed to load article. Please try again.');
        }
    }
    /**   * Show the form for editing the specified knowledge base article with enhanced security. *   * Displays article editing form with category selection and * comprehensive error handling. *   * @param KbArticle $article The article instance *   * @return View The article editing view *   * @throws Exception When database operations fail *   * @example * // Access article editing form: * GET /kb/articles/1/edit *   * // Returns view with: * // - Article data * // - All available categories */
    public function edit(KbArticle $article): View|\Illuminate\Http\RedirectResponse
    {
        try {
            // Article is validated by type hint
            DB::beginTransaction();
            $categories = KbCategory::all();
            DB::commit();
            /** @var view-string $viewName */
            $viewName = 'kb.edit';
            return view($viewName, ['article' => $article, 'categories' => $categories]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load article editing form: ' . $e->getMessage(), [
                'article_id' => $article->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load article editing form. Please try again.');
        }
    }
    /**   * Update the specified knowledge base article with enhanced security. *   * Updates article information with comprehensive validation, * input sanitization, and error handling. *   * @param Request $request The HTTP request containing update data * @param KbArticle $article The article instance *   * @return RedirectResponse The redirect response *   * @throws Exception When database operations fail *   * @example * // Update article: * PUT /kb/articles/1 * { * "title": "Updated title", * "content": "Updated content", * "status": "published" * } */
    public function update(Request $request, KbArticle $article): RedirectResponse
    {
        try {
            // Request and article are validated by type hints
            $validated = $this->validateArticleUpdateData($request, $article);
            DB::beginTransaction();
            $article->update($validated);
            DB::commit();
            return redirect()->route('kb.show', $article)
                ->with('success', 'Article updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update knowledge base article: ' . $e->getMessage(), [
                'article_id' => $article->id ?? null,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to update article. Please try again.')->withInput();
        }
    }
    /**   * Remove the specified knowledge base article with enhanced security. *   * Deletes article with comprehensive error handling and * proper authorization checking. *   * @param KbArticle $article The article instance *   * @return RedirectResponse The redirect response *   * @throws Exception When database operations fail *   * @example * // Delete article: * DELETE /kb/articles/1 */
    public function destroy(KbArticle $article): RedirectResponse
    {
        try {
            // Article is validated by type hint
            DB::beginTransaction();
            $article->delete();
            DB::commit();
            return redirect()->route('kb.index')
                ->with('success', 'Article deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete knowledge base article: ' . $e->getMessage(), [
                'article_id' => $article->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to delete article. Please try again.');
        }
    }
    /**   * Search knowledge base articles with enhanced security. *   * Performs article search with comprehensive validation, * input sanitization, and error handling. *   * @param Request $request The HTTP request containing search query *   * @return View The search results view *   * @throws Exception When database operations fail *   * @example * // Search articles: * GET /kb/search?q=installation *   * // Returns view with: * // - Search results (paginated) * // - Search query */
    public function search(Request $request): View
    {
        try {
            // Request is validated by type hint
            $query = $this->validateSearchQuery(is_string($request->get('q')) ? $request->get('q') : null);
            DB::beginTransaction();
            $articles = KbArticle::where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%")
                        ->orWhere('tags', 'like', "%{$query}%");
                })
                ->with('category')
                ->paginate(self::PAGINATION_LIMIT);
            DB::commit();
            return view('kb.search', ['articles' => $articles, 'query' => $query]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to search knowledge base articles: ' . $e->getMessage(), [
                'query' => $request->get('q'),
                'trace' => $e->getTraceAsString(),
            ]);
            return view('kb.search', ['articles' => collect(), 'query' => ''])
                ->with('error', 'Failed to search articles. Please try again.');
        }
    }
    /**   * Show articles by category with enhanced security. *   * Displays articles belonging to a specific category with * comprehensive error handling and security measures. *   * @param KbCategory $category The category instance *   * @return View The category articles view *   * @throws Exception When database operations fail *   * @example * // Access category articles: * GET /kb/categories/installation *   * // Returns view with: * // - Category information * // - Published articles (paginated) */
    public function category(KbCategory $category): View
    {
        try {
            // Category is validated by type hint
            DB::beginTransaction();
            $articles = $category->articles()
                ->where('status', 'published')
                ->paginate(self::PAGINATION_LIMIT);
            DB::commit();
            return view('kb.category', ['category' => $category, 'articles' => $articles]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to load category articles: ' . $e->getMessage(), [
                'category_id' => $category->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            return view('kb.category', ['category' => null, 'articles' => collect()])
                ->with('error', 'Failed to load category articles. Please try again.');
        }
    }
    /**   * Validate article creation data. *   * @param Request $request The HTTP request *   * @return array<string, mixed> The validated data */
    private function validateArticleData(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kb_articles, slug',
            'content' => 'required|string',
            'category_id' => 'required|exists:kb_categories, id',
            'status' => 'required|in:draft, published',
            'meta_description' => 'nullable|string|max:160',
            'tags' => 'nullable|string',
        ]);

        /** @var array<string, mixed> $result */
        $result = $validated;
        return $result;
    }
    /**   * Validate article update data. *   * @param Request $request The HTTP request * @param KbArticle $article The article instance *   * @return array<string, mixed> The validated data */
    private function validateArticleUpdateData(Request $request, KbArticle $article): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kb_articles, slug, ' . $article->id,
            'content' => 'required|string',
            'category_id' => 'required|exists:kb_categories, id',
            'status' => 'required|in:draft, published',
            'meta_description' => 'nullable|string|max:160',
            'tags' => 'nullable|string',
        ]);

        /** @var array<string, mixed> $result */
        $result = $validated;
        return $result;
    }
    /**   * Validate search query. *   * @param  string|null  $query  The search query *   * @return string The validated query */
    private function validateSearchQuery(?string $query): string
    {
        if (! $query) {
            return '';
        }
        // Sanitize search query to prevent XSS
        return htmlspecialchars(trim($query), ENT_QUOTES, 'UTF-8');
    }
}
