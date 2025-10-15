<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\Product;
use App\Models\ProgrammingLanguage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * License File Service - Handles license file operations.
 */
class LicenseFileService
{
    /**
     * Delete old license files for a product.
     */
    public function deleteOldLicenseFiles(Product $product): void
    {
        try {
            if (!$product->id) {
                return;
            }

            $productDir = "licenses/{$product->id}";
            if (!Storage::disk('public')->exists($productDir)) {
                return;
            }

            $files = Storage::disk('public')->files($productDir);
            foreach ($files as $file) {
                Storage::disk('public')->delete($file);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting old license files', [
                'product_id' => $product->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Save license file.
     */
    public function saveLicenseFile(string $content, string $fileName, Product $product): string
    {
        try {
            if (!$product->id) {
                throw new \InvalidArgumentException('Invalid product for file saving');
            }

            $productDir = "licenses/{$product->id}";
            $filePath = $productDir . '/' . $fileName;

            Storage::disk('public')->put($filePath, $content);

            return $filePath;
        } catch (\Exception $e) {
            Log::error('Error saving license file', [
                'product_id' => $product->id ?? 'unknown',
                'file_name' => $fileName,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate file name for license file.
     */
    public function generateFileName(Product $product, ProgrammingLanguage $language): string
    {
        try {
            if (!$product->slug || !$language->slug) {
                throw new \InvalidArgumentException('Invalid product or language for filename generation');
            }

            $extension = $this->getFileExtensionForLanguage($language->slug);
            $timestamp = now()->format('Y-m-d_H-i-s');
            $sanitizedSlug = preg_replace('/[^a-zA-Z0-9_-]/', '', $product->slug);

            return "license-{$sanitizedSlug}-{$timestamp}.{$extension}";
        } catch (\Exception $e) {
            Log::error('Error generating filename', [
                'product_slug' => $product->slug ?? 'unknown',
                'language_slug' => $language->slug ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get file extension for programming language.
     */
    public function getFileExtensionForLanguage(string $languageSlug): string
    {
        $extensions = [
            'php' => 'php',
            'javascript' => 'js',
            'python' => 'py',
            'java' => 'java',
            'csharp' => 'cs',
            'cpp' => 'cpp',
            'c' => 'c',
            'ruby' => 'rb',
            'go' => 'go',
            'rust' => 'rs',
            'swift' => 'swift',
            'kotlin' => 'kt',
            'scala' => 'scala',
            'typescript' => 'ts',
            'dart' => 'dart',
            'lua' => 'lua',
            'perl' => 'pl',
            'r' => 'r',
            'matlab' => 'm',
            'sql' => 'sql',
            'html' => 'html',
            'css' => 'css',
            'xml' => 'xml',
            'json' => 'json',
            'yaml' => 'yml',
            'markdown' => 'md',
            'text' => 'txt',
        ];

        return $extensions[$languageSlug] ?? 'txt';
    }

    /**
     * Get file extensions for language.
     */
    public function getFileExtensionsForLanguage(string $languageSlug): array
    {
        $extensions = [
            'php' => ['php'],
            'javascript' => ['js', 'jsx'],
            'python' => ['py', 'pyw'],
            'java' => ['java'],
            'csharp' => ['cs'],
            'cpp' => ['cpp', 'cc', 'cxx'],
            'c' => ['c'],
            'ruby' => ['rb'],
            'go' => ['go'],
            'rust' => ['rs'],
            'swift' => ['swift'],
            'kotlin' => ['kt', 'kts'],
            'scala' => ['scala'],
            'typescript' => ['ts', 'tsx'],
            'dart' => ['dart'],
            'lua' => ['lua'],
            'perl' => ['pl', 'pm'],
            'r' => ['r'],
            'matlab' => ['m'],
            'sql' => ['sql'],
            'html' => ['html', 'htm'],
            'css' => ['css'],
            'xml' => ['xml'],
            'json' => ['json'],
            'yaml' => ['yml', 'yaml'],
            'markdown' => ['md', 'markdown'],
            'text' => ['txt'],
        ];

        return $extensions[$languageSlug] ?? ['txt'];
    }

    /**
     * Check if file exists.
     */
    public function fileExists(string $filePath): bool
    {
        return Storage::disk('public')->exists($filePath);
    }

    /**
     * Get file content.
     */
    public function getFileContent(string $filePath): ?string
    {
        try {
            if (!$this->fileExists($filePath)) {
                return null;
            }

            return Storage::disk('public')->get($filePath);
        } catch (\Exception $e) {
            Log::error('Error getting file content', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Delete file.
     */
    public function deleteFile(string $filePath): bool
    {
        try {
            if ($this->fileExists($filePath)) {
                return Storage::disk('public')->delete($filePath);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting file', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
