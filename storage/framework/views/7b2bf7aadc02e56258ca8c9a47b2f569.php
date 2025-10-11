

<?php $__env->startSection('title', trans('app.Verify Email')); ?>
<?php $__env->startSection('page-title', trans('app.Verify your email')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Check your inbox for the verification link')); ?>
<?php $__env->startSection('app.Description', trans('app.Email verification ensures account security')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-envelope-open"></i>
                <?php echo e(trans('app.Verify your email')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Check your inbox for the verification link to complete your account setup')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <div class="register-grid">
                <!-- Main Verification Content -->
                <div class="register-form-section">
                    <!-- Verification Information -->
                    <div class="reset-info-card">
                        <div class="reset-info-header">
                            <div class="reset-info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3 class="reset-info-title"><?php echo e(trans('app.Email Verification Required')); ?></h3>
                        </div>
                        <div class="reset-info-content">
                            <p class="reset-info-description">
                                <?php echo e(trans("app.Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.")); ?>

                            </p>
                            
                            <?php if(session('status') == 'verification-link-sent'): ?>
                                <div class="verification-status-message">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo e(trans('app.A new verification link has been sent to the email address you provided during registration.')); ?>

                                </div>
                            <?php elseif(session('status')): ?>
                                <div class="verification-status-message">
                                    <i class="fas fa-info-circle"></i>
                                    <?php echo e(session('status')); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="verification-actions">
                        <form method="POST" action="<?php echo e(route('verification.send')); ?>" class="verification-form">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="form-submit-button">
                                <span class="button-text"><?php echo e(trans('app.Resend Verification Email')); ?></span>
                                <i class="fas fa-spinner fa-spin button-loading hidden"></i>
                            </button>
                        </form>

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
                    <!-- Verification Process -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-shield-alt"></i>
                                <?php echo e(trans('app.Verification Process')); ?>

                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="reset-process-list">
                                <div class="reset-process-item">
                                    <div class="reset-process-number">1</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title"><?php echo e(trans('app.Check Your Email')); ?></h4>
                                        <p class="reset-process-description"><?php echo e(trans('app.Look for our verification email')); ?></p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">2</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title"><?php echo e(trans('app.Click the Link')); ?></h4>
                                        <p class="reset-process-description"><?php echo e(trans('app.Click the verification link in the email')); ?></p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">3</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title"><?php echo e(trans('app.Complete Setup')); ?></h4>
                                        <p class="reset-process-description"><?php echo e(trans('app.Your account will be fully activated')); ?></p>
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
                                    <?php echo e(trans('app.Can\'t find the verification email?')); ?>

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

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\auth\verify-email.blade.php ENDPATH**/ ?>