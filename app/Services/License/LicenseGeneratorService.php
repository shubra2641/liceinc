<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

/**
 * License Generator Service - Simplified
 */
class LicenseGeneratorService
{
    public function __construct(
        private LicenseTemplateService $templateService,
        private LicenseFileService $fileService
    ) {
    }

    /**
     * Generate license verification file for a product.
     */
    public function generateLicenseFile(Product $product): string
    {
        try {
            if (!$product->id) {
                throw new \InvalidArgumentException('Invalid product provided');
            }

            $product->refresh();
            $language = $product->programmingLanguage;
            if (!$language) {
                throw new \Exception('Programming language not found for product: ' . $product->id);
            }

            // Delete old files
            $this->fileService->deleteOldLicenseFiles($product);

            // Generate new file
            $template = $this->templateService->getLicenseTemplate($language);
            $fileContent = $this->templateService->compileTemplate($template, $product);
            $fileName = $this->fileService->generateFileName($product, $language);
            $filePath = $this->fileService->saveLicenseFile($fileContent, $fileName, $product);

            // Update product with new file path
            $product->update(['integration_file_path' => $filePath]);

            return $filePath;
        } catch (\Exception $e) {
            Log::error('Error generating license file', [
                'product_id' => $product->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get file extensions for language.
     */
    public function getFileExtensionsForLanguage(string $languageSlug): array
    {
        return $this->fileService->getFileExtensionsForLanguage($languageSlug);
    }

    /**
     * Get file extension for language.
     */
    public function getFileExtensionForLanguage(string $languageSlug): string
    {
        return $this->fileService->getFileExtensionForLanguage($languageSlug);
    }

    /**
     * Check if license file exists.
     */
    public function licenseFileExists(Product $product): bool
    {
        if (!$product->integration_file_path) {
            return false;
        }

        return $this->fileService->fileExists($product->integration_file_path);
    }

    /**
     * Get license file content.
     */
    public function getLicenseFileContent(Product $product): ?string
    {
        if (!$product->integration_file_path) {
            return null;
        }

        return $this->fileService->getFileContent($product->integration_file_path);
    }

    /**
     * Delete license file.
     */
    public function deleteLicenseFile(Product $product): bool
    {
        if (!$product->integration_file_path) {
            return true;
        }

        $deleted = $this->fileService->deleteFile($product->integration_file_path);

        if ($deleted) {
            $product->update(['integration_file_path' => null]);
        }

        return $deleted;
    }

    /**
     * Regenerate license file.
     */
    public function regenerateLicenseFile(Product $product): string
    {
        try {
            // Delete existing file
            $this->deleteLicenseFile($product);

            // Generate new file
            return $this->generateLicenseFile($product);
        } catch (\Exception $e) {
            Log::error('Error regenerating license file', [
                'product_id' => $product->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get license file info.
     */
    public function getLicenseFileInfo(Product $product): array
    {
        try {
            if (!$product->integration_file_path) {
                return [
                    'exists' => false,
                    'path' => null,
                    'size' => 0,
                    'created_at' => null,
                ];
            }

            $filePath = $product->integration_file_path;
            $exists = $this->fileService->fileExists($filePath);

            if (!$exists) {
                return [
                    'exists' => false,
                    'path' => $filePath,
                    'size' => 0,
                    'created_at' => null,
                ];
            }

            return [
                'exists' => true,
                'path' => $filePath,
                'size' => $this->getFileSize($filePath),
                'created_at' => $this->getFileCreatedAt($filePath),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting license file info', [
                'product_id' => $product->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return [
                'exists' => false,
                'path' => null,
                'size' => 0,
                'created_at' => null,
            ];
        }
    }

    /**
     * Get file size.
     */
    private function getFileSize(string $filePath): int
    {
        try {
            return \Illuminate\Support\Facades\Storage::disk('public')->size($filePath);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get file created at.
     */
    private function getFileCreatedAt(string $filePath): ?string
    {
        try {
            $timestamp = \Illuminate\Support\Facades\Storage::disk('public')->lastModified($filePath);
            return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
