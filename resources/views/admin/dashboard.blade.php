@extends('layouts.admin')

@section('admin-content')
<!-- Enhanced Professional Dashboard Page -->
<div class="admin-page">
    <div class="dashboard-content">
        <!-- Modern Page Header with Gradient -->
        <div class="admin-page-header modern-header">
            <div class="admin-page-header-content">
                <div class="admin-page-title">
                    <h1 class="gradient-text">{{ trans('app.Dashboard Overview') }}</h1>
                    <p class="admin-page-subtitle">
                        {{ trans('app.Monitor your license management system performance and key metrics') }}
                    </p>
                </div>
                <div class="admin-page-actions">
                    <div class="header-stats">
                        <div class="header-stat">
                            <span class="stat-label">{{ trans('app.Today') }}</span>
                            <span class="stat-value">{{ \Carbon\Carbon::now()->format('M d, Y') }}</span>
                        </div>
                        <div class="header-stat">
                            <span class="stat-label">{{ trans('app.System Status') }}</span>
                            <span class="stat-value {{ $isMaintenance ? 'status-offline' : 'status-online' }}">
                                <span class="status-dot"></span>
                                {{ $isMaintenance ? trans('app.Offline') : trans('app.Online') }}
                            </span>
                        </div>
                        <div class="header-stat">
                            <span class="stat-label">{{ trans('app.System Version') }}</span>
                            <span class="stat-value">
                                v{{ \App\Helpers\VersionHelper::getCurrentVersion() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Stats Cards Grid with Animations -->
        <div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
            <!-- API Requests Today -->
            <div class="stats-card stats-card-primary animate-slide-up">
                <div class="stats-card-background">
                    <div class="stats-card-pattern"></div>
                </div>
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <div class="stats-card-icon api"></div>
                        <div class="stats-card-menu">
                            <button class="stats-menu-btn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value">{{ $stats['api_requests_today'] ?? 0 }}</div>
                        <div class="stats-card-label">{{ trans('app.API Requests Today') }}</div>
                        <div class="stats-card-trend positive">
                            <i class="stats-trend-icon positive"></i>
                            <span>{{ $stats['api_requests_this_month'] ?? 0 }} {{ trans('app.this month') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Open Tickets Stats Card -->
            <div class="stats-card stats-card-warning animate-slide-up animate-delay-200">
                <div class="stats-card-background">
                    <div class="stats-card-pattern"></div>
                </div>
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <div class="stats-card-icon tickets"></div>
                        <div class="stats-card-menu">
                            <button class="stats-menu-btn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value">{{ $stats['tickets_open'] ?? 0 }}</div>
                        <div class="stats-card-label">{{ trans('app.Open Tickets') }}</div>
                        <div class="stats-card-trend negative">
                            <i class="stats-trend-icon negative"></i>
                            <span>-5% {{ trans('app.from last month') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Licenses Stats Card -->
            <div class="stats-card stats-card-success animate-slide-up animate-delay-300">
                <div class="stats-card-background">
                    <div class="stats-card-pattern"></div>
                </div>
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <div class="stats-card-icon licenses"></div>
                        <div class="stats-card-menu">
                            <button class="stats-menu-btn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value">{{ $stats['licenses_active'] ?? 0 }}</div>
                        <div class="stats-card-label">{{ trans('app.Active Licenses') }}</div>
                        <div class="stats-card-trend positive">
                            <i class="stats-trend-icon positive"></i>
                            <span>+8% {{ trans('app.from last month') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Errors Today Stats Card -->
            <div class="stats-card stats-card-danger animate-slide-up animate-delay-400">
                <div class="stats-card-background">
                    <div class="stats-card-pattern"></div>
                </div>
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <div class="stats-card-icon errors"></div>
                        <div class="stats-card-menu">
                            <button class="stats-menu-btn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value">{{ $stats['api_errors_today'] ?? 0 }}</div>
                        <div class="stats-card-label">{{ trans('app.API Errors Today') }}</div>
                        <div class="stats-card-trend negative">
                            <i class="stats-trend-icon negative"></i>
                            <span>{{ $stats['api_errors_this_month'] ?? 0 }} {{ trans('app.this month') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Invoice Stats Grid (4 per row, matches existing cards) -->
        <div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced mt-6">
            <!-- Invoice: Total Count -->
            <div class="stats-card stats-card-neutral animate-slide-up">
                <div class="stats-card-background">
                    <div class="stats-card-pattern"></div>
                </div>
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <div class="stats-card-icon invoices"></div>
                        <div class="stats-card-menu">
                            <button class="stats-menu-btn"></button>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value">{{ $stats['invoices_count'] ?? 0 }}</div>
                        <div class="stats-card-label">{{ trans('app.Total Invoices') }}</div>
                        <div class="stats-card-subvalue text-sm text-gray-500 mt-1">
                            {{ trans('app.Total Invoice Amount') }}:
                            ${{ number_format($stats['invoices_total_amount'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Invoice: Total Amount -->
            <div class="stats-card stats-card-primary animate-slide-up animate-delay-200">
                <div class="stats-card-background">
                    <div class="stats-card-pattern"></div>
                </div>
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <div class="stats-card-icon amount"></div>
                        <div class="stats-card-menu">
                            <button class="stats-menu-btn"></button>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value">${{ number_format($stats['invoices_total_amount'] ?? 0, 2) }}
                        </div>
                        <div class="stats-card-label">{{ trans('app.Total Invoice Amount') }}</div>
                        <div class="stats-card-subvalue text-sm text-gray-500 mt-1">{{ trans('app.Total Invoices') }}:
                            {{ $stats['invoices_count'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Invoice: Paid Amount -->
            <div class="stats-card stats-card-success animate-slide-up animate-delay-400">
                <div class="stats-card-background">
                    <div class="stats-card-pattern"></div>
                </div>
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <div class="stats-card-icon paid"></div>
                        <div class="stats-card-menu">
                            <button class="stats-menu-btn"></button>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value">{{ $stats['invoices_paid_count'] ?? 0 }}</div>
                        <div class="stats-card-label">{{ trans('app.Paid Invoices') }}</div>
                        <div class="stats-card-subvalue text-sm text-gray-500 mt-1">{{ trans('app.Amount') }}:
                            ${{ number_format($stats['invoices_paid_amount'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Invoice: Cancelled Amount -->
            <div class="stats-card stats-card-danger animate-slide-up animate-delay-600">
                <div class="stats-card-background">
                    <div class="stats-card-pattern"></div>
                </div>
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <div class="stats-card-icon cancelled"></div>
                        <div class="stats-card-menu">
                            <button class="stats-menu-btn"></button>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value">{{ $stats['invoices_cancelled_count'] ?? 0 }}</div>
                        <div class="stats-card-label">{{ trans('app.Cancelled Invoices') }}</div>
                        <div class="stats-card-subvalue text-sm text-gray-500 mt-1">{{ trans('app.Amount') }}:
                            ${{ number_format($stats['invoices_cancelled_amount'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Items Grid -->
        <div class="latest-items-grid">
            <!-- Latest Tickets Card -->
            <div class="latest-item-card">
                <div class="latest-item-header">
                    <div class="latest-item-title-section">
                        <h3 class="latest-item-title">
                            <i class="fas fa-ticket-alt latest-item-title-icon"></i>
                            {{ trans('app.Latest Tickets') }}
                        </h3>
                        <a href="{{ route('admin.tickets.index') }}" class="latest-item-view-all">
                            {{ trans('app.View All') }}
                        </a>
                    </div>
                    <p class="latest-item-subtitle">{{ trans('app.Recent customer support requests') }}</p>
                </div>
                <div class="latest-item-content">
                    @forelse($latestTickets ?? [] as $ticket)
                    <div class="latest-item-list">
                        <div class="latest-item-entry">
                            <div class="latest-item-entry-info">
                                <div class="latest-item-entry-title">{{ $ticket->subject }}</div>
                                <div class="latest-item-entry-details">
                                    {{ optional($ticket->user)->name }} • {{ $ticket->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="latest-item-entry-actions">
                                <span class="latest-item-status-badge {{ $ticket->status }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="latest-item-view-btn">
                                    {{ trans('app.View') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="latest-item-empty">
                        <i class="fas fa-ticket-alt latest-item-empty-icon"></i>
                        <p class="latest-item-empty-text">{{ trans('app.No tickets available') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Latest Licenses Card -->
            <div class="latest-item-card">
                <div class="latest-item-header">
                    <div class="latest-item-title-section">
                        <h3 class="latest-item-title">
                            <i class="fas fa-key latest-item-title-icon"></i>
                            {{ trans('app.Latest Licenses') }}
                        </h3>
                        <a href="{{ route('admin.products.index') }}" class="latest-item-view-all">
                            {{ trans('app.View All') }}
                        </a>
                    </div>
                    <p class="latest-item-subtitle">{{ trans('app.Recently issued licenses') }}</p>
                </div>
                <div class="latest-item-content">
                    @forelse($latestLicenses ?? [] as $license)
                    <div class="latest-item-list">
                        <div class="latest-item-entry">
                            <div class="latest-item-entry-info">
                                <div class="latest-item-entry-title">{{ $license->purchase_code }}</div>
                                <div class="latest-item-entry-details">
                                    {{ optional($license->customer)->email }} • {{ optional($license->product)->name }}
                                </div>
                            </div>
                            <div class="latest-item-entry-actions">
                                <span class="latest-item-status-badge {{ $license->status }}">
                                    {{ ucfirst($license->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="latest-item-empty">
                        <i class="fas fa-key latest-item-empty-icon"></i>
                        <p class="latest-item-empty-text">{{ trans('app.No licenses available') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Enhanced Quick Actions with Modern Design -->
        <div class="admin-card quick-actions-card animate-fade-scale animate-delay-500">
            <div class="admin-section-content">
                <div class="flex items-center">
                    <div class="quick-actions-icon">
                        <i class="fas fa-bolt w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="admin-card-title">{{ trans('app.Quick Actions') }}</h3>
                        <p class="admin-card-subtitle">{{ trans('app.Frequently used administrative actions') }}</p>
                    </div>
                </div>
            </div>
            <div class="admin-card-content">
                <div class="quick-actions-grid">
                    <a href="{{ route('admin.products.create') }}" class="quick-action-btn primary">
                        <div class="quick-action-icon product"></div>
                        <div class="quick-action-content">
                            <span class="quick-action-title">{{ trans('app.Product') }}</span>
                            <span class="quick-action-desc">{{ trans('app.Create New Product') }}</span>
                        </div>
                        <div class="quick-action-arrow"></div>
                    </a>

                    <a href="{{ route('admin.tickets.index') }}" class="quick-action-btn warning">
                        <div class="quick-action-icon tickets"></div>
                        <div class="quick-action-content">
                            <span class="quick-action-title">{{ trans('app.Manage Tickets') }}</span>
                            <span class="quick-action-desc">{{ trans('app.View support tickets') }}</span>
                        </div>
                        <div class="quick-action-arrow"></div>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="quick-action-btn success">
                        <div class="quick-action-icon users"></div>
                        <div class="quick-action-content">
                            <span class="quick-action-title">{{ trans('app.Manage Users') }}</span>
                            <span class="quick-action-desc">{{ trans('app.User Management') }}</span>
                        </div>
                        <div class="quick-action-arrow"></div>
                    </a>

                    <a href="{{ route('admin.settings.index') }}" class="quick-action-btn info">
                        <div class="quick-action-icon settings"></div>
                        <div class="quick-action-content">
                            <span class="quick-action-title">{{ trans('app.Settings') }}</span>
                            <span class="quick-action-desc">{{ trans('app.System configuration') }}</span>
                        </div>
                        <div class="quick-action-arrow"></div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Enhanced Charts Section -->
        <div class="dashboard-grid dashboard-grid-2">
            <!-- API Requests Chart -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-chart-line w-5 h-5 mr-2 text-blue-500"></i>
                            <h3 class="admin-card-title">{{ trans('app.API Requests') }}</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <select class="admin-form-input" data-action="change-api-period">
                                <option value="daily">{{ trans('app.Daily') }}</option>
                                <option value="hourly">{{ trans('app.Hourly') }}</option>
                            </select>
                            <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                                data-chart="apiRequests" data-format="csv">
                                {{ trans('app.Export') }}
                            </button>
                        </div>
                        <noscript>
                            <div class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                                {{ trans('app.Export functionality requires JavaScript to be enabled') }}
                            </div>
                        </noscript>
                    </div>
                </div>
                <div class="admin-card-content">
                    <div class="chart-container">
                        <canvas id="apiRequestsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- API Performance Chart -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-chart-bar w-5 h-5 mr-2 text-green-500"></i>
                            <h3 class="admin-card-title">{{ trans('app.API Performance') }}</h3>
                        </div>
                        <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                            data-chart="apiPerformance" data-format="csv">
                            {{ trans('app.Export') }}
                        </button>
                        <noscript>
                            <div class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                                {{ trans('app.Export functionality requires JavaScript to be enabled') }}
                            </div>
                        </noscript>
                    </div>
                </div>
                <div class="admin-card-content">
                    <div class="chart-container">
                        <canvas id="apiPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Overview Charts -->
        <div class="dashboard-grid dashboard-grid-2">
            <!-- System Overview Chart -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-chart-bar w-5 h-5 mr-2 text-purple-500"></i>
                            <h3 class="admin-card-title">{{ trans('app.System Overview') }}</h3>
                        </div>
                        <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                            data-chart="systemOverview" data-format="csv">
                            {{ trans('app.Export') }}
                        </button>
                        <noscript>
                            <div class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                                {{ trans('app.Export functionality requires JavaScript to be enabled') }}
                            </div>
                        </noscript>
                    </div>
                </div>
                <div class="admin-card-content">
                    <div class="chart-container">
                        <canvas id="systemOverviewChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- License Distribution Chart -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-chart-pie w-5 h-5 mr-2 text-orange-500"></i>
                            <h3 class="admin-card-title">{{ trans('app.License Distribution') }}</h3>
                        </div>
                        <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                            data-chart="licenseDistribution" data-format="csv">
                            {{ trans('app.Export') }}
                        </button>
                        <noscript>
                            <div class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                                {{ trans('app.Export functionality requires JavaScript to be enabled') }}
                            </div>
                        </noscript>
                    </div>
                </div>
                <div class="admin-card-content">
                    <div class="chart-container">
                        <canvas id="licenseDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue and Activity Charts -->
        <div class="dashboard-grid dashboard-grid-1">
            <!-- Revenue Chart -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-chart-line w-5 h-5 mr-2 text-emerald-500"></i>
                            <h3 class="admin-card-title">{{ trans('app.Revenue Overview') }}</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <select class="admin-form-input" data-action="change-chart-period">
                                <option value="monthly">{{ trans('app.Monthly') }}</option>
                                <option value="quarterly">{{ trans('app.Quarterly') }}</option>
                                <option value="yearly">{{ trans('app.Yearly') }}</option>
                            </select>
                            <noscript>
                                <div class="text-sm text-amber-600 dark:text-amber-400">
                                    {{ trans('app.Chart period selection requires JavaScript to be enabled') }}
                                </div>
                            </noscript>
                            <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                                data-chart="revenue" data-format="csv">
                                {{ trans('app.Export') }}
                            </button>
                            <noscript>
                                <div class="text-sm text-amber-600 dark:text-amber-400">
                                    {{ trans('app.Export functionality requires JavaScript to be enabled') }}
                                </div>
                            </noscript>
                        </div>
                    </div>
                </div>
                <div class="admin-card-content">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Activity Timeline Chart -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-clock w-5 h-5 mr-2 text-purple-500"></i>
                            <h3 class="admin-card-title">{{ trans('app.Activity Timeline') }}</h3>
                        </div>
                        <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                            data-chart="activityTimeline" data-format="csv">
                            {{ trans('app.Export') }}
                        </button>
                        <noscript>
                            <div class="text-sm text-amber-600 dark:text-amber-400">
                                {{ trans('app.Export functionality requires JavaScript to be enabled') }}
                            </div>
                        </noscript>
                    </div>
                </div>
                <div class="admin-card-content">
                    <div class="chart-container">
                        <canvas id="activityTimelineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection
