{{-- Professional Preloader Component --}}
{{-- High-quality, customizable preloader with multiple styles --}}


@if($preloaderSettings['preloaderEnabled'])
<div class="preloader-container" id="preloader-container" data-enabled="1"
    data-type="{{ $preloaderSettings['preloaderType'] }}" data-color="{{ $preloaderSettings['preloaderColor'] }}"
    data-bg="{{ $preloaderSettings['preloaderBgColor'] }}" data-duration="{{ $preloaderSettings['preloaderDuration'] }}"
    data-min-duration="{{ $preloaderSettings['preloaderMinDuration'] ?? 0 }}"
    data-text="{{ $preloaderSettings['preloaderText'] }}" data-logo="{{ $preloaderSettings['siteLogo'] }}">
    <div class="preloader-content">
        {{-- Logo Section --}}
        @if($preloaderSettings['siteLogo'] || $preloaderSettings['logoShowText'])
        <div class="preloader-logo">
            @if($preloaderSettings['siteLogo'])
            <img src="{{ asset('storage/' . $preloaderSettings['siteLogo']) }}"
                alt="{{ $preloaderSettings['logoText'] }}" class="preloader-logo-img"
                class="max-w-[150px] max-h-[50px]">
            @elseif($preloaderSettings['logoShowText'])
            <div class="preloader-logo-text" class="text-gray-800 text-2xl font-semibold">
                {{ $preloaderSettings['logoText'] }}
            </div>
            @endif
        </div>
        @endif

        {{-- Preloader Animation --}}
        <div class="preloader-animation">
            @switch($preloaderSettings['preloaderType'])
            @case('spinner')
            <div class="preloader-spinner"></div>
            @break
            @case('dots')
            <div class="preloader-dots">
                <div class="preloader-dot"></div>
                <div class="preloader-dot"></div>
                <div class="preloader-dot"></div>
            </div>
            @break
            @case('bars')
            <div class="preloader-bars">
                <div class="preloader-bar"></div>
                <div class="preloader-bar"></div>
                <div class="preloader-bar"></div>
                <div class="preloader-bar"></div>
                <div class="preloader-bar"></div>
            </div>
            @break
            @case('pulse')
            <div class="preloader-pulse"></div>
            @break
            @case('progress')
            <div class="preloader-progress">
                <div class="preloader-progress-bar"></div>
            </div>
            @break
            @case('custom')
            <div class="preloader-custom">
                {{ $settings->preloader_custom_css ?? '<div class="custom-loader"></div>' }}
            </div>
            @break
            @default
            <div class="preloader-spinner"></div>
            @endswitch
        </div>

        {{-- Loading Text --}}
        <div class="preloader-text">{{ $preloaderSettings['preloaderText'] }}</div>
    </div>
</div>

{{-- Preloader settings moved to data-* attributes to avoid inline JS --}} @endif

{{-- Progressive Enhancement: Fallback for users without JavaScript --}}
<noscript>
    <link rel="stylesheet" href="{{ asset('assets/front/css/preloader-noscript.css') }}">
</noscript>