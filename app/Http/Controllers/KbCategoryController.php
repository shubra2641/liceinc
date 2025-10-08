<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KbCategory;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * Knowledge Base Category Controller with enhanced security and comprehensive category management.
 *
 * This controller provides comprehensive knowledge base category management functionality including
 * CRUD operations, hierarchical category support, article management, and enhanced security measures
 * with comprehensive error handling and logging.
 *
 * Features:
 * - Enhanced knowledge base category CRUD operations
 * - Hierarchical category support with parent-child relationships
 * - Article count tracking and management
 * - Product association and serial protection
 * - SEO metadata management
 * - Comprehensive error handling and logging
 * - Input validation and sanitization
 * - Enhanced security measures for category operations
 * - Database transaction support for data integrity
 * - Proper error responses for different scenarios
 * - Comprehensive logging for security monitoring
 *
 *
 * @example
 * // List categories
 * GET /admin/kb-categories
 *
 * // Create category
 * POST /admin/kb-categories
 * {
 *     "name": "Getting Started",
 *     "description": "Basic setup and configuration",
 *     "parent_id": null,
 *     "product_id": 1
 * }
 */
class KbCategoryController extends Controller
{
    /**
     * Display a listing of knowledge base categories with enhanced security.
     *
     * This method displays a paginated list of knowledge base categories
     * with article counts and parent relationships.
     *
     * @return View The categories index view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Display categories list
     * $view = $kbCategoryController->index();
     */
    public function index(): View
    {
        try {
            // Include articles_count for display
            $categories = KbCategory::withCount('articles')
                ->with('parent')
                ->latest()
                ->paginate(15);

            return view('admin.kb.categories.index', ['categories' => $categories]);
        } catch (Throwable $e) {
            Log::error('Failed to load knowledge base categories', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('admin.kb.categories.index', ['categories' => collect()])
                ->with('error', 'Failed to load categories. Please try again.');
        }
    }

    /**
     * Show the form for creating a new knowledge base category with enhanced security.
     *
     * This method displays the form for creating a new knowledge base category
     * with parent categories and active products.
     *
     * @return View The category creation form view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Show create form
     * $view = $kbCategoryController->create();
     */
    public function create(): View
    {
        try {
            $parents = KbCategory::pluck('name', 'id');
            $products = Product::where('is_active', true)->get();

            return view('admin.kb.categories.create', ['parents' => $parents, 'products' => $products]);
        } catch (Throwable $e) {
            Log::error('Failed to load category creation form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('admin.kb.categories.create', [
                'parents' => collect(),
                'products' => collect(),
            ])->with('error', 'Failed to load form data. Please try again.');
        }
    }

