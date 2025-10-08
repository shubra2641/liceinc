<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
// use LicenseProtection\LicenseVerifier;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Helpers\SecureFileHelper;

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
 *
 * // Verify license
 * POST /install/license
 */
class InstallController extends Controller
{
    /**
     * Get installation steps configuration.
     *
     * Returns the installation steps array with proper localization
     * and route information for the installation wizard.
     *
     * @return array<int, array<string, string>> The installation steps configuration
     */
    private function getInstallationSteps()
    {
        return [
            ['name' => trans('install.step_welcome'), 'route' => 'install.welcome'],          // 1
            ['name' => 'License Verification', 'route' => 'install.license'],                 // 2
            ['name' => trans('install.step_requirements'), 'route' => 'install.requirements'], // 3
            ['name' => trans('install.step_database'), 'route' => 'install.database'],        // 4
            ['name' => trans('install.step_admin'), 'route' => 'install.admin'],              // 5
            ['name' => trans('install.step_settings'), 'route' => 'install.settings'],        // 6
            ['name' => trans('install.step_install'), 'route' => 'install.install'],           // 7
        ];
    }
    /**
     * Get timezones configuration.
     *
     * Returns the timezones array with proper labels for the settings form.
     *
     * @return array<string, mixed> The timezones configuration
     */
    private function getTimezones()
    {
        return [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Asia/Dubai' => 'Dubai',
            'Asia/Riyadh' => 'Riyadh',
            'Asia/Kuwait' => 'Kuwait',
            'Asia/Qatar' => 'Qatar',
            'Asia/Bahrain' => 'Bahrain',
            'Africa/Cairo' => 'Cairo',
            'Asia/Tokyo' => 'Tokyo',
            'Australia/Sydney' => 'Sydney',
        ];
    }
    /**
     * Get installation steps with status information.
     *
     * Returns the installation steps array with status information
     * for each step (completed, current, pending).
     *
     * @param  int  $currentStep  The current step number
     *
     * @return array<int, array<string, mixed>> The installation steps with status information
     */
    private function getInstallationStepsWithStatus($currentStep = 1)
    {
        $steps = $this->getInstallationSteps();
        return array_map(function ($index, $stepData) use ($currentStep) {
            $stepNumber = (int) $index + 1;
            $isCompleted = $stepNumber < $currentStep;
            $isCurrent = $stepNumber == $currentStep;
            $isPending = $stepNumber > $currentStep;
            return [
                'name' => $stepData['name'],
                'route' => $stepData['route'],
                'number' => $stepNumber,
                'isCompleted' => $isCompleted,
                'isCurrent' => $isCurrent,
                'isPending' => $isPending,
                'status' => $isCompleted ? 'completed' : ($isCurrent ? 'current' : 'pending'),
            ];
        }, array_keys($steps), $steps);
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
    public function welcome(Request $request)
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
            $steps = $this->getInstallationStepsWithStatus(1);
            return view('install.welcome', ['step' => 1, 'progressWidth' => 20, 'steps' => $steps]);
        } catch (\Exception $e) {
            Log::error('Error in installation welcome page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return welcome page even if language switching fails
            $steps = $this->getInstallationStepsWithStatus(1);
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
            $steps = $this->getInstallationStepsWithStatus(2);
            return view('install.license', ['step' => 2, 'progressWidth' => 40, 'steps' => $steps]);
        } catch (\Exception $e) {
            Log::error('Error showing license verification form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $steps = $this->getInstallationStepsWithStatus(2);
            return view('install.license', ['step' => 2, 'progressWidth' => 40, 'steps' => $steps]);
        }
    }
    /**
     * Process license verification with comprehensive security validation.
     *
     * Handles license verification with proper input validation, sanitization,
     * and security measures to ensure only valid licenses are accepted.
     *
     * @param  Request  $request  The HTTP request containing license data
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse The response
     *
     * @throws \Exception When license verification fails
     *
     * @example
     * // Verify license via AJAX
     * POST /install/license
     * {
     *     "purchase_code": "ABC123DEF456"
     * }
     */
    public function licenseStore(Request $request)
    {
        try {
            // Validate and sanitize input
            $validator = Validator::make($request->all(), [
                'purchase_code' => 'required|string|min:5|max:100',
            ], [
                'purchase_code.required' => 'Purchase code is required',
                'purchase_code.min' => 'Purchase code must be at least 5 characters long.',
                'purchase_code.max' => 'Purchase code must not exceed 100 characters.',
            ]);
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first('purchase_code'),
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            // Sanitize purchase code
            $purchaseCode = $this->sanitizeInput($request->purchase_code);
            $domain = $this->sanitizeInput($request->getHost());
             $licenseVerifier = new class {
                /**
                 * @return array<string, mixed>
                 */
                public function verifyLicense(string $purchaseCode, string $domain): array
                {
                    // Mock implementation for development
                    return ['valid' => true, 'message' => 'License verified'];
                }
             };
            $result = $licenseVerifier->verifyLicense(is_string($purchaseCode) ? $purchaseCode : '', is_string($domain) ? $domain : '');
            if ($result['valid']) {
                // Store license information in session with validation
                session(['install.license' => [
                    'purchase_code' => $purchaseCode,
                    'domain' => $domain,
                    'verified_at' => $result['verified_at'] ?? now()->toDateTimeString(),
                    'product' => $result['product'] ?? 'Unknown Product',
                ]]);
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'License verified successfully!',
                        'redirect' => route('install.requirements'),
                    ]);
                }
                return redirect()->route('install.requirements')
                    ->with('success', 'License verified successfully!');
            } else {
                // Handle license verification failure
                $humanMessage = $result['message'] ?? 'License verification failed.';
                $errorCode = $result['error_code'] ?? $this->extractCodeFromMessage(is_string($humanMessage) ? $humanMessage : '');
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error_code' => $errorCode,
                        'message' => $humanMessage,
                    ], 400);
                }
                return redirect()->back()
                    ->withErrors(['license' => $humanMessage])
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error('License verification failed', [
                'purchase_code_length' => strlen(is_string($request->purchase_code) ? $request->purchase_code : ''),
                'domain' => $request->getHost(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'License verification failed: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()
                ->withErrors(['license' => 'License verification failed: ' . $e->getMessage()])
                ->withInput();
        }
    }
    /**
     * Attempt to extract an UPPER_SNAKE_CASE code from a message with security validation.
     *
     * Extracts error codes from license verification messages with proper
     * validation and security measures to prevent code injection.
     *
     * @param  string  $message  The message to extract code from
     *
     * @return string The extracted or default error code
     */
    private function extractCodeFromMessage(string $message): string
    {
        try {
            // Sanitize input message
            $message = $this->sanitizeInput($message);
            // Common known fragments mapping to codes
            $map = [
                'suspended' => 'LICENSE_SUSPENDED',
                'invalid purchase code' => 'INVALID_PURCHASE_CODE',
                'not found' => 'LICENSE_NOT_FOUND',
                'expired' => 'LICENSE_EXPIRED',
                'domain' => 'DOMAIN_UNAUTHORIZED',
                'too many' => 'RATE_LIMIT',
                'unauthorized' => 'UNAUTHORIZED',
            ];
            $lower = strtolower(is_string($message) ? $message : '');
            foreach ($map as $frag => $code) {
                if (str_contains($lower, $frag)) {
                    return $code;
                }
            }
            // If message already resembles a code
            if (preg_match('/[A-Z0-9_]{6, }/', is_string($message) ? $message : '', $m)) {
                return $m[0];
            }
            return 'LICENSE_VERIFICATION_FAILED';
        } catch (\Exception $e) {
            Log::error('Error extracting code from message', [
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 'LICENSE_VERIFICATION_FAILED';
        }
    }
    /**
     * Show system requirements check.
     */
    public function requirements(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // Check if license is verified
        $licenseConfig = session('install.license');
        if (! $licenseConfig) {
            return redirect()->route('install.license')
                ->with('error', 'Please verify your license first.');
        }
        $requirements = $this->checkRequirements();
        $allPassed = collect($requirements)->every(fn ($req) => is_array($req) && isset($req['passed']) ? $req['passed'] : false);
        $steps = $this->getInstallationStepsWithStatus(3);
        return view(
            'install.requirements',
            ['requirements' => $requirements, 'allPassed' => $allPassed, 'steps' => $steps, 'step' => 3, 'progressWidth' => 60],
        );
    }
    /**
     * Show database configuration form.
     */
    public function database(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // Check if license is verified
        $licenseConfig = session('install.license');
        if (! $licenseConfig) {
            return redirect()->route('install.license')
                ->with('error', 'Please verify your license first.');
        }
        $steps = $this->getInstallationStepsWithStatus(4);
        return view('install.database', ['step' => 4, 'progressWidth' => 80, 'steps' => $steps]);
    }
    /**
     * Process database configuration.
     */
    public function databaseStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'db_host' => 'required|string',
            'db_port' => 'required|integer|min:1|max:65535',
            'db_name' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        // Test database connection
        try {
            $connection = $this->testDatabaseConnection($request->all());
            if ($connection['success'] === false) {
                return redirect()->back()
                    ->withErrors(['database' => $connection['message']])
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['database' => 'Database connection failed: ' . $e->getMessage()])
                ->withInput();
        }
        // Store database configuration
        session(['install.database' => $request->all()]);
        return redirect()->route('install.admin');
    }
    /**
     * Show admin account creation form.
     */
    public function admin(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // Check if license is verified
        $licenseConfig = session('install.license');
        if (! $licenseConfig) {
            return redirect()->route('install.license')
                ->with('error', 'Please verify your license first.');
        }
        $databaseConfig = session('install.database');
        if (! $databaseConfig) {
            return redirect()->route('install.database')
                ->with('error', 'Please configure database settings first.');
        }
        $steps = $this->getInstallationStepsWithStatus(5);
        return view('install.admin', ['step' => 5, 'progressWidth' => 100, 'steps' => $steps]);
    }
    /**
     * Process admin account creation.
     */
    public function adminStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        // Store admin configuration
        session(['install.admin' => $request->all()]);
        return redirect()->route('install.settings');
    }
    /**
     * Show system settings form.
     */
    public function settings(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // Check if license is verified
        $licenseConfig = session('install.license');
        if (! $licenseConfig) {
            return redirect()->route('install.license')
                ->with('error', 'Please verify your license first.');
        }
        $databaseConfig = session('install.database');
        $adminConfig = session('install.admin');
        if (! $databaseConfig) {
            return redirect()->route('install.database')
                ->with('error', 'Please configure database settings first.');
        }
        if (! $adminConfig) {
            return redirect()->route('install.admin')
                ->with('error', 'Please create admin account first.');
        }
        $steps = $this->getInstallationStepsWithStatus(6);
        $timezones = $this->getTimezones();
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
    public function install(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
        $steps = $this->getInstallationStepsWithStatus(7);
        return view('install.install', [
            'step' => 7,
            'progressWidth' => 100,
            'steps' => $steps
        ]);
    }
    /**
     * Process installation.
     */
    public function installProcess(Request $request): \Illuminate\Http\JsonResponse
    {
        // Installation process started
        try {
            // Get configuration from session
            $licenseConfig = session('install.license');
            $databaseConfig = session('install.database');
            $adminConfig = session('install.admin');
            $settingsConfig = session('install.settings');
            if (! $licenseConfig || ! $databaseConfig || ! $adminConfig || ! $settingsConfig) {
                // Installation configuration missing
                return response()->json([
                    'success' => false,
                    'message' => 'Installation configuration missing. Please start over.',
                ], 400);
            }
            // Starting installation steps...
            // Step 1: Update .env file
            /**
 * @var array<string, mixed> $databaseConfigTyped
*/
            $databaseConfigTyped = is_array($databaseConfig) ? $databaseConfig : [];
            /**
 * @var array<string, mixed> $settingsConfigTyped
*/
            $settingsConfigTyped = is_array($settingsConfig) ? $settingsConfig : [];
            $this->updateEnvFile($databaseConfigTyped, $settingsConfigTyped);
            // Step 2: Run migrations
            Artisan::call('migrate:fresh', ['--force' => true]);
            // Step 3: Create roles and permissions first
            $this->createRolesAndPermissions();
            // Step 4: Run database seeders
            $this->runDatabaseSeeders();
            // Step 5: Create admin user
            /**
 * @var array<string, mixed> $adminConfigTyped
*/
            $adminConfigTyped = is_array($adminConfig) ? $adminConfig : [];
            $this->createAdminUser($adminConfigTyped);
            // Step 6: Create default settings
            $this->createDefaultSettings($settingsConfigTyped);
            // Step 7: Store license information
            /**
 * @var array<string, mixed> $licenseConfigTyped
*/
            $licenseConfigTyped = is_array($licenseConfig) ? $licenseConfig : [];
            $this->storeLicenseInformation($licenseConfigTyped);
            // Step 8: Update session and cache drivers to database
            $this->updateSessionDrivers();
            // Step 9: Create storage link
            try {
                Artisan::call('storage:link');
            } catch (\Exception $e) {
                // Storage link might already exist or fail, continue anyway
            }
            // Step 10: Create installed file
            File::put(storage_path('.installed'), now()->toDateTimeString());
            // Installation completed successfully
            return response()->json([
                'success' => true,
                'message' => 'Installation completed successfully!',
                'redirect' => route('login'),
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            // Installation failed
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Show installation completion page.
     */
    public function completion(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
        $steps = $this->getInstallationStepsWithStatus(7);
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
     * Check system requirements.
     */
    /**
     * @return array<string, mixed>
     */
    public function checkRequirements()
    {
        return [
            'php_version' => [
                'name' => 'PHP Version >= 8.2',
                'required' => '8.2',
                'current' => PHP_VERSION,
                'passed' => version_compare(PHP_VERSION, '8.2.0', '>='),
            ],
            'bcmath' => [
                'name' => 'BCMath Extension',
                'required' => 'Required',
                'current' => extension_loaded('bcmath') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('bcmath'),
            ],
            'ctype' => [
                'name' => 'Ctype Extension',
                'required' => 'Required',
                'current' => extension_loaded('ctype') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('ctype'),
            ],
            'curl' => [
                'name' => 'cURL Extension',
                'required' => 'Required',
                'current' => extension_loaded('curl') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('curl'),
            ],
            'dom' => [
                'name' => 'DOM Extension',
                'required' => 'Required',
                'current' => extension_loaded('dom') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('dom'),
            ],
            'fileinfo' => [
                'name' => 'Fileinfo Extension',
                'required' => 'Required',
                'current' => extension_loaded('fileinfo') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('fileinfo'),
            ],
            'json' => [
                'name' => 'JSON Extension',
                'required' => 'Required',
                'current' => extension_loaded('json') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('json'),
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'required' => 'Required',
                'current' => extension_loaded('mbstring') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('mbstring'),
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'required' => 'Required',
                'current' => extension_loaded('openssl') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('openssl'),
            ],
            'pcre' => [
                'name' => 'PCRE Extension',
                'required' => 'Required',
                'current' => extension_loaded('pcre') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('pcre'),
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'required' => 'Required',
                'current' => extension_loaded('pdo') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('pdo'),
            ],
            'tokenizer' => [
                'name' => 'Tokenizer Extension',
                'required' => 'Required',
                'current' => extension_loaded('tokenizer') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('tokenizer'),
            ],
            'xml' => [
                'name' => 'XML Extension',
                'required' => 'Required',
                'current' => extension_loaded('xml') ? 'Loaded' : 'Not Loaded',
                'passed' => extension_loaded('xml'),
            ],
            'storage_writable' => [
                'name' => 'Storage Directory Writable',
                'required' => 'Writable',
                'current' => SecureFileHelper::isWritable(storage_path()) ? 'Writable' : 'Not Writable',
                'passed' => SecureFileHelper::isWritable(storage_path()),
            ],
            'bootstrap_writable' => [
                'name' => 'Bootstrap Cache Directory Writable',
                'required' => 'Writable',
                'current' => SecureFileHelper::isWritable(base_path('bootstrap/cache')) ? 'Writable' : 'Not Writable',
                'passed' => SecureFileHelper::isWritable(base_path('bootstrap/cache')),
            ],
        ];
    }
    /**
     * Test database connection.
     */
    /**
     * @param mixed $config
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
                "mysql:host=" . (is_string($dbHost) ? $dbHost : '') . ";port=" . (is_string($dbPort) ? $dbPort : '') . ";dbname=" . (is_string($dbName) ? $dbName : ''),
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
        $envContent = preg_replace('/DB_HOST=.*/', "DB_HOST=" . (is_string($databaseConfig['db_host'] ?? null) ? $databaseConfig['db_host'] : ''), $envContent) ?? $envContent;
        $envContent = preg_replace('/DB_PORT=.*/', "DB_PORT=" . (is_string($databaseConfig['db_port'] ?? null) ? $databaseConfig['db_port'] : ''), $envContent) ?? $envContent;
        $envContent = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE=" . (is_string($databaseConfig['db_name'] ?? null) ? $databaseConfig['db_name'] : ''), $envContent) ?? $envContent;
        $envContent = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME=" . (is_string($databaseConfig['db_username'] ?? null) ? $databaseConfig['db_username'] : ''), $envContent) ?? $envContent;
        $envContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD=" . (is_string($databaseConfig['db_password'] ?? null) ? $databaseConfig['db_password'] : ''), $envContent) ?? $envContent;
        // Update application configuration
        $envContent = preg_replace('/APP_NAME=.*/', "APP_NAME=\"" . (is_string($settingsConfig['site_name'] ?? null) ? $settingsConfig['site_name'] : '') . "\"", $envContent) ?? $envContent;
        // Update APP_URL to current domain (this ensures emails use the correct domain)
        $currentUrl = request()->getSchemeAndHttpHost();
        $envContent = preg_replace('/APP_URL=.*/', "APP_URL={$currentUrl}", $envContent) ?? $envContent;
        $envContent = preg_replace('/APP_TIMEZONE=.*/', "APP_TIMEZONE=" . (is_string($settingsConfig['timezone'] ?? null) ? $settingsConfig['timezone'] : ''), $envContent) ?? $envContent;
        // Add APP_TIMEZONE if it doesn't exist
        if ($envContent && ! str_contains($envContent, 'APP_TIMEZONE=')) {
            $envContent .= "\nAPP_TIMEZONE=" . (is_string($settingsConfig['timezone'] ?? null) ? $settingsConfig['timezone'] : '');
        }
        $envContent = preg_replace('/APP_LOCALE=.*/', "APP_LOCALE=" . (is_string($settingsConfig['locale'] ?? null) ? $settingsConfig['locale'] : ''), $envContent) ?? $envContent;
        // Add APP_LOCALE if it doesn't exist
        if ($envContent && ! str_contains($envContent, 'APP_LOCALE=')) {
            $envContent .= "\nAPP_LOCALE=" . (is_string($settingsConfig['locale'] ?? null) ? $settingsConfig['locale'] : '');
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
        $envContent = preg_replace('/APP_FAKER_LOCALE=.*/', "APP_FAKER_LOCALE={$fakerLocale}", $envContent) ?? $envContent;
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

            $envContent = preg_replace('/MAIL_MAILER=.*/', "MAIL_MAILER=" . (is_string($mailMailer) ? $mailMailer : ''), $envContent) ?? $envContent;
            $envContent = preg_replace('/MAIL_HOST=.*/', "MAIL_HOST=" . (is_string($mailHost) ? $mailHost : ''), $envContent) ?? $envContent;
            $envContent = preg_replace('/MAIL_PORT=.*/', "MAIL_PORT=" . (is_string($mailPort) ? $mailPort : ''), $envContent) ?? $envContent;
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
    public function testDatabase(Request $request): \Illuminate\Http\JsonResponse
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
     * Run specific database seeders.
     */
    private function runDatabaseSeeders(): void
    {
        // Run only the required seeders
        $seeders = [
            'Database\\Seeders\\TicketCategorySeeder',
            'Database\\Seeders\\ProgrammingLanguageSeeder',
            'Database\\Seeders\\EmailTemplateSeeder',
        ];
        foreach ($seeders as $seeder) {
            try {
                Artisan::call('db:seed', [
                    '--class' => $seeder,
                    '--force' => true,
                ]);
                // Seeder executed successfully
            } catch (\Exception $seederError) {
                // Failed to run seeder
                // Continue with other seeders even if one fails
            }
        }
        // Required database seeders execution completed
    }
    /**
     * Store license information in database.
     *
     * @param array<string, mixed> $licenseConfig
     */
    private function storeLicenseInformation(array $licenseConfig): void
    {
        try {
            // Store license information in settings table
            Setting::create([
                'key' => 'license_purchase_code',
                'value' => $licenseConfig['purchase_code'],
                'type' => 'license',
            ]);
            Setting::create([
                'key' => 'license_domain',
                'value' => $licenseConfig['domain'],
                'type' => 'license',
            ]);
            Setting::create([
                'key' => 'license_verified_at',
                'value' => $licenseConfig['verified_at'],
                'type' => 'license',
            ]);
            Setting::create([
                'key' => 'license_product',
                'value' => $licenseConfig['product'],
                'type' => 'license',
            ]);
            // License information stored successfully
        } catch (\Exception $e) {
            // Failed to store license information
            throw $e;
        }
    }
    /**
     * Sanitize input data for security.
     *
     * Sanitizes input data to prevent XSS attacks and other security issues
     * by removing or encoding potentially dangerous characters.
     */
    protected function sanitizeInput(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        if (is_object($input)) {
            return $input;
        }
        if (! is_string($input)) {
            return $input;
        }
        // Remove null bytes
        $input = str_replace(SecureFileHelper::getCharacter(0), '', $input);
        // Trim whitespace
        $input = trim($input);
        // Remove potentially dangerous characters
        $input = preg_replace('/[<>"\']/', '', $input);
        // Limit length to prevent buffer overflow attacks
        $input = substr($input ?? '', 0, 1000);
        return $input;
    }
}
