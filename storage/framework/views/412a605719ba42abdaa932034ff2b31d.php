<?php $__env->startSection('title', trans('app.Dashboard')); ?>
<?php $__env->startSection('page-title', trans('app.Welcome Back')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Manage your licenses and products')); ?>

<?php $__env->startSection('seo_title', $seoTitle ?? $siteSeoTitle ?? trans('app.Dashboard')); ?>
<?php $__env->startSection('meta_description', $seoDescription ?? $siteSeoDescription ?? trans('app.Manage your licenses, track downloads, and access support from your personal dashboard')); ?>


<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-tachometer-alt"></i>
                <?php echo e(trans('app.Welcome Back')); ?>, <?php echo e(auth()->user()->name); ?>!
            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Manage your licenses, track downloads, and access support from your personal dashboard')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Stats Cards -->
            <div class="user-stats-grid">
                <!-- Active Licenses -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Active Licenses')); ?></div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">
                        <?php echo e($activeCount ?? auth()->user()->licenses()->where('status','active')->count()); ?>

                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Currently active')); ?></p>
                </div>

                <!-- Total Products -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Products')); ?></div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(\App\Models\Product::count()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Available products')); ?></p>
                </div>

                <!-- Open Tickets -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Open Tickets')); ?></div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(auth()->user()->tickets()->where('status','open')->count()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Awaiting response')); ?></p>
                </div>

                <!-- Total Downloads -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Downloads')); ?></div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-download"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(auth()->user()->licenseLogs()->count()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.License downloads')); ?></p>
                </div>

                <!-- Total Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Invoices')); ?></div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($invoiceTotal ?? auth()->user()->invoices()->count()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.All invoices')); ?></p>
                </div>

                <!-- Paid Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Paid Invoices')); ?></div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($invoicePaid ?? auth()->user()->invoices()->where('status','paid')->count()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Completed payments')); ?></p>
                </div>

                <!-- Pending Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Pending Invoices')); ?></div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($invoicePending ?? auth()->user()->invoices()->where('status', 'pending')->count()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Awaiting payment')); ?></p>
                </div>

                <!-- Cancelled Invoices -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Cancelled Invoices')); ?></div>
                        <div class="user-stat-icon red">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($invoiceCancelled ?? auth()->user()->invoices()->where('status','cancelled')->count()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Cancellations')); ?></p>
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
                    <a href="<?php echo e(route('user.tickets.index')); ?>" class="user-action-button">
                        <i class="fas fa-ticket-alt"></i>
                        <?php echo e(trans('app.View Tickets')); ?>

                    </a>
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
                    <a href="<?php echo e(route('user.invoices.index')); ?>" class="user-action-button">
                        <i class="fas fa-eye"></i>
                        <?php echo e(trans('app.View Invoices')); ?>

                    </a>
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

            <!-- My Licenses Section -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-key"></i>
                        <?php echo e(trans('app.My Licenses')); ?>

                    </div>
                    <p class="user-card-subtitle"><?php echo e(trans('app.Manage your purchased licenses')); ?></p>
                </div>
                <div class="user-card-content">
                    <?php if($licenses->isEmpty()): ?>
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            <?php echo e(trans('app.No licenses found')); ?>

                        </h3>
                        <p class="user-empty-state-description">
                            <?php echo e(trans('app.You haven\'t purchased any licenses yet')); ?>

                        </p>
                    </div>
                    <?php else: ?>
                    <div class="table-container">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th><?php echo e(trans('app.Product')); ?></th>
                                    <th><?php echo e(trans('app.License Type')); ?></th>
                                    <th><?php echo e(trans('app.Status')); ?></th>
                                    <th><?php echo e(trans('app.Support')); ?></th>
                                    <th><?php echo e(trans('app.Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $licenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $license): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center">
                                            <div class="license-icon">
                                                <i class="fas fa-key"></i>
                                            </div>
                                            <div>
                                                <div class="license-name"><?php echo e($license->product?->name ?? 'N/A'); ?></div>
                                                <div class="license-version">v<?php echo e($license->product?->version ?? '-'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="license-type-badge">
                                            <?php echo e(ucfirst($license->license_type ?? '-')); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="license-status-badge license-status-<?php echo e($license->status); ?>">
                                            <?php echo e(ucfirst($license->status)); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e(optional($license->support_expires_at)->format('M d, Y') ?? '-'); ?></td>
                                    <td>
                                        <?php if($license->product): ?>
                                        <a href="<?php echo e(route('public.products.show', $license->product->slug)); ?>" class="license-action-link">
                                            <i class="fas fa-eye"></i>
                                            <?php echo e(trans('app.View Details')); ?>

                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?php echo e(trans('app.N/A')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="license-pagination">
                        <?php echo e($licenses->links()); ?>

                    </div>
                    <div class="license-actions">
                        <a href="<?php echo e(route('user.licenses.index')); ?>" class="user-action-button">
                            <i class="fas fa-list"></i>
                            <?php echo e(trans('app.View All Licenses')); ?>

                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- My Invoices Section -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-file-invoice"></i>
                        <?php echo e(trans('app.My Invoices')); ?>

                    </div>
                    <p class="user-card-subtitle"><?php echo e(trans('app.Manage your invoices and payments')); ?></p>
                </div>
                <div class="user-card-content">
                    <?php if($recentInvoices->isEmpty()): ?>
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            <?php echo e(trans('app.No invoices found')); ?>

                        </h3>
                        <p class="user-empty-state-description">
                            <?php echo e(trans('app.You don\'t have any invoices yet')); ?>

                        </p>
                    </div>
                    <?php else: ?>
                    <div class="table-container">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th><?php echo e(trans('app.Invoice')); ?></th>
                                    <th><?php echo e(trans('app.Product')); ?></th>
                                    <th><?php echo e(trans('app.Amount')); ?></th>
                                    <th><?php echo e(trans('app.Status')); ?></th>
                                    <th><?php echo e(trans('app.Due Date')); ?></th>
                                    <th><?php echo e(trans('app.Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $recentInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="invoice-number"><?php echo e($invoice->invoice_number); ?></div>
                                        <div class="invoice-date"><?php echo e($invoice->created_at->format('M d, Y')); ?></div>
                                    </td>
                                    <td>
                                        <div class="invoice-product"><?php echo e($invoice->license->product->name ?? 'N/A'); ?></div>
                                        <div class="invoice-type"><?php echo e($invoice->license->license_type ?? 'N/A'); ?></div>
                                    </td>
                                    <td class="invoice-amount">$<?php echo e(number_format($invoice->amount, 2)); ?></td>
                                    <td>
                                        <span class="invoice-status-badge invoice-status-<?php echo e($invoice->status); ?>">
                                            <?php echo e(ucfirst($invoice->status)); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A'); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('user.invoices.show', $invoice)); ?>" class="invoice-action-link">
                                            <i class="fas fa-eye"></i>
                                            <?php echo e(trans('app.View')); ?>

                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="invoice-actions">
                        <a href="<?php echo e(route('user.invoices.index')); ?>" class="user-action-button">
                            <i class="fas fa-list"></i>
                            <?php echo e(trans('app.View All Invoices')); ?>

                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Available Products Section -->
            <div class="user-products-section">
                <div class="user-products-header">
                    <div class="user-products-title">
                        <i class="fas fa-box"></i>
                        <?php echo e(trans('app.Available Products')); ?>

                    </div>
                    <a href="<?php echo e(route('public.products.index')); ?>" class="user-products-button">
                        <i class="fas fa-eye"></i>
                        <?php echo e(trans('app.View All Products')); ?>

                    </a>
                </div>
                <div class="user-products-grid">
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\user\dashboard.blade.php ENDPATH**/ ?>