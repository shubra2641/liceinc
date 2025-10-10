<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <meta charset="UTF-8">
    <meta name="description"
        content="@yield('meta_description', $siteSeoDescription ?? trans('app.User dashboard for managing licenses and products'))">

    <!-- Responsive Viewport Meta Tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>
        @hasSection('title') 
        @yield('title') - {{ $siteName }}
        @elseif(View::hasSection('page-title'))
        @yield('page-title') - {{ $siteName }}
        @elseif(View::hasSection('seo_title'))
        @yield('seo_title') - {{ $siteName }}
        @elseif($siteSeoTitle)
        {{ $siteSeoTitle }} - {{ $siteName }}
        @else
        {{ $siteName }} - {{ trans('app.Dashboard') }}
        @endif
    </title>
    
    @if(View::hasSection('meta_keywords'))
    <meta name="keywords" content="@yield('meta_keywords')">
    @endif
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/assets/fonts/cairo.css') }}">

    <!-- Bootstrap CSS (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/assets/bootstrap/css/bootstrap.min.css') }}">
    
    <!-- Font Awesome (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/assets/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/assets/fontawesome/css/local-fonts.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/front/css/preloader.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/maintenance.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/user-dashboard.css') }}">
    
    <!-- Laravel Mix Compiled Assets -->
    <link rel="stylesheet" href="{{ mix('assets/front/css/app.css') }}">
    @yield('styles')

</head>
<body class="admin-page">
    {{-- Preloader Component --}}
    @include('components.preloader')
    
    <!-- Header with Logo -->
    <header class="guest-header">
        <div class="guest-header-container">
            <a href="#" class="guest-logo">
                @if($siteLogo)
                    <img src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}" class="guest-logo-icon" />
                @else
                    <div class="guest-logo-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                @endif
                <span class="guest-logo-text">{{ $siteName ?? config('app.name') }}</span>
            </a>
        </div>
    </header>
    
    <!-- Page Content -->
    <main class="admin-main-content px-4 py-6 lg:px-8 lg:py-8 max-w-full overflow-x-auto">
        @yield('content')
    </main>


    <!-- Preloader JavaScript -->
    <!-- Security Utils Library -->
    <script src="{{ asset('assets/js/security-utils.js') }}"></script>
    <script src="{{ asset('assets/js/security-validation.js') }}"></script>
    <script src="{{ asset('assets/front/js/preloader.js') }}"></script>
    <!-- jQuery (must be loaded first) -->
    <script src="{{ asset('assets/front/js/jquery-3.6.0.min.js') }}"></script>
    <!-- Bootstrap JS (required by Summernote BS5) -->
    <script src="{{ asset('vendor/assets/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- Select2 JS -->
    <script src="{{ asset('vendor/assets/select2/select2.min.js') }}"></script>
    <!-- Summernote BS5 JS -->
    <script src="{{ asset('vendor/assets/summernote/summernote-bs5.min.js') }}"></script>
    <!-- Chart.js -->
    <script src="{{ asset('vendor/assets/chartjs/chart.min.js') }}"></script>
    <!-- DataTables JS -->
    <script src="{{ asset('vendor/assets/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/assets/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <!-- Laravel Mix Compiled JavaScript -->
    <script src="{{ mix('assets/front/js/app.js') }}"></script>

    @yield('scripts')
</body>
</html>
