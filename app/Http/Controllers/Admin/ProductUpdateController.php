<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductUpdateRequest;
use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Product Update Controller with enhanced security.
 *
 * This controller handles product update management in the admin panel,
 * including CRUD operations, file uploads, and update distribution.
 * It provides comprehensive update management with security measures.
 *
 * Features:
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Product update CRUD operations
 * - File upload and management
 * - Update status management
 * - Download functionality
 * - AJAX support for dynamic operations
 */
class ProductUpdateController extends Controller
{
    /**
     * Display a listing of product updates with enhanced security.
     *
     * Shows a paginated list of product updates with filtering by product,
     * version, and status. Includes proper input sanitization and error handling.
     *
     * @param  Request  $request  The HTTP request containing filter parameters
     *
     * @return View The product updates index view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request with filters:
     * GET /admin/product-updates?product_id=1&version=1.0.0
     *
     * // Returns view with:
     * // - Paginated updates list
     * // - Product filter options
     * // - Update management options
     */
    public function index(Request $request): View
    {
        try {
            DB::beginTransaction();
            $productId = $request->get('product_id');
            $query = ProductUpdate::with('product');
            if ($productId) {
                $query->where('product_id', $productId);
                $product = Product::findOrFail($productId);
                $product->load('updates');
            } else {
                $product = null;
            }
            $updates = $query->orderBy('created_at', 'desc')->paginate(20);
            $products = Product::where('is_active', true)->get();
            DB::commit();

            return view('admin.product-updates.index', [
                'updates' => $updates,
                'products' => $products,
                'productId' => $productId,
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product updates listing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty results on error
            return view('admin.product-updates.index', [
                'updates' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'products' => collect(),
                'productId' => null,
                'product' => null,
            ]);
        }
    }

    /**
     * Show the form for creating a new product update.
     *
     * Displays the product update creation form with product selection
     * and update configuration options.
     *
     * @param  Request  $request  The HTTP request containing product_id parameter
     *
     * @return View The product update creation form view
     *
     * @example
     * // Access the create form:
     * GET /admin/product-updates/create?product_id=1
     *
     * // Returns view with:
     * // - Product selection (if no product_id provided)
     * // - Update form fields
     * // - File upload field
     * // - Version and requirements fields
     */
    public function create(Request $request): View
    {
        $productId = $request->get('product_id');
        if ($productId) {
            $product = Product::findOrFail($productId);

            return view('admin.product-updates.create', ['product' => $product]);
        }
        $products = Product::where('is_active', true)->get();

        return view('admin.product-updates.create', ['products' => $products]);
    }

    /**
     * Store a newly created product update with enhanced security.
     *
     * Creates a new product update with comprehensive validation including
     * file upload handling, version checking, and proper error handling.
     *
     * @param  ProductUpdateRequest  $request  The validated request containing update data
     *
     * @return RedirectResponse Redirect to updates index or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * POST /admin/product-updates
     * {
     *     "product_id": 1,
     *     "version": "1.0.1",
     *     "title": "Bug Fixes Update",
     *     "description": "Fixed critical bugs",
     *     "update_file": [file],
     *     "is_major": false,
     *     "is_required": true
     * }
     *
     * // Success response: Redirect to updates index
     * // "Product update created successfully."
     */
    public function store(ProductUpdateRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $product = Product::findOrFail($validated['product_id']);
            // Check if version already exists
            $existingUpdate = ProductUpdate::where('product_id', $product->id)
                ->where('version', $validated['version'])
                ->first();
            if ($existingUpdate) {
                DB::rollBack();

                return redirect()->back()
                    ->withErrors(['version' => 'This version already exists for this product'])
                    ->withInput();
            }
            // Handle file upload
            $file = $request->file('update_file');
            $version = $validated['version'] ?? '';
            $versionString = is_string($version) ? $version : '';
            $productSlug = is_string($product->slug) ? $product->slug : '';
            $fileName = 'update_' . $productSlug . '_' . $versionString . '_' . time() . '.zip';
            $filePath = $file->storeAs('product-updates', $fileName);
            $fileHash = hash_file('sha256', $file->getRealPath());
            // Convert changelog text to array
            $changelogText = $validated['changelog'] ?? null;
            $changelogArray = $changelogText
                ? array_filter(array_map(
                    'trim',
                    explode("\n", is_string($changelogText) ? $changelogText : ''),
                ))
                : [];
            // Create update record
            $update = ProductUpdate::create([
                'product_id' => $product->id,
                'version' => $validated['version'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'changelog' => $changelogArray,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'file_hash' => $fileHash,
                'is_major' => $validated['is_major'] ?? false,
                'is_required' => $validated['is_required'] ?? false,
                'requirements' => $validated['requirements'] ?? null,
                'compatibility' => $validated['compatibility'] ?? null,
                'released_at' => $validated['released_at'] ?? now(),
                'is_active' => true,
            ]);
            DB::commit();

            return redirect()->route('admin.product-updates.index')
                ->with('success', 'Product update created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create product update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['update_file']),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create product update'])
                ->withInput();
        }
    }

    /**
     * Display the specified product update.
     *
     * Shows detailed information about a specific product update including
     * its content, file information, and management options.
     *
     * @param  ProductUpdate  $product_update  The product update to display
     *
     * @return View The product update show view
     *
     * @example
     * // Access update details:
     * GET /admin/product-updates/123
     *
     * // Returns view with:
     * // - Update details and content
     * // - File information and download link
     * // - Action buttons (edit, delete, toggle status)
     * // - Version and compatibility information
     */
    public function show(ProductUpdate $product_update): View
    {
        $product_update->load('product');

        return view('admin.product-updates.show', ['product_update' => $product_update]);
    }

    /**
     * Show the form for editing the specified product update.
     *
     * Displays the product update editing form with pre-populated data
     * and product selection for update modification.
     *
     * @param  ProductUpdate  $product_update  The product update to edit
     *
     * @return View The product update edit form view
     *
     * @example
     * // Access the edit form:
     * GET /admin/product-updates/123/edit
     *
     * // Returns view with:
     * // - Pre-populated update data
     * // - Editable fields (title, description, version, etc.)
     * // - File upload field (optional)
     * // - Product selection
     * // - Status toggles
     */
    public function edit(ProductUpdate $product_update): View
    {
        $products = Product::where('is_active', true)->get();

        return view('admin.product-updates.edit', ['product_update' => $product_update, 'products' => $products]);
    }

    /**
     * Update the specified product update with enhanced security.
     *
     * Updates an existing product update with comprehensive validation including
     * file upload handling, version checking, and proper error handling.
     *
     * @param  ProductUpdateRequest  $request  The validated request containing update data
     * @param  ProductUpdate  $product_update  The product update to update
     *
     * @return RedirectResponse Redirect to updates index or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Update request:
     * PUT /admin/product-updates/123
     * {
     *     "product_id": 1,
     *     "version": "1.0.2",
     *     "title": "Updated Bug Fixes",
     *     "description": "Updated critical bugs",
     *     "is_major": false,
     *     "is_required": true
     * }
     *
     * // Success response: Redirect to updates index
     * // "Product update updated successfully."
     */
    public function update(ProductUpdateRequest $request, ProductUpdate $product_update): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Check if version already exists (excluding current update)
            $existingUpdate = ProductUpdate::where('product_id', $validated['product_id'])
                ->where('version', $validated['version'])
                ->where('id', '!=', $product_update->id)
                ->first();
            if ($existingUpdate) {
                DB::rollBack();

                return redirect()->back()
                    ->withErrors(['version' => 'This version already exists for this product'])
                    ->withInput();
            }
            // Convert changelog text to array
            $changelogText = $validated['changelog'] ?? null;
            $changelogArray = $changelogText
                ? array_filter(array_map(
                    'trim',
                    explode("\n", is_string($changelogText) ? $changelogText : ''),
                ))
                : [];
            $updateData = [
                'product_id' => $validated['product_id'],
                'version' => $validated['version'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'changelog' => $changelogArray,
                'is_major' => $validated['is_major'] ?? false,
                'is_required' => $validated['is_required'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
                'requirements' => $validated['requirements'] ?? null,
                'compatibility' => $validated['compatibility'] ?? null,
                'released_at' => $validated['released_at'] ?? $product_update->released_at,
            ];
            // Handle file upload if provided
            if ($request->hasFile('update_file')) {
                $file = $request->file('update_file');
                $fileName = 'update_' . $product_update->product->slug . '_'
                    . (is_string($validated['version'] ?? null) ? $validated['version'] : '')
                    . '_' . time() . '.zip';
                $filePath = $file->storeAs('product-updates', $fileName);
                $fileHash = hash_file('sha256', $file->getRealPath());
                // Delete old file
                if ($product_update->file_path && Storage::exists($product_update->file_path)) {
                    Storage::delete($product_update->file_path);
                }
                $updateData['file_path'] = $filePath;
                $updateData['file_name'] = $fileName;
                $updateData['file_size'] = $file->getSize();
                $updateData['file_hash'] = $fileHash;
            }
            $product_update->update($updateData);
            DB::commit();

            return redirect()->route('admin.product-updates.index')
                ->with('success', 'Product update updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update product update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update_id' => $product_update->id,
                'request_data' => $request->except(['update_file']),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update product update'])
                ->withInput();
        }
    }

    /**
     * Remove the specified product update with enhanced security.
     *
     * Deletes a product update with proper error handling and database
     * transaction management to ensure data integrity.
     *
     * @param  ProductUpdate  $product_update  The product update to delete
     *
     * @return RedirectResponse Redirect to updates index or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Delete update:
     * DELETE /admin/product-updates/123
     *
     * // Success response: Redirect to updates list
     * // "Product update deleted successfully."
     *
     * // Error response: Redirect back with error
     * // "Failed to delete product update. Please try again."
     */
    public function destroy(ProductUpdate $product_update): RedirectResponse
    {
        try {
            DB::beginTransaction();
            // Delete file if exists
            if ($product_update->file_path && Storage::exists($product_update->file_path)) {
                Storage::delete($product_update->file_path);
            }
            $product_update->delete();
            DB::commit();

            return redirect()->route('admin.product-updates.index')
                ->with('success', 'Product update deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete product update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update_id' => $product_update->id,
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete product update']);
        }
    }

    /**
     * Toggle update status with enhanced security.
     *
     * Toggles the active status of a product update with proper error
     * handling and database transaction management.
     *
     * @param  ProductUpdate  $product_update  The product update to toggle
     *
     * @return JsonResponse JSON response with success or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Toggle update status:
     * POST /admin/product-updates/123/toggle
     *
     * // Success response:
     * {
     *     "success": true,
     *     "message": "Update status updated successfully",
     *     "is_active": true
     * }
     *
     * // Error response:
     * {
     *     "success": false,
     *     "message": "Failed to update status"
     * }
     */
    public function toggleStatus(ProductUpdate $product_update): JsonResponse
    {
        try {
            DB::beginTransaction();
            $product_update->update(['is_active' => ! $product_update->is_active]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Update status updated successfully',
                'is_active' => $product_update->is_active,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to toggle product update status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update_id' => $product_update->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
            ], 500);
        }
    }

    /**
     * Get product updates for AJAX with enhanced security.
     *
     * Retrieves product updates for a specific product via AJAX request
     * with proper validation and error handling.
     *
     * @param  Request  $request  The HTTP request containing product_id parameter
     *
     * @return JsonResponse JSON response with updates data or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // AJAX request:
     * GET /admin/product-updates/ajax?product_id=1
     *
     * // Success response:
     * {
     *     "success": true,
     *     "updates": [
     *         {
     *             "id": 1,
     *             "version": "1.0.1",
     *             "title": "Bug Fixes",
     *             "is_major": false,
     *             "is_required": true,
     *             "is_active": true,
     *             "released_at": "2024-01-15 10:30:00",
     *             "file_size": "2.5 MB"
     *         }
     *     ]
     * }
     */
    public function getProductUpdates(Request $request): JsonResponse
    {
        try {
            $productId = $request->input('product_id');
            if (! $productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ID is required',
                ], 400);
            }
            $updates = ProductUpdate::where('product_id', $productId)
                ->orderBy('version', 'desc')
                ->get()
                ->map(function ($update) {
                    return [
                        'id' => $update->id,
                        'version' => $update->version,
                        'title' => $update->title,
                        'is_major' => $update->is_major,
                        'is_required' => $update->is_required,
                        'is_active' => $update->is_active,
                        'released_at' => $update->released_at?->format('Y-m-d H:i:s'),
                        'file_size' => $update->formatted_file_size,
                    ];
                });

            return response()->json([
                'success' => true,
                'updates' => $updates,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get product updates', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'product_id' => $request->input('product_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get product updates',
            ], 500);
        }
    }

    /**
     * Download the update file with enhanced security.
     *
     * Downloads the update file for a specific product update with proper
     * file validation and error handling.
     *
     * @param  ProductUpdate  $product_update  The product update to download
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
     *
     * @throws \Exception When file operations fail
     *
     * @example
     * // Download update file:
     * GET /admin/product-updates/123/download
     *
     * // Success response: File download stream
     * // Error response: Redirect back with error message
     */
    public function download(ProductUpdate $product_update)
    {
        try {
            if ($product_update->file_path === null || Storage::exists($product_update->file_path) === false) {
                return redirect()->back()
                    ->withErrors(['error' => 'Update file not found']);
            }

            return Storage::download($product_update->file_path, $product_update->file_name);
        } catch (\Exception $e) {
            Log::error('Failed to download product update file', [
                'update_id' => $product_update->id,
                'file_path' => $product_update->file_path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to download update file']);
        }
    }
}
