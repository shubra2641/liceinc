@extends('layouts.user')

@section('title', trans('app.Dashboard'))
@section('page-title', trans('app.Welcome Back'))
@section('page-subtitle', trans('app.Manage your licenses and products'))

@section('seo_title', $seoTitle ?? $siteSeoTitle ?? trans('app.Dashboard'))
@section('meta_description', $seoDescription ?? $siteSeoDescription ?? trans('app.Manage your licenses, track downloads, and access support from your personal dashboard'))


@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-tachometer-alt"></i>
                {{ trans('app.Welcome Back') }}, {{ auth()->user()->name }}!
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Manage your licenses, track downloads, and access support from your personal dashboard') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Stats Cards -->
            <div class="user-stats-grid">
                <!-- Active Licenses -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Active Licenses') }}</div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">
                        {{ $activeCount ?? auth()->user()->licenses()->where('status','active')->count() }}
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Currently active') }}</p>
                </div>

                <!-- Total Products -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Products') }}</div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ \App\Models\Product::count() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Available products') }}</p>
                </div>

                <!-- Open Tickets -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Open Tickets') }}</div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ auth()->user()->tickets()->where('status','open')->count() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Awaiting response') }}</p>
                </div>

                <!-- Total Downloads -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Downloads') }}</div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-download"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ auth()->user()->licenseLogs()->count() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.License downloads') }}</p>
                </div>

                <!-- Total Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Invoices') }}</div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $invoiceTotal ?? auth()->user()->invoices()->count() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.All invoices') }}</p>
                </div>

                <!-- Paid Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Paid Invoices') }}</div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $invoicePaid ?? auth()->user()->invoices()->where('status','paid')->count() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Completed payments') }}</p>
                </div>

                <!-- Pending Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Pending Invoices') }}</div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $invoicePending ?? auth()->user()->invoices()->where('status', 'pending')->count() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Awaiting payment') }}</p>
                </div>

                <!-- Cancelled Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Cancelled Invoices') }}</div>
                        <div class="user-stat-icon red">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $invoiceCancelled ?? auth()->user()->invoices()->where('status','cancelled')->count() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Cancellations') }}</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="user-actions-grid">
                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon indigo">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="user-action-content">
                            <h3>{{ trans('app.Support Tickets') }}</h3>
                            <p>{{ trans('app.Get help and support') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('user.tickets.index') }}" class="user-action-button">
                        <i class="fas fa-ticket-alt"></i>
                        {{ trans('app.View Tickets') }}
                    </a>
                </div>

                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon purple">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="user-action-content">
                            <h3>{{ trans('app.My Invoices') }}</h3>
                            <p>{{ trans('app.View and manage invoices') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('user.invoices.index') }}" class="user-action-button">
                        <i class="fas fa-eye"></i>
                        {{ trans('app.View Invoices') }}
                    </a>
                </div>

                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon blue">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="user-action-content">
                            <h3>{{ trans('app.Knowledge Base') }}</h3>
                            <p>{{ trans('app.Find answers and guides') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('kb.index') }}" class="user-action-button">
                        <i class="fas fa-search"></i>
                        {{ trans('app.Explore KB') }}
                    </a>
                </div>
            </div>

            <!-- My Licenses Section -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-key"></i>
                        {{ trans('app.My Licenses') }}
                    </div>
                    <p class="user-card-subtitle">{{ trans('app.Manage your purchased licenses') }}</p>
                </div>
                <div class="user-card-content">
                    @if($licenses->isEmpty())
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            {{ trans('app.No licenses found') }}
                        </h3>
                        <p class="user-empty-state-description">
                            {{ trans('app.You haven\'t purchased any licenses yet') }}
                        </p>
                    </div>
                    @else
                    <div class="table-container">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>{{ trans('app.Product') }}</th>
                                    <th>{{ trans('app.License Type') }}</th>
                                    <th>{{ trans('app.Status') }}</th>
                                    <th>{{ trans('app.Support') }}</th>
                                    <th>{{ trans('app.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($licenses as $license)
                                <tr>
                                    <td>
                                        <div class="flex items-center">
                                            <div class="license-icon">
                                                <i class="fas fa-key"></i>
                                            </div>
                                            <div>
                                                <div class="license-name">{{ $license->product?->name ?? 'N/A' }}</div>
                                                <div class="license-version">v{{ $license->product?->version ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="license-type-badge">
                                            {{ ucfirst($license->license_type ?? '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="license-status-badge license-status-{{ $license->status }}">
                                            {{ ucfirst($license->status) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($license->support_expires_at)->format('M d, Y') ?? '-' }}</td>
                                    <td>
                                        @if($license->product)
                                        <a href="{{ route('public.products.show', $license->product->slug) }}" class="license-action-link">
                                            <i class="fas fa-eye"></i>
                                            {{ trans('app.View Details') }}
                                        </a>
                                        @else
                                        <span class="text-muted">{{ trans('app.N/A') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="license-pagination">
                        {{ $licenses->links() }}
                    </div>
                    <div class="license-actions">
                        <a href="{{ route('user.licenses.index') }}" class="user-action-button">
                            <i class="fas fa-list"></i>
                            {{ trans('app.View All Licenses') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- My Invoices Section -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-file-invoice"></i>
                        {{ trans('app.My Invoices') }}
                    </div>
                    <p class="user-card-subtitle">{{ trans('app.Manage your invoices and payments') }}</p>
                </div>
                <div class="user-card-content">
                    @if($recentInvoices->isEmpty())
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            {{ trans('app.No invoices found') }}
                        </h3>
                        <p class="user-empty-state-description">
                            {{ trans('app.You don\'t have any invoices yet') }}
                        </p>
                    </div>
                    @else
                    <div class="table-container">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>{{ trans('app.Invoice') }}</th>
                                    <th>{{ trans('app.Product') }}</th>
                                    <th>{{ trans('app.Amount') }}</th>
                                    <th>{{ trans('app.Status') }}</th>
                                    <th>{{ trans('app.Due Date') }}</th>
                                    <th>{{ trans('app.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentInvoices as $invoice)
                                <tr>
                                    <td>
                                        <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                                        <div class="invoice-date">{{ $invoice->created_at->format('M d, Y') }}</div>
                                    </td>
                                    <td>
                                        <div class="invoice-product">{{ $invoice->license->product->name ?? 'N/A' }}</div>
                                        <div class="invoice-type">{{ $invoice->license->license_type ?? 'N/A' }}</div>
                                    </td>
                                    <td class="invoice-amount">${{ number_format($invoice->amount, 2) }}</td>
                                    <td>
                                        <span class="invoice-status-badge invoice-status-{{ $invoice->status }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('user.invoices.show', $invoice) }}" class="invoice-action-link">
                                            <i class="fas fa-eye"></i>
                                            {{ trans('app.View') }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="invoice-actions">
                        <a href="{{ route('user.invoices.index') }}" class="user-action-button">
                            <i class="fas fa-list"></i>
                            {{ trans('app.View All Invoices') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Available Products Section -->
            <div class="user-products-section">
                <div class="user-products-header">
                    <div class="user-products-title">
                        <i class="fas fa-box"></i>
                        {{ trans('app.Available Products') }}
                    </div>
                    <a href="{{ route('public.products.index') }}" class="user-products-button">
                        <i class="fas fa-eye"></i>
                        {{ trans('app.View All Products') }}
                    </a>
                </div>
                <div class="user-products-grid">
                    @if($products->isEmpty())
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            {{ trans('app.No products available') }}
                        </h3>
                        <p class="user-empty-state-description">
                            {{ trans('app.Check back later for new products') }}
                        </p>
                    </div>
                    @else
                    @foreach($products as $product)
                    <div class="user-product-card">
                        <div class="user-product-header">
                            <div>
                                <div class="user-product-title-row">
                                    <h3 class="user-product-title">{{ $product->name }}</h3>
                                    @if($product->is_featured || $product->is_popular)
                                    <span class="user-premium-badge">
                                        <i class="fas fa-crown"></i>
                                        {{ trans('app.Premium') }}
                                    </span>
                                    @endif
                                </div>
                                <p class="user-product-version">v{{ $product->latest_version ?? '-' }}</p>
                            </div>
                            <div class="user-product-price">
                                <div class="user-product-price-value">{{ $product->formatted_price }}</div>
                                <div class="user-product-price-period">{{ $product->renewalPeriodLabel() }}</div>
                            </div>
                        </div>

                        @if($product->description)
                        <p class="user-product-description">
                            {{ Str::limit($product->description, 100) }}
                        </p>
                        @endif

                        <a href="{{ route('public.products.show', $product->slug) }}" class="user-product-button">
                            <i class="fas fa-eye"></i>
                            {{ trans('app.View Details') }}
                        </a>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection