<?php $__env->startSection('title', 'Create Invoice'); ?>

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
                                <i class="fas fa-plus-circle text-primary me-2"></i>
                                <?php echo e(trans('app.Create Invoice')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e(trans('app.Create a new invoice for a customer')); ?></p>
        </div>
                        <div>
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

<?php if($errors->any()): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo e(trans('app.Validation Errors')); ?>

                </h5>
                <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

    <form method="POST" action="<?php echo e(route('admin.invoices.store')); ?>" class="needs-validation" novalidate>
    <?php echo csrf_field(); ?>

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
                            data-name="<?php echo e($user->name); ?>"
                            data-email="<?php echo e($user->email); ?>"
                            <?php echo e(old('user_id') == $user->id ? 'selected' : ''); ?>>
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
                    <option value="custom"><?php echo e(trans('app.Custom Invoice (No License)')); ?></option>
                </select>
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    <?php echo e(trans('app.Select a customer first to load their licenses')); ?>

                </div>
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
                                    <option value="initial" <?php echo e(old('type') == 'initial' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Initial Purchase')); ?>

                                    </option>
                                    <option value="renewal" <?php echo e(old('type') == 'renewal' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Renewal')); ?>

                                    </option>
                                    <option value="upgrade" <?php echo e(old('type') == 'upgrade' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Upgrade')); ?>

                                    </option>
                                    <option value="custom" <?php echo e(old('type') == 'custom' ? 'selected' : ''); ?>>
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
                                    <option value="pending" <?php echo e(old('status', 'pending') == 'pending' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Pending')); ?>

                                    </option>
                                    <option value="paid" <?php echo e(old('status') == 'paid' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Paid')); ?>

                                    </option>
                                    <option value="overdue" <?php echo e(old('status') == 'overdue' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Overdue')); ?>

                                    </option>
                                    <option value="cancelled" <?php echo e(old('status') == 'cancelled' ? 'selected' : ''); ?>>
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
                                       id="amount" name="amount" value="<?php echo e(old('amount')); ?>" 
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
                    <option value="USD" <?php echo e(old('currency', 'USD') == 'USD' ? 'selected' : ''); ?>>USD</option>
                    <option value="EUR" <?php echo e(old('currency') == 'EUR' ? 'selected' : ''); ?>>EUR</option>
                    <option value="GBP" <?php echo e(old('currency') == 'GBP' ? 'selected' : ''); ?>>GBP</option>
                    <option value="SAR" <?php echo e(old('currency') == 'SAR' ? 'selected' : ''); ?>>SAR</option>
                    <option value="AED" <?php echo e(old('currency') == 'AED' ? 'selected' : ''); ?>>AED</option>
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
                       value="<?php echo e(old('due_date', now()->addDays(30)->format('Y-m-d'))); ?>">
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

                            <div class="col-md-6 mb-3" id="paid_at_group" class="hidden-field">
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
                                       value="<?php echo e(old('paid_at', now()->format('Y-m-d'))); ?>">
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
                                      placeholder="<?php echo e(trans('app.Add any additional notes for this invoice')); ?>"><?php echo e(old('notes')); ?></textarea>
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
                <div class="card mb-4" id="custom_invoice_fields" class="hidden-field">
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
                                    <option value="one_time" <?php echo e(old('custom_invoice_type') == 'one_time' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.One-time Payment')); ?>

                                    </option>
                                    <option value="monthly" <?php echo e(old('custom_invoice_type') == 'monthly' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Monthly')); ?>

                                    </option>
                                    <option value="quarterly" <?php echo e(old('custom_invoice_type') == 'quarterly' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Quarterly')); ?>

                                    </option>
                                    <option value="semi_annual" <?php echo e(old('custom_invoice_type') == 'semi_annual' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Semi-Annual')); ?>

                                    </option>
                                    <option value="annual" <?php echo e(old('custom_invoice_type') == 'annual' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Annual')); ?>

                                    </option>
                                    <option value="custom_recurring" <?php echo e(old('custom_invoice_type', 'custom_recurring') == 'custom_recurring' ? 'selected' : ''); ?>>
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
                                       value="<?php echo e(old('custom_product_name')); ?>" 
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
                                       value="<?php echo e(old('expiration_date')); ?>">
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
                                <h5 id="preview-customer"><?php echo e(trans('app.Customer Name')); ?></h5>
                                <p id="preview-amount" class="text-muted small mb-0">$0.00 USD</p>
                                <span id="preview-status" class="badge bg-warning mt-2"><?php echo e(trans('app.Pending')); ?></span>
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
                                    <h4 class="text-primary"><?php echo e($users->count()); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Customers')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success">0</h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Total Invoices')); ?></p>
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
                            <p class="text-muted small" id="preview-invoice-number"><?php echo e(trans('app.Auto Generated')); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-success me-1"></i>
                                <?php echo e(trans('app.Created At')); ?>

                            </label>
                            <p class="text-muted small"><?php echo e(now()->format('M d, Y H:i')); ?></p>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-danger me-1"></i>
                                <?php echo e(trans('app.Due Date')); ?>

                </label>
                            <p class="text-muted small" id="preview-due-date"><?php echo e(now()->addDays(30)->format('M d, Y')); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Tips -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            <?php echo e(trans('app.Invoice Tips')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo e(trans('app.Choose the right invoice type')); ?>

                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo e(trans('app.Set appropriate due date')); ?>

                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo e(trans('app.Verify amount and currency')); ?>

                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo e(trans('app.Add relevant notes')); ?>

                            </li>
                        </ul>
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
                                <i class="fas fa-save me-1"></i><?php echo e(trans('app.Create Invoice')); ?>

                            </button>
                        </div>
                    </div>
        </div>
    </div>
    </div>
</form>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Pass translations to JavaScript
    window.translations = {
        searchPlaceholder: '<?php echo e(trans("app.Search for user by name or email")); ?>',
        noResults: '<?php echo e(trans("app.No results found")); ?>',
        searching: '<?php echo e(trans("app.Searching...")); ?>',
        inputTooShort: '<?php echo e(trans("app.Please enter at least one character")); ?>',
        selectLicense: '<?php echo e(trans("app.Select License")); ?>',
        customInvoice: '<?php echo e(trans("app.Custom Invoice (No License)")); ?>',
        noLicensesFound: '<?php echo e(trans("app.No licenses found for this user")); ?>',
        licenseKey: '<?php echo e(trans("app.License Key")); ?>',
        expiresAt: '<?php echo e(trans("app.Expires At")); ?>',
        unknownProduct: '<?php echo e(trans("app.Unknown Product")); ?>',
        notSpecified: '<?php echo e(trans("app.Not Specified")); ?>',
        active: '<?php echo e(trans("app.Active")); ?>',
        inactive: '<?php echo e(trans("app.Inactive")); ?>',
        suspended: '<?php echo e(trans("app.Suspended")); ?>',
        expired: '<?php echo e(trans("app.Expired")); ?>'
    };

</script>
<script src="<?php echo e(asset('assets/admin/js/searchable-select.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\my-logos\resources\views/admin/invoices/create.blade.php ENDPATH**/ ?>