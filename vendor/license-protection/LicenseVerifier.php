<?php
/**
 * License Verification System for Laravel
 * Product: The Ultimate License Management System
 * Generated: 2025-01-27
 * 
 * This file is hidden in vendor directory for security
 */

namespace LicenseProtection;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenseVerifier
{
    protected $apiUrl = 'https://my-logos.com/api/license/verify';
    protected $productSlug = 'the-ultimate-license-management-system';
    protected $verificationKey = 'edb0ebfcbc35d9c4279c3aa94caef5541e225e341c68aaa7d317f6cd0f8925ea';
    protected $apiToken = '0c5b4625ec372497f395c1662f5f5ff05f7cf584a79694890de7470d693a3488';

    /**
     * Verify license with purchase code
     * This method sends a single request to our system which handles both Envato and database verification
     */
    public function verifyLicense(string $purchaseCode, ?string $domain = null): array
    {
        try {
            // Validate purchase code format
            if (!$this->isValidPurchaseCodeFormat($purchaseCode)) {
                return $this->createLicenseResponse(false, 'Invalid purchase code format', null, 'INVALID_FORMAT');
            }

            // Check cache first (but don't fail if cache is unavailable)
            try {
                $cachedResult = $this->getCachedLicenseResult($purchaseCode);
                if ($cachedResult !== null) {
                    return $cachedResult;
                }
            } catch (\Exception $e) {
                // Continue without cache if it fails
                Log::warning('License cache check failed, proceeding without cache', [
                    'error' => $e->getMessage(),
                    'purchase_code' => substr($purchaseCode, 0, 8) . '...'
                ]);
            }

            // Send single request to our system
            $result = $this->verifyWithOurSystem($purchaseCode, $domain);
            
            if ($result['valid']) {
                $response = $this->createLicenseResponse(true, $result['message'], $result['data'], $result['error_code'] ?? null);
                // Cache successful verification for 24 hours (but don't fail if caching fails)
                try {
                    $this->cacheLicenseResult($purchaseCode, $response, 1440);
                } catch (\Exception $e) {
                    Log::warning('Failed to cache successful license verification', [
                        'error' => $e->getMessage(),
                        'purchase_code' => substr($purchaseCode, 0, 8) . '...'
                    ]);
                }
                return $response;
            } else {
                $response = $this->createLicenseResponse(false, $result['message'], null, $result['error_code'] ?? null);
                // Cache failed verification for 1 hour to prevent abuse (but don't fail if caching fails)
                try {
                    $this->cacheLicenseResult($purchaseCode, $response, 60);
                } catch (\Exception $e) {
                    Log::warning('Failed to cache failed license verification', [
                        'error' => $e->getMessage(),
                        'purchase_code' => substr($purchaseCode, 0, 8) . '...'
                    ]);
                }
                return $response;
            }

        } catch (\Exception $e) {
            Log::error('License verification error', [
                'error' => $e->getMessage(),
                'purchase_code' => substr($purchaseCode, 0, 8) . '...',
                'domain' => $domain
            ]);
            return $this->createLicenseResponse(false, 'Verification failed: ' . $e->getMessage(), null, 'NETWORK_EXCEPTION');
        }
    }

