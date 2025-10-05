@extends('layouts.user')

@section('title', trans('app.Welcome To'))
@section('page-title', trans('app.Welcome To'))
@section('page-subtitle', trans('app.Manage your licenses and products'))

@section('seo_title', $siteSeoTitle ?? trans('app.Welcome To'))
@section('meta_description', $siteSeoDescription ?? trans('app.Manage your licenses, track downloads, and access support
from your personal dashboard'))

@section('content')
{{-- Security: All output is automatically escaped using Blade's {{ }} syntax --}}
{{-- Additional XSS protection: htmlspecialchars applied to user content --}}
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-bolt"></i>
                {{ trans('app.Welcome To') }}, {{ $siteName }}!
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Manage your licenses, track downloads, and access support from your personal dashboard')
                }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Stats Cards -->
            <div class="user-stats-grid">
                <!-- Total Customers -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Customers') }}</div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">
                        {{ number_format($stats['customers'] ?? 0) }}
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.All registered users') }}</p>
                </div>

                <!-- Total Licenses -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Licenses') }}</div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ number_format($stats['licenses'] ?? 0) }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.All issued licenses') }}</p>
                </div>

                <!-- Total Tickets -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Tickets') }}</div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ number_format($stats['tickets'] ?? 0) }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Support requests') }}</p>
                </div>

                <!-- Total Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Invoices') }}</div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ number_format($stats['invoices'] ?? 0) }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.All invoices') }}</p>
                </div>

                <!-- Total Products -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Products') }}</div>
                        <div class="user-stat-icon indigo">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ number_format($stats['products'] ?? \App\Models\Product::count()) }}
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Available products') }}</p>
                </div>

                <!-- Active Licenses -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Active Licenses') }}</div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ number_format($stats['active_licenses'] ?? 0) }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Currently active') }}</p>
                </div>

                <!-- Paid Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Paid Invoices') }}</div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ number_format($stats['paid_invoices'] ?? 0) }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Completed payments') }}</p>
                </div>

                <!-- Open Tickets -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Open Tickets') }}</div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ number_format($stats['open_tickets'] ?? 0) }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Awaiting response') }}</p>
                </div>
            </div>

            <!-- Quick Actions -->
            {{-- Security: Authentication checks with @auth/@endauth, CSRF protection via middleware --}}
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
                    @auth
                    <a href="{{ route('user.tickets.index') }}" class="user-action-button">
                        <i class="fas fa-ticket-alt"></i>
                        {{ trans('app.View Tickets') }}
                    </a>
                    @else
                    <a href="{{ route('support.tickets.create') }}" class="user-action-button">
                        <i class="fas fa-plus"></i>
                        {{ trans('app.Create Ticket') }}
                    </a>
                    @endauth
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
                    @auth
                    <a href="{{ route('user.invoices.index') }}" class="user-action-button">
                        <i class="fas fa-eye"></i>
                        {{ trans('app.View Invoices') }}
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="user-action-button">
                        <i class="fas fa-sign-in-alt"></i>
                        {{ trans('app.Sign In') }}
                    </a>
                    @endauth
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

            <!-- Available Products Section -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-box"></i>
                        {{ trans('app.Available Products') }}
                    </div>
                    <p class="user-card-subtitle">{{ trans('app.Discover and purchase new products') }}</p>
                </div>
                <div class="user-card-content">
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
                    <div class="user-products-grid">
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
                            {{-- Security: Product description sanitized with Str::limit and auto-escaped --}}
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
                    </div>
                    <div class="user-products-actions">
                        <a href="{{ route('public.products.index') }}" class="user-action-button">
                            <i class="fas fa-list"></i>
                            {{ trans('app.View All Products') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection