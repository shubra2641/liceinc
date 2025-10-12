<?php $__env->startSection('title', trans('app.reports_and_statistics')); ?>

<?php $__env->startSection('admin-content'); ?>
<!-- Professional Reports Page -->
<div class="admin-reports-page">
    <!-- Page Header -->
    <div class="admin-page-header modern-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="flex-grow-1">
                <h1 class="gradient-text">
                    <i class="fas fa-chart-line me-3"></i><?php echo e(trans('app.reports_and_statistics')); ?>

                </h1>
                <p class="admin-page-subtitle"><?php echo e(trans('app.comprehensive_analytics_and_insights')); ?></p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="admin-btn admin-btn-success admin-btn-m" data-action="refresh-reports">
                    <i class="fas fa-sync-alt me-2"></i>
                    <?php echo e(trans('app.refresh')); ?>

                </button>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-btn admin-btn-secondary admin-btn-m">
                    <i class="fas fa-arrow-left me-2"></i>
                    <?php echo e(trans('app.back_to_dashboard')); ?>

                </a>
            </div>
        </div>
    </div>

    <!-- Export Actions -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-download me-2"></i><?php echo e(trans('app.export_reports')); ?></h2>
            <div class="admin-section-actions">
                <span class="admin-badge admin-badge-info"><?php echo e(trans('app.multiple_formats')); ?></span>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="admin-card export-card">
                        <div class="admin-card-content">
                            <div class="export-card-header">
                                <div class="export-icon-wrapper">
                                    <i class="fas fa-file-pdf export-icon"></i>
                                </div>
                                <div class="export-content">
                                    <h4 class="export-title"><?php echo e(trans('app.export_pdf')); ?></h4>
                                    <p class="export-description"><?php echo e(trans('app.download_comprehensive_pdf_report')); ?></p>
                                </div>
                            </div>
                            <div class="export-card-footer">
                                <button id="export-pdf" class="admin-btn admin-btn-primary admin-btn-m w-100" data-format="pdf">
                                    <i class="fas fa-download me-2"></i><?php echo e(trans('app.download')); ?>

                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="admin-card export-card">
                        <div class="admin-card-content">
                            <div class="export-card-header">
                                <div class="export-icon-wrapper">
                                    <i class="fas fa-file-csv export-icon"></i>
                                </div>
                                <div class="export-content">
                                    <h4 class="export-title"><?php echo e(trans('app.export_csv')); ?></h4>
                                    <p class="export-description"><?php echo e(trans('app.download_data_for_analysis')); ?></p>
                                </div>
                            </div>
                            <div class="export-card-footer">
                                <button id="export-csv" class="admin-btn admin-btn-success admin-btn-m w-100" data-format="csv">
                                    <i class="fas fa-download me-2"></i><?php echo e(trans('app.download')); ?>

                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progressive Enhancement: Fallback for users without JavaScript -->
            <noscript>
                <div class="admin-alert admin-alert-warning mt-4">
                    <div class="admin-alert-content">
                        <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
                        <div>
                            <h4 class="admin-alert-title"><?php echo e(trans('app.javascript_required')); ?></h4>
                            <p class="admin-alert-message">
                                <?php echo e(trans('app.export_functionality_requires_javascript')); ?>

                                <a href="<?php echo e(route('admin.reports.export', ['format' => 'pdf'])); ?>" class="admin-link">
                                    <?php echo e(trans('app.download_pdf_report')); ?>

                                </a> |
                                <a href="<?php echo e(route('admin.reports.export', ['format' => 'csv'])); ?>" class="admin-link">
                                    <?php echo e(trans('app.download_csv_report')); ?>

                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </noscript>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-filter me-2"></i><?php echo e(trans('app.filters')); ?></h2>
            <div class="admin-section-actions">
                <span class="admin-badge admin-badge-info"><?php echo e(trans('app.customize_data_range')); ?></span>
            </div>
        </div>
        <div class="admin-section-content">
            <form action="<?php echo e(route('admin.reports.index')); ?>" method="GET" class="reports-filters-form">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="admin-form-group">
                            <label class="admin-form-label" for="date_from">
                                <i class="fas fa-calendar-alt me-1"></i><?php echo e(trans('app.date_from')); ?>

                            </label>
                            <input type="date" id="date_from" name="date_from" class="admin-form-input"
                                value="<?php echo e(request('date_from')); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-form-group">
                            <label class="admin-form-label" for="date_to">
                                <i class="fas fa-calendar-alt me-1"></i><?php echo e(trans('app.date_to')); ?>

                            </label>
                            <input type="date" id="date_to" name="date_to" class="admin-form-input"
                                value="<?php echo e(request('date_to')); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-form-group">
                            <label class="admin-form-label">&nbsp;</label>
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-m w-100">
                                <i class="fas fa-search me-2"></i><?php echo e(trans('app.apply_filters')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-chart-bar me-2"></i><?php echo e(trans('app.key_metrics')); ?></h2>
            <div class="admin-section-actions">
                <span class="admin-badge admin-badge-primary"><?php echo e(trans('app.system_overview')); ?></span>
            </div>
        </div>
        <div class="admin-section-content">
            <!-- Enhanced Statistics Section -->
            <div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
                <!-- Total Licenses Stats Card -->
                <div class="stats-card stats-card-primary animate-slide-up">
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
                            <div class="stats-card-value"><?php echo e(number_format($totalLicenses)); ?></div>
                            <div class="stats-card-label"><?php echo e(trans('app.Total_licenses')); ?></div>
                            <div class="stats-card-trend positive">
                                <i class="stats-trend-icon positive"></i>
                                <span>+12% <?php echo e(trans('app.from_last_month')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Licenses Stats Card -->
                <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
                    <div class="stats-card-background">
                        <div class="stats-card-pattern"></div>
                    </div>
                    <div class="stats-card-content">
                        <div class="stats-card-header">
                            <div class="stats-card-icon products"></div>
                            <div class="stats-card-menu">
                                <button class="stats-menu-btn">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                        </div>
                        <div class="stats-card-body">
                            <div class="stats-card-value"><?php echo e(number_format($activeLicenses)); ?></div>
                            <div class="stats-card-label"><?php echo e(trans('app.Active_licenses')); ?></div>
                            <div class="stats-card-trend positive">
                                <i class="stats-trend-icon positive"></i>
                                <span><?php echo e($totalLicenses > 0 ? round(($activeLicenses / $totalLicenses) * 100, 1) : 0); ?>% <?php echo e(trans('app.of_total')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expired Licenses Stats Card -->
                <div class="stats-card stats-card-warning animate-slide-up animate-delay-300">
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
                            <div class="stats-card-value"><?php echo e(number_format($expiredLicenses)); ?></div>
                            <div class="stats-card-label"><?php echo e(trans('app.expired_licenses')); ?></div>
                            <div class="stats-card-trend negative">
                                <i class="stats-trend-icon negative"></i>
                                <span><?php echo e($totalLicenses > 0 ? round(($expiredLicenses / $totalLicenses) * 100, 1) : 0); ?>% <?php echo e(trans('app.of_total')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Blocked IPs Stats Card -->
                <div class="stats-card stats-card-danger animate-slide-up animate-delay-400">
                    <div class="stats-card-background">
                        <div class="stats-card-pattern"></div>
                    </div>
                    <div class="stats-card-content">
                        <div class="stats-card-header">
                            <div class="stats-card-icon articles"></div>
                            <div class="stats-card-menu">
                                <button class="stats-menu-btn">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                        </div>
                        <div class="stats-card-body">
                            <div class="stats-card-value"><?php echo e($rateLimitedIPs->count()); ?></div>
                            <div class="stats-card-label"><?php echo e(trans('app.blocked_ips')); ?></div>
                            <div class="stats-card-trend negative">
                                <i class="stats-trend-icon negative"></i>
                                <span><?php echo e(trans('app.security_protection')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices & Revenue -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-receipt me-2"></i><?php echo e(trans('app.Invoices & Revenue')); ?></h2>
            <div class="admin-section-actions">
                <span class="admin-badge admin-badge-info"><?php echo e(trans('app.financial_overview')); ?></span>
            </div>
        </div>
        <div class="admin-section-content">
            <!-- Enhanced Revenue Statistics Section -->
            <div class="dashboard-grid dashboard-grid-3 stats-grid-enhanced mb-4">
                <!-- Total Paid Amount Stats Card -->
                <div class="stats-card stats-card-success animate-slide-up">
                    <div class="stats-card-background">
                        <div class="stats-card-pattern"></div>
                    </div>
                    <div class="stats-card-content">
                        <div class="stats-card-header">
                            <div class="stats-card-icon products"></div>
                            <div class="stats-card-menu">
                                <button class="stats-menu-btn">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                        </div>
                        <div class="stats-card-body">
                            <div class="stats-card-value">$<?php echo e(number_format($invoiceStatusTotals['paid'] ?? 0, 2)); ?></div>
                            <div class="stats-card-label"><?php echo e(trans('app.Total Paid Amount')); ?></div>
                            <div class="stats-card-trend positive">
                                <i class="stats-trend-icon positive"></i>
                                <span>+8.2% <?php echo e(trans('app.from_last_month')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Due Soon Amount Stats Card -->
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
                            <div class="stats-card-value">$<?php echo e(number_format($invoiceStatusTotals['due_soon'] ?? 0, 2)); ?></div>
                            <div class="stats-card-label"><?php echo e(trans('app.Due Soon Amount')); ?></div>
                            <div class="stats-card-trend negative">
                                <i class="stats-trend-icon negative"></i>
                                <span><?php echo e(trans('app.requires_attention')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancelled/Unpaid Amount Stats Card -->
                <div class="stats-card stats-card-danger animate-slide-up animate-delay-300">
                    <div class="stats-card-background">
                        <div class="stats-card-pattern"></div>
                    </div>
                    <div class="stats-card-content">
                        <div class="stats-card-header">
                            <div class="stats-card-icon articles"></div>
                            <div class="stats-card-menu">
                                <button class="stats-menu-btn">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                        </div>
                        <div class="stats-card-body">
                            <div class="stats-card-value">$<?php echo e(number_format($invoiceStatusTotals['cancelled'] ?? 0, 2)); ?></div>
                            <div class="stats-card-label"><?php echo e(trans('app.Cancelled/Unpaid Amount')); ?></div>
                            <div class="stats-card-trend negative">
                                <i class="stats-trend-icon negative"></i>
                                <span>-2.1% <?php echo e(trans('app.from_last_month')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card chart-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-chart-line me-2"></i><?php echo e(trans('app.monthly_revenue_chart')); ?>

                    </h3>
                    <div class="admin-card-actions">
                        <button class="admin-btn admin-btn-outline-primary admin-btn-sm" data-action="export-chart" data-chart="invoicesMonthly" data-format="csv">
                            <i class="fas fa-download me-1"></i><?php echo e(trans('app.Export')); ?>

                        </button>
                    </div>
                </div>
                <div class="admin-card-content">
                    <div class="chart-container">
                        <canvas id="invoicesMonthlyChart" width="400" height="120" data-chart-data='<?php echo json_encode($invoiceMonthlyAmounts ?? [], 15, 512) ?>'></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Charts -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-dollar-sign me-2"></i><?php echo e(trans('app.revenue_analytics')); ?></h2>
            <div class="admin-section-actions">
                <span class="admin-badge admin-badge-success"><?php echo e(trans('app.financial_insights')); ?></span>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-4">
                <!-- Monthly Revenue Chart -->
                <div class="col-lg-12">
                    <div class="admin-card chart-card">
                        <div class="admin-section-content">
                            <h3 class="admin-card-title">
                                <i class="fas fa-chart-line me-2"></i><?php echo e(trans('app.monthly_revenue_from_licenses')); ?>

                            </h3>
                            <div class="admin-card-actions">
                                <button class="admin-btn admin-btn-outline-success admin-btn-sm" data-action="export-chart" data-chart="monthlyRevenue" data-format="csv">
                                    <i class="fas fa-download me-1"></i><?php echo e(trans('app.Export')); ?>

                                </button>
                            </div>
                        </div>
                        <div class="admin-card-content">
                            <div class="chart-container">
                                <canvas id="monthlyRevenueChart" width="400" height="120" data-chart-data='<?php echo json_encode($monthlyRevenue ?? [], 15, 512) ?>'></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Data Visualization -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-chart-pie me-2"></i><?php echo e(trans('app.data_visualization')); ?></h2>
            <div class="admin-section-actions">
                <span class="admin-badge admin-badge-info"><?php echo e(trans('app.interactive_charts')); ?></span>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-4">
                <!-- System Overview Chart -->
                <div class="col-lg-12 mb-4">
                    <div class="admin-card chart-card">
                        <div class="admin-section-content">
                            <h3 class="admin-card-title">
                                <i class="fas fa-cogs me-2"></i><?php echo e(trans('app.system_overview')); ?>

                            </h3>
                            <div class="admin-card-actions">
                                <button class="admin-btn admin-btn-outline-secondary admin-btn-sm" data-action="export-chart" data-chart="systemOverview" data-format="csv">
                                    <i class="fas fa-download me-1"></i><?php echo e(trans('app.Export')); ?>

                                </button>
                            </div>
                        </div>
                        <div class="admin-card-content">
                            <div class="chart-container">
                                <canvas id="systemOverviewChart" width="400" height="150" 
                                    data-chart-data='<?php echo json_encode($systemOverviewData ?? [], 15, 512) ?>'></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- License Type Distribution Chart -->
                <div class="col-lg-6">
                    <div class="admin-card chart-card">
                        <div class="admin-section-content">
                            <h3 class="admin-card-title">
                                <i class="fas fa-tags me-2"></i><?php echo e(trans('app.license_type_distribution')); ?>

                            </h3>
                            <div class="admin-card-actions">
                                <button class="admin-btn admin-btn-outline-primary admin-btn-sm" data-action="export-chart" data-chart="licenseType" data-format="csv">
                                    <i class="fas fa-download me-1"></i><?php echo e(trans('app.Export')); ?>

                                </button>
                            </div>
                        </div>
                        <div class="admin-card-content">
                            <div class="chart-container">
                                <canvas id="licenseTypeChart" width="400" height="200" 
                                    data-chart-data='<?php echo json_encode($licenseTypeData ?? [], 15, 512) ?>'></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Licenses Chart -->
                <div class="col-lg-6">
                    <div class="admin-card chart-card">
                        <div class="admin-section-content">
                            <h3 class="admin-card-title">
                                <i class="fas fa-chart-line me-2"></i><?php echo e(trans('app.monthly_licenses')); ?>

                            </h3>
                            <div class="admin-card-actions">
                                <button class="admin-btn admin-btn-outline-success admin-btn-sm" data-action="export-chart" data-chart="monthlyLicenses" data-format="csv">
                                    <i class="fas fa-download me-1"></i><?php echo e(trans('app.Export')); ?>

                                </button>
                            </div>
                        </div>
                        <div class="admin-card-content">
                            <div class="chart-container">
                                <canvas id="monthlyLicensesChart" width="400" height="200" 
                                    data-chart-data='<?php echo json_encode($monthlyLicenses ?? [], 15, 512) ?>'></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Registrations Chart -->
                <div class="col-lg-6">
                    <div class="admin-card chart-card">
                        <div class="admin-section-content">
                            <h3 class="admin-card-title">
                                <i class="fas fa-user-plus me-2"></i><?php echo e(trans('app.user_registrations')); ?>

                            </h3>
                            <div class="admin-card-actions">
                                <button class="admin-btn admin-btn-outline-warning admin-btn-sm" data-action="export-chart" data-chart="userRegistrations" data-format="csv">
                                    <i class="fas fa-download me-1"></i><?php echo e(trans('app.Export')); ?>

                                </button>
                            </div>
                        </div>
                        <div class="admin-card-content">
                            <div class="chart-container">
                                <canvas id="userRegistrationsChart" width="400" height="200" 
                                    data-chart-data='<?php echo json_encode($userRegistrations ?? [], 15, 512) ?>'></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline Chart -->
                <div class="col-lg-6">
                    <div class="admin-card chart-card">
                        <div class="admin-section-content">
                            <h3 class="admin-card-title">
                                <i class="fas fa-calendar-day me-2"></i><?php echo e(trans('app.activity_timeline')); ?>

                            </h3>
                            <div class="admin-card-actions">
                                <button class="admin-btn admin-btn-outline-info admin-btn-sm" data-action="export-chart" data-chart="activityTimeline" data-format="csv">
                                    <i class="fas fa-download me-1"></i><?php echo e(trans('app.Export')); ?>

                                </button>
                            </div>
                        </div>
                        <div class="admin-card-content">
                            <div class="chart-container">
                                <canvas id="activityTimelineChart" width="400" height="200" 
                                    data-chart-data='<?php echo json_encode($activityTimeline ?? [], 15, 512) ?>'></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Detailed Reports -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-table me-2"></i><?php echo e(trans('app.detailed_reports')); ?></h2>
            <div class="admin-section-actions">
                <span class="admin-badge admin-badge-info"><?php echo e(trans('app.activity_logs')); ?></span>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-4">
                <!-- Top Products Table -->
                <div class="col-lg-6">
                    <div class="admin-card table-card">
                        <div class="admin-section-content">
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="admin-card-title">
                                    <i class="fas fa-trophy me-2"></i><?php echo e(trans('app.top_products')); ?>

                                </h3>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="admin-badge admin-badge-warning"><?php echo e($topProducts->count()); ?> <?php echo e(trans('app.products')); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="admin-card-content">
                            <?php if($topProducts->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <i class="fas fa-box me-1"></i><?php echo e(trans('app.Product')); ?>

                                            </th>
                                            <th>
                                                <i class="fas fa-hashtag me-1"></i><?php echo e(trans('app.Licenses')); ?>

                                            </th>
                                            <th>
                                                <i class="fas fa-dollar-sign me-1"></i><?php echo e(trans('app.Revenue')); ?>

                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $topProducts->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="product-avatar me-3">
                                                        <i class="fas fa-box"></i>
                                                    </div>
                                                    <span class="fw-medium"><?php echo e($product->name); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="admin-badge admin-badge-primary">
                                                    <?php echo e($product->licenses_count); ?>

                                                </span>
                                            </td>
                                            <td class="text-success fw-bold">
                                                $<?php echo e(number_format($product->revenue ?? 0, 2)); ?>

                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-content">
                                    <div class="empty-state-icon-wrapper">
                                        <i class="fas fa-trophy admin-empty-state-icon"></i>
                                    </div>
                                    <h4 class="admin-empty-state-title"><?php echo e(trans('app.No_products_found')); ?></h4>
                                    <p class="admin-empty-state-description"><?php echo e(trans('app.no_top_products_available')); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                        <div class="admin-card-content">
                            <?php if($recentActivities->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <i class="fas fa-user me-1"></i><?php echo e(trans('app.User')); ?>

                                            </th>
                                            <th>
                                                <i class="fas fa-tasks me-1"></i><?php echo e(trans('app.Action')); ?>

                                            </th>
                                            <th>
                                                <i class="fas fa-calendar me-1"></i><?php echo e(trans('app.date')); ?>

                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $recentActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-3">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <span class="fw-medium"><?php echo e($activity->license->user->name ?? 'N/A'); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($activity->action === 'activate'): ?>
                                                    <span class="admin-badge admin-badge-success"><?php echo e(trans('app.license_activated')); ?></span>
                                                <?php elseif($activity->action === 'add_domain'): ?>
                                                    <span class="admin-badge admin-badge-info"><?php echo e(trans('app.domain_added')); ?></span>
                                                <?php elseif($activity->action === 'check_status'): ?>
                                                    <span class="admin-badge admin-badge-warning"><?php echo e(trans('app.Status_checked')); ?></span>
                                                <?php else: ?>
                                                    <span class="admin-badge admin-badge-secondary"><?php echo e($activity->action ?? 'N/A'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted">
                                                <?php echo e($activity->created_at->format('M d, Y H:i')); ?>

                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-content">
                                    <div class="empty-state-icon-wrapper">
                                        <i class="fas fa-history admin-empty-state-icon"></i>
                                    </div>
                                    <h4 class="admin-empty-state-title"><?php echo e(trans('app.No_activity_found')); ?></h4>
                                    <p class="admin-empty-state-description"><?php echo e(trans('app.no_recent_activities')); ?></p>
                                    <div class="admin-empty-state-actions">
                                        <button class="admin-btn admin-btn-primary admin-btn-m" data-action="refresh-activity">
                                            <i class="fas fa-sync-alt me-2"></i><?php echo e(trans('app.Refresh')); ?>

                                        </button>
                                        <button class="admin-btn admin-btn-outline-secondary admin-btn-m" data-action="view-all-activities">
                                            <i class="fas fa-list me-2"></i><?php echo e(trans('app.View All Activities')); ?>

                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Blocked IPs Table -->
                <div class="col-lg-6">
                    <div class="admin-card table-card">
                        <div class="admin-section-content">
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="admin-card-title">
                                    <i class="fas fa-shield-alt me-2"></i><?php echo e(trans('app.blocked_ips_due_to_rate_limiting')); ?>

                                </h3>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="admin-badge admin-badge-danger"><?php echo e($rateLimitedIPs->count()); ?> <?php echo e(trans('app.blocked')); ?></span>
                                    <?php if($rateLimitedIPs->count() > 0): ?>
                                    <button class="admin-btn admin-btn-outline-warning admin-btn-sm" data-action="clear-blocked-ips">
                                        <i class="fas fa-trash me-1"></i><?php echo e(trans('app.Clear All')); ?>

                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="admin-card-content">
                            <?php if($rateLimitedIPs->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <i class="fas fa-globe me-1"></i><?php echo e(trans('app.ip_address')); ?>

                                            </th>
                                            <th>
                                                <i class="fas fa-exclamation-triangle me-1"></i><?php echo e(trans('app.attempts')); ?>

                                            </th>
                                            <th>
                                                <i class="fas fa-clock me-1"></i><?php echo e(trans('app.blocked_until')); ?>

                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $rateLimitedIPs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blockedIP): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="ip-avatar me-3">
                                                        <i class="fas fa-globe"></i>
                                                    </div>
                                                    <span class="font-monospace"><?php echo e($blockedIP['ip']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="admin-badge admin-badge-danger">
                                                    <?php echo e($blockedIP['attempts']); ?> <?php echo e(trans('app.attempts')); ?>

                                                </span>
                                            </td>
                                            <td class="text-muted">
                                                <?php echo e($blockedIP['blocked_until']->format('M d, Y H:i:s')); ?>

                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-content">
                                    <div class="empty-state-icon-wrapper">
                                        <i class="fas fa-shield-alt admin-empty-state-icon"></i>
                                    </div>
                                    <h4 class="admin-empty-state-title"><?php echo e(trans('app.No_blocked_ips')); ?></h4>
                                    <p class="admin-empty-state-description"><?php echo e(trans('app.no_blocked_ips_found')); ?></p>
                                    <div class="admin-empty-state-actions">
                                        <button class="admin-btn admin-btn-success admin-btn-m" data-action="refresh-reports">
                                            <i class="fas fa-sync-alt me-2"></i><?php echo e(trans('app.Refresh')); ?>

                                        </button>
                                        <button class="admin-btn admin-btn-outline-info admin-btn-m" data-action="view-security-logs">
                                            <i class="fas fa-shield-alt me-2"></i><?php echo e(trans('app.View Security Logs')); ?>

                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/admin/reports.blade.php ENDPATH**/ ?>