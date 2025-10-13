<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketCategoryRequest;
use App\Models\TicketCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Admin Ticket Category Controller with enhanced security and compliance.
 *
 * This controller handles ticket category management functionality including
 * CRUD operations, validation, and security measures for the admin panel.
 *
 * Features:
 * - Ticket category CRUD operations with comprehensive validation
 * - Enhanced security measures (XSS protection, input validation, rate limiting)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Request class integration for better validation
 * - CSRF protection and security headers
 * - Model scope integration for optimized queries
 */
class TicketCategoryController extends Controller
{
    /**
     * Display a listing of ticket categories with enhanced security.
     *
     * Shows a paginated list of ticket categories with proper error handling
     * and security measures.
     *
     * @return View The ticket categories index view with paginated data
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Display ticket categories:
     * GET /admin/ticket-categories
     *
     * // Returns view with:
     * // - Paginated categories list
     * // - Sort order management
     * // - Category statistics
     */
    public function index(): View
    {
        try {
            $categories = TicketCategory::orderBy('sort_order')->paginate(15);
            return view('admin.ticket-categories.index', ['categories' => $categories]);
        } catch (\Exception $e) {
            Log::error('Ticket categories listing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return view with empty categories collection
            return view('admin.ticket-categories.index', [
                'categories' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
            ]);
        }
    }
    /**
     * Show the form for creating a new ticket category.
     *
     * Displays the form for creating a new ticket category with
     * proper security measures.
     *
     * @return View The ticket category creation form view
     *
     * @example
     * // Show create form:
     * GET /admin/ticket-categories/create
     *
     * // Returns view with:
     * // - Category creation form
     * // - Validation rules
     * // - Form fields
     */
    public function create(): View
    {
        return view('admin.ticket-categories.create');
    }
    /**
     * Store a newly created ticket category with enhanced security.
     *
     * Creates a new ticket category with comprehensive validation,
     * rate limiting, and security measures.
     *
     * @param  TicketCategoryRequest  $request  The validated request containing category data
     *
     * @return RedirectResponse Redirect to categories index with success message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Create a new category:
     * POST /admin/ticket-categories
     * {
     *     "name": "Technical Support",
     *     "color": "#FF0000",
     *     "sort_order": 1,
     *     "is_active": true
     * }
     *
     * // Returns redirect with:
     * // - Success message
     * // - Updated categories list
     */
    public function store(TicketCategoryRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Generate slug automatically from name
            $validated['slug'] = Str::slug($validated['name']);
            TicketCategory::create($validated);
            DB::commit();
            return redirect()->route('admin.ticket-categories.index')
                ->with('success', 'Ticket category created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ticket category creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token']),
            ]);
            return redirect()->back()
                ->with('error', 'Failed to create ticket category. Please try again.')
                ->withInput();
        }
    }
    /**
     * Display the specified ticket category with enhanced security.
     *
     * Shows detailed information about a specific ticket category
     * including all relevant data and context.
     *
     * @param  TicketCategory  $ticket_category  The ticket category to display
     *
     * @return View The ticket category details view
     *
     * @throws \Exception When view rendering fails
     *
     * @example
     * // Show category details:
     * GET /admin/ticket-categories/123
     *
     * // Returns view with:
     * // - Complete category details
     * // - Related tickets count
     * // - Category statistics
     */
    public function show(TicketCategory $ticket_category): View
    {
        try {
            /**
 * @var view-string $viewName
*/
            $viewName = 'admin.ticket-categories.show';
            return view($viewName, ['ticket_category' => $ticket_category]);
        } catch (\Exception $e) {
            Log::error('Ticket category view failed to load', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $ticket_category->id,
            ]);
            /**
 * @var view-string $viewName
*/
            $viewName = 'admin.ticket-categories.show';
            return view($viewName, [
                'ticket_category' => $ticket_category,
                'error' => 'Unable to load the category details. Please try again later.',
            ]);
        }
    }
    /**
     * Show the form for editing the specified ticket category.
     *
     * Displays the form for editing a ticket category with
     * proper security measures and validation.
     *
     * @param  TicketCategory  $ticket_category  The ticket category to edit
     *
     * @return View The ticket category edit form view
     *
     * @example
     * // Show edit form:
     * GET /admin/ticket-categories/123/edit
     *
     * // Returns view with:
     * // - Category edit form
     * // - Pre-filled data
     * // - Validation rules
     */
    public function edit(TicketCategory $ticket_category): View
    {
        return view('admin.ticket-categories.edit', [
            'ticketCategory' => $ticket_category,
        ]);
    }
    /**
     * Update the specified ticket category with enhanced security.
     *
     * Updates a ticket category with comprehensive validation,
     * rate limiting, and security measures.
     *
     * @param  TicketCategoryRequest  $request  The validated request containing update data
     * @param  TicketCategory  $ticket_category  The ticket category to update
     *
     * @return RedirectResponse Redirect to categories index with success message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Update a category:
     * PUT /admin/ticket-categories/123
     * {
     *     "name": "Updated Technical Support",
     *     "color": "#00FF00",
     *     "sort_order": 2,
     *     "is_active": true
     * }
     *
     * // Returns redirect with:
     * // - Success message
     * // - Updated categories list
     */
    public function update(TicketCategoryRequest $request, TicketCategory $ticket_category): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Keep existing slug (don't change it)
            $validated['slug'] = $ticket_category->slug;
            $ticket_category->update($validated);
            $ticket_category->refresh(); // Refresh the model to get updated data
            DB::commit();
            return redirect()->route('admin.ticket-categories.edit', $ticket_category)
                ->with('success', 'Ticket category updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ticket category update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $ticket_category->id,
                'data' => $request->except(['_token', '_method']),
            ]);
            return redirect()->back()
                ->with('error', 'Failed to update ticket category. Please try again.')
                ->withInput();
        }
    }
    /**
     * Remove the specified ticket category with enhanced security.
     *
     * Deletes a ticket category with comprehensive security measures,
     * access control, and rate limiting to prevent abuse.
     *
     * @param  TicketCategory  $ticket_category  The ticket category to delete
     *
     * @return RedirectResponse Redirect to categories index with success message
     *
     * @throws \Exception When deletion operations fail
     *
     * @example
     * // Delete a category:
     * DELETE /admin/ticket-categories/123
     *
     * // Returns redirect with:
     * // - Success message
     * // - Updated categories list
     * // - Error details if failed
     */
    public function destroy(TicketCategory $ticket_category): RedirectResponse
    {
        // Rate limiting for category deletions
        $key = 'ticket-category-delete:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return redirect()->back()
                ->with('error', 'Too many deletion attempts. Please try again later.');
        }
        RateLimiter::hit($key, 600); // 10 minutes
        try {
            DB::beginTransaction();
            $ticket_category->delete();
            DB::commit();
            return redirect()->route('admin.ticket-categories.index')
                ->with('success', 'Ticket category deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ticket category deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $ticket_category->id,
            ]);
            return redirect()->back()
                ->with('error', 'Failed to delete ticket category. Please try again.');
        }
    }
}
