<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Setting Controller with enhanced security.
 *
 * This controller handles system settings management including CRUD operations,
 * API testing, and configuration management with comprehensive security measures.
 *
 * Features:
 * - System settings CRUD operations with Request class validation
 * - API connection testing with security validation
 * - File upload management with security checks
 * - Comprehensive error handling with database transactions
 * - Enhanced security measures (XSS protection, input validation, rate limiting)
 * - Proper logging for errors and warnings only
 * - Request class compatibility with comprehensive validation
 * - Authorization checks and middleware protection
 */
class SettingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * Apply middleware for authentication, authorization, and rate limiting.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('user');
        $this->middleware('verified');
    }

    /**
     * Display the settings management page with enhanced security.
     *
     * Shows all system settings with their current values and provides
     * interface for updating them with proper error handling and security measures.
     *
     * @return View The settings management view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access settings page:
     * GET /admin/settings
     *
     * // Returns view with:
     * // - All system settings
     * // - Current configuration values
     * // - Settings update form
     * // - API testing interface
     */
    public function index(): View
    {
        try {
            // Rate limiting for security
            $key = 'settings-index:'.request()->ip().':'.Auth::id();
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for settings page access', [
                    'ip' => request()->ip(),
                    'user_id' => Auth::id(),
                    'attempts' => RateLimiter::attempts($key),
                ]);

                return view('admin.settings.index', [
                    'error' => 'Too many requests. Please try again later.',
                    'rate_limited' => true,
                ]);
            }
            RateLimiter::hit($key, 300); // 5 minutes window
            // Validate user permissions
            $user = Auth::user();
            if (! $user || (! $user->is_admin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to settings page', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'is_admin' => $user ? $user->is_admin : false,
                    'has_admin_role' => $user ? $user->hasRole('admin') : false,
                ]);

                return view('admin.settings.index', [
                    'error' => 'Access denied. Admin privileges required.',
                    'unauthorized' => true,
                ]);
            }
            DB::beginTransaction();
            $settings = Setting::first();
            if (! $settings) {
                // Create default settings if none exist
                $settings = new Setting([
                    'site_name' => 'Lic',
                    'site_description' => '',
                    'site_logo' => '',
                    'support_email' => '',
                    'avg_response_time' => 24,
                    'support_phone' => '',
                    'timezone' => 'UTC',
                    'maintenance_mode' => false,
                    'envato_personal_token' => '',
                    'envato_api_key' => '',
                    'envato_auth_enabled' => false,
                    'envato_username' => '',
                    'envato_client_id' => '',
                    'envato_client_secret' => '',
                    'envato_redirect_uri' => (is_string(config('app.url')) ? config('app.url') : '').'/auth/envato/callback',
                    'envato_oauth_enabled' => false,
                    'auto_generate_license' => true,
                    'default_license_length' => 32,
                    'license_max_attempts' => 5,
                    'license_lockout_minutes' => 15,
                    // Preloader defaults
                    'preloader_enabled' => true,
                    'preloader_type' => 'spinner',
                    'preloader_color' => '#3b82f6',
                    'preloader_background_color' => '#ffffff',
                    'preloader_duration' => 800,
                    'preloader_custom_css' => null,
                    // Logo defaults
                    'site_logo_dark' => null,
                    'logo_width' => 150,
                    'logo_height' => 50,
                    'logo_show_text' => true,
                    'logo_text' => config('app.name'),
                    'logo_text_color' => '#1f2937',
                    'logo_text_font_size' => '24px',
                    // License API Token default - should be set via environment
                    'license_api_token' => config('app.license_api_token', ''),
                ]);
                $settings->save();
            }
            // Convert to array for backward compatibility
            $settingsArray = $settings->toArray();
            $currentTimezone = old('timezone', $settings->timezone ?? 'UTC');
            // Process human questions for view
            $existingQuestions = [];
            $humanQuestions = $settingsArray['human_questions'] ?? null;
            if (! empty($humanQuestions) && is_string($humanQuestions)) {
                $existingQuestions = json_decode($humanQuestions, true) ?: [];
            }
            // If old input exists (after validation error) prefer it
            if (old('human_questions') && is_array(old('human_questions'))) {
                $existingQuestions = old('human_questions');
            }
            DB::commit();

            return view('admin.settings.index', ['settings' => $settings, 'settingsArray' => $settingsArray, 'currentTimezone' => $currentTimezone, 'existingQuestions' => $existingQuestions]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Settings page failed to load', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);

            return view('admin.settings.index', [
                'error' => 'Unable to load settings. Please try again later.',
                'fallback' => true,
            ]);
        }
    }

    /**
     * Update system settings with enhanced security.
     *
     * Validates and updates all system settings including
     * general settings, license settings, and security options using Request classes.
     *
     * @param  SettingRequest  $request  The validated request containing settings data
     *
     * @return RedirectResponse Redirect back with success/error message
     *
     * @throws ValidationException When validation fails
     * @throws \Exception When database operations fail
     *
     * @example
     * // Update settings:
     * POST /admin/settings
     * {
     *     "site_name": "My License System",
     *     "support_email": "support@example.com",
     *     "maintenance_mode": false,
     *     "license_api_token": "new_token_here"
     * }
     */
    public function update(SettingRequest $request): RedirectResponse
    {
        try {
            // Rate limiting for security
            $key = sprintf('settings-update:%s:%s', $request->ip(), Auth::id()); // security-ignore: SQL_STRING_CONCAT
            if (RateLimiter::tooManyAttempts($key, 3)) {
                Log::warning('Rate limit exceeded for settings update', [
                    'ip' => $request->ip(),
                    'user_id' => Auth::id(),
                    'attempts' => RateLimiter::attempts($key),
                ]);

                return back()
                    ->withInput()
                    ->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes window
            // Validate user permissions
            $user = Auth::user();
            if (! $user || (! $user->is_admin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to update settings', [
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                    'is_admin' => $user ? $user->is_admin : false,
                    'has_admin_role' => $user ? $user->hasRole('admin') : false,
                ]);

                return back()
                    ->withInput()
                    ->with('error', 'Access denied. Admin privileges required.');
            }
            DB::beginTransaction();
            // Get validated data from Request class
            $validated = $request->validated();
            // Handle file uploads with security validation
            if ($request->hasFile('site_logo')) {
                $logoFile = $request->file('site_logo');
                if ($logoFile->isValid()) {
                    $logoPath = $logoFile->store('logos', 'public');
                    $validated['site_logo'] = $logoPath;
                }
            }
            if ($request->hasFile('seo_og_image')) {
                $ogFile = $request->file('seo_og_image');
                if ($ogFile->isValid()) {
                    $ogPath = $ogFile->store('seo', 'public');
                    $validated['seo_og_image'] = $ogPath;
                }
            }
            if ($request->hasFile('site_logo_dark')) {
                $darkLogoFile = $request->file('site_logo_dark');
                if ($darkLogoFile->isValid()) {
                    $darkLogoPath = $darkLogoFile->store('logos', 'public');
                    $validated['site_logo_dark'] = $darkLogoPath;
                }
            }
            // Update settings
            $setting = Setting::firstOrCreate([]);
            foreach ($validated as $key => $value) {
                // Handle human_questions special case
                if ($key === 'human_questions') {
                    if (is_array($value) && !empty($value)) {
                        $decoded = $value;
                    } elseif (! empty($value)) {
                        try {
                            $decoded = json_decode(is_string($value) ? $value : '', true, 512, JSON_THROW_ON_ERROR);
                        } catch (\JsonException $e) {
                            DB::rollBack();

                            return back()->withErrors([
                                'human_questions' => 'Invalid JSON: '.$e->getMessage(),
                            ])->withInput();
                        }
                    } else {
                        $setting->human_questions = null;
                        continue;
                    }
                    if (! is_array($decoded)) {
                        DB::rollBack();

                        return back()->withErrors([
                            'human_questions' => 'Human questions must be an array',
                        ])->withInput();
                    }
                    // Normalize: keep only question & answer strings
                    $normalized = [];
                    foreach ($decoded as $entry) {
                        if (! is_array($entry)) {
                            continue;
                        }
                        $q = trim(is_string($entry['question'] ?? null) ? $entry['question'] : '');
                        $a = trim(is_string($entry['answer'] ?? null) ? $entry['answer'] : '');
                        if ($q === '' || $a === '') {
                            continue;
                        }
                        $normalized[] = ['question' => $q, 'answer' => $a];
                    }
                    $setting->human_questions = ! empty($normalized) ? $normalized : [];
                    continue;
                }
                $setting->$key = $value;
            }
            $setting->save();
            // Clear cache
            Setting::clearCache();
            DB::commit();
            // Check if API token was auto-generated
            $message = 'Settings updated successfully.';
            if (empty($request->input('license_api_token')) || strlen(is_string($request->input('license_api_token')) ? $request->input('license_api_token') : '') < 32) {
                $message .= ' A new API token has been automatically generated for you.';
            }

            return back()->with('success', $message);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Settings validation failed', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Settings update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);

            return back()->withErrors(['general' => 'An error occurred while updating settings. Please try again.']);
        }
    }

    /**
     * Test Envato API connection with enhanced security.
     *
     * Validates the provided Envato API token by making
     * a test request to the Envato API with proper security measures.
     *
     * @param  SettingRequest  $request  The validated request containing API token
     *
     * @return JsonResponse JSON response with test results
     *
     * @throws ValidationException When validation fails
     * @throws \Exception When API test fails
     *
     * @example
     * // Test API connection:
     * POST /admin/settings/test-api
     * {
     *     "token": "your_envato_token_here"
     * }
     */
    public function testApi(SettingRequest $request): JsonResponse
    {
        try {
            // Rate limiting for security
            $key = 'settings-test-api:'.$request->ip().':'.Auth::id();
            if (RateLimiter::tooManyAttempts($key, 5)) {
                Log::warning('Rate limit exceeded for API testing', [
                    'ip' => $request->ip(),
                    'user_id' => Auth::id(),
                    'attempts' => RateLimiter::attempts($key),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                ], 429);
            }
            RateLimiter::hit($key, 300); // 5 minutes window
            // Validate user permissions
            $user = Auth::user();
            if (! $user || (! $user->is_admin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to test API', [
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                    'is_admin' => $user ? $user->is_admin : false,
                    'has_admin_role' => $user ? $user->hasRole('admin') : false,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.',
                ], 403);
            }
            // Get validated token from Request class
            $token = $request->validated()['token'];
            $envatoService = app('App\Services\EnvatoService');
            // Test API by calling testToken method
            $isValid = $envatoService->testToken(is_string($token) ? $token : '');
            if ($isValid) {
                return response()->json([
                    'success' => true,
                    'message' => 'API connection successful! Your token is valid.',
                ]);
            } else {
                Log::warning('Envato API test failed - invalid token', [
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                    'token_prefix' => substr(is_string($token) ? $token : '', 0, 8).'...',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token or API connection failed. Please check your token and try again.',
                ]);
            }
        } catch (ValidationException $e) {
            Log::warning('Envato API test validation failed', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: '.implode(', ', is_array($e->errors()['token'] ?? null) ? $e->errors()['token'] : ['Invalid input']),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Envato API test failed with exception', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'API test failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display Envato integration guide with enhanced security.
     *
     * Shows step-by-step instructions for setting up
     * Envato API integration and OAuth authentication with proper security measures.
     *
     * @return View The Envato integration guide view
     *
     * @throws \Exception When view rendering fails
     *
     * @example
     * // Access Envato guide:
     * GET /admin/settings/envato-guide
     *
     * // Returns view with:
     * // - Step-by-step integration instructions
     * // - API setup guidelines
     * // - OAuth configuration help
     * // - Security best practices
     */
    public function envatoGuide(): View
    {
        try {
            // Rate limiting for security
            $key = 'settings-envato-guide:'.request()->ip().':'.Auth::id();
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for Envato guide access', [
                    'ip' => request()->ip(),
                    'user_id' => Auth::id(),
                    'attempts' => RateLimiter::attempts($key),
                ]);

                return view('admin.settings.envato-guide', [
                    'error' => 'Too many requests. Please try again later.',
                    'rate_limited' => true,
                ]);
            }
            RateLimiter::hit($key, 300); // 5 minutes window
            // Validate user permissions
            $user = Auth::user();
            if (! $user || (! $user->is_admin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to Envato guide', [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'is_admin' => $user ? $user->is_admin : false,
                    'has_admin_role' => $user ? $user->hasRole('admin') : false,
                ]);

                return view('admin.settings.envato-guide', [
                    'error' => 'Access denied. Admin privileges required.',
                    'unauthorized' => true,
                ]);
            }

            return view('admin.settings.envato-guide');
        } catch (\Exception $e) {
            Log::error('Envato guide view failed to load', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);

            return view('admin.settings.envato-guide', [
                'error' => 'Unable to load the Envato guide. Please try again later.',
                'fallback' => true,
            ]);
        }
    }
}
