<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Installation\InstallationService;
use App\Services\Installation\InstallationStepService;
use App\Services\Installation\LicenseValidationService;
use App\Services\Installation\LicenseVerificationService;
use App\Services\Installation\SystemRequirementsService;
use App\Services\Installation\UserCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InstallController extends Controller
{
    private const STEP_PROGRESS = [
        1 => 20,
        2 => 40,
        3 => 60,
        4 => 80,
        5 => 100,
        6 => 100,
        7 => 100,
    ];

    public function __construct(
        private InstallationStepService $stepService,
        private LicenseVerificationService $licenseService,
        private SystemRequirementsService $requirementsService,
        private InstallationService $installationService,
        private LicenseValidationService $validationService,
        private UserCreationService $userService
    ) {
    }

    public function welcome(Request $request): View
    {
        $locale = $this->sanitizeLocale((string) $request->get('lang', ''));
        if ($locale !== null) {
            app()->setLocale($locale);
            session(['locale' => $locale]);
        }

        return $this->stepView(1, 'install.welcome');
    }

    public function license(): View
    {
        return $this->stepView(2, 'install.license');
    }

    public function licenseStore(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $validation = $this->validationService->validateLicenseInput($request);
            if (!$validation['valid']) {
                $message = (string) ($validation['message'] ?? 'Validation failed');
                return $this->validationService->handleValidationError($request, $message);
            }

            $verification = $this->verifyLicense($request);
            if ($verification['valid'] ?? false) {
                return $this->verificationResponse(
                    $request,
                    true,
                    'License verified successfully',
                    $verification
                );
            }

            $errors = $verification['errors'] ?? ['general' => 'License verification failed'];
            return $this->verificationResponse(
                $request,
                false,
                'License verification failed',
                is_array($errors) ? $errors : ['general' => (string) $errors]
            );
        } catch (\Throwable $exception) {
            return $this->verificationResponse(
                $request,
                false,
                'An error occurred during verification',
                ['general' => $exception->getMessage()],
                500
            );
        }
    }

    public function requirements(): View|RedirectResponse
    {
        $redirect = $this->ensureSessions([
            ['install.license', 'install.license', 'Please verify your license first.'],
        ]);
        if ($redirect !== null) {
            return $redirect;
        }

        $requirements = $this->requirementsService->checkRequirements();

        return $this->stepView(3, 'install.requirements', [
            'requirements' => $requirements,
            'allPassed' => (bool) ($requirements['passed'] ?? false),
        ]);
    }

    public function database(): View|RedirectResponse
    {
        $redirect = $this->ensureSessions([
            ['install.license', 'install.license', 'Please verify your license first.'],
        ]);
        return $redirect ?? $this->stepView(4, 'install.database');
    }

    public function databaseStore(Request $request): RedirectResponse
    {
        $data = $request->all();
        $validation = $this->validateDatabaseInput($data);
        if (!$validation['valid']) {
            $errors = $validation['errors'] ?? [];
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $connection = $this->testDatabaseConnection($data);
        if (!($connection['success'] ?? false)) {
            return redirect()->back()
                ->withErrors(['database' => $connection['message'] ?? 'Connection failed'])
                ->withInput();
        }

        session(['install.database' => $data]);

        return redirect()->route('install.admin');
    }

    public function admin(): View|RedirectResponse
    {
        $redirect = $this->ensureSessions([
            ['install.license', 'install.license', 'Please verify your license first.'],
            ['install.database', 'install.database', 'Please configure database settings first.'],
        ]);
        if ($redirect !== null) {
            return $redirect;
        }

        if (!$this->isDatabaseConfigured()) {
            return redirect()->route('install.database')
                ->with('error', 'Please configure database settings first.');
        }

        return $this->stepView(5, 'install.admin');
    }

    public function adminStore(Request $request): RedirectResponse
    {
        $validation = $this->validationService->validateAdminInput($request);
        if (!$validation['valid']) {
            $errors = $validation['errors'] ?? [];
            return redirect()->back()->withErrors($errors)->withInput();
        }

        session(['install.admin' => $request->all()]);

        return redirect()->route('install.settings');
    }

    public function settings(): View|RedirectResponse
    {
        $redirect = $this->ensureSessions([
            ['install.license', 'install.license', 'Please verify your license first.'],
            ['install.database', 'install.database', 'Please configure database settings first.'],
            ['install.admin', 'install.admin', 'Please create admin account first.'],
        ]);
        if ($redirect !== null) {
            return $redirect;
        }

        return $this->stepView(6, 'install.settings', [
            'timezones' => $this->stepService->getTimezones(),
        ]);
    }

    public function settingsStore(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->getSettingsValidationRules());
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        session(['install.settings' => $request->all()]);

        return redirect()->route('install.install');
    }

    public function install(): View|RedirectResponse
    {
        $redirect = $this->ensureSessions([
            ['install.license', 'install.license', 'Please verify your license first.'],
            ['install.database', 'install.database', 'Please configure database settings first.'],
            ['install.admin', 'install.admin', 'Please create admin account first.'],
            ['install.settings', 'install.settings', 'Please configure system settings first.'],
        ]);

        return $redirect ?? $this->stepView(7, 'install.install');
    }

    public function installProcess(Request $request): JsonResponse
    {
        try {
            $configs = $this->getInstallationConfigs();
            if ($configs === null) {
                return $this->errorResponse(
                    'Installation configuration missing. Please start over.',
                    400
                );
            }

            $this->runInstallationSteps($configs);

            return $this->successResponse('Installation completed successfully!');
        } catch (\Throwable $exception) {
            return $this->errorResponse(
                'Installation failed: ' . $exception->getMessage(),
                500
            );
        }
    }

    public function completion(): View|RedirectResponse
    {
        if (!File::exists(storage_path('.installed'))) {
            return redirect()->route('install.welcome');
        }

        $redirect = $this->ensureSessions([
            ['install.license', 'install.welcome', 'Installation data missing. Start again.'],
            ['install.database', 'install.welcome', 'Installation data missing. Start again.'],
            ['install.admin', 'install.welcome', 'Installation data missing. Start again.'],
            ['install.settings', 'install.welcome', 'Installation data missing. Start again.'],
        ]);
        if ($redirect !== null) {
            return $redirect;
        }

        $viewData = [
            'licenseConfig' => session('install.license'),
            'adminConfig' => session('install.admin'),
            'settingsConfig' => session('install.settings'),
            'databaseConfig' => session('install.database'),
        ];

        session()->forget([
            'install.license',
            'install.database',
            'install.admin',
            'install.settings',
        ]);

        return $this->stepView(7, 'install.completion', $viewData);
    }

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

        return response()->json($this->testDatabaseConnection($request->all()));
    }

    private function verificationResponse(
        Request $request,
        bool $success,
        string $message,
        array $payload = [],
        int $status = 200
    ): RedirectResponse|JsonResponse {
        if ($request->expectsJson()) {
            $body = ['success' => $success, 'message' => $message];
            if ($success) {
                $body['data'] = $payload;
            } else {
                $body['errors'] = $payload;
            }
            $code = $success ? $status : ($status === 200 ? 422 : $status);

            return response()->json($body, $code);
        }

        if ($success) {
            return redirect()->back()->with('success', $message);
        }

        $errors = $payload ?: ['general' => $message];

        return redirect()->back()->withErrors($errors);
    }

    private function validateDatabaseInput(array $data): array
    {
        $validator = Validator::make($data, [
            'db_host' => 'required|string|max:255',
            'db_port' => 'required|integer|min:1|max:65535',
            'db_name' => 'required|string|max:255',
            'db_username' => 'required|string|max:255',
            'db_password' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ['valid' => false, 'errors' => $validator->errors()->toArray()];
        }

        return ['valid' => true];
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
            if (!is_array($config) || $config === []) {
                return null;
            }
        }

        return $configs;
    }

    private function runInstallationSteps(array $configs): void
    {
        $this->setupDatabase($configs);
        $this->runMigrations();
        $this->createUserAndSettings($configs);
        $this->finalizeInstallation($configs);
    }

    private function setupDatabase(array $configs): void
    {
        $database = is_array($configs['database']) ? $configs['database'] : [];
        $settings = is_array($configs['settings']) ? $configs['settings'] : [];

        $this->updateEnvFile($database, $settings);
    }

    private function runMigrations(): void
    {
        Artisan::call('migrate:fresh', ['--force' => true]);
        $this->createRolesAndPermissions();
        $this->installationService->runSeeders();
    }

    private function createUserAndSettings(array $configs): void
    {
        $adminData = is_array($configs['admin']) ? $configs['admin'] : [];
        $settings = is_array($configs['settings']) ? $configs['settings'] : [];

        $this->userService->createAdminUser($adminData);
        $this->createDefaultSettings($settings);
    }

    private function finalizeInstallation(array $configs): void
    {
        $license = is_array($configs['license']) ? $configs['license'] : [];

        $this->installationService->storeLicenseInformation($license);
        $this->installationService->updateSessionDrivers();
        $this->installationService->createStorageLink();
        $this->installationService->createInstalledFile();
    }

    private function successResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'redirect' => route('login'),
        ]);
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    private function testDatabaseConnection(array $config): array
    {
        try {
            $data = $this->normalizeDatabaseData($config);
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s',
                $data['host'],
                $data['port'],
                $data['database']
            );
            $pdo = new \PDO($dsn, $data['username'], $data['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return ['success' => true, 'message' => 'Database connection successful'];
        } catch (\PDOException $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    private function normalizeDatabaseData(array $data): array
    {
        return [
            'host' => (string) ($data['db_host'] ?? ''),
            'port' => (string) ($data['db_port'] ?? ''),
            'database' => (string) ($data['db_name'] ?? ''),
            'username' => (string) ($data['db_username'] ?? ''),
            'password' => (string) ($data['db_password'] ?? ''),
        ];
    }

    private function updateEnvFile(array $databaseConfig, array $settingsConfig): void
    {
        $path = base_path('.env');
        $content = File::get($path);

        $content = $this->updateDatabaseConfig($content, $databaseConfig);
        $content = $this->updateApplicationConfig($content, $settingsConfig);

        File::put($path, $content);
    }

    private function updateDatabaseConfig(string $content, array $databaseConfig): string
    {
        $values = [
            'DB_HOST' => (string) ($databaseConfig['db_host'] ?? ''),
            'DB_PORT' => (string) ($databaseConfig['db_port'] ?? ''),
            'DB_DATABASE' => (string) ($databaseConfig['db_name'] ?? ''),
            'DB_USERNAME' => (string) ($databaseConfig['db_username'] ?? ''),
            'DB_PASSWORD' => (string) ($databaseConfig['db_password'] ?? ''),
        ];

        foreach ($values as $key => $value) {
            $content = $this->replaceEnvValue($content, $key, $value);
        }

        return $content;
    }

    private function updateApplicationConfig(string $content, array $settingsConfig): string
    {
        $content = $this->updateAppSettings($content, $settingsConfig);
        $content = $this->updateLocaleSettings($content, $settingsConfig);
        $content = $this->updateEmailSettings($content, $settingsConfig);

        return $content;
    }

    private function updateAppSettings(string $content, array $settingsConfig): string
    {
        $name = (string) ($settingsConfig['site_name'] ?? '');
        $timezone = (string) ($settingsConfig['timezone'] ?? '');
        $url = request()->getSchemeAndHttpHost();

        $content = $this->replaceEnvValue($content, 'APP_NAME', '"' . $name . '"');
        $content = $this->replaceEnvValue($content, 'APP_URL', $url);
        $content = $this->replaceEnvValue($content, 'APP_TIMEZONE', $timezone);

        return $content;
    }

    private function updateLocaleSettings(string $content, array $settingsConfig): string
    {
        $locale = (string) ($settingsConfig['locale'] ?? 'en');
        $fallback = $locale;
        $faker = $locale === 'ar' ? 'ar_SA' : 'en_US';

        $content = $this->replaceEnvValue($content, 'APP_LOCALE', $locale);
        $content = $this->replaceEnvValue($content, 'APP_FALLBACK_LOCALE', $fallback);
        $content = $this->replaceEnvValue($content, 'APP_FAKER_LOCALE', $faker);

        return $content;
    }

    private function updateEmailSettings(string $content, array $settingsConfig): string
    {
        if (empty($settingsConfig['enable_email'])) {
            return $content;
        }

        $fields = [
            'MAIL_MAILER' => (string) ($settingsConfig['mail_mailer'] ?? ''),
            'MAIL_HOST' => (string) ($settingsConfig['mail_host'] ?? ''),
            'MAIL_PORT' => (string) ($settingsConfig['mail_port'] ?? ''),
            'MAIL_USERNAME' => (string) ($settingsConfig['mail_username'] ?? ''),
            'MAIL_PASSWORD' => (string) ($settingsConfig['mail_password'] ?? ''),
            'MAIL_ENCRYPTION' => (string) ($settingsConfig['mail_encryption'] ?? ''),
            'MAIL_FROM_ADDRESS' => (string) ($settingsConfig['mail_from_address'] ?? ''),
            'MAIL_FROM_NAME' => (string) ($settingsConfig['mail_from_name'] ?? ''),
        ];

        foreach ($fields as $key => $value) {
            $content = $this->replaceEnvValue($content, $key, $value);
        }

        return $content;
    }

    private function createRolesAndPermissions(): void
    {
        $permissions = [
            'manage_users',
            'manage_products',
            'manage_licenses',
            'manage_tickets',
            'manage_settings',
            'manage_knowledge_base',
            'view_reports',
            'manage_invoices',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->givePermissionTo(['view_reports']);
    }

    private function createDefaultSettings(array $settingsConfig): void
    {
        Setting::create([
            'site_name' => $settingsConfig['site_name'] ?? '',
            'site_description' => $settingsConfig['site_description'] ?? null,
            'admin_email' => $settingsConfig['admin_email'] ?? null,
            'timezone' => $settingsConfig['timezone'] ?? 'UTC',
            'locale' => $settingsConfig['locale'] ?? 'en',
            'preloader_enabled' => true,
            'preloader_type' => 'spinner',
            'preloader_color' => '#3b82f6',
            'preloader_background_color' => '#ffffff',
            'preloader_duration' => 2000,
            'logo_show_text' => true,
            'logo_text' => $settingsConfig['site_name'] ?? '',
            'maintenance_mode' => false,
            'registration_enabled' => true,
            'email_verification_required' => !empty($settingsConfig['enable_email']),
        ]);
    }

    private function replaceEnvValue(string $content, string $key, string $value): string
    {
        $pattern = "/^{$key}=.*/m";
        $replacement = sprintf('%s=%s', $key, $value);

        return preg_replace($pattern, $replacement, $content) ?? $content;
    }

    private function ensureSessions(array $requirements): ?RedirectResponse
    {
        foreach ($requirements as $requirement) {
            [$key, $route, $message] = $requirement;
            if (!session($key)) {
                return redirect()->route($route)->with('error', $message);
            }
        }

        return null;
    }

    private function stepView(int $step, string $view, array $data = []): View
    {
        $defaults = [
            'step' => $step,
            'progressWidth' => $this->progressForStep($step),
            'steps' => $this->stepService->getInstallationStepsWithStatus($step),
        ];

        return view($view, array_merge($defaults, $data));
    }

    private function progressForStep(int $step): int
    {
        return self::STEP_PROGRESS[$step] ?? 100;
    }

    private function isDatabaseConfigured(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function getSettingsValidationRules(): array
    {
        return [
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'admin_email' => 'required_if:enable_email,1|nullable|string|email|max:255',
            'timezone' => 'required|string',
            'locale' => 'required|string|in:en,ar',
            'enable_email' => 'nullable|boolean',
            'mail_mailer' => 'required_if:enable_email,1|nullable|string|in:smtp,mailgun,ses,postmark',
            'mail_host' => 'required_if:enable_email,1|nullable|string|max:255',
            'mail_port' => 'required_if:enable_email,1|nullable|integer|min:1|max:65535',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_username' => 'required_if:enable_email,1|nullable|string|max:255',
            'mail_password' => 'required_if:enable_email,1|nullable|string|max:255',
            'mail_from_address' => 'required_if:enable_email,1|nullable|email|max:255',
            'mail_from_name' => 'required_if:enable_email,1|nullable|string|max:255',
        ];
    }

    private function sanitizeLocale(string $candidate): ?string
    {
        $value = strtolower(trim($candidate));

        return in_array($value, ['en', 'ar'], true) ? $value : null;
    }

    private function verifyLicense(Request $request): array
    {
        $purchaseCode = $this->validationService->sanitizeInput($request->input('purchase_code'));
        $domain = $this->validationService->sanitizeInput($request->getHost());

        return $this->licenseService->verifyLicense(
            is_string($purchaseCode) ? $purchaseCode : '',
            is_string($domain) ? $domain : ''
        );
    }
}
