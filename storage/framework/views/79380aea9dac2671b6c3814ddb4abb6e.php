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
                                <?php echo e(trans('app.Create License')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e(trans('app.Create a new license for a customer')); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.licenses.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Licenses')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <form method="POST" action="<?php echo e(route('admin.licenses.store')); ?>" class="needs-validation" novalidate>
        <?php echo csrf_field(); ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- License Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-key me-2"></i>
                            <?php echo e(trans('app.License Information')); ?>

                            <span class="badge bg-light text-primary ms-2"><?php echo e(trans('app.Required')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    <?php echo e(trans('app.User (Owner)')); ?> <span class="text-danger">*</span>
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
                                    <option value=""><?php echo e(trans('app.Select a User')); ?></option>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>" 
                                            data-name="<?php echo e($user->name); ?>"
                                            data-email="<?php echo e($user->email); ?>"
                                        <?php echo e((old('user_id', $selectedUserId) == $user->id) ? 'selected' : ''); ?>>
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
                                <label for="product_id" class="form-label">
                                    <i class="fas fa-box text-success me-1"></i>
                                    <?php echo e(trans('app.Product')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="product_id" name="product_id" required>
                                    <option value=""><?php echo e(trans('app.Select a Product')); ?></option>
                                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($product->id); ?>" 
                                        <?php echo e(old('product_id') == $product->id ? 'selected' : ''); ?>

                                        data-duration-days="<?php echo e($product->duration_days ?? 365); ?>"
                                        data-support-days="<?php echo e($product->support_days ?? 365); ?>"
                                        data-max-domains="<?php echo e($product->max_domains ?? 1); ?>"
                                        data-license-type="<?php echo e($product->license_type ?? 'single'); ?>">
                                        <?php echo e($product->name); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['product_id'];
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
                                <label for="license_key" class="form-label">
                                    <i class="fas fa-key text-primary me-1"></i>
                                    <?php echo e(trans('app.License Key')); ?>

                                    <small class="text-muted">(<?php echo e(trans('app.Auto Generated')); ?>)</small>
                                </label>
                                <input type="text" class="form-control" id="license_key_display"
                                       value="<?php echo e(old('license_key', 'Will be generated automatically')); ?>"
                                       readonly disabled>
                                <input type="hidden" name="license_key" id="license_key_hidden" value="<?php echo e(old('license_key')); ?>">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.License key will be auto-generated when creating the license')); ?>

                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="license_type" class="form-label">
                                    <i class="fas fa-tag text-warning me-1"></i>
                                    <?php echo e(trans('app.License Type')); ?>

                                    <small class="text-muted">(<?php echo e(trans('app.Auto-filled from product')); ?>)</small>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['license_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="license_type" name="license_type">
                                    <option value=""><?php echo e(trans('app.Select License Type')); ?></option>
                                    <option value="single" <?php echo e(old('license_type') == 'single' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Single Site')); ?>

                                    </option>
                                    <option value="multi" <?php echo e(old('license_type') == 'multi' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Multi Site')); ?>

                                    </option>
                                    <option value="developer" <?php echo e(old('license_type') == 'developer' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Developer')); ?>

                                    </option>
                                    <option value="extended" <?php echo e(old('license_type') == 'extended' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Extended')); ?>

                                    </option>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Will be auto-filled from selected product')); ?>

                                </div>
                                <?php $__errorArgs = ['license_type'];
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
                                    <i class="fas fa-toggle-on text-info me-1"></i>
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
                                    <option value=""><?php echo e(trans('app.Select Status')); ?></option>
                                    <option value="active" <?php echo e(old('status', 'active') == 'active' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Active')); ?>

                                    </option>
                                    <option value="inactive" <?php echo e(old('status') == 'inactive' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Inactive')); ?>

                                    </option>
                                    <option value="suspended" <?php echo e(old('status') == 'suspended' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Suspended')); ?>

                                    </option>
                                    <option value="expired" <?php echo e(old('status') == 'expired' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Expired')); ?>

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
                                <label for="max_domains" class="form-label">
                                    <i class="fas fa-globe text-success me-1"></i>
                                    <?php echo e(trans('app.Max Domains')); ?>

                                    <small class="text-muted">(<?php echo e(trans('app.Auto-calculated')); ?>)</small>
                                </label>
                                <input type="number" class="form-control <?php $__errorArgs = ['max_domains'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="max_domains" name="max_domains" value="<?php echo e(old('max_domains', 1)); ?>" 
                                       min="1" placeholder="<?php echo e(trans('app.Maximum allowed domains')); ?>" readonly>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Calculated automatically based on license type')); ?>

                                </div>
                                <?php $__errorArgs = ['max_domains'];
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
                                      placeholder="<?php echo e(trans('app.Enter any additional notes')); ?>"><?php echo e(old('notes')); ?></textarea>
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

                <!-- Invoice Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            <?php echo e(trans('app.Invoice Settings')); ?>

                            <span class="badge bg-light text-warning ms-2"><?php echo e(trans('app.Automatic')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="invoice_payment_status" class="form-label">
                                    <i class="fas fa-credit-card text-primary me-1"></i>
                                    <?php echo e(trans('app.Invoice Payment Status')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['invoice_payment_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="invoice_payment_status" name="invoice_payment_status" required>
                                    <option value=""><?php echo e(trans('app.Select Payment Status')); ?></option>
                                    <option value="paid" <?php echo e(old('invoice_payment_status', 'paid') == 'paid' ? 'selected' : ''); ?>>
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        <?php echo e(trans('app.Paid')); ?>

                                    </option>
                                    <option value="pending" <?php echo e(old('invoice_payment_status') == 'pending' ? 'selected' : ''); ?>>
                                        <i class="fas fa-clock text-warning me-1"></i>
                                        <?php echo e(trans('app.Pending')); ?>

                                    </option>
                                </select>
                                <?php $__errorArgs = ['invoice_payment_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Choose whether the invoice should be marked as paid or pending')); ?>

                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="invoice_due_date" class="form-label">
                                    <i class="fas fa-calendar-alt text-info me-1"></i>
                                    <?php echo e(trans('app.Invoice Due Date')); ?>

                                </label>
                                <input type="date" class="form-control <?php $__errorArgs = ['invoice_due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="invoice_due_date" name="invoice_due_date" value="<?php echo e(old('invoice_due_date')); ?>">
                                <?php $__errorArgs = ['invoice_due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Leave empty to use current date for paid invoices')); ?>

                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong><?php echo e(trans('app.Note:')); ?></strong> <?php echo e(trans('app.An invoice will be automatically created based on the product price and duration when the license is created.')); ?>

                        </div>
                    </div>
                </div>

                <!-- License Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            <?php echo e(trans('app.License Settings')); ?>

                            <span class="badge bg-light text-success ms-2"><?php echo e(trans('app.Optional')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label for="order_number" class="form-label">
                                    <i class="fas fa-receipt text-info me-1"></i>
                                    <?php echo e(trans('app.Order Number')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['order_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="order_number" name="order_number" value="<?php echo e(old('order_number')); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter order number')); ?>">
                                <?php $__errorArgs = ['order_number'];
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
                                <label for="license_expires_at" class="form-label">
                                    <i class="fas fa-calendar-times text-danger me-1"></i>
                                    <?php echo e(trans('app.License Expires At')); ?>

                                    <small class="text-muted">(<?php echo e(trans('app.Auto-calculated')); ?>)</small>
                                </label>
                                <input type="date" class="form-control <?php $__errorArgs = ['license_expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="license_expires_at" name="license_expires_at" value="<?php echo e(old('license_expires_at')); ?>" readonly>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Calculated from product duration')); ?>

                                </div>
                                <?php $__errorArgs = ['license_expires_at'];
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
                                <label for="support_expires_at" class="form-label">
                                    <i class="fas fa-headset text-success me-1"></i>
                                    <?php echo e(trans('app.Support Expires At')); ?>

                                    <small class="text-muted">(<?php echo e(trans('app.Auto-calculated')); ?>)</small>
                                </label>
                                <input type="date" class="form-control <?php $__errorArgs = ['support_expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="support_expires_at" name="support_expires_at" value="<?php echo e(old('support_expires_at')); ?>" readonly>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Calculated from product support days')); ?>

                                </div>
                                <?php $__errorArgs = ['support_expires_at'];
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
                <!-- License Preview -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>
                            <?php echo e(trans('app.License Preview')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div id="license-preview" class="p-3 rounded border">
                                <i class="fas fa-key fs-1 text-primary mb-2"></i>
                                <h5 id="preview-product"><?php echo e(trans('app.Product Name')); ?></h5>
                                <p id="preview-user" class="text-muted small mb-0"><?php echo e(trans('app.User Name')); ?></p>
                                <span id="preview-status" class="badge bg-success mt-2"><?php echo e(trans('app.Active')); ?></span>
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
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Users')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success"><?php echo e($products->count()); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Products')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- License Information -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo e(trans('app.License Information')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-key text-primary me-1"></i>
                                <?php echo e(trans('app.License Key')); ?>

                            </label>
                            <p class="text-muted small" id="preview-license-key"><?php echo e(trans('app.Auto Generated')); ?></p>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-action="generate-preview">
                                    <i class="fas fa-refresh me-1"></i><?php echo e(trans('app.Generate Preview')); ?>

                                </button>
                            </div>
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
                                <i class="fas fa-globe text-info me-1"></i>
                                <?php echo e(trans('app.Max Domains')); ?>

                            </label>
                            <p class="text-muted small" id="preview-domains">1</p>
                        </div>
                    </div>
                </div>

                <!-- License Tips -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            <?php echo e(trans('app.License Tips')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo e(trans('app.Choose the right license type')); ?>

                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo e(trans('app.Set appropriate expiration date')); ?>

                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo e(trans('app.Configure domain limits')); ?>

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
                            <a href="<?php echo e(route('admin.licenses.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i><?php echo e(trans('app.Cancel')); ?>

                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?php echo e(trans('app.Create License')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/admin/licenses/create.blade.php ENDPATH**/ ?>