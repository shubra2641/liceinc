<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * License Verification Guide Controller with enhanced security.
 *
 * This controller displays the license verification guide for developers
 * and administrators, providing comprehensive documentation on how to implement
 * license verification in their applications with security best practices.
 *
 * Features:
 * - Developer documentation and guides
 * - License verification examples and code samples
 * - API integration instructions with security guidelines
 * - Best practices and security implementation guides
 * - Comprehensive error handling with proper logging
 * - Enhanced security measures for documentation access
 * - Rate limiting and authorization checks
 * - Proper logging for errors and warnings only
 * - Database transaction support for future enhancements
 */
class LicenseVerificationGuideController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * Apply middleware for authentication and authorization.
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
     * Display the license verification guide for developers with enhanced security.
     *
     * Shows comprehensive documentation for license verification implementation,
     * including API examples, security best practices, and integration guides.
     * Implements rate limiting and proper error handling with fallback mechanisms.
     *
     * @param  Request  $request  The HTTP request instance
     *
     * @return View The license verification guide view
     *
     * @throws \Exception When view rendering fails or rate limit exceeded
     *
     * @example
     * // Access the license verification guide:
     * GET /admin/license-verification-guide
     *
     * // Returns view with:
     * // - Developer documentation
     * // - API integration examples
     * // - Security best practices
     * // - Code samples and templates
     * // - Rate limiting protection
     * // - Enhanced error handling
     */
    public function index(Request $request): View
    {
        try {
            // Rate limiting for security
            $key = 'license-guide:'.$request->ip().':'.Auth::id();
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for license verification guide access', [
                    'ip' => $request->ip(),
                    'user_id' => Auth::id(),
                    'attempts' => RateLimiter::attempts($key),
                ]);

                return view('admin.license-verification-guide.index', [
                    'error' => 'Too many requests. Please try again later.',
                    'rate_limited' => true,
                ]);
            }
            RateLimiter::hit($key, 300); // 5 minutes window
            // Validate user permissions
            $user = Auth::user();
            if (! $user || (! $user->is_admin && ! $user->hasRole('admin'))) {
                Log::warning('Unauthorized access attempt to license verification guide', [
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                    'is_admin' => $user ? $user->is_admin : false,
                    'has_admin_role' => $user ? $user->hasRole('admin') : false,
                ]);

                return view('admin.license-verification-guide.index', [
                    'error' => 'Access denied. Admin privileges required.',
                    'unauthorized' => true,
                ]);
            }
            // Prepare guide data with security context
            $guideData = [
                'user' => Auth::user(),
                'timestamp' => now(),
                'security_level' => 'enhanced',
                'api_endpoints' => [
                    'verify' => url('api/license/verify'),
                    'register' => url('api/license/register'),
                    'status' => url('api/license/status'),
                ],
            ];

            return view('admin.license-verification-guide.index', $guideData);
        } catch (\Exception $e) {
            Log::error('License verification guide view failed to load', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
            ]);

            // Return fallback view with error information
            return view('admin.license-verification-guide.index', [
                'error' => 'Unable to load the license verification guide. Please try again later.',
                'fallback' => true,
                'support_contact' => config('app.support_email', 'support@example.com'),
            ]);
        }
    }
}
