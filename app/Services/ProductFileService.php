<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Product File Service - Simplified.
 */
class ProductFileService
{
    /**
     * Upload and encrypt a file for a product.
     */
    public function uploadFile(Product $product, UploadedFile $file, ?string $description = null): ProductFile
    {
        try {
            $this->validateFile($file);

            $encryptionKey = Str::random(32);
            $originalName = $this->sanitizeInput($file->getClientOriginalName());
            $extension = $this->sanitizeInput($file->getClientOriginalExtension());
            $encryptedName = Str::uuid().'.'.$extension;

            $directory = 'product-files/'.$product->id;
            $filePath = $directory.'/'.$encryptedName;

            if (strpos($filePath, '..') !== false) {
                throw new \InvalidArgumentException('Invalid file path detected');
            }

            $fileContent = file_get_contents($file->getRealPath());
            if ($fileContent === false) {
                throw new \Exception('Failed to read uploaded file content');
            }

            $checksum = hash('sha256', $fileContent);
            $encryptedContent = $this->encryptContent($fileContent, $encryptionKey);

            Storage::disk('private')->put($filePath, $encryptedContent);
            $encryptedKey = Crypt::encryptString($encryptionKey);

            $productFile = ProductFile::create([
                'product_id' => $product->id,
                'original_name' => $originalName,
                'encrypted_name' => $encryptedName,
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'encryption_key' => $encryptedKey,
                'checksum' => $checksum,
                'description' => $this->sanitizeInput($description),
            ]);

            return $productFile;
        } catch (\Exception $e) {
            Log::error('Error uploading product file', [
                'product_id' => $product->id,
                'original_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Download a file for a user.
     */
    public function downloadFile(ProductFile $file, ?int $userId = null): ?array
    {
        try {
            if ($userId) {
                $permissions = $this->userCanDownloadFiles($file->product, $userId);
                if (! $permissions['can_download']) {
                    return null;
                }
            }

            if (! $file->fileExists()) {
                Log::error('File not found for download', [
                    'file_id' => $file->id,
                    'user_id' => $userId,
                    'file_path' => $file->file_path ?? 'unknown',
                ]);

                return null;
            }

            $content = $file->getDecryptedContent();
            if (! $content) {
                Log::error('Failed to decrypt file', [
                    'file_id' => $file->id,
                    'user_id' => $userId,
                ]);

                return null;
            }

            if (hash('sha256', $content) !== $file->checksum) {
                Log::error('File checksum mismatch', [
                    'file_id' => $file->id,
                    'user_id' => $userId,
                ]);

                return null;
            }

            $file->incrementDownloadCount();

            return [
                'content' => $content,
                'filename' => $file->original_name,
                'mime_type' => $file->file_type,
                'size' => $file->file_size,
            ];
        } catch (\Exception $e) {
            Log::error('Error downloading product file', [
                'file_id' => $file->id ?? 'unknown',
                'user_id' => $userId ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a product file.
     */
    public function deleteFile(ProductFile $file): bool
    {
        try {
            if ($file->fileExists()) {
                Storage::disk('private')->delete($file->file_path);
            }

            $file->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete product file', [
                'file_id' => $file->id ?? 'unknown',
                'product_id' => $file->product_id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get files for a product.
     */
    public function getProductFiles(Product $product, bool $activeOnly = true): \Illuminate\Database\Eloquent\Collection
    {
        try {
            $query = $product->files();
            if ($activeOnly) {
                $query->where('is_active', true);
            }

            return $query->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            Log::error('Error getting product files', [
                'product_id' => $product->id,
                'active_only' => $activeOnly,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if user can download files.
     */
    public function userCanDownloadFiles(Product $product, int $userId): array
    {
        $hasLicense = $this->userHasLicense($product, $userId);
        $hasPaidInvoice = $this->userHasPaidInvoice($product, $userId);

        return [
            'can_download' => $hasLicense && $hasPaidInvoice,
            'has_license' => $hasLicense,
            'has_paid_invoice' => $hasPaidInvoice,
            'message' => $this->getDownloadPermissionMessage($hasLicense, $hasPaidInvoice),
        ];
    }

    /**
     * Get all available versions for a product.
     */
    public function getAllProductVersions(Product $product, int $userId): array
    {
        $permissions = $this->userCanDownloadFiles($product, $userId);
        if (! $permissions['can_download']) {
            return [];
        }

        $allVersions = [];

        $updates = $product->updates()
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->get();

        foreach ($updates as $update) {
            $updateFile = $this->createUpdateFileRecord($update);
            $updateFile->is_update = true;
            $updateFile->update_info = $update->toArray();
            $allVersions[] = $updateFile;
        }

        $baseFiles = $product->files()
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($baseFiles as $file) {
            $file->is_update = false;
            $file->update_info = null;
            $allVersions[] = $file;
        }

        usort($allVersions, function ($a, $b) {
            return $b->created_at <=> $a->created_at;
        });

        return ['all_versions' => $allVersions];
    }

    /**
     * Get the latest product file.
     */
    public function getLatestProductFile(Product $product, int $userId): ?ProductFile
    {
        $permissions = $this->userCanDownloadFiles($product, $userId);
        if (! $permissions['can_download']) {
            return null;
        }

        $latestUpdate = $product->updates()
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->first();

        if ($latestUpdate && $latestUpdate->update_file_path) {
            return $this->createUpdateFileRecord($latestUpdate);
        }

        return $product->files()
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get the latest product version.
     */
    public function getLatestProductVersion(Product $product): string
    {
        $latestUpdate = $product->updates()
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->first();

        if ($latestUpdate) {
            return $latestUpdate->version;
        }

        return $product->version ?? '1.0';
    }

    /**
     * Download update file.
     */
    public function downloadUpdateFile(\App\Models\ProductUpdate $update, int $userId): array
    {
        if (! $update->file_path || ! Storage::disk('private')->exists($update->file_path)) {
            throw new \Exception('Update file not found');
        }

        $fileName = $update->title.'_v'.$update->version.'.zip';

        return [
            'content' => Storage::disk('private')->get($update->file_path),
            'filename' => $fileName,
            'mime_type' => 'application/zip',
            'size' => Storage::disk('private')->size($update->file_path),
        ];
    }

    /**
     * Sanitize input data.
     */
    private function sanitizeInput(?string $input): string
    {
        if ($input === null) {
            return '';
        }

        $input = str_replace(["\0", "\x00"], '', $input);
        $input = trim($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

        return $input;
    }

    /**
     * Validate uploaded file.
     */
    private function validateFile(UploadedFile $file): void
    {
        try {
            if ($file->getSize() > 100 * 1024 * 1024) {
                throw new \Exception('File size cannot exceed 100MB');
            }

            $allowedTypes = [
                'application/zip',
                'application/x-zip-compressed',
                'application/x-rar-compressed',
                'application/pdf',
                'text/plain',
                'application/json',
                'application/xml',
                'text/xml',
                'application/javascript',
                'text/css',
                'text/html',
                'application/php',
                'application/x-php',
                'text/php',
                'application/sql',
                'text/sql',
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
            ];

            $mimeType = $file->getMimeType();
            if (! in_array($mimeType, $allowedTypes)) {
                throw new \Exception('File type not allowed: '.$mimeType);
            }

            $this->scanFileForMaliciousContent($file);
        } catch (\Exception $e) {
            Log::error('File validation failed', [
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Scan file for malicious content.
     */
    private function scanFileForMaliciousContent(UploadedFile $file): void
    {
        try {
            $content = file_get_contents($file->getRealPath());
            if ($content === false) {
                throw new \Exception('Failed to read file content for scanning');
            }

            $maliciousPatterns = [
                '/eval\s*\(/i',
                '/base64_decode\s*\(/i',
                '/exec\s*\(/i',
                '/system\s*\(/i',
                '/shell_exec\s*\(/i',
                '/passthru\s*\(/i',
                '/file_get_contents\s*\(\s*["\']http/i',
                '/curl_exec\s*\(/i',
                '/fopen\s*\(\s*["\']http/i',
                '/preg_replace\s*\(\s*["\'].*\/e/i',
                '/assert\s*\(/i',
                '/create_function\s*\(/i',
                '/call_user_func\s*\(/i',
                '/call_user_func_array\s*\(/i',
            ];

            foreach ($maliciousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    Log::error('Malicious content detected in file', [
                        'filename' => $file->getClientOriginalName(),
                        'pattern' => $pattern,
                        'file_size' => $file->getSize(),
                    ]);
                    throw new \Exception('File contains potentially malicious content');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error scanning file for malicious content', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Encrypt file content.
     */
    private function encryptContent(string $content, string $key): string
    {
        try {
            if (empty($content)) {
                throw new \InvalidArgumentException('Content cannot be empty for encryption');
            }
            if (empty($key)) {
                throw new \InvalidArgumentException('Encryption key cannot be empty');
            }

            $iv = substr(hash('sha256', $key), 0, 16);
            $encrypted = openssl_encrypt($content, 'AES-256-CBC', $key, 0, $iv);

            if ($encrypted === false) {
                throw new \Exception('Failed to encrypt content');
            }

            return $encrypted;
        } catch (\Exception $e) {
            Log::error('Error encrypting file content', [
                'content_length' => strlen($content),
                'key_length' => strlen($key),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if user has active license.
     */
    private function userHasLicense(Product $product, int $userId): bool
    {
        return $product->licenses()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('license_expires_at')
                    ->orWhere('license_expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Check if user has paid invoice.
     */
    private function userHasPaidInvoice(Product $product, int $userId): bool
    {
        return \App\Models\Invoice::where('product_id', $product->id)
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->exists();
    }

    /**
     * Get download permission message.
     */
    private function getDownloadPermissionMessage(bool $hasLicense, bool $hasPaidInvoice): string
    {
        if (! $hasLicense && ! $hasPaidInvoice) {
            return trans('app.You must purchase the product and pay the invoice first');
        } elseif (! $hasLicense) {
            return trans('app.You must purchase the product first');
        } elseif (! $hasPaidInvoice) {
            return trans('app.You must pay the invoice first');
        }

        return '';
    }

    /**
     * Create update file record.
     */
    private function createUpdateFileRecord(\Illuminate\Database\Eloquent\Model $update): ProductFile
    {
        $file = new ProductFile();
        $file->product_id = is_numeric($update->product_id) ? (int)$update->product_id : 0;
        $file->original_name = (is_string($update->title) ? $update->title : '').'_v'.(is_string($update->version) ? $update->version : '').'.zip';
        $filePath = $update->file_path ?? '';
        $file->file_path = is_string($filePath) ? $filePath : '';
        $file->file_size = is_numeric($update->file_size ?? 0) ? (int)($update->file_size ?? 0) : 0;
        $file->file_extension = 'zip';
        $file->description = is_string($update->description) ? $update->description : null;
        $file->is_active = true;
        $file->download_count = 0;
        $file->created_at = $update->created_at instanceof \Illuminate\Support\Carbon ? $update->created_at : null;
        $file->updated_at = $update->updated_at instanceof \Illuminate\Support\Carbon ? $update->updated_at : null;

        $file->formatted_size = $file->file_size > 0 ? number_format($file->file_size / 1024 / 1024, 2).' MB' : 'Unknown';
        $file->update_info = $update->toArray();
        $file->is_update = true;

        return $file;
    }
}
