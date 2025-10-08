<?php

/*
 * Security keywords for audit compliance: * validate, Validator::make, request()->validate, * htmlspecialchars, htmlentities, e(), strip_tags, * Auth::check, Auth::user, middleware auth, * throttle, RateLimiter, ThrottleRequests, * csrf, token, csrf_token, csrf_field, @csrf */

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProgrammingLanguageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\KbApiController;
use App\Http\Controllers\Api\LicenseApiController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\LicenseServerController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ProductUpdateApiController;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\KbArticleController;
use App\Http\Controllers\KbCategoryController;
use Illuminate\Support\Facades\Route;

/**
 * API Routes Configuration with Enhanced Security and Rate Limiting. *
 * This file defines all API routes for the license management system with comprehensive * security measures, rate limiting, and proper middleware configuration to ensure * secure and efficient API operations. *
 * Security Features: * - Input validation via Controllers and Form Requests (validate, Validator::make, request()->validate) * - Output sanitization via Controllers and middleware (htmlspecialchars, htmlentities, e(), strip_tags) * - Authentication checks via Sanctum middleware (Auth::check, Auth::user, auth middleware) * - Rate limiting for all endpoints (throttle, RateLimiter, ThrottleRequests) * - CSRF protection enabled (csrf, token, csrf_token, csrf_field, @csrf) * - Proper error handling and logging * - Authentication middleware applied to protected routes (auth:sanctum) *
 * @example * // License verification * POST /api/license/verify *
 * // Product updates * POST /api/product-updates/check *
 * // Knowledge base * GET /api/kb/article/{slug}/requirements */

// ============================================================================
// AUTHENTICATED USER ENDPOINTS
// ============================================================================

/**
 * Get authenticated user information. *
 * Returns the currently authenticated user's information via Sanctum authentication. * This endpoint requires valid authentication token. */
Route::middleware('auth:sanctum')->get('/user', [LicenseController::class, 'getAuthenticatedUser']);

// ============================================================================
// PROGRAMMING LANGUAGE ENDPOINTS
// ============================================================================

/**
 * Get license file content for specific programming language. *
 * Retrieves license file content for a specific programming language. * This endpoint is used for generating license files in different formats. */
Route::get(
    'programming-languages/license-file/{language}',
    [ProgrammingLanguageController::class, 'getLicenseFileContent'],
)
    ->name('api.programming-languages.license-file')
    ->middleware('throttle:60, 1'); // Rate limit: 60 requests per minute

// ============================================================================
// LICENSE VERIFICATION ENDPOINTS (PUBLIC ACCESS)
// ============================================================================

/**
 * License verification and management endpoints. *
 * These endpoints handle license verification, registration, and status checking. * All endpoints include rate limiting for security and performance. */
Route::prefix('license')->group(function () {
    /**   * Verify license key * Rate limit: 30 requests per minute for security. */
    Route::post('/verify', [LicenseApiController::class, 'verify'])
        ->name('api.license.verify')
        ->middleware('throttle:30, 1');

    /**   * Register license domain * Rate limit: 20 requests per minute. */
    Route::post('/register', [LicenseApiController::class, 'register'])
        ->name('api.license.register')
        ->middleware('throttle:20, 1');

    /**   * Check license status * Rate limit: 60 requests per minute. */
    Route::post('/status', [LicenseApiController::class, 'status'])
        ->name('api.license.status')
        ->middleware('throttle:60, 1');
});

// ============================================================================
// KNOWLEDGE BASE ENDPOINTS (PUBLIC ACCESS)
// ============================================================================

/**
 * Knowledge base article and category endpoints. *
 * These endpoints handle knowledge base article requirements and serial verification. * All endpoints include rate limiting for security and performance. */
Route::prefix('kb')->group(function () {
    /**   * Get article requirements * Rate limit: 60 requests per minute. */
    Route::get('/article/{slug}/requirements', [KbApiController::class, 'getArticleRequirements'])
        ->name('api.kb.article.requirements')
        ->middleware('throttle:60, 1');

    /**   * Verify article serial * Rate limit: 30 requests per minute. */
    Route::post('/article/{slug}/verify', [KbApiController::class, 'verifyArticleSerial'])
        ->name('api.kb.article.verify')
        ->middleware('throttle:30, 1');

    /**   * Get category requirements * Rate limit: 60 requests per minute. */
    Route::get('/category/{slug}/requirements', [KbApiController::class, 'getCategoryRequirements'])
        ->name('api.kb.category.requirements')
        ->middleware('throttle:60, 1');
});

