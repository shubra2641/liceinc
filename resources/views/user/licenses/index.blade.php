@extends('layouts.user')

@section('title', trans('app.My Licenses & Invoices'))
@section('page-title', trans('app.My Licenses & Invoices'))
@section('page-subtitle', trans('app.Manage your licenses and invoices'))

@section('seo_title', $siteSeoTitle ?? trans('app.My Licenses'))
@section('meta_description', $siteSeoDescription ?? trans('app.Manage your purchased licenses'))


@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-key"></i>
                {{ trans('app.My Licenses & Invoices') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Manage your licenses and invoices, track payment status') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Tabs -->
            <div class="license-tabs">
                <button class="tab-button active" data-tab="licenses">
                    <i class="fas fa-key"></i>
                    {{ trans('app.Licenses') }} ({{ $licenses->total() }})
                </button>
                <button class="tab-button" data-tab="invoices">
                    <i class="fas fa-file-invoice"></i>
                    {{ trans('app.Invoices') }} ({{ $invoices->total() }})
                </button>
            </div>

            <!-- Licenses Tab -->
            <div id="licenses-tab" class="tab-content active">
                <!-- Filters and Search -->
                <div class="license-filters">
                    <div class="filter-group">
                        <label for="status-filter">{{ trans('app.Filter by Status') }}:</label>
                        <select id="status-filter" class="filter-select">
                            <option value="">{{ trans('app.All Statuses') }}</option>
                            <option value="active">{{ trans('app.Active') }}</option>
                            <option value="expired">{{ trans('app.Expired') }}</option>
                            <option value="suspended">{{ trans('app.Suspended') }}</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search-input">{{ trans('app.Search') }}:</label>
                        <input type="text" id="search-input" class="filter-input" placeholder="{{ trans('app.Search by product name...') }}">
                    </div>
                </div>

            @if($licenses->isEmpty())
            <div class="user-empty-state">
                <div class="user-empty-state-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h3 class="user-empty-state-title">
                    {{ trans('app.No licenses found') }}
                </h3>
                <p class="user-empty-state-description">
                    {{ trans('app.You haven\'t purchased any licenses yet. Browse our products to get started!') }}
                </p>
                <a href="{{ route('public.products.index') }}" class="user-action-button">
                    <i class="fas fa-shopping-cart"></i>
                    {{ trans('app.Browse Products') }}
                </a>
            </div>
            @else
            <!-- Licenses Table -->
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>{{ trans('app.Product') }}</th>
                            <th>{{ trans('app.License Key') }}</th>
                            <th>{{ trans('app.Type') }}</th>
                            <th>{{ trans('app.Status') }}</th>
                            <th>{{ trans('app.Purchase Date') }}</th>
                            <th>{{ trans('app.Support Until') }}</th>
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
                                <div class="license-key">
                                    <code class="license-key-code">{{ $license->license_key }}</code>
                                    <button class="copy-key-btn" data-key="{{ $license->license_key }}" title="{{ trans('app.Copy License Key') }}">
                                        <i class="fas fa-copy"></i>
                                    </button>
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
                            <td>{{ $license->created_at->format('M d, Y') }}</td>
                            <td>{{ optional($license->support_expires_at)->format('M d, Y') ?? '-' }}</td>
                            <td>
                                <div class="license-actions-cell">
                                    <a href="{{ route('user.licenses.show', $license) }}" class="license-action-link">
                                        <i class="fas fa-eye"></i>
                                        {{ trans('app.View') }}
                                    </a>
                                    @if($license->product)
                                    <a href="{{ route('public.products.show', $license->product->slug) }}" class="license-action-link">
                                        <i class="fas fa-external-link-alt"></i>
                                        {{ trans('app.Product') }}
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

                <!-- Pagination -->
                <div class="license-pagination">
                    {{ $licenses->links() }}
                </div>
                @endif
            </div>

            <!-- Invoices Tab -->
            <div id="invoices-tab" class="tab-content">
                @if($invoices->isEmpty())
                <div class="user-empty-state">
                    <div class="user-empty-state-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3 class="user-empty-state-title">
                        {{ trans('app.No invoices found') }}
                    </h3>
                    <p class="user-empty-state-description">
                        {{ trans('app.You don\'t have any invoices yet.') }}
                    </p>
                </div>
                @else
                <!-- Invoices Table -->
                <div class="table-container">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>{{ trans('app.Invoice Number') }}</th>
                                <th>{{ trans('app.Product') }}</th>
                                <th>{{ trans('app.Amount') }}</th>
                                <th>{{ trans('app.Status') }}</th>
                                <th>{{ trans('app.Created Date') }}</th>
                                <th>{{ trans('app.Paid Date') }}</th>
                                <th>{{ trans('app.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    <div class="invoice-number">
                                        <code class="invoice-number-code">{{ $invoice->invoice_number }}</code>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center">
                                        <div class="invoice-icon">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>
                                        <div>
                                            <div class="invoice-product">{{ $invoice->product?->name ?? 'N/A' }}</div>
                                            @if($invoice->license)
                                            <div class="invoice-license">
                                                <i class="fas fa-key"></i>
                                                {{ trans('app.License') }}: {{ $invoice->license->license_key }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="invoice-amount">${{ number_format($invoice->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="invoice-status-badge invoice-status-{{ $invoice->status }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                                <td>{{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y') : '-' }}</td>
                                <td>
                                    <div class="invoice-actions-cell">
                                        <a href="{{ route('user.invoices.show', $invoice) }}" class="invoice-action-link">
                                            <i class="fas fa-eye"></i>
                                            {{ trans('app.View') }}
                                        </a>
                                        @if($invoice->status === 'pending')
                                        <a href="{{ route('user.invoices.show', $invoice) }}" class="invoice-action-link primary">
                                            <i class="fas fa-credit-card"></i>
                                            {{ trans('app.Pay Now') }}
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="invoice-pagination">
                    {{ $invoices->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

