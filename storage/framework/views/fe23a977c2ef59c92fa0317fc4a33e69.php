

<?php $__env->startSection('title', trans('app.Create Support Ticket')); ?>
<?php $__env->startSection('page-title', trans('app.Create Support Ticket')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Get help with your products and licenses')); ?>

<?php $__env->startSection('seo_title', $ticketsSeoTitle ?? $siteSeoTitle ?? trans('app.Create Support Ticket')); ?>
<?php $__env->startSection('meta_description', $ticketsSeoDescription ?? $siteSeoDescription ?? trans('app.Get help with your products and licenses')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-plus"></i>
                <?php echo e(trans('app.Create Support Ticket')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Get help with your products and licenses')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Quick Help -->
            <div class="quick-help-section">
                <div class="quick-help-header">
                    <h3><?php echo e(trans('app.Need Quick Help?')); ?></h3>
                    <p><?php echo e(trans('app.Before creating a ticket, check our knowledge base for instant answers')); ?></p>
                </div>
                
                <div class="quick-help-actions">
                    <a href="<?php echo e(route('kb.index')); ?>" class="user-action-button">
                        <i class="fas fa-book"></i>
                        <?php echo e(trans('app.Browse Knowledge Base')); ?>

                    </a>
                    
                    <a href="<?php echo e(route('kb.index')); ?>?category=faq" class="user-action-button">
                        <i class="fas fa-question-circle"></i>
                        <?php echo e(trans('app.Frequently Asked Questions')); ?>

                    </a>
                </div>
            </div>

            <!-- Ticket Form -->
            <form action="<?php echo e(route('user.tickets.store')); ?>" method="POST" class="ticket-form">
                <?php echo csrf_field(); ?>
                
                <div class="form-grid">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3><?php echo e(trans('app.Basic Information')); ?></h3>
                        
                        <div class="form-group">
                            <label for="subject"><?php echo e(trans('app.Subject')); ?> <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" class="form-input" placeholder="<?php echo e(trans('app.Brief description of your issue')); ?>" value="<?php echo e(old('subject')); ?>" required>
                            <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id"><?php echo e(trans('app.Category')); ?> <span class="required">*</span></label>
                            <select id="category_id" name="category_id" class="form-select" required>
                                <option value=""><?php echo e(trans('app.Select a category')); ?></option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" 
                                        data-requires-purchase-code="<?php echo e($category->requires_valid_purchase_code ? 'true' : 'false'); ?>"
                                        <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>>
                                    <?php echo e($category->name); ?> 
                                    <?php if($category->requires_valid_purchase_code): ?> (Requires Purchase Code) <?php endif; ?>
                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            
                            <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority"><?php echo e(trans('app.Priority')); ?> <span class="required">*</span></label>
                            <select id="priority" name="priority" class="form-select" required>
                                <option value=""><?php echo e(trans('app.Select priority')); ?></option>
                                <option value="low" <?php echo e(old('priority') == 'low' ? 'selected' : ''); ?>><?php echo e(trans('app.Low')); ?></option>
                                <option value="medium" <?php echo e(old('priority') == 'medium' ? 'selected' : ''); ?>><?php echo e(trans('app.Medium')); ?></option>
                                <option value="high" <?php echo e(old('priority') == 'high' ? 'selected' : ''); ?>><?php echo e(trans('app.High')); ?></option>
                            </select>
                            <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    
                    <!-- Related Information -->
                    <div class="form-section">
                        <h3><?php echo e(trans('app.Related Information')); ?></h3>
                        
                        <div id="purchase-code-section" class="form-group hidden">
                            <label for="purchase_code"><?php echo e(trans('app.Purchase Code')); ?> <span class="required hidden" id="purchase-code-required">*</span></label>
                            <input type="text" id="purchase_code" name="purchase_code" class="form-input" placeholder="<?php echo e(trans('app.Enter your purchase code if applicable')); ?>" value="<?php echo e(old('purchase_code')); ?>">
                            <small class="form-help"><?php echo e(trans('app.Purchase code will be verified automatically')); ?></small>
                            <?php $__errorArgs = ['purchase_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div id="product-slug-section" class="form-group hidden">
                            <label for="product_slug"><?php echo e(trans('app.Product Slug')); ?></label>
                            <input type="text" id="product_slug" name="product_slug" class="form-input" placeholder="<?php echo e(trans('app.Product identifier from URL')); ?>" value="<?php echo e(old('product_slug')); ?>" readonly>
                            <small class="form-help"><?php echo e(trans('app.Product slug will be filled automatically from purchase code')); ?></small>
                            <?php $__errorArgs = ['product_slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            
                            <div id="product-name-display" class="product-info hidden">
                                <strong><?php echo e(trans('app.Product')); ?>:</strong> <span id="product-name"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="invoice_id"><?php echo e(trans('app.Related Invoice')); ?></label>
                            <select id="invoice_id" name="invoice_id" class="form-select">
                                <option value=""><?php echo e(trans('app.Select invoice (optional)')); ?></option>
                                <?php if(auth()->check()): ?>
                                    <?php $__currentLoopData = auth()->user()->invoices ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($invoice->id); ?>" <?php echo e(old('invoice_id') == $invoice->id ? 'selected' : ''); ?>>
                                        #<?php echo e($invoice->id); ?> - <?php echo e($invoice->total); ?> <?php echo e($invoice->currency); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                            <?php $__errorArgs = ['invoice_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group hidden">
                            <label for="product_version"><?php echo e(trans('app.Product Version')); ?></label>
                            <input type="text" id="product_version" name="product_version" class="form-input" placeholder="<?php echo e(trans('app.e.g., 1.0.0')); ?>" value="<?php echo e(old('product_version')); ?>">
                            <?php $__errorArgs = ['product_version'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group hidden">
                            <input type="hidden" id="browser_info" name="browser_info" value="<?php echo e(old('browser_info')); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="form-section">
                    <h3><?php echo e(trans('app.Description')); ?></h3>
                    
                    <div class="form-group">
                        <label for="content"><?php echo e(trans('app.Detailed Description')); ?> <span class="required">*</span></label>
                        <textarea id="content" name="content" rows="8" class="form-textarea" placeholder="<?php echo e(trans('app.Please provide a detailed description of your issue, including steps to reproduce if applicable...')); ?>" required><?php echo e(old('content')); ?></textarea>
                        <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="form-error"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="<?php echo e(route('user.tickets.index')); ?>" class="user-action-button secondary">
                        <i class="fas fa-arrow-left"></i>
                        <?php echo e(trans('app.Cancel')); ?>

                    </a>
                    
                    <button type="submit" class="user-action-button">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo e(trans('app.Create Ticket')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\user\tickets\create.blade.php ENDPATH**/ ?>