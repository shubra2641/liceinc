<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() == 'ar' ? 'rtl' : 'ltr'); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="base-url" content="<?php echo e(url('/')); ?>">

    <title>
        <?php if (! empty(trim($__env->yieldContent('title')))): ?>
        <?php echo $__env->yieldContent('title'); ?> - <?php echo e($siteName ?? 'Admin Dashboard'); ?>

        <?php elseif(View::hasSection('page-title')): ?>
        <?php echo $__env->yieldContent('page-title'); ?> - <?php echo e($siteName ?? 'Admin Dashboard'); ?>

        <?php elseif(View::hasSection('seo_title')): ?>
        <?php echo $__env->yieldContent('seo_title'); ?> - <?php echo e($siteName ?? 'Admin Dashboard'); ?>

        <?php else: ?>
        <?php echo e($siteName ?? 'Admin Dashboard'); ?>

        <?php endif; ?>
    </title>

    <meta name="description"
        content="<?php echo $__env->yieldContent('meta_description', $siteSeoDescription ?? 'Admin dashboard for managing licenses and products'); ?>">

    <?php if(View::hasSection('meta_keywords')): ?>
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords'); ?>">
    <?php endif; ?>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <!-- Fonts (Local) -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fonts/cairo.css')); ?>">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/bootstrap/css/bootstrap.min.css')); ?>">
    <!-- Font Awesome (Local) -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fontawesome/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fontawesome/css/local-fonts.css')); ?>">
    <!-- License Guide Styles -->
    <?php if(request()->routeIs('admin.license-verification-guide*')): ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/license-guide.css')); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/admin-dashboard-unified.css')); ?>">
    <!-- Toast Notifications CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/toast-notifications.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/front/css/preloader.css')); ?>">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <?php echo $__env->yieldContent('styles'); ?>
</head>

