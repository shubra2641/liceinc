<?php $__env->startSection('title', trans('app.Invoice Details')); ?>
<?php $__env->startSection('page-title', trans('app.Invoice Details')); ?>
<?php $__env->startSection('page-subtitle', trans('app.View invoice information and make payments')); ?>


<?php $__env->startSection('seo_title', $siteSeoTitle ?? trans('app.Invoice Details')); ?>
<?php $__env->startSection('meta_description', $siteSeoDescription ?? trans('app.View invoice information and make payments')); ?>


<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-file-invoice"></i>
                <?php echo e(trans('app.Invoice')); ?> #<?php echo e($invoice->invoice_number); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Invoice details and payment information')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Invoice Status Banner -->
            <div class="invoice-status-banner invoice-status-<?php echo e($invoice->status); ?>">
                <div class="status-content">
                    <i class="fas fa-<?php echo e($invoice->status === 'paid' ? 'check-circle' : ($invoice->status === 'pending' ? 'clock' : 'times-circle')); ?>"></i>
                    <div>
                        <h3><?php echo e(trans('app.Invoice')); ?> <?php echo e(ucfirst($invoice->status)); ?></h3>
                        <p>
                            <?php if($invoice->status === 'paid'): ?>
                                <?php echo e(trans('app.This invoice has been paid successfully')); ?>

                            <?php elseif($invoice->status === 'pending'): ?>
                                <?php echo e(trans('app.This invoice is pending payment')); ?>

                            <?php else: ?>
                                <?php echo e(trans('app.This invoice has been cancelled')); ?>

                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Invoice Details Grid -->
            <div class="license-details-grid">
                <!-- Invoice Information -->
                <div class="license-info-card">
                    <div class="license-info-header">
                        <h3><?php echo e(trans('app.Invoice Information')); ?></h3>
                    </div>
                    
                    <div class="license-info-content">
                        <div class="info-row">
                            <label><?php echo e(trans('app.Invoice Number')); ?>:</label>
                            <span><?php echo e($invoice->invoice_number); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Status')); ?>:</label>
                            <span class="invoice-status-badge invoice-status-<?php echo e($invoice->status); ?>">
                                <?php echo e(ucfirst($invoice->status)); ?>

                            </span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Amount')); ?>:</label>
                            <span class="invoice-amount">$<?php echo e(number_format($invoice->amount, 2)); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Created Date')); ?>:</label>
                            <span><?php echo e($invoice->created_at->format('M d, Y')); ?></span>
                        </div>
                        
                        <?php if($invoice->due_date): ?>
                        <div class="info-row">
                            <label><?php echo e(trans('app.Due Date')); ?>:</label>
                            <span><?php echo e($invoice->due_date->format('M d, Y')); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($invoice->paid_at): ?>
                        <div class="info-row">
                            <label><?php echo e(trans('app.Paid Date')); ?>:</label>
                            <span><?php echo e($invoice->paid_at->format('M d, Y')); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Information -->
                <?php if($hasLicense): ?>
                <div class="license-info-card">
                    <div class="license-info-header">
                        <h3><?php echo e(trans('app.Product Information')); ?></h3>
                    </div>
                    
                    <div class="license-info-content">
                        <div class="info-row">
                            <label><?php echo e(trans('app.Product')); ?>:</label>
                            <span><?php echo e($invoice->license->product?->name ?? 'N/A'); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Version')); ?>:</label>
                            <span>v<?php echo e($invoice->license->product?->version ?? '-'); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.License Type')); ?>:</label>
                            <span class="license-type-badge"><?php echo e(ucfirst($invoice->license->license_type ?? '-')); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.License Key')); ?>:</label>
                            <div class="license-key-display">
                                <code class="license-key-code"><?php echo e($invoice->license->license_key); ?></code>
                                <button class="copy-key-btn" data-key="<?php echo e($invoice->license->license_key); ?>" title="<?php echo e(trans('app.Copy License Key')); ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Actions')); ?>:</label>
                            <?php if($hasLicense): ?>
                            <a href="<?php echo e(route('public.products.show', $invoice->license->product->slug)); ?>" class="license-action-link">
                                <i class="fas fa-external-link-alt"></i>
                                <?php echo e(trans('app.View Product')); ?>

                            </a>
                            <?php else: ?>
                            <span class="text-muted"><?php echo e(trans('app.No product available')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php elseif($isCustomInvoice): ?>
                <!-- Custom Invoice Information -->
                <div class="license-info-card">
                    <div class="license-info-header">
                        <h3><?php echo e(trans('app.Service Information')); ?></h3>
                    </div>
                    
                    <div class="license-info-content">
                        <div class="info-row">
                            <label><?php echo e(trans('app.Service Type')); ?>:</label>
                            <span><?php echo e(trans('app.Additional Service')); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Description')); ?>:</label>
                            <span><?php echo e($invoice->notes ?? trans('app.Custom service invoice')); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <label><?php echo e(trans('app.Invoice Type')); ?>:</label>
                            <span class="license-type-badge"><?php echo e(trans('app.Service Invoice')); ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Payment Section -->
            <?php if($invoice->status === 'pending'): ?>
            <div id="payment" class="payment-section">
                <div class="section-header">
                    <h3><?php echo e(trans('app.Make Payment')); ?></h3>
                    <p class="text-muted"><?php echo e(trans('app.Choose your preferred payment method to complete the payment')); ?></p>
                </div>
                
                <div class="payment-methods">
                    
                    <?php if(empty($enabledGateways)): ?>
                        <div data-flash-warning class="flash-message-hidden"><?php echo e(trans('app.No payment gateways are currently available. Please contact support.')); ?></div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo e(trans('app.No payment gateways are currently available. Please contact support.')); ?>

                        </div>
                    <?php else: ?>
                        <?php $__currentLoopData = $enabledGateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="payment-method-card">
                                <div class="payment-method-header">
                                    <?php if($gateway === 'stripe'): ?>
                                        <i class="fas fa-credit-card text-primary"></i>
                                        <h4><?php echo e(trans('app.Credit Card')); ?></h4>
                                    <?php elseif($gateway === 'paypal'): ?>
                                        <i class="fab fa-paypal text-primary"></i>
                                        <h4><?php echo e(trans('app.PayPal')); ?></h4>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if($gateway === 'stripe'): ?>
                                    <p><?php echo e(trans('app.Pay securely with your credit or debit card')); ?></p>
                                <?php elseif($gateway === 'paypal'): ?>
                                    <p><?php echo e(trans('app.Pay with your PayPal account')); ?></p>
                                <?php endif; ?>
                                
                                <?php if($productForPayment || $isCustomInvoice): ?>
                                <form method="POST" action="<?php echo e($productForPayment ? route('payment.process', $productForPayment) : route('payment.process.custom', $invoice)); ?>" class="inline-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="gateway" value="<?php echo e($gateway); ?>">
                                    <input type="hidden" name="invoice_id" value="<?php echo e($invoice->id); ?>">
                                    <button type="submit" class="user-action-button primary">
                                        <?php if($gateway === 'stripe'): ?>
                                            <i class="fas fa-credit-card"></i>
                                            <?php echo e(trans('app.Pay with Credit Card')); ?>

                                        <?php elseif($gateway === 'paypal'): ?>
                                            <i class="fab fa-paypal"></i>
                                            <?php echo e(trans('app.Pay with PayPal')); ?>

                                        <?php endif; ?>
                                    </button>
                                </form>
                                <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?php echo e(trans('app.No product available for payment')); ?>

                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Information -->
                <div class="payment-info mt-4">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong><?php echo e(trans('app.Payment Amount')); ?>:</strong> 
                        <span class="fw-bold">$<?php echo e(number_format($invoice->amount, 2)); ?></span>
                        <?php if($invoice->due_date): ?>
                            <br>
                            <strong><?php echo e(trans('app.Due Date')); ?>:</strong> 
                            <?php echo e($invoice->due_date->format('M d, Y')); ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Download Section -->
            <?php if($invoice->status === 'paid' && $hasLicense): ?>
            <div id="download" class="download-section">
                <div class="section-header">
                    <h3><?php echo e(trans('app.Download Product')); ?></h3>
                </div>
                
                <div class="download-content">
                        <div class="download-info">
                            <i class="fas fa-download"></i>
                            <div>
                                <h4><?php echo e($invoice->license->product?->name ?? 'N/A'); ?></h4>
                                <p><?php echo e(trans('app.Your product is ready for download')); ?></p>
                            </div>
                        </div>
                    
                    <div class="download-actions">
                        <?php if($hasLicense): ?>
                        <a href="<?php echo e(route('public.products.show', $invoice->license->product->slug)); ?>" class="user-action-button">
                            <i class="fas fa-download"></i>
                            <?php echo e(trans('app.Download Product')); ?>

                        </a>
                        <?php endif; ?>
                        
                        <?php if($invoice->license): ?>
                        <a href="<?php echo e(route('user.licenses.show', $invoice->license)); ?>" class="user-action-button">
                            <i class="fas fa-key"></i>
                            <?php echo e(trans('app.View License')); ?>

                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php elseif($invoice->status === 'paid' && $isCustomInvoice): ?>
            <!-- Service Completion Section -->
            <div id="service" class="download-section">
                <div class="section-header">
                    <h3><?php echo e(trans('app.Service Status')); ?></h3>
                </div>
                
                <div class="download-content">
                    <div class="download-info">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <h4><?php echo e(trans('app.Service Payment Completed')); ?></h4>
                            <p><?php echo e(trans('app.Your payment has been received and the service will be processed.')); ?></p>
                        </div>
                    </div>
                    
                    <div class="download-actions">
                        <a href="<?php echo e(route('user.dashboard')); ?>" class="user-action-button">
                            <i class="fas fa-tachometer-alt"></i>
                            <?php echo e(trans('app.Go to Dashboard')); ?>

                        </a>
                        
                        <a href="<?php echo e(route('user.invoices.index')); ?>" class="user-action-button">
                            <i class="fas fa-file-invoice"></i>
                            <?php echo e(trans('app.View All Invoices')); ?>

                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Invoice Actions -->
            <div class="license-actions-section">
                <div class="action-buttons">
                    <a href="<?php echo e(route('user.invoices.index')); ?>" class="user-action-button">
                        <i class="fas fa-arrow-left"></i>
                        <?php echo e(trans('app.Back to Invoices')); ?>

                    </a>
                    
                    <button class="user-action-button" data-action="print">
                        <i class="fas fa-print"></i>
                        <?php echo e(trans('app.Print Invoice')); ?>

                    </button>
                    
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
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\user\invoices\show.blade.php ENDPATH**/ ?>