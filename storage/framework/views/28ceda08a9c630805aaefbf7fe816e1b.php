

<?php $__env->startSection('title', trans('app.License Details')); ?>
<?php $__env->startSection('page-title', trans('app.License Details')); ?>
<?php $__env->startSection('page-subtitle', trans('app.View license information and manage domains')); ?>

<?php $__env->startSection('seo_title', $siteSeoTitle ?? trans('app.License Details')); ?>
<?php $__env->startSection('meta_description', $siteSeoDescription ?? trans('app.View license information and manage domains')); ?>


<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-key"></i>
                <?php echo e(trans('app.License Details')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Manage your license and registered domains')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- License Information -->
            <div class="license-details-grid">
                <div class="license-info-card">
                    <div class="license-info-header">
                        <h3><?php echo e(trans('app.License Information')); ?></h3>
                        <span class="license-status-badge license-status-<?php echo e($license->status); ?>">
                            <?php echo e(ucfirst($license->status)); ?>

                        </span>
                    </div>
                    
                    <div class="license-info-content">
                        <div class="info-row">
                            <label><?php echo e(trans('app.Product')); ?>:</label>
                            <span><?php echo e($license->product?->name ?? 'N/A'); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.License Key')); ?>:</label>
                            <div class="license-key-display">
                                <code class="license-key-code"><?php echo e($license->license_key); ?></code>
                                <button class="copy-key-btn" data-key="<?php echo e($license->license_key); ?>" title="<?php echo e(trans('app.Copy License Key')); ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.License Type')); ?>:</label>
                            <span class="license-type-badge"><?php echo e(ucfirst($license->license_type ?? '-')); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Purchase Date')); ?>:</label>
                            <span><?php echo e($license->created_at->format('M d, Y')); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Support Until')); ?>:</label>
                            <span><?php echo e(optional($license->support_expires_at)->format('M d, Y') ?? '-'); ?></span>
                        </div>
                        
                        <?php if($license->license_expires_at): ?>
                        <div class="info-row">
                            <label><?php echo e(trans('app.Expires On')); ?>:</label>
                            <span><?php echo e($license->license_expires_at->format('M d, Y')); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Information -->
                <?php if($license->product): ?>
                <div class="license-info-card">
                    <div class="license-info-header">
                        <h3><?php echo e(trans('app.Product Information')); ?></h3>
                    </div>
                    
                    <div class="license-info-content">
                        <div class="info-row">
                            <label><?php echo e(trans('app.Name')); ?>:</label>
                            <span><?php echo e($license->product->name); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Version')); ?>:</label>
                            <span>v<?php echo e($license->product->version ?? '-'); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Category')); ?>:</label>
                            <span><?php echo e($license->product->category?->name ?? '-'); ?></span>
                        </div>
                        
                        <?php if($license->product->description): ?>
                        <div class="info-row">
                            <label><?php echo e(trans('app.Description')); ?>:</label>
                            <span><?php echo e($license->product->description); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Actions')); ?>:</label>
                            <a href="<?php echo e(route('public.products.show', $license->product->slug)); ?>" class="license-action-link">
                                <i class="fas fa-external-link-alt"></i>
                                <?php echo e(trans('app.View Product')); ?>

                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Registered Domains -->
            <div class="license-domains-section">
                <div class="section-header">
                    <h3><?php echo e(trans('app.Registered Domains')); ?></h3>
                    <span class="domain-count"><?php echo e($license->domains->count()); ?> <?php echo e(trans('app.domains registered')); ?></span>
                </div>

                <?php if($license->domains->isEmpty()): ?>
                <div class="user-empty-state">
                    <div class="user-empty-state-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="user-empty-state-title">
                        <?php echo e(trans('app.No domains registered')); ?>

                    </h3>
                    <p class="user-empty-state-description">
                        <?php echo e(trans('app.This license has no registered domains yet')); ?>

                    </p>
                </div>
                <?php else: ?>
                <div class="domains-table-container">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th><?php echo e(trans('app.Domain')); ?></th>
                                <th><?php echo e(trans('app.Registered Date')); ?></th>
                                <th><?php echo e(trans('app.Status')); ?></th>
                                <th><?php echo e(trans('app.Last Check')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $license->domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <div class="domain-info">
                                        <i class="fas fa-globe"></i>
                                        <span><?php echo e($domain->domain); ?></span>
                                    </div>
                                </td>
                                <td><?php echo e($domain->created_at->format('M d, Y')); ?></td>
                                <td>
                                    <span class="domain-status-badge domain-status-<?php echo e($domain->status); ?>">
                                        <?php echo e(ucfirst($domain->status)); ?>

                                    </span>
                                </td>
                                <td><?php echo e(optional($domain->last_checked_at)->format('M d, Y H:i') ?? '-'); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- License Actions -->
            <div class="license-actions-section">
                <div class="action-buttons">
                    <a href="<?php echo e(route('user.licenses.index')); ?>" class="user-action-button">
                        <i class="fas fa-arrow-left"></i>
                        <?php echo e(trans('app.Back to Licenses')); ?>

                    </a>
                    
                    <?php if($license->product): ?>
                    <a href="<?php echo e(route('public.products.show', $license->product->slug)); ?>" class="user-action-button">
                        <i class="fas fa-download"></i>
                        <?php echo e(trans('app.Download Product')); ?>

                    </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo e(route('user.tickets.create')); ?>" class="user-action-button">
                        <i class="fas fa-headset"></i>
                        <?php echo e(trans('app.Get Support')); ?>

                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\user\licenses\show.blade.php ENDPATH**/ ?>