<?php

declare(strict_types=1);

namespace App\Services\Product;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * File Validation Service - Handles validation for file operations.
 */
class FileValidationService
{
    /**
     * Validate uploaded file.
     */
    public function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('Invalid file upload');
        }

        if ($file->getSize() === 0) {
            throw new InvalidArgumentException('File is empty');
        }

        if ($file->getSize() > 100 * 1024 * 1024) { // 100MB limit
            throw new InvalidArgumentException('File is too large');
        }

        $allowedExtensions = ['zip', 'rar', 'pdf', 'doc', 'docx', 'txt', 'md'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            throw new InvalidArgumentException('File type not allowed');
        }

        $allowedMimeTypes = [
            'application/zip',
            'application/x-rar-compressed',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'text/markdown'
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new InvalidArgumentException('File MIME type not allowed');
        }
    }

    /**
     * Validate file path.
     */
    public function validateFilePath(string $filePath): void
    {
        if (empty($filePath)) {
            throw new InvalidArgumentException('File path cannot be empty');
        }

        if (strpos($filePath, '..') !== false) {
            throw new InvalidArgumentException('Invalid file path detected');
        }

        if (strlen($filePath) > 255) {
            throw new InvalidArgumentException('File path too long');
        }
    }

    /**
     * Validate file content for malicious patterns.
     */
    public function validateFileContent(string $content): void
    {
        if (empty($content)) {
            throw new InvalidArgumentException('File content is empty');
        }

        $maliciousPatterns = [
            '/<script[^>]*>.*?<\/script>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new InvalidArgumentException('File contains potentially malicious content');
            }
        }
    }

    /**
     * Sanitize input string.
     */
    public function sanitizeInput(string $input): string
    {
        return trim(strip_tags($input));
    }

    /**
     * Validate file size.
     */
    public function validateFileSize(int $size): void
    {
        if ($size <= 0) {
            throw new InvalidArgumentException('File size must be positive');
        }

        if ($size > 100 * 1024 * 1024) { // 100MB limit
            throw new InvalidArgumentException('File size exceeds limit');
        }
    }

    /**
     * Validate file extension.
     */
    public function validateFileExtension(string $extension): void
    {
        $allowedExtensions = ['zip', 'rar', 'pdf', 'doc', 'docx', 'txt', 'md'];
        $extension = strtolower($extension);

        if (!in_array($extension, $allowedExtensions)) {
            throw new InvalidArgumentException('File extension not allowed');
        }
    }
}
