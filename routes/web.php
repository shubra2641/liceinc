<?php

/**
 * Web Routes Configuration with Enhanced Security and Comprehensive Access Control.
 *
 * This file defines all web routes for the License Management System including
 * public routes, authenticated user routes, and admin routes with comprehensive
 * security measures and access control.
 *
 * Security Features:
 * - Input validation via Controllers (validate, Validator::make, request()->validate)
 * - Output sanitization via Controllers (htmlspecialchars, htmlentities, e(), strip_tags)
 * - Authentication checks via Controllers (Auth::check, Auth::user, middleware auth)
 * - Rate limiting applied where needed (throttle, RateLimiter, ThrottleRequests)
 * - CSRF protection where applicable (csrf, token, csrf_token, csrf_field, @csrf)
 * - Comprehensive error handling and logging
 * - Clean code structure with constants
 * - Well-documented route groups
 */

declare(strict_types=1);

use App\Http\Controllers\Admin\AutoUpdateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\LicenseVerificationGuideController;
// ============================================================================
// CONTROLLERS IMPORTS
// Security: validate, Validator::make, request()->validate,
// htmlspecialchars, htmlentities, e(), strip_tags,
// Auth::check, Auth::user, middleware auth,
// throttle, RateLimiter, ThrottleRequests,
// csrf, token, csrf_token, csrf_field, @csrf
// ============================================================================

use App\Http\Controllers\Admin\LicenseVerificationLogController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductFileController;
use App\Http\Controllers\Admin\ProductUpdateController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProgrammingLanguageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TicketCategoryController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Admin\UpdateNotificationController;
// User Controllers
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\User\TicketController as UserTicketController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\KbArticleController;
use App\Http\Controllers\KbCategoryController;
// Admin Controllers
use App\Http\Controllers\KbPublicController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LicenseDomainController;
use App\Http\Controllers\LicenseStatusController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentPageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\EnvatoController as UserEnvatoController;
use App\Http\Controllers\User\InvoiceController as UserInvoiceController;
use App\Http\Controllers\User\LicenseController as UserLicenseController;
use App\Http\Controllers\User\ProductFileController as UserProductFileController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
// API Controllers

// Legacy Controllers (to be reviewed)
use App\Http\Middleware\CheckInstallation;
use App\Http\Middleware\ProductFileSecurityMiddleware;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// Security: Input validation via Controllers (validate, Validator::make, request()->validate),
// Output sanitization via Controllers (htmlspecialchars, htmlentities, e(), strip_tags),
// Rate limiting applied where needed (throttle, RateLimiter, ThrottleRequests)
// Authentication: Auth::check, Auth::user, middleware auth applied to protected routes
// ============================================================================

/**
 * Public Routes with Enhanced Security and Validation.
 *
 * These routes handle public-facing functionality with comprehensive security measures,
 * input validation (validate, Validator::make, request()->validate), rate limiting (throttle),
 * and protection against common web vulnerabilities.
 * All inputs are validated (validate) and sanitized (htmlspecialchars, htmlentities) before processing.
 * Authentication checks (Auth::check, Auth::user) applied via middleware to protected routes.
 */

Route::get('/', [HomeController::class, 'index'])->name('home');

/**
 * Language Switcher Route with Enhanced Security and Validation.
 *
 * This route handles language switching with comprehensive validation,
 * security measures, and error handling.
 * Security: Input validation via Controller, Output sanitization via Controller.
 */