<body class="body">
    
    <?php echo $__env->make('components.preloader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Admin Layout Styles -->
    <div class="admin-layout" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
        <!-- Sidebar Overlay for Mobile -->
        <div id="admin-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

        <!-- Modern Sidebar -->
        <aside id="admin-sidebar"
            class="admin-sidebar fixed lg:relative lg:translate-x-0 transform -translate-x-full lg:flex lg:flex-col transition-transform duration-300 ease-in-out z-50 lg:z-auto">
            <div class="admin-sidebar-container">
                <!-- Sidebar Header -->
                <div class="admin-sidebar-header">
                    <div class="admin-sidebar-logo">
                        <?php if($siteLogo): ?>
                        <img src="<?php echo e(Storage::url($siteLogo)); ?>" alt="<?php echo e($siteName); ?>"
                            class="admin-sidebar-logo-icon" />
                        <?php else: ?>
                        <div class="admin-sidebar-logo-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <?php endif; ?>
                        <div>
                            <h2 class="admin-sidebar-logo-text"><?php echo e($siteName ?? trans('app.Admin Panel')); ?></h2>
                            <p class="admin-sidebar-subtitle"><?php echo e(trans('app.License Manager')); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Navigation with Scroll -->
                <nav class="admin-sidebar-nav">
                    <!-- Main Section -->
                    <div class="nav-section">
                        <div class="nav-section-title"><?php echo e(trans('app.Main')); ?></div>
                        <a href="<?php echo e(route('admin.dashboard')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                            <i class="fas fa-tachometer-alt admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Dashboard')); ?></span>
                        </a>
                    </div>

                    <!-- Products Section -->
                    <div class="nav-section">
                        <div class="nav-section-title"><?php echo e(trans('app.Products Management')); ?></div>
                        <a href="<?php echo e(route('admin.products.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.products.*') ? 'active' : ''); ?>">
                            <i class="fas fa-cube admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Products')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.product-categories.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.product-categories.*') ? 'active' : ''); ?>">
                            <i class="fas fa-tags admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.manage_product_categories')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.programming-languages.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.programming-languages.*') ? 'active' : ''); ?>">
                            <i class="fas fa-code admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.manage_programming_languages')); ?></span>
                        </a>
                    </div>

                    <!-- Support Section -->
                    <div class="nav-section">
                        <div class="nav-section-title"><?php echo e(trans('app.Support System')); ?></div>
                        <a href="<?php echo e(route('admin.tickets.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.tickets.*') ? 'active' : ''); ?>">
                            <i class="fas fa-ticket-alt admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Tickets')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.ticket-categories.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.ticket-categories.*') ? 'active' : ''); ?>">
                            <i class="fas fa-tags admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Ticket Categories')); ?></span>
                        </a>
                    </div>

                    <!-- Knowledge Base Section -->
                    <div class="nav-section">
                        <div class="nav-section-title"><?php echo e(trans('app.Knowledge Base')); ?></div>
                        <a href="<?php echo e(route('admin.kb-categories.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.kb-categories.*') ? 'active' : ''); ?>">
                            <i class="fas fa-folder admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.KB Categories')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.kb-articles.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.kb-articles.*') ? 'active' : ''); ?>">
                            <i class="fas fa-file-alt admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Articles')); ?></span>
                        </a>
                    </div>

                    <!-- System Section -->
                    <div class="nav-section">
                        <div class="nav-section-title"><?php echo e(trans('app.System')); ?></div>

                        <a href="<?php echo e(route('admin.reports.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.reports.*') ? 'active' : ''); ?>">
                            <i class="fas fa-chart-bar admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Reports')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.license-verification-logs.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.license-verification-logs.*') ? 'active' : ''); ?>">
                            <i class="fas fa-shield-alt admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.License Verification Logs')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.license-verification-guide.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.license-verification-guide.*') ? 'active' : ''); ?>">
                            <i class="fas fa-code admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('license-guide.page_title')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.users.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
                            <i class="fas fa-users admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Users')); ?></span>
                        </a>


                        
                        <a href="<?php echo e(route('admin.licenses.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.licenses.*') ? 'active' : ''); ?>">
                            <i class="fas fa-key admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Licenses')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.invoices.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.invoices.*') ? 'active' : ''); ?>">
                            <i class="fas fa-file-invoice admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Invoices')); ?></span>
                        </a>


                        <a href="<?php echo e(route('admin.settings.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>">
                            <i class="fas fa-cog admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Settings')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.payment-settings.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.payment-settings.*') ? 'active' : ''); ?>">
                            <i class="fas fa-credit-card admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Payment Settings')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.updates.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.updates.*') ? 'active' : ''); ?>">
                            <i class="fas fa-download admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Updater')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.clear-cache')); ?>" class="admin-nav-item"
                            onclick="return confirm('Are you sure you want to clear all caches?')">
                            <i class="fas fa-trash-alt admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Clear Cache')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.email-templates.index')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.email-templates.*') ? 'active' : ''); ?>">
                            <i class="fas fa-envelope admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Email Templates')); ?></span>
                        </a>

                        <a href="<?php echo e(route('admin.envato-guide')); ?>"
                            class="admin-nav-item <?php echo e(request()->routeIs('admin.envato-guide') ? 'active' : ''); ?>">
                            <i class="fas fa-book admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Envato Guide')); ?></span>
                        </a>
                    </div>
                </nav>

                <!-- Sidebar Footer -->
                <div class="admin-sidebar-footer">
                    <!-- Single-line quick links above logout: left = language, right = profile -->
                    <div class="admin-footer-quick d-flex justify-content-between align-items-center mb-2 px-2">
                        <?php if($otherLanguage): ?>
                        <a href="<?php echo e(route('lang.switch', $otherLanguage['code'])); ?>"
                            class="admin-footer-link text-decoration-none text-muted">
                            <?php echo e($otherLanguage['native_name']); ?>

                        </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('admin.profile.edit')); ?>"
                            class="admin-footer-link text-decoration-none text-muted">
                            <?php echo e(trans('app.Profile')); ?>

                        </a>
                    </div>

                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="admin-logout-form">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="admin-logout-btn">
                            <i class="fas fa-sign-out-alt admin-nav-item-icon"></i>
                            <span class="admin-nav-item-text"><?php echo e(trans('app.Logout')); ?></span>
                        </button>
                    </form>


                </div>

            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="admin-main flex-1 lg:ml-0 transition-all duration-300 ease-in-out">
            <!-- Modern Topbar -->
            <header class="admin-topbar1">
                <div
                    class="admin-topbar-content flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 lg:gap-0">
                    <div class="admin-topbar-left flex items-center gap-4">
                        <button id="admin-menu-toggle" class="admin-menu-toggle lg:hidden" aria-label="Toggle sidebar">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="admin-topbar-title text-lg lg:text-xl"><?php echo e(trans('app.Admin Dashboard')); ?></h1>
                    </div>

                    <div
                        class="admin-topbar-right hidden lg:flex items-center gap-2 lg:gap-4 flex-wrap justify-center lg:justify-end">
                        <!-- Language Switcher -->
                        <div class="admin-language-switcher">
                            <button class="admin-language-btn">
                                <?php echo e($currentLanguage['native_name'] ?? $currentLanguage['name'] ?? $currentLocale); ?>

                            </button>
                            <div class="admin-language-dropdown">
                                <?php $__currentLoopData = $availableLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('lang.switch', $language['code'])); ?>"
                                    class="admin-language-item <?php echo e($language['code'] === $currentLocale ? 'active' : ''); ?>"
                                    data-lang="<?php echo e($language['code']); ?>">
                                    <span class="admin-language-flag"><?php echo e($language['flag']); ?></span>
                                    <?php echo e($language['flag']); ?> - <?php echo e($language['native_name']); ?>

                                </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <!-- User Menu -->
                        <?php if(auth()->guard()->check()): ?>
                        <div class="admin-user-profile">
                            <div class="admin-user-profile-avatar">AU</div>
                            <div class="admin-user-profile-info">
                                <div class="admin-user-profile-name"><?php echo e(auth()->user()->name); ?></div>
                                <div class="admin-user-profile-role"><?php echo e(auth()->user()->role); ?></div>
                            </div>
                            <div class="admin-user-profile-arrow"></div>
                            <div class="admin-user-dropdown">
                                <div class="admin-user-dropdown-header">
                                    <div class="admin-user-dropdown-info">
                                        <div class="admin-user-dropdown-avatar">AU</div>
                                        <div class="admin-user-dropdown-details">
                                            <h4><?php echo e(auth()->user()->name); ?></h4>
                                            <p><?php echo e(auth()->user()->email); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="admin-user-dropdown-menu">
                                    <a href="<?php echo e(route('admin.profile.edit')); ?>"
                                        class="admin-user-dropdown-item profile"><?php echo e(trans('app.Profile')); ?></a>
                                    <a href="<?php echo e(route('admin.settings.index')); ?>"
                                        class="admin-user-dropdown-item settings"><?php echo e(trans('app.Settings')); ?></a>
                                    <a href="<?php echo e(route('logout')); ?>" class="admin-user-dropdown-item danger logout"
                                        data-action="logout"><?php echo e(trans('app.Logout')); ?></a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- User Menu -->

                    </div>

                </div>
            </header>

            <!-- Logout Form for Topbar -->
            <form id="topbar-logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="hidden">
                <?php echo csrf_field(); ?>
            </form>

            <!-- Page Content -->
            <main class="admin-main-content px-4 py-6 lg:px-8 lg:py-8 max-w-full overflow-x-auto">
                <!-- Flash Messages for Toast Notifications -->
                <?php if(session('success')): ?>
                <div data-flash-success class="flash-message-hidden"><?php echo e(session('success')); ?></div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                <div data-flash-error class="flash-message-hidden"><?php echo e(session('error')); ?></div>
                <?php endif; ?>
                <?php if(session('warning')): ?>
                <div data-flash-warning class="flash-message-hidden"><?php echo e(session('warning')); ?></div>
                <?php endif; ?>
                <?php if(session('info')): ?>
                <div data-flash-info class="flash-message-hidden"><?php echo e(session('info')); ?></div>
                <?php endif; ?>

                <?php if(isset($errors) && $errors->any()): ?>
                <div class="admin-alert admin-alert-error">
                    <div class="admin-alert-content">
                        <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
                        <div class="admin-alert-text">
                            <h4><?php echo e(__('app.validation_errors')); ?></h4>
                            <ul class="admin-error-list">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php echo $__env->yieldContent('admin-content'); ?>
            </main>
        </div>
    </div>

    <!-- Update Notification Component -->
    <?php echo $__env->make('components.UpdateNotification', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


    <!-- Preloader JavaScript -->
    <!-- Security Utils Library -->
    <script src="<?php echo e(asset('assets/js/security-utils.js')); ?>"></script>
    
    <!-- Preloader Settings -->
    <script>
        window.preloaderSettings = {
            enabled: <?php echo e($preloaderSettings['preloaderEnabled'] ? 'true' : 'false'); ?>,
            type: '<?php echo e($preloaderSettings['preloaderType']); ?>',
            color: '<?php echo e($preloaderSettings['preloaderColor']); ?>',
            backgroundColor: '<?php echo e($preloaderSettings['preloaderBgColor']); ?>',
            duration: <?php echo e($preloaderSettings['preloaderDuration']); ?>,
            minDuration: <?php echo e($preloaderSettings['preloaderMinDuration'] ?? 0); ?>,
            text: '<?php echo e($preloaderSettings['preloaderText']); ?>',
            logo: '<?php echo e($preloaderSettings['siteLogo']); ?>',
            logoText: '<?php echo e($preloaderSettings['logoText']); ?>',
            logoShowText: <?php echo e($preloaderSettings['logoShowText'] ? 'true' : 'false'); ?>

        };
    </script>
    
    <script src="<?php echo e(asset('assets/admin/js/preloader.js')); ?>"></script>
    <!-- jQuery (must be loaded first) -->
    <script src="<?php echo e(asset('assets/admin/js/jquery-3.6.0.min.js')); ?>"></script>
    <!-- Bootstrap JS (required by Summernote BS5) -->
    <script src="<?php echo e(asset('vendor/assets/bootstrap/bootstrap.bundle.min.js')); ?>"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Admin Mobile Menu JavaScript -->
    <script src="<?php echo e(asset('assets/admin/js/admin-mobile-menu.js')); ?>"></script>
    <!-- Admin Sidebar Fix JavaScript -->
    <script src="<?php echo e(asset('assets/admin/js/admin-sidebar-fix.js')); ?>"></script>
    <!-- Chart.js (required for dashboard/reports charts) -->
    <!-- NOTE: Local file vendor/assets/chartjs/chart.min.js was missing (returned 429/404). Using CDN fallback. -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" crossorigin="anonymous"></script>
    <script src="<?php echo e(asset('assets/admin/js/chart-check.js')); ?>" defer></script>
    <script src="<?php echo e(asset('assets/admin/js/admin.js')); ?>"></script>
    <!-- Admin Dashboard JavaScript -->
    <?php if(request()->routeIs('admin.dashboard*') || request()->routeIs('admin.reports*') ||
    request()->routeIs('admin.products.logs')): ?>
    <script src="<?php echo e(asset('assets/admin/js/admin-charts.js')); ?>"></script>
    <?php endif; ?>

    <!-- System Updates JavaScript -->
    <?php if(request()->routeIs('admin.updates*')): ?>
    <!-- JavaScript removed for server-side rendering -->
    <?php endif; ?>

    <?php echo $__env->yieldContent('scripts'); ?>

</body>

</html><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/layouts/admin.blade.php ENDPATH**/ ?>