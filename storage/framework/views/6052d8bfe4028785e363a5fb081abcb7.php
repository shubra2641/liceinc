

<?php $__env->startSection('page-title', trans('app.Choose Payment Method')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Select your preferred payment gateway')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-credit-card"></i>
                <?php echo e(trans('app.Complete Your Purchase')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Select your preferred payment method to complete your purchase')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Product Summary -->
            <div class="user-card mb-4">
                <div class="user-card-content">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <?php if($product->image): ?>
                                <img src="<?php echo e(asset('storage/' . $product->image)); ?>" alt="<?php echo e($product->name); ?>" class="rounded w-80 h-80 object-cover">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center bg-light rounded w-80 h-80">
                                    <i class="fas fa-box fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-2"><?php echo e($product->name); ?></h4>
                            <p class="text-muted mb-2"><?php echo e(Str::limit($product->description, 120)); ?></p>
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-2"><?php echo e(trans('app.Price')); ?>:</span>
                                <span class="h4 text-success mb-0">$<?php echo e(number_format($product->price, 2)); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-credit-card"></i>
                        <?php echo e(trans('app.Payment Methods')); ?>

                    </div>
                </div>
                <div class="user-card-content">
                    <div class="row">
                        <?php $__currentLoopData = $enabledGateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-lg-6 mb-4">
                                <div class="user-card payment-gateway-card">
                                    <div class="user-card-content">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3">
                                                <?php if($gateway === 'paypal'): ?>
                                                    <div class="user-stat-icon blue">
                                                        <i class="fab fa-paypal"></i>
                                                    </div>
                                                <?php elseif($gateway === 'stripe'): ?>
                                                    <div class="user-stat-icon green">
                                                        <i class="fab fa-stripe"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">
                                                    <?php if($gateway === 'paypal'): ?>
                                                        <?php echo e(trans('app.PayPal')); ?>

                                                    <?php elseif($gateway === 'stripe'): ?>
                                                        <?php echo e(trans('app.Stripe')); ?>

                                                    <?php endif; ?>
                                                </h5>
                                                <p class="text-muted mb-0 small">
                                                    <?php if($gateway === 'paypal'): ?>
                                                        <?php echo e(trans('app.Pay securely with PayPal')); ?>

                                                    <?php elseif($gateway === 'stripe'): ?>
                                                        <?php echo e(trans('app.Pay with credit or debit card')); ?>

                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <form method="POST" action="<?php echo e(route('payment.process', $product)); ?>" class="d-grid">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="gateway" value="<?php echo e($gateway); ?>">
                                            <button type="submit" class="user-action-button primary">
                                                <i class="fas fa-arrow-right"></i>
                                                <?php echo e(trans('app.Pay Now')); ?> - $<?php echo e(number_format($product->price, 2)); ?>

                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="user-card">
                <div class="user-card-content">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="user-stat-icon green">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-1"><?php echo e(trans('app.Secure Payment')); ?></h6>
                            <p class="text-muted mb-0 small"><?php echo e(trans('app.Your payment information is encrypted and secure. We do not store your payment details.')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\payment\gateways.blade.php ENDPATH**/ ?>