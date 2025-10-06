<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductFileRequest;
use App\Models\Product;
use App\Models\ProductFile;
use App\Services\ProductFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
/**
 * Product File Controller with enhanced security and compliance.
 *
 * This controller handles product file management functionality including
 * file uploads, downloads, updates, and deletion with comprehensive security measures.
 *
 * Features:
 * - File upload and management with comprehensive validation
 * - Secure file downloads with access control and rate limiting
 * - File status management (activate/deactivate) with validation
 * - File statistics and analytics with error handling
 * - Comprehensive error handling with database transactions
 * - Enhanced security measures (XSS protection, input validation, rate limiting)
 * - Proper logging for errors and warnings only
 * - Request class integration for better validation
 * - CSRF protection and security headers
 * - Model scope integration for optimized queries
 */
class ProductFileController extends Controller
{
    protected $productFileService;
    /**
     * Constructor with dependency injection.
     *
     * Initializes the ProductFileController with the required ProductFileService
     * for handling file operations and business logic.
     *
     * @param  ProductFileService  $productFileService  The service for handling file operations
     */
    public function __construct(ProductFileService $productFileService)
    {
        $this->productFileService = $productFileService;
    }
    /**
     * Display files for a product with enhanced security.
     *
     * Shows a comprehensive list of files associated with a product
     * including file details, status, and management options.
     *
     * @param  Product  $product  The product to display files for
     *
     * @return View The product files index view with file data
     *
     * @throws \Exception When file retrieval fails
     *
     * @example
     * // Display product files:
     * GET /admin/products/123/files
     *
     * // Returns view with:
     * // - Product information
     * // - File list with details
     * // - Management options
     * // - Statistics and analytics
     */
    public function index(Product $product): View
    {
        try {
            $files = $this->productFileService->getProductFiles($product, false);
            return view('admin.products.files.index', compact('product', 'files'));
        } catch (\Exception $e) {
            Log::error('Product files listing failed', [
                'product_id' => $product->id,
                'error'  => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return view with empty files collection
            return view('admin.products.files.index', [
                'product' => $product,
                'files'  => collect(),
            ]);
        }
    }
    /**
     * Store a newly uploaded file with enhanced security.
     *
     * Uploads a new file for a product with comprehensive validation,
     * rate limiting, and security measures.
     *
     * @param  ProductFileStoreRequest  $request  The validated request containing file data
     * @param  Product  $product  The product to attach the file to
     *
     * @return JsonResponse JSON response with upload results
     *
     * @throws \Exception When file upload operations fail
     *
     * @example
     * // Upload a new file:
     * POST /admin/products/123/files
     * {
     *     "file": [file upload],
     *     "description": "Product documentation"
     * }
     *
     * // Returns JSON with:
     * // - Success status
     * // - File details
     * // - Upload confirmation
     */
    public function store(ProductFileRequest $request, Product $product): JsonResponse
    {
        // Rate limiting for file uploads
        $key = 'product-file-upload:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'success'  => false,
                'message' => 'Too many upload attempts. Please try again later.',
            ], 429);
        }
        RateLimiter::hit($key, 300); // 5 minutes
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $file = $this->productFileService->uploadFile(
                $product,
                $request->file('file'),
                $validated['description'],
            );
            DB::commit();
            return response()->json([
                'success'  => true,
                'message' => 'File uploaded successfully',
                'file'  => [
                    'id' => $file->id,
                    'original_name'  => $file->original_name,
                    'file_size' => $file->formatted_size,
                    'file_type'  => $file->file_type,
                    'description' => $file->description,
                    'download_count'  => $file->download_count,
                    'created_at' => $file->created_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('File upload failed', [
                'product_id' => $product->id,
                'error'  => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success'  => false,
                'message' => 'File upload failed. Please try again.',
            ], 500);
        }
    }
    /**
     * Download a file with enhanced security and rate limiting.
     *
     * Downloads a product file with comprehensive security measures,
     * access control, and rate limiting to prevent abuse.
     *
     * @param  ProductFile  $file  The file to download
     *
     * @return \Symfony\Component\HttpFoundation\Response File download response
     *
     * @throws \Exception When download operations fail
     *
     * @example
     * // Download a file:
     * GET /admin/product-files/123/download
     *
     * // Returns file download with:
     * // - Proper headers
     * // - Security validation
     * // - Access control
     */
    public function download(ProductFile $file)
    {
        // Rate limiting for file downloads
        $key = 'product-file-download:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            abort(429, 'Too many download attempts. Please try again later.');
        }
        RateLimiter::hit($key, 300); // 5 minutes
        try {
            $fileData = $this->productFileService->downloadFile($file, auth()->id());
            if (! $fileData) {
                abort(404, 'File not found or access denied');
            }
            return response($fileData['content'])
                ->header('Content-Type', $fileData['mime_type'])
                ->header('Content-Disposition', 'attachment; filename="'.$fileData['filename'].'"')
                ->header('Content-Length', $fileData['size'])
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            Log::error('File download failed', [
                'file_id' => $file->id,
                'error'  => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Download failed');
        }
    }
    /**
     * Update file status and description with enhanced security.
     *
     * Updates a product file's status (activate/deactivate) and description
     * with comprehensive validation and security measures.
     *
     * @param  ProductFileUpdateRequest  $request  The validated request containing update data
     * @param  ProductFile  $file  The file to update
     *
     * @return JsonResponse JSON response with update results
     *
     * @throws \Exception When update operations fail
     *
     * @example
     * // Update file status:
     * PUT /admin/product-files/123
     * {
     *     "is_active": true,
     *     "description": "Updated description"
     * }
     *
     * // Returns JSON with:
     * // - Success status
     * // - Updated file details
     * // - Confirmation message
     */
    public function update(ProductFileRequest $request, ProductFile $file): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $file->update([
                'is_active' => $validated['is_active'],
                'description' => $validated['description'],
            ]);
            DB::commit();
            return response()->json([
                'success'  => true,
                'message' => 'File updated successfully',
                'file'  => [
                    'id' => $file->id,
                    'is_active'  => $file->is_active,
                    'description' => $file->description,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('File update failed', [
                'file_id' => $file->id,
                'error'  => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success'  => false,
                'message' => 'File update failed. Please try again.',
            ], 500);
        }
    }
    /**
     * Delete a file with enhanced security and rate limiting.
     *
     * Deletes a product file with comprehensive security measures,
     * access control, and rate limiting to prevent abuse.
     *
     * @param  ProductFile  $file  The file to delete
     *
     * @return JsonResponse JSON response with deletion results
     *
     * @throws \Exception When deletion operations fail
     *
     * @example
     * // Delete a file:
     * DELETE /admin/product-files/123
     *
     * // Returns JSON with:
     * // - Success status
     * // - Deletion confirmation
     * // - Error details if failed
     */
    public function destroy(ProductFile $file): JsonResponse
    {
        // Rate limiting for file deletions
        $key = 'product-file-delete:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many deletion attempts. Please try again later.',
            ], 429);
        }
        RateLimiter::hit($key, 600); // 10 minutes
        try {
            DB::beginTransaction();
            $success = $this->productFileService->deleteFile($file);
            if ($success) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully',
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete file',
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('File deletion failed', [
                'file_id'  => $file->id,
                'error' => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'File deletion failed. Please try again.',
            ], 500);
        }
    }
    /**
     * Get comprehensive file statistics for a product with enhanced security.
     *
     * Retrieves detailed statistics about product files including counts,
     * sizes, and download metrics with proper error handling.
     *
     * @param  Product  $product  The product to get statistics for
     *
     * @return JsonResponse JSON response with file statistics
     *
     * @throws \Exception When statistics retrieval fails
     *
     * @example
     * // Get file statistics:
     * GET /admin/products/123/files/statistics
     *
     * // Returns JSON with:
     * // - Total file count
     * // - Active file count
     * // - Total downloads
     * // - Total size (bytes and formatted)
     * // - Success status
     */
    public function statistics(Product $product): JsonResponse
    {
        try {
            $files = $product->files;
            $stats = [
                'total_files' => $files->count(),
                'active_files' => $files->where('is_active', true)->count(),
                'total_downloads' => $files->sum('download_count'),
                'total_size' => $files->sum('file_size'),
                'formatted_total_size' => $this->formatBytes($files->sum('file_size')),
            ];
            return response()->json([
                'success'  => true,
                'statistics' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('File statistics retrieval failed', [
                'product_id' => $product->id,
                'error'  => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success'  => false,
                'message' => 'Failed to retrieve file statistics',
            ], 500);
        }
    }
    /**
     * Format bytes to human readable format with enhanced precision.
     *
     * Converts byte values to human-readable format (B, KB, MB, GB, TB)
     * with configurable precision for better display.
     *
     * @param  int  $bytes  The number of bytes to format
     * @param  int  $precision  The number of decimal places (default: 2)
     *
     * @return string The formatted byte string with unit
     *
     * @example
     * // Format bytes:
     * $this->formatBytes(1024); // Returns "1 KB"
     * $this->formatBytes(1048576); // Returns "1 MB"
     * $this->formatBytes(1073741824, 3); // Returns "1.000 GB"
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitsCount = count($units);
        for ($i = 0; $bytes > 1024 && $i < $unitsCount - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision).' '.$units[$i];
    }
}
