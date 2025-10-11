@extends('layouts.user')

@section('page-title', trans('app.Payment Cancelled'))
@section('page-subtitle', trans('app.Your payment has been cancelled'))

@section('content')
<div class="user-dashboard-container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Cancel Card -->
            <div class="user-card text-center">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-ban text-warning fa-3x mb-3"></i>
                        <h2 class="text-warning">{{ trans('app.Payment Cancelled') }}</h2>
                    </div>
                </div>
                <div class="user-card-content">
                    <p class="lead text-muted mb-4">
                        {{ trans('app.Your payment has been cancelled. No charges have been made to your account.') }}
                    </p>

                    <!-- Information Card -->
                    <div class="user-card mb-4">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-info-circle text-info"></i>
                                {{ trans('app.What happens next?') }}
                            </div>
                        </div>
                        <div class="user-card-content">
                            <ul class="list-unstyled text-start">
                                <li class="mb-2">
                                    <i class="fas fa-check text-muted me-2"></i>
                                    {{ trans('app.No charges have been made to your account') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-muted me-2"></i>
                                    {{ trans('app.You can try the payment again anytime') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-muted me-2"></i>
                                    {{ trans('app.Your cart items are still available') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-muted me-2"></i>
                                    {{ trans('app.You can contact support if you need help') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ route('user.dashboard') }}" class="user-action-button primary">
                            <i class="fas fa-shopping-cart"></i>
                            {{ trans('app.Continue Shopping') }}
                        </a>
                        <a href="{{ route('user.dashboard') }}" class="user-action-button">
                            <i class="fas fa-tachometer-alt"></i>
                            {{ trans('app.Go to Dashboard') }}
                        </a>
                        <a href="{{ route('user.dashboard') }}" class="user-action-button">
                            <i class="fas fa-headset"></i>
                            {{ trans('app.Contact Support') }}
                        </a>
                    </div>

                    <!-- Help Notice -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <i class="fas fa-question-circle text-primary me-2"></i>
                        <span class="text-muted">
                            {{ trans('app.Need help with your purchase? Our support team is here to assist you.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
