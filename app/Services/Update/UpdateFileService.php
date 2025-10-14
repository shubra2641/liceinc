<?php

declare(strict_types=1);

namespace App\Services\Update;

use App\Models\ProductUpdate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Update File Service
 * 
 * Handles file operations for product updates
 */
class UpdateFileService
{
    /**
     * Store update file
     */
    public function storeFile(ProductUpdate $update, UploadedFile $file): string
    {
        try {
            $filename = $this->generateFilename($update, $file);
            $path = $this->getUpdatePath($update->product_id, $filename);
            
            $storedPath = $file->storeAs(
                $this->getUpdateDirectory($update->product_id),
                $filename,
                'private'
            );
            
            $this->updateFileInfo($update, $storedPath, $file);
            
            return $storedPath;
            
        } catch (\Exception $e) {
            Log::error('Failed to store update file', [
                'update_id' => $update->id,
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete update file
     */
    public function deleteFile(ProductUpdate $update): bool
    {
        try {
            if ($update->file_path && Storage::disk('private')->exists($update->file_path)) {
                Storage::disk('private')->delete($update->file_path);
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to delete update file', [
                'update_id' => $update->id,
                'file_path' => $update->file_path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get file download URL
     */
    public function getDownloadUrl(ProductUpdate $update): string
    {
        return route('admin.updates.download', $update->id);
    }

    /**
     * Generate filename
     */
    private function generateFilename(ProductUpdate $update, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "update_{$update->product_id}_{$update->version}_{$timestamp}.{$extension}";
    }

    /**
     * Get update path
     */
    private function getUpdatePath(int $productId, string $filename): string
    {
        return "updates/product_{$productId}/{$filename}";
    }

    /**
     * Get update directory
     */
    private function getUpdateDirectory(int $productId): string
    {
        return "updates/product_{$productId}";
    }

    /**
     * Update file information
     */
    private function updateFileInfo(ProductUpdate $update, string $filePath, UploadedFile $file): void
    {
        $update->update([
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'file_mime_type' => $file->getMimeType(),
            'original_filename' => $file->getClientOriginalName(),
        ]);
    }
}
