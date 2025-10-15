<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Product File Service with enhanced security and encryption.
 */
class ProductFileService
{
    public function __construct(
        private FileValidationService $validationService,
        private FileEncryptionService $encryptionService,
        private FileStorageService $storageService
    ) {
    }

    /**
     * Upload and encrypt a file for a product.
     */
    public function uploadFile(Product $product, UploadedFile $file, ?string $description = null): ProductFile
    {
        try {
            $this->validationService->validateFile($file);

            $encryptionKey = $this->encryptionService->generateEncryptionKey();
            $originalName = $this->validationService->sanitizeInput($file->getClientOriginalName());
            $extension = $this->validationService->sanitizeInput($file->getClientOriginalExtension());

            $fileContent = file_get_contents($file->getRealPath());
            if ($fileContent === false) {
                throw new \Exception('Failed to read uploaded file content');
            }

            $this->validationService->validateFileContent($fileContent);

            $checksum = $this->encryptionService->calculateChecksum($fileContent);
            $encryptedContent = $this->encryptionService->encryptContent($fileContent, $encryptionKey);

            $filePath = $this->storageService->storeEncryptedFile($product, $encryptedContent, $originalName, $extension);
            $encryptedKey = $this->encryptionService->encryptKey($encryptionKey);

            return $this->storageService->createProductFileRecord(
                $product,
                $filePath,
                $originalName,
                $extension,
                $encryptedKey,
                $checksum,
                $description
            );
        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'product_id' => $product->id,
                'file_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Download a file with permission check.
     */
    public function downloadFile(ProductFile $file, ?int $userId = null): ?array
    {
        try {
            if ($userId) {
                $permissions = $this->userCanDownloadFiles($file->product, $userId);
                if (!$permissions['can_download']) {
                    return null;
                }
            }

            if (!$this->storageService->fileExists($file->file_path)) {
                Log::error('File not found for download', [
                    'file_id' => $file->id,
                    'user_id' => $userId,
                    'file_path' => $file->file_path ?? 'unknown',
                ]);
                return null;
            }

            $encryptedContent = $this->storageService->getEncryptedFileContent($file->file_path);
            if (!$encryptedContent) {
                Log::error('Failed to retrieve encrypted file', [
                    'file_id' => $file->id,
                    'user_id' => $userId,
                ]);
                return null;
            }

            $encryptionKey = $this->encryptionService->decryptKey($file->encryption_key);
            $content = $this->encryptionService->decryptContent($encryptedContent, $encryptionKey);

            if (!$this->encryptionService->verifyChecksum($content, $file->checksum)) {
                Log::error('File checksum mismatch', [
                    'file_id' => $file->id,
                    'user_id' => $userId,
                    'expected_checksum' => $file->checksum,
                    'actual_checksum' => $this->encryptionService->calculateChecksum($content),
                ]);
                return null;
            }

            $file->incrementDownloadCount();

            return [
                'content' => $content,
                'filename' => $file->original_name,
                'mime_type' => $file->file_type,
                'size' => $file->file_size,
            ];
        } catch (\Exception $e) {
            Log::error('File download failed', [
                'file_id' => $file->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get files for a product.
     */
    public function getProductFiles(Product $product, bool $activeOnly = true): \Illuminate\Database\Eloquent\Collection
    {
        try {
            $query = $product->files();
            if ($activeOnly) {
                $query->where('is_active', true);
            }

            return $query->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            Log::error('Error getting product files', [
                'product_id' => $product->id,
                'active_only' => $activeOnly,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a product file.
     */
    public function deleteFile(ProductFile $file): bool
    {
        try {
            return $this->storageService->deleteProductFileRecord($file);
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Update file information.
     */
    public function updateFile(ProductFile $file, array $data): bool
    {
        try {
            return $this->storageService->updateProductFileRecord($file, $data);
        } catch (\Exception $e) {
            Log::error('File update failed', [
                'file_id' => $file->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if user can download files.
     */
    public function userCanDownloadFiles(Product $product, int $userId): array
    {
        try {
            // Check if user has active license
            $hasLicense = $product->licenses()
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->exists();

            if (!$hasLicense) {
                return [
                    'can_download' => false,
                    'reason' => 'No active license found'
                ];
            }

            // Check if user has paid invoice
            $hasPaidInvoice = $product->invoices()
                ->where('user_id', $userId)
                ->where('status', 'paid')
                ->exists();

            if (!$hasPaidInvoice) {
                return [
                    'can_download' => false,
                    'reason' => 'No paid invoice found'
                ];
            }

            return [
                'can_download' => true,
                'reason' => 'User has valid license and paid invoice'
            ];
        } catch (\Exception $e) {
            Log::error('Permission check failed', [
                'product_id' => $product->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'can_download' => false,
                'reason' => 'Permission check failed'
            ];
        }
    }

    /**
     * Get all product versions.
     */
    public function getAllProductVersions(Product $product, int $userId): array
    {
        $permissions = $this->userCanDownloadFiles($product, $userId);
        if (!$permissions['can_download']) {
            return [];
        }

        $allVersions = [];

        // Get all active product updates
        $updates = $product->updates()
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->get();

        foreach ($updates as $update) {
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

        return ['all_versions' => $allVersions];
    }

    /**
     * Get the latest product file.
     */
    public function getLatestProductFile(Product $product, int $userId): ?ProductFile
    {
        $permissions = $this->userCanDownloadFiles($product, $userId);
        if (!$permissions['can_download']) {
            return null;
        }

        // Get the latest update file
        $latestUpdate = $product->updates()
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->first();

        if ($latestUpdate) {
            return $this->createUpdateFileRecord($latestUpdate);
        }

        // Fallback to latest base file
        return $product->files()
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Create update file record.
     */
    private function createUpdateFileRecord($update): ProductFile
    {
        $file = new ProductFile();
        $file->id = 'update_' . $update->id;
        $file->product_id = $update->product_id;
        $file->original_name = $update->title ?? 'Update ' . $update->version;
        $file->file_type = 'zip';
        $file->file_size = 0;
        $file->description = $update->description;
        $file->is_active = $update->is_active;
        $file->created_at = $update->created_at;
        $file->updated_at = $update->updated_at;

        return $file;
    }
}
