<?php $__env->startSection('title', trans('app.My Licenses & Invoices')); ?>
<?php $__env->startSection('page-title', trans('app.My Licenses & Invoices')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Manage your licenses and invoices')); ?>

<?php $__env->startSection('seo_title', $siteSeoTitle ?? trans('app.My Licenses')); ?>
<?php $__env->startSection('meta_description', $siteSeoDescription ?? trans('app.Manage your purchased licenses')); ?>


<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-key"></i>
                <?php echo e(trans('app.My Licenses & Invoices')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Manage your licenses and invoices, track payment status')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Tabs -->
            <div class="license-tabs">
                <button class="tab-button active" data-tab="licenses">
                    <i class="fas fa-key"></i>
                    <?php echo e(trans('app.Licenses')); ?> (<?php echo e($licenses->total()); ?>)
                </button>
                <button class="tab-button" data-tab="invoices">
                    <i class="fas fa-file-invoice"></i>
                    <?php echo e(trans('app.Invoices')); ?> (<?php echo e($invoices->total()); ?>)
                </button>
            </div>

            <!-- Licenses Tab -->
            <div id="licenses-tab" class="tab-content active">
                <!-- Filters and Search -->
                <div class="license-filters">
                    <div class="filter-group">
                        <label for="status-filter"><?php echo e(trans('app.Filter by Status')); ?>:</label>
                        <select id="status-filter" class="filter-select">
                            <option value=""><?php echo e(trans('app.All Statuses')); ?></option>
                            <option value="active"><?php echo e(trans('app.Active')); ?></option>
                            <option value="expired"><?php echo e(trans('app.Expired')); ?></option>
                            <option value="suspended"><?php echo e(trans('app.Suspended')); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search-input"><?php echo e(trans('app.Search')); ?>:</label>
                        <input type="text" id="search-input" class="filter-input" placeholder="<?php echo e(trans('app.Search by product name...')); ?>">
                    </div>
                </div>

            <?php if($licenses->isEmpty()): ?>
            <div class="user-empty-state">
                <div class="user-empty-state-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h3 class="user-empty-state-title">
                    <?php echo e(trans('app.No licenses found')); ?>

                </h3>
                <p class="user-empty-state-description">
                    <?php echo e(trans('app.You haven\'t purchased any licenses yet. Browse our products to get started!')); ?>

                </p>
                <a href="<?php echo e(route('public.products.index')); ?>" class="user-action-button">
                    <i class="fas fa-shopping-cart"></i>
                    <?php echo e(trans('app.Browse Products')); ?>

                </a>
            </div>
            <?php else: ?>
            <!-- Licenses Table -->
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th><?php echo e(trans('app.Product')); ?></th>
                            <th><?php echo e(trans('app.License Key')); ?></th>
                            <th><?php echo e(trans('app.Type')); ?></th>
                            <th><?php echo e(trans('app.Status')); ?></th>
                            <th><?php echo e(trans('app.Purchase Date')); ?></th>
                            <th><?php echo e(trans('app.Support Until')); ?></th>
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
                                <div class="license-key">
                                    <code class="license-key-code"><?php echo e($license->license_key); ?></code>
                                    <button class="copy-key-btn" data-key="<?php echo e($license->license_key); ?>" title="<?php echo e(trans('app.Copy License Key')); ?>">
                                        <i class="fas fa-copy"></i>
                                    </button>
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
                            <td><?php echo e($license->created_at->format('M d, Y')); ?></td>
                            <td><?php echo e(optional($license->support_expires_at)->format('M d, Y') ?? '-'); ?></td>
                            <td>
                                <div class="license-actions-cell">
                                    <a href="<?php echo e(route('user.licenses.show', $license)); ?>" class="license-action-link">
                                        <i class="fas fa-eye"></i>
                                        <?php echo e(trans('app.View')); ?>

                                    </a>
                                    <?php if($license->product): ?>
                                    <a href="<?php echo e(route('public.products.show', $license->product->slug)); ?>" class="license-action-link">
                                        <i class="fas fa-external-link-alt"></i>
                                        <?php echo e(trans('app.Product')); ?>

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
                    <?php echo e($licenses->links()); ?>

                </div>
                <?php endif; ?>
            </div>

            <!-- Invoices Tab -->
            <div id="invoices-tab" class="tab-content">
                <?php if($invoices->isEmpty()): ?>
                <div class="user-empty-state">
                    <div class="user-empty-state-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3 class="user-empty-state-title">
                        <?php echo e(trans('app.No invoices found')); ?>

                    </h3>
                    <p class="user-empty-state-description">
                        <?php echo e(trans('app.You don\'t have any invoices yet.')); ?>

                    </p>
                </div>
                <?php else: ?>
                <!-- Invoices Table -->
                <div class="table-container">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th><?php echo e(trans('app.Invoice Number')); ?></th>
                                <th><?php echo e(trans('app.Product')); ?></th>
                                <th><?php echo e(trans('app.Amount')); ?></th>
                                <th><?php echo e(trans('app.Status')); ?></th>
                                <th><?php echo e(trans('app.Created Date')); ?></th>
                                <th><?php echo e(trans('app.Paid Date')); ?></th>
                                <th><?php echo e(trans('app.Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <div class="invoice-number">
                                        <code class="invoice-number-code"><?php echo e($invoice->invoice_number); ?></code>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center">
                                        <div class="invoice-icon">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>
                                        <div>
                                            <div class="invoice-product"><?php echo e($invoice->product?->name ?? 'N/A'); ?></div>
                                            <?php if($invoice->license): ?>
                                            <div class="invoice-license">
                                                <i class="fas fa-key"></i>
                                                <?php echo e(trans('app.License')); ?>: <?php echo e($invoice->license->license_key); ?>

                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="invoice-amount">$<?php echo e(number_format($invoice->amount, 2)); ?></span>
                                </td>
                                <td>
                                    <span class="invoice-status-badge invoice-status-<?php echo e($invoice->status); ?>">
                                        <?php echo e(ucfirst($invoice->status)); ?>

                                    </span>
                                </td>
                                <td><?php echo e($invoice->created_at->format('M d, Y')); ?></td>
                                <td><?php echo e($invoice->paid_at ? $invoice->paid_at->format('M d, Y') : '-'); ?></td>
                                <td>
                                    <div class="invoice-actions-cell">
                                        <a href="<?php echo e(route('user.invoices.show', $invoice)); ?>" class="invoice-action-link">
                                            <i class="fas fa-eye"></i>
                                            <?php echo e(trans('app.View')); ?>

                                        </a>
                                        <?php if($invoice->status === 'pending'): ?>
                                        <a href="<?php echo e(route('user.invoices.show', $invoice)); ?>" class="invoice-action-link primary">
                                            <i class="fas fa-credit-card"></i>
                                            <?php echo e(trans('app.Pay Now')); ?>

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
                <div class="invoice-pagination">
                    <?php echo e($invoices->links()); ?>

                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/user/licenses/index.blade.php ENDPATH**/ ?>