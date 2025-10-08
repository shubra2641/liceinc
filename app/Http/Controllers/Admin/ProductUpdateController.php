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
     * GET /admin/product-updates?productId=1&version=1.0.0
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
            $productId = $request->get('productId');
            $query = ProductUpdate::with('product');
            if ($productId) {
                $query->where('productId', $productId);
                $product = Product::findOrFail($productId);
                $product->load('updates');
            } else {
                $product = null;
            }
            $updates = $query->orderBy('createdAt', 'desc')->paginate(20);
            $products = Product::where('isActive', true)->get();
            DB::commit();

            return view('admin.product-updates.index', [
                'updates' => $updates,
                'products' => $products,
                'productId' => $productId,
                'product' => $product
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
     * @param  Request  $request  The HTTP request containing productId parameter
     *
     * @return View The product update creation form view
     *
     * @example
     * // Access the create form:
     * GET /admin/product-updates/create?productId=1
     *
     * // Returns view with:
     * // - Product selection (if no productId provided)
     * // - Update form fields
     * // - File upload field
     * // - Version and requirements fields
     */
    public function create(Request $request): View
    {
        $productId = $request->get('productId');
        if ($productId) {
            $product = Product::findOrFail($productId);

            return view('admin.product-updates.create', ['product' => $product]);
        }
        $products = Product::where('isActive', true)->get();

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
     *     "productId": 1,
     *     "version": "1.0.1",
     *     "title": "Bug Fixes Update",
     *     "description": "Fixed critical bugs",
     *     "update_file": [file],
     *     "isMajor": false,
     *     "isRequired": true
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
            $product = Product::findOrFail($validated['productId']);
            // Check if version already exists
            $existingUpdate = ProductUpdate::where('productId', $product->id)
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
            $changelogArray = $changelogText ? array_filter(array_map('trim', explode("\n", is_string($changelogText) ? $changelogText : ''))) : [];
            // Create update record
            $update = ProductUpdate::create([
                'productId' => $product->id,
                'version' => $validated['version'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'changelog' => $changelogArray,
                'filePath' => $filePath,
                'fileName' => $fileName,
                'file_size' => $file->getSize(),
                'file_hash' => $fileHash,
                'isMajor' => $validated['isMajor'] ?? false,
                'isRequired' => $validated['isRequired'] ?? false,
                'requirements' => $validated['requirements'] ?? null,
                'compatibility' => $validated['compatibility'] ?? null,
                'releasedAt' => $validated['releasedAt'] ?? now(),
                'isActive' => true,
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
     * @param  ProductUpdate  $productUpdate  The product update to display
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
    public function show(ProductUpdate $productUpdate): View
    {
        $productUpdate->load('product');

        return view('admin.product-updates.show', ['productUpdate' => $productUpdate]);
    }

    /**
     * Show the form for editing the specified product update.
     *
     * Displays the product update editing form with pre-populated data
     * and product selection for update modification.
     *
     * @param  ProductUpdate  $productUpdate  The product update to edit
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
    public function edit(ProductUpdate $productUpdate): View
    {
        $products = Product::where('isActive', true)->get();

        return view('admin.product-updates.edit', ['productUpdate' => $productUpdate, 'products' => $products]);
    }

    /**
     * Update the specified product update with enhanced security.
     *
     * Updates an existing product update with comprehensive validation including
     * file upload handling, version checking, and proper error handling.
     *
     * @param  ProductUpdateRequest  $request  The validated request containing update data
     * @param  ProductUpdate  $productUpdate  The product update to update
     *
     * @return RedirectResponse Redirect to updates index or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Update request:
     * PUT /admin/product-updates/123
     * {
     *     "productId": 1,
     *     "version": "1.0.2",
     *     "title": "Updated Bug Fixes",
     *     "description": "Updated critical bugs",
     *     "isMajor": false,
     *     "isRequired": true
     * }
     *
     * // Success response: Redirect to updates index
     * // "Product update updated successfully."
     */
    public function update(ProductUpdateRequest $request, ProductUpdate $productUpdate): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Check if version already exists (excluding current update)
            $existingUpdate = ProductUpdate::where('productId', $validated['productId'])
                ->where('version', $validated['version'])
                ->where('id', '!=', $productUpdate->id)
                ->first();
            if ($existingUpdate) {
                DB::rollBack();

                return redirect()->back()
                    ->withErrors(['version' => 'This version already exists for this product'])
                    ->withInput();
            }
            // Convert changelog text to array
            $changelogText = $validated['changelog'] ?? null;
            $changelogArray = $changelogText ? array_filter(array_map('trim', explode("\n", is_string($changelogText) ? $changelogText : ''))) : [];
            $updateData = [
                'productId' => $validated['productId'],
                'version' => $validated['version'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'changelog' => $changelogArray,
                'isMajor' => $validated['isMajor'] ?? false,
                'isRequired' => $validated['isRequired'] ?? false,
                'isActive' => $validated['isActive'] ?? true,
                'requirements' => $validated['requirements'] ?? null,
                'compatibility' => $validated['compatibility'] ?? null,
                'releasedAt' => $validated['releasedAt'] ?? $productUpdate->releasedAt,
            ];
            // Handle file upload if provided
            if ($request->hasFile('update_file')) {
                $file = $request->file('update_file');
                $fileName = 'update_' . $productUpdate->product->slug . '_' . (is_string($validated['version'] ?? null) ? $validated['version'] : '') . '_' . time() . '.zip';
                $filePath = $file->storeAs('product-updates', $fileName);
                $fileHash = hash_file('sha256', $file->getRealPath());
                // Delete old file
                if ($productUpdate->filePath && Storage::exists($productUpdate->filePath)) {
                    Storage::delete($productUpdate->filePath);
                }
                $updateData['filePath'] = $filePath;
                $updateData['fileName'] = $fileName;
                $updateData['file_size'] = $file->getSize();
                $updateData['file_hash'] = $fileHash;
            }
            $productUpdate->update($updateData);
            DB::commit();

            return redirect()->route('admin.product-updates.index')
                ->with('success', 'Product update updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update product update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update_id' => $productUpdate->id,
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
     * @param  ProductUpdate  $productUpdate  The product update to delete
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
    public function destroy(ProductUpdate $productUpdate): RedirectResponse
    {
        try {
            DB::beginTransaction();
            // Delete file if exists
            if ($productUpdate->filePath && Storage::exists($productUpdate->filePath)) {
                Storage::delete($productUpdate->filePath);
            }
            $productUpdate->delete();
            DB::commit();

            return redirect()->route('admin.product-updates.index')
                ->with('success', 'Product update deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete product update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update_id' => $productUpdate->id,
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
     * @param  ProductUpdate  $productUpdate  The product update to toggle
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
     *     "isActive": true
     * }
     *
     * // Error response:
     * {
     *     "success": false,
     *     "message": "Failed to update status"
     * }
     */
    public function toggleStatus(ProductUpdate $productUpdate): JsonResponse
    {
        try {
            DB::beginTransaction();
            $productUpdate->update(['isActive' => ! $productUpdate->isActive]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Update status updated successfully',
                'isActive' => $productUpdate->isActive,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to toggle product update status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update_id' => $productUpdate->id,
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
     * @param  Request  $request  The HTTP request containing productId parameter
     *
     * @return JsonResponse JSON response with updates data or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // AJAX request:
     * GET /admin/product-updates/ajax?productId=1
     *
     * // Success response:
     * {
     *     "success": true,
     *     "updates": [
     *         {
     *             "id": 1,
     *             "version": "1.0.1",
     *             "title": "Bug Fixes",
     *             "isMajor": false,
     *             "isRequired": true,
     *             "isActive": true,
     *             "releasedAt": "2024-01-15 10:30:00",
     *             "file_size": "2.5 MB"
     *         }
     *     ]
     * }
     */
    public function getProductUpdates(Request $request): JsonResponse
    {
        try {
            $productId = $request->input('productId');
            if (! $productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ID is required',
                ], 400);
            }
            $updates = ProductUpdate::where('productId', $productId)
                ->orderBy('version', 'desc')
                ->get()
                ->map(function ($update) {
                    return [
                        'id' => $update->id,
                        'version' => $update->version,
                        'title' => $update->title,
                        'isMajor' => $update->isMajor,
                        'isRequired' => $update->isRequired,
                        'isActive' => $update->isActive,
                        'releasedAt' => $update->releasedAt?->format('Y-m-d H:i:s'),
                        'file_size' => $update->formattedFileSize,
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
                'productId' => $request->input('productId'),
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
     * @param  ProductUpdate  $productUpdate  The product update to download
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
    public function download(ProductUpdate $productUpdate)
    {
        try {
            if ($productUpdate->filePath === null || Storage::exists($productUpdate->filePath) === false) {
                return redirect()->back()
                    ->withErrors(['error' => 'Update file not found']);
            }

            return Storage::download($productUpdate->filePath, $productUpdate->fileName);
        } catch (\Exception $e) {
            Log::error('Failed to download product update file', [
                'update_id' => $productUpdate->id,
                'filePath' => $productUpdate->filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to download update file']);
        }
    }
}
