@extends('layouts.user')

@section('page-title', trans('app.Payment Successful'))
@section('page-subtitle', trans('app.Your payment has been processed successfully'))

@section('content')
<div class="user-dashboard-container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Card -->
            <div class="user-card text-center">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h2 class="text-success">{{ trans('app.Payment Successful!') }}</h2>
                    </div>
                </div>
                <div class="user-card-content">
                    <p class="lead text-muted mb-4">
                        {{ trans('app.Your payment has been processed successfully and your license has been activated.') }}
                    </p>

                    @if(isset($license) && isset($invoice))
                    <!-- License Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="user-card">
                                <div class="user-card-header">
                                    <div class="user-card-title">
                                        <i class="fas fa-key text-primary"></i>
                                        {{ trans('app.License Information') }}
                                    </div>
                                </div>
                                <div class="user-card-content">
                                    <div class="mb-2">
                                        <strong>{{ trans('app.License Key') }}:</strong>
                                        <code class="d-block mt-1 p-2 bg-light rounded">{{ $license->license_key }}</code>
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ trans('app.Status') }}:</strong>
                                        <span class="badge bg-success">{{ ucfirst($license->status) }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ trans('app.Expires') }}:</strong>
                                        <span class="text-muted">
                                            {{ $license->license_expires_at ? $license->license_expires_at->format('M d, Y') : trans('app.Never') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="user-card">
                                <div class="user-card-header">
                                    <div class="user-card-title">
                                        <i class="fas fa-file-invoice text-info"></i>
                                        {{ trans('app.Invoice Information') }}
                                    </div>
                                </div>
                                <div class="user-card-content">
                                    <div class="mb-2">
                                        <strong>{{ trans('app.Invoice Number') }}:</strong>
                                        <span class="text-muted">{{ $invoice->invoice_number }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ trans('app.Amount') }}:</strong>
                                        <span class="text-success fw-bold">${{ number_format($invoice->amount, 2) }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ trans('app.Payment Date') }}:</strong>
                                        <span class="text-muted">{{ $invoice->paid_at->format('M d, Y \a\t g:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ route('user.dashboard') }}" class="user-action-button primary">
                            <i class="fas fa-tachometer-alt"></i>
                            {{ trans('app.Go to Dashboard') }}
                        </a>
                        <a href="{{ route('user.licenses.index') }}" class="user-action-button">
                            <i class="fas fa-key"></i>
                            {{ trans('app.View Licenses') }}
                        </a>
                        <a href="{{ route('user.invoices.index') }}" class="user-action-button">
                            <i class="fas fa-file-invoice"></i>
                            {{ trans('app.View Invoices') }}
                        </a>
                    </div>

                    <!-- Email Notice -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <span class="text-muted">
                            {{ trans('app.A confirmation email has been sent to your registered email address.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
