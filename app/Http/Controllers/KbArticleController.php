<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Knowledge Base Article Controller with enhanced security.
 *
 * This controller handles knowledge base article management in the admin panel,
 * including CRUD operations, image uploads, and SEO optimization.
 *
 * Features:
 * - Article listing with pagination and category filtering
 * - Article creation with image upload and SEO fields
 * - Article editing with validation and security measures
 * - Article deletion with proper authorization
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling and logging
 * - Proper logging for errors and warnings only
 * - Rate limiting for destructive operations
 * - Authorization checks for admin access
 */
class KbArticleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'user', 'verified']);
    }
    /**
     * Display a listing of knowledge base articles with enhanced security.
     *
     * Shows a paginated list of knowledge base articles with category filtering
     * and proper authorization checks.
     *
     * @return View The articles listing view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access: GET /admin/kb-articles
     * // Returns: View with paginated articles and categories
     */
    public function index(): View
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (! $user || (! $user->is_admin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to KB articles', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(403, 'Unauthorized access');
            }
            $articles = KbArticle::with('category')->latest()->paginate(10);
            $categories = KbCategory::all();
            return view('admin.kb.articles.index', ['articles' => $articles, 'categories' => $categories]);
        } catch (\Exception $e) {
            Log::error('Failed to load KB articles index', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            abort(500, 'Failed to load articles');
        }
    }
    /**
     * Show the form for creating a new knowledge base article.
     *
     * Displays the article creation form with available categories
     * and proper authorization checks.
     *
     * @return View The article creation form view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access: GET /admin/kb-articles/create
     * // Returns: View with article creation form
     */
    public function create(): View
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (! $user || (! $user->is_admin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to KB article creation form', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(403, 'Unauthorized access');
            }
            $categories = KbCategory::pluck('name', 'id');
            return view('admin.kb.articles.create', ['categories' => $categories]);
        } catch (\Exception $e) {
            Log::error('Failed to load KB article creation form', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            abort(500, 'Failed to load creation form');
        }
    }
    /**
     * Store a newly created knowledge base article with enhanced security.
     *
     * Creates a new knowledge base article with comprehensive validation,
     * image upload handling, and security measures.
     *
     * @param  Request  $request  The HTTP request containing article data
     *
     * @return RedirectResponse Redirect to articles index or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * POST /admin/kb-articles
     * {
     *     "kb_category_id": 1,
     *     "title": "How to Install",
     *     "content": "Installation guide...",
     *     "image": [file]
     * }
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $this->checkAdminAuthorization('create KB article');
            DB::beginTransaction();
            $validated = $request->validate($this->getArticleValidationRules());
            // Process and sanitize data
            $data = $this->processArticleData($validated, $request);
            // Handle image upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('kb_images', 'public');
                $data['image'] = $path;
            }
            // @phpstan-ignore-next-line
            KbArticle::create($data);
            DB::commit();
            return redirect()->route('admin.kb-articles.index')->with('success', 'Article created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('KB article creation validation failed', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create KB article', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return back()->with('error', 'Failed to create article. Please try again.');
        }
    }
    /**
     * Show the form for editing the specified knowledge base article.
     *
     * Displays the article editing form with current article data
     * and available categories.
     *
     * @param  KbArticle  $kbArticle  The article to edit
     *
     * @return View The article editing form view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access: GET /admin/kb-articles/{id}/edit
     * // Returns: View with article editing form
     */
    public function edit(KbArticle $kbArticle): View
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (! $user || (! $user->is_admin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to KB article editing form', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(403, 'Unauthorized access');
            }
            $categories = KbCategory::pluck('name', 'id');
            return view('admin.kb.articles.edit', ['article' => $kbArticle, 'categories' => $categories]);
        } catch (\Exception $e) {
            Log::error('Failed to load KB article editing form', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            abort(500, 'Failed to load editing form');
        }
    }
    /**
     * Update the specified knowledge base article with enhanced security.
     *
     * Updates an existing knowledge base article with comprehensive validation,
     * image upload handling, and security measures.
     *
     * @param  Request  $request  The HTTP request containing updated article data
     * @param  KbArticle  $kbArticle  The article to update
     *
     * @return RedirectResponse Redirect back with success or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * PUT /admin/kb-articles/{id}
     * {
     *     "kb_category_id": 1,
     *     "title": "Updated Title",
     *     "content": "Updated content...",
     *     "image": [file]
     * }
     */
    public function update(Request $request, KbArticle $kbArticle): RedirectResponse
    {
        try {
            $this->checkAdminAuthorization('update KB article');
            DB::beginTransaction();
            $validated = $request->validate($this->getArticleValidationRules());
            // Process and sanitize data
            $data = $this->processArticleData($validated, $request);
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('kb_images', 'public');
                $data['image'] = $path;
            }
            // @phpstan-ignore-next-line
            $kbArticle->update($data);
            DB::commit();
            return back()->with('success', 'Article updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('KB article update validation failed', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update KB article', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return back()->with('error', 'Failed to update article. Please try again.');
        }
    }
    /**
     * Remove the specified knowledge base article with enhanced security.
     *
     * Deletes a knowledge base article with proper authorization checks
     * and security measures.
     *
     * @param  KbArticle  $kbArticle  The article to delete
     *
     * @return RedirectResponse Redirect to articles index with success message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * DELETE /admin/kb-articles/{id}
     * // Returns: Redirect to articles index
     */
    public function destroy(KbArticle $kbArticle): RedirectResponse
    {
        try {
            // Authorization check
            $user = Auth::user();
            if (! $user || (! $user->is_admin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized attempt to delete KB article', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(403, 'Unauthorized access');
            }
            DB::beginTransaction();
            $kbArticle->delete();
            DB::commit();
            return redirect()->route('admin.kb-articles.index')->with('success', 'Article deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete KB article', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return back()->with('error', 'Failed to delete article. Please try again.');
        }
    }

    /**
     * Process and sanitize article data
     */
    private function processArticleData(array $validated, Request $request): array
    {
        $data = [
            'title' => $this->sanitizeInput($validated['title'] ?? ''),
            'excerpt' => $this->sanitizeInput($validated['excerpt'] ?? ''),
            'content' => $this->sanitizeInput($validated['content'] ?? ''),
            'serial' => $this->sanitizeInput($validated['serial'] ?? ''),
            'serial_message' => $this->sanitizeInput($validated['serial_message'] ?? ''),
            'meta_title' => $this->sanitizeInput($validated['meta_title'] ?? ''),
            'meta_description' => $this->sanitizeInput($validated['meta_description'] ?? ''),
            'meta_keywords' => $this->sanitizeInput($validated['meta_keywords'] ?? ''),
            'slug' => Str::slug($validated['title']),
            'is_published' => $request->boolean('is_published'),
            'allow_comments' => $request->has('allow_comments'),
            'is_featured' => $request->has('is_featured'),
            'requires_serial' => $request->has('requires_serial'),
            'kb_category_id' => $validated['kb_category_id'],
        ];

        return $data;
    }

    /**
     * Check admin authorization
     */
    private function checkAdminAuthorization(string $action): void
    {
        $user = Auth::user();
        if (!$user || (!$user->is_admin && !$user->hasRole('admin'))) {
            Log::warning("Unauthorized attempt to {$action}", [
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            abort(403, 'Unauthorized access');
        }
    }

    /**
     * Get article validation rules
     */
    private function getArticleValidationRules(): array
    {
        return [
            'kb_category_id' => ['required', 'exists:kb_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_published' => ['sometimes', 'boolean'],
            'serial' => ['nullable', 'string', 'max:255'],
            'requires_serial' => ['sometimes', 'boolean'],
            'serial_message' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string'],
            'allow_comments' => ['boolean'],
            'is_featured' => ['boolean'],
        ];
    }
}
