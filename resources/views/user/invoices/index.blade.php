@extends('layouts.user')

@section('title', trans('app.My Invoices'))
@section('page-title', trans('app.My Invoices'))
@section('page-subtitle', trans('app.View and manage your invoices'))

@section('seo_title', $siteSeoTitle ?? trans('app.My Invoices'))
@section('meta_description', $siteSeoDescription ?? trans('app.View and manage your invoices'))


@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-file-invoice"></i>
                {{ trans('app.My Invoices') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.View and manage your invoices and payments') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Invoice Statistics -->
            <div class="invoice-stats-grid">
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Invoices') }}</div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $invoices->total() }}</div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Paid Invoices') }}</div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $invoices->where('status', 'paid')->count() }}</div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Pending Invoices') }}</div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $invoices->where('status', 'pending')->count() }}</div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Amount') }}</div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">${{ number_format($invoices->sum('amount'), 2) }}</div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="license-filters">
                <div class="filter-group">
                    <label for="status-filter">{{ trans('app.Filter by Status') }}:</label>
                    <select id="status-filter" class="filter-select">
                        <option value="">{{ trans('app.All Statuses') }}</option>
                        <option value="paid">{{ trans('app.Paid') }}</option>
                        <option value="pending">{{ trans('app.Pending') }}</option>
                        <option value="cancelled">{{ trans('app.Cancelled') }}</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search-input">{{ trans('app.Search') }}:</label>
                    <input type="text" id="search-input" class="filter-input" placeholder="{{ trans('app.Search by invoice number...') }}">
                </div>
            </div>

            @if($invoices->isEmpty())
            <div class="user-empty-state">
                <div class="user-empty-state-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3 class="user-empty-state-title">
                    {{ trans('app.No invoices found') }}
                </h3>
                <p class="user-empty-state-description">
                    {{ trans('app.You don\'t have any invoices yet. Purchase a product to get started!') }}
                </p>
                <a href="{{ route('public.products.index') }}" class="user-action-button">
                    <i class="fas fa-shopping-cart"></i>
                    {{ trans('app.Browse Products') }}
                </a>
            </div>
            @else
            <!-- Invoices Table -->
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>{{ trans('app.Invoice') }}</th>
                            <th>{{ trans('app.Product') }}</th>
                            <th>{{ trans('app.Amount') }}</th>
                            <th>{{ trans('app.Status') }}</th>
                            <th>{{ trans('app.Due Date') }}</th>
                            <th>{{ trans('app.Created') }}</th>
                            <th>{{ trans('app.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
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
                            <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}</td>
                            <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="license-actions-cell">
                                    <a href="{{ route('user.invoices.show', $invoice) }}" class="license-action-link">
                                        <i class="fas fa-eye"></i>
                                        {{ trans('app.View') }}
                                    </a>
                                    @if($invoice->status === 'pending')
                                    <a href="{{ route('user.invoices.show', $invoice) }}#payment" class="license-action-link">
                                        <i class="fas fa-credit-card"></i>
                                        {{ trans('app.Pay') }}
                                    </a>
                                    @endif
                                    @if($invoice->status === 'paid')
                                    <a href="{{ route('user.invoices.show', $invoice) }}#download" class="license-action-link">
                                        <i class="fas fa-download"></i>
                                        {{ trans('app.Download') }}
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
                {{ $invoices->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection