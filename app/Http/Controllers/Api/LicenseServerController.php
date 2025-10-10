<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApiUpdateRequest;
use App\Http\Requests\Api\CheckUpdatesRequest;
use App\Http\Requests\Api\GetVersionHistoryRequest;
use App\Http\Requests\Api\GetLatestVersionRequest;
use App\Http\Requests\Api\GetUpdateInfoRequest;
use App\Http\Controllers\Api\Services\LicenseServerService;
use App\Http\Controllers\Api\Services\UpdateService;
use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Simplified License Server Controller.
 */
class LicenseServerController extends Controller
{
    public function __construct(
        private LicenseServerService $licenseService,
        private UpdateService $updateService
    ) {}

    /**
     * Check for updates.
     */
    public function checkUpdates(CheckUpdatesRequest $request): JsonResponse
    {
        $rateLimitKey = 'check-updates:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            return response()->json(['error' => 'Too many requests'], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        try {
            $product = $this->licenseService->verifyLicenseAndGetProduct(
                $request->license_key,
                $request->domain
            );

            if (!$product) {
                return response()->json(['error' => 'Invalid license or domain'], 403);
            }

            $updateInfo = $this->updateService->getUpdateInfo($product, $request->current_version);

            return response()->json($updateInfo);
        } catch (\Exception $e) {
            Log::error('Check updates failed: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get latest version.
     */
    public function getLatestVersion(GetLatestVersionRequest $request): JsonResponse
    {
        $rateLimitKey = 'latest-version:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            return response()->json(['error' => 'Too many requests'], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        try {
            $product = $this->licenseService->verifyLicenseAndGetProduct(
                $request->license_key,
                $request->domain
            );

            if (!$product) {
                return response()->json(['error' => 'Invalid license or domain'], 403);
            }

            $latestVersion = $this->licenseService->getLatestVersion($product);

            if (!$latestVersion) {
                return response()->json(['error' => 'No versions available'], 404);
            }

            return response()->json([
                'version' => $latestVersion->version,
                'changelog' => $latestVersion->changelog,
                    'download_url' => route('api.license.download-update', [
                    'id' => $latestVersion->id,
                    'token' => $latestVersion->download_token
                ])
            ]);
        } catch (\Exception $e) {
            Log::error('Get latest version failed: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get version history.
     */
    public function getVersionHistory(GetVersionHistoryRequest $request): JsonResponse
    {
        $rateLimitKey = 'version-history:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            return response()->json(['error' => 'Too many requests'], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        try {
            $product = $this->licenseService->verifyLicenseAndGetProduct(
                $request->license_key,
                $request->domain
            );

            if (!$product) {
                return response()->json(['error' => 'Invalid license or domain'], 403);
            }

            $history = $this->licenseService->getVersionHistory($product, $request->limit);

            return response()->json(['versions' => $history]);
        } catch (\Exception $e) {
            Log::error('Get version history failed: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Download update file.
     */
    public function downloadUpdate(Request $request, int $id): Response
    {
        $rateLimitKey = 'download-update:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            abort(429, 'Too many requests');
        }

        RateLimiter::hit($rateLimitKey, 300);

        try {
            $update = ProductUpdate::findOrFail($id);
            
            if ($update->download_token !== $request->token) {
                abort(403, 'Invalid download token');
            }

            $filePath = $this->updateService->downloadUpdate($update);
            
            if (!$filePath) {
                abort(404, 'Update file not found');
            }

            return response()->download($filePath, $update->filename);
        } catch (\Exception $e) {
            Log::error('Download update failed: ' . $e->getMessage());
            abort(500, 'Internal server error');
        }
    }
}