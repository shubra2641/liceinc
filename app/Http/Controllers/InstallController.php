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
            // Validate input
            $validationResult = $this->validationService->validateLicenseInput($request);
            if (!$validationResult['valid']) {
                $message = $validationResult['message'] ?? 'Validation failed';
                return $this->validationService->handleValidationError(
                    $request, 
                    is_string($message) ? $message : 'Validation failed'
                );
            }

            // Verify license
            $purchaseCode = $this->validationService->sanitizeInput($request->purchase_code);
            $domain = $this->validationService->sanitizeInput($request->getHost());
            $result = $this->licenseService->verifyLicense(
                is_string($purchaseCode) ? $purchaseCode : '',
                is_string($domain) ? $domain : ''
            );

            if ($result['valid']) {
                return $this->handleSuccessfulVerification($request, $result);
            } else {
                return $this->handleFailedVerification($request, $result);
            }
        } catch (\Exception $e) {
            return $this->handleVerificationException($request, $e);
        }
    }

    /**
     * Validate license input.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    private function validateLicenseInput(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'purchase_code' => 'required|string|min:5|max:100',
        ], [
            'purchase_code.required' => 'Purchase code is required',
            'purchase_code.min' => 'Purchase code must be at least 5 characters long.',
            'purchase_code.max' => 'Purchase code must not exceed 100 characters.',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'message' => $validator->errors()->first('purchase_code'),
            ];
        }

        return ['valid' => true];
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
        $allPassed = $this->checkAllRequirementsPassed($requirements);
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
        $validationResult = $this->validateDatabaseInput($request);
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
        $validator = Validator::make($request->all(), [
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
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        // Store system settings
        session(['install.settings' => $request->all()]);
        return redirect()->route('install.install');
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
                return $this->createErrorResponse('Installation configuration missing. Please start over.', 400);
            }

            $this->executeInstallationSteps($configs);
            
            return $this->createSuccessResponse();
        } catch (\Exception $e) {
            return $this->createErrorResponse('Installation failed: ' . $e->getMessage(), 500);
        }
    }

    private function getInstallationConfigs(): ?array
    {
        $configs = [
            'license' => session('install.license'),
            'database' => session('install.database'),
            'admin' => session('install.admin'),
            'settings' => session('install.settings'),
        ];

        foreach ($configs as $config) {
            if (!$config) {
                return null;
            }
        }

        return $configs;
    }

    private function executeInstallationSteps(array $configs): void
    {
        $this->updateEnvironmentFiles($configs);
        $this->runDatabaseMigrations();
        $this->setupRolesAndPermissions();
        $this->runDatabaseSeeders();
        $this->createAdminUser($configs['admin']);
        $this->setupDefaultSettings($configs['settings']);
        $this->storeLicenseInformation($configs['license']);
        $this->updateSystemDrivers();
        $this->createStorageLink();
        $this->createInstalledFile();
    }

    private function updateEnvironmentFiles(array $configs): void
    {
        $databaseConfig = is_array($configs['database']) ? $configs['database'] : [];
        $settingsConfig = is_array($configs['settings']) ? $configs['settings'] : [];
        $this->updateEnvFile($databaseConfig, $settingsConfig);
    }

    private function runDatabaseMigrations(): void
    {
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    private function setupRolesAndPermissions(): void
    {
        $this->createRolesAndPermissions();
    }

    private function runDatabaseSeeders(): void
    {
        $this->installationService->runSeeders();
    }

    private function createAdminUser(array $adminConfig): void
    {
        $this->userService->createAdminUser($adminConfig);
    }

    private function setupDefaultSettings(array $settingsConfig): void
    {
        $this->createDefaultSettings($settingsConfig);
    }

    private function storeLicenseInformation(array $licenseConfig): void
    {
        $this->installationService->storeLicenseInformation($licenseConfig);
    }

    private function updateSystemDrivers(): void
    {
        $this->installationService->updateSessionDrivers();
    }

    private function createStorageLink(): void
    {
        $this->installationService->createStorageLink();
    }

    private function createInstalledFile(): void
    {
        $this->installationService->createInstalledFile();
    }

    private function createSuccessResponse(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Installation completed successfully!',
            'redirect' => route('login'),
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    private function createErrorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
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
    private function updateEnvFile(array $databaseConfig, array $settingsConfig): void
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);
        // Update database configuration
        $envContent = preg_replace(
            '/DB_HOST=.*/',
            "DB_HOST=" . (is_string($databaseConfig['db_host'] ?? null) ? $databaseConfig['db_host'] : ''),
            $envContent
        ) ?? $envContent;
        $envContent = preg_replace(
            '/DB_PORT=.*/',
            "DB_PORT=" . (is_string($databaseConfig['db_port'] ?? null) ? $databaseConfig['db_port'] : ''),
            $envContent
        ) ?? $envContent;
        $envContent = preg_replace(
            '/DB_DATABASE=.*/',
            "DB_DATABASE=" . (is_string($databaseConfig['db_name'] ?? null) ? $databaseConfig['db_name'] : ''),
            $envContent
        ) ?? $envContent;
        $envContent = preg_replace(
            '/DB_USERNAME=.*/',
            'DB_USERNAME=' . (is_string($databaseConfig['db_username'] ?? null) ? $databaseConfig['db_username'] : ''),
            $envContent
        ) ?? $envContent;
        $envContent = preg_replace(
            '/DB_PASSWORD=.*/',
            'DB_PASSWORD=' . (is_string($databaseConfig['db_password'] ?? null) ? $databaseConfig['db_password'] : ''),
            $envContent
        ) ?? $envContent;
        // Update application configuration
        $envContent = preg_replace(
            '/APP_NAME=.*/',
            'APP_NAME="' . (is_string($settingsConfig['site_name'] ?? null) ? $settingsConfig['site_name'] : '') . '"',
            $envContent
        ) ?? $envContent;
        // Update APP_URL to current domain (this ensures emails use the correct domain)
        $currentUrl = request()->getSchemeAndHttpHost();
        $envContent = preg_replace('/APP_URL=.*/', "APP_URL={$currentUrl}", $envContent) ?? $envContent;
        $envContent = preg_replace(
            '/APP_TIMEZONE=.*/',
            'APP_TIMEZONE=' . (is_string($settingsConfig['timezone'] ?? null) ? $settingsConfig['timezone'] : ''),
            $envContent
        ) ?? $envContent;
        // Add APP_TIMEZONE if it doesn't exist
        if ($envContent && ! str_contains($envContent, 'APP_TIMEZONE=')) {
            $envContent .= "\nAPP_TIMEZONE=" . (is_string($settingsConfig['timezone'] ?? null)
                ? $settingsConfig['timezone']
                : '');
        }
        $envContent = preg_replace(
            '/APP_LOCALE=.*/',
            "APP_LOCALE=" . (is_string($settingsConfig['locale'] ?? null) ? $settingsConfig['locale'] : ''),
            $envContent
        ) ?? $envContent;
        // Add APP_LOCALE if it doesn't exist
        if ($envContent && ! str_contains($envContent, 'APP_LOCALE=')) {
            $envContent .= "\nAPP_LOCALE=" .
                (is_string($settingsConfig['locale'] ?? null) ? $settingsConfig['locale'] : '');
        }
        // Update APP_FALLBACK_LOCALE
        $locale = $settingsConfig['locale'] ?? null;
        $localeStr = is_string($locale) ? $locale : '';
        $envContent = preg_replace(
            '/APP_FALLBACK_LOCALE=.*/',
            "APP_FALLBACK_LOCALE=" . $localeStr,
            $envContent,
        ) ?? $envContent;
        // Add APP_FALLBACK_LOCALE if it doesn't exist
        if ($envContent && ! str_contains($envContent, 'APP_FALLBACK_LOCALE = ')) {
            $envContent .= "\nAPP_FALLBACK_LOCALE={$localeStr}";
        }
        // Update APP_FAKER_LOCALE
        $fakerLocale = $localeStr === 'ar' ? 'ar_SA' : 'en_US';
        $envContent = preg_replace(
            '/APP_FAKER_LOCALE=.*/',
            "APP_FAKER_LOCALE={$fakerLocale}",
            $envContent
        ) ?? $envContent;
        // Add APP_FAKER_LOCALE if it doesn't exist
        if ($envContent && ! str_contains($envContent, 'APP_FAKER_LOCALE = ')) {
            $envContent .= "\nAPP_FAKER_LOCALE={$fakerLocale}";
        }
        // Update email configuration if enabled
        if (isset($settingsConfig['enable_email']) && $settingsConfig['enable_email']) {
            $mailMailer = $settingsConfig['mail_mailer'] ?? null;
            $mailHost = $settingsConfig['mail_host'] ?? null;
            $mailPort = $settingsConfig['mail_port'] ?? null;
            $mailUsername = $settingsConfig['mail_username'] ?? null;
            $mailPassword = $settingsConfig['mail_password'] ?? null;
            $mailEncryption = $settingsConfig['mail_encryption'] ?? null;
            $mailFromAddress = $settingsConfig['mail_from_address'] ?? null;
            $mailFromName = $settingsConfig['mail_from_name'] ?? null;

            $envContent = preg_replace(
                '/MAIL_MAILER=.*/',
                "MAIL_MAILER=" . (is_string($mailMailer) ? $mailMailer : ''),
                $envContent
            ) ?? $envContent;
            $envContent = preg_replace(
                '/MAIL_HOST=.*/',
                "MAIL_HOST=" . (is_string($mailHost) ? $mailHost : ''),
                $envContent
            ) ?? $envContent;
            $envContent = preg_replace(
                '/MAIL_PORT=.*/',
                "MAIL_PORT=" . (is_string($mailPort) ? $mailPort : ''),
                $envContent
            ) ?? $envContent;
            $envContent = preg_replace(
                '/MAIL_USERNAME=.*/',
                "MAIL_USERNAME=" . (is_string($mailUsername) ? $mailUsername : ''),
                $envContent,
            ) ?? $envContent;
            $envContent = preg_replace(
                '/MAIL_PASSWORD=.*/',
                "MAIL_PASSWORD=" . (is_string($mailPassword) ? $mailPassword : ''),
                $envContent,
            ) ?? $envContent;
            $envContent = preg_replace(
                '/MAIL_ENCRYPTION=.*/',
                "MAIL_ENCRYPTION=" . (is_string($mailEncryption) ? $mailEncryption : ''),
                $envContent,
            ) ?? $envContent;
            $envContent = preg_replace(
                '/MAIL_FROM_ADDRESS=.*/',
                "MAIL_FROM_ADDRESS=" . (is_string($mailFromAddress) ? $mailFromAddress : ''),
                $envContent,
            ) ?? $envContent;
            $envContent = preg_replace(
                '/MAIL_FROM_NAME=.*/',
                "MAIL_FROM_NAME=\"" . (is_string($mailFromName) ? $mailFromName : '') . "\"",
                $envContent,
            ) ?? $envContent;
        }
        // Keep session and cache drivers as file during installation
        // They will be updated to database after migrations are complete
        // $envContent = preg_replace('/SESSION_DRIVER=.*/', "SESSION_DRIVER=database", $envContent);
        // $envContent = preg_replace('/CACHE_STORE=.*/', "CACHE_STORE=database", $envContent);
        // $envContent = preg_replace('/QUEUE_CONNECTION=.*/', "QUEUE_CONNECTION=database", $envContent);
        // Set debug to false for production
        $envContent = preg_replace('/APP_DEBUG=.*/', 'APP_DEBUG=false', $envContent) ?? $envContent;
        File::put($envPath, $envContent);
    }
    /**
     * Update session and cache drivers to database after migrations.
     */
    private function updateSessionDrivers(): void
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);
        // Update session and cache drivers to database after migrations are complete
        $envContent = preg_replace('/SESSION_DRIVER=.*/', 'SESSION_DRIVER=database', $envContent) ?? $envContent;
        $envContent = preg_replace('/CACHE_STORE=.*/', 'CACHE_STORE=database', $envContent) ?? $envContent;
        $envContent = preg_replace('/QUEUE_CONNECTION=.*/', 'QUEUE_CONNECTION=database', $envContent) ?? $envContent;
        File::put($envPath, $envContent);
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
     * Create admin user.
     *
     * @param array<string, mixed> $adminConfig
     */
    private function createAdminUser(array $adminConfig): void
    {
        try {
            $user = User::create([
                'name' => $adminConfig['name'],
                'email' => $adminConfig['email'],
                'password' => Hash::make(is_string($adminConfig['password'] ?? null) ? $adminConfig['password'] : ''),
                'email_verified_at' => now(),
                'status' => 'active',
                'email_verified' => true,
            ]);
            $user->assignRole('admin');
            // Admin user created successfully
        } catch (\Exception $e) {
            // Failed to create admin user
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
        $validationResult = $this->validateDatabaseRequest($request);
        if (!$validationResult['valid']) {
            return $this->createValidationErrorResponse($validationResult['errors']);
        }

        $connectionResult = $this->testDatabaseConnection($request->all());
        return response()->json($connectionResult);
    }

    private function validateDatabaseRequest(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'db_host' => 'required|string',
            'db_port' => 'required|integer|min:1|max:65535',
            'db_name' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors(),
            ];
        }

        return ['valid' => true];
    }

    private function createValidationErrorResponse($errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors,
        ], 422);
    }
}
