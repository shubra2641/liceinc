<?php $__env->startSection('title', 'Edit Invoice'); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid products-form">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-edit text-primary me-2"></i>
                                <?php echo e(trans('app.Edit Invoice')); ?>

                            </h1>
                            <p class="text-muted mb-0">#<?php echo e($invoice->invoice_number ?? $invoice->id); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.invoices.show', $invoice)); ?>" class="btn btn-info me-2">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo e(trans('app.View Invoice')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.invoices.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Invoices')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    

    <form method="POST" action="<?php echo e(route('admin.invoices.update', $invoice)); ?>" class="needs-validation" novalidate>
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Invoice Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            <?php echo e(trans('app.Invoice Information')); ?>

                            <span class="badge bg-light text-primary ms-2"><?php echo e(trans('app.Required')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    <?php echo e(trans('app.Customer')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="user_id" name="user_id" required>
                                    <option value=""><?php echo e(trans('app.Select Customer')); ?></option>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>" 
                                        <?php echo e(old('user_id', $invoice->user_id) == $user->id ? 'selected' : ''); ?>>
                                        <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)
                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="license_id" class="form-label">
                                    <i class="fas fa-key text-success me-1"></i>
                                    <?php echo e(trans('app.License')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['license_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="license_id" name="license_id" required>
                                    <option value=""><?php echo e(trans('app.Select License')); ?></option>
                                    <option value="custom" <?php echo e(old('license_id', $invoice->license_id) == 'custom' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Custom Invoice (No License)')); ?>

                                    </option>
                                    <?php if($invoice->license): ?>
                                    <option value="<?php echo e($invoice->license->id); ?>" selected>
                                        <?php echo e($invoice->license->product->name); ?> - <?php echo e($invoice->license->license_type); ?>

                                    </option>
                                    <?php endif; ?>
                                </select>
                                <?php $__errorArgs = ['license_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    <i class="fas fa-tag text-warning me-1"></i>
                                    <?php echo e(trans('app.Invoice Type')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="type" name="type" required>
                                    <option value="initial" <?php echo e(old('type', $invoice->type) == 'initial' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Initial Purchase')); ?>

                                    </option>
                                    <option value="renewal" <?php echo e(old('type', $invoice->type) == 'renewal' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Renewal')); ?>

                                    </option>
                                    <option value="upgrade" <?php echo e(old('type', $invoice->type) == 'upgrade' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Upgrade')); ?>

                                    </option>
                                    <option value="custom" <?php echo e(old('type', $invoice->type) == 'custom' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Custom')); ?>

                                    </option>
                                </select>
                                <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    <?php echo e(trans('app.Status')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="status" name="status" required>
                                    <option value="pending" <?php echo e(old('status', $invoice->status) == 'pending' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Pending')); ?>

                                    </option>
                                    <option value="paid" <?php echo e(old('status', $invoice->status) == 'paid' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Paid')); ?>

                                    </option>
                                    <option value="overdue" <?php echo e(old('status', $invoice->status) == 'overdue' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Overdue')); ?>

                                    </option>
                                    <option value="cancelled" <?php echo e(old('status', $invoice->status) == 'cancelled' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Cancelled')); ?>

                                    </option>
                                </select>
                                <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">
                                    <i class="fas fa-dollar-sign text-success me-1"></i>
                                    <?php echo e(trans('app.Amount')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="amount" name="amount" value="<?php echo e(old('amount', $invoice->amount)); ?>" 
                                       step="0.01" min="0" required>
                                <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">
                                    <i class="fas fa-money-bill text-warning me-1"></i>
                                    <?php echo e(trans('app.Currency')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="currency" name="currency" required>
                                    <option value="USD" <?php echo e(old('currency', $invoice->currency) == 'USD' ? 'selected' : ''); ?>>USD</option>
                                    <option value="EUR" <?php echo e(old('currency', $invoice->currency) == 'EUR' ? 'selected' : ''); ?>>EUR</option>
                                    <option value="GBP" <?php echo e(old('currency', $invoice->currency) == 'GBP' ? 'selected' : ''); ?>>GBP</option>
                                    <option value="SAR" <?php echo e(old('currency', $invoice->currency) == 'SAR' ? 'selected' : ''); ?>>SAR</option>
                                    <option value="AED" <?php echo e(old('currency', $invoice->currency) == 'AED' ? 'selected' : ''); ?>>AED</option>
                                </select>
                                <?php $__errorArgs = ['currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">
                                    <i class="fas fa-calendar text-danger me-1"></i>
                                    <?php echo e(trans('app.Due Date')); ?>

                                </label>
                                <input type="date" class="form-control <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="due_date" name="due_date" 
                                       value="<?php echo e(old('due_date', $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '')); ?>">
                                <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3" id="paid_at_group" class="<?php echo e($invoice->status == 'paid' ? 'visible-field' : 'hidden-field'); ?>">
                                <label for="paid_at" class="form-label">
                                    <i class="fas fa-check text-success me-1"></i>
                                    <?php echo e(trans('app.Paid At')); ?>

                                </label>
                                <input type="date" class="form-control <?php $__errorArgs = ['paid_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="paid_at" name="paid_at" 
                                       value="<?php echo e(old('paid_at', $invoice->paid_at ? $invoice->paid_at->format('Y-m-d') : now()->format('Y-m-d'))); ?>">
                                <?php $__errorArgs = ['paid_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note text-warning me-1"></i>
                                <?php echo e(trans('app.Notes')); ?>

                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="notes" name="notes" rows="4"
                                      placeholder="<?php echo e(trans('app.Add any additional notes for this invoice')); ?>"><?php echo e(old('notes', $invoice->notes)); ?></textarea>
                            <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <!-- Custom Invoice Fields -->
                <?php if($invoice->license_id == 'custom' || old('license_id') == 'custom'): ?>
                <div class="card mb-4" id="custom_invoice_fields">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            <?php echo e(trans('app.Custom Invoice Settings')); ?>

                            <span class="badge bg-light text-success ms-2"><?php echo e(trans('app.Required for Custom')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="custom_invoice_type" class="form-label">
                                    <i class="fas fa-cog text-warning me-1"></i>
                                    <?php echo e(trans('app.Custom Invoice Type')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['custom_invoice_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="custom_invoice_type" name="custom_invoice_type">
                                    <option value="one_time" <?php echo e(old('custom_invoice_type', $invoice->custom_invoice_type) == 'one_time' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.One-time Payment')); ?>

                                    </option>
                                    <option value="monthly" <?php echo e(old('custom_invoice_type', $invoice->custom_invoice_type) == 'monthly' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Monthly')); ?>

                                    </option>
                                    <option value="quarterly" <?php echo e(old('custom_invoice_type', $invoice->custom_invoice_type) == 'quarterly' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Quarterly')); ?>

                                    </option>
                                    <option value="semi_annual" <?php echo e(old('custom_invoice_type', $invoice->custom_invoice_type) == 'semi_annual' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Semi-Annual')); ?>

                                    </option>
                                    <option value="annual" <?php echo e(old('custom_invoice_type', $invoice->custom_invoice_type) == 'annual' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Annual')); ?>

                                    </option>
                                    <option value="custom_recurring" <?php echo e(old('custom_invoice_type', $invoice->custom_invoice_type) == 'custom_recurring' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Custom Recurring')); ?>

                                    </option>
                                </select>
                                <?php $__errorArgs = ['custom_invoice_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="custom_product_name" class="form-label">
                                    <i class="fas fa-shopping-cart text-primary me-1"></i>
                                    <?php echo e(trans('app.Product/Service Description')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['custom_product_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="custom_product_name" name="custom_product_name" 
                                       value="<?php echo e(old('custom_product_name', $invoice->custom_product_name)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter product or service description')); ?>">
                                <?php $__errorArgs = ['custom_product_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3" id="expiration_date_group">
                                <label for="expiration_date" class="form-label">
                                    <i class="fas fa-calendar-times text-danger me-1"></i>
                                    <?php echo e(trans('app.Expiration Date')); ?>

                                </label>
                                <input type="date" class="form-control <?php $__errorArgs = ['expiration_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="expiration_date" name="expiration_date" 
                                       value="<?php echo e(old('expiration_date', $invoice->expiration_date ? $invoice->expiration_date->format('Y-m-d') : '')); ?>">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Leave empty for one-time payment')); ?>

                                </div>
                                <?php $__errorArgs = ['expiration_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Invoice Preview -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>
                            <?php echo e(trans('app.Invoice Preview')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div id="invoice-preview" class="p-3 rounded border">
                                <i class="fas fa-file-invoice-dollar fs-1 text-primary mb-2"></i>
                                <h5 id="preview-customer"><?php echo e($invoice->user->name ?? trans('app.Customer Name')); ?></h5>
                                <p id="preview-amount" class="text-muted small mb-0"><?php echo e($invoice->amount); ?> <?php echo e($invoice->currency); ?></p>
                                <span id="preview-status" class="badge bg-<?php echo e($invoice->status == 'paid' ? 'success' : ($invoice->status == 'overdue' ? 'danger' : 'warning')); ?> mt-2">
                                    <?php echo e(trans('app.' . ucfirst($invoice->status))); ?>

                                </span>
                            </div>
                            <p class="text-muted small mt-2"><?php echo e(trans('app.Live Preview')); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            <?php echo e(trans('app.Quick Stats')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-primary"><?php echo e($invoice->user->invoices_count ?? 0); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.User Invoices')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success"><?php echo e($invoice->amount); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Amount')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-info"><?php echo e($invoice->created_at->format('M Y')); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Created')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-warning"><?php echo e($invoice->updated_at->format('M Y')); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Updated')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Information -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo e(trans('app.Invoice Information')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-hashtag text-primary me-1"></i>
                                <?php echo e(trans('app.Invoice Number')); ?>

                            </label>
                            <p class="text-muted small"><?php echo e($invoice->invoice_number ?? '#' . $invoice->id); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-success me-1"></i>
                                <?php echo e(trans('app.Created At')); ?>

                            </label>
                            <p class="text-muted small"><?php echo e($invoice->created_at->format('M d, Y H:i')); ?></p>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-danger me-1"></i>
                                <?php echo e(trans('app.Due Date')); ?>

                            </label>
                            <p class="text-muted small" id="preview-due-date">
                                <?php echo e($invoice->due_date ? $invoice->due_date->format('M d, Y') : trans('app.No Due Date')); ?>

                            </p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            <?php echo e(trans('app.Invoice Actions')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo e(route('admin.invoices.show', $invoice)); ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo e(trans('app.View Invoice')); ?>

                            </a>
                            <?php if($invoice->user): ?>
                            <a href="<?php echo e(route('admin.users.show', $invoice->user)); ?>" class="btn btn-outline-success">
                                <i class="fas fa-user me-1"></i>
                                <?php echo e(trans('app.View Customer')); ?>

                            </a>
                            <?php endif; ?>
                            <?php if($invoice->license): ?>
                            <a href="<?php echo e(route('admin.licenses.show', $invoice->license)); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-key me-1"></i>
                                <?php echo e(trans('app.View License')); ?>

                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('admin.invoices.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i><?php echo e(trans('app.Cancel')); ?>

                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?php echo e(trans('app.Save Changes')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo e(trans('app.Danger Zone')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3"><?php echo e(trans('app.Delete Invoice Warning')); ?></p>
                    <form method="post" action="<?php echo e(route('admin.invoices.destroy', $invoice)); ?>" 
                          data-confirm="delete-invoice">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i><?php echo e(trans('app.Delete Invoice')); ?>

                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\invoices\edit.blade.php ENDPATH**/ ?>