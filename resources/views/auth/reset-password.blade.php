@extends('layouts.user')

@section('title', trans('app.Reset Password'))
@section('page-title', trans('app.Reset Your Password'))
@section('page-subtitle', trans('app.Enter your new password below'))
@section('app.Description', trans('app.Create a new secure password for your account'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-lock"></i>
                {{ trans('app.Reset Your Password') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Enter your new secure password below to complete the reset process') }}
            </p>
        </div>

        <div class="user-card-content">
            <div class="register-grid">
                <!-- Main Reset Form -->
                <div class="register-form-section">
                    <!-- Reset Information -->
                    <div class="reset-info-card">
                        <div class="reset-info-header">
                            <div class="reset-info-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="reset-info-title">{{ trans('app.Secure Password Reset') }}</h3>
                        </div>
                        <div class="reset-info-content">
                            <p class="reset-info-description">
                                {{ trans('app.Create a strong password that includes uppercase letters, lowercase letters, numbers, and special characters.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Reset Form -->
                    <form method="POST" action="{{ route('password.store') }}" class="register-form" novalidate>
                        @csrf

                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
                                        value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
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
                                    {{ trans('app.New Password') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="reset-password" name="password" type="password"
                                        class="form-input @error('password') form-input-error @enderror"
                                        required autocomplete="new-password"
                                        placeholder="{{ trans('app.Enter your new password') }}" />
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

                            <!-- Confirm Password -->
                            <div class="form-field-group">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    {{ trans('app.Confirm New Password') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="password_confirmation" name="password_confirmation" type="password"
                                        class="form-input @error('password_confirmation') form-input-error @enderror"
                                        required autocomplete="new-password"
                                        placeholder="{{ trans('app.Confirm your new password') }}" />
                                </div>
                                @error('password_confirmation')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="form-submit-button">
                            <span class="button-text">{{ trans('app.Reset Password') }}</span>
                            <i class="fas fa-spinner fa-spin button-loading hidden"></i>
                        </button>
                    </form>

                    <!-- Back to Login -->
                    <div class="form-signin-link">
                        <p class="signin-text">
                            {{ trans('app.Remember your password?') }}
                            <a href="{{ route('login') }}" class="signin-link">
                                <i class="fas fa-arrow-left"></i>
                                {{ trans('app.Back to Sign In') }}
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="register-sidebar">
                    <!-- Password Requirements -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-shield-alt"></i>
                                {{ trans('app.Password Requirements') }}
                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="password-requirements-list">
                                <div class="password-requirement-item">
                                    <div class="password-requirement-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="password-requirement-content">
                                        <h4 class="password-requirement-title">{{ trans('app.At least 8 characters long') }}</h4>
                                    </div>
                                </div>
                                <div class="password-requirement-item">
                                    <div class="password-requirement-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="password-requirement-content">
                                        <h4 class="password-requirement-title">{{ trans('app.Uppercase and lowercase letters') }}</h4>
                                    </div>
                                </div>
                                <div class="password-requirement-item">
                                    <div class="password-requirement-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="password-requirement-content">
                                        <h4 class="password-requirement-title">{{ trans('app.Numbers and special characters') }}</h4>
                                    </div>
                                </div>
                                <div class="password-requirement-item">
                                    <div class="password-requirement-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="password-requirement-content">
                                        <h4 class="password-requirement-title">{{ trans('app.Not easily guessable') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Security Tips -->
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800 p-6">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle w-8 h-8 text-amber-600 dark:text-amber-400 mx-auto mb-3"></i>
                        <h4 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                            {{ trans('app.Security Tips') }}
                        </h4>
                        <p class="text-slate-600 dark:text-slate-300 text-sm mb-4">
                            {{ trans('app.Use a unique password for this account') }}
                        </p>
                        <ul class="text-left text-sm text-slate-600 dark:text-slate-400 space-y-1">
                            <li>• {{ trans('app.Don\'t reuse passwords') }}</li>
                            <li>• {{ trans('app.Avoid personal information') }}</li>
                            <li>• {{ trans('app.Consider using a password manager') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-links')
    <div class="text-center">
        <a href="{{ route('login') }}" class="text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition-colors">
            {{ trans('app.Back to Sign In') }}
        </a>
    </div>
@endsection
