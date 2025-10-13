<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\Installation\InstallationStepService;
use App\Services\Installation\LicenseVerificationService;
use App\Services\Installation\SystemRequirementsService;
use App\Services\Installation\InstallationService;
use App\Services\Installation\LicenseValidationService;
use App\Services\Installation\UserCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Helpers\SecureFileHelper;
use LicenseProtection\LicenseVerifier;

/**
 * Installation Controller with enhanced security and comprehensive setup.
 *
 * This controller handles the complete installation process for the application
 * including license verification, system requirements checking, database configuration,
 * admin user creation, and system settings with comprehensive security measures.
 *
 * Features:
 * - Multi-step installation wizard with progress tracking
 * - License verification with domain validation
 * - System requirements checking and validation
 * - Database configuration and connection testing
 * - Admin user creation with role assignment
 * - System settings configuration and storage
 * - Comprehensive error handling and logging
 * - Security validation for all installation steps
 *
 * @example
 * // Start installation process
 * GET /install
 * // Verify license
 * POST /install/license
 */
class InstallController extends Controller
{
    public function __construct(
        protected InstallationStepService $stepService,
        protected LicenseVerificationService $licenseService,
        protected SystemRequirementsService $requirementsService,
        protected InstallationService $installationService,
        protected LicenseValidationService $validationService,
        protected UserCreationService $userService
    ) {
    }
    /**
     * Show installation welcome page with language support.
     *
     * Displays the initial installation welcome page with language switching
     * functionality and proper validation for supported locales.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return \Illuminate\View\View The welcome page view
     *
     * @example
     * // Access welcome page
     * GET /install
     *
     * // Switch language
     * GET /install?lang=ar
     */
    public function welcome(Request $request): View
    {
        try {
            // Handle language switching with validation
            if ($request->has('lang')) {
                $locale = $this->sanitizeInput($request->get('lang'));
                if (in_array($locale, ['en', 'ar'])) {
                    app()->setLocale(is_string($locale) ? $locale : 'en');
                    session(['locale' => $locale]);
                }
            }
            $steps = $this->stepService->getInstallationStepsWithStatus(1);
            return view('install.welcome', ['step' => 1, 'progressWidth' => 20, 'steps' => $steps]);
        } catch (\Exception $e) {
            Log::error('Error in installation welcome page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return welcome page even if language switching fails
            $steps = $this->stepService->getInstallationStepsWithStatus(1);
            return view('install.welcome', ['step' => 1, 'progressWidth' => 20, 'steps' => $steps]);
        }
    }
    /**
     * Show license verification form with security validation.
     *
     * Displays the license verification form for the installation process
     * with proper security measures and validation.
     *
     * @return \Illuminate\View\View The license verification form view
     *
     * @example
     * // Access license verification form
     * GET /install/license
     */
    public function license()
    {
        try {
            $steps = $this->stepService->getInstallationStepsWithStatus(2);
            return view('install.license', ['step' => 2, 'progressWidth' => 40, 'steps' => $steps]);
        } catch (\Exception $e) {
            Log::error('Error showing license verification form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $steps = $this->stepService->getInstallationStepsWithStatus(2);
            return view('install.license', ['step' => 2, 'progressWidth' => 40, 'steps' => $steps]);
        }
    }
    /**
     * Process license verification with comprehensive security validation.
     *
     * @param Request $request
     *
     * @return RedirectResponse|JsonResponse
     */
    public function licenseStore(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $validationResult = $this->validateLicenseInput($request);
            if (!$validationResult['valid']) {
                return $this->handleValidationError($request, $validationResult['message']);
            }

            $result = $this->verifyLicense($request);
            return $result['valid'] 
                ? $this->handleSuccessfulVerification($request, $result)
                : $this->handleFailedVerification($request, $result);
        } catch (\Exception $e) {
            return $this->handleVerificationException($request, $e);
        }
    }

    /**
     * Handle successful verification.
     *
     * @param \Illuminate\Http\Request $request
     * @param array<string, mixed> $result
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function handleSuccessfulVerification(
        \Illuminate\Http\Request $request,
        array $result
    ): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'License verified successfully',
                'data' => $result,
            ]);
        }

        return redirect()->back()->with('success', 'License verified successfully');
    }

    /**
     * Handle failed verification.
     *
     * @param \Illuminate\Http\Request $request
     * @param array<string, mixed> $result
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function handleFailedVerification(
        \Illuminate\Http\Request $request,
        array $result
    ): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'License verification failed',
                'errors' => $result['errors'] ?? [],
            ], 422);
        }

        return redirect()->back()->withErrors(is_array($result['errors'] ?? null) ? $result['errors'] : []);
    }

    /**
     * Handle verification exception.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $e
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function handleVerificationException(
        \Illuminate\Http\Request $request,
        \Exception $e
    ): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during verification',
                'error' => $e->getMessage(),
            ], 500);
        }

        return redirect()->back()->withErrors(['general' => 'An error occurred during verification']);
    }


    /**
     * Check all requirements passed.
     *
     * @return bool
     */
    private function checkAllRequirementsPassed(): bool
    {
        $requirements = $this->requirementsService->checkRequirements();
        return (bool) ($requirements['passed'] ?? false);
    }

    /**
     * Validate database input.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function validateDatabaseInput(array $data): array
    {
        $validator = Validator::make($data, [
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'database' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
            ];
        }

        return ['valid' => true];
    }

    /**
     * Check if database is configured.
     *
     * @return bool
     */
    private function isDatabaseConfigured(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Show system requirements check.
     */
    public function requirements(): View|RedirectResponse
    {
        // Check if license is verified
        if (!session('install.license')) {
            return redirect()->route('install.license')
                ->with('error', 'Please verify your license first.');
        }

        $requirements = $this->requirementsService->checkRequirements();
        $allPassed = $this->checkAllRequirementsPassed();
        $steps = $this->stepService->getInstallationStepsWithStatus(3);

        return view('install.requirements', [
            'requirements' => $requirements,
            'allPassed' => $allPassed,
            'steps' => $steps,
            'step' => 3,
            'progressWidth' => 60
        ]);
    }


    /**
     * Show database configuration form.
     */
    public function database(): View|RedirectResponse
    {
        if (!session('install.license')) {
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
    public function databaseStore(Request $request): RedirectResponse
    {
        // Validate database configuration
        /**
 * @var array<string, mixed> $requestData
*/
        $requestData = $request->all();
        $validationResult = $this->validateDatabaseInput($requestData);
        if (!$validationResult['valid']) {
            $errors = $validationResult['errors'] ?? [];
            return redirect()->back()
                ->withErrors(is_array($errors) ? $errors : [])
                ->withInput();
        }

        // Test database connection
        $connectionResult = $this->testDatabaseConnection($request->all());
        if (!$connectionResult['success']) {
            return redirect()->back()
                ->withErrors(['database' => $connectionResult['message']])
                ->withInput();
        }

        // Store database configuration
        session(['install.database' => $request->all()]);
        return redirect()->route('install.admin');
    }

    /**
     * Show admin account creation form.
     */
    public function admin(): View|RedirectResponse
    {
        if (!session('install.license')) {
            return redirect()->route('install.license')
                ->with('error', 'Please verify your license first.');
        }

        if (!$this->isDatabaseConfigured()) {
            return redirect()->route('install.database')
                ->with('error', 'Please configure database settings first.');
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
    public function adminStore(Request $request): RedirectResponse
    {
        $validationResult = $this->validationService->validateAdminInput($request);
        if (!$validationResult['valid']) {
            $errors = $validationResult['errors'] ?? [];
            return redirect()->back()
                ->withErrors(is_array($errors) ? $errors : [])
                ->withInput();
        }

        // Store admin configuration
        session(['install.admin' => $request->all()]);
        return redirect()->route('install.settings');
    }

    /**
     * Show system settings form.
     */
    public function settings(): View|RedirectResponse
    {
        if (!session('install.license')) {
            return redirect()->route('install.license')
                ->with('error', 'Please verify your license first.');
        }

        if (!session('install.database')) {
            return redirect()->route('install.database')
                ->with('error', 'Please configure database settings first.');
        }

        if (!session('install.admin')) {
            return redirect()->route('install.admin')
                ->with('error', 'Please create admin account first.');
        }

        $steps = $this->stepService->getInstallationStepsWithStatus(6);
        $timezones = $this->stepService->getTimezones();
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
    public function settingsStore(Request $request): RedirectResponse
    {
        $validator = $this->createSettingsValidator($request);
        if ($validator->fails()) {
            return $this->handleValidationFailure($validator);
        }

        $this->storeSettings($request);
        return redirect()->route('install.install');
    }

    private function createSettingsValidator(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), $this->getSettingsValidationRules());
    }

    /**
     * @return array<string, mixed>
     */
    private function getSettingsValidationRules(): array
    {
        return [
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'admin_email' => 'required_if:enable_email, 1|nullable|string|email|max:255',
            'timezone' => 'required|string',
            'locale' => 'required|string|in:en, ar',
            'enable_email' => 'nullable|boolean',
            'mail_mailer' => 'required_if:enable_email, 1|nullable|string|in:smtp, mailgun, ses, postmark',
            'mail_host' => 'required_if:enable_email, 1|nullable|string|max:255',
            'mail_port' => 'required_if:enable_email, 1|nullable|integer|min:1|max:65535',
            'mail_encryption' => 'nullable|string|in:tls, ssl',
            'mail_username' => 'required_if:enable_email, 1|nullable|string|max:255',
            'mail_password' => 'required_if:enable_email, 1|nullable|string|max:255',
            'mail_from_address' => 'required_if:enable_email, 1|nullable|email|max:255',
            'mail_from_name' => 'required_if:enable_email, 1|nullable|string|max:255',
        ];
    }

    private function handleValidationFailure(\Illuminate\Contracts\Validation\Validator $validator): RedirectResponse
    {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    private function storeSettings(Request $request): void
    {
        session(['install.settings' => $request->all()]);
    }
    /**
     * Show installation progress.
     */
    public function install(): View|RedirectResponse
    {
        // Check if all required configuration is available
        $licenseConfig = session('install.license');
        $databaseConfig = session('install.database');
        $adminConfig = session('install.admin');
        $settingsConfig = session('install.settings');
        if (! $licenseConfig) {
            return redirect()->route('install.license')
                ->with('error', 'Please verify your license first.');
        }
        if (! $databaseConfig) {
            return redirect()->route('install.database')
                ->with('error', 'Please configure database settings first.');
        }
        if (! $adminConfig) {
            return redirect()->route('install.admin')
                ->with('error', 'Please create admin account first.');
        }
        if (! $settingsConfig) {
            return redirect()->route('install.settings')
                ->with('error', 'Please configure system settings first.');
        }
        $steps = $this->stepService->getInstallationStepsWithStatus(7);
        return view('install.install', [
            'step' => 7,
            'progressWidth' => 100,
            'steps' => $steps
        ]);
    }
    /**
     * Process installation.
     */
    public function installProcess(Request $request): JsonResponse
    {
        try {
            $configs = $this->getInstallationConfigs();
            if (!$configs) {
                return $this->errorResponse('Installation configuration missing. Please start over.', 400);
            }

            $this->runInstallationSteps($configs);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Installation failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getInstallationConfigs(): ?array
    {
        $configs = [
            'license' => session('install.license'),
            'database' => session('install.database'),
            'admin' => session('install.admin'),
            'settings' => session('install.settings'),
        ];

        return array_filter($configs) === $configs ? $configs : null;
    }

    /**
     * @param array<string, mixed> $configs
     */
    private function runInstallationSteps(array $configs): void
    {
        $this->setupDatabase($configs);
        $this->runMigrations();
        $this->createUserAndSettings($configs);
        $this->finalizeInstallation($configs);
    }

    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => 'Installation completed successfully!',
            'redirect' => route('login'),
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    protected function errorResponse(
        string $message = 'Error',
        mixed $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
    /**
     * Show installation completion page.
     */
    public function completion(): View|RedirectResponse
    {
        // Check if system is installed
        $installedFile = storage_path('.installed');
        if (! File::exists($installedFile)) {
            return redirect()->route('install.welcome');
        }
        // Check if we have session data
        $licenseConfig = session('install.license');
        $adminConfig = session('install.admin');
        $settingsConfig = session('install.settings');
        $databaseConfig = session('install.database');
        if (! $licenseConfig || ! $adminConfig || ! $settingsConfig || ! $databaseConfig) {
            return redirect()->route('install.welcome');
        }
        // Pass session data to view before clearing
        $steps = $this->stepService->getInstallationStepsWithStatus(7);
        $viewData = [
            'step' => 7,
            'progressWidth' => 100,
            'steps' => $steps,
            'licenseConfig' => $licenseConfig,
            'adminConfig' => $adminConfig,
            'settingsConfig' => $settingsConfig,
            'databaseConfig' => $databaseConfig,
        ];
        // Clear session data after passing to view
        session()->forget(['install.license', 'install.database', 'install.admin', 'install.settings']);
        return view('install.completion', $viewData);
    }
    /**
     * Test database connection.
     */
    /**
     * @param mixed $config
     *
     * @return array<string, mixed>
     */
    private function testDatabaseConnection($config)
    {
        try {
            $dbHost = is_array($config) ? ($config['db_host'] ?? null) : null;
            $dbPort = is_array($config) ? ($config['db_port'] ?? null) : null;
            $dbName = is_array($config) ? ($config['db_name'] ?? null) : null;
            $dbUsername = is_array($config) ? ($config['db_username'] ?? null) : null;
            $dbPassword = is_array($config) ? ($config['db_password'] ?? null) : null;

            $connection = new \PDO(
                "mysql:host=" . (is_string($dbHost) ? $dbHost : '') .
                ";port=" . (is_string($dbPort) ? $dbPort : '') .
                ";dbname=" . (is_string($dbName) ? $dbName : ''),
                is_string($dbUsername) ? $dbUsername : null,
                is_string($dbPassword) ? $dbPassword : null,
            );
            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return ['success' => true, 'message' => 'Database connection successful'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    /**
     * Update .env file.
     *
     * @param array<string, mixed> $databaseConfig
     * @param array<string, mixed> $settingsConfig
     */
    /**
     * @param array<string, mixed> $databaseConfig
     * @param array<string, mixed> $settingsConfig
     */
    private function updateEnvFile(array $databaseConfig, array $settingsConfig): void
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);
        
        $envContent = $this->updateDatabaseConfig($envContent, $databaseConfig);
        $envContent = $this->updateApplicationConfig($envContent, $settingsConfig);
        
        File::put($envPath, $envContent);
    }

    /**
     * Update database configuration in .env file
     */
    private function updateDatabaseConfig(string $envContent, array $databaseConfig): string
    {
        $replacements = [
            'DB_HOST' => $databaseConfig['db_host'] ?? '',
            'DB_PORT' => $databaseConfig['db_port'] ?? '',
            'DB_DATABASE' => $databaseConfig['db_name'] ?? '',
            'DB_USERNAME' => $databaseConfig['db_username'] ?? '',
            'DB_PASSWORD' => $databaseConfig['db_password'] ?? '',
        ];

        foreach ($replacements as $key => $value) {
            $envContent = preg_replace("/{$key}=.*/", "{$key}=" . (is_string($value) ? $value : ''), $envContent) ?? $envContent;
        }

        return $envContent;
    }

    /**
     * Update application configuration in .env file
     */
    private function updateApplicationConfig(string $envContent, array $settingsConfig): string
    {
        $envContent = $this->updateAppSettings($envContent, $settingsConfig);
        $envContent = $this->updateLocaleSettings($envContent, $settingsConfig);
        $envContent = $this->updateEmailSettings($envContent, $settingsConfig);
        
        return $envContent;
    }

    /**
     * Update app settings
     */
    private function updateAppSettings(string $envContent, array $settingsConfig): string
    {
        $currentUrl = request()->getSchemeAndHttpHost();
        $siteName = $settingsConfig['site_name'] ?? '';
        $timezone = $settingsConfig['timezone'] ?? '';

        $envContent = preg_replace('/APP_NAME=.*/', 'APP_NAME="' . (is_string($siteName) ? $siteName : '') . '"', $envContent) ?? $envContent;
        $envContent = preg_replace('/APP_URL=.*/', "APP_URL={$currentUrl}", $envContent) ?? $envContent;
        $envContent = preg_replace('/APP_TIMEZONE=.*/', 'APP_TIMEZONE=' . (is_string($timezone) ? $timezone : ''), $envContent) ?? $envContent;

        return $envContent;
    }

    /**
     * Update locale settings
     */
    private function updateLocaleSettings(string $envContent, array $settingsConfig): string
    {
        $locale = $settingsConfig['locale'] ?? '';
        $localeStr = is_string($locale) ? $locale : '';
        $fakerLocale = $localeStr === 'ar' ? 'ar_SA' : 'en_US';

        $envContent = preg_replace('/APP_LOCALE=.*/', "APP_LOCALE={$localeStr}", $envContent) ?? $envContent;
        $envContent = preg_replace('/APP_FALLBACK_LOCALE=.*/', "APP_FALLBACK_LOCALE={$localeStr}", $envContent) ?? $envContent;
        $envContent = preg_replace('/APP_FAKER_LOCALE=.*/', "APP_FAKER_LOCALE={$fakerLocale}", $envContent) ?? $envContent;

        return $envContent;
    }

    /**
     * Update email settings
     */
    private function updateEmailSettings(string $envContent, array $settingsConfig): string
    {
        if (!isset($settingsConfig['enable_email']) || !$settingsConfig['enable_email']) {
            return $envContent;
        }

        $emailConfig = [
            'MAIL_MAILER' => $settingsConfig['mail_mailer'] ?? '',
            'MAIL_HOST' => $settingsConfig['mail_host'] ?? '',
            'MAIL_PORT' => $settingsConfig['mail_port'] ?? '',
            'MAIL_USERNAME' => $settingsConfig['mail_username'] ?? '',
            'MAIL_PASSWORD' => $settingsConfig['mail_password'] ?? '',
            'MAIL_ENCRYPTION' => $settingsConfig['mail_encryption'] ?? '',
            'MAIL_FROM_ADDRESS' => $settingsConfig['mail_from_address'] ?? '',
            'MAIL_FROM_NAME' => $settingsConfig['mail_from_name'] ?? '',
        ];

        foreach ($emailConfig as $key => $value) {
            $envContent = preg_replace("/{$key}=.*/", "{$key}=" . (is_string($value) ? $value : ''), $envContent) ?? $envContent;
        }

        return $envContent;
    }
    /**
     * Create roles and permissions.
     */
    private function createRolesAndPermissions(): void
    {
        try {
            // Create permissions
            $permissions = [
                'manage_users', 'manage_products', 'manage_licenses', 'manage_tickets',
                'manage_settings', 'manage_knowledge_base', 'view_reports', 'manage_invoices',
            ];
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }
            // Create admin role
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $adminRole->givePermissionTo($permissions);
            // Create user role
            $userRole = Role::firstOrCreate(['name' => 'user']);
            $userRole->givePermissionTo(['view_reports']);
            // Roles and permissions created successfully
        } catch (\Exception $e) {
            // Failed to create roles and permissions
            throw $e;
        }
    }
    /**
     * Create default settings.
     *
     * @param array<string, mixed> $settingsConfig
     */
    private function createDefaultSettings(array $settingsConfig): void
    {
        try {
            Setting::create([
                'site_name' => $settingsConfig['site_name'],
                'site_description' => $settingsConfig['site_description'],
                'admin_email' => $settingsConfig['admin_email'] ?? null,
                'timezone' => $settingsConfig['timezone'],
                'locale' => $settingsConfig['locale'],
                'preloader_enabled' => true,
                'preloader_type' => 'spinner',
                'preloader_color' => '#3b82f6',
                'preloader_background_color' => '#ffffff',
                'preloader_duration' => 2000,
                'logo_show_text' => true,
                'logo_text' => $settingsConfig['site_name'],
                'maintenance_mode' => false,
                'registration_enabled' => true,
                'email_verification_required' => isset($settingsConfig['enable_email'])
                    && $settingsConfig['enable_email'],
            ]);
            // Default settings created successfully
        } catch (\Exception $e) {
            // Failed to create default settings
            throw $e;
        }
    }
    /**
     * Test database connection.
     */
    public function testDatabase(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'db_host' => 'required|string',
            'db_port' => 'required|integer|min:1|max:65535',
            'db_name' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        $connection = $this->testDatabaseConnection($request->all());
        return response()->json($connection);
    }

    /**
     * Validate license input
     */
    private function validateLicenseInput(Request $request): array
    {
        return $this->validationService->validateLicenseInput($request);
    }

    /**
     * Handle validation error
     */
    private function handleValidationError(Request $request, ?string $message): RedirectResponse|JsonResponse
    {
        return $this->validationService->handleValidationError(
            $request,
            is_string($message) ? $message : 'Validation failed'
        );
    }

    /**
     * Verify license
     */
    private function verifyLicense(Request $request): array
    {
        $purchaseCode = $this->validationService->sanitizeInput($request->purchase_code);
        $domain = $this->validationService->sanitizeInput($request->getHost());
        
        return $this->licenseService->verifyLicense(
            is_string($purchaseCode) ? $purchaseCode : '',
            is_string($domain) ? $domain : ''
        );
    }

    /**
     * Setup database configuration
     */
    private function setupDatabase(array $configs): void
    {
        $databaseConfig = is_array($configs['database']) ? $configs['database'] : [];
        $settingsConfig = is_array($configs['settings']) ? $configs['settings'] : [];
        $this->updateEnvFile($databaseConfig, $settingsConfig);
    }

    /**
     * Run database migrations
     */
    private function runMigrations(): void
    {
        Artisan::call('migrate:fresh', ['--force' => true]);
        $this->createRolesAndPermissions();
        $this->installationService->runSeeders();
    }

    /**
     * Create user and settings
     */
    private function createUserAndSettings(array $configs): void
    {
        $adminData = is_array($configs['admin']) ? $configs['admin'] : [];
        $settingsConfig = is_array($configs['settings']) ? $configs['settings'] : [];
        
        $this->userService->createAdminUser($adminData);
        $this->createDefaultSettings($settingsConfig);
    }

    /**
     * Finalize installation
     */
    private function finalizeInstallation(array $configs): void
    {
        $licenseConfig = is_array($configs['license']) ? $configs['license'] : [];
        
        $this->installationService->storeLicenseInformation($licenseConfig);
        $this->installationService->updateSessionDrivers();
        $this->installationService->createStorageLink();
        $this->installationService->createInstalledFile();
    }
}
