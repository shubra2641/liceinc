<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Services;

use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Update Service for handling update operations.
 */
class UpdateService
{
    /**
     * Get update info for product.
     */
    public function getUpdateInfo(Product $product, string $currentVersion): array
    {
        $latestVersion = $this->getLatestVersion($product);
        
        if (!$latestVersion) {
            return [
                'update_available' => false,
                'message' => 'No updates available'
            ];
        }

        $isUpdateAvailable = version_compare($currentVersion, $latestVersion->version, '<');

        return [
            'update_available' => $isUpdateAvailable,
            'latest_version' => $latestVersion->version,
            'current_version' => $currentVersion,
            'changelog' => $latestVersion->changelog,
            'download_url' => $this->getDownloadUrl($latestVersion),
            'file_size' => $this->getFileSize($latestVersion),
            'checksum' => $latestVersion->checksum
        ];
    }

    /**
     * Get latest version for product.
     */
    private function getLatestVersion(Product $product): ?ProductUpdate
    {
        return ProductUpdate::where('product_id', $product->id)
            ->where('status', 'published')
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Get download URL for update.
     */
    private function getDownloadUrl(ProductUpdate $update): string
    {
        return route('api.license.download-update', [
            'id' => $update->id,
            'token' => $update->download_token
        ]);
    }

    /**
     * Get file size for update.
     */
    private function getFileSize(ProductUpdate $update): int
    {
        if ($update->file_path && Storage::exists($update->file_path)) {
            return Storage::size($update->file_path);
        }

        return 0;
    }

    /**
     * Download update file.
     */
    public function downloadUpdate(ProductUpdate $update): ?string
    {
        if (!$update->file_path || !Storage::exists($update->file_path)) {
            Log::error('Update file not found: ' . $update->file_path);
            return null;
        }

        return Storage::path($update->file_path);
    }
}