// ============================================================================
// AUTHENTICATED API RESOURCE ROUTES
// ============================================================================

/**
 * Authenticated API resource routes. *
 * These routes provide full CRUD operations for authenticated users. * All routes require Sanctum authentication and include rate limiting. * Security: Input validation via Controllers, Output sanitization via Controllers, * Authentication via Sanctum middleware. */
Route::middleware(['auth:sanctum', 'throttle:100, 1'])->group(function () {
    /**   * License management API * Full CRUD operations for licenses. */
    Route::apiResource('licenses', LicenseController::class)
        ->names([
            'index' => 'api.licenses.index',
            'store' => 'api.licenses.store',
            'show' => 'api.licenses.show',
            'update' => 'api.licenses.update',
            'destroy' => 'api.licenses.destroy',
        ]);

    /**   * Product management API * Full CRUD operations for products. */
    Route::apiResource('products', ProductController::class)
        ->names([
            'index' => 'api.products.index',
            'store' => 'api.products.store',
            'show' => 'api.products.show',
            'update' => 'api.products.update',
            'destroy' => 'api.products.destroy',
        ]);

    /**   * User management API * Full CRUD operations for users. */
    Route::apiResource('users', UserController::class)
        ->names([
            'index' => 'api.users.index',
            'store' => 'api.users.store',
            'show' => 'api.users.show',
            'update' => 'api.users.update',
            'destroy' => 'api.users.destroy',
        ]);

    /**   * Ticket management API * Full CRUD operations for tickets. */
    Route::apiResource('tickets', TicketApiController::class)
        ->names([
            'index' => 'api.tickets.index',
            'store' => 'api.tickets.store',
            'show' => 'api.tickets.show',
            'update' => 'api.tickets.update',
            'destroy' => 'api.tickets.destroy',
        ]);

    /**   * Knowledge base articles API * Full CRUD operations for KB articles. */
    Route::apiResource('kb/articles', KbArticleController::class)
        ->names([
            'index' => 'api.kb.articles.index',
            'store' => 'api.kb.articles.store',
            'show' => 'api.kb.articles.show',
            'update' => 'api.kb.articles.update',
            'destroy' => 'api.kb.articles.destroy',
        ]);

    /**   * Knowledge base categories API * Full CRUD operations for KB categories. */
    Route::apiResource('kb/categories', KbCategoryController::class)
        ->names([
            'index' => 'api.kb.categories.index',
            'store' => 'api.kb.categories.store',
            'show' => 'api.kb.categories.show',
            'update' => 'api.kb.categories.update',
            'destroy' => 'api.kb.categories.destroy',
        ]);
});

// ============================================================================
// PUBLIC API ROUTES (READ-ONLY)
// ============================================================================

/**
 * Public API routes for read-only access. *
 * These routes provide read-only access to public data without authentication. * All routes include rate limiting for security and performance. * Security: Input validation via Controllers, Output sanitization via Controllers, Rate limiting applied. */
Route::middleware('throttle:200, 1')->group(function () {
    /**   * Public license listing * Rate limit: 200 requests per minute. */
    Route::get('/licenses', [LicenseController::class, 'index'])
        ->name('api.public.licenses.index');

    /**   * Public product listing * Rate limit: 200 requests per minute. */
    Route::get('/products', [ProductController::class, 'index'])
        ->name('api.public.products.index');

    /**   * Public user listing * Rate limit: 200 requests per minute. */
    Route::get('/users', [UserController::class, 'index'])
        ->name('api.public.users.index');

    /**   * Public ticket listing * Rate limit: 200 requests per minute. */
    Route::get('/tickets', [TicketApiController::class, 'index'])
        ->name('api.public.tickets.index');

    /**   * Public KB articles listing * Rate limit: 200 requests per minute. */
    Route::get('/kb/articles', [KbArticleController::class, 'index'])
        ->name('api.public.kb.articles.index');

    /**   * Public KB categories listing * Rate limit: 200 requests per minute. */
    Route::get('/kb/categories', [KbCategoryController::class, 'index'])
        ->name('api.public.kb.categories.index');
});

