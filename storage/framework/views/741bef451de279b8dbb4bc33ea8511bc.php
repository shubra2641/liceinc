<?php $__env->startSection('title', trans('app.My Invoices')); ?>
<?php $__env->startSection('page-title', trans('app.My Invoices')); ?>
<?php $__env->startSection('page-subtitle', trans('app.View and manage your invoices')); ?>

<?php $__env->startSection('seo_title', $siteSeoTitle ?? trans('app.My Invoices')); ?>
<?php $__env->startSection('meta_description', $siteSeoDescription ?? trans('app.View and manage your invoices')); ?>


<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-file-invoice"></i>
                <?php echo e(trans('app.My Invoices')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.View and manage your invoices and payments')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Invoice Statistics -->
            <div class="invoice-stats-grid">
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Invoices')); ?></div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($invoices->total()); ?></div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Paid Invoices')); ?></div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($invoices->where('status', 'paid')->count()); ?></div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Pending Invoices')); ?></div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($invoices->where('status', 'pending')->count()); ?></div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Amount')); ?></div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">$<?php echo e(number_format($invoices->sum('amount'), 2)); ?></div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="license-filters">
                <div class="filter-group">
                    <label for="status-filter"><?php echo e(trans('app.Filter by Status')); ?>:</label>
                    <select id="status-filter" class="filter-select">
                        <option value=""><?php echo e(trans('app.All Statuses')); ?></option>
                        <option value="paid"><?php echo e(trans('app.Paid')); ?></option>
                        <option value="pending"><?php echo e(trans('app.Pending')); ?></option>
                        <option value="cancelled"><?php echo e(trans('app.Cancelled')); ?></option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search-input"><?php echo e(trans('app.Search')); ?>:</label>
                    <input type="text" id="search-input" class="filter-input" placeholder="<?php echo e(trans('app.Search by invoice number...')); ?>">
                </div>
            </div>

            <?php if($invoices->isEmpty()): ?>
            <div class="user-empty-state">
                <div class="user-empty-state-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3 class="user-empty-state-title">
                    <?php echo e(trans('app.No invoices found')); ?>

                </h3>
                <p class="user-empty-state-description">
                    <?php echo e(trans('app.You don\'t have any invoices yet. Purchase a product to get started!')); ?>

                </p>
                <a href="<?php echo e(route('public.products.index')); ?>" class="user-action-button">
                    <i class="fas fa-shopping-cart"></i>
                    <?php echo e(trans('app.Browse Products')); ?>

                </a>
            </div>
            <?php else: ?>
            <!-- Invoices Table -->
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th><?php echo e(trans('app.Invoice')); ?></th>
                            <th><?php echo e(trans('app.Product')); ?></th>
                            <th><?php echo e(trans('app.Amount')); ?></th>
                            <th><?php echo e(trans('app.Status')); ?></th>
                            <th><?php echo e(trans('app.Due Date')); ?></th>
                            <th><?php echo e(trans('app.Created')); ?></th>
                            <th><?php echo e(trans('app.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                            <td><?php echo e($invoice->due_date ? $invoice->due_date->format('M d, Y') : '-'); ?></td>
                            <td><?php echo e($invoice->created_at->format('M d, Y')); ?></td>
                            <td>
                                <div class="license-actions-cell">
                                    <a href="<?php echo e(route('user.invoices.show', $invoice)); ?>" class="license-action-link">
                                        <i class="fas fa-eye"></i>
                                        <?php echo e(trans('app.View')); ?>

                                    </a>
                                    <?php if($invoice->status === 'pending'): ?>
                                    <a href="<?php echo e(route('user.invoices.show', $invoice)); ?>#payment" class="license-action-link">
                                        <i class="fas fa-credit-card"></i>
                                        <?php echo e(trans('app.Pay')); ?>

                                    </a>
                                    <?php endif; ?>
                                    <?php if($invoice->status === 'paid'): ?>
                                    <a href="<?php echo e(route('user.invoices.show', $invoice)); ?>#download" class="license-action-link">
                                        <i class="fas fa-download"></i>
                                        <?php echo e(trans('app.Download')); ?>

                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="license-pagination">
                <?php echo e($invoices->links()); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\user\invoices\index.blade.php ENDPATH**/ ?>