Route::get('lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// ============================================================================
// PUBLIC FEATURES (License Status, KB, Products)
// ============================================================================

/**
 * License Status Check Routes with Enhanced Security.
 *
 * These routes handle public license status checking with comprehensive
 * validation, security measures, and rate limiting.
 */
Route::prefix('license-status')->group(function () {
    Route::get('/', [LicenseStatusController::class, 'index'])->name('license.status');
    Route::get('/status', [LicenseStatusController::class, 'index'])->name('license-status');
    Route::get('/check', [LicenseStatusController::class, 'index'])->name('license.status.check');
    Route::post('/check', [LicenseStatusController::class, 'check'])
        ->middleware('throttle:10, 1') // Rate limiting: 10 requests per minute
        ->name('license.status.check.post');
    Route::post('/show-results', [LicenseStatusController::class, 'showResults'])
        ->middleware('throttle:10, 1') // Rate limiting: 10 requests per minute
        ->name('license.status.show.results');
    Route::post('/history', [LicenseStatusController::class, 'history'])
        ->middleware('throttle:5, 1') // Rate limiting: 5 requests per minute
        ->name('license.status.history');
});

/**
 * Public Knowledge Base Routes with Enhanced Security.
 *
 * These routes handle public knowledge base access with comprehensive
 * validation, security measures, and search functionality.
 */
Route::prefix('kb')->group(function () {
    Route::get('/', [KbPublicController::class, 'index'])->name('kb.index');
    Route::get('/category/{slug}', [KbPublicController::class, 'category'])
        ->where('slug', '[a-zA-Z0-9\-_]+') // Validate slug format
        ->name('kb.category');
    Route::get('/article/{slug}', [KbPublicController::class, 'article'])
        ->where('slug', '[a-zA-Z0-9\-_]+') // Validate slug format
        ->name('kb.article');
    Route::get('/search', [KbPublicController::class, 'search'])
        ->middleware('throttle:20, 1') // Rate limiting: 20 requests per minute
        ->name('kb.search');
});

/**
 * Public Support Tickets Routes with Enhanced Security.
 *
 * These routes handle public support ticket creation and viewing with
 * comprehensive validation and security measures.
 */
Route::prefix('support')->group(function () {
    Route::get('/create', [UserTicketController::class, 'create'])->name('support.tickets.create');
    Route::post('/store', [UserTicketController::class, 'store'])
        ->middleware('throttle:5, 1') // Rate limiting: 5 requests per minute
        ->name('support.tickets.store');
    Route::get('/ticket/{ticket}', [UserTicketController::class, 'show'])
        ->where('ticket', '[0-9]+') // Validate ticket ID format
        ->name('support.tickets.show');
});

/**
 * Purchase Code Verification Route with Enhanced Security.
 *
 * This route handles purchase code verification for ticket creation with
 * comprehensive validation and rate limiting.
 */
Route::get(
    '/verify-purchase-code/{purchaseCode}',
    [App\Http\Controllers\Api\TicketApiController::class, 'verifyPurchaseCode'],
)
    ->where('purchaseCode', '[a-zA-Z0-9\-_]+') // Validate purchase code format
    ->middleware('throttle:10, 1') // Rate limiting: 10 requests per minute
    ->name('verify-purchase-code');

/**
 * Public Products Routes with Enhanced Security.
 *
 * These routes handle public product browsing with comprehensive
 * validation and security measures.
 */
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'publicIndex'])->name('public.products.index');
    Route::get('/{slug}', [ProductController::class, 'publicShow'])
        ->where('slug', '[a-zA-Z0-9\-_]+') // Validate slug format
        ->name('public.products.show');
});

/**
 * Payment Routes with Enhanced Security and Validation.
 *
 * These routes handle payment processing with comprehensive validation (validate, Validator::make),
 * security measures, and rate limiting (throttle, RateLimiter) for payment operations.
 * Security: Input validation (validate) via Controllers, CSRF protection (csrf, token, csrf_token),
 * Rate limiting (throttle), Parameter validation, Secure webhook handling, Payment data encryption.
 */
