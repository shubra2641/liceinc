<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Product Form Handler
 * 
 * Handles form processing for product operations
 */
class ProductFormHandler
{
    /**
     * Handle product creation
     */
    public function createProduct(array $data, array $files = []): Product
    {
        try {
            DB::beginTransaction();
            
            $product = $this->createProductRecord($data);
            $this->handleProductFiles($product, $files);
            $this->handleProductCategories($product, $data['categories'] ?? []);
            
            DB::commit();
            
            Log::info('Product created successfully', [
                'product_id' => $product->id,
                'name' => $product->name
            ]);
            
            return $product;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create product', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle product update
     */
    public function updateProduct(Product $product, array $data, array $files = []): Product
    {
        try {
            DB::beginTransaction();
            
            $this->updateProductRecord($product, $data);
            $this->handleProductFiles($product, $files);
            $this->handleProductCategories($product, $data['categories'] ?? []);
            
            DB::commit();
            
            Log::info('Product updated successfully', [
                'product_id' => $product->id,
                'name' => $product->name
            ]);
            
            return $product;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update product', [
                'product_id' => $product->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Create product record
     */
    private function createProductRecord(array $data): Product
    {
        return Product::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'short_description' => $data['short_description'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            'is_active' => $data['is_active'] ?? true,
            'is_featured' => $data['is_featured'] ?? false,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'slug' => $this->generateSlug($data['name']),
        ]);
    }

    /**
     * Update product record
     */
    private function updateProductRecord(Product $product, array $data): void
    {
        $product->update([
            'name' => $data['name'],
            'description' => $data['description'],
            'short_description' => $data['short_description'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            'is_active' => $data['is_active'] ?? true,
            'is_featured' => $data['is_featured'] ?? false,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'slug' => $this->generateSlug($data['name'], $product->id),
        ]);
    }

    /**
     * Handle product files
     */
    private function handleProductFiles(Product $product, array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->storeProductFile($product, $file);
            }
        }
    }

    /**
     * Handle product categories
     */
    private function handleProductCategories(Product $product, array $categoryIds): void
    {
        $product->categories()->sync($categoryIds);
    }

    /**
     * Store product file
     */
    private function storeProductFile(Product $product, UploadedFile $file): ProductFile
    {
        $filename = $this->generateFilename($product, $file);
        $path = $file->storeAs(
            "products/{$product->id}",
            $filename,
            'public'
        );

        return ProductFile::create([
            'product_id' => $product->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'is_active' => true,
        ]);
    }

    /**
     * Generate slug
     */
    private function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = \Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Product::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Generate filename
     */
    private function generateFilename(Product $product, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "product_{$product->id}_{$timestamp}.{$extension}";
    }
}
