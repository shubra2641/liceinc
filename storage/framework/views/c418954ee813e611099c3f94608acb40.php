<?php $__env->startSection('admin-content'); ?>
<!-- Enhanced Professional Dashboard Page -->
<div class="admin-page">
    <div class="dashboard-content">
        <!-- Modern Page Header with Gradient -->
        <div class="admin-page-header modern-header">
            <div class="admin-page-header-content">
                <div class="admin-page-title">
                    <h1 class="gradient-text"><?php echo e(trans('app.Dashboard Overview')); ?></h1>
                    <p class="admin-page-subtitle">
                        <?php echo e(trans('app.Monitor your license management system performance and key metrics')); ?>

                    </p>
                </div>
                <div class="admin-page-actions">
                    <div class="header-stats">
                        <div class="header-stat">
                            <span class="stat-label"><?php echo e(trans('app.Today')); ?></span>
                            <span class="stat-value"><?php echo e(\Carbon\Carbon::now()->format('M d, Y')); ?></span>
                        </div>
                        <div class="header-stat">
                            <span class="stat-label"><?php echo e(trans('app.System Status')); ?></span>
                            <span class="stat-value <?php echo e($isMaintenance ? 'status-offline' : 'status-online'); ?>">
                                <span class="status-dot"></span>
                                <?php echo e($isMaintenance ? trans('app.Offline') : trans('app.Online')); ?>

                            </span>
                        </div>
                        <div class="header-stat">
                            <span class="stat-label"><?php echo e(trans('app.System Version')); ?></span>
                            <span class="stat-value">
                                v<?php echo e(\App\Helpers\VersionHelper::getCurrentVersion()); ?>

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
                        <div class="stats-card-value"><?php echo e($stats['api_requests_today'] ?? 0); ?></div>
                        <div class="stats-card-label"><?php echo e(trans('app.API Requests Today')); ?></div>
                        <div class="stats-card-trend positive">
                            <i class="stats-trend-icon positive"></i>
                            <span><?php echo e($stats['api_requests_this_month'] ?? 0); ?> <?php echo e(trans('app.this month')); ?></span>
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
                        <div class="stats-card-value"><?php echo e($stats['tickets_open'] ?? 0); ?></div>
                        <div class="stats-card-label"><?php echo e(trans('app.Open Tickets')); ?></div>
                        <div class="stats-card-trend negative">
                            <i class="stats-trend-icon negative"></i>
                            <span>-5% <?php echo e(trans('app.from last month')); ?></span>
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
                        <div class="stats-card-value"><?php echo e($stats['licenses_active'] ?? 0); ?></div>
                        <div class="stats-card-label"><?php echo e(trans('app.Active Licenses')); ?></div>
                        <div class="stats-card-trend positive">
                            <i class="stats-trend-icon positive"></i>
                            <span>+8% <?php echo e(trans('app.from last month')); ?></span>
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
                        <div class="stats-card-value"><?php echo e($stats['api_errors_today'] ?? 0); ?></div>
                        <div class="stats-card-label"><?php echo e(trans('app.API Errors Today')); ?></div>
                        <div class="stats-card-trend negative">
                            <i class="stats-trend-icon negative"></i>
                            <span><?php echo e($stats['api_errors_this_month'] ?? 0); ?> <?php echo e(trans('app.this month')); ?></span>
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
                        <div class="stats-card-value"><?php echo e($stats['invoices_count'] ?? 0); ?></div>
                        <div class="stats-card-label"><?php echo e(trans('app.Total Invoices')); ?></div>
                        <div class="stats-card-subvalue text-sm text-gray-500 mt-1">
                            <?php echo e(trans('app.Total Invoice Amount')); ?>:
                            $<?php echo e(number_format($stats['invoices_total_amount'] ?? 0, 2)); ?></div>
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
                        <div class="stats-card-value">$<?php echo e(number_format($stats['invoices_total_amount'] ?? 0, 2)); ?>

                        </div>
                        <div class="stats-card-label"><?php echo e(trans('app.Total Invoice Amount')); ?></div>
                        <div class="stats-card-subvalue text-sm text-gray-500 mt-1"><?php echo e(trans('app.Total Invoices')); ?>:
                            <?php echo e($stats['invoices_count'] ?? 0); ?></div>
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
                        <div class="stats-card-value"><?php echo e($stats['invoices_paid_count'] ?? 0); ?></div>
                        <div class="stats-card-label"><?php echo e(trans('app.Paid Invoices')); ?></div>
                        <div class="stats-card-subvalue text-sm text-gray-500 mt-1"><?php echo e(trans('app.Amount')); ?>:
                            $<?php echo e(number_format($stats['invoices_paid_amount'] ?? 0, 2)); ?></div>
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
                        <div class="stats-card-value"><?php echo e($stats['invoices_cancelled_count'] ?? 0); ?></div>
                        <div class="stats-card-label"><?php echo e(trans('app.Cancelled Invoices')); ?></div>
                        <div class="stats-card-subvalue text-sm text-gray-500 mt-1"><?php echo e(trans('app.Amount')); ?>:
                            $<?php echo e(number_format($stats['invoices_cancelled_amount'] ?? 0, 2)); ?></div>
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
                            <?php echo e(trans('app.Latest Tickets')); ?>

                        </h3>
                        <a href="<?php echo e(route('admin.tickets.index')); ?>" class="latest-item-view-all">
                            <?php echo e(trans('app.View All')); ?>

                        </a>
                    </div>
                    <p class="latest-item-subtitle"><?php echo e(trans('app.Recent customer support requests')); ?></p>
                </div>
                <div class="latest-item-content">
                    <?php $__empty_1 = true; $__currentLoopData = $latestTickets ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="latest-item-list">
                        <div class="latest-item-entry">
                            <div class="latest-item-entry-info">
                                <div class="latest-item-entry-title"><?php echo e($ticket->subject); ?></div>
                                <div class="latest-item-entry-details">
                                    <?php echo e(optional($ticket->user)->name); ?> • <?php echo e($ticket->created_at->diffForHumans()); ?>

                                </div>
                            </div>
                            <div class="latest-item-entry-actions">
                                <span class="latest-item-status-badge <?php echo e($ticket->status); ?>">
                                    <?php echo e(ucfirst($ticket->status)); ?>

                                </span>
                                <a href="<?php echo e(route('admin.tickets.show', $ticket)); ?>" class="latest-item-view-btn">
                                    <?php echo e(trans('app.View')); ?>

                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="latest-item-empty">
                        <i class="fas fa-ticket-alt latest-item-empty-icon"></i>
                        <p class="latest-item-empty-text"><?php echo e(trans('app.No tickets available')); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Latest Licenses Card -->
            <div class="latest-item-card">
                <div class="latest-item-header">
                    <div class="latest-item-title-section">
                        <h3 class="latest-item-title">
                            <i class="fas fa-key latest-item-title-icon"></i>
                            <?php echo e(trans('app.Latest Licenses')); ?>

                        </h3>
                        <a href="<?php echo e(route('admin.products.index')); ?>" class="latest-item-view-all">
                            <?php echo e(trans('app.View All')); ?>

                        </a>
                    </div>
                    <p class="latest-item-subtitle"><?php echo e(trans('app.Recently issued licenses')); ?></p>
                </div>
                <div class="latest-item-content">
                    <?php $__empty_1 = true; $__currentLoopData = $latestLicenses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $license): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="latest-item-list">
                        <div class="latest-item-entry">
                            <div class="latest-item-entry-info">
                                <div class="latest-item-entry-title"><?php echo e($license->purchase_code); ?></div>
                                <div class="latest-item-entry-details">
                                    <?php echo e(optional($license->customer)->email); ?> • <?php echo e(optional($license->product)->name); ?>

                                </div>
                            </div>
                            <div class="latest-item-entry-actions">
                                <span class="latest-item-status-badge <?php echo e($license->status); ?>">
                                    <?php echo e(ucfirst($license->status)); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="latest-item-empty">
                        <i class="fas fa-key latest-item-empty-icon"></i>
                        <p class="latest-item-empty-text"><?php echo e(trans('app.No licenses available')); ?></p>
                    </div>
                    <?php endif; ?>
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
                        <h3 class="admin-card-title"><?php echo e(trans('app.Quick Actions')); ?></h3>
                        <p class="admin-card-subtitle"><?php echo e(trans('app.Frequently used administrative actions')); ?></p>
                    </div>
                </div>
            </div>
            <div class="admin-card-content">
                <div class="quick-actions-grid">
                    <a href="<?php echo e(route('admin.products.create')); ?>" class="quick-action-btn primary">
                        <div class="quick-action-icon product"></div>
                        <div class="quick-action-content">
                            <span class="quick-action-title"><?php echo e(trans('app.Product')); ?></span>
                            <span class="quick-action-desc"><?php echo e(trans('app.Create New Product')); ?></span>
                        </div>
                        <div class="quick-action-arrow"></div>
                    </a>

                    <a href="<?php echo e(route('admin.tickets.index')); ?>" class="quick-action-btn warning">
                        <div class="quick-action-icon tickets"></div>
                        <div class="quick-action-content">
                            <span class="quick-action-title"><?php echo e(trans('app.Manage Tickets')); ?></span>
                            <span class="quick-action-desc"><?php echo e(trans('app.View support tickets')); ?></span>
                        </div>
                        <div class="quick-action-arrow"></div>
                    </a>

                    <a href="<?php echo e(route('admin.users.index')); ?>" class="quick-action-btn success">
                        <div class="quick-action-icon users"></div>
                        <div class="quick-action-content">
                            <span class="quick-action-title"><?php echo e(trans('app.Manage Users')); ?></span>
                            <span class="quick-action-desc"><?php echo e(trans('app.User Management')); ?></span>
                        </div>
                        <div class="quick-action-arrow"></div>
                    </a>

                    <a href="<?php echo e(route('admin.settings.index')); ?>" class="quick-action-btn info">
                        <div class="quick-action-icon settings"></div>
                        <div class="quick-action-content">
                            <span class="quick-action-title"><?php echo e(trans('app.Settings')); ?></span>
                            <span class="quick-action-desc"><?php echo e(trans('app.System configuration')); ?></span>
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
                            <h3 class="admin-card-title"><?php echo e(trans('app.API Requests')); ?></h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <select class="admin-form-input" data-action="change-api-period">
                                <option value="daily"><?php echo e(trans('app.Daily')); ?></option>
                                <option value="hourly"><?php echo e(trans('app.Hourly')); ?></option>
                            </select>
                            <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                                data-chart="apiRequests" data-format="csv">
                                <?php echo e(trans('app.Export')); ?>

                            </button>
                        </div>
                        <noscript>
                            <div class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                                <?php echo e(trans('app.Export functionality requires JavaScript to be enabled')); ?>

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
                            <h3 class="admin-card-title"><?php echo e(trans('app.API Performance')); ?></h3>
                        </div>
                        <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                            data-chart="apiPerformance" data-format="csv">
                            <?php echo e(trans('app.Export')); ?>

                        </button>
                        <noscript>
                            <div class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                                <?php echo e(trans('app.Export functionality requires JavaScript to be enabled')); ?>

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
                            <h3 class="admin-card-title"><?php echo e(trans('app.System Overview')); ?></h3>
                        </div>
                        <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                            data-chart="systemOverview" data-format="csv">
                            <?php echo e(trans('app.Export')); ?>

                        </button>
                        <noscript>
                            <div class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                                <?php echo e(trans('app.Export functionality requires JavaScript to be enabled')); ?>

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
                            <h3 class="admin-card-title"><?php echo e(trans('app.License Distribution')); ?></h3>
                        </div>
                        <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                            data-chart="licenseDistribution" data-format="csv">
                            <?php echo e(trans('app.Export')); ?>

                        </button>
                        <noscript>
                            <div class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                                <?php echo e(trans('app.Export functionality requires JavaScript to be enabled')); ?>

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
                            <h3 class="admin-card-title"><?php echo e(trans('app.Revenue Overview')); ?></h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <select class="admin-form-input" data-action="change-chart-period">
                                <option value="monthly"><?php echo e(trans('app.Monthly')); ?></option>
                                <option value="quarterly"><?php echo e(trans('app.Quarterly')); ?></option>
                                <option value="yearly"><?php echo e(trans('app.Yearly')); ?></option>
                            </select>
                            <noscript>
                                <div class="text-sm text-amber-600 dark:text-amber-400">
                                    <?php echo e(trans('app.Chart period selection requires JavaScript to be enabled')); ?>

                                </div>
                            </noscript>
                            <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                                data-chart="revenue" data-format="csv">
                                <?php echo e(trans('app.Export')); ?>

                            </button>
                            <noscript>
                                <div class="text-sm text-amber-600 dark:text-amber-400">
                                    <?php echo e(trans('app.Export functionality requires JavaScript to be enabled')); ?>

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
                            <h3 class="admin-card-title"><?php echo e(trans('app.Activity Timeline')); ?></h3>
                        </div>
                        <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-chart"
                            data-chart="activityTimeline" data-format="csv">
                            <?php echo e(trans('app.Export')); ?>

                        </button>
                        <noscript>
                            <div class="text-sm text-amber-600 dark:text-amber-400">
                                <?php echo e(trans('app.Export functionality requires JavaScript to be enabled')); ?>

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



<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>