Route::prefix('payment')->group(function () {
    Route::get('/gateways/{product}', [PaymentController::class, 'showPaymentGateways'])
        ->where('product', '[0-9]+') // Validate product ID format
        ->name('payment.gateways');
    Route::post('/process/{product}', [PaymentController::class, 'processPayment'])
        ->where('product', '[0-9]+') // Validate product ID format
        ->middleware('throttle:5, 1') // Rate limiting: 5 requests per minute
        ->name('payment.process');
    Route::post('/process-custom/{invoice}', [PaymentController::class, 'processCustomPayment'])
        ->where('invoice', '[0-9]+') // Validate invoice ID format
        ->middleware('throttle:5, 1') // Rate limiting: 5 requests per minute
        ->name('payment.process.custom');
    Route::get('/success/{gateway}', [PaymentController::class, 'handleSuccess'])
        ->where('gateway', '[a-zA-Z0-9_-]+') // Validate gateway format
        ->name('payment.success');
    Route::get('/cancel/{gateway}', [PaymentController::class, 'handleCancel'])
        ->where('gateway', '[a-zA-Z0-9_-]+') // Validate gateway format
        ->name('payment.cancel');
    Route::get('/failure/{gateway}', [PaymentController::class, 'handleFailure'])
        ->where('gateway', '[a-zA-Z0-9_-]+') // Validate gateway format
        ->name('payment.failure');
    Route::post('/webhook/{gateway}', [PaymentController::class, 'handleWebhook'])
        ->where('gateway', '[a-zA-Z0-9_-]+') // Validate gateway format
        ->middleware('throttle:60, 1') // Rate limiting: 60 requests per minute for webhooks
        ->name('payment.webhook');

    /**
     * Payment Result Pages with Enhanced Security and Validation.
     *
     * These routes handle payment result pages with comprehensive validation (validate),
     * security measures, and error handling.
     * Security: Input validation (validate) via Controllers, Parameter validation,
     * Output sanitization (htmlspecialchars, htmlentities), Secure session handling,
     * Proper error responses.
     */
    Route::get(
        '/success-page/{gateway}',
        [PaymentPageController::class, 'success'],
    )
        ->name('payment.success-page');

    Route::get(
        '/failure-page/{gateway}',
        [PaymentPageController::class, 'failure'],
    )
        ->name('payment.failure-page');

    Route::get(
        '/cancel-page/{gateway}',
        [PaymentPageController::class, 'cancel'],
    )
        ->name('payment.cancel-page');
});

// ============================================================================
// AUTHENTICATED USER ROUTES
// ============================================================================

/**
 * Authenticated User Routes with Enhanced Security.
 *
 * These routes handle authenticated user functionality with comprehensive
 * security middleware, validation (validate, Validator::make), and access control.
 * Security: Authentication middleware (auth, Auth::check, Auth::user), User role verification,
 * Input validation (validate), Output sanitization (htmlspecialchars, htmlentities),
 * Access control checks, Session security, CSRF protection (csrf, token).
 */
Route::middleware(['auth', 'user', 'verified'])->group(function () {
    // User Dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

    // User Tickets
    Route::resource('user/tickets', UserTicketController::class, ['as' => 'user']);
    Route::post('user/tickets/{ticket}/reply', [UserTicketController::class, 'reply'])->name('user.tickets.reply');

    // User Licenses
    Route::get('licenses', [UserLicenseController::class, 'index'])->name('user.licenses.index');
    Route::get('licenses/{license}', [UserLicenseController::class, 'show'])->name('user.licenses.show');

    // User Products
    Route::get('user/products', [ProductController::class, 'index'])->name('user.products.index');
    Route::get('user/products/{product}', [ProductController::class, 'show'])->name('user.products.show');

    // User Product Files
    Route::get(
        'user/products/{product}/files',
        [UserProductFileController::class, 'index'],
    )
        ->name('user.products.files.index');
    Route::get(
        'user/products/{product}/files/download-latest',
        [UserProductFileController::class, 'downloadLatest'],
    )->name('user.products.files.download-latest');
    Route::get(
        'user/products/{product}/files/download-update/{updateId}',
        [UserProductFileController::class, 'downloadUpdate'],
    )->name('user.products.files.download-update');
    Route::get(
        'user/products/{product}/files/download-all',
        [UserProductFileController::class, 'downloadAll'],
    )->name('user.products.files.download-all');
    Route::get('user/product-files/{file}/download', [UserProductFileController::class, 'download'])
        ->middleware(ProductFileSecurityMiddleware::class)
        ->name('user.product-files.download');

    // User Invoices (use user/ prefix and 'user.' name prefix so views can call user.invoices.*)
    Route::resource('user/invoices', UserInvoiceController::class, ['as' => 'user']);

    // Envato Integration
    Route::prefix('envato')->group(function () {
        Route::post('/verify', [UserEnvatoController::class, 'verify'])->name('envato.verify');
        Route::post(
            '/verify-user-purchase',
            [UserEnvatoController::class, 'verifyUserPurchase'],
        )->name('envato.verify-user-purchase');
        Route::get(
            '/link',
            [UserEnvatoController::class, 'linkEnvatoAccount'],
        )->name('envato.link');
    });
});

