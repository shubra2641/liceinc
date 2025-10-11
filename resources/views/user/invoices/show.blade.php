@extends('layouts.user')

@section('title', trans('app.Invoice Details'))
@section('page-title', trans('app.Invoice Details'))
@section('page-subtitle', trans('app.View invoice information and make payments'))


@section('seo_title', $siteSeoTitle ?? trans('app.Invoice Details'))
@section('meta_description', $siteSeoDescription ?? trans('app.View invoice information and make payments'))


@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-file-invoice"></i>
                {{ trans('app.Invoice') }} #{{ $invoice->invoice_number }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Invoice details and payment information') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Invoice Status Banner -->
            <div class="invoice-status-banner invoice-status-{{ $invoice->status }}">
                <div class="status-content">
                    <i class="fas fa-{{ $invoice->status === 'paid' ? 'check-circle' : ($invoice->status === 'pending' ? 'clock' : 'times-circle') }}"></i>
                    <div>
                        <h3>{{ trans('app.Invoice') }} {{ ucfirst($invoice->status) }}</h3>
                        <p>
                            @if($invoice->status === 'paid')
                                {{ trans('app.This invoice has been paid successfully') }}
                            @elseif($invoice->status === 'pending')
                                {{ trans('app.This invoice is pending payment') }}
                            @else
                                {{ trans('app.This invoice has been cancelled') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Invoice Details Grid -->
            <div class="license-details-grid">
                <!-- Invoice Information -->
                <div class="license-info-card">
                    <div class="license-info-header">
                        <h3>{{ trans('app.Invoice Information') }}</h3>
                    </div>
                    
                    <div class="license-info-content">
                        <div class="info-row">
                            <label>{{ trans('app.Invoice Number') }}:</label>
                            <span>{{ $invoice->invoice_number }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Status') }}:</label>
                            <span class="invoice-status-badge invoice-status-{{ $invoice->status }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Amount') }}:</label>
                            <span class="invoice-amount">${{ number_format($invoice->amount, 2) }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Created Date') }}:</label>
                            <span>{{ $invoice->created_at->format('M d, Y') }}</span>
                        </div>
                        
                        @if($invoice->due_date)
                        <div class="info-row">
                            <label>{{ trans('app.Due Date') }}:</label>
                            <span>{{ $invoice->due_date->format('M d, Y') }}</span>
                        </div>
                        @endif
                        
                        @if($invoice->paid_at)
                        <div class="info-row">
                            <label>{{ trans('app.Paid Date') }}:</label>
                            <span>{{ $invoice->paid_at->format('M d, Y') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Product Information -->
                @if($hasLicense)
                <div class="license-info-card">
                    <div class="license-info-header">
                        <h3>{{ trans('app.Product Information') }}</h3>
                    </div>
                    
                    <div class="license-info-content">
                        <div class="info-row">
                            <label>{{ trans('app.Product') }}:</label>
                            <span>{{ $invoice->license->product?->name ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Version') }}:</label>
                            <span>v{{ $invoice->license->product?->version ?? '-' }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.License Type') }}:</label>
                            <span class="license-type-badge">{{ ucfirst($invoice->license->license_type ?? '-') }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.License Key') }}:</label>
                            <div class="license-key-display">
                                <code class="license-key-code">{{ $invoice->license->license_key }}</code>
                                <button class="copy-key-btn" data-key="{{ $invoice->license->license_key }}" title="{{ trans('app.Copy License Key') }}">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Actions') }}:</label>
                            @if($hasLicense)
                            <a href="{{ route('public.products.show', $invoice->license->product->slug) }}" class="license-action-link">
                                <i class="fas fa-external-link-alt"></i>
                                {{ trans('app.View Product') }}
                            </a>
                            @else
                            <span class="text-muted">{{ trans('app.No product available') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @elseif($isCustomInvoice)
                <!-- Custom Invoice Information -->
                <div class="license-info-card">
                    <div class="license-info-header">
                        <h3>{{ trans('app.Service Information') }}</h3>
                    </div>
                    
                    <div class="license-info-content">
                        <div class="info-row">
                            <label>{{ trans('app.Service Type') }}:</label>
                            <span>{{ trans('app.Additional Service') }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Description') }}:</label>
                            <span>{{ $invoice->notes ?? trans('app.Custom service invoice') }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Invoice Type') }}:</label>
                            <span class="license-type-badge">{{ trans('app.Service Invoice') }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Payment Section -->
            @if($invoice->status === 'pending')
            <div id="payment" class="payment-section">
                <div class="section-header">
                    <h3>{{ trans('app.Make Payment') }}</h3>
                    <p class="text-muted">{{ trans('app.Choose your preferred payment method to complete the payment') }}</p>
                </div>
                
                <div class="payment-methods">
                    
                    @if(empty($enabledGateways))
                        <div data-flash-warning class="flash-message-hidden">{{ trans('app.No payment gateways are currently available. Please contact support.') }}</div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ trans('app.No payment gateways are currently available. Please contact support.') }}
                        </div>
                    @else
                        @foreach($enabledGateways as $gateway)
                            <div class="payment-method-card">
                                <div class="payment-method-header">
                                    @if($gateway === 'stripe')
                                        <i class="fas fa-credit-card text-primary"></i>
                                        <h4>{{ trans('app.Credit Card') }}</h4>
                                    @elseif($gateway === 'paypal')
                                        <i class="fab fa-paypal text-primary"></i>
                                        <h4>{{ trans('app.PayPal') }}</h4>
                                    @endif
                                </div>
                                
                                @if($gateway === 'stripe')
                                    <p>{{ trans('app.Pay securely with your credit or debit card') }}</p>
                                @elseif($gateway === 'paypal')
                                    <p>{{ trans('app.Pay with your PayPal account') }}</p>
                                @endif
                                
                                @if($productForPayment || $isCustomInvoice)
                                <form method="POST" action="{{ $productForPayment ? route('payment.process', $productForPayment) : route('payment.process.custom', $invoice) }}" class="inline-form">
                                    @csrf
                                    <input type="hidden" name="gateway" value="{{ $gateway }}">
                                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                                    <button type="submit" class="user-action-button primary">
                                        @if($gateway === 'stripe')
                                            <i class="fas fa-credit-card"></i>
                                            {{ trans('app.Pay with Credit Card') }}
                                        @elseif($gateway === 'paypal')
                                            <i class="fab fa-paypal"></i>
                                            {{ trans('app.Pay with PayPal') }}
                                        @endif
                                    </button>
                                </form>
                                @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ trans('app.No product available for payment') }}
                                </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
                
                <!-- Payment Information -->
                <div class="payment-info mt-4">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>{{ trans('app.Payment Amount') }}:</strong> 
                        <span class="fw-bold">${{ number_format($invoice->amount, 2) }}</span>
                        @if($invoice->due_date)
                            <br>
                            <strong>{{ trans('app.Due Date') }}:</strong> 
                            {{ $invoice->due_date->format('M d, Y') }}
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Download Section -->
            @if($invoice->status === 'paid' && $hasLicense)
            <div id="download" class="download-section">
                <div class="section-header">
                    <h3>{{ trans('app.Download Product') }}</h3>
                </div>
                
                <div class="download-content">
                        <div class="download-info">
                            <i class="fas fa-download"></i>
                            <div>
                                <h4>{{ $invoice->license->product?->name ?? 'N/A' }}</h4>
                                <p>{{ trans('app.Your product is ready for download') }}</p>
                            </div>
                        </div>
                    
                    <div class="download-actions">
                        @if($hasLicense)
                        <a href="{{ route('public.products.show', $invoice->license->product->slug) }}" class="user-action-button">
                            <i class="fas fa-download"></i>
                            {{ trans('app.Download Product') }}
                        </a>
                        @endif
                        
                        @if($invoice->license)
                        <a href="{{ route('user.licenses.show', $invoice->license) }}" class="user-action-button">
                            <i class="fas fa-key"></i>
                            {{ trans('app.View License') }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @elseif($invoice->status === 'paid' && $isCustomInvoice)
            <!-- Service Completion Section -->
            <div id="service" class="download-section">
                <div class="section-header">
                    <h3>{{ trans('app.Service Status') }}</h3>
                </div>
                
                <div class="download-content">
                    <div class="download-info">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <h4>{{ trans('app.Service Payment Completed') }}</h4>
                            <p>{{ trans('app.Your payment has been received and the service will be processed.') }}</p>
                        </div>
                    </div>
                    
                    <div class="download-actions">
                        <a href="{{ route('user.dashboard') }}" class="user-action-button">
                            <i class="fas fa-tachometer-alt"></i>
                            {{ trans('app.Go to Dashboard') }}
                        </a>
                        
                        <a href="{{ route('user.invoices.index') }}" class="user-action-button">
                            <i class="fas fa-file-invoice"></i>
                            {{ trans('app.View All Invoices') }}
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Invoice Actions -->
            <div class="license-actions-section">
                <div class="action-buttons">
                    <a href="{{ route('user.invoices.index') }}" class="user-action-button">
                        <i class="fas fa-arrow-left"></i>
                        {{ trans('app.Back to Invoices') }}
                    </a>
                    
                    <button class="user-action-button" data-action="print">
                        <i class="fas fa-print"></i>
                        {{ trans('app.Print Invoice') }}
                    </button>
                    
                    <a href="{{ route('user.tickets.create') }}" class="user-action-button">
                        <i class="fas fa-headset"></i>
                        {{ trans('app.Get Support') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection