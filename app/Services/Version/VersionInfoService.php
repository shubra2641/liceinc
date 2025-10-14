<?php

declare(strict_types=1);

namespace App\Services\Version;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Version Info Service
 * 
 * Handles version information retrieval
 */
class VersionInfoService
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get version information
     */
    public function getVersionInfo(string $version): array
    {
        $cacheKey = "version_info_{$version}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($version) {
            return $this->fetchVersionInfo($version);
        });
    }

    /**
     * Get update instructions
     */
    public function getUpdateInstructions(string $version): array
    {
        $cacheKey = "update_instructions_{$version}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($version) {
            return $this->fetchUpdateInstructions($version);
        });
    }

    /**
     * Get version status
     */
    public function getVersionStatus(): array
    {
        $cacheKey = 'version_status';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return $this->fetchVersionStatus();
        });
    }

    /**
     * Fetch version information from remote
     */
    private function fetchVersionInfo(string $version): array
    {
        try {
            $response = Http::timeout(30)->get($this->getVersionInfoUrl($version));
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return $this->getDefaultVersionInfo($version);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch version info', [
                'version' => $version,
                'error' => $e->getMessage()
            ]);
            
            return $this->getDefaultVersionInfo($version);
        }
    }

    /**
     * Fetch update instructions from remote
     */
    private function fetchUpdateInstructions(string $version): array
    {
        try {
            $response = Http::timeout(30)->get($this->getUpdateInstructionsUrl($version));
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return $this->getDefaultUpdateInstructions($version);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch update instructions', [
                'version' => $version,
                'error' => $e->getMessage()
            ]);
            
            return $this->getDefaultUpdateInstructions($version);
        }
    }

    /**
     * Fetch version status from remote
     */
    private function fetchVersionStatus(): array
    {
        try {
            $response = Http::timeout(30)->get($this->getVersionStatusUrl());
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return $this->getDefaultVersionStatus();
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch version status', [
                'error' => $e->getMessage()
            ]);
            
            return $this->getDefaultVersionStatus();
        }
    }

    /**
     * Get version info URL
     */
    private function getVersionInfoUrl(string $version): string
    {
        return config('app.update_server_url', 'https://updates.example.com') . "/version/{$version}";
    }

    /**
     * Get update instructions URL
     */
    private function getUpdateInstructionsUrl(string $version): string
    {
        return config('app.update_server_url', 'https://updates.example.com') . "/instructions/{$version}";
    }

    /**
     * Get version status URL
     */
    private function getVersionStatusUrl(): string
    {
        return config('app.update_server_url', 'https://updates.example.com') . '/status';
    }

    /**
     * Get default version info
     */
    private function getDefaultVersionInfo(string $version): array
    {
        return [
            'version' => $version,
            'title' => "Version {$version}",
            'description' => "Update to version {$version}",
            'changelog' => "Bug fixes and improvements",
            'released_at' => now()->toISOString(),
        ];
    }

    /**
     * Get default update instructions
     */
    private function getDefaultUpdateInstructions(string $version): array
    {
        return [
            'version' => $version,
            'steps' => [
                'Backup your current installation',
                'Download the update package',
                'Extract and replace files',
                'Run database migrations if needed',
                'Clear cache and test functionality'
            ],
            'requirements' => [
                'PHP 8.0 or higher',
                'MySQL 5.7 or higher',
                'At least 100MB free disk space'
            ]
        ];
    }

    /**
     * Get default version status
     */
    private function getDefaultVersionStatus(): array
    {
        return [
            'current_version' => $this->getCurrentVersion(),
            'latest_version' => $this->getCurrentVersion(),
            'is_update_available' => false,
        ];
    }

    /**
     * Get current version
     */
    private function getCurrentVersion(): string
    {
        return config('app.version', '1.0.0');
    }
}