    /**
     * Verify with our license system
     */
    protected function verifyWithOurSystem(string $purchaseCode, ?string $domain = null): array
    {
        try {
            // Use cURL directly for better error handling
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'purchase_code' => $purchaseCode,
                    'product_slug' => $this->productSlug,
                    'domain' => $domain,
                    'verification_key' => $this->verificationKey
                ]),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'User-Agent: LicenseVerifier/1.0',
                    'Authorization: Bearer ' . $this->apiToken,
                    'Accept: application/json'
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('License API cURL error', [
                    'error' => $curlError,
                    'purchase_code' => substr($purchaseCode, 0, 8) . '...'
                ]);
                return $this->createLicenseResponse(false, 'Network error. Please check your connection and try again.', null, 'NETWORK_ERROR');
            }

            if ($httpCode === 200) {
                $data = json_decode($responseBody, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return $this->createLicenseResponse(false, 'Invalid response from license server', null, 'INVALID_JSON');
                }

                if (!isset($data['valid'])) {
                    return $this->createLicenseResponse(false, 'Invalid response from license server', null, 'MALFORMED_RESPONSE');
                }

                if ($data['valid'] === true) {
                    return $this->createLicenseResponse(true, 'License verified successfully', [
                        'verified_at' => now()->toISOString(),
                        'product' => $this->productSlug,
                        'domain' => $domain,
                        'purchase_code' => substr($purchaseCode, 0, 8) . '...'
                    ], $data['error_code'] ?? null);
                } else {
                    // Return the actual server message
                    $serverMessage = $data['message'] ?? 'License verification failed';
                    return $this->createLicenseResponse(false, $serverMessage, null, $data['error_code'] ?? $this->inferErrorCodeFromMessage($serverMessage));
                }
            } else {
                // Handle HTTP error responses
                $errorData = json_decode($responseBody, true);
                $errorMessage = 'License verification failed. Please check your purchase code.';
                $errorCode = $errorData['error_code'] ?? null;
                
                if ($errorData && isset($errorData['message'])) {
                    $errorMessage = $errorData['message']; // Use actual server message
                    $errorCode = $errorCode ?? $this->inferErrorCodeFromMessage($errorData['message']);
                } else {
                    // Handle different HTTP status codes
                    switch ($httpCode) {
                        case 401:
                            $errorMessage = 'Unauthorized access to license server';
                            $errorCode = $errorCode ?? 'UNAUTHORIZED';
                            break;
                        case 403:
                            $errorMessage = 'License verification failed. Please check your purchase code.';
                            $errorCode = $errorCode ?? 'FORBIDDEN';
                            break;
                        case 404:
                            $errorMessage = 'License server endpoint not found';
                            $errorCode = $errorCode ?? 'ENDPOINT_NOT_FOUND';
                            break;
                        case 429:
                            $errorMessage = 'Too many verification attempts. Please try again later';
                            $errorCode = $errorCode ?? 'RATE_LIMIT';
                            break;
                        case 500:
                            $errorMessage = 'License server error. Please try again later';
                            $errorCode = $errorCode ?? 'SERVER_ERROR';
                            break;
                        default:
                            $errorMessage = 'License verification failed. Please try again.';
                            $errorCode = $errorCode ?? 'UNKNOWN_ERROR';
                    }
                }
                
                return $this->createLicenseResponse(false, $errorMessage, null, $errorCode);
            }

        } catch (\Exception $e) {
            Log::error('License API network error', [
                'error' => $e->getMessage(),
                'purchase_code' => substr($purchaseCode, 0, 8) . '...'
            ]);
            
            // Always return a clean, user-friendly error message
            return $this->createLicenseResponse(false, 'License verification failed. Please check your purchase code and try again.', null, 'NETWORK_EXCEPTION');
        }
    }

    /**
     * Validate purchase code format
     */
    protected function isValidPurchaseCodeFormat(string $purchaseCode): bool
    {
        // Accept any format - just check basic requirements
        return strlen(trim($purchaseCode)) >= 5 && strlen(trim($purchaseCode)) <= 100;
    }

    /**
     * Create standardized response
     */
    protected function createLicenseResponse(bool $valid, string $message, ?array $data = null, ?string $errorCode = null): array
    {
        return [
            'valid' => $valid,
            'message' => $message,
            'data' => $data,
            'verified_at' => now()->toISOString(),
            'product' => $this->productSlug,
            'error_code' => $errorCode,
        ];
    }

    /**
     * Cache license verification result
     */
    public function cacheLicenseResult(string $purchaseCode, array $result, int $minutes = 60): void
    {
        try {
            // Try Laravel cache first
            $cacheKey = 'license_result_' . md5($purchaseCode . $this->productSlug);
            Cache::put($cacheKey, $result, now()->addMinutes($minutes));
        } catch (\Exception $e) {
            // Fallback to file cache if database cache fails
            $this->cacheToFile($purchaseCode, $result, $minutes);
        }
    }

    /**
     * Get cached license result
     */
    public function getCachedLicenseResult(string $purchaseCode): ?array
    {
        try {
            // Try Laravel cache first
            $cacheKey = 'license_result_' . md5($purchaseCode . $this->productSlug);
            $result = Cache::get($cacheKey);
            if ($result !== null) {
                return $result;
            }
        } catch (\Exception $e) {
            // Fallback to file cache if database cache fails
        }
        
        // Try file cache as fallback
        return $this->getFromFileCache($purchaseCode);
    }

    /**
     * Clear license cache
     */
    public function clearLicenseCache(string $purchaseCode): void
    {
        try {
            // Try Laravel cache first
            $cacheKey = 'license_result_' . md5($purchaseCode . $this->productSlug);
            Cache::forget($cacheKey);
        } catch (\Exception $e) {
            // Fallback to file cache if database cache fails
        }
        
        // Also clear file cache
        $this->clearFileCache($purchaseCode);
    }

    /**
     * Cache to file as fallback
     */
    private function cacheToFile(string $purchaseCode, array $result, int $minutes): void
    {
        try {
            $cacheDir = storage_path('app/cache');
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            
            $cacheKey = 'license_result_' . md5($purchaseCode . $this->productSlug);
            $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
            
            $cacheData = [
                'data' => $result,
                'expires_at' => time() + ($minutes * 60),
                'created_at' => time()
            ];
            
            file_put_contents($cacheFile, json_encode($cacheData));
        } catch (\Exception $e) {
            // Silently fail if file caching also fails
            Log::warning('File cache failed for license verification', [
                'error' => $e->getMessage(),
                'purchase_code' => substr($purchaseCode, 0, 8) . '...'
            ]);
        }
    }

    /**
     * Get from file cache
     */
    private function getFromFileCache(string $purchaseCode): ?array
    {
        try {
            $cacheKey = 'license_result_' . md5($purchaseCode . $this->productSlug);
            $cacheFile = storage_path('app/cache/' . $cacheKey . '.json');
            
            if (!file_exists($cacheFile)) {
                return null;
            }
            
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            
            if (!$cacheData || !isset($cacheData['expires_at'])) {
                return null;
            }
            
            // Check if cache is expired
            if (time() > $cacheData['expires_at']) {
                unlink($cacheFile);
                return null;
            }
            
            return $cacheData['data'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Clear file cache
     */
    private function clearFileCache(string $purchaseCode): void
    {
        try {
            $cacheKey = 'license_result_' . md5($purchaseCode . $this->productSlug);
            $cacheFile = storage_path('app/cache/' . $cacheKey . '.json');
            
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    /**
     * Clean error message to be user-friendly
     */
    private function cleanErrorMessage(string $message): string
    {
        // Clean up common error messages
        if (strpos($message, 'License is suspended') !== false) {
            return 'License verification failed. Please check your purchase code.';
        } elseif (strpos($message, 'Invalid purchase code') !== false) {
            return 'Invalid purchase code. Please check your code and try again.';
        } elseif (strpos($message, 'License not found') !== false) {
            return 'License not found. Please verify your purchase code.';
        } elseif (strpos($message, 'License expired') !== false) {
            return 'License has expired. Please renew your license.';
        } elseif (strpos($message, 'Domain not allowed') !== false) {
            return 'This domain is not authorized for this license.';
        } elseif (strpos($message, 'Too many attempts') !== false) {
            return 'Too many verification attempts. Please try again later.';
        }
        
        // Return a generic message for unknown errors
        return 'License verification failed. Please check your purchase code and try again.';
    }

    private function inferErrorCodeFromMessage(string $message): string
    {
        $map = [
            'suspend' => 'LICENSE_SUSPENDED',
            'invalid purchase code' => 'INVALID_PURCHASE_CODE',
            'not found' => 'LICENSE_NOT_FOUND',
            'expired' => 'LICENSE_EXPIRED',
            'domain not allowed' => 'DOMAIN_UNAUTHORIZED',
            'too many' => 'RATE_LIMIT',
            'unauthorized' => 'UNAUTHORIZED',
        ];
        $lower = strtolower($message);
        foreach ($map as $frag => $code) {
            if (str_contains($lower, $frag)) {
                return $code;
            }
        }
        return 'UNKNOWN_ERROR';
    }

    /**
     * Get product information
     */
    public function getProductInfo(): array
    {
        return [
            'name' => 'The Ultimate License Management System',
            'slug' => $this->productSlug,
            'version' => '1.0.0',
            'author' => 'My-Logos'
        ];
    }
}
