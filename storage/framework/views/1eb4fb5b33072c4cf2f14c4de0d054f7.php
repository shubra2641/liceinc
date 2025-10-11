<?php $__env->startSection('title', trans('app.Sign In')); ?>
<?php $__env->startSection('page-title', trans('app.Welcome Back')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Sign in to your account to continue')); ?>
<?php $__env->startSection('app.Description', trans('app.Secure sign in to your account with email and password or Envato OAuth')); ?>

<?php $__env->startSection('seo_title', $siteSeoTitle ?? trans('app.Sign In')); ?>
<?php $__env->startSection('meta_description', $siteSeoDescription ?? trans('app.Secure sign in to your account with email and password or
Envato OAuth')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-sign-in-alt"></i>
                <?php echo e(trans('app.Welcome Back')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Sign in to your account to continue with our premium services')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <?php if($fromInstall ?? false): ?>
            <div class="installation-success-message">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="success-content">
                    <h3><?php echo e(trans('install.installation_completed')); ?>!</h3>
                    <p><?php echo e(trans('install.installation_success_message')); ?></p>
                    <div class="success-details">
                        <p><i class="fas fa-database"></i> <?php echo e(trans('install.database_created')); ?></p>
                        <p><i class="fas fa-users"></i> <?php echo e(trans('install.admin_account_created')); ?></p>
                        <p><i class="fas fa-cog"></i> <?php echo e(trans('install.system_configured')); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="register-grid">
                <!-- Main Login Form -->
                <div class="register-form-section">
                    <!-- Envato OAuth Login -->
                    <?php if(\App\Helpers\EnvatoHelper::isConfigured()): ?>
                    <div class="envato-auth-section">
                        <a href="<?php echo e(route('auth.envato')); ?>" class="envato-auth-button">
                            <i class="fas fa-external-link-alt"></i>
                            <?php echo e(trans('app.Continue with Envato')); ?>

                        </a>

                        <div class="auth-divider">
                            <div class="auth-divider-line"></div>
                            <span class="auth-divider-text"><?php echo e(trans('app.Or continue with email')); ?></span>
                            <div class="auth-divider-line"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="POST" action="<?php echo e(route('login')); ?>" class="register-form" novalidate>
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
                                        value="<?php echo e(old('email')); ?>" required autofocus autocomplete="username"
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
                                    <?php echo e(trans('app.Password')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="login-password" name="password" type="password"
                                        class="form-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required
                                        autocomplete="current-password"
                                        placeholder="<?php echo e(trans('app.Enter your password')); ?>" />
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
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="form-remember-section">
                            <div class="form-checkbox-wrapper">
                                <input id="remember_me" name="remember" type="checkbox" class="form-checkbox">
                                <label for="remember_me" class="form-checkbox-label">
                                    <?php echo e(trans('app.Remember me')); ?>

                                </label>
                            </div>

                            <?php if(Route::has('password.request')): ?>
                            <a href="<?php echo e(route('password.request')); ?>" class="form-link">
                                <?php echo e(trans('app.Forgot Password?')); ?>

                            </a>
                            <?php endif; ?>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="form-submit-button">
                            <span class="button-text"><?php echo e(trans('app.Sign In')); ?></span>
                            <i class="fas fa-spinner fa-spin button-loading hidden"></i>
                        </button>
                    </form>

                    <!-- Register link -->
                    <div class="form-signin-link">
                        <p class="signin-text">
                            <?php echo e(trans("app.Don't have an account?")); ?>

                            <a href="<?php echo e(route('register')); ?>" class="signin-link">
                                <?php echo e(trans('app.Create one now')); ?>

                            </a>
                        </p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="register-sidebar">
                    <!-- Security Features -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-shield-alt"></i>
                                <?php echo e(trans('app.Security Features')); ?>

                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="benefits-list">
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title"><?php echo e(trans('app.Secure Login')); ?></h4>
                                        <p class="benefit-description"><?php echo e(trans('app.Your credentials are encrypted and
                                            secure')); ?></p>
                                    </div>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title"><?php echo e(trans('app.Two-Factor Authentication')); ?></h4>
                                        <p class="benefit-description"><?php echo e(trans('app.Additional security layer for your
                                            account')); ?></p>
                                    </div>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-desktop"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title"><?php echo e(trans('app.Session Management')); ?></h4>
                                        <p class="benefit-description"><?php echo e(trans('app.Control your active sessions and
                                            devices')); ?></p>
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
                                    <?php echo e(trans('app.Can\'t access your account?')); ?>

                                </p>
                                <a href="<?php echo e(route('password.request')); ?>" class="help-button">
                                    <i class="fas fa-key"></i>
                                    <?php echo e(trans('app.Reset Password')); ?>

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
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\auth\login.blade.php ENDPATH**/ ?>