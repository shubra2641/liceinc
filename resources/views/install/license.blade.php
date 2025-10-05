@extends('install.layout', ['step' => 2])

@section('title', 'License Verification')


@section('content')
<div class="license-verification">
    <div class="install-card">
        <div class="install-card-header">
            <div class="install-card-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="install-card-title">License Verification</h1>
            <p class="install-card-subtitle">Verify your purchase to continue installation</p>
        </div>

        <div class="install-card-body">
            <!-- Product & Domain Info -->
            <div class="license-info-cards">
                <div class="license-info-card">
                    <div class="icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <h4>Product</h4>
                    <p>The Ultimate License Management System</p>
                </div>
                <div class="license-info-card">
                    <div class="icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h4>Domain</h4>
                    <p>{{ request()->getHost() }}</p>
                </div>
            </div>

            <!-- License Form -->
            <div class="license-form">
                <form method="POST" action="{{ route('install.license.store') }}" id="licenseForm">
                    @csrf
                    <div class="license-form-group">
                        <label for="purchase_code" class="license-label">
                            <i class="fas fa-key"></i>
                            <span>Purchase Code</span>
                        </label>
                        <input type="text"
                               id="purchase_code"
                               name="purchase_code"
                               value="{{ old('purchase_code') }}"
                               placeholder="Enter your purchase code"
                               maxlength="100"
                               class="license-input @error('purchase_code') is-invalid @enderror"
                               required autocomplete="off">
                        @error('purchase_code')
                            <div class="license-error">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        @error('license')
                            <div class="license-error">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="license-hint">
                            <i class="fas fa-info-circle"></i>
                            Enter your purchase code or license key
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="license-security-notice">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>Security Notice:</strong> Your purchase code is sent securely to our license server for validation. This ensures you have a valid license for this domain.
                        </div>
                    </div>

                    <!-- Success Message (Hidden by default) -->
                    <div class="license-success" id="licenseSuccess">
                        <i class="fas fa-check-circle"></i>
                        <div class="license-success-text">
                            License verified successfully! You can now continue with the installation.
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="license-actions">
                        <a href="{{ route('install.welcome') }}" class="license-btn license-btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back</span>
                        </a>
                        
                        <button type="submit" class="license-btn license-btn-primary" id="verifyBtn">
                            <i class="fas fa-check"></i>
                            <span>Verify License</span>
                        </button>
                    </div>

                    <!-- Continue Button (Hidden by default) -->
                    <a href="{{ route('install.requirements') }}" class="license-btn license-continue-btn" id="continueBtn">
                        <i class="fas fa-arrow-right"></i>
                        <span>Continue Installation</span>
                    </a>
                </form>
            </div>

            <!-- Help Section -->
            <div class="license-help">
                <h4>
                    <i class="fas fa-question-circle"></i>
                    Need Help?
                </h4>
                <div class="license-help-grid">
                    <div class="license-help-item">
                        <h5>Where to find your purchase code?</h5>
                        <p>Check your email confirmation, account dashboard, or the platform where you purchased the license. The code format may vary.</p>
                    </div>
                    <div class="license-help-item">
                        <h5>Purchase code not working?</h5>
                        <p>Make sure you're using the correct purchase code, ensure your license is still valid, or contact our support team.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
