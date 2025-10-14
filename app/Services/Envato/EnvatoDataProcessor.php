<?php

declare(strict_types=1);

namespace App\Services\Envato;

/**
 * Envato Data Processor
 * 
 * Handles data processing for Envato API responses
 */
class EnvatoDataProcessor
{
    /**
     * Process item information
     */
    public function processItemInfo(array $data): array
    {
        if (!isset($data['item']) || !is_array($data['item'])) {
            return [
                'success' => false,
                'error' => 'Invalid item data structure'
            ];
        }

        $item = $data['item'];
        
        return [
            'success' => true,
            'data' => [
                'id' => $item['id'] ?? null,
                'name' => $item['name'] ?? null,
                'description' => $item['description'] ?? null,
                'url' => $item['url'] ?? null,
                'price' => $item['price'] ?? null,
                'currency' => $item['currency'] ?? 'USD',
                'author' => $item['author'] ?? null,
                'tags' => $item['tags'] ?? [],
                'category' => $item['category'] ?? null,
                'rating' => $item['rating'] ?? null,
                'sales' => $item['sales'] ?? 0,
                'updated_at' => $item['updated_at'] ?? null,
                'created_at' => $item['created_at'] ?? null,
            ]
        ];
    }

    /**
     * Process purchase verification
     */
    public function processPurchaseVerification(array $data): array
    {
        if (!isset($data['sale']) || !is_array($data['sale'])) {
            return [
                'success' => false,
                'error' => 'Invalid purchase data structure'
            ];
        }

        $sale = $data['sale'];
        
        return [
            'success' => true,
            'data' => [
                'purchase_code' => $sale['code'] ?? null,
                'item_id' => $sale['item']['id'] ?? null,
                'item_name' => $sale['item']['name'] ?? null,
                'buyer' => $sale['buyer'] ?? null,
                'purchase_date' => $sale['sold_at'] ?? null,
                'license' => $sale['license'] ?? null,
                'support_amount' => $sale['support_amount'] ?? null,
                'total_amount' => $sale['amount'] ?? null,
                'currency' => $sale['currency'] ?? 'USD',
            ]
        ];
    }

    /**
     * Process user information
     */
    public function processUserInfo(array $data): array
    {
        if (!isset($data['user']) || !is_array($data['user'])) {
            return [
                'success' => false,
                'error' => 'Invalid user data structure'
            ];
        }

        $user = $data['user'];
        
        return [
            'success' => true,
            'data' => [
                'username' => $user['username'] ?? null,
                'firstname' => $user['firstname'] ?? null,
                'surname' => $user['surname'] ?? null,
                'email' => $user['email'] ?? null,
                'country' => $user['country'] ?? null,
                'city' => $user['city'] ?? null,
                'image' => $user['image'] ?? null,
                'followers' => $user['followers'] ?? 0,
                'sales' => $user['sales'] ?? 0,
                'rating' => $user['rating'] ?? null,
            ]
        ];
    }

    /**
     * Extract support information from item data
     */
    public function extractSupportInfo(array $itemData): array
    {
        $supportInfo = [
            'support_days' => 180, // Default
            'support_available' => false,
            'support_type' => 'none'
        ];

        if (isset($itemData['attributes']) && is_array($itemData['attributes'])) {
            foreach ($itemData['attributes'] as $attribute) {
                if (isset($attribute['name']) && $attribute['name'] === 'support') {
                    $supportInfo['support_available'] = true;
                    $supportInfo['support_type'] = $attribute['value'] ?? 'none';
                    $supportInfo['support_days'] = $this->calculateSupportDays($attribute['value'] ?? '');
                }
            }
        }

        return $supportInfo;
    }

    /**
     * Calculate support days from support value
     */
    private function calculateSupportDays(string $supportValue): int
    {
        $supportValue = strtolower($supportValue);
        
        if (strpos($supportValue, 'month') !== false) {
            preg_match('/(\d+)/', $supportValue, $matches);
            return isset($matches[1]) ? (int)$matches[1] * 30 : 180;
        }
        
        if (strpos($supportValue, 'year') !== false) {
            preg_match('/(\d+)/', $supportValue, $matches);
            return isset($matches[1]) ? (int)$matches[1] * 365 : 365;
        }
        
        return 180; // Default
    }
}
