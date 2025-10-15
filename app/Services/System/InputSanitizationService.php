<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Support\Facades\Log;

/**
 * Input Sanitization Service - Handles input sanitization and validation.
 */
class InputSanitizationService
{
    /**
     * Sanitize input data.
     */
    public function sanitizeInput(array $input): array
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Input sanitization failed', [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize string input.
     */
    public function sanitizeString(string $input): string
    {
        try {
            // Remove null bytes
            $input = str_replace("\0", '', $input);

            // Remove control characters
            $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);

            // Trim whitespace
            $input = trim($input);

            // Remove excessive whitespace
            $input = preg_replace('/\s+/', ' ', $input);

            // Remove HTML tags
            $input = strip_tags($input);

            // Escape special characters
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

            return $input;
        } catch (\Exception $e) {
            Log::error('String sanitization failed', [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize HTML content.
     */
    public function sanitizeHtml(string $html): string
    {
        try {
            // Remove dangerous tags
            $dangerousTags = ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button'];
            foreach ($dangerousTags as $tag) {
                $html = preg_replace('/<' . $tag . '[^>]*>.*?<\/' . $tag . '>/i', '', $html);
                $html = preg_replace('/<' . $tag . '[^>]*\/>/i', '', $html);
            }

            // Remove dangerous attributes
            $dangerousAttributes = ['onload', 'onerror', 'onclick', 'onmouseover', 'onfocus', 'onblur'];
            foreach ($dangerousAttributes as $attr) {
                $html = preg_replace('/\s+' . $attr . '\s*=\s*["\'][^"\']*["\']/i', '', $html);
            }

            // Remove javascript: protocols
            $html = preg_replace('/javascript:/i', '', $html);

            return $html;
        } catch (\Exception $e) {
            Log::error('HTML sanitization failed', [
                'error' => $e->getMessage(),
                'html' => $html,
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize email address.
     */
    public function sanitizeEmail(string $email): string
    {
        try {
            $email = trim($email);
            $email = strtolower($email);
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid email format');
            }

            return $email;
        } catch (\Exception $e) {
            Log::error('Email sanitization failed', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize URL.
     */
    public function sanitizeUrl(string $url): string
    {
        try {
            $url = trim($url);
            $url = filter_var($url, FILTER_SANITIZE_URL);

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \InvalidArgumentException('Invalid URL format');
            }

            return $url;
        } catch (\Exception $e) {
            Log::error('URL sanitization failed', [
                'error' => $e->getMessage(),
                'url' => $url,
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize phone number.
     */
    public function sanitizePhone(string $phone): string
    {
        try {
            // Remove all non-digit characters except + and -
            $phone = preg_replace('/[^\d+\-]/', '', $phone);

            // Remove leading zeros
            $phone = ltrim($phone, '0');

            // Add country code if missing
            if (!str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }

            return $phone;
        } catch (\Exception $e) {
            Log::error('Phone sanitization failed', [
                'error' => $e->getMessage(),
                'phone' => $phone,
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize numeric input.
     */
    public function sanitizeNumeric(string $input): float
    {
        try {
            // Remove non-numeric characters except decimal point
            $input = preg_replace('/[^\d.]/', '', $input);

            // Ensure only one decimal point
            $parts = explode('.', $input);
            if (count($parts) > 2) {
                $input = $parts[0] . '.' . implode('', array_slice($parts, 1));
            }

            $numeric = (float)$input;

            if (!is_numeric($input)) {
                throw new \InvalidArgumentException('Invalid numeric input');
            }

            return $numeric;
        } catch (\Exception $e) {
            Log::error('Numeric sanitization failed', [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);
            throw $e;
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
            throw $e;
        }
    }

    /**
     * Validate and sanitize JSON input.
     */
    public function sanitizeJson(string $json): array
    {
        try {
            $decoded = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON format');
            }

            return $this->sanitizeInput($decoded);
        } catch (\Exception $e) {
            Log::error('JSON sanitization failed', [
                'error' => $e->getMessage(),
                'json' => $json,
            ]);
            throw $e;
        }
    }

    /**
     * Check if input contains XSS attempts.
     */
    public function containsXss(string $input): bool
    {
        try {
            $xssPatterns = [
                '/<script[^>]*>.*?<\/script>/i',
                '/javascript:/i',
                '/on\w+\s*=/i',
                '/<iframe[^>]*>/i',
                '/<object[^>]*>/i',
                '/<embed[^>]*>/i',
                '/<form[^>]*>/i',
                '/<input[^>]*>/i',
                '/<button[^>]*>/i',
            ];

            foreach ($xssPatterns as $pattern) {
                if (preg_match($pattern, $input)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('XSS check failed', [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);
            return false;
        }
    }

    /**
     * Check if input contains SQL injection attempts.
     */
    public function containsSqlInjection(string $input): bool
    {
        try {
            $sqlPatterns = [
                '/(\bunion\b.*\bselect\b)/i',
                '/(\bselect\b.*\bfrom\b)/i',
                '/(\binsert\b.*\binto\b)/i',
                '/(\bdelete\b.*\bfrom\b)/i',
                '/(\bdrop\b.*\btable\b)/i',
                '/(\balter\b.*\btable\b)/i',
                '/(\bexec\b.*\b\(\))/i',
                '/(\bexecute\b.*\b\(\))/i',
                '/(\bcreate\b.*\btable\b)/i',
                '/(\bupdate\b.*\bset\b)/i',
            ];

            foreach ($sqlPatterns as $pattern) {
                if (preg_match($pattern, $input)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('SQL injection check failed', [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);
            return false;
        }
    }
}
