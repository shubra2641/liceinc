@extends('install.layout', ['step' => 6])

@section('title', trans('install.settings_title'))

@section('content')
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-sliders-h"></i>
        </div>
        <h1 class="install-card-title">{{ trans('install.settings_title') }}</h1>
        <p class="install-card-subtitle">{{ trans('install.settings_subtitle') }}</p>
    </div>

    <form method="POST" action="{{ route('install.settings.store') }}" class="install-form" id="settings-form">
        @csrf
        
        <div class="install-card-body">
            <div class="form-group">
                <label for="site_name" class="form-label">
                    <i class="fas fa-globe"></i>
                    {{ trans('install.site_name') }}
                </label>
                <input type="text" 
                       id="site_name" 
                       name="site_name" 
                       class="form-input" 
                       value="{{ old('site_name', 'License Management System') }}" 
                       required>
                @error('site_name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="site_description" class="form-label">
                    <i class="fas fa-info-circle"></i>
                    {{ trans('install.site_description') }}
                </label>
                <textarea id="site_description" 
                          name="site_description" 
                          class="form-textarea" 
                          rows="3">{{ old('site_description', 'Professional license management and verification system') }}</textarea>
                @error('site_description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="admin_email" class="form-label">
                    <i class="fas fa-envelope"></i>
                    {{ trans('install.admin_email') }}
                </label>
                <input type="email" 
                       id="admin_email" 
                       name="admin_email" 
                       class="form-input" 
                       value="{{ old('admin_email') }}">
                @error('admin_email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">{{ trans('install.admin_email_hint') }}</div>
            </div>

            <div class="form-group">
                <label for="timezone" class="form-label">
                    <i class="fas fa-clock"></i>
                    {{ trans('install.timezone') }}
                </label>
                <select id="timezone" name="timezone" class="form-select" required>
                    @foreach($timezones as $value => $label)
                        <option value="{{ $value }}" {{ old('timezone', 'UTC') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('timezone')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="locale" class="form-label">
                    <i class="fas fa-language"></i>
                    {{ trans('install.default_language') }}
                </label>
                <select id="locale" name="locale" class="form-select" required>
                    <option value="en" {{ old('locale', app()->getLocale()) == 'en' ? 'selected' : '' }}>
                        English
                    </option>
                    <option value="ar" {{ old('locale', app()->getLocale()) == 'ar' ? 'selected' : '' }}>
                        العربية
                    </option>
                </select>
                @error('locale')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Configuration Section -->
            <div class="install-section">
                <h3 class="section-title">
                    <i class="fas fa-envelope"></i>
                    {{ trans('install.email_configuration') }}
                </h3>
                <p class="section-subtitle">{{ trans('install.email_configuration_subtitle') }}</p>
                
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" id="enable_email" name="enable_email" value="1" {{ old('enable_email') ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        {{ trans('install.enable_email_notifications') }}
                    </label>
                    <div class="form-hint">{{ trans('install.enable_email_hint') }}</div>
                    <noscript>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            {{ trans('install.javascript_required_for_email_settings') }}
                        </div>
                    </noscript>
                </div>

                <div id="email-settings" class="email-settings {{ old('enable_email') ? 'd-block' : 'd-none' }}">
                    <div class="form-group">
                        <label for="mail_mailer" class="form-label">
                            <i class="fas fa-server"></i>
                            {{ trans('install.mail_mailer') }}
                        </label>
                        <select id="mail_mailer" name="mail_mailer" class="form-select">
                            <option value="smtp" {{ old('mail_mailer', 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="mailgun" {{ old('mail_mailer') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                            <option value="ses" {{ old('mail_mailer') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                            <option value="postmark" {{ old('mail_mailer') == 'postmark' ? 'selected' : '' }}>Postmark</option>
                        </select>
                        @error('mail_mailer')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="mail_host" class="form-label">
                            <i class="fas fa-globe"></i>
                            {{ trans('install.mail_host') }}
                        </label>
                        <input type="text" 
                               id="mail_host" 
                               name="mail_host" 
                               class="form-input" 
                               value="{{ old('mail_host', 'smtp.gmail.com') }}" 
                               placeholder="smtp.gmail.com">
                        @error('mail_host')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="mail_port" class="form-label">
                                <i class="fas fa-plug"></i>
                                {{ trans('install.mail_port') }}
                            </label>
                            <input type="number" 
                                   id="mail_port" 
                                   name="mail_port" 
                                   class="form-input" 
                                   value="{{ old('mail_port', '587') }}" 
                                   placeholder="587">
                            @error('mail_port')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mail_encryption" class="form-label">
                                <i class="fas fa-lock"></i>
                                {{ trans('install.mail_encryption') }}
                            </label>
                            <select id="mail_encryption" name="mail_encryption" class="form-select">
                                <option value="tls" {{ old('mail_encryption', 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('mail_encryption') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ old('mail_encryption') == '' ? 'selected' : '' }}>None</option>
                            </select>
                            @error('mail_encryption')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mail_username" class="form-label">
                            <i class="fas fa-user"></i>
                            {{ trans('install.mail_username') }}
                        </label>
                        <input type="text" 
                               id="mail_username" 
                               name="mail_username" 
                               class="form-input" 
                               value="{{ old('mail_username') }}" 
                               placeholder="your-email@gmail.com">
                        @error('mail_username')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="mail_password" class="form-label">
                            <i class="fas fa-key"></i>
                            {{ trans('install.mail_password') }}
                        </label>
                        <input type="password" 
                               id="mail_password" 
                               name="mail_password" 
                               class="form-input" 
                               value="{{ old('mail_password') }}" 
                               placeholder="{{ trans('install.mail_password_placeholder') }}">
                        @error('mail_password')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">{{ trans('install.mail_password_hint') }}</div>
                    </div>

                    <div class="form-group">
                        <label for="mail_from_address" class="form-label">
                            <i class="fas fa-at"></i>
                            {{ trans('install.mail_from_address') }}
                        </label>
                        <input type="email" 
                               id="mail_from_address" 
                               name="mail_from_address" 
                               class="form-input" 
                               value="{{ old('mail_from_address') }}" 
                               placeholder="noreply@yourdomain.com">
                        @error('mail_from_address')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="mail_from_name" class="form-label">
                            <i class="fas fa-signature"></i>
                            {{ trans('install.mail_from_name') }}
                        </label>
                        <input type="text" 
                               id="mail_from_name" 
                               name="mail_from_name" 
                               class="form-input" 
                               value="{{ old('mail_from_name', 'License Management System') }}" 
                               placeholder="{{ trans('install.mail_from_name_placeholder') }}">
                        @error('mail_from_name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="install-actions">
            <a href="{{ route('install.admin') }}" class="install-btn install-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>{{ trans('install.back') }}</span>
            </a>
            
            <button type="submit" class="install-btn install-btn-primary">
                <i class="fas fa-arrow-right"></i>
                <span>{{ trans('install.continue') }}</span>
            </button>
        </div>
    </form>
</div>


@endsection