    /**
     * Store a newly created knowledge base category with enhanced security and validation.
     *
     * This method creates a new knowledge base category with comprehensive
     * validation, sanitization, and error handling.
     *
     * @param  Request  $request  The current HTTP request instance
     *
     * @return RedirectResponse Redirect response with success or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Create new category
     * $response = $kbCategoryController->store($request);
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $result = $this->transaction(function () use ($request) {
                $validated = $this->validateRequest($request, [
                    'name' => 'required|string|max:255',
                    'slug' => 'nullable|string|max:255|unique:kb_categories,slug',
                    'description' => 'nullable|string',
                    'parent_id' => 'nullable|exists:kb_categories,id',
                    'product_id' => 'nullable|exists:products,id',
                    'serial' => 'nullable|string|max:255',
                    'requires_serial' => 'sometimes|boolean',
                    'serial_message' => 'nullable|string',
                    'meta_title' => 'nullable|string|max:255',
                    'meta_description' => 'nullable|string|max:500',
                    'meta_keywords' => 'nullable|string|max:255',
                    'icon' => 'nullable|string|max:255',
                    'is_featured' => 'sometimes|boolean',
                    'is_active' => 'sometimes|boolean',
                    'sort_order' => 'nullable|integer|min:0',
                ]);
                // Sanitize input data
                $validated = $this->sanitizeCategoryData($validated);
                // Generate slug if not provided
                $validated['slug'] = $validated['slug'] ?: Str::slug(is_string($validated['name']) ? $validated['name'] : '');
                $category = KbCategory::create($validated);
                Log::debug('Knowledge base category created successfully', [
                    'category_id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ]);

                return $this->redirectWithMessage(
                    'admin.kb-categories.index',
                    'Category created successfully',
                    'success',
                );
            });
        } catch (Throwable $e) {
            Log::error('Failed to create knowledge base category', [
                'error' => $e->getMessage(),
                'input' => $this->sanitizeInput($request->all()),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Failed to create category. Please try again.']);
        }

        return $result instanceof RedirectResponse ? $result : back();
    }

    /**
     * Display the specified knowledge base category with enhanced security.
     *
     * This method displays a specific knowledge base category with its
     * articles and related information.
     *
     * @param  KbCategory  $kbCategory  The knowledge base category to display
     *
     * @return View The category detail view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Show category details
     * $view = $kbCategoryController->show($kbCategory);
     */
    public function show(KbCategory $kbCategory): View|RedirectResponse
    {
        try {
            $kbCategory->load(['articles' => function ($query) {
                if (is_object($query) && method_exists($query, 'where')) {
                    $query->where('is_active', true);
                    if (method_exists($query, 'latest')) {
                        $query->latest();
                    }
                }
            }, 'parent', 'product']);
            /** @var view-string $viewName */
            $viewName = 'admin.kb.categories.show';

            return view($viewName, ['kbCategory' => $kbCategory]);
        } catch (Throwable $e) {
            Log::error('Failed to load knowledge base category details', [
                'error' => $e->getMessage(),
                'category_id' => $kbCategory->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.kb-categories.index')
                ->with('error', 'Failed to load category details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified knowledge base category with enhanced security.
     *
     * This method displays the form for editing a knowledge base category
     * with parent categories and active products.
     *
     * @param  KbCategory  $kbCategory  The knowledge base category to edit
     *
     * @return View The category edit form view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Show edit form
     * $view = $kbCategoryController->edit($kbCategory);
     */
    public function edit(KbCategory $kbCategory): View|RedirectResponse
    {
        try {
            $parents = KbCategory::where('id', '!=', $kbCategory->id)->pluck('name', 'id');
            $products = Product::where('is_active', true)->get();

            return view('admin.kb.categories.edit', [
                'category' => $kbCategory,
                'parents' => $parents,
                'products' => $products,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to load category edit form', [
                'error' => $e->getMessage(),
                'category_id' => $kbCategory->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.kb-categories.index')
                ->with('error', 'Failed to load edit form. Please try again.');
        }
    }

    /**
     * Update the specified knowledge base category with enhanced security and validation.
     *
     * This method updates a knowledge base category with comprehensive
     * validation, sanitization, and error handling.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  KbCategory  $kbCategory  The knowledge base category to update
     *
     * @return RedirectResponse Redirect response with success or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Update category
     * $response = $kbCategoryController->update($request, $kbCategory);
     */
    public function update(Request $request, KbCategory $kbCategory): RedirectResponse
    {
        try {
            $result = $this->transaction(function () use ($request, $kbCategory) {
                $validated = $this->validateRequest($request, [
                    'name' => 'required|string|max:255',
                    'slug' => 'required|string|max:255|unique:kb_categories,slug,'.$kbCategory->id,
                    'description' => 'nullable|string',
                    'parent_id' => 'nullable|exists:kb_categories,id',
                    'product_id' => 'nullable|exists:products,id',
                    'serial' => 'nullable|string|max:255',
                    'requires_serial' => 'sometimes|boolean',
                    'serial_message' => 'nullable|string',
                    'meta_title' => 'nullable|string|max:255',
                    'meta_description' => 'nullable|string|max:500',
                    'meta_keywords' => 'nullable|string|max:255',
                    'icon' => 'nullable|string|max:255',
                    'is_featured' => 'sometimes|boolean',
                    'is_active' => 'sometimes|boolean',
                    'sort_order' => 'nullable|integer|min:0',
                ]);
                // Sanitize input data
                $validated = $this->sanitizeCategoryData($validated);
                $kbCategory->update($validated);
                Log::debug('Knowledge base category updated successfully', [
                    'category_id' => $kbCategory->id,
                    'name' => $kbCategory->name,
                    'slug' => $kbCategory->slug,
                ]);

                return back()->with('success', 'Category updated successfully');
            });
        } catch (Throwable $e) {
            Log::error('Failed to update knowledge base category', [
                'error' => $e->getMessage(),
                'category_id' => $kbCategory->id,
                'input' => $this->sanitizeInput($request->all()),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Failed to update category. Please try again.']);
        }

        return $result instanceof RedirectResponse ? $result : back();
    }

    /**
     * Remove the specified knowledge base category with enhanced security and comprehensive handling.
     *
     * This method removes a knowledge base category with support for different
     * deletion modes and comprehensive error handling.
     *
     * @param  KbCategory  $kbCategory  The knowledge base category to delete
     *
     * @return RedirectResponse Redirect response with success or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Delete category
     * $response = $kbCategoryController->destroy($kbCategory);
     */
    public function destroy(KbCategory $kbCategory): RedirectResponse
    {
        try {
            $result = $this->transaction(function () use ($kbCategory) {
                // Support two deletion modes via request param 'delete_mode':
                // - with_articles : delete category and all its articles
                // - keep_articles : move articles to 'uncategorized' category (created if missing)
                $mode = request('delete_mode', 'keep_articles');
                if ($mode === 'with_articles') {
                    // Delete articles and category
                    $articleCount = $kbCategory->articles()->count();
                    $kbCategory->articles()->delete();
                    $kbCategory->delete();
                    Log::debug('Knowledge base category deleted with articles', [
                        'category_id' => $kbCategory->id,
                        'deleted_articles_count' => $articleCount,
                    ]);
                } else {
                    // Move articles to uncategorized (create if necessary)
                    $uncat = KbCategory::firstOrCreate(
                        ['slug' => 'uncategorized'],
                        [
                            'name' => 'Uncategorized',
                            'description' => 'Auto-created category for uncategorized articles',
                        ],
                    );
                    $movedArticlesCount = $kbCategory->articles()->count();
                    $kbCategory->articles()->update(['kb_category_id' => $uncat->id]);
                    $kbCategory->delete();
                    Log::debug('Knowledge base category deleted, articles moved to uncategorized', [
                        'category_id' => $kbCategory->id,
                        'moved_articles_count' => $movedArticlesCount,
                        'uncategorized_category_id' => $uncat->id,
                    ]);
                }

                return $this->redirectWithMessage(
                    'admin.kb-categories.index',
                    'Category deleted successfully',
                    'success',
                );
            });
        } catch (Throwable $e) {
            Log::error('Failed to delete knowledge base category', [
                'error' => $e->getMessage(),
                'category_id' => $kbCategory->id,
                'delete_mode' => request('delete_mode', 'keep_articles'),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Failed to delete category. Please try again.']);
        }

        return $result instanceof RedirectResponse ? $result : back();
    }

    /**
     * Sanitize category data to prevent XSS attacks.
     *
     * @param  array<string, mixed>  $data  The category data to sanitize
     *
     * @return array<string, mixed> The sanitized category data
     */
    private function sanitizeCategoryData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } elseif (is_bool($value) || is_int($value)) {
                $sanitized[$key] = $value;
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
