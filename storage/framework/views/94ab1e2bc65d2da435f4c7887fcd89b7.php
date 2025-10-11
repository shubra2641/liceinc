<?php $__env->startSection('title', trans('app.Reset Password')); ?>
<?php $__env->startSection('page-title', trans('app.Reset Your Password')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Enter your new password below')); ?>
<?php $__env->startSection('app.Description', trans('app.Create a new secure password for your account')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-lock"></i>
                <?php echo e(trans('app.Reset Your Password')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Enter your new secure password below to complete the reset process')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <div class="register-grid">
                <!-- Main Reset Form -->
                <div class="register-form-section">
                    <!-- Reset Information -->
                    <div class="reset-info-card">
                        <div class="reset-info-header">
                            <div class="reset-info-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="reset-info-title"><?php echo e(trans('app.Secure Password Reset')); ?></h3>
                        </div>
                        <div class="reset-info-content">
                            <p class="reset-info-description">
                                <?php echo e(trans('app.Create a strong password that includes uppercase letters, lowercase letters, numbers, and special characters.')); ?>

                            </p>
                        </div>
                    </div>

                    <!-- Reset Form -->
                    <form method="POST" action="<?php echo e(route('password.store')); ?>" class="register-form" novalidate>
                        <?php echo csrf_field(); ?>

                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="<?php echo e($request->route('token')); ?>">

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
                                        value="<?php echo e(old('email', $request->email)); ?>" required autofocus autocomplete="username"
                                        placeholder="<?php echo e(trans('app.Enter your email address')); ?>" />
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

                            <!-- Password -->
                            <div class="form-field-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    <?php echo e(trans('app.New Password')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="reset-password" name="password" type="password"
                                        class="form-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        required autocomplete="new-password"
                                        placeholder="<?php echo e(trans('app.Enter your new password')); ?>" />
                                    <button type="button" id="toggle-password" class="form-input-toggle">
                                        <i class="fas fa-eye" id="password-show"></i>
                                        <i class="fas fa-eye-slash hidden" id="password-hide"></i>
                                    </button>
                                </div>
                                <?php $__errorArgs = ['password'];
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

                            <!-- Confirm Password -->
                            <div class="form-field-group">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    <?php echo e(trans('app.Confirm New Password')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="password_confirmation" name="password_confirmation" type="password"
                                        class="form-input <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        required autocomplete="new-password"
                                        placeholder="<?php echo e(trans('app.Confirm your new password')); ?>" />
                                </div>
                                <?php $__errorArgs = ['password_confirmation'];
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
                            <span class="button-text"><?php echo e(trans('app.Reset Password')); ?></span>
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
                    <!-- Password Requirements -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-shield-alt"></i>
                                <?php echo e(trans('app.Password Requirements')); ?>

                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="password-requirements-list">
                                <div class="password-requirement-item">
                                    <div class="password-requirement-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="password-requirement-content">
                                        <h4 class="password-requirement-title"><?php echo e(trans('app.At least 8 characters long')); ?></h4>
                                    </div>
                                </div>
                                <div class="password-requirement-item">
                                    <div class="password-requirement-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="password-requirement-content">
                                        <h4 class="password-requirement-title"><?php echo e(trans('app.Uppercase and lowercase letters')); ?></h4>
                                    </div>
                                </div>
                                <div class="password-requirement-item">
                                    <div class="password-requirement-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="password-requirement-content">
                                        <h4 class="password-requirement-title"><?php echo e(trans('app.Numbers and special characters')); ?></h4>
                                    </div>
                                </div>
                                <div class="password-requirement-item">
                                    <div class="password-requirement-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="password-requirement-content">
                                        <h4 class="password-requirement-title"><?php echo e(trans('app.Not easily guessable')); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Security Tips -->
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800 p-6">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle w-8 h-8 text-amber-600 dark:text-amber-400 mx-auto mb-3"></i>
                        <h4 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                            <?php echo e(trans('app.Security Tips')); ?>

                        </h4>
                        <p class="text-slate-600 dark:text-slate-300 text-sm mb-4">
                            <?php echo e(trans('app.Use a unique password for this account')); ?>

                        </p>
                        <ul class="text-left text-sm text-slate-600 dark:text-slate-400 space-y-1">
                            <li>• <?php echo e(trans('app.Don\'t reuse passwords')); ?></li>
                            <li>• <?php echo e(trans('app.Avoid personal information')); ?></li>
                            <li>• <?php echo e(trans('app.Consider using a password manager')); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('footer-links'); ?>
    <div class="text-center">
        <a href="<?php echo e(route('login')); ?>" class="text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition-colors">
            <?php echo e(trans('app.Back to Sign In')); ?>

        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\auth\reset-password.blade.php ENDPATH**/ ?>