<?php $__env->startSection('page-title', trans('app.Payment Settings')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Configure payment gateways and settings')); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="admin-dashboard-container">
    <!-- Header Section -->
    <div class="admin-card">
        <div class="admin-section-content">
            <div class="admin-card-title">
                <i class="fas fa-credit-card"></i>
                <?php echo e(trans('app.Payment Settings')); ?>

            </div>
            <p class="admin-card-subtitle">
                <?php echo e(trans('app.Configure and manage payment gateways for your store')); ?>

            </p>
        </div>

        <div class="admin-card-content">
            <!-- Success/Error Messages -->
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Payment Gateway Overview -->
            <div class="admin-info-section">
                <div class="admin-info-card">
                    <div class="admin-info-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="admin-info-content">
                        <h4><?php echo e(trans('app.Payment Gateway Configuration')); ?></h4>
                        <p><?php echo e(trans('app.Configure your payment gateways to accept payments from customers. Enable sandbox mode for testing.')); ?></p>
                    </div>
                </div>
            </div>

            <!-- PayPal Settings -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <div class="admin-card-title">
                        <i class="fab fa-paypal"></i>
                        <?php echo e(trans('app.PayPal')); ?>

                    </div>
                    <p class="admin-card-subtitle">
                        <?php echo e(trans('app.Accept payments via PayPal')); ?>

                    </p>
                </div>

                <div class="admin-card-content">
                    <form method="POST" action="<?php echo e(route('admin.payment-settings.update')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="gateway" value="paypal">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">
                                        <input type="checkbox" name="is_enabled" value="1" <?php echo e($paypalSettings->is_enabled ? 'checked' : ''); ?>>
                                        <?php echo e(trans('app.Enable PayPal')); ?>

                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">
                                        <input type="checkbox" name="is_sandbox" value="1" <?php echo e($paypalSettings->is_sandbox ? 'checked' : ''); ?>>
                                        <?php echo e(trans('app.Sandbox Mode')); ?>

                                    </label>
                                    <small class="admin-form-help"><?php echo e(trans('app.Use sandbox for testing')); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label"><?php echo e(trans('app.Client ID')); ?></label>
                                    <input type="text" name="credentials[client_id]" class="admin-form-input" 
                                           value="<?php echo e($paypalSettings->credentials['client_id'] ?? ''); ?>" 
                                           placeholder="Enter PayPal Client ID" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label"><?php echo e(trans('app.Client Secret')); ?></label>
                                    <input type="password" name="credentials[client_secret]" class="admin-form-input" 
                                           value="<?php echo e($paypalSettings->credentials['client_secret'] ?? ''); ?>" 
                                           placeholder="Enter PayPal Client Secret" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="admin-form-group">
                                    <label class="admin-form-label"><?php echo e(trans('app.Webhook URL')); ?></label>
                                    <input type="url" name="webhook_url" class="admin-form-input" 
                                           value="<?php echo e($paypalSettings->webhook_url ?? ''); ?>" 
                                           placeholder="https://yoursite.com/payment/webhook/paypal">
                                    <small class="admin-form-help">
                                        <?php echo e(trans('app.Webhook URL for PayPal notifications (optional)')); ?>

                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo e(trans('app.Save PayPal Settings')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stripe Settings -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <div class="admin-card-title">
                        <i class="fab fa-stripe"></i>
                        <?php echo e(trans('app.Stripe')); ?>

                    </div>
                    <p class="admin-card-subtitle">
                        <?php echo e(trans('app.Accept credit and debit card payments')); ?>

                    </p>
                </div>

                <div class="admin-card-content">
                    <form method="POST" action="<?php echo e(route('admin.payment-settings.update')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="gateway" value="stripe">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">
                                        <input type="checkbox" name="is_enabled" value="1" <?php echo e($stripeSettings->is_enabled ? 'checked' : ''); ?>>
                                        <?php echo e(trans('app.Enable Stripe')); ?>

                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">
                                        <input type="checkbox" name="is_sandbox" value="1" <?php echo e($stripeSettings->is_sandbox ? 'checked' : ''); ?>>
                                        <?php echo e(trans('app.Sandbox Mode')); ?>

                                    </label>
                                    <small class="admin-form-help"><?php echo e(trans('app.Use sandbox for testing')); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label"><?php echo e(trans('app.Publishable Key')); ?></label>
                                    <input type="text" name="credentials[publishable_key]" class="admin-form-input" 
                                           value="<?php echo e($stripeSettings->credentials['publishable_key'] ?? ''); ?>" 
                                           placeholder="pk_test_..." required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label"><?php echo e(trans('app.Secret Key')); ?></label>
                                    <input type="password" name="credentials[secret_key]" class="admin-form-input" 
                                           value="<?php echo e($stripeSettings->credentials['secret_key'] ?? ''); ?>" 
                                           placeholder="sk_test_..." required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label"><?php echo e(trans('app.Webhook Secret')); ?></label>
                                    <input type="password" name="credentials[webhook_secret]" class="admin-form-input" 
                                           value="<?php echo e($stripeSettings->credentials['webhook_secret'] ?? ''); ?>" 
                                           placeholder="whsec_...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label"><?php echo e(trans('app.Webhook URL')); ?></label>
                                    <input type="url" name="webhook_url" class="admin-form-input" 
                                           value="<?php echo e($stripeSettings->webhook_url ?? ''); ?>" 
                                           placeholder="https://yoursite.com/payment/webhook/stripe">
                                    <small class="admin-form-help">
                                        <?php echo e(trans('app.Webhook URL for Stripe notifications (optional)')); ?>

                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo e(trans('app.Save Stripe Settings')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\payment-settings\index.blade.php ENDPATH**/ ?>