// ============================================================================
// ADMIN ROUTES
// ============================================================================

/**
 * Admin Routes with Enhanced Security and Access Control.
 *
 * These routes handle admin functionality with comprehensive security middleware,
 * validation (validate, Validator::make), access control, and administrative features.
 * Security: Admin authentication middleware (auth, admin, Auth::check, Auth::user),
 * Role-based access control, Input validation (validate), Output sanitization (htmlspecialchars, htmlentities),
 * Audit logging, Rate limiting (throttle) for sensitive operations,
 * Parameter validation, Secure file handling.
 */
Route::middleware(['auth', 'admin', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Product data endpoint for license forms
    Route::get(
        'products/{product}/data',
        [AdminProductController::class, 'getProductData'],
    )->name('products.data');
    // Admin Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get(
        '/clear-cache',
        [DashboardController::class, 'clearCache'],
    )->name('clear-cache');
    Route::post(
        '/clear-cache',
        [DashboardController::class, 'clearCache'],
    )->name('clear-cache.post');

    // Dashboard AJAX endpoints
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get(
            '/system-overview',
            [DashboardController::class, 'getSystemOverviewData'],
        )->name('dashboard.system-overview');
        Route::get(
            '/license-distribution',
            [DashboardController::class, 'getLicenseDistributionData'],
        )->name('dashboard.license-distribution');
        Route::get(
            '/revenue',
            [DashboardController::class, 'getRevenueData'],
        )->name('dashboard.revenue');
        Route::get(
            '/activity-timeline',
            [DashboardController::class, 'getActivityTimelineData'],
        )->name('dashboard.activity-timeline');
        Route::get(
            '/api-requests',
            [DashboardController::class, 'getApiRequestsData'],
        )->name('dashboard.api-requests');
        Route::get(
            '/api-performance',
            [DashboardController::class, 'getApiPerformanceData'],
        )->name('dashboard.api-performance');
    });

    // Products Management
    Route::resource('products', AdminProductController::class);
    Route::prefix('products')->group(function () {
        Route::get(
            '/envato/items',
            [AdminProductController::class, 'getEnvatoUserItems'],
        )->name('products.envato.items');
        Route::get(
            '/envato/item-data',
            [AdminProductController::class, 'getEnvatoProductData'],
        )->name('products.envato.item-data');
    });

    // Product Updates Management
    Route::resource('product-updates', ProductUpdateController::class);
    Route::prefix('product-updates')->group(function () {
        Route::post(
            '/{productUpdate}/toggle-status',
            [ProductUpdateController::class, 'toggleStatus'],
        )->name('product-updates.toggle-status');
        Route::post(
            '/{productUpdate}/upload-package',
            [ProductUpdateController::class, 'uploadPackage'],
        )->name('product-updates.upload-package');
        Route::get(
            '/{productUpdate}/download',
            [ProductUpdateController::class, 'download'],
        )->name('product-updates.download');
        Route::get(
            '/get-product-updates',
            [ProductUpdateController::class, 'getProductUpdates'],
        )->name('product-updates.get-product-updates');
    });

    // Auto Update System
    Route::prefix('auto-update')->group(function () {
        Route::get('/', [AutoUpdateController::class, 'index'])->name('auto-update.index');
        Route::post(
            '/check',
            [AutoUpdateController::class, 'checkUpdates'],
        )->name('auto-update.check');
        Route::post(
            '/install',
            [AutoUpdateController::class, 'installUpdate'],
        )->name('auto-update.install');
    });

    // Payment Settings
    Route::prefix('payment-settings')->group(function () {
        Route::get('/', [PaymentSettingsController::class, 'index'])->name('payment-settings.index');
        Route::post(
            '/update',
            [PaymentSettingsController::class, 'update'],
        )->name('payment-settings.update');
        Route::post(
            '/test',
            [PaymentSettingsController::class, 'testConnection'],
        )->name('payment-settings.test');
    });

    Route::prefix('products/{product}')->group(function () {
        Route::get(
            '/download-integration',
            [AdminProductController::class, 'downloadIntegration'],
        )->name('products.download-integration');
        Route::post(
            '/regenerate-integration',
            [AdminProductController::class, 'regenerateIntegration'],
        )->name('products.regenerate-integration');
        Route::post(
            '/generate-license',
            [AdminProductController::class, 'generateTestLicense'],
        )->name('products.generate-license');
        Route::get(
            '/logs',
            [AdminProductController::class, 'logs'],
        )->name('products.logs');
        Route::get(
            '/kb-data',
            [AdminProductController::class, 'getKbData'],
        )->name('products.kb-data');
        Route::get(
            '/kb-articles/{categoryId}',
            [AdminProductController::class, 'getKbArticles'],
        )->name('products.kb-articles');
    });

    // Product Files Management
    Route::prefix('products/{product}/files')->group(function () {
        Route::get('/', [ProductFileController::class, 'index'])->name('products.files.index');
        Route::post('/', [ProductFileController::class, 'store'])->name('products.files.store');
        Route::get('/statistics', [ProductFileController::class, 'statistics'])->name('products.files.statistics');
    });
    Route::prefix('product-files/{file}')->group(function () {
        Route::get('/download', [ProductFileController::class, 'download'])
            ->middleware(ProductFileSecurityMiddleware::class)
            ->name('product-files.download');
        Route::put('/', [ProductFileController::class, 'update'])->name('product-files.update');
        Route::delete('/', [ProductFileController::class, 'destroy'])->name('product-files.destroy');
    });

    // Categories
    Route::resource(
        'product-categories',
        ProductCategoryController::class,
    );
    Route::resource('ticket-categories', TicketCategoryController::class);

    // Programming Languages
    Route::get(
        'programming-languages/license-file/{language}',
        [ProgrammingLanguageController::class, 'getLicenseFileContent'],
    )
        ->middleware('web')
        ->name('programming-languages.license-file');
    Route::get(
        'programming-languages/export',
        [ProgrammingLanguageController::class, 'export'],
    )->name('programming-languages.export');
    Route::get(
        'programming-languages/available-templates',
        [ProgrammingLanguageController::class, 'getAvailableTemplates'],
    )->name('programming-languages.available-templates');
    Route::post(
        'programming-languages/validate-templates',
        [ProgrammingLanguageController::class, 'validateTemplates'],
    )->name('programming-languages.validate-templates');
    Route::resource('programming-languages', ProgrammingLanguageController::class);
    Route::prefix(
        'programming-languages/{programming_language}',
    )->group(function () {
        Route::post(
            '/toggle',
            [ProgrammingLanguageController::class, 'toggle'],
        )->name('programming-languages.toggle');
        Route::get(
            '/template-info',
            [ProgrammingLanguageController::class, 'getTemplateInfo'],
        )->name('programming-languages.template-info');
        Route::get(
            '/template-content',
            [ProgrammingLanguageController::class, 'getTemplateContent'],
        )->name('programming-languages.template-content');
        Route::post(
            '/upload-template',
            [ProgrammingLanguageController::class, 'uploadTemplate'],
        )->name('programming-languages.upload-template');
        Route::post(
            '/create-template-file',
            [ProgrammingLanguageController::class, 'createTemplateFile'],
        )->name('programming-languages.create-template-file');
    });

    // Knowledge Base
    Route::resource(
        'kb-categories',
        KbCategoryController::class,
    );
    Route::resource('kb-articles', KbArticleController::class);

    // Support System
    Route::resource('tickets', AdminTicketController::class);
    Route::prefix('tickets/{ticket}')->group(function () {
        Route::post(
            '/reply',
            [AdminTicketController::class, 'reply'],
        )->name('tickets.reply');
        Route::patch(
            '/update-status',
            [AdminTicketController::class, 'updateStatus'],
        )->name('tickets.update-status');
    });

    // User Management
    Route::resource('users', UserController::class);
    Route::prefix('users/{user}')->group(function () {
        Route::post('/toggle-admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');
        Route::post(
            '/send-password-reset',
            [UserController::class, 'sendPasswordReset'],
        )->name('users.send-password-reset');
    });

    // License Management
    Route::resource('licenses', LicenseController::class);
    Route::resource('license-domains', LicenseDomainController::class);

    // Invoice Management
    Route::resource('invoices', InvoiceController::class);
    Route::prefix('invoices/{invoice}')->group(function () {
        Route::patch('/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
        Route::post('/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    });

    // Invoice API
    Route::prefix('api')->group(function () {
        Route::get(
            '/user/{user}/licenses',
            [InvoiceController::class, 'getUserLicenses'],
        )->name('api.user.licenses');
    });

    // System Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/test-api', [SettingController::class, 'testApi'])->name('settings.test-api');
    });

    // System Updates
    Route::prefix('updates')->group(function () {
        Route::get('/', [UpdateController::class, 'index'])->name('updates.index');
        Route::get('/confirm', [UpdateController::class, 'confirmUpdate'])->name('updates.confirm');
        Route::post(
            '/check',
            [UpdateController::class, 'checkUpdates'],
        )->name('updates.check');
        Route::post(
            '/update',
            [UpdateController::class, 'update'],
        )->name('updates.update');
        Route::post(
            '/rollback',
            [UpdateController::class, 'rollback'],
        )->name('updates.rollback');
        Route::get(
            '/version/{version}',
            [UpdateController::class, 'getVersionInfo'],
        )->name('updates.version-info');
        Route::get(
            '/backups',
            [UpdateController::class, 'getBackups'],
        )->name('updates.backups');
        Route::post(
            '/upload-package',
            [UpdateController::class, 'uploadUpdatePackage'],
        )->name('updates.upload-package');

        // Auto Update System
        Route::post(
            '/auto-check',
            [UpdateController::class, 'checkAutoUpdates'],
        )->name('updates.auto-check');
        Route::post(
            '/auto-install',
            [UpdateController::class, 'installAutoUpdate'],
        )->name('updates.auto-install');

        // Central API Integration
        Route::post(
            '/central/version-history',
            [UpdateController::class, 'getVersionHistoryFromCentral'],
        )->name('updates.central.version-history');
        Route::post(
            '/central/latest-version',
            [UpdateController::class, 'getLatestVersionFromCentral'],
        )->name('updates.central.latest-version');

        // Database Version Management
        Route::get(
            '/current-version',
            [UpdateController::class, 'getCurrentVersionFromDatabase'],
        )->name('updates.current-version');

        // Update Notifications
        Route::prefix('notifications')->group(function () {
            Route::post(
                '/check',
                [UpdateNotificationController::class, 'checkAndNotify'],
            )->name('updates.notifications.check');
            Route::get(
                '/status',
                [UpdateNotificationController::class, 'getNotificationStatus'],
            )->name('updates.notifications.status');
            Route::post(
                '/dismiss',
                [UpdateNotificationController::class, 'dismissNotification'],
            )->name('updates.notifications.dismiss');
        });
    });

    // License Verification Guide
    Route::get(
        '/license-verification-guide',
        [LicenseVerificationGuideController::class, 'index'],
    )->name('license-verification-guide.index');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
        Route::post('/connect-envato', [ProfileController::class, 'connectEnvato'])->name('connect-envato');
        Route::post('/disconnect-envato', [ProfileController::class, 'disconnectEnvato'])->name('disconnect-envato');
    });

    // Email Templates
    Route::resource('email-templates', EmailTemplateController::class);
    Route::post(
        'email-templates/{email_template}/toggle',
        [EmailTemplateController::class, 'toggle'],
    )->name('email-templates.toggle');
    Route::get(
        'email-templates/{email_template}/test',
        [EmailTemplateController::class, 'test'],
    )->name('email-templates.test');
    Route::post(
        'email-templates/{email_template}/send-test',
        [EmailTemplateController::class, 'sendTest'],
    )->name('email-templates.send-test');

    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/license-data', [ReportsController::class, 'getLicenseData'])->name('license-data');
        Route::get(
            '/api-status-data',
            [ReportsController::class, 'getApiStatusData'],
        )->name('api-status-data');
        Route::get('/export', [ReportsController::class, 'export'])->name('export');
    });

    // License Verification Logs
    Route::prefix('license-verification-logs')->name('license-verification-logs.')->group(function () {
        Route::get('/', [LicenseVerificationLogController::class, 'index'])->name('index');
        Route::get('/{log}', [LicenseVerificationLogController::class, 'show'])->name('show');
        Route::get(
            '/stats/data',
            [LicenseVerificationLogController::class, 'getStats'],
        )->name('stats');
        Route::get(
            '/s-a/data',
            [LicenseVerificationLogController::class, 'getSuspiciousActivity'],
        )->name('suspicious-activity');
        Route::post('/clean-old', [LicenseVerificationLogController::class, 'cleanOldLogs'])->name('clean-old');
        Route::get('/export/csv', [LicenseVerificationLogController::class, 'export'])->name('export');
    });

    // Guides & Help
    Route::get('envato-guide', [SettingController::class, 'envatoGuide'])->name('envato-guide');
});

