<?php

declare(strict_types=1);

namespace App\Services\Update;

use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Update Validation Service
 * 
 * Handles validation for product updates
 */
class UpdateValidationService
{
    /**
     * Validate update data
     */
    public function validateUpdateData(array $data, ?ProductUpdate $update = null): array
    {
        $rules = $this->getValidationRules($update);
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }

    /**
     * Validate file upload
     */
    public function validateFile(UploadedFile $file): void
    {
        $validator = Validator::make(['file' => $file], [
            'file' => 'required|file|mimes:zip,rar,7z|max:102400', // 100MB max
        ]);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate version uniqueness
     */
    public function validateVersionUniqueness(string $version, int $productId, ?int $excludeId = null): void
    {
        $query = ProductUpdate::where('product_id', $productId)
            ->where('version', $version);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        if ($query->exists()) {
            throw new \InvalidArgumentException("Version {$version} already exists for this product");
        }
    }

    /**
     * Validate product exists
     */
    public function validateProductExists(int $productId): Product
    {
        $product = Product::find($productId);
        
        if (!$product) {
            throw new \InvalidArgumentException('Product not found');
        }
        
        return $product;
    }

    /**
     * Get validation rules
     */
    private function getValidationRules(?ProductUpdate $update = null): array
    {
        $baseRules = [
            'product_id' => 'required|integer|exists:products,id',
            'version' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'changelog' => 'nullable|string',
            'is_major' => 'boolean',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'released_at' => 'nullable|date',
        ];

        if (!$update) {
            $baseRules['file'] = 'required|file|mimes:zip,rar,7z|max:102400';
        } else {
            $baseRules['file'] = 'sometimes|file|mimes:zip,rar,7z|max:102400';
        }

        return $baseRules;
    }
}
