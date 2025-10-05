@extends('layouts.user')

@section('title', trans('app.Test Email Warning'))
@section('page-title', trans('app.Test Email Detected'))
@section('page-subtitle', trans('app.You are using a test email address'))
@section('app.Description', trans('app.Test email addresses cannot receive verification emails'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                {{ trans('app.Test Email Detected') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.You are using a test email address that cannot receive verification emails') }}
            </p>
        </div>

        <div class="user-card-content">
            <div class="register-grid">
                <!-- Main Warning Content -->
                <div class="register-form-section">
                    <!-- Warning Information -->
                    <div class="reset-info-card">
                        <div class="reset-info-header">
                            <div class="reset-info-icon">
                                <i class="fas fa-envelope-slash text-warning"></i>
                            </div>
                            <h3 class="reset-info-title">{{ trans('app.Test Email Address') }}</h3>
                        </div>
                        <div class="reset-info-content">
                            <p class="reset-info-description">
                                {{ trans('app.You are currently using a test email address') }} <strong>({{ $email }})</strong> {{ trans('app.that cannot receive verification emails. Test email addresses like @example.com, @test.com, @localhost, and @demo.com are not real email addresses and cannot receive emails.') }}
                            </p>
                            
                            <div class="verification-status-message warning-message">
                                <i class="fas fa-info-circle"></i>
                                {{ trans('app.To use the system normally, please register with a real email address that can receive verification emails.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="verification-actions">
                        <a href="{{ route('dashboard') }}" class="form-submit-button">
                            <span class="button-text">{{ trans('app.Continue to Dashboard') }}</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>

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
                    <!-- Test Email Info -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-info-circle text-info"></i>
                                {{ trans('app.About Test Emails') }}
                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="reset-process-list">
                                <div class="reset-process-item">
                                    <div class="reset-process-number">1</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title">{{ trans('app.Test Email Domains') }}</h4>
                                        <p class="reset-process-description">{{ trans('app.@example.com, @test.com, @localhost, @demo.com') }}</p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">2</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title">{{ trans('app.Cannot Receive Emails') }}</h4>
                                        <p class="reset-process-description">{{ trans('app.These domains are not real and cannot receive emails') }}</p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">3</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title">{{ trans('app.Use Real Email') }}</h4>
                                        <p class="reset-process-description">{{ trans('app.Register with Gmail, Yahoo, or other real email providers') }}</p>
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
                                    {{ trans('app.Want to use a real email address?') }}
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
