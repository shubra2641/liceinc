@extends('layouts.user')

@section('page-title', trans('app.Choose Payment Method'))
@section('page-subtitle', trans('app.Select your preferred payment gateway'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-credit-card"></i>
                {{ trans('app.Complete Your Purchase') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Select your preferred payment method to complete your purchase') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Product Summary -->
            <div class="user-card mb-4">
                <div class="user-card-content">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded w-80 h-80 object-cover">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light rounded w-80 h-80">
                                    <i class="fas fa-box fa-2x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-2">{{ $product->name }}</h4>
                            <p class="text-muted mb-2">{{ Str::limit($product->description, 120) }}</p>
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-2">{{ trans('app.Price') }}:</span>
                                <span class="h4 text-success mb-0">${{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-credit-card"></i>
                        {{ trans('app.Payment Methods') }}
                    </div>
                </div>
                <div class="user-card-content">
                    <div class="row">
                        @foreach($enabledGateways as $gateway)
                            <div class="col-lg-6 mb-4">
                                <div class="user-card payment-gateway-card">
                                    <div class="user-card-content">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3">
                                                @if($gateway === 'paypal')
                                                    <div class="user-stat-icon blue">
                                                        <i class="fab fa-paypal"></i>
                                                    </div>
                                                @elseif($gateway === 'stripe')
                                                    <div class="user-stat-icon green">
                                                        <i class="fab fa-stripe"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">
                                                    @if($gateway === 'paypal')
                                                        {{ trans('app.PayPal') }}
                                                    @elseif($gateway === 'stripe')
                                                        {{ trans('app.Stripe') }}
                                                    @endif
                                                </h5>
                                                <p class="text-muted mb-0 small">
                                                    @if($gateway === 'paypal')
                                                        {{ trans('app.Pay securely with PayPal') }}
                                                    @elseif($gateway === 'stripe')
                                                        {{ trans('app.Pay with credit or debit card') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <form method="POST" action="{{ route('payment.process', $product) }}" class="d-grid">
                                            @csrf
                                            <input type="hidden" name="gateway" value="{{ $gateway }}">
                                            <button type="submit" class="user-action-button primary">
                                                <i class="fas fa-arrow-right"></i>
                                                {{ trans('app.Pay Now') }} - ${{ number_format($product->price, 2) }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="user-card">
                <div class="user-card-content">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="user-stat-icon green">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ trans('app.Secure Payment') }}</h6>
                            <p class="text-muted mb-0 small">{{ trans('app.Your payment information is encrypted and secure. We do not store your payment details.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection