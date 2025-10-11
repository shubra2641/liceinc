<?php $__env->startSection('title', trans('app.Maintenance')); ?>
<?php $__env->startSection('page-title', trans('app.Maintenance System')); ?>
<?php $__env->startSection('page-subtitle', trans('app.We are under maintenance')); ?>

<?php $__env->startSection('content'); ?>
<!-- Maintenance Page -->
<div class="maintenance-page">
    <!-- Background Animation -->
    <div class="maintenance-background">
        <div class="maintenance-animation">
            <div class="maintenance-gear maintenance-gear-1">
                <i class="fas fa-cog"></i>
            </div>
            <div class="maintenance-gear maintenance-gear-2">
                <i class="fas fa-cog"></i>
            </div>
            <div class="maintenance-gear maintenance-gear-3">
                <i class="fas fa-cog"></i>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-8 col-md-10">
                <div class="maintenance-card">
                    <!-- Header -->
                    <div class="maintenance-header">
                        <div class="maintenance-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h1 class="maintenance-title"><?php echo e(trans('app.We are under maintenance')); ?></h1>
                        <p class="maintenance-subtitle"><?php echo e(trans('app.We are performing scheduled maintenance. Please check back later.')); ?></p>
                    </div>

                    <!-- Progress Section -->
                    <div class="maintenance-progress">
                        <div class="progress-info">
                            <span class="progress-label"><?php echo e(trans('app.Maintenance Progress')); ?></span>
                            <span class="progress-percentage">75%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill w-3/4"></div>
                        </div>
                        <p class="progress-text"><?php echo e(trans('app.Estimated completion time: 2 hours')); ?></p>
                    </div>

                    <!-- Features Section -->
                    <div class="maintenance-features">
                        <h3 class="features-title"><?php echo e(trans('app.What we are working on')); ?></h3>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-server"></i>
                                    </div>
                                    <h4 class="feature-title"><?php echo e(trans('app.Server Updates')); ?></h4>
                                    <p class="feature-description"><?php echo e(trans('app.Updating server infrastructure for better performance')); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <h4 class="feature-title"><?php echo e(trans('app.Security Enhancements')); ?></h4>
                                    <p class="feature-description"><?php echo e(trans('app.Implementing latest security measures')); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-rocket"></i>
                                    </div>
                                    <h4 class="feature-title"><?php echo e(trans('app.Performance Optimization')); ?></h4>
                                    <p class="feature-description"><?php echo e(trans('app.Optimizing system performance and speed')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Updates -->
                    <div class="maintenance-status">
                        <h3 class="status-title"><?php echo e(trans('app.Latest Updates')); ?></h3>
                        <div class="status-timeline">
                            <div class="status-item status-completed">
                                <div class="status-dot"></div>
                                <div class="status-content">
                                    <h4 class="status-item-title"><?php echo e(trans('app.Database Migration')); ?></h4>
                                    <p class="status-item-text"><?php echo e(trans('app.Successfully migrated database to new version')); ?></p>
                                    <span class="status-time"><?php echo e(trans('app.2 hours ago')); ?></span>
                                </div>
                            </div>
                            <div class="status-item status-completed">
                                <div class="status-dot"></div>
                                <div class="status-content">
                                    <h4 class="status-item-title"><?php echo e(trans('app.Cache Optimization')); ?></h4>
                                    <p class="status-item-text"><?php echo e(trans('app.Optimized caching system for better performance')); ?></p>
                                    <span class="status-time"><?php echo e(trans('app.1 hour ago')); ?></span>
                                </div>
                            </div>
                            <div class="status-item status-current">
                                <div class="status-dot"></div>
                                <div class="status-content">
                                    <h4 class="status-item-title"><?php echo e(trans('app.Security Updates')); ?></h4>
                                    <p class="status-item-text"><?php echo e(trans('app.Applying latest security patches and updates')); ?></p>
                                    <span class="status-time"><?php echo e(trans('app.In progress')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Section -->
                    <div class="maintenance-contact">
                        <h3 class="contact-title"><?php echo e(trans('app.Need immediate assistance?')); ?></h3>
                        <p class="contact-text"><?php echo e(trans('app.If you are the administrator, you can disable maintenance mode from the admin settings.')); ?></p>
                        <div class="contact-actions">
                            <a href="<?php echo e(route('admin.dashboard')); ?>" class="maintenance-btn maintenance-btn-primary">
                                <i class="fas fa-cog me-2"></i>
                                <?php echo e(trans('app.Admin Panel')); ?>

                            </a>
                            <button type="button" class="maintenance-btn maintenance-btn-secondary" data-action="reload">
                                <i class="fas fa-sync-alt me-2"></i>
                                <?php echo e(trans('app.Refresh Page')); ?>

                            </button>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="maintenance-footer">
                        <p class="footer-text">
                            <i class="fas fa-clock me-2"></i>
                            <?php echo e(trans('app.Last updated')); ?>: <?php echo e(now()->format('M d, Y H:i')); ?>

                        </p>
                        <p class="footer-text">
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo e(trans('app.For support')); ?>: <a href="mailto:support@example.com">support@example.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\maintenance.blade.php ENDPATH**/ ?>