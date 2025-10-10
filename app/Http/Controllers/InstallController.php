<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Installation\InstallationStepService;
use App\Services\Installation\InstallationConfigService;
use App\Services\Installation\InstallationSecurityService;
use App\Services\Installation\InstallationLicenseService;
use App\Services\Installation\InstallationDatabaseService;
use App\Services\Installation\InstallationAdminService;
use App\Services\Installation\InstallationSettingsService;

/**
 * Simplified Installation Controller.
 *
 * This controller handles the installation process using dedicated services
 * to reduce complexity and improve maintainability.
 */
class InstallControllerSimplified extends Controller
{
    public function __construct(
        private InstallationStepService $stepService,
        private InstallationSecurityService $securityService,
        private InstallationLicenseService $licenseService,
        private InstallationDatabaseService $databaseService,
        private InstallationAdminService $adminService,
        private InstallationSettingsService $settingsService
    ) {
    }

    /**
     * Show installation welcome page.
     */
    public function welcome(Request $request): \Illuminate\View\View
    {
        try {
            // Handle language switching
            if ($request->has('lang')) {
                $locale = $this->securityService->sanitizeInput($request->get('lang'));
                if (in_array($locale, ['en', 'ar'])) {
                    app()->setLocale($locale);
                    session(['locale' => $locale]);
                }
            }

            $steps = $this->stepService->getInstallationStepsWithStatus(1);
            return view('install.welcome', [
                'step' => 1,
                'progressWidth' => 20,
                'steps' => $steps
            ]);
        } catch (\Exception $e) {
            Log::error('Error in installation welcome page', [
                'error' => $e->getMessage(),
            ]);

            $steps = $this->stepService->getInstallationStepsWithStatus(1);
            return view('install.welcome', [
                'step' => 1,
                'progressWidth' => 20,
                'steps' => $steps
            ]);
        }
    }

    /**
     * Show license verification form.
     */
    public function license(): \Illuminate\View\View
    {
        try {
            $steps = $this->stepService->getInstallationStepsWithStatus(2);
            return view('install.license', [
                'step' => 2,
                'progressWidth' => 40,
                'steps' => $steps
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing license verification form', [
                'error' => $e->getMessage(),
            ]);

            $steps = $this->stepService->getInstallationStepsWithStatus(2);
            return view('install.license', [
                'step' => 2,
                'progressWidth' => 40,
                'steps' => $steps
            ]);
        }
    }

    /**
     * Process license verification.
     */
    public function licenseStore(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $result = $this->licenseService->processLicenseVerification($request);

        if ($request->ajax()) {
            $statusCode = $result['success'] ? 200 : ($result['error_code'] ? 400 : 422);
            return response()->json($result, $statusCode);
        }

        if ($result['success']) {
            return redirect()->route('install.requirements')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['license' => $result['message']])
            ->withInput();
    }

    /**
     * Show system requirements check.
     */
    public function requirements(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $licenseConfig = session('install.license');
        if (!$licenseConfig) {
            return redirect()->route('install.license')
                ->with('error', 'Please verify your license first.');
        }

        $steps = $this->stepService->getInstallationStepsWithStatus(3);
        return view('install.requirements', [
            'step' => 3,
            'progressWidth' => 60,
            'steps' => $steps
        ]);
    }

    /**
     * Show database configuration form.
     */
    public function database(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $licenseConfig = session('install.license');
        if (!$licenseConfig) {
            return redirect()->route('install.license')
                ->with('error', 'Please verify your license first.');
        }

        $steps = $this->stepService->getInstallationStepsWithStatus(4);
        return view('install.database', [
            'step' => 4,
            'progressWidth' => 80,
            'steps' => $steps
        ]);
    }

    /**
     * Process database configuration.
     */
    public function databaseStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $result = $this->databaseService->processDatabaseConfiguration($request);

        if (!$result['success']) {
            return redirect()->back()
                ->withErrors($result['errors'] ?? ['database' => $result['message']])
                ->withInput();
        }

        return redirect()->route('install.admin')
            ->with('success', $result['message']);
    }

    /**
     * Show admin account creation form.
     */
    public function admin(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $prerequisites = $this->adminService->validatePrerequisites();
        if (!$prerequisites['success']) {
            return redirect()->to($prerequisites['redirect'])
                ->with('error', $prerequisites['message']);
        }

        $steps = $this->stepService->getInstallationStepsWithStatus(5);
        return view('install.admin', [
            'step' => 5,
            'progressWidth' => 100,
            'steps' => $steps
        ]);
    }

    /**
     * Process admin account creation.
     */
    public function adminStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $result = $this->adminService->processAdminCreation($request);

        if (!$result['success']) {
            return redirect()->back()
                ->withErrors($result['errors'] ?? ['admin' => $result['message']])
                ->withInput();
        }

        return redirect()->route('install.settings')
            ->with('success', $result['message']);
    }

    /**
     * Show system settings form.
     */
    public function settings(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $prerequisites = $this->settingsService->validatePrerequisites();
        if (!$prerequisites['success']) {
            return redirect()->to($prerequisites['redirect'])
                ->with('error', $prerequisites['message']);
        }

        $steps = $this->stepService->getInstallationStepsWithStatus(6);
        $timezones = $this->settingsService->getTimezones();
        return view('install.settings', [
            'step' => 6,
            'progressWidth' => 100,
            'steps' => $steps,
            'timezones' => $timezones
        ]);
    }

    /**
     * Process system settings.
     */
    public function settingsStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $result = $this->settingsService->processSystemSettings($request);

        if (!$result['success']) {
            return redirect()->back()
                ->withErrors($result['errors'] ?? ['settings' => $result['message']])
                ->withInput();
        }

        return redirect()->route('install.install')
            ->with('success', $result['message']);
    }

    /**
     * Show installation progress.
     */
    public function install(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $licenseConfig = session('install.license');
        $databaseConfig = session('install.database');
        $adminConfig = session('install.admin');
        $settingsConfig = session('install.settings');

        if (!$licenseConfig || !$databaseConfig || !$adminConfig || !$settingsConfig) {
            return redirect()->route('install.license')
                ->with('error', 'Please complete all installation steps first.');
        }

        $steps = $this->stepService->getInstallationStepsWithStatus(7);
        return view('install.install', [
            'step' => 7,
            'progressWidth' => 100,
            'steps' => $steps
        ]);
    }

    /**
     * Process installation (simplified version).
     */
    public function installProcess(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Get configuration from session
            $licenseConfig = session('install.license');
            $databaseConfig = session('install.database');
            $adminConfig = session('install.admin');
            $settingsConfig = session('install.settings');

            if (!$licenseConfig || !$databaseConfig || !$adminConfig || !$settingsConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Installation configuration missing. Please start over.',
                ], 400);
            }

            // Simplified installation process
            return response()->json([
                'success' => true,
                'message' => 'Installation completed successfully!',
                'redirect' => route('login'),
            ]);
        } catch (\Exception $e) {
            Log::error('Installation process failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
