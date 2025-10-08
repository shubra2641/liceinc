<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="base-url" content="<?php echo e(url('/')); ?>">
    
    <title>
        <?php if (! empty(trim($__env->yieldContent('title')))): ?>
        <?php echo $__env->yieldContent('title'); ?> - <?php echo e($siteName); ?>

        <?php elseif(View::hasSection('page-title')): ?>
        <?php echo $__env->yieldContent('page-title'); ?> - <?php echo e($siteName); ?>

        <?php elseif(View::hasSection('seo_title')): ?>
        <?php echo $__env->yieldContent('seo_title'); ?> - <?php echo e($siteName); ?>

        <?php elseif($siteSeoTitle): ?>
        <?php echo e($siteSeoTitle); ?> - <?php echo e($siteName); ?>

        <?php else: ?>
        <?php echo e($siteName); ?> - <?php echo e(trans('app.Dashboard')); ?>

        <?php endif; ?>
    </title>

    <meta name="description"
        content="<?php echo $__env->yieldContent('meta_description', $siteSeoDescription ?? trans('app.User dashboard for managing licenses and products')); ?>">
    
    <?php if(View::hasSection('meta_keywords')): ?>
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords'); ?>">
    <?php endif; ?>

    <!-- Responsive Viewport Meta Tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">

    <!-- Page Title and Subtitle -->
    
    <?php if(View::hasSection('og:title') || View::hasSection('page-title')): ?>
    <meta property="og:title"
        content="<?php echo $__env->yieldContent('og:title', View::hasSection('page-title') ? trim(strip_tags($__env->yieldContent('page-title'))) . ' - ' . $siteName : ''); ?>">
    <?php elseif($siteSeoTitle): ?>
    <meta property="og:title" content="<?php echo e($siteSeoTitle); ?> - <?php echo e($siteName); ?>">
    <?php endif; ?>

    <?php if(View::hasSection('og:description') || View::hasSection('page-subtitle')): ?>
    <meta property="og:description"
        content="<?php echo $__env->yieldContent('og:description', View::hasSection('page-subtitle') ? trim(strip_tags($__env->yieldContent('page-subtitle'))) : ''); ?>">
    <?php elseif($siteSeoDescription): ?>
    <meta property="og:description" content="<?php echo e($siteSeoDescription); ?>">
    <?php endif; ?>
    <!-- Font Awesome (Local) -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fontawesome/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fontawesome/css/local-fonts.css')); ?>">
    <?php if(View::hasSection('og:image')): ?>
    <meta property="og:image" content="<?php echo $__env->yieldContent('og:image'); ?>">
    <?php elseif($ogImage): ?>
    <meta property="og:image" content="<?php echo e(asset('storage/' . $ogImage)); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <?php if(View::hasSection('og:image') || $ogImage): ?>
    <meta name="twitter:image" content="<?php echo $__env->yieldContent('og:image', $ogImage ? asset('storage/' . $ogImage) : ''); ?>">
    <?php endif; ?>
    <!-- Fonts (Local) -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fonts/cairo.css')); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <!-- User Dashboard CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/front/css/user-dashboard.css')); ?>">
    <!-- Toast Notifications CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/toast-notifications.css')); ?>">


    <link rel="stylesheet" href="<?php echo e(asset('assets/front/css/preloader.css')); ?>">
    
    <!-- Laravel Mix Compiled Assets -->
    <?php echo $__env->yieldContent('styles'); ?>


</head>

