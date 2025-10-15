<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use LicenseProtection\LicenseVerifier;

class InstallController extends Controller
{
    public function welcome(Request $request): View
    {
        $locale = $request->get('lang', 'en');
        if (in_array($locale, ['en', 'ar', 'hi'])) {
            app()->setLocale($locale);
            session(['locale' => $locale]);
        }

        return view('install.welcome', ['step' => 1]);
    }

    public function license(): View
    {
        return view('install.license', ['step' => 2]);
    }

    public function licenseStore(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'purchase_code' => ['required', 'string', 'min:5', 'max:100'],
            ]);

            if ($validator->fails()) {
                return $this->handleValidationError($request, $validator->errors()->first());
            }

            $purchaseCode = $request->input('purchase_code');
            $domain = $request->getHost();

            // Use the actual LicenseVerifier
            $verifier = new LicenseVerifier();
            $verificationResult = $verifier->verifyLicense($purchaseCode, $domain);

            if ($verificationResult['valid'] ?? false) {
                session(['install.license' => $verificationResult['data']]);
                return $this->verificationResponse(
                    $request,
                    true,
                    'License verified successfully',
                    $verificationResult
                );
            } else {
                $message = $verificationResult['message'] ?? 'License verification failed';
                return $this->verificationResponse($request, false, $message, $verificationResult);
            }
        } catch (\Throwable $exception) {
            Log::error('License verification error in InstallController', ['error' => $exception->getMessage()]);
            return $this->verificationResponse(
                $request,
                false,
                'An error occurred during verification',
                ['general' => $exception->getMessage()],
                500
            );
        }
    }

    public function requirements(): View
    {
        if (!session('install.license')) {
            return redirect()->route('install.license')->withErrors(['error' => 'Please verify your license first.']);
        }

        $requirements = [
            'php_version' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'extensions' => [
                'pdo' => extension_loaded('pdo'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'curl' => extension_loaded('curl'),
                'gd' => extension_loaded('gd'),
            ],
            'writable_dirs' => [
                'storage' => is_writable(storage_path()),
                'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
            ]
        ];

        $allPassed = $requirements['php_version'] &&
                    array_reduce($requirements['extensions'], fn($carry, $ext) => $carry && $ext, true) &&
                    array_reduce($requirements['writable_dirs'], fn($carry, $dir) => $carry && $dir, true);

        return view('install.requirements', [
            'step' => 3,
            'requirements' => $requirements,
            'allPassed' => $allPassed
        ]);
    }

    public function database(): View
    {
        if (!session('install.license')) {
            return redirect()->route('install.license')->withErrors(['error' => 'Please verify your license first.']);
        }

        return view('install.database', ['step' => 4]);
    }

    public function databaseStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Test database connection
        try {
            $config = [
                'driver' => 'mysql',
                'host' => $request->input('host'),
                'port' => $request->input('port'),
                'database' => $request->input('database'),
                'username' => $request->input('username'),
                'password' => $request->input('password'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ];

            config(['database.connections.test' => $config]);
            DB::connection('test')->getPdo();

            session(['install.database' => $request->all()]);
            return redirect()->route('install.admin');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['database' => 'Database connection failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function admin(): View
    {
        if (!session('install.license')) {
            return redirect()->route('install.license')->withErrors(['error' => 'Please verify your license first.']);
        }
        if (!session('install.database')) {
            return redirect()->route('install.database')->withErrors(['error' => 'Please configure database settings first.']);
        }

        return view('install.admin', ['step' => 5]);
    }

    public function adminStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        session(['install.admin' => $request->all()]);
        return redirect()->route('install.settings');
    }

    public function settings(): View
    {
        if (!session('install.license')) {
            return redirect()->route('install.license')->withErrors(['error' => 'Please verify your license first.']);
        }
        if (!session('install.database')) {
            return redirect()->route('install.database')->withErrors(['error' => 'Please configure database settings first.']);
        }
        if (!session('install.admin')) {
            return redirect()->route('install.admin')->withErrors(['error' => 'Please create admin account first.']);
        }

        return view('install.settings', ['step' => 6]);
    }

    public function settingsStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        session(['install.settings' => $request->all()]);
        return redirect()->route('install.install');
    }

    public function install(): View
    {
        if (!session('install.license')) {
            return redirect()->route('install.license')->withErrors(['error' => 'Please verify your license first.']);
        }
        if (!session('install.database')) {
            return redirect()->route('install.database')->withErrors(['error' => 'Please configure database settings first.']);
        }
        if (!session('install.admin')) {
            return redirect()->route('install.admin')->withErrors(['error' => 'Please create admin account first.']);
        }
        if (!session('install.settings')) {
            return redirect()->route('install.settings')->withErrors(['error' => 'Please configure application settings first.']);
        }

        return view('install.install', ['step' => 7]);
    }

    public function installProcess(Request $request)
    {
        try {
            // Update .env file
            $this->updateEnvFile();

            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Create admin user
            $this->createAdminUser();

            // Create settings
            $this->createSettings();

            // Create installation file
            $this->createInstallationFile();

            return response()->json(['success' => true, 'message' => 'Installation completed successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Installation failed: ' . $e->getMessage()], 500);
        }
    }

    private function updateEnvFile()
    {
        $database = session('install.database');
        $settings = session('install.settings');

        $envContent = file_get_contents(base_path('.env'));

        $envContent = str_replace('DB_HOST=127.0.0.1', 'DB_HOST=' . $database['host'], $envContent);
        $envContent = str_replace('DB_PORT=3306', 'DB_PORT=' . $database['port'], $envContent);
        $envContent = str_replace('DB_DATABASE=laravel', 'DB_DATABASE=' . $database['database'], $envContent);
        $envContent = str_replace('DB_USERNAME=root', 'DB_USERNAME=' . $database['username'], $envContent);
        $envContent = str_replace('DB_PASSWORD=', 'DB_PASSWORD=' . $database['password'], $envContent);

        $envContent = str_replace('APP_NAME=Laravel', 'APP_NAME="' . $settings['app_name'] . '"', $envContent);
        $envContent = str_replace('APP_URL=http://localhost', 'APP_URL=' . $settings['app_url'], $envContent);

        if ($settings['mail_host']) {
            $envContent = str_replace('MAIL_HOST=mailpit', 'MAIL_HOST=' . $settings['mail_host'], $envContent);
            $envContent = str_replace('MAIL_PORT=1025', 'MAIL_PORT=' . $settings['mail_port'], $envContent);
            $envContent = str_replace('MAIL_USERNAME=null', 'MAIL_USERNAME=' . $settings['mail_username'], $envContent);
            $envContent = str_replace('MAIL_PASSWORD=null', 'MAIL_PASSWORD=' . $settings['mail_password'], $envContent);
            $envContent = str_replace('MAIL_ENCRYPTION=null', 'MAIL_ENCRYPTION=' . $settings['mail_encryption'], $envContent);
        }

        file_put_contents(base_path('.env'), $envContent);
    }

    private function createAdminUser()
    {
        $admin = session('install.admin');

        User::create([
            'name' => $admin['name'],
            'email' => $admin['email'],
            'password' => Hash::make($admin['password']),
            'is_admin' => true,
            'email_verified_at' => now()
        ]);
    }

    private function createSettings()
    {
        $settings = session('install.settings');
        $license = session('install.license');

        Setting::create(['key' => 'app_name', 'value' => $settings['app_name']]);
        Setting::create(['key' => 'app_url', 'value' => $settings['app_url']]);
        Setting::create(['key' => 'purchase_code', 'value' => $license]);
        Setting::create(['key' => 'installation_completed', 'value' => 'true']);
    }

    private function createInstallationFile()
    {
        $installFile = base_path('installed');
        file_put_contents($installFile, date('Y-m-d H:i:s'));
    }

    public function complete(): View
    {
        return view('install.complete');
    }

    /**
     * Handle validation errors with proper response format.
     */
    private function handleValidationError(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }
        return redirect()->back()->withErrors(['purchase_code' => $message])->withInput();
    }

    /**
     * Handle verification response with proper format.
     */
    private function verificationResponse(Request $request, bool $success, string $message, array $data = [], int $statusCode = 200): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data
            ], $statusCode);
        }

        if ($success) {
            return redirect()->route('install.requirements')->with('success', $message);
        } else {
            return redirect()->back()->withErrors(['purchase_code' => $message])->withInput();
        }
    }
}
