<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envato Service - Simplified.
 */
class EnvatoService
{
    protected string $baseUrl = 'https://api.envato.com';

    /**
     * Get Envato settings from database.
     */
    public function getEnvatoSettings(): array
    {
        try {
            $setting = Setting::first();
            if (! $setting) {
                Log::error('No settings found in database for Envato configuration');
                throw new Exception('Settings not found');
            }

            return [
                'token' => $this->sanitizeString($setting->envato_personal_token ?? config('envato.token', '')),
                'api_key' => $this->sanitizeString($setting->envato_api_key ?? config('envato.api_key', '')),
                'client_id' => $this->sanitizeString($setting->envato_client_id ?? config('envato.client_id', '')),
                'client_secret' => $this->sanitizeString($setting->envato_client_secret ?? config('envato.client_secret', '')),
                'redirect' => $this->sanitizeString($setting->envato_redirect_uri ?? config('services.envato.redirect', '')),
                'oauth_enabled' => (bool)($setting->envato_oauth_enabled ?? false),
                'username' => $this->sanitizeString((string)($setting->envato_username ?? '')),
                'auth_enabled' => (bool)($setting->envato_auth_enabled ?? false),
            ];
        } catch (Exception $e) {
            Log::error('Failed to retrieve Envato settings: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify purchase code with Envato API.
     */
    public function verifyPurchase(string $purchaseCode): ?array
    {
        try {
            $purchaseCode = $this->validatePurchaseCode($purchaseCode);
            $settings = $this->getEnvatoSettings();
            $token = $settings['token'];

            if (empty($token)) {
                Log::error('Envato token not configured for purchase verification');

                return null;
            }

            $cacheKey = 'envato_purchase_' . md5($purchaseCode);
            $result = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($purchaseCode, $token) {
                try {
                    $response = Http::withToken($token)
                        ->acceptJson()
                        ->timeout(30)
                        ->get("{$this->baseUrl}/v3/market/author/sale", [
                            'code' => $purchaseCode,
                        ]);

                    if ($response->failed()) {
                        Log::error('Failed to verify purchase code with Envato API', [
                            'purchase_code' => $purchaseCode,
                            'status' => $response->status(),
                        ]);

                        return null;
                    }

                    $result = $response->json();

                    return is_array($result) ? $result : null;
                } catch (Exception $e) {
                    Log::error('Exception during purchase verification: ' . $e->getMessage());

                    return null;
                }
            });

            return is_array($result) ? $result : null;
        } catch (Exception $e) {
            Log::error('Failed to verify purchase code: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get user information from Envato API.
     */
    public function getUserInfo(string $username): ?array
    {
        try {
            $username = $this->validateUsername($username);
            $settings = $this->getEnvatoSettings();
            $token = $settings['token'];

            if (empty($token)) {
                Log::error('Envato token not configured for user info retrieval');

                return null;
            }

            $cacheKey = 'envato_user_' . md5($username);
            $result = Cache::remember($cacheKey, now()->addHours(1), function () use ($username, $token) {
                try {
                    $response = Http::withToken($token)
                        ->acceptJson()
                        ->timeout(30)
                        ->get("{$this->baseUrl}/v2/market/user:" . $username);

                    if ($response->failed()) {
                        Log::error('Failed to retrieve user info from Envato API', [
                            'username' => $username,
                            'status' => $response->status(),
                        ]);

                        return null;
                    }

                    $result = $response->json();

                    return is_array($result) ? $result : null;
                } catch (Exception $e) {
                    Log::error('Exception during user info retrieval: ' . $e->getMessage());

                    return null;
                }
            });

            return is_array($result) ? $result : null;
        } catch (Exception $e) {
            Log::error('Failed to get user info: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get OAuth user information from Envato API.
     */
    public function getOAuthUserInfo(string $accessToken): ?array
    {
        try {
            $accessToken = $this->validateAccessToken($accessToken);
            $cacheKey = 'envato_oauth_user_' . md5($accessToken);

            $result = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($accessToken) {
                try {
                    $response = Http::withToken($accessToken)
                        ->acceptJson()
                        ->timeout(30)
                        ->get("{$this->baseUrl}/v1/market/private/user/account.json");

                    if ($response->failed()) {
                        Log::error('Failed to retrieve OAuth user info from Envato API', [
                            'status' => $response->status(),
                        ]);

                        return null;
                    }

                    $result = $response->json();

                    return is_array($result) ? $result : null;
                } catch (Exception $e) {
                    Log::error('Exception during OAuth user info retrieval: ' . $e->getMessage());

                    return null;
                }
            });

            return is_array($result) ? $result : null;
        } catch (Exception $e) {
            Log::error('Failed to get OAuth user info: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Test token validity with Envato API.
     */
    public function testToken(string $token): bool
    {
        try {
            $token = $this->validateAccessToken($token);
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(30)
                ->get("{$this->baseUrl}/v1/market/private/user/account.json");

            $isValid = $response->successful();
            if (! $isValid) {
                Log::error('Token validation failed', [
                    'status' => $response->status(),
                ]);
            }

            return $isValid;
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
        $settings = $this->getEnvatoSettings();
        $token = $settings['token'];

        if (empty($token)) {
            return null;
        }

        $cacheKey = 'envato_item_' . $itemId;
        $result = Cache::remember($cacheKey, now()->addHours(6), function () use ($itemId, $token) {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$this->baseUrl}/v3/market/catalog/item", [
                    'id' => $itemId,
                ]);

            if ($response->failed()) {
                return null;
            }

            $result = $response->json();

            return is_array($result) ? $result : null;
        });

        return is_array($result) ? $result : null;
    }

    /**
     * Get user items.
     */
    public function getUserItems(string $username): ?array
    {
        $settings = $this->getEnvatoSettings();
        $token = $settings['token'];

        if (empty($token)) {
            return null;
        }

        $cacheKey = 'envato_user_items_' . md5($username);
        $result = Cache::remember($cacheKey, now()->addHours(6), function () use ($username, $token) {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$this->baseUrl}/v2/market/user/items-by-author", [
                    'username' => $username,
                ]);

            if ($response->failed()) {
                return null;
            }

            $result = $response->json();

            return is_array($result) ? $result : null;
        });

        return is_array($result) ? $result : null;
    }

    /**
     * Validate token.
     */
    public function validateToken(): bool
    {
        $settings = $this->getEnvatoSettings();
        $token = $settings['token'];

        if (empty($token) || ! is_string($token)) {
            return false;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->get("{$this->baseUrl}/v1/market/private/user/account.json");

        return $response->successful();
    }

    /**
     * Clear Envato API cache.
     */
    public function clearCache(): void
    {
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Cache::getStore()->getRedis();
                if (method_exists($redis, 'keys')) {
                    $cacheKeys = $redis->keys('*envato_purchase_*');
                    if (is_array($cacheKeys)) {
                        foreach ($cacheKeys as $key) {
                            if (is_string($key)) {
                                Cache::forget($key);
                            }
                        }
                    }
                }
            }

            Cache::forget('envato_user_*');
            Cache::forget('envato_item_*');
            Cache::forget('envato_user_items_*');
            Cache::forget('envato_oauth_user_*');
        } catch (Exception $e) {
            Log::error('Failed to clear Envato cache: ' . $e->getMessage());
        }
    }

    /**
     * Validate and sanitize purchase code.
     */
    private function validatePurchaseCode(string $purchaseCode): string
    {
        if (empty($purchaseCode)) {
            throw new \InvalidArgumentException('Purchase code cannot be empty');
        }

        $sanitized = htmlspecialchars(trim($purchaseCode), ENT_QUOTES, 'UTF-8');
        if (empty($sanitized) || strlen($sanitized) < 10) {
            throw new \InvalidArgumentException('Purchase code must be at least 10 characters long');
        }

        return $sanitized;
    }

    /**
     * Validate and sanitize username.
     */
    private function validateUsername(string $username): string
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('Username cannot be empty');
        }

        $sanitized = htmlspecialchars(trim($username), ENT_QUOTES, 'UTF-8');
        if (empty($sanitized) || strlen($sanitized) < 2) {
            throw new \InvalidArgumentException('Username must be at least 2 characters long');
        }

        return $sanitized;
    }

    /**
     * Validate and sanitize access token.
     */
    private function validateAccessToken(string $accessToken): string
    {
        if (empty($accessToken)) {
            throw new \InvalidArgumentException('Access token cannot be empty');
        }

        $sanitized = htmlspecialchars(trim($accessToken), ENT_QUOTES, 'UTF-8');
        if (empty($sanitized) || strlen($sanitized) < 20) {
            throw new \InvalidArgumentException('Access token must be at least 20 characters long');
        }

        return $sanitized;
    }

    /**
     * Sanitize string input with XSS protection.
     */
    private function sanitizeString(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
