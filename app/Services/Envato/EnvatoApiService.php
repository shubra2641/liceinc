<?php

declare(strict_types=1);

namespace App\Services\Envato;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envato API Service - Handles API communication with Envato.
 */
class EnvatoApiService
{
    protected string $baseUrl = 'https://api.envato.com';

    /**
     * Make API request to Envato.
     */
    public function makeRequest(string $endpoint, string $token, array $params = []): array
    {
        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(30)
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Envato API request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'API request failed',
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Envato API request exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'API request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test token validity.
     */
    public function testToken(string $token): bool
    {
        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(30)
                ->get("{$this->baseUrl}/v1/market/private/user/account.json");

            $isValid = $response->successful();

            if (!$isValid) {
                Log::error('Token validation failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }

            return $isValid;
        } catch (\Exception $e) {
            Log::error('Exception during token testing: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get item information.
     */
    public function getItemInfo(int $itemId, string $token): array
    {
        return $this->makeRequest('/v3/market/catalog/item', $token, ['id' => $itemId]);
    }

    /**
     * Get user items.
     */
    public function getUserItems(string $username, string $token): array
    {
        return $this->makeRequest('/v2/market/user/items-by-author', $token, ['username' => $username]);
    }

    /**
     * Verify purchase code.
     */
    public function verifyPurchaseCode(string $purchaseCode, string $token): array
    {
        return $this->makeRequest('/v3/market/author/sale', $token, ['code' => $purchaseCode]);
    }

    /**
     * Get user account information.
     */
    public function getUserAccount(string $token): array
    {
        return $this->makeRequest('/v1/market/private/user/account.json', $token);
    }
}
