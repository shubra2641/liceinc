<?php $__env->startSection('title', trans('app.Welcome To')); ?>
<?php $__env->startSection('page-title', trans('app.Welcome To')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Manage your licenses and products')); ?>

<?php $__env->startSection('seo_title', $siteSeoTitle ?? trans('app.Welcome To')); ?>
<?php $__env->startSection('meta_description', $siteSeoDescription ?? trans('app.Manage your licenses, track downloads, and access support
from your personal dashboard')); ?>

<?php $__env->startSection('content'); ?>


<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-bolt"></i>
                <?php echo e(trans('app.Welcome To')); ?>, <?php echo e($siteName); ?>!
            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Manage your licenses, track downloads, and access support from your personal dashboard')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Stats Cards -->
            <div class="user-stats-grid">
                <!-- Total Customers -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Customers')); ?></div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">
                        <?php echo e(number_format($stats['customers'] ?? 0)); ?>

                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.All registered users')); ?></p>
                </div>

                <!-- Total Licenses -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Licenses')); ?></div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(number_format($stats['licenses'] ?? 0)); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.All issued licenses')); ?></p>
                </div>

                <!-- Total Tickets -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Tickets')); ?></div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(number_format($stats['tickets'] ?? 0)); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Support requests')); ?></p>
                </div>

                <!-- Total Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Invoices')); ?></div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(number_format($stats['invoices'] ?? 0)); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.All invoices')); ?></p>
                </div>

                <!-- Total Products -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Products')); ?></div>
                        <div class="user-stat-icon indigo">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(number_format($stats['products'] ?? \App\Models\Product::count())); ?>

                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Available products')); ?></p>
                </div>

                <!-- Active Licenses -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Active Licenses')); ?></div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(number_format($stats['active_licenses'] ?? 0)); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Currently active')); ?></p>
                </div>

                <!-- Paid Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Paid Invoices')); ?></div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(number_format($stats['paid_invoices'] ?? 0)); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Completed payments')); ?></p>
                </div>

                <!-- Open Tickets -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Open Tickets')); ?></div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(number_format($stats['open_tickets'] ?? 0)); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Awaiting response')); ?></p>
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
                            <h3><?php echo e(trans('app.Support Tickets')); ?></h3>
                            <p><?php echo e(trans('app.Get help and support')); ?></p>
                        </div>
                    </div>
                    <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('user.tickets.index')); ?>" class="user-action-button">
                        <i class="fas fa-ticket-alt"></i>
                        <?php echo e(trans('app.View Tickets')); ?>

                    </a>
                    <?php else: ?>
                    <a href="<?php echo e(route('support.tickets.create')); ?>" class="user-action-button">
                        <i class="fas fa-plus"></i>
                        <?php echo e(trans('app.Create Ticket')); ?>

                    </a>
                    <?php endif; ?>
                </div>

                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon purple">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="user-action-content">
                            <h3><?php echo e(trans('app.My Invoices')); ?></h3>
                            <p><?php echo e(trans('app.View and manage invoices')); ?></p>
                        </div>
                    </div>
                    <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('user.invoices.index')); ?>" class="user-action-button">
                        <i class="fas fa-eye"></i>
                        <?php echo e(trans('app.View Invoices')); ?>

                    </a>
                    <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="user-action-button">
                        <i class="fas fa-sign-in-alt"></i>
                        <?php echo e(trans('app.Sign In')); ?>

                    </a>
                    <?php endif; ?>
                </div>

                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon blue">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="user-action-content">
                            <h3><?php echo e(trans('app.Knowledge Base')); ?></h3>
                            <p><?php echo e(trans('app.Find answers and guides')); ?></p>
                        </div>
                    </div>
                    <a href="<?php echo e(route('kb.index')); ?>" class="user-action-button">
                        <i class="fas fa-search"></i>
                        <?php echo e(trans('app.Explore KB')); ?>

                    </a>
                </div>
            </div>

            <!-- Available Products Section -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-box"></i>
                        <?php echo e(trans('app.Available Products')); ?>

                    </div>
                    <p class="user-card-subtitle"><?php echo e(trans('app.Discover and purchase new products')); ?></p>
                </div>
                <div class="user-card-content">
                    <?php if($products->isEmpty()): ?>
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            <?php echo e(trans('app.No products available')); ?>

                        </h3>
                        <p class="user-empty-state-description">
                            <?php echo e(trans('app.Check back later for new products')); ?>

                        </p>
                    </div>
                    <?php else: ?>
                    <div class="user-products-grid">
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="user-product-card">
                            <div class="user-product-header">
                                <div>
                                    <div class="user-product-title-row">
                                        <h3 class="user-product-title"><?php echo e($product->name); ?></h3>
                                        <?php if($product->is_featured || $product->is_popular): ?>
                                        <span class="user-premium-badge">
                                            <i class="fas fa-crown"></i>
                                            <?php echo e(trans('app.Premium')); ?>

                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="user-product-version">v<?php echo e($product->latest_version ?? '-'); ?></p>
                                </div>
                                <div class="user-product-price">
                                    <div class="user-product-price-value"><?php echo e($product->formatted_price); ?></div>
                                    <div class="user-product-price-period"><?php echo e($product->renewalPeriodLabel()); ?></div>
                                </div>
                            </div>

                            <?php if($product->description): ?>
                            
                            <p class="user-product-description">
                                <?php echo e(Str::limit($product->description, 100)); ?>

                            </p>
                            <?php endif; ?>

                            <a href="<?php echo e(route('public.products.show', $product->slug)); ?>" class="user-product-button">
                                <i class="fas fa-eye"></i>
                                <?php echo e(trans('app.View Details')); ?>

                            </a>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="user-products-actions">
                        <a href="<?php echo e(route('public.products.index')); ?>" class="user-action-button">
                            <i class="fas fa-list"></i>
                            <?php echo e(trans('app.View All Products')); ?>

                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\welcome.blade.php ENDPATH**/ ?>