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
                                <?php echo e(trans('app.Edit License')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($license->license_key); ?></p>
            </div>
                        <div>
                            <a href="<?php echo e(route('admin.licenses.show', $license)); ?>" class="btn btn-info me-2">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo e(trans('app.View License')); ?>

                            </a>
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

    <form method="POST" action="<?php echo e(route('admin.licenses.update', $license)); ?>" class="needs-validation" novalidate>
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

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
                                        <?php echo e(old('user_id', $license->user_id) == $user->id ? 'selected' : ''); ?>>
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
                                        <?php echo e(old('product_id', $license->product_id) == $product->id ? 'selected' : ''); ?>>
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
                                    <option value="single" <?php echo e(old('license_type', $license->license_type) == 'single' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Single Site')); ?>

                                    </option>
                                    <option value="multi" <?php echo e(old('license_type', $license->license_type) == 'multi' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Multi Site')); ?>

                                    </option>
                                    <option value="developer" <?php echo e(old('license_type', $license->license_type) == 'developer' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Developer')); ?>

                                    </option>
                                    <option value="extended" <?php echo e(old('license_type', $license->license_type) == 'extended' ? 'selected' : ''); ?>>
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
                                    <option value="active" <?php echo e(old('status', $license->status) == 'active' ? 'selected' : ''); ?>>
                                <?php echo e(trans('app.Active')); ?>

                            </option>
                                    <option value="inactive" <?php echo e(old('status', $license->status) == 'inactive' ? 'selected' : ''); ?>>
                                <?php echo e(trans('app.Inactive')); ?>

                            </option>
                                    <option value="suspended" <?php echo e(old('status', $license->status) == 'suspended' ? 'selected' : ''); ?>>
                                <?php echo e(trans('app.Suspended')); ?>

                            </option>
                                    <option value="expired" <?php echo e(old('status', $license->status) == 'expired' ? 'selected' : ''); ?>>
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
                                <label for="expires_at" class="form-label">
                                    <i class="fas fa-calendar text-danger me-1"></i>
                                    <?php echo e(trans('app.Expires At')); ?>

                                </label>
                                <input type="datetime-local" class="form-control <?php $__errorArgs = ['expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="expires_at" name="expires_at" 
                                       value="<?php echo e(old('expires_at', $license->expires_at ? $license->expires_at->format('Y-m-d\TH:i') : '')); ?>">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Leave empty for lifetime license')); ?>

                                </div>
                        <?php $__errorArgs = ['expires_at'];
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

                                </label>
                                <input type="number" class="form-control <?php $__errorArgs = ['max_domains'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="max_domains" name="max_domains" value="<?php echo e(old('max_domains', $license->max_domains)); ?>" 
                                       min="1" placeholder="<?php echo e(trans('app.Maximum allowed domains')); ?>">
                                <?php if($license->hasReachedDomainLimit()): ?>
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        <?php echo e(trans('app.Warning: This license has reached its domain limit')); ?>

                                    </div>
                                <?php endif; ?>
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
                        placeholder="<?php echo e(trans('app.Enter any additional notes')); ?>"><?php echo e(old('notes', $license->notes)); ?></textarea>
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
                                <label for="purchase_code" class="form-label">
                                    <i class="fas fa-shopping-cart text-primary me-1"></i>
                                    <?php echo e(trans('app.Purchase Code')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['purchase_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="purchase_code" name="purchase_code" value="<?php echo e(old('purchase_code', $license->purchase_code)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter purchase code')); ?>">
                                <?php $__errorArgs = ['purchase_code'];
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
                                       id="order_number" name="order_number" value="<?php echo e(old('order_number', $license->order_number)); ?>" 
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
                                <label for="license_key" class="form-label">
                                    <i class="fas fa-key text-warning me-1"></i>
                                    <?php echo e(trans('app.License Key')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['license_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="license_key" name="license_key" value="<?php echo e(old('license_key', $license->license_key)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter license key')); ?>">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Unique license key')); ?>

                                </div>
                                <?php $__errorArgs = ['license_key'];
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

                                </label>
                                <input type="datetime-local" class="form-control <?php $__errorArgs = ['support_expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="support_expires_at" name="support_expires_at" 
                                       value="<?php echo e(old('support_expires_at', $license->support_expires_at ? $license->support_expires_at->format('Y-m-d\TH:i') : '')); ?>">
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
                                <h5 id="preview-product"><?php echo e($license->product->name ?? trans('app.Product Name')); ?></h5>
                                <p id="preview-user" class="text-muted small mb-0"><?php echo e($license->user->name ?? trans('app.User Name')); ?></p>
                                <span id="preview-status" class="badge bg-<?php echo e($license->status == 'active' ? 'success' : 'danger'); ?> mt-2">
                                    <?php echo e(trans('app.' . ucfirst($license->status))); ?>

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
                            <div class="col-4 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-primary"><?php echo e($license->active_domains_count); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Used Domains')); ?></p>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success"><?php echo e($license->max_domains ?? 1); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Max Domains')); ?></p>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-info"><?php echo e($license->remaining_domains); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Remaining')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-info"><?php echo e($license->created_at->format('M Y')); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Created')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-warning"><?php echo e($license->updated_at->format('M Y')); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Updated')); ?></p>
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
                            <p class="text-muted small" id="preview-license-key"><?php echo e($license->license_key); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-success me-1"></i>
                                <?php echo e(trans('app.Created At')); ?>

                            </label>
                            <p class="text-muted small"><?php echo e($license->created_at->format('M d, Y H:i')); ?></p>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">
                                <i class="fas fa-globe text-info me-1"></i>
                                <?php echo e(trans('app.Max Domains')); ?>

                            </label>
                            <p class="text-muted small" id="preview-domains"><?php echo e($license->max_domains ?? 1); ?></p>
                </div>
        </div>
    </div>

                <!-- License Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            <?php echo e(trans('app.License Actions')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary copy-btn" data-text="<?php echo e($license->license_key); ?>">
                                <i class="fas fa-copy me-1"></i>
                                <?php echo e(trans('app.Copy License Key')); ?>

                            </button>
                            <a href="<?php echo e(route('admin.licenses.show', $license)); ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo e(trans('app.View License')); ?>

                            </a>
                            <?php if($license->user): ?>
                            <a href="<?php echo e(route('admin.users.show', $license->user)); ?>" class="btn btn-outline-success">
                                <i class="fas fa-user me-1"></i>
                                <?php echo e(trans('app.View User')); ?>

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
                            <a href="<?php echo e(route('admin.licenses.index')); ?>" class="btn btn-outline-secondary">
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
                    <p class="text-muted mb-3"><?php echo e(trans('app.Delete License Warning')); ?></p>
                    <form method="post" action="<?php echo e(route('admin.licenses.destroy', $license)); ?>" 
                          data-confirm="delete-license">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i><?php echo e(trans('app.Delete License')); ?>

                </button>
            </form>
        </div>
    </div>
</div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\licenses\edit.blade.php ENDPATH**/ ?>