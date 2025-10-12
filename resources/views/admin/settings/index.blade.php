@extends('layouts.admin')

@section('admin-content')
<!-- Admin Settings Page -->
<div class="admin-settings-page">
<div class="admin-page-header modern-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1 class="gradient-text">{{ trans('app.system_settings') }}</h1>
            <p class="admin-page-subtitle">{{ trans('app.configure_your_application_settings') }}</p>
        </div>
        <div class="admin-page-actions">
            <a href="{{ route('admin.envato-guide') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                <i class="fas fa-book w-4 h-4 mr-2"></i>
                {{ trans('app.envato_guide') }}
            </a>
            <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
                {{ trans('app.back_to_dashboard') }}
            </a>
        </div>
    </div>
</div>

<div class="admin-content">
    <!-- Settings Tabs Navigation -->
    <div class="admin-card mb-4">
        <div class="admin-section-content">
            <h3 class="admin-card-title">{{ trans('app.system_settings') }}</h3>
        </div>
        <div class="admin-card-content">
            <!-- Tabs Navigation -->
            <div class="admin-tabs-nav">
                <button type="button" class="admin-tab-btn admin-tab-btn-active" data-action="show-tab" data-tab="general-tab" role="tab" aria-selected="true" aria-controls="general-tab" tabindex="0">
                    <i class="fas fa-cog me-2"></i>
                    {{ trans('app.general_settings') }}
                </button>
                <button type="button" class="admin-tab-btn" data-action="show-tab" data-tab="seo-tab" role="tab" aria-selected="false" aria-controls="seo-tab" tabindex="-1">
                    <i class="fas fa-search me-2"></i>
                    {{ trans('app.SEO') }}
                </button>
                <button type="button" class="admin-tab-btn" data-action="show-tab" data-tab="envato-api-tab" role="tab" aria-selected="false" aria-controls="envato-api-tab" tabindex="-1">
                    <i class="fas fa-plug me-2"></i>
                    {{ trans('app.envato_api_settings') }}
                </button>
                <button type="button" class="admin-tab-btn" data-action="show-tab" data-tab="oauth-tab" role="tab" aria-selected="false" aria-controls="oauth-tab" tabindex="-1">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    {{ trans('app.envato_oauth_settings') }}
                </button>
                <button type="button" class="admin-tab-btn" data-action="show-tab" data-tab="license-tab" role="tab" aria-selected="false" aria-controls="license-tab" tabindex="-1">
                    <i class="fas fa-shield-alt me-2"></i>
                    {{ trans('app.license_settings') }}
                </button>
                <button type="button" class="admin-tab-btn" data-action="show-tab" data-tab="preloader-tab" role="tab" aria-selected="false" aria-controls="preloader-tab" tabindex="-1">
                    <i class="fas fa-spinner me-2"></i>
                    {{ trans('app.preloader_settings') }}
                </button>
                <button type="button" class="admin-tab-btn" data-action="show-tab" data-tab="logo-tab" role="tab" aria-selected="false" aria-controls="logo-tab" tabindex="-1">
                    <i class="fas fa-image me-2"></i>
                    {{ trans('app.logo_settings') }}
                </button>
                <button type="button" class="admin-tab-btn" data-action="show-tab" data-tab="advanced-license-tab" role="tab" aria-selected="false" aria-controls="advanced-license-tab" tabindex="-1">
                    <i class="fas fa-cogs me-2"></i>
                    {{ trans('app.advanced_license_settings') }}
                </button>
                <button type="button" class="admin-tab-btn" data-action="show-tab" data-tab="security-tab" role="tab" aria-selected="false" aria-controls="security-tab" tabindex="-1">
                    <i class="fas fa-shield-alt me-2"></i>
                    {{ trans('app.security_antispam') ?? 'Security / Anti-Spam' }}
                </button>
                <button type="button" class="admin-tab-btn" data-action="show-tab" data-tab="contact-tab" role="tab" aria-selected="false" aria-controls="contact-tab" tabindex="-1">
                    <i class="fas fa-address-book me-2"></i>
                    {{ trans('app.contact_information') ?? 'Contact Information' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Content Container -->
    <form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="settings-form" class="needs-validation" novalidate>
        @csrf
        @method('PUT')

        <!-- General Settings Tab -->
        <div class="admin-tab-panel" id="general-tab" role="tabpanel" aria-labelledby="general-tab" aria-hidden="false">
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-cog text-blue-500 me-2"></i>{{ trans('app.general_settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-primary">{{ trans('app.Required') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label required" for="site_name">
                                    <i class="fas fa-globe text-blue-500 me-1"></i>{{ trans('app.site_name') }}
                                </label>
                                <input type="text" id="site_name" name="site_name" class="admin-form-input"
                                    value="{{ old('site_name', $settingsArray['site_name'] ?? 'Lic') }}" required
                                    placeholder="{{ trans('app.enter_site_name') }}">
                                @error('site_name')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="support_email">
                                    <i class="fas fa-envelope text-green-500 me-1"></i>{{ trans('app.support_email') }}
                                </label>
                                <input type="email" id="support_email" name="support_email" class="admin-form-input"
                                    value="{{ old('support_email', $settingsArray['support_email'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_support_email') }}">
                                @error('support_email')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="avg_response_time">
                                    <i class="fas fa-clock text-orange-500 me-1"></i>{{ trans('app.avg_response_time') }}
                                </label>
                                <input type="number" id="avg_response_time" name="avg_response_time" class="admin-form-input"
                                    value="{{ old('avg_response_time', $settingsArray['avg_response_time'] ?? 24) }}"
                                    min="1" max="168" placeholder="24">
                                <p class="admin-form-help">{{ trans('app.avg_response_time_help') }}</p>
                                @error('avg_response_time')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="support_phone">
                                    <i class="fas fa-phone text-purple-500 me-1"></i>{{ trans('app.support_phone') }}
                                </label>
                                <input type="text" id="support_phone" name="support_phone" class="admin-form-input"
                                    value="{{ old('support_phone', $settingsArray['support_phone'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_support_phone') }}">
                                @error('support_phone')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label required" for="timezone">
                                    <i class="fas fa-clock text-orange-500 me-1"></i>{{ trans('app.timezone') }}
                                </label>
                                <select id="timezone" name="timezone" class="admin-form-input" required>
                                    @foreach(\DateTimeZone::listIdentifiers() as $tz)
                                    <option value="{{ $tz }}" {{ $tz === $currentTimezone ? 'selected' : '' }}>
                                        {{ $tz }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-tools text-gray-500 me-1"></i>{{ trans('app.maintenance_mode') }}
                                </label>
                                <div class="admin-switch">
                                    <input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1"
                                        @checked(old('maintenance_mode', $settingsArray['maintenance_mode'] ?? false)) class="admin-switch-input">
                                    <label for="maintenance_mode" class="admin-switch-label">
                                        <span class="admin-switch-inner"></span>
                                        <span class="admin-switch-switch"></span>
                                    </label>
                                </div>
                                <p class="admin-form-help">{{ trans('app.put_the_site_in_maintenance_mode') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Settings Tab -->
        <div class="admin-tab-panel admin-tab-panel-hidden" id="seo-tab" role="tabpanel" aria-labelledby="seo-tab">
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-search text-indigo-500 me-2"></i>{{ trans('app.SEO') }}
                    </h3>
                    <span class="admin-badge admin-badge-info">{{ trans('app.Optional') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="seo_site_title">{{ trans('app.SEO Site Title') }}</label>
                                <input type="text" id="seo_site_title" name="seo_site_title" class="admin-form-input"
                                    value="{{ old('seo_site_title', $settingsArray['seo_site_title'] ?? '') }}"
                                    maxlength="255" placeholder="{{ trans('app.SEO Title Placeholder') }}">
                                @error('seo_site_title')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="seo_site_description">{{ trans('app.SEO Site Description') }}</label>
                                <input type="text" id="seo_site_description" name="seo_site_description"
                                    class="admin-form-input"
                                    value="{{ old('seo_site_description', $settingsArray['seo_site_description'] ?? '') }}"
                                    maxlength="500" placeholder="{{ trans('app.SEO Description Placeholder') }}">
                                @error('seo_site_description')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="seo_og_image">{{ trans('app.SEO OG Image') }}</label>
                                <input type="file" id="seo_og_image" name="seo_og_image" class="admin-form-input">
                                @if(!empty($settingsArray['seo_og_image']))
                                <div class="mt-3">
                                    <img src="{{ asset('storage/' . $settingsArray['seo_og_image']) }}" alt="OG Image"
                                        class="admin-image-preview">
                                </div>
                                @endif
                                @error('seo_og_image')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="seo_kb_title">{{ trans('app.KB SEO Title') }}</label>
                                <input type="text" id="seo_kb_title" name="seo_kb_title" class="admin-form-input"
                                    value="{{ old('seo_kb_title', $settingsArray['seo_kb_title'] ?? '') }}"
                                    maxlength="255">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="seo_kb_description">{{ trans('app.KB SEO Description') }}</label>
                                <input type="text" id="seo_kb_description" name="seo_kb_description"
                                    class="admin-form-input"
                                    value="{{ old('seo_kb_description', $settingsArray['seo_kb_description'] ?? '') }}"
                                    maxlength="500">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="seo_tickets_title">{{ trans('app.Tickets SEO Title') }}</label>
                                <input type="text" id="seo_tickets_title" name="seo_tickets_title"
                                    class="admin-form-input"
                                    value="{{ old('seo_tickets_title', $settingsArray['seo_tickets_title'] ?? '') }}"
                                    maxlength="255">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="seo_tickets_description">{{ trans('app.Tickets SEO Description') }}</label>
                                <input type="text" id="seo_tickets_description" name="seo_tickets_description"
                                    class="admin-form-input"
                                    value="{{ old('seo_tickets_description', $settingsArray['seo_tickets_description'] ?? '') }}"
                                    maxlength="500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Analytics Settings -->
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-chart-line text-green-500 me-2"></i>{{ trans('app.Analytics Settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-info">{{ trans('app.Optional') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="analytics_google_analytics">
                                    <i class="fab fa-google text-blue-500 me-1"></i>{{ trans('app.Google Analytics ID') }}
                                </label>
                                <input type="text" id="analytics_google_analytics" name="analytics_google_analytics" class="admin-form-input"
                                    value="{{ old('analytics_google_analytics', $settingsArray['analytics_google_analytics'] ?? '') }}"
                                    placeholder="G-XXXXXXXXXX">
                                <small class="admin-form-help">{{ trans('app.Google Analytics Help') }}</small>
                                @error('analytics_google_analytics')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="analytics_google_tag_manager">
                                    <i class="fab fa-google text-red-500 me-1"></i>{{ trans('app.Google Tag Manager ID') }}
                                </label>
                                <input type="text" id="analytics_google_tag_manager" name="analytics_google_tag_manager" class="admin-form-input"
                                    value="{{ old('analytics_google_tag_manager', $settingsArray['analytics_google_tag_manager'] ?? '') }}"
                                    placeholder="GTM-XXXXXXX">
                                <small class="admin-form-help">{{ trans('app.Google Tag Manager Help') }}</small>
                                @error('analytics_google_tag_manager')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="analytics_facebook_pixel">
                                    <i class="fab fa-facebook text-blue-600 me-1"></i>{{ trans('app.Facebook Pixel ID') }}
                                </label>
                                <input type="text" id="analytics_facebook_pixel" name="analytics_facebook_pixel" class="admin-form-input"
                                    value="{{ old('analytics_facebook_pixel', $settingsArray['analytics_facebook_pixel'] ?? '') }}"
                                    placeholder="123456789012345">
                                <small class="admin-form-help">{{ trans('app.Facebook Pixel Help') }}</small>
                                @error('analytics_facebook_pixel')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Envato API Settings Tab -->
        <div class="admin-tab-panel admin-tab-panel-hidden" id="envato-api-tab" role="tabpanel" aria-labelledby="envato-api-tab">
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-plug text-green-500 me-2"></i>{{ trans('app.envato_api_settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-info">{{ trans('app.Optional') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="admin-alert admin-alert-info mb-4">
                        <div class="admin-alert-content">
                            <i class="fas fa-info-circle admin-alert-icon"></i>
                            <div class="admin-alert-text">
                                <p>{{ trans('app.configure_envato_api_credentials_for_license_verification_and_purchase_validation') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="envato_personal_token">
                                    <i class="fas fa-key text-orange-500 me-1"></i>{{ trans('app.envato_personal_token') }}
                                </label>
                                <input type="password" id="envato_personal_token" name="envato_personal_token"
                                    class="admin-form-input"
                                    value="{{ old('envato_personal_token', $settingsArray['envato_personal_token'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_envato_personal_token') }}">
                                <p class="admin-form-help">
                                    {{ trans('app.required_for_verifying_purchases_and_licenses_get_from') }}
                                    <a href="https://build.envato.com/my-apps/" target="_blank"
                                        class="text-primary">{{ trans('app.envato_my_apps') }}</a>
                                </p>
                                @error('envato_personal_token')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="envato_api_key">
                                    <i class="fas fa-key text-purple-500 me-1"></i>{{ trans('app.envato_api_key') }}
                                </label>
                                <input type="password" id="envato_api_key" name="envato_api_key"
                                    class="admin-form-input"
                                    value="{{ old('envato_api_key', $settingsArray['envato_api_key'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_envato_api_key') }}">
                                <p class="admin-form-help">{{ trans('app.optional_api_key_for_additional_envato_services') }}</p>
                                @error('envato_api_key')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="button" id="test-api-btn" class="admin-btn admin-btn-info admin-btn-m"
                                data-action="test-envato-api">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ trans('app.test_api_connection') }}
                            </button>
                            <noscript>
                                <div class="admin-alert admin-alert-warning mt-2">
                                    <div class="admin-alert-content">
                                        <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
                                        <div class="admin-alert-text">
                                            <h4>{{ trans('app.javascript_required') }}</h4>
                                            <p>{{ trans('app.api_testing_requires_javascript') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </noscript>
                            <div id="api-test-result" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OAuth Settings Tab -->
        <div class="admin-tab-panel admin-tab-panel-hidden" id="oauth-tab" role="tabpanel" aria-labelledby="oauth-tab">
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-sign-in-alt text-indigo-500 me-2"></i>{{ trans('app.envato_oauth_settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-info">{{ trans('app.Optional') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="admin-alert admin-alert-info mb-4">
                        <div class="admin-alert-content">
                            <i class="fas fa-info-circle admin-alert-icon"></i>
                            <div class="admin-alert-text">
                                <p>{{ trans('app.configure_oauth_for_user_authentication_and_account_linking') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="envato_client_id">
                                    <i class="fas fa-id-card text-blue-500 me-1"></i>{{ trans('app.envato_client_id') }}
                                </label>
                                <input type="password" id="envato_client_id" name="envato_client_id"
                                    class="admin-form-input"
                                    value="{{ old('envato_client_id', $settingsArray['envato_client_id'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_envato_client_id') }}">
                                <p class="admin-form-help">{{ trans('app.oauth_client_id_from_your_envato_app') }}</p>
                                @error('envato_client_id')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="envato_client_secret">
                                    <i class="fas fa-lock text-red-500 me-1"></i>{{ trans('app.envato_client_secret') }}
                                </label>
                                <input type="password" id="envato_client_secret" name="envato_client_secret"
                                    class="admin-form-input"
                                    value="{{ old('envato_client_secret', $settingsArray['envato_client_secret'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_envato_client_secret') }}">
                                <p class="admin-form-help">{{ trans('app.oauth_client_secret_from_your_envato_app') }}</p>
                                @error('envato_client_secret')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="envato_redirect_uri">
                                    <i class="fas fa-link text-green-500 me-1"></i>{{ trans('app.envato_redirect_uri') }}
                                </label>
                                <input type="text" id="envato_redirect_uri" name="envato_redirect_uri"
                                    class="admin-form-input"
                                    value="{{ old('envato_redirect_uri', $settingsArray['envato_redirect_uri'] ?? config('app.url') . '/auth/envato/callback') }}"
                                    placeholder="{{ trans('app.enter_envato_redirect_uri') }}">
                                <p class="admin-form-help">{{ trans('app.oauth_redirect_uri_automatically_set_to_your_apps_callback_url') }}</p>
                                @error('envato_redirect_uri')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-toggle-on text-purple-500 me-1"></i>{{ trans('app.enable_envato_oauth') }}
                                </label>
                                <div class="admin-switch">
                                    <input type="checkbox" id="envato_oauth_enabled" name="envato_oauth_enabled"
                                        value="1" @checked(old('envato_oauth_enabled', $settingsArray['envato_oauth_enabled'] ?? false)) class="admin-switch-input">
                                    <label for="envato_oauth_enabled" class="admin-switch-label">
                                        <span class="admin-switch-inner"></span>
                                        <span class="admin-switch-switch"></span>
                                    </label>
                                </div>
                                <p class="admin-form-help">{{ trans('app.allow_users_to_login_and_link_their_envato_accounts') }}</p>
                            </div>
                        </div>

                        <div class="col-12">
                            <a href="{{ route('admin.envato-guide') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                                <i class="fas fa-book me-2"></i>
                                {{ trans('app.how_to_create_envato_app') }}
                            </a>
                            <p class="admin-form-help">{{ trans('app.learn_how_to_create_and_configure_your_envato_oauth_application') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security / Anti-Spam Settings Tab -->
        <div class="admin-tab-panel admin-tab-panel-hidden" id="security-tab" role="tabpanel" aria-labelledby="security-tab">
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-shield-alt text-red-500 me-2"></i>{{ trans('app.security_antispam') ?? 'Security / Anti-Spam' }}
                    </h3>
                    <span class="admin-badge admin-badge-info">{{ trans('app.Optional') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="enable_captcha">{{ trans('app.enable_captcha') ?? 'Enable Google reCAPTCHA' }}</label>
                                <div class="admin-switch">
                                    <input type="checkbox" id="enable_captcha" name="enable_captcha" value="1" @checked(old('enable_captcha', $settingsArray['enable_captcha'] ?? false)) class="admin-switch-input">
                                    <label for="enable_captcha" class="admin-switch-label">
                                        <span class="admin-switch-inner"></span>
                                        <span class="admin-switch-switch"></span>
                                    </label>
                                </div>
                                @error('enable_captcha')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="captcha_site_key">{{ trans('app.captcha_site_key') ?? 'Captcha Site Key' }}</label>
                                <input type="text" id="captcha_site_key" name="captcha_site_key" class="admin-form-input" value="{{ old('captcha_site_key', $settingsArray['captcha_site_key'] ?? '') }}" placeholder="">
                                @error('captcha_site_key')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="captcha_secret_key">{{ trans('app.captcha_secret_key') ?? 'Captcha Secret Key' }}</label>
                                <input type="password" id="captcha_secret_key" name="captcha_secret_key" class="admin-form-input" value="{{ old('captcha_secret_key', $settingsArray['captcha_secret_key'] ?? '') }}" placeholder="">
                                @error('captcha_secret_key')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="enable_human_question">{{ trans('app.enable_human_question') ?? 'Enable Human Question' }}</label>
                                <div class="admin-switch">
                                    <input type="checkbox" id="enable_human_question" name="enable_human_question" value="1" @checked(old('enable_human_question', $settingsArray['enable_human_question'] ?? true)) class="admin-switch-input">
                                    <label for="enable_human_question" class="admin-switch-label">
                                        <span class="admin-switch-inner"></span>
                                        <span class="admin-switch-switch"></span>
                                    </label>
                                </div>
                                @error('enable_human_question')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="human_questions_list">{{ trans('app.human_questions') ?? 'Human Questions' }}</label>
                                <div id="human-questions-list">

                                    @if(!empty($existingQuestions))
                                        @foreach($existingQuestions as $i => $q)
                                        <div class="human-question-row mb-3" data-index="{{ $i }}">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-7">
                                                    <input type="text" name="human_questions[{{ $i }}][question]" class="admin-form-input" value="{{ $q['question'] ?? '' }}" placeholder="{{ trans('app.Question') }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" name="human_questions[{{ $i }}][answer]" class="admin-form-input" value="{{ $q['answer'] ?? '' }}" placeholder="{{ trans('app.Answer') }}">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="admin-btn admin-btn-danger btn-remove-question">&times;</button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="human-question-row mb-3" data-index="0">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-7">
                                                    <input type="text" name="human_questions[0][question]" class="admin-form-input" value="What is 2 + 3?" placeholder="{{ trans('app.Question') }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" name="human_questions[0][answer]" class="admin-form-input" value="5" placeholder="{{ trans('app.Answer') }}">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="admin-btn admin-btn-danger btn-remove-question">&times;</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-3">
                                    <button type="button" id="btn-add-question" class="admin-btn admin-btn-primary">{{ trans('app.add_question') ?? 'Add question' }}</button>
                                </div>

                                <p class="admin-form-help">{{ trans('app.human_questions_help') ?? 'Add one or more simple question/answer pairs to reduce automated signups.' }}</p>
                                @error('human_questions')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- License Settings Tab -->
        <div class="admin-tab-panel admin-tab-panel-hidden" id="license-tab" role="tabpanel" aria-labelledby="license-tab">
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-shield-alt text-red-500 me-2"></i>{{ trans('app.license_settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-primary">{{ trans('app.Required') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label required" for="license_max_attempts">
                                    <i class="fas fa-repeat text-orange-500 me-1"></i>{{ trans('app.max_verification_attempts') }}
                                </label>
                                <input type="number" id="license_max_attempts" name="license_max_attempts"
                                    class="admin-form-input"
                                    value="{{ old('license_max_attempts', $settingsArray['license_max_attempts'] ?? 5) }}"
                                    min="1" required placeholder="5">
                                <p class="admin-form-help">{{ trans('app.maximum_number_of_verification_attempts_allowed_per_license') }}</p>
                                @error('license_max_attempts')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label required" for="license_lockout_minutes">
                                    <i class="fas fa-clock text-blue-500 me-1"></i>{{ trans('app.lockout_duration_minutes') }}
                                </label>
                                <input type="number" id="license_lockout_minutes" name="license_lockout_minutes"
                                    class="admin-form-input"
                                    value="{{ old('license_lockout_minutes', $settingsArray['license_lockout_minutes'] ?? 15) }}"
                                    min="1" required placeholder="15">
                                <p class="admin-form-help">{{ trans('app.how_long_to_lock_out_verification_attempts_after_max_attempts_reached') }}</p>
                                @error('license_lockout_minutes')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="license_api_token">
                                    <i class="fas fa-key text-purple-500 me-1"></i>{{ trans('app.license_api_token') }}
                                </label>
                                <div class="input-group">
                                <input type="text" id="license_api_token" name="license_api_token"
                                    class="admin-form-input"
                                    value="{{ old('license_api_token', $settingsArray['license_api_token'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_license_api_token') }}"
                                    class="cursor-text">
                                    <button type="button" class="admin-btn admin-btn-secondary" id="generate-api-token">
                                        <i class="fas fa-sync-alt me-1"></i>{{ trans('app.generate_new_token') }}
                                    </button>
                                </div>
                                <p class="admin-form-help">{{ trans('app.license_api_token_description') }}</p>
                                @error('license_api_token')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preloader Settings Tab -->
        <div class="admin-tab-panel admin-tab-panel-hidden" id="preloader-tab" role="tabpanel" aria-labelledby="preloader-tab">
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-spinner text-purple-500 me-2"></i>{{ trans('app.preloader_settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-info">{{ trans('app.Optional') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <!-- Preloader Enable/Disable -->
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-toggle-on me-2"></i>
                                    {{ trans('app.enable_preloader') }}
                                </label>
                                <div class="admin-switch">
                                    <input type="checkbox" name="preloader_enabled" value="1"
                                           {{ old('preloader_enabled', $settingsArray['preloader_enabled'] ?? false) ? 'checked' : '' }}
                                           class="admin-switch-input" id="preloader_enabled">
                                    <label for="preloader_enabled" class="admin-switch-label">
                                        <span class="admin-switch-inner"></span>
                                        <span class="admin-switch-switch"></span>
                                    </label>
                                </div>
                                <p class="admin-form-help">{{ trans('app.show_preloader_on_page_load') }}</p>
                            </div>
                        </div>

                        <!-- Preloader Type -->
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-palette me-2"></i>
                                    {{ trans('app.preloader_type') }}
                                </label>
                                <select name="preloader_type" class="admin-form-input">
                                    <option value="spinner" {{ old('preloader_type', $settingsArray['preloader_type'] ?? 'spinner') === 'spinner' ? 'selected' : '' }}>
                                        {{ trans('app.spinner') }}
                                    </option>
                                    <option value="dots" {{ old('preloader_type', $settingsArray['preloader_type'] ?? 'spinner') === 'dots' ? 'selected' : '' }}>
                                        {{ trans('app.dots') }}
                                    </option>
                                    <option value="bars" {{ old('preloader_type', $settingsArray['preloader_type'] ?? 'spinner') === 'bars' ? 'selected' : '' }}>
                                        {{ trans('app.bars') }}
                                    </option>
                                    <option value="pulse" {{ old('preloader_type', $settingsArray['preloader_type'] ?? 'spinner') === 'pulse' ? 'selected' : '' }}>
                                        {{ trans('app.pulse') }}
                                    </option>
                                    <option value="progress" {{ old('preloader_type', $settingsArray['preloader_type'] ?? 'spinner') === 'progress' ? 'selected' : '' }}>
                                        {{ trans('app.progress_bar') }}
                                    </option>
                                    <option value="custom" {{ old('preloader_type', $settingsArray['preloader_type'] ?? 'spinner') === 'custom' ? 'selected' : '' }}>
                                        {{ trans('app.custom') }}
                                    </option>
                                </select>
                                <p class="admin-form-help">{{ trans('app.choose_preloader_animation_style') }}</p>
                            </div>
                        </div>

                        <!-- Preloader Color -->
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-fill-drip me-2"></i>
                                    {{ trans('app.preloader_color') }}
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" name="preloader_color"
                                           value="{{ old('preloader_color', $settingsArray['preloader_color'] ?? '#3b82f6') }}"
                                           class="admin-form-color">
                                    <input type="text" name="preloader_color_text"
                                           value="{{ old('preloader_color', $settingsArray['preloader_color'] ?? '#3b82f6') }}"
                                           class="admin-form-input" placeholder="#3b82f6">
                                </div>
                                <p class="admin-form-help">{{ trans('app.primary_color_for_preloader_animation') }}</p>
                            </div>
                        </div>

                        <!-- Background Color -->
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-paint-brush me-2"></i>
                                    {{ trans('app.background_color') }}
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" name="preloader_background_color"
                                           value="{{ old('preloader_background_color', $settingsArray['preloader_background_color'] ?? '#ffffff') }}"
                                           class="admin-form-color">
                                    <input type="text" name="preloader_background_color_text"
                                           value="{{ old('preloader_background_color', $settingsArray['preloader_background_color'] ?? '#ffffff') }}"
                                           class="admin-form-input" placeholder="#ffffff">
                                </div>
                                <p class="admin-form-help">{{ trans('app.background_color_for_preloader') }}</p>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-clock me-2"></i>
                                    {{ trans('app.duration_milliseconds') }}
                                </label>
                                <input type="number" name="preloader_duration"
                                       value="{{ old('preloader_duration', $settingsArray['preloader_duration'] ?? 2000) }}"
                                       class="admin-form-input" min="500" max="10000" step="100">
                                <p class="admin-form-help">{{ trans('app.how_long_to_show_preloader') }}</p>
                            </div>
                        </div>

                        <!-- Custom CSS -->
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-code me-2"></i>
                                    {{ trans('app.custom_css') }}
                                </label>
                                <textarea name="preloader_custom_css" rows="6"
                                          class="admin-form-textarea"
                                          placeholder="{{ trans('app.enter_custom_css_for_preloader') }}">{{ old('preloader_custom_css', $settingsArray['preloader_custom_css'] ?? '') }}</textarea>
                                <p class="admin-form-help">{{ trans('app.add_custom_styles_for_preloader') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="admin-card mt-4">
                        <div class="admin-section-content">
                            <h4 class="admin-card-title">{{ trans('app.preview') }}</h4>
                        </div>
                        <div class="admin-card-content">
                            <div class="text-center">
                                <button type="button" id="preview-preloader" class="admin-btn admin-btn-secondary">
                                    <i class="fas fa-eye me-2"></i>
                                    {{ trans('app.preview_preloader') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logo Settings Tab -->
        <div class="admin-tab-panel admin-tab-panel-hidden" id="logo-tab" role="tabpanel" aria-labelledby="logo-tab">
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-image text-green-500 me-2"></i>{{ trans('app.logo_settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-info">{{ trans('app.Optional') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <!-- Site Logo -->
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-image me-2"></i>
                                    {{ trans('app.site_logo') }}
                                </label>
                                <div class="admin-file-upload">
                                    <input type="file" name="site_logo" accept="image/*" class="admin-file-input" id="site_logo">
                                    <label for="site_logo" class="admin-file-label">
                                        <i class="fas fa-upload me-2"></i>
                                        {{ trans('app.choose_logo_file') }}
                                    </label>
                                </div>
                                @if(!empty($settingsArray['site_logo']))
                                    <div class="mt-3">
                                        <img src="{{ asset('storage/' . $settingsArray['site_logo']) }}"
                                             alt="{{ trans('app.current_logo') }}"
                                             class="admin-image-preview">
                                        <p class="text-muted mt-1">{{ trans('app.current_logo') }}</p>
                                    </div>
                                @endif
                                <p class="admin-form-help">{{ trans('app.upload_site_logo_recommended_size') }}</p>
                            </div>
                        </div>

                        <!-- Dark Mode Logo -->
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-moon me-2"></i>
                                    {{ trans('app.dark_mode_logo') }}
                                </label>
                                <div class="admin-file-upload">
                                    <input type="file" name="site_logo_dark" accept="image/*" class="admin-file-input" id="site_logo_dark">
                                    <label for="site_logo_dark" class="admin-file-label">
                                        <i class="fas fa-upload me-2"></i>
                                        {{ trans('app.choose_dark_logo_file') }}
                                    </label>
                                </div>
                                @if(!empty($settingsArray['site_logo_dark']))
                                    <div class="mt-3">
                                        <img src="{{ asset('storage/' . $settingsArray['site_logo_dark']) }}"
                                             alt="{{ trans('app.current_dark_logo') }}"
                                             class="admin-image-preview">
                                        <p class="text-muted mt-1">{{ trans('app.current_dark_logo') }}</p>
                                    </div>
                                @endif
                                <p class="admin-form-help">{{ trans('app.upload_dark_mode_logo_optional') }}</p>
                            </div>
                        </div>

                        <!-- Logo Dimensions -->
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-expand-arrows-alt me-2"></i>
                                    {{ trans('app.logo_width') }}
                                </label>
                                <input type="number" name="logo_width"
                                       value="{{ old('logo_width', $settingsArray['logo_width'] ?? 150) }}"
                                       class="admin-form-input" min="50" max="500">
                                <p class="admin-form-help">{{ trans('app.logo_width_in_pixels') }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-expand-arrows-alt me-2"></i>
                                    {{ trans('app.logo_height') }}
                                </label>
                                <input type="number" name="logo_height"
                                       value="{{ old('logo_height', $settingsArray['logo_height'] ?? 50) }}"
                                       class="admin-form-input" min="20" max="200">
                                <p class="admin-form-help">{{ trans('app.logo_height_in_pixels') }}</p>
                            </div>
                        </div>

                        <!-- Logo Text Settings -->
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-font me-2"></i>
                                    {{ trans('app.show_logo_text') }}
                                </label>
                                <div class="admin-switch">
                                    <input type="checkbox" name="logo_show_text" value="1"
                                           {{ old('logo_show_text', $settingsArray['logo_show_text'] ?? false) ? 'checked' : '' }}
                                           class="admin-switch-input" id="logo_show_text">
                                    <label for="logo_show_text" class="admin-switch-label">
                                        <span class="admin-switch-inner"></span>
                                        <span class="admin-switch-switch"></span>
                                    </label>
                                </div>
                                <p class="admin-form-help">{{ trans('app.show_text_next_to_logo') }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-text-width me-2"></i>
                                    {{ trans('app.logo_text') }}
                                </label>
                                <input type="text" name="logo_text"
                                       value="{{ old('logo_text', $settingsArray['logo_text'] ?? config('app.name')) }}"
                                       class="admin-form-input" placeholder="{{ config('app.name') }}">
                                <p class="admin-form-help">{{ trans('app.text_to_display_with_logo') }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-fill-drip me-2"></i>
                                    {{ trans('app.logo_text_color') }}
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" name="logo_text_color"
                                           value="{{ old('logo_text_color', $settingsArray['logo_text_color'] ?? '#1f2937') }}"
                                           class="admin-form-color">
                                    <input type="text" name="logo_text_color_text"
                                           value="{{ old('logo_text_color', $settingsArray['logo_text_color'] ?? '#1f2937') }}"
                                           class="admin-form-input" placeholder="#1f2937">
                                </div>
                                <p class="admin-form-help">{{ trans('app.color_for_logo_text') }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-text-height me-2"></i>
                                    {{ trans('app.logo_text_font_size') }}
                                </label>
                                <input type="text" name="logo_text_font_size"
                                       value="{{ old('logo_text_font_size', $settingsArray['logo_text_font_size'] ?? '24px') }}"
                                       class="admin-form-input" placeholder="24px">
                                <p class="admin-form-help">{{ trans('app.font_size_for_logo_text') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Logo Preview -->
                    <div class="admin-card mt-4">
                        <div class="admin-section-content">
                            <h4 class="admin-card-title">{{ trans('app.logo_preview') }}</h4>
                        </div>
                        <div class="admin-card-content">
                            <div class="text-center p-4 bg-light rounded">
                                <div id="logo-preview" class="admin-logo-preview" 
                                     data-logo-width="{{ $settingsArray['logo_width'] ?? 150 }}"
                                     data-logo-height="{{ $settingsArray['logo_height'] ?? 50 }}"
                                     data-logo-text-color="{{ $settingsArray['logo_text_color'] ?? '#1f2937' }}"
                                     data-logo-text-font-size="{{ $settingsArray['logo_text_font_size'] ?? '24px' }}">
                                    @if(!empty($settingsArray['site_logo']))
                                        <img src="{{ asset('storage/' . $settingsArray['site_logo']) }}"
                                             alt="{{ $settingsArray['logo_text'] ?? config('app.name') }}"
                                             class="admin-logo-preview-image">
                                    @endif
                                    @if($settingsArray['logo_show_text'] ?? false)
                                        <span class="admin-logo-preview-text">
                                            {{ $settingsArray['logo_text'] ?? config('app.name') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced License Settings Tab -->
        <div class="admin-tab-panel admin-tab-panel-hidden" id="advanced-license-tab" role="tabpanel" aria-labelledby="advanced-license-tab">
            <!-- License Verification Settings -->
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-shield-alt text-green-500 me-2"></i>{{ trans('app.license_verification_settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-success">{{ trans('app.Advanced') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-check-circle text-green-500 me-1"></i>
                                    {{ trans('app.verify_with_envato') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_verify_envato" name="license_verify_envato" value="1" {{ old('license_verify_envato', $settings->license_verify_envato ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_verify_envato"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.verify_with_envato_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-database text-blue-500 me-1"></i>
                                    {{ trans('app.fallback_to_internal') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_fallback_internal" name="license_fallback_internal" value="1" {{ old('license_fallback_internal', $settings->license_fallback_internal ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_fallback_internal"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.fallback_to_internal_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-clock text-orange-500 me-1"></i>
                                    {{ trans('app.cache_duration_minutes') }}
                                </label>
                                <input type="number" class="admin-form-input" id="license_cache_duration" name="license_cache_duration" value="{{ old('license_cache_duration', $settings->license_cache_duration ?? 60) }}" min="1" max="1440">
                                <div class="admin-form-help">{{ trans('app.cache_duration_minutes_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-calendar text-purple-500 me-1"></i>
                                    {{ trans('app.grace_period_days') }}
                                </label>
                                <input type="number" class="admin-form-input" id="license_grace_period" name="license_grace_period" value="{{ old('license_grace_period', $settings->license_grace_period ?? 7) }}" min="0" max="30">
                                <div class="admin-form-help">{{ trans('app.grace_period_days_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Domain Management Settings -->
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-globe text-blue-500 me-2"></i>{{ trans('app.domain_management_settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-info">{{ trans('app.Domain') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-home text-green-500 me-1"></i>
                                    {{ trans('app.allow_localhost') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_allow_localhost" name="license_allow_localhost" value="1" {{ old('license_allow_localhost', $settings->license_allow_localhost ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_allow_localhost"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.allow_localhost_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-asterisk text-orange-500 me-1"></i>
                                    {{ trans('app.allow_wildcards') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_allow_wildcards" name="license_allow_wildcards" value="1" {{ old('license_allow_wildcards', $settings->license_allow_wildcards ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_allow_wildcards"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.allow_wildcards_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-plus-circle text-green-500 me-1"></i>
                                    {{ trans('app.auto_register_domains') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_auto_register_domains" name="license_auto_register_domains" value="1" {{ old('license_auto_register_domains', $settings->license_auto_register_domains ?? false) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_auto_register_domains"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.auto_register_domains_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-hashtag text-purple-500 me-1"></i>
                                    {{ trans('app.max_domains_per_license') }}
                                </label>
                                <input type="number" class="admin-form-input" id="license_max_domains" name="license_max_domains" value="{{ old('license_max_domains', $settings->license_max_domains ?? 5) }}" min="1" max="100">
                                <div class="admin-form-help">{{ trans('app.max_domains_per_license_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-clock text-red-500 me-1"></i>
                                    {{ trans('app.domain_change_cooldown_hours') }}
                                </label>
                                <input type="number" class="admin-form-input" id="license_domain_cooldown" name="license_domain_cooldown" value="{{ old('license_domain_cooldown', $settings->license_domain_cooldown ?? 24) }}" min="1" max="168">
                                <div class="admin-form-help">{{ trans('app.domain_change_cooldown_hours_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-lock text-red-500 me-2"></i>{{ trans('app.security_settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-danger">{{ trans('app.Security') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-shield-alt text-green-500 me-1"></i>
                                    {{ trans('app.encrypt_license_data') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_encrypt_data" name="license_encrypt_data" value="1" {{ old('license_encrypt_data', $settings->license_encrypt_data ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_encrypt_data"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.encrypt_license_data_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-key text-blue-500 me-1"></i>
                                    {{ trans('app.use_secure_tokens') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_secure_tokens" name="license_secure_tokens" value="1" {{ old('license_secure_tokens', $settings->license_secure_tokens ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_secure_tokens"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.use_secure_tokens_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-ban text-red-500 me-1"></i>
                                    {{ trans('app.prevent_license_sharing') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_prevent_sharing" name="license_prevent_sharing" value="1" {{ old('license_prevent_sharing', $settings->license_prevent_sharing ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_prevent_sharing"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.prevent_license_sharing_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-exclamation-triangle text-orange-500 me-1"></i>
                                    {{ trans('app.detect_suspicious_activity') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_detect_suspicious" name="license_detect_suspicious" value="1" {{ old('license_detect_suspicious', $settings->license_detect_suspicious ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_detect_suspicious"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.detect_suspicious_activity_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-bell text-yellow-500 me-2"></i>{{ trans('app.notification_settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-warning">{{ trans('app.Notifications') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-envelope text-blue-500 me-1"></i>
                                    {{ trans('app.notification_email') }}
                                </label>
                                <input type="email" class="admin-form-input" id="license_notification_email" name="license_notification_email" value="{{ old('license_notification_email', $settings->license_notification_email ?? '') }}" placeholder="admin@example.com">
                                <div class="admin-form-help">{{ trans('app.notification_email_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-calendar-times text-red-500 me-1"></i>
                                    {{ trans('app.notify_on_expiration') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_notify_expiration" name="license_notify_expiration" value="1" {{ old('license_notify_expiration', $settings->license_notify_expiration ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_notify_expiration"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.notify_on_expiration_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-globe text-green-500 me-1"></i>
                                    {{ trans('app.notify_on_domain_change') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_notify_domain_change" name="license_notify_domain_change" value="1" {{ old('license_notify_domain_change', $settings->license_notify_domain_change ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_notify_domain_change"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.notify_on_domain_change_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label">
                                    <i class="fas fa-exclamation-triangle text-orange-500 me-1"></i>
                                    {{ trans('app.notify_on_suspicious_activity') }}
                                </label>
                                <div class="admin-form-switch">
                                    <input type="checkbox" class="admin-form-switch-input" id="license_notify_suspicious" name="license_notify_suspicious" value="1" {{ old('license_notify_suspicious', $settings->license_notify_suspicious ?? true) ? 'checked' : '' }}>
                                    <label class="admin-form-switch-label" for="license_notify_suspicious"></label>
                                </div>
                                <div class="admin-form-help">{{ trans('app.notify_on_suspicious_activity_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Tab -->
        <div class="admin-tab-panel admin-tab-panel-hidden" id="contact-tab" role="tabpanel" aria-labelledby="contact-tab">
            <div class="admin-card mb-4">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-address-book text-blue-500 me-2"></i>{{ trans('app.contact_information') }}
                    </h3>
                    <span class="admin-badge admin-badge-info">{{ trans('app.Optional') }}</span>
                </div>
                <div class="admin-card-content">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="contact_phone">
                                    <i class="fas fa-phone text-green-500 me-1"></i>{{ trans('app.contact_phone') }}
                                </label>
                                <input type="text" id="contact_phone" name="contact_phone" class="admin-form-input"
                                    value="{{ old('contact_phone', $settingsArray['contact_phone'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_contact_phone') }}">
                                @error('contact_phone')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="contact_city">
                                    <i class="fas fa-city text-purple-500 me-1"></i>{{ trans('app.contact_city') }}
                                </label>
                                <input type="text" id="contact_city" name="contact_city" class="admin-form-input"
                                    value="{{ old('contact_city', $settingsArray['contact_city'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_contact_city') }}">
                                @error('contact_city')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="contact_address">
                                    <i class="fas fa-map-marker-alt text-red-500 me-1"></i>{{ trans('app.contact_address') }}
                                </label>
                                <textarea id="contact_address" name="contact_address" class="admin-form-input" rows="3"
                                    placeholder="{{ trans('app.enter_contact_address') }}">{{ old('contact_address', $settingsArray['contact_address'] ?? '') }}</textarea>
                                @error('contact_address')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="contact_state">
                                    <i class="fas fa-map text-orange-500 me-1"></i>{{ trans('app.contact_state') }}
                                </label>
                                <input type="text" id="contact_state" name="contact_state" class="admin-form-input"
                                    value="{{ old('contact_state', $settingsArray['contact_state'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_contact_state') }}">
                                @error('contact_state')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="contact_country">
                                    <i class="fas fa-flag text-blue-500 me-1"></i>{{ trans('app.contact_country') }}
                                </label>
                                <input type="text" id="contact_country" name="contact_country" class="admin-form-input"
                                    value="{{ old('contact_country', $settingsArray['contact_country'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_contact_country') }}">
                                @error('contact_country')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="contact_postal_code">
                                    <i class="fas fa-mail-bulk text-green-500 me-1"></i>{{ trans('app.contact_postal_code') }}
                                </label>
                                <input type="text" id="contact_postal_code" name="contact_postal_code" class="admin-form-input"
                                    value="{{ old('contact_postal_code', $settingsArray['contact_postal_code'] ?? '') }}"
                                    placeholder="{{ trans('app.enter_contact_postal_code') }}">
                                @error('contact_postal_code')
                                <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="admin-card">
            <div class="admin-card-content">
                <div class="d-flex gap-3">
                    <button type="submit" class="admin-btn admin-btn-primary admin-btn-m">
                        <i class="fas fa-save me-2"></i>
                        {{ trans('app.save_settings') }}
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                        <i class="fas fa-times me-2"></i>
                        {{ trans('app.Cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
</div>

<style>
.admin-preloader-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
}

.admin-preloader-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}

.admin-preloader-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.admin-preloader-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

@endsection