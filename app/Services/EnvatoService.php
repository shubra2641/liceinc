<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envato Service with enhanced security. *
 * A comprehensive service for interacting with Envato's API, providing * secure authentication, data retrieval, and comprehensive error handling. *
 * Features: * - Secure API communication with Envato * - Purchase code verification and validation * - User information retrieval and caching * - OAuth token management and validation * - Item information and user items retrieval * - Enhanced error handling and logging * - Input validation and sanitization * - Comprehensive security measures * - Clean code structure with no duplicate patterns * - Proper type hints and return types */
class EnvatoService
{
    /**   * The base URL for Envato API. */
    protected string $baseUrl = 'https://api.envato.com';
    /**   * Get Envato settings from database with fallback to config and enhanced security. *   * Retrieves Envato configuration settings from the database with fallback * to configuration files, including comprehensive validation and sanitization. *   * @return array The Envato settings array *   * @throws Exception When settings retrieval fails *   * @version 1.0.6 *   *   *   *   */
    /**   * @return array<string, mixed> */
    public function getEnvatoSettings(): array
    {
        try {
            $setting = Setting::first();
            if (! $setting) {
                Log::error('No settings found in database for Envato configuration');
                throw new Exception('Settings not found');
            }
            return [
                'token' => $this->sanitizeString(
                    is_string($setting->envato_personal_token ?? null) ? $setting->envato_personal_token : (is_string(config('envato.token')) ? config('envato.token') : ''),
                ),
                'api_key' => $this->sanitizeString(is_string($setting->envato_api_key ?? null) ? $setting->envato_api_key : (is_string(config('envato.api_key')) ? config('envato.api_key') : '')),
                'client_id' => $this->sanitizeString(is_string($setting->envato_client_id ?? null) ? $setting->envato_client_id : (is_string(config('envato.client_id')) ? config('envato.client_id') : '')),
                'client_secret' => $this->sanitizeString(
                    is_string($setting->envato_client_secret ?? null) ? $setting->envato_client_secret : (is_string(config('envato.client_secret')) ? config('envato.client_secret') : ''),
                ),
                'redirect' => $this->sanitizeString(
                    is_string($setting->envato_redirect_uri ?? null) ? $setting->envato_redirect_uri : (is_string(config('services.envato.redirect')) ? config('services.envato.redirect') : ''),
                ),
                'oauth_enabled' => (bool)($setting->envato_oauth_enabled ?? false),
                'username' => $this->sanitizeString((string) ($setting->envato_username ?? null)),
                'auth_enabled' => (bool)($setting->envato_auth_enabled ?? false),
            ];
        } catch (Exception $e) {
            Log::error('Failed to retrieve Envato settings: ' . $e->getMessage());
            throw $e;
        }
    }
    /**   * Verify purchase code with Envato API and enhanced security. *   * Verifies a purchase code with Envato's API using secure authentication * and comprehensive error handling with caching for performance. *   * @param string $purchaseCode The purchase code to verify *   * @return array|null The purchase verification data or null if failed *   * @throws \InvalidArgumentException When purchase code is invalid *   * @version 1.0.6 *   *   *   *   */
    /**   * @return array<mixed, mixed>|null */
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
                    $tokenString = is_string($token) ? $token : '';
                    $response = Http::withToken($tokenString)
                        ->acceptJson()
                        ->timeout(30)
                        ->get("{$this->baseUrl}/v3/market/author/sale", [
                            'code' => $purchaseCode,
                        ]);
                    if ($response->failed()) {
                        Log::error('Failed to verify purchase code with Envato API', [
                            'purchase_code' => $purchaseCode,
                            'status' => $response->status(),
                            'response' => $response->body(),
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
    /**   * Get user information from Envato API with enhanced security. *   * Retrieves user information from Envato's API using secure authentication * and comprehensive error handling with caching for performance. *   * @param string $username The username to retrieve information for *   * @return array|null The user information data or null if failed *   * @throws \InvalidArgumentException When username is invalid *   * @version 1.0.6 *   *   *   *   */
    /**   * @return array<mixed, mixed>|null */
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
                    $response = Http::withToken(is_string($token) ? $token : '')
                        ->acceptJson()
                        ->timeout(30)
                        ->get("{$this->baseUrl}/v2/market/user:" . $username);
                    if ($response->failed()) {
                        Log::error('Failed to retrieve user info from Envato API', [
                            'username' => $username,
                            'status' => $response->status(),
                            'response' => $response->body(),
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
    /**   * Get OAuth user information from Envato API with enhanced security. *   * Retrieves OAuth user information from Envato's API using secure authentication * and comprehensive error handling with caching for performance. *   * @param string $accessToken The OAuth access token *   * @return array|null The OAuth user information data or null if failed *   * @throws \InvalidArgumentException When access token is invalid *   * @version 1.0.6 *   *   *   *   */
    /**   * @return array<mixed, mixed>|null */
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
                            'response' => $response->body(),
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
    /**   * Test token validity with Envato API and enhanced security. *   * Tests the validity of an Envato token by making a secure API call * with comprehensive error handling and timeout protection. *   * @param string $token The token to test *   * @return bool True if token is valid, false otherwise *   * @throws \InvalidArgumentException When token is invalid *   * @version 1.0.6 *   *   *   *   */
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
                    'response' => $response->body(),
                ]);
            }
            return $isValid;
        } catch (Exception $e) {
            Log::error('Exception during token testing: ' . $e->getMessage());
            return false;
        }
    }
    /**   * @return array<mixed, mixed>|null */
    public function getItemInfo(int $itemId): ?array
    {
        $settings = $this->getEnvatoSettings();
        $token = $settings['token'];
        if (empty($token)) {
            return null;
        }
        $cacheKey = 'envato_item_' . $itemId;
        $result = Cache::remember($cacheKey, now()->addHours(6), function () use ($itemId, $token) {
            $tokenString = is_string($token) ? $token : '';
            $response = Http::withToken($tokenString)
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
    /**   * @return array<mixed, mixed>|null */
    public function getUserItems(string $username): ?array
    {
        $settings = $this->getEnvatoSettings();
        $token = $settings['token'];
        if (empty($token)) {
            return null;
        }
        $cacheKey = 'envato_user_items_' . md5($username);
        $result = Cache::remember($cacheKey, now()->addHours(6), function () use ($username, $token) {
            $response = Http::withToken(is_string($token) ? $token : '')
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
    public function validateToken(): bool
    {
        $settings = $this->getEnvatoSettings();
        $token = $settings['token'];
        if (empty($token) || !is_string($token)) {
            return false;
        }
        $response = Http::withToken($token)
            ->acceptJson()
            ->get("{$this->baseUrl}/v1/market/private/user/account.json");
        return $response->successful();
    }
    /**   * Clear Envato API cache with enhanced security. *   * Clears all cached data from Envato API calls with comprehensive * error handling and logging. *   *   * @version 1.0.6 *   *   *   *   */
    public function clearCache(): void
    {
        try {
            // Clear all Envato purchase cache entries
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
    /**   * Validate and sanitize purchase code. *   * Validates the purchase code and returns a sanitized version * with proper security measures. *   * @param string $purchaseCode The purchase code to validate *   * @return string The validated and sanitized purchase code *   * @throws \InvalidArgumentException When purchase code is invalid *   * @version 1.0.6 *   *   *   *   */
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
    /**   * Validate and sanitize username. *   * Validates the username and returns a sanitized version * with proper security measures. *   * @param string $username The username to validate *   * @return string The validated and sanitized username *   * @throws \InvalidArgumentException When username is invalid *   * @version 1.0.6 *   *   *   *   */
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
    /**   * Validate and sanitize access token. *   * Validates the access token and returns a sanitized version * with proper security measures. *   * @param string $accessToken The access token to validate *   * @return string The validated and sanitized access token *   * @throws \InvalidArgumentException When access token is invalid *   * @version 1.0.6 *   *   *   *   */
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
    /**   * Validate and sanitize item ID. *   * Validates the item ID and returns a sanitized version * with proper security measures. *   * @param int $itemId The item ID to validate *   * @return int The validated item ID *   * @throws \InvalidArgumentException When item ID is invalid *   * @version 1.0.6 *   *   *   *   */
    // private function validateItemId(int $itemId): int
    // {
    //     if ($itemId <= 0) {
    //         throw new \InvalidArgumentException('Item ID must be a positive integer');
    //     }
    //     return $itemId;
    // }
    /**   * Sanitize string input with XSS protection. *   * Sanitizes string input to prevent XSS attacks and other * security vulnerabilities. *   * @param  string|null  $input  The input string to sanitize *   * @return string|null The sanitized string or null *   * @version 1.0.6 *   *   *   *   */
    private function sanitizeString(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
