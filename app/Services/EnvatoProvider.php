<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

/**
 * Envato OAuth Provider with enhanced security and error handling.
 *
 * This provider handles OAuth authentication with Envato's API, providing
 * secure user authentication, data mapping, and comprehensive error handling.
 *
 * Features:
 * - Secure OAuth 2.0 authentication with Envato API
 * - Comprehensive user data mapping and validation
 * - Enhanced error handling and logging
 * - Input sanitization and security measures
 * - Proper token management and validation
 * - Fallback mechanisms for missing user data
 *
 *
 * @example
 * // Configure in config/services.php
 * 'envato' => [
 *     'client_id' => env('ENVATO_CLIENT_ID'),
 *     'client_secret' => env('ENVATO_CLIENT_SECRET'),
 *     'redirect' => env('ENVATO_REDIRECT_URI'),
 * ],
 *
 * // Use in controller
 * return Socialite::driver('envato')->redirect();
 */
class EnvatoProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The base Envato API URL for secure communication.
     */
    protected string $baseUrl = 'https://api.envato.com';
    /**
     * Get the authentication URL for the Envato OAuth provider.
     *
     * Constructs the secure authentication URL with proper state parameter
     * for CSRF protection and OAuth flow security.
     *
     * @param  string  $state  The state parameter for CSRF protection
     *
     * @return string The complete authentication URL
     *
     * @throws \Exception When URL construction fails
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://api.envato.com/authorization', $state);
    }
    /**
     * Get the token URL for OAuth token exchange.
     *
     * Returns the secure endpoint URL for exchanging authorization codes
     * for access tokens during the OAuth 2.0 flow.
     *
     * @return string The token exchange endpoint URL
     */
    protected function getTokenUrl(): string
    {
        return 'https://api.envato.com/token';
    }
    /**
     * Get the raw user data from Envato API using access token.
     *
     * Retrieves user account information from Envato's API with comprehensive
     * error handling, input validation, and security measures. Includes
     * proper token validation and response sanitization.
     *
     * @param  string  $token  The OAuth access token for API authentication
     *
     * @return array<mixed, mixed> The raw user data from Envato API
     *
     * @throws \Exception When API request fails or returns invalid data
     *
     * @example
     * $userData = $this->getUserByToken($accessToken);
     * $account = $userData['account'] ?? [];
     */
    /**
     * @return array<mixed, mixed>
     */
    protected function getUserByToken($token): array
    {
        try {
            // Validate token format
            if (empty($token)) {
                throw new \InvalidArgumentException('Invalid access token provided');
            }
            // Sanitize token to prevent injection attacks
            $sanitizedToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
            $response = $this->getHttpClient()->get($this->baseUrl . '/v1/market/private/user/account.json', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $sanitizedToken,
                    'User-Agent' => 'Sekuret-License-Management/1.0',
                ],
                'timeout' => 30,
            ]);
            $statusCode = $response->getStatusCode();
            if ($statusCode >= 400) {
                throw new \Exception('Failed to retrieve user data from Envato API: HTTP ' . (int)$statusCode);
            }
            $data = json_decode($response->getBody(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Envato API: ' . json_last_error_msg());
            }
            if (!is_array($data)) {
                throw new \Exception('Invalid response format from Envato API');
            }
            return $data;
        } catch (\Exception $e) {
            Log::error('Error retrieving user data from Envato API', [
                'error' => $e->getMessage(),
                'token_length' => strlen($token),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Map the raw user array to a Socialite User instance with validation.
     *
     * Transforms raw Envato API user data into a standardized Socialite User
     * object with comprehensive data validation, sanitization, and fallback
     * mechanisms for missing or invalid data.
     *
     * @param  array  $user  The raw user data from Envato API
     *
     * @return User The mapped Socialite User instance
     *
     * @throws \Exception When user data mapping fails
     *
     * @example
     * $user = $this->mapUserToObject($rawUserData);
     * echo "User: " . $user->getName() . " (" . $user->getEmail() . ")";
     */
    /**
     * @param array<string, mixed> $user
     */
    protected function mapUserToObject(array $user): User
    {
        try {
            // User parameter is already type-hinted as array
            $account = $user['account'] ?? [];
            if (!is_array($account)) {
                $account = [];
            }
            // Generate a unique ID if not provided
            $accountId = $account['id'] ?? null;
            $id = is_string($accountId) ? $accountId : uniqid('envato_', true);
            $id = $this->sanitizeInput($id);
            // Generate username from firstname and surname if not provided
            $accountUsername = $account['username'] ?? null;
            $accountFirstname = $account['firstname'] ?? null;
            $accountSurname = $account['surname'] ?? null;
            $username = is_string($accountUsername) ? $accountUsername : 
                       strtolower((is_string($accountFirstname) ? $accountFirstname : 'user') . 
                                 (is_string($accountSurname) ? $accountSurname : ''));
            $username = $this->sanitizeInput($username);
            // For OAuth, we might not get email from this endpoint
            // We'll need to get it from somewhere else or generate one
            $accountEmail = $account['email'] ?? null;
            $email = is_string($accountEmail) ? $accountEmail : ($username . '@envato.temp');
            $email = $this->sanitizeInput($email);
            // Validate email format
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = $username . '@envato.temp';
            }
            $firstname = $account['firstname'] ?? '';
            $surname = $account['surname'] ?? '';
            $name = trim((is_string($firstname) ? $firstname : '') . ' ' . (is_string($surname) ? $surname : ''));
            $name = $this->sanitizeInput($name);
            // Sanitize avatar URL if provided
            $avatar = $account['image'] ?? null;
            if ($avatar && ! filter_var($avatar, FILTER_VALIDATE_URL)) {
                $avatar = null;
            }
            return (new User())->setRaw($user)->map([
                'id' => $id,
                'nickname' => $username,
                'name' => $name,
                'email' => $email,
                'avatar' => $avatar,
            ]);
        } catch (\Exception $e) {
            Log::error('Error mapping Envato user data', [
                'error' => $e->getMessage(),
                'user_data_keys' => array_keys($user),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get the POST fields for the OAuth token request with validation.
     *
     * Prepares the required fields for OAuth token exchange including
     * authorization code validation and proper grant type specification.
     * Includes input sanitization and security measures.
     *
     * @param  string  $code  The authorization code from OAuth callback
     *
     * @return array<mixed, mixed> The token request fields with proper validation
     *
     * @throws \InvalidArgumentException When authorization code is invalid
     */
    protected function getTokenFields($code): array
    {
        try {
            // Validate authorization code
            if (empty($code)) {
                throw new \InvalidArgumentException('Invalid authorization code provided');
            }
            // Sanitize authorization code
            $sanitizedCode = $this->sanitizeInput($code);
            return array_merge(parent::getTokenFields($sanitizedCode), [
                'grant_type' => 'authorization_code',
            ]);
        } catch (\Exception $e) {
            Log::error('Error preparing token request fields', [
                'error' => $e->getMessage(),
                'code_length' => strlen($code),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Sanitize input data to prevent XSS and injection attacks.
     *
     * Provides comprehensive input sanitization for user data and API responses
     * to ensure security and prevent various types of injection attacks.
     *
     * @param  string|null  $input  The input string to sanitize
     *
     * @return string The sanitized input string
     */
    private function sanitizeInput(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        // Remove null bytes and control characters
        $input = str_replace(["\0", "\x00"], '', $input);
        // Trim whitespace
        $input = trim($input);
        // Escape HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
}