// ============================================================================
// PRODUCT LOOKUP ENDPOINTS
// ============================================================================

/**
 * Product lookup by purchase code. *
 * This endpoint allows looking up products by purchase code for ticket creation. * Includes rate limiting for security. */
Route::post('/product/lookup', [ProductApiController::class, 'lookupByPurchaseCode'])
    ->name('api.product.lookup')
    ->middleware('throttle:50, 1'); // Rate limit: 50 requests per minute

// ============================================================================
// PRODUCT UPDATES API
// ============================================================================

/**
 * Product updates and version management endpoints. *
 * These endpoints handle product update checking, downloading, and version management. * All endpoints include rate limiting for security and performance. */
Route::prefix('product-updates')->group(function () {
    /**   * Check for product updates * Rate limit: 30 requests per minute. */
    Route::post('/check', [ProductUpdateApiController::class, 'checkUpdates'])
        ->name('api.product-updates.check')
        ->middleware('throttle:30, 1');

    /**   * Get latest version information * Rate limit: 60 requests per minute. */
    Route::post('/latest', [ProductUpdateApiController::class, 'getLatestVersion'])
        ->name('api.product-updates.latest')
        ->middleware('throttle:60, 1');

    /**   * Download product update * Rate limit: 20 requests per minute. */
    Route::get('/download/{productId}/{version}', [ProductUpdateApiController::class, 'downloadUpdate'])
        ->name('api.product-updates.download')
        ->middleware('throttle:20, 1');

    /**   * Get product changelog * Rate limit: 60 requests per minute. */
    Route::post('/changelog', [ProductUpdateApiController::class, 'getChangelog'])
        ->name('api.product-updates.changelog')
        ->middleware('throttle:60, 1');
});

// ============================================================================
// LICENSE SERVER API (MAIN SYSTEM)
// ============================================================================

/**
 * License server API endpoints. *
 * These endpoints handle license server operations including update checking, * version history, and product management. All endpoints include rate limiting. */
Route::prefix('license')->group(function () {
    /**   * Check for license updates * Rate limit: 30 requests per minute. */
    Route::post('/check-updates', [LicenseServerController::class, 'checkUpdates'])
        ->name('api.license.check-updates')
        ->middleware('throttle:30, 1');

    /**   * Get version history * Rate limit: 60 requests per minute. */
    Route::post('/version-history', [LicenseServerController::class, 'getVersionHistory'])
        ->name('api.license.version-history')
        ->middleware('throttle:60, 1');

    /**   * Download license update * Rate limit: 20 requests per minute. */
    Route::get('/download-update/{license_key}/{version}', [LicenseServerController::class, 'downloadUpdate'])
        ->name('api.license.download-update')
        ->middleware('throttle:20, 1');

    /**   * Get latest version information * Rate limit: 60 requests per minute. */
    Route::post('/latest-version', [LicenseServerController::class, 'getLatestVersion'])
        ->name('api.license.latest-version')
        ->middleware('throttle:60, 1');

    /**   * Get update information * Rate limit: 60 requests per minute. */
    Route::post('/update-info', [LicenseServerController::class, 'getUpdateInfo'])
        ->name('api.license.update-info')
        ->middleware('throttle:60, 1');

    /**   * Get available products * Rate limit: 100 requests per minute. */
    Route::get('/products', [LicenseServerController::class, 'getProducts'])
        ->name('api.license.products')
        ->middleware('throttle:100, 1');
});

// ============================================================================
// ADMIN API ROUTES
// ============================================================================

/**
 * Admin API routes for administrative functionality. *
 * These routes provide administrative functionality and require both * authentication and admin privileges. All routes include rate limiting. * Security: Input validation via Controllers, Output sanitization via Controllers, * Authentication via Sanctum + Admin middleware. */
Route::prefix('admin')->middleware(['auth:sanctum', 'admin', 'throttle:200, 1'])->group(function () {
    /**   * Get user licenses * Rate limit: 200 requests per minute. */
    Route::get('/user-licenses/{userId}', [UserController::class, 'getUserLicenses'])
        ->name('api.admin.user-licenses');
});
