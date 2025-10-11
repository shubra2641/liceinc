@extends('layouts.user')

@section('title', trans('app.Reset Password'))
@section('page-title', trans('app.Forgot Password?'))
@section('page-subtitle', trans('app.No worries, we\'ll send you reset instructions'))
@section('app.Description', trans('app.Enter your email address and we\'ll send you a link to reset your password'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-key"></i>
                {{ trans('app.Forgot Password?') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.No worries, we\'ll send you reset instructions to your email') }}
            </p>
        </div>

        <div class="user-card-content">
            <div class="register-grid">
                <!-- Main Reset Form -->
                <div class="register-form-section">
                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="forgot-password-status-message">
                            <i class="fas fa-check-circle"></i>
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Reset Information -->
                    <div class="reset-info-card">
                        <div class="reset-info-header">
                            <div class="reset-info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3 class="reset-info-title">{{ trans('app.Password Reset') }}</h3>
                        </div>
                        <div class="reset-info-content">
                            <p class="reset-info-description">
                                {{ trans('app.Enter your email address below and we\'ll send you a secure link to reset your password. The link will expire in 60 minutes for security reasons.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Reset Form -->
                    <form method="POST" action="{{ route('password.email') }}" class="register-form" novalidate>
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
                                        value="{{ old('email') }}" required autofocus autocomplete="email"
                                        placeholder="{{ trans('app.Enter your email address') }}" />
                                </div>
                                <div class="form-help-text">
                                    {{ trans('app.We\'ll send reset instructions to this email address') }}
                                </div>
                                @error('email')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="form-submit-button">
                            <span class="button-text">{{ trans('app.Send Reset Link') }}</span>
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
                    <!-- Reset Process -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-shield-alt"></i>
                                {{ trans('app.Reset Process') }}
                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="reset-process-list">
                                <div class="reset-process-item">
                                    <div class="reset-process-number">1</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title">{{ trans('app.Enter Email') }}</h4>
                                        <p class="reset-process-description">{{ trans('app.Provide your registered email address') }}</p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">2</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title">{{ trans('app.Check Email') }}</h4>
                                        <p class="reset-process-description">{{ trans('app.We\'ll send you a secure reset link') }}</p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">3</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title">{{ trans('app.Create Password') }}</h4>
                                        <p class="reset-process-description">{{ trans('app.Set a new secure password') }}</p>
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
                                    {{ trans('app.Can\'t find the reset email?') }}
                                </p>
                                <a href="{{ route('support.tickets.create') }}" class="help-button">
                                    <i class="fas fa-headset"></i>
                                    {{ trans('app.Contact Support') }}
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
