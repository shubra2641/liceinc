<?php

declare(strict_types=1);

namespace App\Services\Envato;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envato API Client
 * 
 * Handles API communication with Envato
 */
class EnvatoApiClient
{
    private string $baseUrl = 'https://api.envato.com/v3';
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get item information
     */
    public function getItemInfo(int $itemId): array
    {
        try {
            $response = $this->makeRequest('GET', "/market/catalog/item?id={$itemId}");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return $this->handleApiError($response);
            
        } catch (\Exception $e) {
            Log::error('Envato API request failed', [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify purchase
     */
    public function verifyPurchase(string $purchaseCode): array
    {
        try {
            $response = $this->makeRequest('GET', "/market/author/sale?code={$purchaseCode}");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return $this->handleApiError($response);
            
        } catch (\Exception $e) {
            Log::error('Envato purchase verification failed', [
                'purchase_code' => substr($purchaseCode, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user information
     */
    public function getUserInfo(string $username): array
    {
        try {
            $response = $this->makeRequest('GET', "/market/user:{$username}");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return $this->handleApiError($response);
            
        } catch (\Exception $e) {
            Log::error('Envato user info request failed', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Make API request
     */
    private function makeRequest(string $method, string $endpoint): \Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'User-Agent' => 'My-Logos/1.0',
        ])
        ->timeout(30)
        ->retry(3, 1000)
        ->$method($this->baseUrl . $endpoint);
    }

    /**
     * Handle API error response
     */
    private function handleApiError(\Illuminate\Http\Client\Response $response): array
    {
        $statusCode = $response->status();
        $errorData = $response->json();
        
        Log::error('Envato API error', [
            'status_code' => $statusCode,
            'error_data' => $errorData
        ]);
        
        return [
            'success' => false,
            'error' => $errorData['error'] ?? 'API request failed',
            'status_code' => $statusCode
        ];
    }
}