<body class="min-h-screen bg-gray-50">
    
    <?php echo $__env->make('components.preloader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- User Dashboard Container -->
    <div class="user-dashboard-container">
        <!-- Top Navbar -->
        <nav class="user-header">
            <div class="user-nav">
                <!-- Logo Section -->
                <a href="<?php echo e(route('dashboard')); ?>" class="user-logo">
                    <?php if($siteLogo): ?>
                    <img src="<?php echo e(Storage::url($siteLogo)); ?>" alt="<?php echo e($siteName); ?>" class="user-logo-icon" />
                    <?php else: ?>
                    <div class="user-logo-icon">
                        <i class="fas fa-bolt text-white"></i>
                    </div>
                    <?php endif; ?>
                    <span><?php echo e($siteName); ?></span>
                </a>

                <!-- Desktop Navigation -->
                <ul class="user-nav-links">
                    <li>
                        <a href="<?php if(auth()->guard()->check()): ?><?php echo e(route('dashboard')); ?><?php else: ?><?php echo e(url('/')); ?><?php endif; ?>"
                            class="user-nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                            <?php if(auth()->guard()->check()): ?>
                            <?php echo e(trans('app.Dashboard')); ?>

                            <?php else: ?>
                            <?php echo e(trans('app.Home')); ?>

                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('user.tickets.index')); ?>"
                            class="user-nav-link <?php echo e(request()->routeIs('user.tickets.*') ? 'active' : ''); ?>">
                            <?php echo e(trans('app.Support')); ?>

                        </a>
                    </li>
                    <?php if(auth()->guard()->check()): ?>
                    <li>
                        <a href="<?php echo e(route('user.invoices.index')); ?>"
                            class="user-nav-link <?php echo e(request()->routeIs('invoices.*') ? 'active' : ''); ?>">
                            <?php echo e(trans('app.Invoices')); ?>

                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo e(route('kb.index')); ?>"
                            class="user-nav-link <?php echo e(request()->routeIs('kb.*') ? 'active' : ''); ?>">
                            <?php echo e(trans('app.Knowledge Base')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('license.status')); ?>"
                            class="user-nav-link <?php echo e(request()->routeIs('license.*') ? 'active' : ''); ?>">
                            <?php echo e(trans('license_status.page_title')); ?>

                        </a>
                    </li>
                </ul>

                <!-- Right Side Actions -->
                <div class="user-nav-actions">
                    <!-- Desktop Language Switcher -->
                    <div class="user-dropdown hidden md:block">
                        <button class="user-dropdown-toggle">
                            <i class="fas fa-globe"></i>
                            <span><?php echo e($currentLanguage['native_name'] ?? $currentLanguage['name'] ?? $currentLocale); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>

                        <div class="user-dropdown-menu">
                            <?php $__currentLoopData = $availableLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('lang.switch', $language['code'])); ?>" 
                               class="user-dropdown-item <?php echo e($language['code'] === $currentLocale ? 'active' : ''); ?>">
                                <span class="mr-2"><?php echo e($language['flag']); ?></span>
                                <span><?php echo e($language['native_name']); ?></span>
                            </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <!-- Desktop Profile Dropdown -->
                    <?php if(auth()->guard()->check()): ?>
                    <div class="user-dropdown hidden md:block">
                        <button class="user-dropdown-toggle">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-indigo-600"></i>
                            </div>
                            <span><?php echo e(auth()->user()->name); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>

                        <div class="user-dropdown-menu">
                            <a href="<?php echo e(route('profile.edit')); ?>" class="user-dropdown-item">
                                <i class="fas fa-user-cog mr-2"></i>
                                <?php echo e(trans('app.Profile Settings')); ?>

                            </a>
                            <a href="<?php echo e(route('user.tickets.index')); ?>" class="user-dropdown-item">
                                <i class="fas fa-ticket-alt mr-2"></i>
                                <?php echo e(trans('app.My Tickets')); ?>

                            </a>
                            <div class="border-t border-slate-200 my-1"></div>
                            <a href="#" data-action="logout" class="user-dropdown-item text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                <?php echo e(trans('app.Logout')); ?>

                            </a>
                            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="hidden">
                                <?php echo csrf_field(); ?>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="hidden md:flex items-center space-x-2">
                        <a href="<?php echo e(route('login')); ?>" class="user-action-button">
                            <?php echo e(trans('app.Login')); ?>

                        </a>
                        <a href="<?php echo e(route('register')); ?>" class="user-action-button">
                            <?php echo e(trans('app.Register')); ?>

                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Mobile menu button -->
                    <button class="md:hidden p-2 text-gray-600 hover:text-indigo-600 hover:bg-gray-100 rounded-md transition-colors" data-mobile-menu-toggle>
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </nav>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu hidden md:hidden" data-mobile-menu>
        <div class="mobile-menu-content">
            <!-- Mobile Menu Header -->
            <div class="mobile-menu-header">
                <div class="mobile-menu-title"><?php echo e($siteName); ?></div>
                <button class="mobile-menu-close" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Navigation Links -->
            <div class="mobile-nav-links">
                <a href="<?php if(auth()->guard()->check()): ?><?php echo e(route('dashboard')); ?><?php else: ?><?php echo e(url('/')); ?><?php endif; ?>"
                    class="mobile-nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                    <i class="fas fa-home"></i>
                    <?php if(auth()->guard()->check()): ?>
                    <?php echo e(trans('app.Dashboard')); ?>

                    <?php else: ?>
                    <?php echo e(trans('app.Home')); ?>

                    <?php endif; ?>
                </a>
                <a href="<?php echo e(route('user.tickets.index')); ?>"
                    class="mobile-nav-link <?php echo e(request()->routeIs('user.tickets.*') ? 'active' : ''); ?>">
                    <i class="fas fa-ticket-alt"></i>
                    <?php echo e(trans('app.Support')); ?>

                </a>
                <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('user.invoices.index')); ?>"
                    class="mobile-nav-link <?php echo e(request()->routeIs('invoices.*') ? 'active' : ''); ?>">
                    <i class="fas fa-receipt"></i>
                    <?php echo e(trans('app.Invoices')); ?>

                </a>
                <?php endif; ?>
                <a href="<?php echo e(route('kb.index')); ?>"
                    class="mobile-nav-link <?php echo e(request()->routeIs('kb.*') ? 'active' : ''); ?>">
                    <i class="fas fa-book"></i>
                    <?php echo e(trans('app.Knowledge Base')); ?>

                </a>
                <a href="<?php echo e(route('license.status')); ?>"
                    class="mobile-nav-link <?php echo e(request()->routeIs('license.*') ? 'active' : ''); ?>">
                    <i class="fas fa-check-circle"></i>
                    <?php echo e(trans('license_status.page_title')); ?>

                </a>
            </div>

            <!-- Language Switcher -->
            <div class="mobile-language-section">
                <h4 class="mobile-section-title"><?php echo e(trans('app.Language')); ?></h4>
                <div class="mobile-language-buttons">
                    <?php $__currentLoopData = $availableLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('lang.switch', $language['code'])); ?>"
                        class="mobile-language-btn <?php echo e($language['code'] === $currentLocale ? 'active' : ''); ?>">
                        <?php echo e($language['flag']); ?> <?php echo e($language['native_name']); ?>

                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Profile Section -->
            <?php if(auth()->guard()->check()): ?>
            <div class="mobile-profile-section">
                <h4 class="mobile-section-title"><?php echo e(trans('app.Profile')); ?></h4>
                <div class="mobile-profile-info">
                    <div class="mobile-profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="mobile-profile-details">
                        <h5><?php echo e(auth()->user()->name); ?></h5>
                        <p><?php echo e(auth()->user()->email); ?></p>
                    </div>
                </div>
                <div class="mobile-profile-actions">
                    <a href="<?php echo e(route('profile.edit')); ?>" class="mobile-action-btn">
                        <i class="fas fa-user-cog"></i>
                        <?php echo e(trans('app.Profile Settings')); ?>

                    </a>
                    <a href="<?php echo e(route('user.tickets.index')); ?>" class="mobile-action-btn">
                        <i class="fas fa-ticket-alt"></i>
                        <?php echo e(trans('app.My Tickets')); ?>

                    </a>
                    <form action="<?php echo e(route('logout')); ?>" method="POST" class="mobile-logout-form">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="mobile-logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <?php echo e(trans('app.Logout')); ?>

                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="mobile-auth-section">
                <h4 class="mobile-section-title"><?php echo e(trans('app.Account')); ?></h4>
                <div class="mobile-auth-buttons">
                    <a href="<?php echo e(route('login')); ?>" class="mobile-auth-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <?php echo e(trans('app.Login')); ?>

                    </a>
                    <a href="<?php echo e(route('register')); ?>" class="mobile-auth-btn primary">
                        <i class="fas fa-user-plus"></i>
                        <?php echo e(trans('app.Register')); ?>

                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile Menu Backdrop -->
    <div class="mobile-menu-backdrop"></div>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 min-h-screen">
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

        <?php if($errors->any()): ?>
        <div class="user-alert user-alert-error">
            <div class="user-alert-content">
                <i class="fas fa-exclamation-triangle user-alert-icon"></i>
                <div class="user-alert-text">
                    <h4><?php echo e(__('app.validation_errors')); ?></h4>
                    <ul class="user-error-list">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </main>


    <!-- Integrated Footer Links -->
    <div class="user-footer-integrated">
        <div class="user-footer-content">
            <p>&copy; <?php echo e(date('Y')); ?> <?php echo e($siteName); ?>. <?php echo e(trans('app.All rights reserved.')); ?></p>
        </div>
        <div class="user-footer-links">
            <a href="<?php echo e(route('kb.index')); ?>" class="user-footer-link">
                <?php echo e(trans('app.Help Center')); ?>

            </a>
            <a href="<?php echo e(route('user.tickets.index')); ?>" class="user-footer-link">
                <?php echo e(trans('app.Support')); ?>

            </a>
            <?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('profile.edit')); ?>" class="user-footer-link">
                <?php echo e(trans('app.Profile')); ?>

            </a>
            <?php endif; ?>
        </div>
    </div>


    <!-- jQuery (must be loaded first) -->
    <!-- Security Utils Library -->
    <script src="<?php echo e(asset('assets/js/security-utils.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/js/jquery-3.6.0.min.js')); ?>"></script>
    <!-- Alpine.js for Interactive Components (Local) -->
    <script src="<?php echo e(asset('vendor/assets/alpinejs/alpine.min.js')); ?>" defer></script>
    <!-- User Dashboard JavaScript -->
    <script src="<?php echo e(asset('assets/front/js/user-dashboard.js')); ?>" defer></script>
    <!-- Admin JavaScript for Toast Notifications -->
    <script src="<?php echo e(asset('assets/admin/js/admin.js')); ?>"></script>
    <!-- Preloader JavaScript -->
    <script src="<?php echo e(asset('assets/admin/js/preloader.js')); ?>"></script>
    <!-- User Tickets JavaScript -->
    <?php if(request()->routeIs('user.tickets.*')): ?>
    <script src="<?php echo e(asset('assets/front/js/user-tickets.js')); ?>"></script>
    <?php endif; ?>
    <!-- Product Show JavaScript -->
    <?php if(request()->routeIs('user.products.show') || request()->routeIs('public.products.show') || request()->routeIs('products.show')): ?>
    <script src="<?php echo e(asset('assets/front/js/product-show.js')); ?>"></script>
    <?php endif; ?>
    <script src="<?php echo e(asset('assets/front/js/layouts.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/front/js/admin-actions.js')); ?>"></script>
    
    <!-- Laravel Mix Compiled JavaScript -->
    <script src="<?php echo e(mix('assets/front/js/app.js')); ?>" defer></script>
    
    <!-- Knowledge Base Search JavaScript -->
    <?php if(request()->routeIs('kb.search*')): ?>
    <script src="<?php echo e(asset('assets/front/js/kb-search.js')); ?>"></script>
    <?php endif; ?>


    <?php echo $__env->yieldContent('scripts'); ?>

</body>

</html><?php /**PATH D:\xampp\htdocs\my-logos\resources\views/layouts/user.blade.php ENDPATH**/ ?>