@extends('layouts.admin')

@section('page-title', trans('app.Payment Settings'))
@section('page-subtitle', trans('app.Configure payment gateways and settings'))

@section('admin-content')
<div class="admin-dashboard-container">
    <!-- Header Section -->
    <div class="admin-card">
        <div class="admin-section-content">
            <div class="admin-card-title">
                <i class="fas fa-credit-card"></i>
                {{ trans('app.Payment Settings') }}
            </div>
            <p class="admin-card-subtitle">
                {{ trans('app.Configure and manage payment gateways for your store') }}
            </p>
        </div>

        <div class="admin-card-content">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Payment Gateway Overview -->
            <div class="admin-info-section">
                <div class="admin-info-card">
                    <div class="admin-info-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="admin-info-content">
                        <h4>{{ trans('app.Payment Gateway Configuration') }}</h4>
                        <p>{{ trans('app.Configure your payment gateways to accept payments from customers. Enable sandbox mode for testing.') }}</p>
                    </div>
                </div>
            </div>

            <!-- PayPal Settings -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <div class="admin-card-title">
                        <i class="fab fa-paypal"></i>
                        {{ trans('app.PayPal') }}
                    </div>
                    <p class="admin-card-subtitle">
                        {{ trans('app.Accept payments via PayPal') }}
                    </p>
                </div>

                <div class="admin-card-content">
                    <form method="POST" action="{{ route('admin.payment-settings.update') }}">
                        @csrf
                        <input type="hidden" name="gateway" value="paypal">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">
                                        <input type="checkbox" name="is_enabled" value="1" {{ $paypalSettings->is_enabled ? 'checked' : '' }}>
                                        {{ trans('app.Enable PayPal') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">
                                        <input type="checkbox" name="is_sandbox" value="1" {{ $paypalSettings->is_sandbox ? 'checked' : '' }}>
                                        {{ trans('app.Sandbox Mode') }}
                                    </label>
                                    <small class="admin-form-help">{{ trans('app.Use sandbox for testing') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">{{ trans('app.Client ID') }}</label>
                                    <input type="text" name="credentials[client_id]" class="admin-form-input" 
                                           value="{{ $paypalSettings->credentials['client_id'] ?? '' }}" 
                                           placeholder="Enter PayPal Client ID" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">{{ trans('app.Client Secret') }}</label>
                                    <input type="password" name="credentials[client_secret]" class="admin-form-input" 
                                           value="{{ $paypalSettings->credentials['client_secret'] ?? '' }}" 
                                           placeholder="Enter PayPal Client Secret" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">{{ trans('app.Webhook URL') }}</label>
                                    <input type="url" name="webhook_url" class="admin-form-input" 
                                           value="{{ $paypalSettings->webhook_url ?? '' }}" 
                                           placeholder="https://yoursite.com/payment/webhook/paypal">
                                    <small class="admin-form-help">
                                        {{ trans('app.Webhook URL for PayPal notifications (optional)') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-save"></i>
                                {{ trans('app.Save PayPal Settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stripe Settings -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <div class="admin-card-title">
                        <i class="fab fa-stripe"></i>
                        {{ trans('app.Stripe') }}
                    </div>
                    <p class="admin-card-subtitle">
                        {{ trans('app.Accept credit and debit card payments') }}
                    </p>
                </div>

                <div class="admin-card-content">
                    <form method="POST" action="{{ route('admin.payment-settings.update') }}">
                        @csrf
                        <input type="hidden" name="gateway" value="stripe">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">
                                        <input type="checkbox" name="is_enabled" value="1" {{ $stripeSettings->is_enabled ? 'checked' : '' }}>
                                        {{ trans('app.Enable Stripe') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">
                                        <input type="checkbox" name="is_sandbox" value="1" {{ $stripeSettings->is_sandbox ? 'checked' : '' }}>
                                        {{ trans('app.Sandbox Mode') }}
                                    </label>
                                    <small class="admin-form-help">{{ trans('app.Use sandbox for testing') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">{{ trans('app.Publishable Key') }}</label>
                                    <input type="text" name="credentials[publishable_key]" class="admin-form-input" 
                                           value="{{ $stripeSettings->credentials['publishable_key'] ?? '' }}" 
                                           placeholder="pk_test_..." required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">{{ trans('app.Secret Key') }}</label>
                                    <input type="password" name="credentials[secret_key]" class="admin-form-input" 
                                           value="{{ $stripeSettings->credentials['secret_key'] ?? '' }}" 
                                           placeholder="sk_test_..." required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">{{ trans('app.Webhook Secret') }}</label>
                                    <input type="password" name="credentials[webhook_secret]" class="admin-form-input" 
                                           value="{{ $stripeSettings->credentials['webhook_secret'] ?? '' }}" 
                                           placeholder="whsec_...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">{{ trans('app.Webhook URL') }}</label>
                                    <input type="url" name="webhook_url" class="admin-form-input" 
                                           value="{{ $stripeSettings->webhook_url ?? '' }}" 
                                           placeholder="https://yoursite.com/payment/webhook/stripe">
                                    <small class="admin-form-help">
                                        {{ trans('app.Webhook URL for Stripe notifications (optional)') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-save"></i>
                                {{ trans('app.Save Stripe Settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