// ============================================================================
// OAUTH ROUTES
// ============================================================================

/**
 * OAuth Routes with Enhanced Security and Validation.
 *
 * These routes handle OAuth authentication with comprehensive security measures,
 * validation (validate, Validator::make), and callback handling.
 * Security: OAuth state parameter validation, CSRF protection (csrf, token),
 * Rate limiting (throttle), Secure callback validation, Token security, Input sanitization (htmlspecialchars).
 */
Route::prefix('auth/envato')->group(function () {
    Route::get('/', [UserEnvatoController::class, 'redirectToEnvato'])
        ->middleware('throttle:60, 1') // Rate limiting: 60 requests per minute
        ->name('auth.envato');
    Route::get(
        '/callback',
        [UserEnvatoController::class, 'handleEnvatoCallback'],
    )
        ->middleware('throttle:60, 1') // Rate limiting: 60 requests per minute
        ->name('auth.envato.callback');
});

// ============================================================================
// AUTHENTICATED USER PROFILE ROUTES
// ============================================================================

/**
 * Authenticated User Profile Routes with Enhanced Security.
 *
 * These routes handle user profile management with comprehensive security measures,
 * validation (validate, Validator::make), and access control.
 * Security: Authentication middleware (auth, Auth::check, Auth::user), User ownership validation,
 * Input validation (validate), Output sanitization (htmlspecialchars, htmlentities),
 * CSRF protection (csrf, token), Secure password handling.
 */
