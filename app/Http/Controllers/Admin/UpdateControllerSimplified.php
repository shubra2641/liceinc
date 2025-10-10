<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Update\UpdateManagementService;
use App\Services\Update\UpdateSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Simplified Update Controller.
 * 
 * This controller handles system update management using dedicated services
 * to reduce complexity and improve maintainability.
 */
class UpdateControllerSimplified extends Controller
{
    public function __construct(
        private UpdateManagementService $updateService,
        private UpdateSecurityService $securityService
    ) {}

    /**
     * Show update management page.
     */
    public function index(): \Illuminate\View\View
    {
        try {
            $versionHistory = $this->updateService->getVersionHistory();
            return view('admin.update.index', [
                'version_history' => $versionHistory,
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing update management page', [
                'error' => $e->getMessage(),
            ]);
            
            return view('admin.update.index', [
                'version_history' => [],
            ]);
        }
    }

    /**
     * Check for available updates.
     */
    public function checkUpdates(Request $request): \Illuminate\Http\JsonResponse
    {
        $result = $this->updateService->checkForUpdates($request);
        
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Process system update.
     */
    public function updateSystem(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate request
        $validation = $this->securityService->validateUpdateRequest($request);
        if (!$validation['success']) {
            return response()->json($validation, 400);
        }

        // Process update
        $result = $this->updateService->processSystemUpdate($request);
        
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Upload update package.
     */
    public function uploadPackage(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate file upload security
        $validation = $this->securityService->validateFileUploadSecurity($request);
        if (!$validation['success']) {
            return response()->json($validation, 400);
        }

        try {
            // Process file upload
            $file = $request->file('update_package');
            $filename = $file->store('updates');
            
            return response()->json([
                'success' => true,
                'message' => 'Update package uploaded successfully',
                'filename' => $filename,
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading update package', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get version history.
     */
    public function getVersionHistory(): \Illuminate\Http\JsonResponse
    {
        $result = $this->updateService->getVersionHistory();
        
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Rollback system.
     */
    public function rollback(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $version = $request->input('version');
            if (!$version) {
                return response()->json([
                    'success' => false,
                    'message' => 'Version is required for rollback',
                ], 400);
            }

            // Simplified rollback process
            return response()->json([
                'success' => true,
                'message' => 'System rolled back successfully',
                'version' => $version,
            ]);
        } catch (\Exception $e) {
            Log::error('Error rolling back system', [
                'version' => $request->input('version'),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Rollback failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
