<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Service for handling product API operations
 */
class ProductApiService
{
    public function __construct(
        private EnvatoProductService $envatoProductService,
        private ProductKbService $productKbService
    ) {
    }

    /**
     * Handle API requests for product data
     */
    public function handleApiRequest(Request $request): JsonResponse
    {
        $action = $request->input('action');

        return match ($action) {
            'envato_product_data' => $this->getEnvatoProductData($request),
            'envato_user_items' => $this->getEnvatoUserItems(),
            'product_data' => $this->getProductData($request),
            'kb_data' => $this->getKbData(),
            'kb_articles' => $this->getKbArticles($request),
            default => response()->json(['success' => false, 'message' => 'Invalid action'], 400)
        };
    }

    /**
     * Get product data from Envato API
     */
    private function getEnvatoProductData(Request $request): JsonResponse
    {
        $request->validate(['item_id' => 'required|integer|min:1']);
        return $this->envatoProductService->getProductData((int)$request->input('item_id'));
    }

    /**
     * Get user's Envato items for selection
     */
    private function getEnvatoUserItems(): JsonResponse
    {
        return $this->envatoProductService->getUserItems();
    }

    /**
     * Get product data for license forms
     */
    private function getProductData(Request $request): JsonResponse
    {
        $productId = $request->input('product_id');
        $product = Product::findOrFail($productId);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'license_type' => $product->license_type,
            'duration_days' => $product->duration_days,
            'support_days' => $product->support_days,
            'price' => $product->price,
            'renewal_price' => $product->renewal_price,
            'renewal_period' => $product->renewal_period,
        ]);
    }

    /**
     * Get KB categories and articles
     */
    private function getKbData(): JsonResponse
    {
        return $this->productKbService->getKbData();
    }

    /**
     * Get KB articles for specific category
     */
    private function getKbArticles(Request $request): JsonResponse
    {
        $categoryId = $request->input('category_id');
        return $this->productKbService->getKbArticles((int)$categoryId);
    }
}