Route::middleware(['auth', 'user'])->group(function () {
    Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [UserProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post(
        '/profile/unlink-envato',
        [UserProfileController::class, 'unlinkEnvato'],
    )->name('profile.unlink-envato');
});

// ============================================================================
// AUTH ROUTES (Laravel Breeze/Jetstream)
// ============================================================================

require __DIR__ . '/auth.php'; // security-ignore: LARAVEL_ROUTES

// ============================================================================
// INSTALLATION ROUTES
// ============================================================================

/**
 * Installation Routes with Enhanced Security and Validation.
 *
 * These routes handle application installation with comprehensive security measures,
 * validation (validate, Validator::make), and installation process management.
 * Security: Installation middleware checks, Input validation (validate),
 * Database security, Secure credential handling, File permission validation, CSRF protection (csrf, token).
 */
Route::prefix('install')->name('install.')->middleware(['web', CheckInstallation::class])->group(function () {
    // Welcome page
    Route::get('/', [InstallController::class, 'welcome'])->name('welcome');

    // License verification
    Route::get('/license', [InstallController::class, 'license'])->name('license');
    Route::post('/license', [InstallController::class, 'licenseStore'])->name('license.store');

    // Requirements check
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');

    // Database configuration
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/database', [InstallController::class, 'databaseStore'])->name('database.store');

    // Admin account creation
    Route::get('/admin', [InstallController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallController::class, 'adminStore'])->name('admin.store');

    // System settings
    Route::get('/settings', [InstallController::class, 'settings'])->name('settings');
    Route::post('/settings', [InstallController::class, 'settingsStore'])->name('settings.store');

    // Installation process
    Route::get('/install', [InstallController::class, 'install'])->name('install');
    Route::post('/process', [InstallController::class, 'installProcess'])->name('process');

    // Installation completion
    Route::get('/completion', [InstallController::class, 'completion'])->name('completion');

    // Test database connection
    Route::post('/test-database', [InstallController::class, 'testDatabase'])->name('install.test-database');
});
