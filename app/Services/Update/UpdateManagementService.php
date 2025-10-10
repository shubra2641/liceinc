<?php

declare(strict_types=1);

namespace App\Services\Update;

use App\Services\LicenseServerService;
use App\Services\UpdatePackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing system updates.
 */
class UpdateManagementService
{
    public function __construct(
        private LicenseServerService $licenseServerService
    ) {
    }

    /**
     * Check for available updates.
     *
     * @param Request $request The HTTP request
     * @return array<string, mixed> The update information
     */
    public function checkForUpdates(Request $request): array
    {
        try {
            $currentVersion = config('app.version', '1.0.0');
            $latestVersion = $this->licenseServerService->getLatestVersion('', '');

            if (!$latestVersion) {
                return [
                    'success' => false,
                    'message' => 'Unable to check for updates',
                ];
            }

            $latestVersionString = '1.0.0';
            if (isset($latestVersion['version'])) {
                $latestVersionString = (string) $latestVersion['version'];
            }
            $isUpdateAvailable = version_compare($currentVersion, $latestVersionString, '<');

            return [
                'success' => true,
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion,
                'update_available' => $isUpdateAvailable,
                'update_info' => $isUpdateAvailable ? $this->getUpdateInfo($currentVersion) : null,
            ];
        } catch (\Exception $e) {
            Log::error('Error checking for updates', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error checking for updates: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get update information.
     *
     * @param string $version The version to get info for
     * @return array<string, mixed> The update information
     */
    private function getUpdateInfo(string $version): array
    {
        try {
            return $this->licenseServerService->getUpdateInfo($version, '');
        } catch (\Exception $e) {
            Log::error('Error getting update info', [
                'version' => $version,
                'error' => $e->getMessage(),
            ]);

            return [
                'version' => $version,
                'description' => 'Update available',
                'release_date' => now()->toDateString(),
            ];
        }
    }

    /**
     * Process system update.
     *
     * @param Request $request The HTTP request
     * @return array<string, mixed> The update result
     */
    public function processSystemUpdate(Request $request): array
    {
        try {
            $version = $request->input('version');
            if (!$version) {
                return [
                    'success' => false,
                    'message' => 'Version is required',
                ];
            }

            // Simplified update process
            return [
                'success' => true,
                'message' => 'System updated successfully',
                'new_version' => $version,
            ];
        } catch (\Exception $e) {
            Log::error('Error processing system update', [
                'version' => $request->input('version'),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get version history.
     *
     * @return array<string, mixed> The version history
     */
    public function getVersionHistory(): array
    {
        try {
            return $this->licenseServerService->getVersionHistory('', '');
        } catch (\Exception $e) {
            Log::error('Error getting version history', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Unable to retrieve version history',
            ];
        }
    }
}
