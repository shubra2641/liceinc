<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', trans('install.install_title')) - {{ config('app.name', 'License Management System') }}</title>
    
    <!-- Fonts -->
    <!-- Fonts (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/assets/fonts/cairo.css') }}">
    
    <!-- FontAwesome (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/assets/fontawesome/css/all.min.css') }}">
    
    <!-- Installation Styles -->
    <link rel="stylesheet" href="{{ asset('assets/install/css/install.css') }}">
    
    <!-- License Verification Styles -->
    @if(request()->routeIs('install.license*'))
    <link rel="stylesheet" href="{{ asset('assets/install/css/license.css') }}">
    @endif
    
    @stack('styles')
</head>
<body class="install-body">
    <!-- Installation Header -->
    <div class="install-header">
        <div class="install-header-content">
            <div class="install-logo">
                <i class="fas fa-shield-alt"></i>
                <span>{{ config('app.name', 'License Management System') }}</span>
            </div>
            <div class="install-version">
                <span>Installation Wizard</span>
            </div>
        </div>
    </div>

    <!-- Installation Progress -->
    <div class="install-progress">
        <div class="install-progress-line"></div>
        
        @foreach($steps as $stepData)
            <div class="install-step {{ $stepData['status'] }}">
                <div class="install-step-number">
                    @if($stepData['isCompleted'])
                        <i class="fas fa-check"></i>
                    @else
                        {{ $stepData['number'] }}
                    @endif
                </div>
                <div class="install-step-title">{{ $stepData['name'] }}</div>
            </div>
        @endforeach
    </div>

    <!-- Main Content -->
    <div class="install-container">
        <div class="install-content">
            @if(session('success'))
                <div class="install-alert install-alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="install-alert install-alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="install-alert install-alert-info">
                    <i class="fas fa-info-circle"></i>
                    {{ session('info') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Installation Footer -->
    <div class="install-footer">
        <div class="install-footer-content">
            <div class="install-footer-text">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'License Management System') }}. All rights reserved.</p>
            </div>
            <div class="install-footer-links">
                <a href="https://my-logos.com/tickets" class="install-footer-link">
                    <i class="fas fa-question-circle"></i>
                    <span>Help</span>
                </a>
                <a href="Https://www.my-logos.com/kb" class="install-footer-link">
                    <i class="fas fa-book"></i>
                    <span>Documentation</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Installation Scripts -->
    <script src="{{ asset('assets/install/js/install.js') }}"></script>
    
    <!-- License Verification Scripts -->
    @if(request()->routeIs('install.license*'))
    <script src="{{ asset('assets/install/js/license.js') }}"></script>
    @endif
    
    @stack('scripts')
</body>
</html>
