<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Product Validation Service
 * 
 * Handles validation for product operations
 */
class ProductValidationService
{
    /**
     * Validate product data
     */
    public function validateProductData(array $data, ?Product $product = null): array
    {
        $rules = $this->getValidationRules($product);
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }

    /**
     * Validate product files
     */
    public function validateProductFiles(array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->validateSingleFile($file);
            }
        }
    }

    /**
     * Validate single file
     */
    private function validateSingleFile(UploadedFile $file): void
    {
        $validator = Validator::make(['file' => $file], [
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,pdf,zip,rar|max:10240', // 10MB max
        ]);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate product categories
     */
    public function validateProductCategories(array $categoryIds): void
    {
        if (empty($categoryIds)) {
            return;
        }

        $existingCategories = ProductCategory::whereIn('id', $categoryIds)->pluck('id')->toArray();
        $missingCategories = array_diff($categoryIds, $existingCategories);

        if (!empty($missingCategories)) {
            throw new \InvalidArgumentException('Invalid category IDs: ' . implode(', ', $missingCategories));
        }
    }

    /**
     * Validate product slug uniqueness
     */
    public function validateSlugUniqueness(string $slug, ?int $excludeId = null): void
    {
        $query = Product::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        if ($query->exists()) {
            throw new \InvalidArgumentException("Slug '{$slug}' already exists");
        }
    }

    /**
     * Get validation rules
     */
    private function getValidationRules(?Product $product = null): array
    {
        $baseRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:product_categories,id',
        ];

        if (!$product) {
            $baseRules['name'] .= '|unique:products,name';
        } else {
            $baseRules['name'] .= '|unique:products,name,' . $product->id;
        }

        return $baseRules;
    }
}
