<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Security Validation Service - Handles security-related validation.
 */
class SecurityValidationService
{
    /**
     * Validate input for security threats.
     */
    public function validateInput(array $input): array
    {
        try {
            $rules = $this->getSecurityValidationRules();
            $validator = Validator::make($input, $rules);

            if ($validator->fails()) {
                return [
                    'valid' => false,
                    'errors' => $validator->errors()->toArray(),
                    'sanitized' => $this->sanitizeInput($input),
                ];
            }

            return [
                'valid' => true,
                'errors' => [],
                'sanitized' => $input,
            ];
        } catch (\Exception $e) {
            Log::error('Security validation failed', [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);

            return [
                'valid' => false,
                'errors' => ['validation' => ['Security validation failed']],
                'sanitized' => $this->sanitizeInput($input),
            ];
        }
    }

    /**
     * Validate file upload.
     */
    public function validateFileUpload($file, array $allowedTypes = [], int $maxSize = 5242880): array
    {
        try {
            if (!$file) {
                return [
                    'valid' => false,
                    'errors' => ['file' => ['No file provided']],
                ];
            }

            $errors = [];

            // Check file size
            if ($file->getSize() > $maxSize) {
                $errors['size'] = ['File size exceeds maximum allowed size'];
            }

            // Check file type
            if (!empty($allowedTypes) && !in_array($file->getMimeType(), $allowedTypes)) {
                $errors['type'] = ['File type not allowed'];
            }

            // Check file extension
            $extension = $file->getClientOriginalExtension();
            $allowedExtensions = $this->getAllowedExtensions($allowedTypes);
            if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
                $errors['extension'] = ['File extension not allowed'];
            }

            // Check for malicious content
            if ($this->containsMaliciousContent($file)) {
                $errors['content'] = ['File contains potentially malicious content'];
            }

            return [
                'valid' => empty($errors),
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error('File upload validation failed', [
                'error' => $e->getMessage(),
                'file' => $file ? $file->getClientOriginalName() : null,
            ]);

            return [
                'valid' => false,
                'errors' => ['validation' => ['File validation failed']],
            ];
        }
    }

    /**
     * Validate API request.
     */
    public function validateApiRequest(Request $request): array
    {
        try {
            $errors = [];

            // Check required headers
            $requiredHeaders = ['User-Agent', 'Accept'];
            foreach ($requiredHeaders as $header) {
                if (!$request->hasHeader($header)) {
                    $errors['headers'][] = "Missing required header: {$header}";
                }
            }

            // Check content type for POST/PUT requests
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
                $contentType = $request->header('Content-Type');
                if (!$contentType || !str_contains($contentType, 'application/json')) {
                    $errors['content_type'][] = 'Invalid or missing Content-Type header';
                }
            }

            // Check request size
            $maxSize = 1024 * 1024; // 1MB
            if ($request->header('Content-Length') && (int)$request->header('Content-Length') > $maxSize) {
                $errors['size'][] = 'Request size exceeds maximum allowed size';
            }

            return [
                'valid' => empty($errors),
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error('API request validation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return [
                'valid' => false,
                'errors' => ['validation' => ['API request validation failed']],
            ];
        }
    }

    /**
     * Validate user input.
     */
    public function validateUserInput(array $input, array $rules = []): array
    {
        try {
            $defaultRules = $this->getUserInputValidationRules();
            $finalRules = array_merge($defaultRules, $rules);

            $validator = Validator::make($input, $finalRules);

            if ($validator->fails()) {
                return [
                    'valid' => false,
                    'errors' => $validator->errors()->toArray(),
                    'sanitized' => $this->sanitizeInput($input),
                ];
            }

            return [
                'valid' => true,
                'errors' => [],
                'sanitized' => $this->sanitizeInput($input),
            ];
        } catch (\Exception $e) {
            Log::error('User input validation failed', [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);

            return [
                'valid' => false,
                'errors' => ['validation' => ['User input validation failed']],
                'sanitized' => $this->sanitizeInput($input),
            ];
        }
    }

    /**
     * Get security validation rules.
     */
    private function getSecurityValidationRules(): array
    {
        return [
            '*.password' => 'required|string|min:8|max:255',
            '*.email' => 'required|email|max:255',
            '*.name' => 'required|string|max:255',
            '*.username' => 'required|string|alpha_dash|max:255',
            '*.phone' => 'nullable|string|max:20',
            '*.address' => 'nullable|string|max:500',
            '*.description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get user input validation rules.
     */
    private function getUserInputValidationRules(): array
    {
        return [
            'password' => 'required|string|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'username' => 'required|string|alpha_dash|max:255|min:3',
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[1-9][\d]{0,15}$/',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Sanitize input.
     */
    private function sanitizeInput(array $input): array
    {
        $sanitized = [];
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Sanitize string.
     */
    private function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Remove control characters
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);

        // Trim whitespace
        $value = trim($value);

        // Remove excessive whitespace
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    /**
     * Get allowed extensions for file types.
     */
    private function getAllowedExtensions(array $allowedTypes): array
    {
        $extensionMap = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'application/pdf' => ['pdf'],
            'text/plain' => ['txt'],
            'application/zip' => ['zip'],
            'application/x-zip-compressed' => ['zip'],
        ];

        $extensions = [];
        foreach ($allowedTypes as $type) {
            if (isset($extensionMap[$type])) {
                $extensions = array_merge($extensions, $extensionMap[$type]);
            }
        }

        return array_unique($extensions);
    }

    /**
     * Check if file contains malicious content.
     */
    private function containsMaliciousContent($file): bool
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
}
