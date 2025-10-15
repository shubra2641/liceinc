<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

/**
 * Service for handling Envato product operations
 */
class EnvatoProductService
{
    /**
     * Get product data from Envato API
     */
    public function getProductData(int $itemId): JsonResponse
    {
        try {
            $envatoService = app(\App\Services\Envato\EnvatoService::class);
            $itemData = $envatoService->getItemInfo($itemId);

            if (!$itemData) {
                return response()->json([
                    'success' => false,
                    'message' => trans('app.Unable to fetch product data from Envato')
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'envato_item_id' => $itemData['id'] ?? null,
                    'purchase_url_envato' => $itemData['url'] ?? null,
                    'purchase_url_buy' => $itemData['url'] ?? null,
                    'support_days' => $this->calculateSupportDays($itemData),
                    'version' => $itemData['version'] ?? null,
                    'price' => isset($itemData['price_cents']) ? ($itemData['price_cents'] / 100) : null,
                    'name' => $itemData['name'] ?? null,
                    'description' => $itemData['description'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('app.Error fetching product data: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's Envato items for selection
     */
    public function getUserItems(): JsonResponse
    {
        try {
            $envatoService = app(\App\Services\Envato\EnvatoService::class);
            $settings = $envatoService->getEnvatoSettings();

            if (empty($settings['username'])) {
                return response()->json([
                    'success' => false,
                    'message' => trans('app.Envato username not configured')
                ], 400);
            }

            $userItems = $envatoService->getUserItems($settings['username']);

            if (!$userItems || !isset($userItems['matches'])) {
                return response()->json([
                    'success' => false,
                    'message' => trans('app.Unable to fetch user items from Envato')
                ], 404);
            }

            $items = collect($userItems['matches'])->map(function (array $item): array {
                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'url' => $item['url'],
                    'price' => isset($item['price_cents']) ? ($item['price_cents'] / 100) : 0,
                    'rating' => $item['rating'] ?? null,
                    'sales' => $item['number_of_sales'] ?? 0,
                ];
            });

            return response()->json(['success' => true, 'items' => $items]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('app.Error fetching user items: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate support days from Envato item data
     */
    private function calculateSupportDays(array $itemData): int
    {
        if (isset($itemData['attributes']) && is_array($itemData['attributes'])) {
            foreach ($itemData['attributes'] as $attribute) {
                if (is_array($attribute) && isset($attribute['name']) && $attribute['name'] === 'support') {
                    $value = strtolower($attribute['value'] ?? '');
                    if (strpos($value, 'month') !== false) {
                        preg_match('/(\d+)/', $value, $matches);
                        return isset($matches[1]) ? (int)$matches[1] * 30 : 180;
                    } elseif (strpos($value, 'year') !== false) {
                        preg_match('/(\d+)/', $value, $matches);
                        return isset($matches[1]) ? (int)$matches[1] * 365 : 365;
                    }
                }
            }
        }
        return 180;
    }
}
