<?php

declare(strict_types=1);

namespace App\Services\Version;

/**
 * Version Validation Service
 * 
 * Handles version validation logic
 */
class VersionValidationService
{
    /**
     * Validate version format
     */
    public function isValidVersion(string $version): bool
    {
        if (empty($version)) {
            return false;
        }

        // Check if version matches semantic versioning pattern
        return preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;
    }

    /**
     * Validate version range
     */
    public function isValidVersionRange(string $minVersion, string $maxVersion): bool
    {
        if (!$this->isValidVersion($minVersion) || !$this->isValidVersion($maxVersion)) {
            return false;
        }

        return version_compare($minVersion, $maxVersion) <= 0;
    }

    /**
     * Validate update compatibility
     */
    public function isCompatibleUpdate(string $currentVersion, string $targetVersion): bool
    {
        if (!$this->isValidVersion($currentVersion) || !$this->isValidVersion($targetVersion)) {
            return false;
        }

        // Target version must be newer than current
        return version_compare($targetVersion, $currentVersion) > 0;
    }

    /**
     * Validate major version update
     */
    public function isMajorUpdate(string $currentVersion, string $targetVersion): bool
    {
        if (!$this->isCompatibleUpdate($currentVersion, $targetVersion)) {
            return false;
        }

        $currentMajor = $this->getMajorVersion($currentVersion);
        $targetMajor = $this->getMajorVersion($targetVersion);

        return $targetMajor > $currentMajor;
    }

    /**
     * Get major version number
     */
    private function getMajorVersion(string $version): int
    {
        $parts = explode('.', $version);
        return (int) ($parts[0] ?? 0);
    }

    /**
     * Get minor version number
     */
    private function getMinorVersion(string $version): int
    {
        $parts = explode('.', $version);
        return (int) ($parts[1] ?? 0);
    }

    /**
     * Get patch version number
     */
    private function getPatchVersion(string $version): int
    {
        $parts = explode('.', $version);
        return (int) ($parts[2] ?? 0);
    }

    /**
     * Check if version is stable
     */
    public function isStableVersion(string $version): bool
    {
        if (!$this->isValidVersion($version)) {
            return false;
        }

        // For now, all semantic versions are considered stable
        // This could be extended to check against a list of stable versions
        return true;
    }
}
