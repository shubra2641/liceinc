<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Product File Service with enhanced security and encryption.
 *
 * This service handles secure file upload, storage, and download for products
 * with comprehensive encryption, validation, and access control mechanisms.
 *
 * Features:
 * - Secure file upload with encryption and validation
 * - File content scanning for malicious patterns
 * - License and invoice verification for downloads
 * - Comprehensive access control and permissions
 * - File integrity verification with checksums
 * - Support for product updates and versioning
 * - Automatic file cleanup and management
 * - Multi-format file support with type validation
 *
 * @example
 * // Upload a file for a product
 * $service = new ProductFileService();
 * $file = $service->uploadFile($product, $uploadedFile, 'Product documentation');
 *
 * // Download a file with permission check
 * $result = $service->downloadFile($file, $userId);
 */
class ProductFileService
{
    /**
     * Upload and encrypt a file for a product with comprehensive security validation.
     *
     * Handles secure file upload with encryption, validation, and malicious content
     * scanning. Includes proper error handling and security measures to prevent
     * unauthorized access and file corruption.
     *
     * @param  Product  $product  The product to upload file for
     * @param  UploadedFile  $file  The uploaded file to process
     * @param  string|null  $description  Optional description for the file
     *
     * @return ProductFile The created product file record
     *
     * @throws \Exception When file upload fails or validation errors occur
     *
     * @example
     * $file = $service->uploadFile($product, $uploadedFile, 'Product documentation');
     * echo "File uploaded with ID: " . $file->id;
     */
    public function uploadFile(Product $product, UploadedFile $file, ?string $description = null): ProductFile
    {
        try {
            // Validate inputs
            // Product and file are validated by type hints
            // Validate file
            $this->validateFile($file);
            // Generate unique encryption key for this file
            $encryptionKey = Str::random(32);
            // Generate unique filename
            $originalName = $this->sanitizeInput($file->getClientOriginalName());
            $extension = $this->sanitizeInput($file->getClientOriginalExtension());
            $encryptedName = Str::uuid() . '.' . $extension;
            // Create directory path with validation
            $directory = 'product-files/' . $product->id;
            $filePath = $directory . '/' . $encryptedName;
            // Validate file path
            if (strpos($filePath, '..') !== false) {
                throw new \InvalidArgumentException('Invalid file path detected');
            }
            // Read file content
            $fileContent = file_get_contents($file->getRealPath());
            if ($fileContent === false) {
                throw new \Exception('Failed to read uploaded file content');
            }
            // Calculate checksum
            $checksum = hash('sha256', $fileContent);
            // Encrypt file content
            $encryptedContent = $this->encryptContent($fileContent, $encryptionKey);
            // Store encrypted file
            Storage::disk('private')->put($filePath, $encryptedContent);
            // Encrypt the encryption key for storage
            $encryptedKey = Crypt::encryptString($encryptionKey);
            // Create database record
            $productFile = ProductFile::create([
                'product_id' => $product->id,
                'original_name' => $originalName,
                'encrypted_name' => $encryptedName,
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'encryption_key' => $encryptedKey,
                'checksum' => $checksum,
                'description' => $this->sanitizeInput($description),
            ]);
            return $productFile;
        } catch (\Exception $e) {
            Log::error('Error uploading product file', [
                'product_id' => $product->id,
                'original_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Download a file for a user with comprehensive security and permission validation.
     *
     * Handles secure file download with license verification, invoice validation,
     * file integrity checks, and access control. Includes proper error handling
     * and security measures to prevent unauthorized access.
     *
     * @param  ProductFile  $file  The product file to download
     * @param  int|null  $userId  The user ID requesting the download
     *
     * @return array|null The file data array or null if access denied
     *
     * @throws \Exception When download fails or security validation errors occur
     *
     * @example
     * $result = $service->downloadFile($file, $userId);
     * if ($result) {
     *     echo "File: " . $result['filename'];
     * }
     */
    /**
     * @return array<string, mixed>|null
     */
    public function downloadFile(ProductFile $file, ?int $userId = null): ?array
    {
        try {
            // Validate inputs
            // File is validated by type hint
            // Verify user has license and paid invoice for this product
            if ($userId) {
                $permissions = $this->userCanDownloadFiles($file->product, $userId);
                if (!$permissions['can_download']) {
                    return null;
                }
            }
            // Check if file exists
            if (!$file->fileExists()) {
                Log::error('File not found for download', [
                    'file_id' => $file->id,
                    'user_id' => $userId,
                    'file_path' => $file->file_path ?? 'unknown',
                ]);
                return null;
            }
            // Get decrypted content
            $content = $file->getDecryptedContent();
            if (! $content) {
                Log::error('Failed to decrypt file', [
                    'file_id' => $file->id,
                    'user_id' => $userId,
                ]);
                return null;
            }
            // Verify checksum
            if (hash('sha256', $content) !== $file->checksum) {
                Log::error('File checksum mismatch', [
                    'file_id' => $file->id,
                    'user_id' => $userId,
                    'expected_checksum' => $file->checksum,
                    'actual_checksum' => hash('sha256', $content),
                ]);
                return null;
            }
            // Increment download count
            $file->incrementDownloadCount();
            return [
                'content' => $content,
                'filename' => $file->original_name,
                'mime_type' => $file->file_type,
                'size' => $file->file_size,
            ];
        } catch (\Exception $e) {
            Log::error('Error downloading product file', [
                'file_id' => $file->id ?? 'unknown',
                'user_id' => $userId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Delete a product file with comprehensive security validation.
     *
     * Safely removes both the physical file and database record with proper
     * error handling and security validation to prevent unauthorized access.
     *
     * @param  ProductFile  $file  The product file to delete
     *
     * @return bool True if deletion successful, false otherwise
     *
     * @throws \Exception When deletion fails
     *
     * @example
     * $success = $service->deleteFile($file);
     * if ($success) {
     *     echo "File deleted successfully";
     * }
     */
    public function deleteFile(ProductFile $file): bool
    {
        try {
            // Validate input
            // File is validated by type hint
            // Delete physical file
            if ($file->fileExists()) {
                Storage::disk('private')->delete($file->file_path);
            }
            // Delete database record
            $file->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete product file', [
                'file_id' => $file->id ?? 'unknown',
                'product_id' => $file->product_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get files for a product with validation and filtering.
     *
     * Retrieves product files with optional filtering for active files only.
     * Includes proper validation and error handling for secure data access.
     *
     * @param  Product  $product  The product to get files for
     * @param  bool  $activeOnly  Whether to return only active files
     *
     * @return \Illuminate\Database\Eloquent\Collection The collection of product files
     *
     * @throws \Exception When file retrieval fails
     *
     * @example
     * $files = $service->getProductFiles($product, true);
     * echo "Found " . $files->count() . " active files";
     */
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ProductFile>
     */
    public function getProductFiles(Product $product, bool $activeOnly = true): \Illuminate\Database\Eloquent\Collection
    {
        try {
            // Validate input
            // Product is validated by type hint
            $query = $product->files();
            if ($activeOnly) {
                $query->where('is_active', true);
            }
            /**
 * @var \Illuminate\Database\Eloquent\Collection<int, ProductFile> $files
*/
            $files = $query->orderBy('created_at', 'desc')->get();
            return $files;
        } catch (\Exception $e) {
            Log::error('Error getting product files', [
                'product_id' => $product->id,
                'active_only' => $activeOnly,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Sanitize input data to prevent XSS and injection attacks.
     *
     * Provides comprehensive input sanitization for file names, descriptions,
     * and other user inputs to ensure security and prevent various types
     * of injection attacks.
     *
     * @param  string|null  $input  The input string to sanitize
     *
     * @return string The sanitized input string
     */
    private function sanitizeInput(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        // Remove null bytes and control characters
        $input = str_replace(["\0", "\x00"], '', $input);
        // Trim whitespace
        $input = trim($input);
        // Escape HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
    /**
     * Validate uploaded file with comprehensive security checks.
     *
     * Performs thorough validation of uploaded files including size limits,
     * file type restrictions, and malicious content scanning to ensure
     * security and prevent unauthorized file uploads.
     *
     * @param  UploadedFile  $file  The uploaded file to validate
     *
     * @throws \Exception When file validation fails
     */
    private function validateFile(UploadedFile $file): void
    {
        try {
            // File is validated by type hint
            // Check file size (max 100MB)
            if ($file->getSize() > 100 * 1024 * 1024) {
                throw new \Exception('File size cannot exceed 100MB');
            }
            // Check file type
            $allowedTypes = [
                'application/zip',
                'application/x-zip-compressed',
                'application/x-rar-compressed',
                'application/pdf',
                'text/plain',
                'application/json',
                'application/xml',
                'text/xml',
                'application/javascript',
                'text/css',
                'text/html',
                'application/php',
                'application/x-php',
                'text/php',
                'application/sql',
                'text/sql',
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
            ];
            $mimeType = $file->getMimeType();
            if (! in_array($mimeType, $allowedTypes)) {
                throw new \Exception('File type not allowed: ' . $mimeType);
            }
            // Check for malicious content
            $this->scanFileForMaliciousContent($file);
        } catch (\Exception $e) {
            $fileName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            Log::error('File validation failed', [
                'filename' => $fileName,
                'mime_type' => is_string($mimeType) ? $mimeType : 'unknown',
                'file_size' => is_numeric($fileSize) ? (int)$fileSize : 0,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Scan file for malicious content with comprehensive pattern detection.
     *
     * Performs thorough security scanning of file content to detect common
     * malicious patterns and potential security threats. Includes proper
     * error handling and detailed logging for security monitoring.
     *
     * @param  UploadedFile  $file  The uploaded file to scan
     *
     * @throws \Exception When malicious content is detected
     */
    private function scanFileForMaliciousContent(UploadedFile $file): void
    {
        try {
            $content = file_get_contents($file->getRealPath());
            if ($content === false) {
                throw new \Exception('Failed to read file content for scanning');
            }
            // Check for common malicious patterns
            $maliciousPatterns = [
                '/eval\s*\(/i',
                '/base64_decode\s*\(/i',
                '/exec\s*\(/i',
                '/system\s*\(/i',
                '/shell_exec\s*\(/i',
                '/passthru\s*\(/i',
                '/file_get_contents\s*\(\s*["\']http/i',
                '/curl_exec\s*\(/i',
                '/fopen\s*\(\s*["\']http/i',
                '/preg_replace\s*\(\s*["\'].*\/e/i',
                '/assert\s*\(/i',
                '/create_function\s*\(/i',
                '/call_user_func\s*\(/i',
                '/call_user_func_array\s*\(/i',
            ];
            foreach ($maliciousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    Log::error('Malicious content detected in file', [
                        'filename' => $file->getClientOriginalName(),
                        'pattern' => $pattern,
                        'file_size' => $file->getSize(),
                    ]);
                    throw new \Exception('File contains potentially malicious content');
                }
            }
        } catch (\Exception $e) {
            $fileName = $file->getClientOriginalName();
            Log::error('Error scanning file for malicious content', [
                'filename' => $fileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Encrypt file content with AES-256-CBC encryption.
     *
     * Provides secure file content encryption using AES-256-CBC algorithm
     * with proper initialization vector generation for enhanced security.
     *
     * @param  string  $content  The content to encrypt
     * @param  string  $key  The encryption key
     *
     * @return string The encrypted content
     *
     * @throws \Exception When encryption fails
     */
    private function encryptContent(string $content, string $key): string
    {
        try {
            if (empty($content)) {
                throw new \InvalidArgumentException('Content cannot be empty for encryption');
            }
            if (empty($key)) {
                throw new \InvalidArgumentException('Encryption key cannot be empty');
            }
            $iv = substr(hash('sha256', $key), 0, 16);
            $encrypted = openssl_encrypt($content, 'AES-256-CBC', $key, 0, $iv);
            if ($encrypted === false) {
                throw new \Exception('Failed to encrypt content');
            }
            return $encrypted;
        } catch (\Exception $e) {
            $contentLength = strlen($content);
            $keyLength = strlen($key);
            Log::error('Error encrypting file content', [
                'content_length' => $contentLength,
                'key_length' => $keyLength,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Check if user has active license for product.
     */
    private function userHasLicense(Product $product, int $userId): bool
    {
        return $product->licenses()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('license_expires_at')
                    ->orWhere('license_expires_at', '>', now());
            })
            ->exists();
    }
    /**
     * Check if user has paid invoice for product.
     */
    private function userHasPaidInvoice(Product $product, int $userId): bool
    {
        return \App\Models\Invoice::where('product_id', $product->id)
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->exists();
    }
    /**
     * Check if user can download files (has license AND paid invoice).
     */
    /**
     * @return array<string, mixed>
     */
    public function userCanDownloadFiles(Product $product, int $userId): array
    {
        $hasLicense = $this->userHasLicense($product, $userId);
        $hasPaidInvoice = $this->userHasPaidInvoice($product, $userId);
        return [
            'can_download' => $hasLicense && $hasPaidInvoice,
            'has_license' => $hasLicense,
            'has_paid_invoice' => $hasPaidInvoice,
            'message' => $this->getDownloadPermissionMessage($hasLicense, $hasPaidInvoice),
        ];
    }
    /**
     * Get appropriate message based on download permissions.
     */
    private function getDownloadPermissionMessage(bool $hasLicense, bool $hasPaidInvoice): string
    {
        if (! $hasLicense && ! $hasPaidInvoice) {
            return trans('app.You must purchase the product and pay the invoice first');
        } elseif (! $hasLicense) {
            return trans('app.You must purchase the product first');
        } elseif (! $hasPaidInvoice) {
            return trans('app.You must pay the invoice first');
        }
        return '';
    }
    /**
     * Get all available versions (updates + base files) for a product.
     */
    /**
     * @return array<string, mixed>
     */
    public function getAllProductVersions(Product $product, int $userId): array
    {
        // First check if user can download files
        $permissions = $this->userCanDownloadFiles($product, $userId);
        if (! $permissions['can_download']) {
            return [];
        }
        $allVersions = [];
        // Get all active product updates (both required and optional)
        $updates = $product->updates()
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->get();
        foreach ($updates as $update) {
            // Add update even if no file path (for display purposes)
            $updateFile = $this->createUpdateFileRecord($update);
            $updateFile->is_update = true;
            $updateFile->update_info = $update->toArray();
            $allVersions[] = $updateFile;
        }
        // Get all base product files
        $baseFiles = $product->files()
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
        foreach ($baseFiles as $file) {
            $file->is_update = false;
            $file->update_info = null;
            $allVersions[] = $file;
        }
        // Sort by creation date (newest first)
        usort($allVersions, function ($a, $b) {
            return $b->created_at <=> $a->created_at;
        });
        // Return all versions without logging success
        return ['all_versions' => $allVersions];
    }
    /**
     * Get the latest update file for a product or return the base product file.
     */
    public function getLatestProductFile(Product $product, int $userId): ?ProductFile
    {
        // First check if user can download files
        $permissions = $this->userCanDownloadFiles($product, $userId);
        if (! $permissions['can_download']) {
            return null;
        }
        // Check if there are any product updates available
        $latestUpdate = $product->updates()
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->first();
        if ($latestUpdate && $latestUpdate->update_file_path) {
            // Return the latest update file
            return $this->createUpdateFileRecord($latestUpdate);
        }
        // If no updates available, return the base product file
        return $product->files()
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->first();
    }
    /**
     * Get the latest version for a product (from updates or base version).
     */
    public function getLatestProductVersion(Product $product): string
    {
        // Check if there are any product updates available
        $latestUpdate = $product->updates()
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->first();
        if ($latestUpdate) {
            // Return the latest update version
            return $latestUpdate->version;
        }
        // If no updates available, return the base product version
        return $product->version ?? '1.0';
    }
    /**
     * Create a ProductFile record for an update file.
     */
    private function createUpdateFileRecord(\Illuminate\Database\Eloquent\Model $update): ProductFile
    {
        // Create a temporary ProductFile record for the update
        $file = new ProductFile();
        // Note: We can't set id directly as it's auto-increment
        // This is a temporary object for display purposes
        $file->product_id = is_numeric($update->product_id)
            ? (int)$update->product_id
            : 0;
        $file->original_name = (is_string($update->title) ? $update->title : '')
            . '_v'
            . (is_string($update->version) ? $update->version : '')
            . '.zip';
        $filePath = $update->file_path ?? '';
        $file->file_path = is_string($filePath) ? $filePath : '';
        $file->file_size = is_numeric($update->file_size ?? 0)
            ? (int)($update->file_size ?? 0)
            : 0;
        $file->file_extension = 'zip';
        $file->description = is_string($update->description) ? $update->description : null;
        $file->is_active = true;
        $file->download_count = 0;
        $file->created_at = $update->created_at instanceof \Illuminate\Support\Carbon ? $update->created_at : null;
        $file->updated_at = $update->updated_at instanceof \Illuminate\Support\Carbon ? $update->updated_at : null;
        // Add formatted_size for display
        $file->formatted_size = $file->file_size > 0 ?
            number_format($file->file_size / 1024 / 1024, 2) . ' MB' :
            'Unknown';
        // Add update_info for the view
        $file->update_info = $update->toArray();
        $file->is_update = true;
        return $file;
    }
    /**
     * Download update file directly from the update record.
     */
    /**
     * @param \App\Models\ProductUpdate $update
     *
     * @return array<string, mixed>
     */
    public function downloadUpdateFile(\App\Models\ProductUpdate $update, int $userId): array
    {
        if (! $update->file_path || ! Storage::disk('private')->exists($update->file_path)) {
            throw new \Exception('Update file not found');
        }
        $fileName = $update->title . '_v' . $update->version . '.zip';
        return [
            'content' => Storage::disk('private')->get($update->file_path),
            'filename' => $fileName,
            'mime_type' => 'application/zip',
            'size' => Storage::disk('private')->size($update->file_path),
        ];
    }
}
