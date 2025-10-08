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
use Illuminate\Support\Facades\RateLimiter;
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
            // Rate limiting
            $key = 'kb-articles-index:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 20)) {
                Log::warning('Rate limit exceeded for KB articles index', [
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(429, 'Too many requests');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Authorization check
            $user = Auth::user();
            if (! $user || (! $user->isAdmin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to KB articles', [
                    'userId' => Auth::id(),
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
                'userId' => Auth::id(),
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
            // Rate limiting
            $key = 'kb-articles-create:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for KB article creation form', [
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(429, 'Too many requests');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Authorization check
            $user = Auth::user();
            if (! $user || (! $user->isAdmin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to KB article creation form', [
                    'userId' => Auth::id(),
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
                'userId' => Auth::id(),
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
     *     "kbCategory_id": 1,
     *     "title": "How to Install",
     *     "content": "Installation guide...",
     *     "image": [file]
     * }
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'kb-articles-store:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 5)) {
                Log::warning('Rate limit exceeded for KB article creation', [
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return back()->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Authorization check
            $user = Auth::user();
            if (! $user || (! $user->isAdmin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized attempt to create KB article', [
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(403, 'Unauthorized access');
            }
            DB::beginTransaction();
            $validated = $request->validate([
                'kbCategory_id' => ['required', 'exists:kb_categories, id'],
                'title' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255', 'unique:kbArticles, slug'],
                'excerpt' => ['nullable', 'string'],
                'content' => ['required', 'string'],
                'image' => ['nullable', 'image', 'max:2048'],
                'is_published' => ['sometimes', 'boolean'],
                'serial' => ['nullable', 'string', 'max:255'],
                'requires_serial' => ['sometimes', 'boolean'],
                'serial_message' => ['nullable', 'string'],
                'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string', 'max:500'],
                'meta_keywords' => ['nullable', 'string', 'max:255'],
                'allow_comments' => ['boolean'],
                'is_featured' => ['boolean'],
            ]);
            // Sanitize input
            $validatedArray = is_array($validated) ? $validated : [];
            $validatedArray['title'] = $this->sanitizeInput($validatedArray['title'] ?? '');
            $validatedArray['excerpt'] = $this->sanitizeInput($validatedArray['excerpt'] ?? '');
            $validatedArray['content'] = $this->sanitizeInput($validatedArray['content'] ?? '');
            $validatedArray['serial'] = $this->sanitizeInput($validatedArray['serial'] ?? '');
            $validatedArray['serial_message'] = $this->sanitizeInput($validatedArray['serial_message'] ?? '');
            $validatedArray['meta_title'] = $this->sanitizeInput($validatedArray['meta_title'] ?? '');
            $validatedArray['meta_description'] = $this->sanitizeInput($validatedArray['meta_description'] ?? '');
            $validatedArray['meta_keywords'] = $this->sanitizeInput($validatedArray['meta_keywords'] ?? '');
            $validatedArray['slug'] = $validatedArray['slug']
                ?: Str::slug(
                    is_string($validatedArray['title'] ?? null)
                        ? $validatedArray['title']
                        : ''
                );
            $validatedArray['is_published'] = $request->boolean('is_published');
            // Handle checkbox values
            $validatedArray['allow_comments'] = $request->has('allow_comments');
            $validatedArray['is_featured'] = $request->has('is_featured');
            $validatedArray['requires_serial'] = $request->has('requires_serial');
            // Handle image upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('kb_images', 'public');
                $validatedArray['image'] = $path;
            }
            // @phpstan-ignore-next-line
            KbArticle::create($validatedArray);
            DB::commit();
            return redirect()->route('admin.kb-articles.index')->with('success', 'Article created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('KB article creation validation failed', [
                'errors' => $e->errors(),
                'userId' => Auth::id(),
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
                'userId' => Auth::id(),
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
            // Rate limiting
            $key = 'kb-articles-edit:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for KB article editing form', [
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(429, 'Too many requests');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Authorization check
            $user = Auth::user();
            if (! $user || (! $user->isAdmin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to KB article editing form', [
                    'userId' => Auth::id(),
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
                'userId' => Auth::id(),
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
     *     "kbCategory_id": 1,
     *     "title": "Updated Title",
     *     "content": "Updated content...",
     *     "image": [file]
     * }
     */
    public function update(Request $request, KbArticle $kbArticle): RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'kb-articles-update:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 5)) {
                Log::warning('Rate limit exceeded for KB article update', [
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return back()->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Authorization check
            $user = Auth::user();
            if (! $user || (! $user->isAdmin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized attempt to update KB article', [
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                abort(403, 'Unauthorized access');
            }
            DB::beginTransaction();
            $validated = $request->validate([
                'kbCategory_id' => ['required', 'exists:kb_categories, id'],
                'title' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', 'unique:kbArticles, slug, ' . $kbArticle->id],
                'excerpt' => ['nullable', 'string'],
                'content' => ['required', 'string'],
                'image' => ['nullable', 'image', 'max:2048'],
                'is_published' => ['sometimes', 'boolean'],
                'serial' => ['nullable', 'string', 'max:255'],
                'requires_serial' => ['sometimes', 'boolean'],
                'serial_message' => ['nullable', 'string'],
                'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string', 'max:500'],
                'meta_keywords' => ['nullable', 'string', 'max:255'],
                'allow_comments' => ['boolean'],
                'is_featured' => ['boolean'],
            ]);
            // Sanitize input
            $validatedArray = is_array($validated) ? $validated : [];
            $validatedArray['title'] = $this->sanitizeInput($validatedArray['title'] ?? '');
            $validatedArray['excerpt'] = $this->sanitizeInput($validatedArray['excerpt'] ?? '');
            $validatedArray['content'] = $this->sanitizeInput($validatedArray['content'] ?? '');
            $validatedArray['serial'] = $this->sanitizeInput($validatedArray['serial'] ?? '');
            $validatedArray['serial_message'] = $this->sanitizeInput($validatedArray['serial_message'] ?? '');
            $validatedArray['meta_title'] = $this->sanitizeInput($validatedArray['meta_title'] ?? '');
            $validatedArray['meta_description'] = $this->sanitizeInput($validatedArray['meta_description'] ?? '');
            $validatedArray['meta_keywords'] = $this->sanitizeInput($validatedArray['meta_keywords'] ?? '');
            $validatedArray['is_published'] = $request->boolean('is_published');
            // Handle checkbox values
            $validatedArray['allow_comments'] = $request->has('allow_comments');
            $validatedArray['is_featured'] = $request->has('is_featured');
            $validatedArray['requires_serial'] = $request->has('requires_serial');
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('kb_images', 'public');
                $validatedArray['image'] = $path;
            }
            // @phpstan-ignore-next-line
            $kbArticle->update($validatedArray);
            DB::commit();
            return back()->with('success', 'Article updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('KB article update validation failed', [
                'errors' => $e->errors(),
                'userId' => Auth::id(),
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
                'userId' => Auth::id(),
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
            // Rate limiting
            $key = 'kb-articles-destroy:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 3)) {
                Log::warning('Rate limit exceeded for KB article deletion', [
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return back()->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Authorization check
            $user = Auth::user();
            if (! $user || (! $user->isAdmin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized attempt to delete KB article', [
                    'userId' => Auth::id(),
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
                'userId' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return back()->with('error', 'Failed to delete article. Please try again.');
        }
    }
}
