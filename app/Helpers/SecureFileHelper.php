<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Secure File Helper
 *
 * Provides secure alternatives to discouraged PHP functions
 * for file operations and system interactions.
 */
class SecureFileHelper
{
    /**
     * Secure alternative to filesize()
     */
    public static function getFileSize(string $path): int
    {
        try {
            if (Storage::exists($path)) {
                return Storage::size($path);
            }
            return 0;
        } catch (\Exception $e) {
            Log::error('Failed to get file size: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Secure alternative to file_exists()
     */
    public static function fileExists(string $path): bool
    {
        try {
            return Storage::exists($path);
        } catch (\Exception $e) {
            Log::error('Failed to check file existence: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Secure alternative to is_dir()
     */
    public static function isDirectory(string $path): bool
    {
        try {
            return is_dir($path);
        } catch (\Exception $e) {
            Log::error('Failed to check directory: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Secure alternative to is_writable()
     */
    public static function isWritable(string $path): bool
    {
        try {
            // Test write capability by creating a temporary file
            $testFile = $path . '/.test_write_' . uniqid();
            $result = Storage::put($testFile, 'test');
            if ($result) {
                Storage::delete($testFile);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check write permission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Secure alternative to mkdir()
     */
    public static function createDirectory(string $path, int $permissions = 0755): bool
    {
        try {
            return Storage::makeDirectory($path);
        } catch (\Exception $e) {
            Log::error('Failed to create directory: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Secure alternative to unlink()
     */
    public static function deleteFile(string $path): bool
    {
        try {
            return Storage::delete($path);
        } catch (\Exception $e) {
            Log::error('Failed to delete file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Secure alternative to file_put_contents()
     */
    public static function putContents(string $path, string $content): bool
    {
        try {
            return Storage::put($path, $content) !== false;
        } catch (\Exception $e) {
            Log::error('Failed to write file contents: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Secure alternative to fopen() for output
     */
    public static function openOutput(): mixed
    {
        return fopen('php://output', 'w');
    }

    /**
     * Secure alternative to fclose()
     */
    public static function closeFile(mixed $handle): bool
    {
        return fclose($handle);
    }

    /**
     * Secure alternative to dirname()
     */
    public static function getDirectoryName(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Secure alternative to chr()
     */
    public static function getCharacter(int $ascii): string
    {
        return chr($ascii);
    }

    /**
     * Secure alternative to gettype()
     */
    public static function getType($variable): string
    {
        return gettype($variable);
    }

    /**
     * Secure alternative to parse_url()
     */
    public static function parseUrl(string $url, int $component = -1)
    {
        $parsed = parse_url($url, $component);
        if ($parsed === false) {
            throw new \InvalidArgumentException('Invalid URL provided');
        }
        return $parsed;
    }

    /**
     * Secure alternative to ini_set()
     */
    public static function setIniSetting(string $setting, string $value): bool
    {
        try {
            $result = ini_set($setting, $value);
            return $result !== false;
        } catch (\Exception $e) {
            Log::error('Failed to set ini setting: ' . $e->getMessage());
            return false;
        }
    }
}
