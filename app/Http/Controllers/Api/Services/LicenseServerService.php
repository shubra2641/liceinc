<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Services;

use App\Models\License;
use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * License Server Service for handling license operations.
 */
class LicenseServerService
{
    /**
     * Verify license and get product.
     */
    public function verifyLicenseAndGetProduct(string $licenseKey, string $domain): ?Product
    {
        try {
            $license = License::where('license_key', $licenseKey)
                ->where('status', 'active')
                ->first();

            if (!$license) {
                return null;
            }

            // Verify domain
            if (!$this->verifyDomain($license, $domain)) {
                return null;
            }

            return $license->product;
        } catch (\Exception $e) {
            Log::error('License verification failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify domain for license.
     */
    private function verifyDomain(License $license, string $domain): bool
    {
        $allowedDomains = $license->allowed_domains ?? [];
        if (!is_array($allowedDomains)) {
            $allowedDomains = [];
        }

        if (empty($allowedDomains)) {
            return true; // No domain restrictions
        }

        return in_array($domain, $allowedDomains);
    }

    /**
     * Get latest version for product.
     */
    public function getLatestVersion(Product $product): ?ProductUpdate
    {
        return ProductUpdate::where('product_id', $product->id)
            ->where('status', 'published')
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Get version history for product.
     */
    public function getVersionHistory(Product $product, int $limit = 10): array
    {
        return ProductUpdate::where('product_id', $product->id)
            ->where('status', 'published')
            ->orderBy('version', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Check if update is available.
     */
    public function isUpdateAvailable(Product $product, string $currentVersion): bool
    {
        $latestVersion = $this->getLatestVersion($product);

        if (!$latestVersion) {
            return false;
        }

        return version_compare($currentVersion, $latestVersion->version, '<');
    }
}
