

<?php $__env->startSection('title', 'License Verification'); ?>


<?php $__env->startSection('content'); ?>
<div class="license-verification">
    <div class="install-card">
        <div class="install-card-header">
            <div class="install-card-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="install-card-title">License Verification</h1>
            <p class="install-card-subtitle">Verify your purchase to continue installation</p>
        </div>

        <div class="install-card-body">
            <!-- Product & Domain Info -->
            <div class="license-info-cards">
                <div class="license-info-card">
                    <div class="icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <h4>Product</h4>
                    <p>The Ultimate License Management System</p>
                </div>
                <div class="license-info-card">
                    <div class="icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h4>Domain</h4>
                    <p><?php echo e(request()->getHost()); ?></p>
                </div>
            </div>

            <!-- License Form -->
            <div class="license-form">
                <form method="POST" action="<?php echo e(route('install.license.store')); ?>" id="licenseForm">
                    <?php echo csrf_field(); ?>
                    <div class="license-form-group">
                        <label for="purchase_code" class="license-label">
                            <i class="fas fa-key"></i>
                            <span>Purchase Code</span>
                        </label>
                        <input type="text"
                               id="purchase_code"
                               name="purchase_code"
                               value="<?php echo e(old('purchase_code')); ?>"
                               placeholder="Enter your purchase code"
                               maxlength="100"
                               class="license-input <?php $__errorArgs = ['purchase_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               required autocomplete="off">
                        <?php $__errorArgs = ['purchase_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="license-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php $__errorArgs = ['license'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="license-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div class="license-hint">
                            <i class="fas fa-info-circle"></i>
                            Enter your purchase code or license key
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="license-security-notice">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>Security Notice:</strong> Your purchase code is sent securely to our license server for validation. This ensures you have a valid license for this domain.
                        </div>
                    </div>

                    <!-- Success Message (Hidden by default) -->
                    <div class="license-success" id="licenseSuccess">
                        <i class="fas fa-check-circle"></i>
                        <div class="license-success-text">
                            License verified successfully! You can now continue with the installation.
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="license-actions">
                        <a href="<?php echo e(route('install.welcome')); ?>" class="license-btn license-btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back</span>
                        </a>
                        
                        <button type="submit" class="license-btn license-btn-primary" id="verifyBtn">
                            <i class="fas fa-check"></i>
                            <span>Verify License</span>
                        </button>
                    </div>

                    <!-- Continue Button (Hidden by default) -->
                    <a href="<?php echo e(route('install.requirements')); ?>" class="license-btn license-continue-btn" id="continueBtn">
                        <i class="fas fa-arrow-right"></i>
                        <span>Continue Installation</span>
                    </a>
                </form>
            </div>

            <!-- Help Section -->
            <div class="license-help">
                <h4>
                    <i class="fas fa-question-circle"></i>
                    Need Help?
                </h4>
                <div class="license-help-grid">
                    <div class="license-help-item">
                        <h5>Where to find your purchase code?</h5>
                        <p>Check your email confirmation, account dashboard, or the platform where you purchased the license. The code format may vary.</p>
                    </div>
                    <div class="license-help-item">
                        <h5>Purchase code not working?</h5>
                        <p>Make sure you're using the correct purchase code, ensure your license is still valid, or contact our support team.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('install.layout', ['step' => 2], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\install\license.blade.php ENDPATH**/ ?>