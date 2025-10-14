<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Product Service
 * 
 * Handles product operations
 */
class ProductService
{
    public function __construct(
        private ProductFormHandler $formHandler,
        private ProductValidationService $validationService
    ) {
    }

    /**
     * Create new product
     */
    public function createProduct(array $data, array $files = []): Product
    {
        $validatedData = $this->validationService->validateProductData($data);
        $this->validationService->validateProductFiles($files);
        
        if (isset($validatedData['categories'])) {
            $this->validationService->validateProductCategories($validatedData['categories']);
        }
        
        return $this->formHandler->createProduct($validatedData, $files);
    }

    /**
     * Update existing product
     */
    public function updateProduct(Product $product, array $data, array $files = []): Product
    {
        $validatedData = $this->validationService->validateProductData($data, $product);
        $this->validationService->validateProductFiles($files);
        
        if (isset($validatedData['categories'])) {
            $this->validationService->validateProductCategories($validatedData['categories']);
        }
        
        return $this->formHandler->updateProduct($product, $validatedData, $files);
    }

    /**
     * Delete product
     */
    public function deleteProduct(Product $product): bool
    {
        try {
            DB::beginTransaction();
            
            $this->deleteProductFiles($product);
            $product->delete();
            
            DB::commit();
            
            Log::info('Product deleted successfully', [
                'product_id' => $product->id,
                'name' => $product->name
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete product', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Product $product): Product
    {
        $product->update(['is_active' => !$product->is_active]);
        
        Log::info('Product status toggled', [
            'product_id' => $product->id,
            'is_active' => $product->is_active
        ]);
        
        return $product;
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Product $product): Product
    {
        $product->update(['is_featured' => !$product->is_featured]);
        
        Log::info('Product featured status toggled', [
            'product_id' => $product->id,
            'is_featured' => $product->is_featured
        ]);
        
        return $product;
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(ProductCategory $category): \Illuminate\Database\Eloquent\Collection
    {
        return $category->products()
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search products
     */
    public function searchProducts(string $query, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Delete product files
     */
    private function deleteProductFiles(Product $product): void
    {
        foreach ($product->files as $file) {
            if (\Storage::disk('public')->exists($file->file_path)) {
                \Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }
    }
}
