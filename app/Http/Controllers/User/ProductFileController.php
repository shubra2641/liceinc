<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFile;
use App\Services\ProductFileService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Product File Controller with enhanced security and file management.
 *
 * This controller handles secure product file downloads and management for users
 * with comprehensive license verification, invoice validation, and access control
 * mechanisms to ensure only authorized users can download product files.
 *
 * Features:
 * - Secure file download with license and invoice verification
 * - Product file listing with permission validation
 * - Update file downloads with version management
 * - Latest file download with automatic version detection
 * - Bulk file downloads as ZIP archives
 * - Comprehensive access control and authorization
 * - Enhanced error handling and security measures
 *
 * @example
 * // Download a specific product file
 * GET /user/products/{product}/files/{file}/download
 *
 * // Get all downloadable files for a product
 * GET /user/products/{product}/files
 */
class ProductFileController extends Controller
{
    /**
     * The product file service instance.
     *
     * @var ProductFileService
     */
    protected $productFileService;
    /**
     * Create a new ProductFileController instance.
     *
     * @param  ProductFileService  $productFileService  The product file service
     */
    public function __construct(ProductFileService $productFileService)
    {
        $this->productFileService = $productFileService;
    }
    /**
     * Download a product file with comprehensive security validation.
     *
     * Handles secure product file downloads with license verification, invoice validation,
     * and access control to ensure only authorized users can download files.
     *
     * @param  ProductFile  $file  The product file to download
     *
     * @return Response The file download response
     *
     * @throws \Exception When download fails or security validation errors occur
     *
     * @example
     * // Download a specific product file
     * GET /user/products/{product}/files/{file}/download
     */
    public function download(ProductFile $file): Response
    {
        try {
            // Validate inputs
            if (! $file->id) {
                throw new \InvalidArgumentException('Invalid product file provided');
            }
            // Ensure user is authenticated
            if (! auth()->check()) {
                abort(401, 'Authentication required');
            }
            // Check if product is downloadable
            if (! $file->product->is_downloadable) {
                abort(403, 'This product does not support file downloads');
            }
            // Check if file is active
            if (! $file->is_active) {
                abort(404, 'File not available');
            }
            // Check user permissions
            $permissions = $this->productFileService->userCanDownloadFiles(
                $file->product,
                auth()->id() ? (int)auth()->id() : 0
            );
            if (! $permissions['can_download']) {
                abort(403, is_string($permissions['message']) ? $permissions['message'] : 'Access denied');
            }
            $fileData = $this->productFileService->downloadFile(
                $file,
                auth()->id() ? (int)auth()->id() : 0
            );
            if (! $fileData) {
                abort(403, 'File download failed');
            }
            return response(is_string($fileData['content']) ? $fileData['content'] : '')
                ->header(
                    'Content-Type',
                    is_string($fileData['mime_type'])
                        ? $fileData['mime_type']
                        : 'application/octet-stream'
                )
                ->header(
                    'Content-Disposition',
                    'attachment; filename="'
                        . $this->sanitizeFilename(
                            is_string($fileData['filename'])
                                ? $fileData['filename']
                                : ''
                        ) . '"'
                )
                ->header('Content-Length', is_numeric($fileData['size']) ? (string)$fileData['size'] : '0')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            Log::error('User file download failed', [
                'user_id' => auth()->id() ? (int)auth()->id() : 0,
                'file_id' => $file->id ?? 'unknown',
                'product_id' => $file->product_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Download failed');
        }
    }
    /**
     * Get downloadable files for a product (user must have valid license and paid invoice).
     */
    public function index(Product $product): \Illuminate\View\View
    {
        // Ensure user is authenticated
        if (! auth()->check()) {
            abort(401, 'Authentication required');
        }
        // Check if product is downloadable
        if (! $product->is_downloadable) {
            abort(403, 'This product does not support file downloads');
        }
        // Check user permissions
        $permissions = $this->productFileService->userCanDownloadFiles($product, auth()->id() ? (int)auth()->id() : 0);
        if (! $permissions['can_download']) {
            return view('user.products.files.index', ['product' => $product])
                ->with('permissions', $permissions)
                ->with('allVersions', [])
                ->with('latestUpdate', null)
                ->with('latestFile', null);
        }
        // Get all available versions (updates + base files)
        $allVersions = $this->productFileService->getAllProductVersions($product, auth()->id() ? (int)auth()->id() : 0);
        // Get latest update information
        $latestUpdate = $product->updates()
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->first();
        // Get latest file (update or base)
        $latestFile = $this->productFileService->getLatestProductFile($product, auth()->id() ? (int)auth()->id() : 0);
        // Return view with data
        return view('user.products.files.index', [
            'product' => $product,
            'allVersions' => $allVersions,
            'permissions' => $permissions,
            'latestUpdate' => $latestUpdate,
            'latestFile' => $latestFile,
        ]);
    }
    /**
     * Download a specific update version.
     */
    public function downloadUpdate(Product $product, int $updateId): Response
    {
        // Ensure user is authenticated
        if (! auth()->check()) {
            abort(401, 'Authentication required');
        }
        // Check if product is downloadable
        if (! $product->is_downloadable) {
            abort(403, 'This product does not support file downloads');
        }
        // Check user permissions
        $permissions = $this->productFileService->userCanDownloadFiles($product, auth()->id() ? (int)auth()->id() : 0);
        if (! $permissions['can_download']) {
            abort(403, is_string($permissions['message']) ? $permissions['message'] : 'Access denied');
        }
        try {
            // Get the specific update
            $update = $product->updates()->find($updateId);
            if (! $update || ! $update->is_active) {
                abort(404, 'Update not found or not available');
            }
            if ($update->file_path === null) {
                abort(404, 'Update file not available');
            }
            $fileData = $this->productFileService->downloadUpdateFile($update, auth()->id() ? (int)auth()->id() : 0);
            // Return file download response
            return response(is_string($fileData['content']) ? $fileData['content'] : '')
                ->header(
                    'Content-Type',
                    is_string($fileData['mime_type'])
                        ? $fileData['mime_type']
                        : 'application/octet-stream'
                )
                ->header(
                    'Content-Disposition',
                    'attachment; filename="' . (
                        is_string($fileData['filename'])
                            ? $fileData['filename']
                            : ''
                    ) . '"'
                )
                ->header('Content-Length', is_numeric($fileData['size']) ? (string)$fileData['size'] : '0')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            Log::error('User update download failed', [
                'user_id' => auth()->id() ? (int)auth()->id() : 0,
                'product_id' => $product->id,
                'update_id' => $updateId,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Download failed');
        }
    }
    /**
     * Download the latest version (update or base file).
     */
    public function downloadLatest(Product $product): Response
    {
        // Ensure user is authenticated
        if (! auth()->check()) {
            abort(401, 'Authentication required');
        }
        // Check if product is downloadable
        if (! $product->is_downloadable) {
            abort(403, 'This product does not support file downloads');
        }
        // Check user permissions
        $permissions = $this->productFileService->userCanDownloadFiles($product, auth()->id() ? (int)auth()->id() : 0);
        if (! $permissions['can_download']) {
            abort(403, is_string($permissions['message']) ? $permissions['message'] : 'Access denied');
        }
        try {
            // Get the latest file (update or base)
            $latestFile = $this->productFileService->getLatestProductFile(
                $product,
                auth()->id() ? (int)auth()->id() : 0
            );
            if (! $latestFile) {
                abort(404, 'No files available for download');
            }
            // Check if it's an update file
            if (str_starts_with((string)$latestFile->id, 'update_')) {
                // It's an update file, get the update record
                $updateId = str_replace('update_', '', (string)$latestFile->id);
                $update = $product->updates()->find($updateId);
                if (! $update) {
                    abort(404, 'Update file not found');
                }
                $fileData = $this->productFileService->downloadUpdateFile(
                    $update,
                    auth()->id() ? (int)auth()->id() : 0
                );
            } else {
                // It's a regular product file
                $fileData = $this->productFileService->downloadFile(
                    $latestFile,
                    auth()->id() ? (int)auth()->id() : 0
                );
            }
            if (! $fileData) {
                abort(403, 'File download failed');
            }
            // Return file download response
            return response(is_string($fileData['content']) ? $fileData['content'] : '')
                ->header(
                    'Content-Type',
                    is_string($fileData['mime_type'])
                        ? $fileData['mime_type']
                        : 'application/octet-stream'
                )
                ->header(
                    'Content-Disposition',
                    'attachment; filename="' . (
                        is_string($fileData['filename'])
                            ? $fileData['filename']
                            : ''
                    ) . '"'
                )
                ->header('Content-Length', is_numeric($fileData['size']) ? (string)$fileData['size'] : '0')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            Log::error('User latest file download failed', [
                'user_id' => auth()->id() ? (int)auth()->id() : 0,
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Download failed');
        }
    }
    /**
     * Download all files as a ZIP archive.
     */
    public function downloadAll(Product $product): Response
    {
        // Ensure user is authenticated
        if (! auth()->check()) {
            abort(401, 'Authentication required');
        }
        // Check if product is downloadable
        if (! $product->is_downloadable) {
            abort(403, 'This product does not support file downloads');
        }
        // Check user permissions
        $permissions = $this->productFileService->userCanDownloadFiles($product, auth()->id() ? (int)auth()->id() : 0);
        if (! $permissions['can_download']) {
            abort(403, is_string($permissions['message']) ? $permissions['message'] : 'Access denied');
        }
        $files = $this->productFileService->getProductFiles($product, true);
        if ($files->isEmpty()) {
            abort(404, 'No files available for download');
        }
        try {
            // Create temporary ZIP file
            $zipFileName = $product->slug . '_files_' . now()->format('Y-m-d_H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);
            // Ensure temp directory exists
            if (! file_exists(dirname($zipPath))) {
                mkdir(dirname($zipPath), 0755, true);
            }
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                abort(500, 'Cannot create ZIP file');
            }
            $addedFiles = 0;
            foreach ($files as $file) {
                $fileData = $this->productFileService->downloadFile(
                    $file,
                    auth()->id() ? (int)auth()->id() : 0
                );
                if ($fileData) {
                    $zip->addFromString(
                        $file->original_name,
                        is_string($fileData['content']) ? $fileData['content'] : ''
                    );
                    $addedFiles++;
                }
            }
            $zip->close();
            if ($addedFiles === 0) {
                unlink($zipPath);
                abort(500, 'No files could be added to ZIP');
            }
            // Return ZIP download response
            return new Response(file_get_contents($zipPath), 200, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"'
            ]);
        } catch (\Exception $e) {
            Log::error('ZIP download failed', [
                'user_id' => auth()->id() ? (int)auth()->id() : 0,
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'ZIP creation failed');
        }
    }
    /**
     * Sanitize filename for secure download headers.
     *
     * Removes potentially dangerous characters from filenames to prevent
     * header injection attacks and ensure safe file downloads.
     *
     * @param  string  $filename  The filename to sanitize
     *
     * @return string The sanitized filename
     */
    private function sanitizeFilename(?string $filename): string
    {
        try {
            if (empty($filename)) {
                return 'download';
            }
            // Remove null bytes and control characters
            $filename = str_replace(["\0", "\x00"], '', $filename);
            // Remove potentially dangerous characters
            $filename = preg_replace('/[^\w\-_\.]/', '_', $filename);
            // Limit filename length
            if ($filename && strlen($filename) > 255) {
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $name = pathinfo($filename, PATHINFO_FILENAME);
                $filename = substr($name, 0, 255 - strlen($extension) - 1) . '.' . $extension;
            }
            return $filename ?? '';
        } catch (\Exception $e) {
            Log::error('Error sanitizing filename', [
                'original_filename' => $filename ?: 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 'download';
        }
    }
}
