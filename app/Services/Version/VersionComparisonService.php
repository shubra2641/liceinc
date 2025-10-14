<?php

declare(strict_types=1);

namespace App\Services\Version;

/**
 * Version Comparison Service
 * 
 * Handles version comparison logic
 */
class VersionComparisonService
{
    /**
     * Compare two versions
     */
    public function compare(string $version1, string $version2): int
    {
        return version_compare($version1, $version2);
    }

    /**
     * Check if version is newer
     */
    public function isNewer(string $version1, string $version2): bool
    {
        return $this->compare($version1, $version2) > 0;
    }

    /**
     * Check if version is older
     */
    public function isOlder(string $version1, string $version2): bool
    {
        return $this->compare($version1, $version2) < 0;
    }

    /**
     * Check if versions are equal
     */
    public function isEqual(string $version1, string $version2): bool
    {
        return $this->compare($version1, $version2) === 0;
    }

    /**
     * Get version difference
     */
    public function getDifference(string $version1, string $version2): string
    {
        $comparison = $this->compare($version1, $version2);
        
        return match ($comparison) {
            1 => 'newer',
            -1 => 'older',
            0 => 'equal',
            default => 'unknown'
        };
    }
}
