<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * File Storage Service - Handles file storage operations.
 */
class FileStorageService
{
    /**
     * Store encrypted file.
     */
    public function storeEncryptedFile(Product $product, string $encryptedContent, string $originalName, string $extension): string
    {
        try {
            $encryptedName = Str::uuid() . '.' . $extension;
            $directory = 'product-files/' . $product->id;
            $filePath = $directory . '/' . $encryptedName;

            Storage::disk('private')->put($filePath, $encryptedContent);

            return $filePath;
        } catch (\Exception $e) {
            Log::error('Failed to store encrypted file', [
                'product_id' => $product->id,
                'original_name' => $originalName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve encrypted file content.
     */
    public function getEncryptedFileContent(string $filePath): ?string
    {
        try {
            if (!Storage::disk('private')->exists($filePath)) {
                return null;
            }

            return Storage::disk('private')->get($filePath);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve encrypted file', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Delete file from storage.
     */
    public function deleteFile(string $filePath): bool
    {
        try {
            if (Storage::disk('private')->exists($filePath)) {
                return Storage::disk('private')->delete($filePath);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete file', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if file exists.
     */
    public function fileExists(string $filePath): bool
    {
        return Storage::disk('private')->exists($filePath);
    }

    /**
     * Get file size.
     */
    public function getFileSize(string $filePath): int
    {
        try {
            return Storage::disk('private')->size($filePath);
        } catch (\Exception $e) {
            Log::error('Failed to get file size', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Create product file record.
     */
    public function createProductFileRecord(Product $product, string $filePath, string $originalName, string $extension, string $encryptedKey, string $checksum, ?string $description = null): ProductFile
    {
        return ProductFile::create([
            'product_id' => $product->id,
            'file_path' => $filePath,
            'original_name' => $originalName,
            'file_type' => $extension,
            'file_size' => $this->getFileSize($filePath),
            'encryption_key' => $encryptedKey,
            'checksum' => $checksum,
            'description' => $description,
            'is_active' => true,
        ]);
    }

    /**
     * Update product file record.
     */
    public function updateProductFileRecord(ProductFile $file, array $data): bool
    {
        try {
            return $file->update($data);
        } catch (\Exception $e) {
            Log::error('Failed to update product file record', [
                'file_id' => $file->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete product file record.
     */
    public function deleteProductFileRecord(ProductFile $file): bool
    {
        try {
            $filePath = $file->file_path;
            $deleted = $file->delete();

            if ($deleted && $filePath) {
                $this->deleteFile($filePath);
            }

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to delete product file record', [
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
