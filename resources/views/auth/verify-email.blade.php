@extends('layouts.user')

@section('title', trans('app.Verify Email'))
@section('page-title', trans('app.Verify your email'))
@section('page-subtitle', trans('app.Check your inbox for the verification link'))
@section('app.Description', trans('app.Email verification ensures account security'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-envelope-open"></i>
                {{ trans('app.Verify your email') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Check your inbox for the verification link to complete your account setup') }}
            </p>
        </div>

        <div class="user-card-content">
            <div class="register-grid">
                <!-- Main Verification Content -->
                <div class="register-form-section">
                    <!-- Verification Information -->
                    <div class="reset-info-card">
                        <div class="reset-info-header">
                            <div class="reset-info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3 class="reset-info-title">{{ trans('app.Email Verification Required') }}</h3>
                        </div>
                        <div class="reset-info-content">
                            <p class="reset-info-description">
                                {{ trans("app.Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.") }}
                            </p>
                            
                            @if (session('status') == 'verification-link-sent')
                                <div class="verification-status-message">
                                    <i class="fas fa-check-circle"></i>
                                    {{ trans('app.A new verification link has been sent to the email address you provided during registration.') }}
                                </div>
                            @elseif (session('status'))
                                <div class="verification-status-message">
                                    <i class="fas fa-info-circle"></i>
                                    {{ session('status') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="verification-actions">
                        <form method="POST" action="{{ route('verification.send') }}" class="verification-form">
                            @csrf
                            <button type="submit" class="form-submit-button">
                                <span class="button-text">{{ trans('app.Resend Verification Email') }}</span>
                                <i class="fas fa-spinner fa-spin button-loading hidden"></i>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="logout-form">
                            @csrf
                            <button type="submit" class="form-logout-button">
                                <i class="fas fa-sign-out-alt"></i>
                                {{ trans('app.Log Out') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="register-sidebar">
                    <!-- Verification Process -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-shield-alt"></i>
                                {{ trans('app.Verification Process') }}
                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="reset-process-list">
                                <div class="reset-process-item">
                                    <div class="reset-process-number">1</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title">{{ trans('app.Check Your Email') }}</h4>
                                        <p class="reset-process-description">{{ trans('app.Look for our verification email') }}</p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">2</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title">{{ trans('app.Click the Link') }}</h4>
                                        <p class="reset-process-description">{{ trans('app.Click the verification link in the email') }}</p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">3</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title">{{ trans('app.Complete Setup') }}</h4>
                                        <p class="reset-process-description">{{ trans('app.Your account will be fully activated') }}</p>
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
                                    {{ trans('app.Can\'t find the verification email?') }}
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
