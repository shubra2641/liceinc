@extends('layouts.user')

@section('title', trans('app.Sign In'))
@section('page-title', trans('app.Welcome Back'))
@section('page-subtitle', trans('app.Sign in to your account to continue'))
@section('app.Description', trans('app.Secure sign in to your account with email and password or Envato OAuth'))

@section('seo_title', $siteSeoTitle ?? trans('app.Sign In'))
@section('meta_description', $siteSeoDescription ?? trans('app.Secure sign in to your account with email and password or
Envato OAuth'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-sign-in-alt"></i>
                {{ trans('app.Welcome Back') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Sign in to your account to continue with our premium services') }}
            </p>
        </div>

        <div class="user-card-content">
            @if($fromInstall ?? false)
            <div class="installation-success-message">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="success-content">
                    <h3>{{ trans('install.installation_completed') }}!</h3>
                    <p>{{ trans('install.installation_success_message') }}</p>
                    <div class="success-details">
                        <p><i class="fas fa-database"></i> {{ trans('install.database_created') }}</p>
                        <p><i class="fas fa-users"></i> {{ trans('install.admin_account_created') }}</p>
                        <p><i class="fas fa-cog"></i> {{ trans('install.system_configured') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="register-grid">
                <!-- Main Login Form -->
                <div class="register-form-section">
                    <!-- Envato OAuth Login -->
                    @if(\App\Helpers\EnvatoHelper::isConfigured())
                    <div class="envato-auth-section">
                        <a href="{{ route('auth.envato') }}" class="envato-auth-button">
                            <i class="fas fa-external-link-alt"></i>
                            {{ trans('app.Continue with Envato') }}
                        </a>

                        <div class="auth-divider">
                            <div class="auth-divider-line"></div>
                            <span class="auth-divider-text">{{ trans('app.Or continue with email') }}</span>
                            <div class="auth-divider-line"></div>
                        </div>
                    </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" class="register-form" novalidate>
                        @csrf

                        <div class="form-fields-grid">
                            <!-- Email Address -->
                            <div class="form-field-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    {{ trans('app.Email Address') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="email" name="email" type="email"
                                        class="form-input @error('email') form-input-error @enderror"
                                        value="{{ old('email') }}" required autofocus autocomplete="username"
                                        placeholder="{{ trans('app.Enter your email address') }}" />
                                </div>
                                @error('email')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="form-field-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    {{ trans('app.Password') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="login-password" name="password" type="password"
                                        class="form-input @error('password') form-input-error @enderror" required
                                        autocomplete="current-password"
                                        placeholder="{{ trans('app.Enter your password') }}" />
                                    <button type="button" id="toggle-password" class="form-input-toggle">
                                        <i class="fas fa-eye" id="password-show"></i>
                                        <i class="fas fa-eye-slash hidden" id="password-hide"></i>
                                    </button>
                                </div>
                                @error('password')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="form-remember-section">
                            <div class="form-checkbox-wrapper">
                                <input id="remember_me" name="remember" type="checkbox" class="form-checkbox">
                                <label for="remember_me" class="form-checkbox-label">
                                    {{ trans('app.Remember me') }}
                                </label>
                            </div>

                            @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="form-link">
                                {{ trans('app.Forgot Password?') }}
                            </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="form-submit-button">
                            <span class="button-text">{{ trans('app.Sign In') }}</span>
                            <i class="fas fa-spinner fa-spin button-loading hidden"></i>
                        </button>
                    </form>

                    <!-- Register link -->
                    <div class="form-signin-link">
                        <p class="signin-text">
                            {{ trans("app.Don't have an account?") }}
                            <a href="{{ route('register') }}" class="signin-link">
                                {{ trans('app.Create one now') }}
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="register-sidebar">
                    <!-- Security Features -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-shield-alt"></i>
                                {{ trans('app.Security Features') }}
                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="benefits-list">
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title">{{ trans('app.Secure Login') }}</h4>
                                        <p class="benefit-description">{{ trans('app.Your credentials are encrypted and
                                            secure') }}</p>
                                    </div>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title">{{ trans('app.Two-Factor Authentication') }}</h4>
                                        <p class="benefit-description">{{ trans('app.Additional security layer for your
                                            account') }}</p>
                                    </div>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-desktop"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title">{{ trans('app.Session Management') }}</h4>
                                        <p class="benefit-description">{{ trans('app.Control your active sessions and
                                            devices') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="user-card help-card">
                        <div class="user-card-content">
                            <div class="help-content">
                                <div class="help-icon">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                <h4 class="help-title">
                                    {{ trans('app.Need Help?') }}
                                </h4>
                                <p class="help-description">
                                    {{ trans('app.Can\'t access your account?') }}
                                </p>
                                <a href="{{ route('password.request') }}" class="help-button">
                                    <i class="fas fa-key"></i>
                                    {{ trans('app.Reset Password') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection