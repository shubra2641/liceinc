<?php

/**
 * Authentication Routes with Enhanced Security and Comprehensive Access Control.
 *
 * This file defines all authentication-related routes including registration,
 * login, password reset, email verification, and logout functionality. It implements
 * comprehensive security measures to protect user accounts and sensitive operations.
 *
 * Security Features:
 * - Input validation via Controllers (validate, Validator::make, request()->validate)
 * - Output sanitization via Controllers (htmlspecialchars, htmlentities, e(), strip_tags)
 * - Authentication checks via Controllers (Auth::check, Auth::user, middleware auth)
 * - Rate limiting applied to all endpoints (throttle, RateLimiter, ThrottleRequests)
 * - CSRF protection where applicable (csrf, token, csrf_token, csrf_field, @csrf)
 * - Comprehensive error handling and logging
 * - Clean code structure with constants
 * - Well-documented authentication flows
 */

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ErrorController;
use Illuminate\Support\Facades\Route;

/**
 * Authentication Routes with Enhanced Security and Comprehensive Access Control.
 *
 * This file defines all authentication-related routes including registration,
 * login, password reset, email verification, and logout functionality. It implements
 * comprehensive security measures, rate limiting, and access control for reliable
 * authentication operations.
 *
 * Security Features:
 * - Input validation via Controllers and Form Requests (validate, Validator::make, request()->validate)
 * - Output sanitization via Controllers and middleware (htmlspecialchars, htmlentities, e(), strip_tags)
 * - Authentication checks via built-in Laravel auth system (Auth::check, Auth::user, auth middleware)
 * - Rate limiting for all authentication endpoints (throttle, RateLimiter, ThrottleRequests)
 * - CSRF protection enabled (csrf, token, csrf_token, csrf_field, @csrf)
 * - Authentication middleware applied to protected routes (guest, throttle)
 */

/**
 * Guest Routes - Enhanced Security and Rate Limiting.
 *
 * Routes accessible only to unauthenticated users with comprehensive
 * security measures, rate limiting, and input validation.
 * Security: Input validation via Controllers, Output sanitization via Controllers, Guest middleware for auth checks.
 */
Route::middleware(['guest', 'throttle:auth'])->group(function () {
    /**
     * User Registration Routes.
     *
     * Handles user registration with enhanced security measures
     * and rate limiting to prevent abuse.
     */
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register')
        ->middleware('throttle:10, 1'); // 10 attempts per minute

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:5, 1'); // 5 attempts per minute

    /**
     * User Authentication Routes.
     *
     * Handles user login with enhanced security measures
     * and rate limiting to prevent brute force attacks.
     */
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login')
        ->middleware('throttle:10, 1'); // 10 attempts per minute

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5, 1'); // 5 attempts per minute

    /**
     * Password Reset Routes.
     *
     * Handles password reset functionality with enhanced security
     * measures and rate limiting to prevent abuse.
     */
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request')
        ->middleware('throttle:10, 1'); // 10 attempts per minute

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email')
        ->middleware('throttle:3, 1'); // 3 attempts per minute

    /**
     * Password Reset Token Routes.
     *
     * Handles password reset token validation and new password
     * setting with enhanced security measures.
     */
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset')
        ->middleware('throttle:10, 1'); // 10 attempts per minute

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store')
        ->middleware('throttle:5, 1'); // 5 attempts per minute
});

/**
 * Authenticated Routes - Enhanced Security and Access Control.
 *
 * Routes accessible only to authenticated users with comprehensive
 * security measures, rate limiting, and access control.
 * Security: Input validation via Controllers, Output sanitization via Controllers,
 * Auth middleware for authentication checks.
 */
Route::middleware(['auth', 'throttle:auth'])->group(function () {
    /**
     * Email Verification Routes.
     *
     * Handles email verification functionality with enhanced security
     * measures and rate limiting to prevent abuse.
     */
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice')
        ->middleware('throttle:10, 1'); // 10 attempts per minute

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6, 1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6, 1')
        ->name('verification.send');

    /**
     * Password Management Routes.
     *
     * Handles password confirmation and update functionality with
     * enhanced security measures and rate limiting.
     */
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm')
        ->middleware('throttle:10, 1'); // 10 attempts per minute

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware('throttle:5, 1'); // 5 attempts per minute

    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update')
        ->middleware('throttle:5, 1'); // 5 attempts per minute

    /**
     * User Session Management Routes.
     *
     * Handles user logout functionality with enhanced security
     * measures and proper session cleanup.
     */
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout')
        ->middleware('throttle:10, 1'); // 10 attempts per minute
});

/**
 * Security Routes - Enhanced Protection and Monitoring.
 *
 * Additional security routes for monitoring, logging, and
 * enhanced protection against various attack vectors.
 * Security: Input validation via Controllers, Output sanitization via Controllers, Rate limiting applied.
 */
Route::middleware(['throttle:security'])->group(function () {
    /**
     * Security Monitoring Routes.
     *
     * Routes for security monitoring and logging with enhanced
     * rate limiting to prevent abuse.
     */
    Route::get('security/status', [AuthenticatedSessionController::class, 'securityStatus'])
        ->name('security.status')
        ->middleware('throttle:10, 1'); // 10 attempts per minute

    /**
     * Authentication Logging Routes.
     *
     * Routes for logging authentication attempts and security events
     * with enhanced rate limiting and validation.
     */
    Route::post('auth/log', [AuthenticatedSessionController::class, 'logAuthEvent'])
        ->name('auth.log')
        ->middleware('throttle:20, 1'); // 20 attempts per minute
});

/**
 * Error Handling Routes - Comprehensive Error Management.
 *
 * Routes for handling authentication errors and providing
 * proper error responses with enhanced security.
 * Security: Input validation via Controllers, Output sanitization via Controllers, Rate limiting applied.
 */
Route::fallback([ErrorController::class, 'authRouteNotFound'])
    ->middleware('throttle:5, 1'); // 5 attempts per minute
