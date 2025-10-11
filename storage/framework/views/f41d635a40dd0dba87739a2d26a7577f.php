<?php $__env->startSection('title', trans('app.Reset Password')); ?>
<?php $__env->startSection('page-title', trans('app.Forgot Password?')); ?>
<?php $__env->startSection('page-subtitle', trans('app.No worries, we\'ll send you reset instructions')); ?>
<?php $__env->startSection('app.Description', trans('app.Enter your email address and we\'ll send you a link to reset your password')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-key"></i>
                <?php echo e(trans('app.Forgot Password?')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.No worries, we\'ll send you reset instructions to your email')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <div class="register-grid">
                <!-- Main Reset Form -->
                <div class="register-form-section">
                    <!-- Session Status -->
                    <?php if(session('status')): ?>
                        <div class="forgot-password-status-message">
                            <i class="fas fa-check-circle"></i>
                            <?php echo e(session('status')); ?>

                        </div>
                    <?php endif; ?>

                    <!-- Reset Information -->
                    <div class="reset-info-card">
                        <div class="reset-info-header">
                            <div class="reset-info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3 class="reset-info-title"><?php echo e(trans('app.Password Reset')); ?></h3>
                        </div>
                        <div class="reset-info-content">
                            <p class="reset-info-description">
                                <?php echo e(trans('app.Enter your email address below and we\'ll send you a secure link to reset your password. The link will expire in 60 minutes for security reasons.')); ?>

                            </p>
                        </div>
                    </div>

                    <!-- Reset Form -->
                    <form method="POST" action="<?php echo e(route('password.email')); ?>" class="register-form" novalidate>
                        <?php echo csrf_field(); ?>

                        <div class="form-fields-grid">
                            <!-- Email Address -->
                            <div class="form-field-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo e(trans('app.Email Address')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="email" name="email" type="email"
                                        class="form-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        value="<?php echo e(old('email')); ?>" required autofocus autocomplete="email"
                                        placeholder="<?php echo e(trans('app.Enter your email address')); ?>" />
                                </div>
                                <div class="form-help-text">
                                    <?php echo e(trans('app.We\'ll send reset instructions to this email address')); ?>

                                </div>
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo e($message); ?>

                                </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="form-submit-button">
                            <span class="button-text"><?php echo e(trans('app.Send Reset Link')); ?></span>
                            <i class="fas fa-spinner fa-spin button-loading hidden"></i>
                        </button>
                    </form>

                    <!-- Back to Login -->
                    <div class="form-signin-link">
                        <p class="signin-text">
                            <?php echo e(trans('app.Remember your password?')); ?>

                            <a href="<?php echo e(route('login')); ?>" class="signin-link">
                                <i class="fas fa-arrow-left"></i>
                                <?php echo e(trans('app.Back to Sign In')); ?>

                            </a>
                        </p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="register-sidebar">
                    <!-- Reset Process -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-shield-alt"></i>
                                <?php echo e(trans('app.Reset Process')); ?>

                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="reset-process-list">
                                <div class="reset-process-item">
                                    <div class="reset-process-number">1</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title"><?php echo e(trans('app.Enter Email')); ?></h4>
                                        <p class="reset-process-description"><?php echo e(trans('app.Provide your registered email address')); ?></p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">2</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title"><?php echo e(trans('app.Check Email')); ?></h4>
                                        <p class="reset-process-description"><?php echo e(trans('app.We\'ll send you a secure reset link')); ?></p>
                                    </div>
                                </div>
                                <div class="reset-process-item">
                                    <div class="reset-process-number">3</div>
                                    <div class="reset-process-content">
                                        <h4 class="reset-process-title"><?php echo e(trans('app.Create Password')); ?></h4>
                                        <p class="reset-process-description"><?php echo e(trans('app.Set a new secure password')); ?></p>
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
                                    <?php echo e(trans('app.Can\'t find the reset email?')); ?>

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

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\auth\forgot-password.blade.php ENDPATH**/ ?>