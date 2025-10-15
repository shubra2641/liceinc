<?php

declare(strict_types=1);

namespace App\Services\Envato;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Envato Service with enhanced security.
 */
class EnvatoService
{
    public function __construct(
        private EnvatoApiService $apiService,
        private EnvatoCacheService $cacheService,
        private EnvatoValidationService $validationService
    ) {
    }

    /**
     * Get Envato settings from database.
     */
    public function getEnvatoSettings(): array
    {
        try {
            $setting = Setting::first();
            if (!$setting) {
                Log::error('No settings found in database for Envato configuration');
                throw new Exception('Settings not found');
            }

            return [
                'token' => $this->validationService->sanitizeString($setting->envato_token ?? ''),
                'client_id' => $this->validationService->sanitizeString($setting->envato_client_id ?? ''),
                'client_secret' => $this->validationService->sanitizeString($setting->envato_client_secret ?? ''),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get Envato settings: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify purchase code.
     */
    public function verifyPurchaseCode(string $purchaseCode): array
    {
        try {
            $purchaseCode = $this->validationService->validatePurchaseCode($purchaseCode);

            // Check cache first
            $cachedData = $this->cacheService->getCachedPurchaseVerification($purchaseCode);
            if ($cachedData) {
                return $cachedData;
            }

            $settings = $this->getEnvatoSettings();
            $token = $settings['token'];

            if (empty($token)) {
                return [
                    'success' => false,
                    'message' => 'Envato token not configured'
                ];
            }

            $response = $this->apiService->verifyPurchaseCode($purchaseCode, $token);

            if ($response['success']) {
                $this->cacheService->cachePurchaseVerification($purchaseCode, $response);
            }

            return $response;
        } catch (Exception $e) {
            Log::error('Purchase code verification failed', [
                'purchase_code' => $this->validationService->sanitizeString($purchaseCode),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Purchase code verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test token validity.
     */
    public function testToken(string $token): bool
    {
        try {
            $token = $this->validationService->validateAccessToken($token);
            return $this->apiService->testToken($token);
        } catch (Exception $e) {
            Log::error('Exception during token testing: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get item information.
     */
    public function getItemInfo(int $itemId): ?array
    {
        try {
            $itemId = $this->validationService->validateItemId($itemId);

            // Check cache first
            $cachedData = $this->cacheService->getCachedItemInfo($itemId);
            if ($cachedData) {
                return $cachedData;
            }

            $settings = $this->getEnvatoSettings();
            $token = $settings['token'];

            if (empty($token)) {
                return null;
            }

            $response = $this->apiService->getItemInfo($itemId, $token);

            if ($response['success']) {
                $this->cacheService->cacheItemInfo($itemId, $response['data']);
                return $response['data'];
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to get item info', [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get user items.
     */
    public function getUserItems(string $username): ?array
    {
        try {
            $username = $this->validationService->validateUsername($username);

            // Check cache first
            $cachedData = $this->cacheService->getCachedUserItems($username);
            if ($cachedData) {
                return $cachedData;
            }

            $settings = $this->getEnvatoSettings();
            $token = $settings['token'];

            if (empty($token)) {
                return null;
            }

            $response = $this->apiService->getUserItems($username, $token);

            if ($response['success']) {
                $this->cacheService->cacheUserItems($username, $response['data']);
                return $response['data'];
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to get user items', [
                'username' => $this->validationService->sanitizeString($username),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Validate token.
     */
    public function validateToken(): bool
    {
        try {
            $settings = $this->getEnvatoSettings();
            $token = $settings['token'];

            if (empty($token) || !is_string($token)) {
                return false;
            }

            return $this->apiService->testToken($token);
        } catch (Exception $e) {
            Log::error('Token validation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cache.
     */
    public function clearCache(): void
    {
        $this->cacheService->clearAllCache();
    }
}
