<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    {{-- Page title: prefer @section('title') then page-title section then specific SEO then site SEO title then app name --}}
    <title>
        @hasSection('title')
        @yield('title') - {{ $siteName ?? config('app.name', 'Laravel') }}
        @elseif(View::hasSection('page-title'))
        @yield('page-title') - {{ $siteName ?? config('app.name', 'Laravel') }}
        @elseif(View::hasSection('seo_title'))
        @yield('seo_title') - {{ $siteName ?? config('app.name', 'Laravel') }}
        @elseif($siteSeoTitle)
        {{ $siteSeoTitle }} - {{ $siteName ?? config('app.name', 'Laravel') }}
        @else
        {{ $siteName ?? config('app.name', 'Laravel') }} - {{ trans('app.Dashboard') }}
        @endif
    </title>

    <meta name="description"
        content="@yield('meta_description', $siteSeoDescription ?? trans('app.User dashboard for managing licenses and products'))">
    
    @if(View::hasSection('meta_keywords'))
    <meta name="keywords" content="@yield('meta_keywords')">
    @endif

    <!-- Responsive Viewport Meta Tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">

    <!-- Page Title and Subtitle -->
    {{-- Open Graph and Twitter tags: prefer explicit sections, then settings --}}
    @if(View::hasSection('og:title') || View::hasSection('page-title'))
    <meta property="og:title"
        content="@yield('og:title', View::hasSection('page-title') ? trim(strip_tags($__env->yieldContent('page-title'))) . ' - ' . ($siteName ?? config('app.name', 'Laravel')) : '')">
    @elseif($siteSeoTitle)
    <meta property="og:title" content="{{ $siteSeoTitle }} - {{ $siteName ?? config('app.name', 'Laravel') }}">
    @endif

    @if(View::hasSection('og:description') || View::hasSection('page-subtitle'))
    <meta property="og:description"
        content="@yield('og:description', View::hasSection('page-subtitle') ? trim(strip_tags($__env->yieldContent('page-subtitle'))) : '')">
    @elseif($siteSeoDescription)
    <meta property="og:description" content="{{ $siteSeoDescription }}">
    @endif
    <!-- Font Awesome (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/assets/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/assets/fontawesome/css/local-fonts.css') }}">
    @if(View::hasSection('og:image'))
    <meta property="og:image" content="@yield('og:image')">
    @elseif($ogImage)
    <meta property="og:image" content="{{ asset('storage/' . $ogImage) }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @if(View::hasSection('og:image') || $ogImage)
    <meta name="twitter:image" content="@yield('og:image', $ogImage ? asset('storage/' . $ogImage) : '')">
    @endif
    <!-- Fonts (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/assets/fonts/cairo.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- User Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/user-dashboard.css') }}">
    <!-- Toast Notifications CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/toast-notifications.css') }}">


    <link rel="stylesheet" href="{{ asset('assets/front/css/preloader.css') }}">
    

    @yield('styles')


</head>

<body class="min-h-screen bg-gray-50">
    {{-- Preloader Component --}}
    @include('components.preloader')

    <!-- User Dashboard Container -->
    <div class="user-dashboard-container">
        <!-- Top Navbar -->
        <nav class="user-header">
            <div class="user-nav">
                <!-- Logo Section -->
                <a href="{{ route('dashboard') }}" class="user-logo">
                    @if($siteLogo)
                    <img src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName ?? config('app.name', 'Laravel') }}" class="user-logo-icon" />
                    @else
                    <div class="user-logo-icon">
                        <i class="fas fa-bolt text-white"></i>
                    </div>
                    @endif
                    <span>{{ $siteName ?? config('app.name', 'Laravel') }}</span>
                </a>

                <!-- Desktop Navigation -->
                <ul class="user-nav-links">
                    <li>
                        <a href="@auth{{ route('dashboard') }}@else{{ url('/') }}@endauth"
                            class="user-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            @auth
                            {{ trans('app.Dashboard') }}
                            @else
                            {{ trans('app.Home') }}
                            @endauth
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user.tickets.index') }}"
                            class="user-nav-link {{ request()->routeIs('user.tickets.*') ? 'active' : '' }}">
                            {{ trans('app.Support') }}
                        </a>
                    </li>
                    @auth
                    <li>
                        <a href="{{ route('user.invoices.index') }}"
                            class="user-nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                            {{ trans('app.Invoices') }}
                        </a>
                    </li>
                    @endauth
                    <li>
                        <a href="{{ route('kb.index') }}"
                            class="user-nav-link {{ request()->routeIs('kb.*') ? 'active' : '' }}">
                            {{ trans('app.Knowledge Base') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('license.status') }}"
                            class="user-nav-link {{ request()->routeIs('license.*') ? 'active' : '' }}">
                            {{ trans('license_status.page_title') }}
                        </a>
                    </li>
                </ul>

                <!-- Right Side Actions -->
                <div class="user-nav-actions">
                    <!-- Desktop Language Switcher -->
                    <div class="user-dropdown hidden md:block">
                        <button class="user-dropdown-toggle">
                            <i class="fas fa-globe"></i>
                            <span>{{ $currentLanguage['native_name'] ?? $currentLanguage['name'] ?? $currentLocale }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>

                        <div class="user-dropdown-menu">
                            @foreach($availableLanguages as $language)
                            <a href="{{ route('lang.switch', $language['code']) }}" 
                               class="user-dropdown-item {{ $language['code'] === $currentLocale ? 'active' : '' }}">
                                <span class="mr-2">{{ $language['flag'] }}</span>
                                <span>{{ $language['native_name'] }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Desktop Profile Dropdown -->
                    @auth
                    <div class="user-dropdown hidden md:block">
                        <button class="user-dropdown-toggle">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-indigo-600"></i>
                            </div>
                            <span>{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>

                        <div class="user-dropdown-menu">
                            <a href="{{ route('profile.edit') }}" class="user-dropdown-item">
                                <i class="fas fa-user-cog mr-2"></i>
                                {{ trans('app.Profile Settings') }}
                            </a>
                            <a href="{{ route('user.tickets.index') }}" class="user-dropdown-item">
                                <i class="fas fa-ticket-alt mr-2"></i>
                                {{ trans('app.My Tickets') }}
                            </a>
                            <div class="border-t border-slate-200 my-1"></div>
                            <a href="#" data-action="logout" class="user-dropdown-item text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                {{ trans('app.Logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="hidden md:flex items-center space-x-2">
                        <a href="{{ route('login') }}" class="user-action-button">
                            {{ trans('app.Login') }}
                        </a>
                        <a href="{{ route('register') }}" class="user-action-button">
                            {{ trans('app.Register') }}
                        </a>
                    </div>
                    @endauth

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
                <div class="mobile-menu-title">{{ $siteName ?? config('app.name', 'Laravel') }}</div>
                <button class="mobile-menu-close" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Navigation Links -->
            <div class="mobile-nav-links">
                <a href="@auth{{ route('dashboard') }}@else{{ url('/') }}@endauth"
                    class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    @auth
                    {{ trans('app.Dashboard') }}
                    @else
                    {{ trans('app.Home') }}
                    @endauth
                </a>
                <a href="{{ route('user.tickets.index') }}"
                    class="mobile-nav-link {{ request()->routeIs('user.tickets.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i>
                    {{ trans('app.Support') }}
                </a>
                @auth
                <a href="{{ route('user.invoices.index') }}"
                    class="mobile-nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-receipt"></i>
                    {{ trans('app.Invoices') }}
                </a>
                @endauth
                <a href="{{ route('kb.index') }}"
                    class="mobile-nav-link {{ request()->routeIs('kb.*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i>
                    {{ trans('app.Knowledge Base') }}
                </a>
                <a href="{{ route('license.status') }}"
                    class="mobile-nav-link {{ request()->routeIs('license.*') ? 'active' : '' }}">
                    <i class="fas fa-check-circle"></i>
                    {{ trans('license_status.page_title') }}
                </a>
            </div>

            <!-- Language Switcher -->
            <div class="mobile-language-section">
                <h4 class="mobile-section-title">{{ trans('app.Language') }}</h4>
                <div class="mobile-language-buttons">
                    @foreach($availableLanguages as $language)
                    <a href="{{ route('lang.switch', $language['code']) }}"
                        class="mobile-language-btn {{ $language['code'] === $currentLocale ? 'active' : '' }}">
                        {{ $language['flag'] }} {{ $language['native_name'] }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Profile Section -->
            @auth
            <div class="mobile-profile-section">
                <h4 class="mobile-section-title">{{ trans('app.Profile') }}</h4>
                <div class="mobile-profile-info">
                    <div class="mobile-profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="mobile-profile-details">
                        <h5>{{ auth()->user()->name }}</h5>
                        <p>{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <div class="mobile-profile-actions">
                    <a href="{{ route('profile.edit') }}" class="mobile-action-btn">
                        <i class="fas fa-user-cog"></i>
                        {{ trans('app.Profile Settings') }}
                    </a>
                    <a href="{{ route('user.tickets.index') }}" class="mobile-action-btn">
                        <i class="fas fa-ticket-alt"></i>
                        {{ trans('app.My Tickets') }}
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="mobile-logout-form">
                        @csrf
                        <button type="submit" class="mobile-logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            {{ trans('app.Logout') }}
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="mobile-auth-section">
                <h4 class="mobile-section-title">{{ trans('app.Account') }}</h4>
                <div class="mobile-auth-buttons">
                    <a href="{{ route('login') }}" class="mobile-auth-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        {{ trans('app.Login') }}
                    </a>
                    <a href="{{ route('register') }}" class="mobile-auth-btn primary">
                        <i class="fas fa-user-plus"></i>
                        {{ trans('app.Register') }}
                    </a>
                </div>
            </div>
            @endauth
        </div>
    </div>

    <!-- Mobile Menu Backdrop -->
    <div class="mobile-menu-backdrop"></div>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 min-h-screen">
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

        @if($errors->any())
        <div class="user-alert user-alert-error">
            <div class="user-alert-content">
                <i class="fas fa-exclamation-triangle user-alert-icon"></i>
                <div class="user-alert-text">
                    <h4>{{ __('app.validation_errors') }}</h4>
                    <ul class="user-error-list">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        @yield('content')
    </main>


    <!-- Integrated Footer Links -->
    <div class="user-footer-integrated">
        <div class="user-footer-content">
            <p>&copy; {{ date('Y') }} {{ $siteName ?? config('app.name', 'Laravel') }}. {{ trans('app.All rights reserved.') }}</p>
        </div>
        <div class="user-footer-links">
            <a href="{{ route('kb.index') }}" class="user-footer-link">
                {{ trans('app.Help Center') }}
            </a>
            <a href="{{ route('user.tickets.index') }}" class="user-footer-link">
                {{ trans('app.Support') }}
            </a>
            @auth
            <a href="{{ route('profile.edit') }}" class="user-footer-link">
                {{ trans('app.Profile') }}
            </a>
            @endauth
        </div>
    </div>


    <!-- jQuery (must be loaded first) -->
    <!-- Security Utils Library -->
    <script src="{{ asset('assets/js/security-utils.js') }}"></script>
    
    <!-- Preloader Settings -->
    <script>
        window.preloaderSettings = {
            enabled: {{ $preloaderSettings['preloaderEnabled'] ? 'true' : 'false' }},
            type: '{{ $preloaderSettings['preloaderType'] }}',
            color: '{{ $preloaderSettings['preloaderColor'] }}',
            backgroundColor: '{{ $preloaderSettings['preloaderBgColor'] }}',
            duration: {{ $preloaderSettings['preloaderDuration'] }},
            minDuration: {{ $preloaderSettings['preloaderMinDuration'] ?? 0 }},
            text: '{{ $preloaderSettings['preloaderText'] }}',
            logo: '{{ $preloaderSettings['siteLogo'] }}',
            logoText: '{{ $preloaderSettings['logoText'] }}',
            logoShowText: {{ $preloaderSettings['logoShowText'] ? 'true' : 'false' }}
        };
    </script>
    
    <script src="{{ asset('assets/admin/js/jquery-3.6.0.min.js') }}"></script>
    <!-- Alpine.js for Interactive Components (Local) -->
    <script src="{{ asset('vendor/assets/alpinejs/alpine.min.js') }}" defer></script>
    <!-- User Dashboard JavaScript -->
    <script src="{{ asset('assets/front/js/user-dashboard.js') }}" defer></script>
    <!-- Preloader JavaScript -->
    <script src="{{ asset('assets/admin/js/preloader.js') }}"></script>
    <!-- User Tickets JavaScript -->
    @if(request()->routeIs('user.tickets.*'))
    <script src="{{ asset('assets/front/js/user-tickets.js') }}"></script>
    @endif
    <!-- Product Show JavaScript -->
    @if(request()->routeIs('user.products.show') || request()->routeIs('public.products.show') || request()->routeIs('products.show'))
    <script src="{{ asset('assets/front/js/product-show.js') }}"></script>
    @endif
    <script src="{{ asset('assets/front/js/layouts.js') }}"></script>
    <script src="{{ asset('assets/front/js/admin-actions.js') }}"></script>
    

    <!-- Knowledge Base Search JavaScript -->
    @if(request()->routeIs('kb.search*'))
    <script src="{{ asset('assets/front/js/kb-search.js') }}"></script>
    @endif


    @yield('scripts')

</body>

</html>