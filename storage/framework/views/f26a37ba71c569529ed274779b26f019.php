

<?php $__env->startSection('title', trans('app.Test Email Warning')); ?>
<?php $__env->startSection('page-title', trans('app.Test Email Detected')); ?>
<?php $__env->startSection('page-subtitle', trans('app.You are using a test email address')); ?>
<?php $__env->startSection('app.Description', trans('app.Test email addresses cannot receive verification emails')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                <?php echo e(trans('app.Test Email Detected')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.You are using a test email address that cannot receive verification emails')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <div class="register-grid">
                <!-- Main Warning Content -->
                <div class="register-form-section">
                    <!-- Warning Information -->
                    <div class="reset-info-card">
                        <div class="reset-info-header">
                            <div class="reset-info-icon">
                                <i class="fas fa-envelope-slash text-warning"></i>
                            </div>
                            <h3 class="reset-info-title"><?php echo e(trans('app.Test Email Address')); ?></h3>
                        </div>
                        <div class="reset-info-content">
                            <p class="reset-info-description">
                                <?php echo e(trans('app.You are currently using a test email address')); ?> <strong>(<?php echo e($email); ?>)</strong> <?php echo e(trans('app.that cannot receive verification emails. Test email addresses like @example.com, @test.com, @localhost, and @demo.com are not real email addresses and cannot receive emails.')); ?>

                            </p>
                            
                            <div class="verification-status-message warning-message">
                                <i class="fas fa-info-circle"></i>
                                <?php echo e(trans('app.To use the system normally, please register with a real email address that can receive verification emails.')); ?>

                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="verification-actions">
                        <a href="<?php echo e(route('dashboard')); ?>" class="form-submit-button">
                            <span class="button-text"><?php echo e(trans('app.Continue to Dashboard')); ?></span>
                            <i class="fas fa-arrow-right"></i>
                        </a>

                        <form method="POST" action="<?php echo e(route('logout')); ?>" class="logout-form">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="form-logout-button">
                                <i class="fas fa-sign-out-alt"></i>
                                <?php echo e(trans('app.Log Out')); ?>

                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="register-sidebar">
                    <!-- Test Email Info -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-info-circle text-info"></i>
                                <?php echo e(trans('app.About Test Emails')); ?>

                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="reset-process-list">
                                <div class="reset-process-item">
                                    <div class="reset-process-number">1</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title"><?php echo e(trans('app.Test Email Domains')); ?></h4>
                                        <p class="reset-process-description"><?php echo e(trans('app.@example.com, @test.com, @localhost, @demo.com')); ?></p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">2</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title"><?php echo e(trans('app.Cannot Receive Emails')); ?></h4>
                                        <p class="reset-process-description"><?php echo e(trans('app.These domains are not real and cannot receive emails')); ?></p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">3</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title"><?php echo e(trans('app.Use Real Email')); ?></h4>
                                        <p class="reset-process-description"><?php echo e(trans('app.Register with Gmail, Yahoo, or other real email providers')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="user-card help-card">
                        <div class="user-card-content">
                            <div class="help-content">
                                <div class="help-icon">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                <h4 class="help-title">
                                    <?php echo e(trans('app.Need Help?')); ?>

                                </h4>
                                <p class="help-description">
                                    <?php echo e(trans('app.Want to use a real email address?')); ?>

                                </p>
                                <a href="<?php echo e(route('support.tickets.create')); ?>" class="help-button">
                                    <i class="fas fa-headset"></i>
                                    <?php echo e(trans('app.Contact Support')); ?>

                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\auth\test-email-warning.blade.php ENDPATH**/ ?>