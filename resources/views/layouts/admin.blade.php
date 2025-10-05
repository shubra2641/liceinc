<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <title>
        @hasSection('title')
        @yield('title') - {{ $siteName ?? 'Admin Dashboard' }}
        @elseif(View::hasSection('page-title'))
        @yield('page-title') - {{ $siteName ?? 'Admin Dashboard' }}
        @elseif(View::hasSection('seo_title'))
        @yield('seo_title') - {{ $siteName ?? 'Admin Dashboard' }}
        @else
        {{ $siteName ?? 'Admin Dashboard' }}
        @endif
    </title>
    
    <meta name="description" content="@yield('meta_description', $siteSeoDescription ?? 'Admin dashboard for managing licenses and products')">
    
    @if(View::hasSection('meta_keywords'))
    <meta name="keywords" content="@yield('meta_keywords')">
    @endif
 
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Fonts (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/assets/fonts/cairo.css') }}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/assets/bootstrap/css/bootstrap.min.css') }}">
    <!-- Font Awesome (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/assets/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/assets/fontawesome/css/local-fonts.css') }}">
    <!-- License Guide Styles -->
    @if(request()->routeIs('admin.license-verification-guide*'))
    <link rel="stylesheet" href="{{ asset('assets/admin/css/license-guide.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin-dashboard-unified.css') }}">
    <!-- Toast Notifications CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/toast-notifications.css') }}">

    <!-- Laravel Mix Compiled Assets -->
    <link rel="stylesheet" href="{{ mix('assets/admin/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/preloader.css') }}">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    @stack('styles')
</head>

<body class="body">
    {{-- Preloader Component --}}
    @include('components.preloader')

    <!-- Admin Layout Styles -->
    <div class="admin-layout" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Sidebar Overlay for Mobile -->
    <div id="admin-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

    <!-- Modern Sidebar -->
    <aside id="admin-sidebar"
        class="admin-sidebar fixed lg:relative lg:translate-x-0 transform -translate-x-full lg:flex lg:flex-col transition-transform duration-300 ease-in-out z-50 lg:z-auto">
        <div class="admin-sidebar-container">
            <!-- Sidebar Header -->
            <div class="admin-sidebar-header">
                <div class="admin-sidebar-logo">
                    @if($siteLogo)
                        <img src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}" class="admin-sidebar-logo-icon" />
                    @else
                        <div class="admin-sidebar-logo-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                    @endif
                    <div>
                        <h2 class="admin-sidebar-logo-text">{{ $siteName ?? trans('app.Admin Panel') }}</h2>
                        <p class="admin-sidebar-subtitle">{{ trans('app.License Manager') }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation with Scroll -->
            <nav class="admin-sidebar-nav">
                <!-- Main Section -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ trans('app.Main') }}</div>
                    <a href="{{ route('admin.dashboard') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Dashboard') }}</span>
                    </a>
                </div>

                <!-- Products Section -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ trans('app.Products Management') }}</div>
                    <a href="{{ route('admin.products.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fas fa-cube admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Products') }}</span>
                    </a>

                    <a href="{{ route('admin.product-categories.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.product-categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.manage_product_categories') }}</span>
                    </a>

                    <a href="{{ route('admin.programming-languages.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.programming-languages.*') ? 'active' : '' }}">
                        <i class="fas fa-code admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.manage_programming_languages') }}</span>
                    </a>
                </div>

                <!-- Support Section -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ trans('app.Support System') }}</div>
                    <a href="{{ route('admin.tickets.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                        <i class="fas fa-ticket-alt admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Tickets') }}</span>
                    </a>

                    <a href="{{ route('admin.ticket-categories.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.ticket-categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Ticket Categories') }}</span>
                    </a>
                </div>

                <!-- Knowledge Base Section -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ trans('app.Knowledge Base') }}</div>
                    <a href="{{ route('admin.kb-categories.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.kb-categories.*') ? 'active' : '' }}">
                        <i class="fas fa-folder admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.KB Categories') }}</span>
                    </a>

                    <a href="{{ route('admin.kb-articles.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.kb-articles.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Articles') }}</span>
                    </a>
                </div>

                <!-- System Section -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ trans('app.System') }}</div>

                    <a href="{{ route('admin.reports.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Reports') }}</span>
                    </a>

                    <a href="{{ route('admin.license-verification-logs.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.license-verification-logs.*') ? 'active' : '' }}">
                        <i class="fas fa-shield-alt admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.License Verification Logs') }}</span>
                    </a>

                    <a href="{{ route('admin.license-verification-guide.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.license-verification-guide.*') ? 'active' : '' }}">
                        <i class="fas fa-code admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('license-guide.page_title') }}</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Users') }}</span>
                    </a>


                    {{-- Customers admin UI removed; keep sidebar tidy. --}}
                    <a href="{{ route('admin.licenses.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.licenses.*') ? 'active' : '' }}">
                        <i class="fas fa-key admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Licenses') }}</span>
                    </a>

                    <a href="{{ route('admin.invoices.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Invoices') }}</span>
                    </a>


                    <a href="{{ route('admin.settings.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Settings') }}</span>
                    </a>

                    <a href="{{ route('admin.payment-settings.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.payment-settings.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Payment Settings') }}</span>
                    </a>

                    <a href="{{ route('admin.updates.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.updates.*') ? 'active' : '' }}">
                        <i class="fas fa-download admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Updater') }}</span>
                    </a>

                    <a href="{{ route('admin.clear-cache') }}" class="admin-nav-item" onclick="return confirm('Are you sure you want to clear all caches?')">
                        <i class="fas fa-trash-alt admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Clear Cache') }}</span>
                    </a>

                    <a href="{{ route('admin.email-templates.index') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">
                        <i class="fas fa-envelope admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Email Templates') }}</span>
                    </a>

                    <a href="{{ route('admin.envato-guide') }}"
                        class="admin-nav-item {{ request()->routeIs('admin.envato-guide') ? 'active' : '' }}">
                        <i class="fas fa-book admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Envato Guide') }}</span>
                    </a>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="admin-sidebar-footer">
                <!-- Single-line quick links above logout: left = language, right = profile -->
                <div class="admin-footer-quick d-flex justify-content-between align-items-center mb-2 px-2">
                    @if($otherLanguage)
                    <a href="{{ route('lang.switch', $otherLanguage['code']) }}" class="admin-footer-link text-decoration-none text-muted">
                        {{ $otherLanguage['native_name'] }}
                    </a>
                    @endif
                    <a href="{{ route('admin.profile.edit') }}" class="admin-footer-link text-decoration-none text-muted">
                        {{ trans('app.Profile') }}
                    </a>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="admin-logout-form">
                    @csrf
                    <button type="submit" class="admin-logout-btn">
                        <i class="fas fa-sign-out-alt admin-nav-item-icon"></i>
                        <span class="admin-nav-item-text">{{ trans('app.Logout') }}</span>
                    </button>
                </form>

                
            </div>
            
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main flex-1 lg:ml-0 transition-all duration-300 ease-in-out">
        <!-- Modern Topbar -->
        <header class="admin-topbar">
            <div class="admin-topbar-content flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 lg:gap-0">
                <div class="admin-topbar-left flex items-center gap-4">
                    <button id="admin-menu-toggle" class="admin-menu-toggle lg:hidden" aria-label="Toggle sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="admin-topbar-title text-lg lg:text-xl">{{ trans('app.Admin Dashboard') }}</h1>
                </div>

                <div
                    class="admin-topbar-right hidden lg:flex items-center gap-2 lg:gap-4 flex-wrap justify-center lg:justify-end">
                    <!-- Language Switcher -->
                    <div class="admin-language-switcher">
                    <button class="admin-language-btn">
                        {{ $currentLanguage['native_name'] ?? $currentLanguage['name'] ?? $currentLocale }}
                    </button>
                    <div class="admin-language-dropdown">
                        @foreach($availableLanguages as $language)
                        <a href="{{ route('lang.switch', $language['code']) }}" 
                           class="admin-language-item {{ $language['code'] === $currentLocale ? 'active' : '' }}" 
                           data-lang="{{ $language['code'] }}">
                            <span class="admin-language-flag">{{ $language['flag'] }}</span>
                            {{ $language['flag'] }} - {{ $language['native_name'] }}
                        </a>
                        @endforeach
                    </div>
                </div>
                <!-- User Menu -->
                @auth
                <div class="admin-user-profile">
                    <div class="admin-user-profile-avatar">AU</div>
                    <div class="admin-user-profile-info">
                        <div class="admin-user-profile-name">{{ auth()->user()->name }}</div>
                        <div class="admin-user-profile-role">{{ auth()->user()->role }}</div>
                    </div>
                    <div class="admin-user-profile-arrow"></div>
                    <div class="admin-user-dropdown">
                        <div class="admin-user-dropdown-header">
                            <div class="admin-user-dropdown-info">
                                <div class="admin-user-dropdown-avatar">AU</div>
                                <div class="admin-user-dropdown-details">
                                    <h4>{{ auth()->user()->name }}</h4>
                                    <p>{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="admin-user-dropdown-menu">
                            <a href="{{ route('admin.profile.edit') }}" class="admin-user-dropdown-item profile">{{ trans('app.Profile') }}</a>
                            <a href="{{ route('admin.settings.index') }}" class="admin-user-dropdown-item settings">{{ trans('app.Settings') }}</a>
                            <a href="{{ route('logout') }}" class="admin-user-dropdown-item danger logout" data-action="logout">{{ trans('app.Logout') }}</a>
                        </div>
                    </div>
                </div>
                @endauth
                    <!-- User Menu -->

                </div>

            </div>
        </header>

        <!-- Logout Form for Topbar -->
        <form id="topbar-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>

        <!-- Page Content -->
        <main class="admin-main-content px-4 py-6 lg:px-8 lg:py-8 max-w-full overflow-x-auto">
                <!-- Flash Messages for Toast Notifications -->
    @if(session('success'))
    <div data-flash-success class="flash-message-hidden">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div data-flash-error class="flash-message-hidden">{{ session('error') }}</div>
    @endif
    @if(session('warning'))
    <div data-flash-warning class="flash-message-hidden">{{ session('warning') }}</div>
    @endif
    @if(session('info'))
    <div data-flash-info class="flash-message-hidden">{{ session('info') }}</div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="admin-alert admin-alert-error">
        <div class="admin-alert-content">
            <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
            <div class="admin-alert-text">
                <h4>{{ __('app.validation_errors') }}</h4>
                <ul class="admin-error-list">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif
            @yield('admin-content')
        </main>
    </div>
</div>

<!-- Update Notification Component -->
@include('components.UpdateNotification')


    <!-- Preloader JavaScript -->
    <script src="{{ asset('assets/admin/js/preloader.js') }}"></script>
    <!-- jQuery (must be loaded first) -->
    <script src="{{ asset('assets/admin/js/jquery-3.6.0.min.js') }}"></script>
    <!-- Bootstrap JS (required by Summernote BS5) -->
    <script src="{{ asset('vendor/assets/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Admin Mobile Menu JavaScript -->
    <script src="{{ asset('assets/admin/js/admin-mobile-menu.js') }}"></script>
    <!-- Admin Sidebar Fix JavaScript -->
    <script src="{{ asset('assets/admin/js/admin-sidebar-fix.js') }}"></script>
    <script src="{{ asset('assets/admin/js/admin.js') }}"></script>
    <!-- Laravel Mix Compiled JavaScript -->
    <script src="{{ mix('assets/admin/js/app.js') }}"></script>
    <!-- Admin Dashboard JavaScript -->
    @if(request()->routeIs('admin.dashboard*') || request()->routeIs('admin.reports*') || request()->routeIs('admin.products.logs'))
    <script src="{{ asset('assets/admin/js/admin-charts.js') }}"></script>
    @endif

    <!-- System Updates JavaScript -->
    @if(request()->routeIs('admin.updates*'))
    <script src="{{ asset('assets/admin/js/updates.js') }}"></script>
    @endif

    @stack('scripts')

</body>

</html>