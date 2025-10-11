@extends('layouts.user')

@section('page-title', trans('app.Payment Failed'))
@section('page-subtitle', trans('app.Your payment could not be processed'))

@section('content')
<div class="user-dashboard-container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Failure Card -->
            <div class="user-card text-center">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-times-circle text-danger fa-3x mb-3"></i>
                        <h2 class="text-danger">{{ trans('app.Payment Failed') }}</h2>
                    </div>
                </div>
                <div class="user-card-content">
                    <p class="lead text-muted mb-4">
                        {{ trans('app.We apologize, but your payment could not be processed at this time.') }}
                    </p>

                    <!-- Error Details -->
                    @if(isset($error_message))
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>{{ trans('app.Error') }}:</strong> {{ $error_message }}
                    </div>
                    @endif

                    <!-- Common Reasons -->
                    <div class="user-card mb-4">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-info-circle text-info"></i>
                                {{ trans('app.Common Reasons for Payment Failure') }}
                            </div>
                        </div>
                        <div class="user-card-content">
                            <ul class="list-unstyled text-start">
                                <li class="mb-2">
                                    <i class="fas fa-check text-muted me-2"></i>
                                    {{ trans('app.Insufficient funds in your account') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-muted me-2"></i>
                                    {{ trans('app.Incorrect card information') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-muted me-2"></i>
                                    {{ trans('app.Card expired or blocked') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-muted me-2"></i>
                                    {{ trans('app.Network connection issues') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-muted me-2"></i>
                                    {{ trans('app.Payment gateway temporarily unavailable') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ route('user.dashboard') }}" class="user-action-button primary">
                            <i class="fas fa-redo"></i>
                            {{ trans('app.Try Again') }}
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

                    <!-- Support Notice -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <i class="fas fa-headset text-primary me-2"></i>
                        <span class="text-muted">
                            {{ trans('app.If you continue to experience issues, please contact our support team for assistance.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
