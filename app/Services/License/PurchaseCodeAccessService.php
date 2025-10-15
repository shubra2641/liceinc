<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Purchase Code Access Service - Handles access control for purchase codes.
 */
class PurchaseCodeAccessService
{
    /**
     * Check if user has access to product.
     */
    public function userHasProductAccess(User $user, int $productId): bool
    {
        try {
            $license = $user->licenses()
                ->where('product_id', $productId)
                ->where('status', 'active')
                ->first();

            return $license !== null;
        } catch (\Exception $e) {
            Log::error('Error checking user product access', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user has access to knowledge base.
     */
    public function userHasKnowledgeBaseAccess(User $user, int $productId): bool
    {
        try {
            $license = $user->licenses()
                ->where('product_id', $productId)
                ->where('status', 'active')
                ->where('support_expires_at', '>', now())
                ->first();

            return $license !== null;
        } catch (\Exception $e) {
            Log::error('Error checking user knowledge base access', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get user's active licenses.
     */
    public function getUserActiveLicenses(User $user): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return $user->licenses()
                ->where('status', 'active')
                ->with('product')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting user active licenses', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    /**
     * Get user's licenses for product.
     */
    public function getUserLicensesForProduct(User $user, int $productId): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return $user->licenses()
                ->where('product_id', $productId)
                ->with('product')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting user licenses for product', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    /**
     * Check if user can download product files.
     */
    public function userCanDownloadFiles(User $user, int $productId): bool
    {
        try {
            $license = $user->licenses()
                ->where('product_id', $productId)
                ->where('status', 'active')
                ->where('license_expires_at', '>', now())
                ->first();

            return $license !== null;
        } catch (\Exception $e) {
            Log::error('Error checking download access', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get user's product access summary.
     */
    public function getUserProductAccessSummary(User $user): array
    {
        try {
            $licenses = $this->getUserActiveLicenses($user);

            $summary = [
                'total_licenses' => $licenses->count(),
                'products' => [],
                'can_download' => [],
                'can_access_kb' => [],
            ];

            foreach ($licenses as $license) {
                $productId = $license->product_id;
                $summary['products'][] = $productId;

                if ($this->userCanDownloadFiles($user, $productId)) {
                    $summary['can_download'][] = $productId;
                }

                if ($this->userHasKnowledgeBaseAccess($user, $productId)) {
                    $summary['can_access_kb'][] = $productId;
                }
            }

            return $summary;
        } catch (\Exception $e) {
            Log::error('Error getting user product access summary', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'total_licenses' => 0,
                'products' => [],
                'can_download' => [],
                'can_access_kb' => [],
            ];
        }
    }

    /**
     * Check if license is valid for product.
     */
    public function isLicenseValidForProduct(License $license, int $productId): bool
    {
        try {
            return $license->product_id === $productId
                && $license->status === 'active'
                && $license->license_expires_at > now();
        } catch (\Exception $e) {
            Log::error('Error checking license validity for product', [
                'license_id' => $license->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get license for user and product.
     */
    public function getLicenseForUserAndProduct(User $user, int $productId): ?License
    {
        try {
            return $user->licenses()
                ->where('product_id', $productId)
                ->where('status', 'active')
                ->first();
        } catch (\Exception $e) {
            Log::error('Error getting license for user and product', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
