<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * File Security Service - Handles file security validation and processing.
 */
class FileSecurityService
{
    /**
     * Allowed file types.
     */
    private const ALLOWED_TYPES = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
        'archive' => ['zip', 'rar', '7z', 'tar', 'gz'],
        'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv'],
        'audio' => ['mp3', 'wav', 'ogg', 'aac', 'flac'],
    ];

    /**
     * Dangerous file extensions.
     */
    private const DANGEROUS_EXTENSIONS = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'php', 'asp', 'jsp',
        'sh', 'bash', 'csh', 'ksh', 'zsh', 'pl', 'py', 'rb', 'ps1', 'psm1', 'psd1',
    ];

    /**
     * Maximum file sizes by type.
     */
    private const MAX_SIZES = [
        'image' => 5242880, // 5MB
        'document' => 10485760, // 10MB
        'archive' => 52428800, // 50MB
        'video' => 104857600, // 100MB
        'audio' => 52428800, // 50MB
    ];

    /**
     * Validate file upload.
     */
    public function validateFileUpload(
        UploadedFile $file,
        string $type = 'image',
        int $maxSize = null
    ): array {
        try {
            $errors = [];

            // Check file size
            $maxSize = $maxSize ?? self::MAX_SIZES[$type] ?? 5242880;
            if ($file->getSize() > $maxSize) {
                $errors['size'] = ['File size exceeds maximum allowed size'];
            }

            // Check file type
            $allowedExtensions = self::ALLOWED_TYPES[$type] ?? [];
            if (!empty($allowedExtensions)) {
                $extension = strtolower($file->getClientOriginalExtension());
                if (!in_array($extension, $allowedExtensions)) {
                    $errors['type'] = ['File type not allowed'];
                }
            }

            // Check for dangerous extensions
            $extension = strtolower($file->getClientOriginalExtension());
            if (in_array($extension, self::DANGEROUS_EXTENSIONS)) {
                $errors['security'] = ['File type is not allowed for security reasons'];
            }

            // Check MIME type
            $mimeType = $file->getMimeType();
            if (!$this->isValidMimeType($mimeType, $type)) {
                $errors['mime'] = ['Invalid MIME type'];
            }

            // Check for malicious content
            if ($this->containsMaliciousContent($file)) {
                $errors['content'] = ['File contains potentially malicious content'];
            }

            // Check file name
            $fileName = $file->getClientOriginalName();
            if (!$this->isValidFileName($fileName)) {
                $errors['filename'] = ['Invalid file name'];
            }

            return [
                'valid' => empty($errors),
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error('File upload validation failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            return [
                'valid' => false,
                'errors' => ['validation' => ['File validation failed']],
            ];
        }
    }

    /**
     * Scan file for malware.
     */
    public function scanFileForMalware(UploadedFile $file): array
    {
        try {
            $content = file_get_contents($file->getPathname());
            $malwarePatterns = $this->getMalwarePatterns();

            $threats = [];
            foreach ($malwarePatterns as $pattern => $description) {
                if (preg_match($pattern, $content)) {
                    $threats[] = [
                        'pattern' => $pattern,
                        'description' => $description,
                        'severity' => $this->getThreatSeverity($pattern),
                    ];
                }
            }

            return [
                'clean' => empty($threats),
                'threats' => $threats,
                'scan_time' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('Malware scan failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            return [
                'clean' => false,
                'threats' => [['description' => 'Scan failed', 'severity' => 'high']],
                'scan_time' => now()->toISOString(),
            ];
        }
    }

    /**
     * Sanitize file name.
     */
    public function sanitizeFileName(string $fileName): string
    {
        try {
            // Remove path traversal attempts
            $fileName = str_replace(['../', '..\\', '/', '\\'], '', $fileName);

            // Remove dangerous characters
            $fileName = preg_replace('/[^\w\-_\.]/', '', $fileName);

            // Remove multiple dots
            $fileName = preg_replace('/\.{2,}/', '.', $fileName);

            // Ensure file has extension
            if (!str_contains($fileName, '.')) {
                $fileName .= '.txt';
            }

            // Limit length
            if (strlen($fileName) > 255) {
                $fileName = substr($fileName, 0, 255);
            }

            return $fileName;
        } catch (\Exception $e) {
            Log::error('File name sanitization failed', [
                'error' => $e->getMessage(),
                'file_name' => $fileName,
            ]);
            return 'sanitized_file.txt';
        }
    }

    /**
     * Generate secure file name.
     */
    public function generateSecureFileName(string $originalName, string $type = 'file'): string
    {
        try {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $timestamp = now()->format('YmdHis');
            $random = bin2hex(random_bytes(8));

            return "{$type}_{$timestamp}_{$random}.{$extension}";
        } catch (\Exception $e) {
            Log::error('Secure file name generation failed', [
                'error' => $e->getMessage(),
                'original_name' => $originalName,
            ]);
            return 'secure_file_' . time() . '.txt';
        }
    }

    /**
     * Store file securely.
     */
    public function storeFileSecurely(
        UploadedFile $file,
        string $path = 'uploads',
        string $disk = 'local'
    ): array {
        try {
            $fileName = $this->generateSecureFileName($file->getClientOriginalName());
            $fullPath = $path . '/' . $fileName;

            $stored = Storage::disk($disk)->putFileAs($path, $file, $fileName);

            if (!$stored) {
                throw new \Exception('Failed to store file');
            }

            return [
                'success' => true,
                'path' => $fullPath,
                'filename' => $fileName,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];
        } catch (\Exception $e) {
            Log::error('Secure file storage failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if MIME type is valid.
     */
    private function isValidMimeType(string $mimeType, string $type): bool
    {
        $validMimeTypes = [
            'image' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'
            ],
            'document' => [
                'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain', 'application/rtf'
            ],
            'archive' => [
                'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed',
                'application/x-tar', 'application/gzip'
            ],
            'video' => [
                'video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/x-flv'
            ],
            'audio' => [
                'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/aac', 'audio/flac'
            ],
        ];

        $allowedMimeTypes = $validMimeTypes[$type] ?? [];
        return in_array($mimeType, $allowedMimeTypes);
    }

    /**
     * Check if file contains malicious content.
     */
    private function containsMaliciousContent(UploadedFile $file): bool
    {
        try {
            $content = file_get_contents($file->getPathname());

            // Check for common malicious patterns
            $maliciousPatterns = [
                '/<script[^>]*>.*?<\/script>/i',
                '/javascript:/i',
                '/vbscript:/i',
                '/onload=/i',
                '/onerror=/i',
                '/eval\s*\(/i',
                '/exec\s*\(/i',
                '/system\s*\(/i',
                '/shell_exec\s*\(/i',
                '/passthru\s*\(/i',
                '/popen\s*\(/i',
                '/proc_open\s*\(/i',
            ];

            foreach ($maliciousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Malicious content check failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            return true; // Assume malicious on error
        }
    }

    /**
     * Check if file name is valid.
     */
    private function isValidFileName(string $fileName): bool
    {
        // Check for path traversal
        if (strpos($fileName, '..') !== false) {
            return false;
        }

        // Check for dangerous characters
        if (preg_match('/[<>:"|?*]/', $fileName)) {
            return false;
        }

        // Check length
        if (strlen($fileName) > 255) {
            return false;
        }

        return true;
    }

    /**
     * Get malware patterns.
     */
    private function getMalwarePatterns(): array
    {
        return [
            '/eval\s*\(/i' => 'Code execution attempt',
            '/exec\s*\(/i' => 'System command execution',
            '/system\s*\(/i' => 'System command execution',
            '/shell_exec\s*\(/i' => 'Shell command execution',
            '/passthru\s*\(/i' => 'System command execution',
            '/popen\s*\(/i' => 'Process opening',
            '/proc_open\s*\(/i' => 'Process opening',
            '/<script[^>]*>.*?<\/script>/i' => 'JavaScript injection',
            '/javascript:/i' => 'JavaScript protocol',
            '/vbscript:/i' => 'VBScript protocol',
            '/onload=/i' => 'Event handler injection',
            '/onerror=/i' => 'Event handler injection',
        ];
    }

    /**
     * Get threat severity.
     */
    private function getThreatSeverity(string $pattern): string
    {
        $highSeverityPatterns = [
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
        ];

        if (in_array($pattern, $highSeverityPatterns)) {
            return 'high';
        }

        return 'medium';
    }
}
