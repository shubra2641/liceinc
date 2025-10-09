<?php

declare(strict_types=1);

/**
 * License Verification System for Laravel
 * Product: Ai Ecommerce
 * Generated: 2025-10-09 22:21:39
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LaravelLicenseVerifier
{
    protected $apiUrl = 'https://localhost/my-logos/public/api/license/verify';
    protected $productSlug = 'ai-ecommerce';
    protected $verificationKey = 'b8e5683af8c9d7e22bf62c348895b44cedbac6f23971f36a92706486735fac29';
    protected $apiToken = '0c5b4625ec372497f395c1662f5f5ff05f7cf584a79694890de7470d693a3488';

    /**
     * Verify license with purchase code
     * This method sends a single request to our system which handles both Envato and database verification
     */
    public function verifyLicense(string $purchaseCode, ?string $domain = null): array
    {
        try {
// Send single request to our system
$result = $this->verifyWithOurSystem($purchaseCode, $domain);
if ($result['valid']) {
    return $this->createLicenseResponse(true, $result['message'], $result['data']);
} else {
    return $this->createLicenseResponse(false, $result['message']);
}

        } catch (\Exception $e) {
return $this->createLicenseResponse(false, 'Verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify with our license system
     */
    protected function verifyWithOurSystem(string $purchaseCode, ?string $domain = null): array
    {
        try {
$response = Http::timeout(15)
    ->withHeaders([
        'Content-Type' => 'application/x-www-form-urlencoded',
        'User-Agent' => 'LicenseVerifier/1.0',
        'Authorization' => 'Bearer ' . $this->apiToken
    ])
    ->asForm()
    ->post($this->apiUrl, [
        'purchase_code' => $purchaseCode,
        'product_slug' => $this->productSlug,
        'domain' => $domain,
        'verification_key' => $this->verificationKey
    ]);

if ($response->successful()) {
    $data = $response->json();

    return $this->createLicenseResponse(
        $data['valid'] ?? false,
        $data['message'] ?? 'Verification completed',
        $data
    );
}

return $this->createLicenseResponse(false, 'Unable to verify license');
        } catch (\Exception $e) {
// Log::error('License API network error', ['error' => $e->getMessage()]);
return $this->createLicenseResponse(false, 'Network error: ' . $e->getMessage());
        }
    }

    /**
     * Create standardized response
     */
    protected function createLicenseResponse(bool $valid, string $message, ?array $data = null): array
    {
        return [
'valid' => $valid,
'message' => $message,
'data' => $data,
'verified_at' => now()->toISOString(),
'product' => $this->productSlug
        ];
    }

    /**
     * Cache license verification result
     */
    public function cacheLicenseResult(string $purchaseCode, array $result, int $minutes = 60): void
    {
        $cacheKey = 'license_result_' . md5($purchaseCode . $this->productSlug);
        Cache::put($cacheKey, $result, now()->addMinutes($minutes));
    }

    /**
     * Get cached license result
     */
    public function getCachedLicenseResult(string $purchaseCode): ?array
    {
        $cacheKey = 'license_result_' . md5($purchaseCode . $this->productSlug);
        return Cache::get($cacheKey);
    }

    /**
     * Clear license cache
     */
    public function clearLicenseCache(string $purchaseCode): void
    {
        $cacheKey = 'license_result_' . md5($purchaseCode . $this->productSlug);
        Cache::forget($cacheKey);
    }
}

// Usage example:
/*
// In your controller or service
use App\Services\LaravelLicenseVerifier;

$verifier = new LaravelLicenseVerifier();
$result = $verifier->verifyLicense('YOUR_PURCHASE_CODE', request()->getHost());

if ($result['valid']) {
    // License is valid
    $verifier->cacheLicenseResult('YOUR_PURCHASE_CODE', $result);
    return response()->json(['app.Status' => 'success', 'message' => 'License verified']);
} else {
    // License invalid
    return response()->json(['app.Status' => 'error', 'message' => $result['message']], 403);
}
*/