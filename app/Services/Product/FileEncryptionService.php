<?php

declare(strict_types=1);

namespace App\Services\Product;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * File Encryption Service - Handles file encryption and decryption.
 */
class FileEncryptionService
{
    /**
     * Encrypt file content.
     */
    public function encryptContent(string $content, string $key): string
    {
        try {
            $iv = Str::random(16);
            $encrypted = openssl_encrypt($content, 'AES-256-CBC', $key, 0, $iv);

            if ($encrypted === false) {
                throw new \Exception('Failed to encrypt content');
            }

            return base64_encode($iv . $encrypted);
        } catch (\Exception $e) {
            Log::error('File encryption failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Decrypt file content.
     */
    public function decryptContent(string $encryptedContent, string $key): string
    {
        try {
            $data = base64_decode($encryptedContent);
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);

            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);

            if ($decrypted === false) {
                throw new \Exception('Failed to decrypt content');
            }

            return $decrypted;
        } catch (\Exception $e) {
            Log::error('File decryption failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate encryption key.
     */
    public function generateEncryptionKey(): string
    {
        return Str::random(32);
    }

    /**
     * Encrypt encryption key for storage.
     */
    public function encryptKey(string $key): string
    {
        return Crypt::encryptString($key);
    }

    /**
     * Decrypt encryption key from storage.
     */
    public function decryptKey(string $encryptedKey): string
    {
        return Crypt::decryptString($encryptedKey);
    }

    /**
     * Calculate file checksum.
     */
    public function calculateChecksum(string $content): string
    {
        return hash('sha256', $content);
    }

    /**
     * Verify file checksum.
     */
    public function verifyChecksum(string $content, string $expectedChecksum): bool
    {
        $actualChecksum = $this->calculateChecksum($content);
        return hash_equals($expectedChecksum, $actualChecksum);
    }
}
