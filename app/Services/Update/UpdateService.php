<?php

declare(strict_types=1);

namespace App\Services\Update;

use App\Models\ProductUpdate;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Update Service
 * 
 * Handles product update operations
 */
class UpdateService
{
    public function __construct(
        private UpdateValidationService $validationService,
        private UpdateFileService $fileService
    ) {
    }

    /**
     * Create new update
     */
    public function createUpdate(array $data, ?UploadedFile $file = null): ProductUpdate
    {
        try {
            DB::beginTransaction();
            
            $validatedData = $this->validationService->validateUpdateData($data);
            $this->validationService->validateProductExists($validatedData['product_id']);
            $this->validationService->validateVersionUniqueness(
                $validatedData['version'],
                $validatedData['product_id']
            );
            
            if ($file) {
                $this->validationService->validateFile($file);
            }
            
            $update = ProductUpdate::create($validatedData);
            
            if ($file) {
                $this->fileService->storeFile($update, $file);
            }
            
            DB::commit();
            
            Log::info('Update created successfully', [
                'update_id' => $update->id,
                'product_id' => $update->product_id,
                'version' => $update->version
            ]);
            
            return $update;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create update', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Update existing update
     */
    public function updateUpdate(ProductUpdate $update, array $data, ?UploadedFile $file = null): ProductUpdate
    {
        try {
            DB::beginTransaction();
            
            $validatedData = $this->validationService->validateUpdateData($data, $update);
            
            if (isset($validatedData['version']) && $validatedData['version'] !== $update->version) {
                $this->validationService->validateVersionUniqueness(
                    $validatedData['version'],
                    $update->product_id,
                    $update->id
                );
            }
            
            if ($file) {
                $this->validationService->validateFile($file);
                $this->fileService->deleteFile($update);
                $this->fileService->storeFile($update, $file);
            }
            
            $update->update($validatedData);
            
            DB::commit();
            
            Log::info('Update updated successfully', [
                'update_id' => $update->id,
                'product_id' => $update->product_id,
                'version' => $update->version
            ]);
            
            return $update;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update update', [
                'update_id' => $update->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Delete update
     */
    public function deleteUpdate(ProductUpdate $update): bool
    {
        try {
            DB::beginTransaction();
            
            $this->fileService->deleteFile($update);
            $update->delete();
            
            DB::commit();
            
            Log::info('Update deleted successfully', [
                'update_id' => $update->id,
                'product_id' => $update->product_id,
                'version' => $update->version
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete update', [
                'update_id' => $update->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Toggle update status
     */
    public function toggleStatus(ProductUpdate $update): ProductUpdate
    {
        $update->update(['is_active' => !$update->is_active]);
        
        Log::info('Update status toggled', [
            'update_id' => $update->id,
            'is_active' => $update->is_active
        ]);
        
        return $update;
    }

    /**
     * Get update download URL
     */
    public function getDownloadUrl(ProductUpdate $update): string
    {
        return $this->fileService->getDownloadUrl($update);
    }
}
