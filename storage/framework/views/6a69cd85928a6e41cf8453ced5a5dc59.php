<?php $__env->startSection('title', trans('app.Create Product Update')); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-plus text-success me-2"></i>
                                <?php echo e(trans('app.Create Product Update')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($product->name); ?></p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo e(route('admin.products.show', $product)); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Product')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sync-alt me-2"></i>
                        <?php echo e(trans('app.Update Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.product-updates.store')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="version" class="form-label fw-bold">
                                    <i class="fas fa-tag text-primary me-1"></i>
                                    <?php echo e(trans('app.Version')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['version'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="version" name="version" value="<?php echo e(old('version')); ?>" 
                                       placeholder="e.g., 1.0.1" required>
                                <?php $__errorArgs = ['version'];
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
                                <label for="title" class="form-label fw-bold">
                                    <i class="fas fa-heading text-info me-1"></i>
                                    <?php echo e(trans('app.Title')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="title" name="title" value="<?php echo e(old('title')); ?>" 
                                       placeholder="e.g., Bug Fixes and Improvements" required>
                                <?php $__errorArgs = ['title'];
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
                            <label for="description" class="form-label fw-bold">
                                <i class="fas fa-align-left text-secondary me-1"></i>
                                <?php echo e(trans('app.Description')); ?>

                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Brief description of the update"><?php echo e(old('description')); ?></textarea>
                            <?php $__errorArgs = ['description'];
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

                        <div class="mb-3">
                            <label for="changelog" class="form-label fw-bold">
                                <i class="fas fa-list-check text-success me-1"></i>
                                <?php echo e(trans('app.Changelog')); ?>

                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['changelog'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="changelog" name="changelog" rows="8" 
                                      placeholder="Enter changelog items, one per line..."><?php echo e(old('changelog')); ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                <?php echo e(trans('app.Enter each changelog item on a new line')); ?>

                            </div>
                            <?php $__errorArgs = ['changelog'];
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

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_major" name="is_major" value="1" <?php echo e(old('is_major') ? 'checked' : ''); ?>>
                                    <label class="form-check-label fw-bold" for="is_major">
                                        <i class="fas fa-star text-warning me-1"></i>
                                        <?php echo e(trans('app.Major Update')); ?>

                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" <?php echo e(old('is_required') ? 'checked' : ''); ?>>
                                    <label class="form-check-label fw-bold" for="is_required">
                                        <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                                        <?php echo e(trans('app.Required Update')); ?>

                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                                    <label class="form-check-label fw-bold" for="is_active">
                                        <i class="fas fa-toggle-on text-success me-1"></i>
                                        <?php echo e(trans('app.Active')); ?>

                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="released_at" class="form-label fw-bold">
                                <i class="fas fa-calendar text-primary me-1"></i>
                                <?php echo e(trans('app.Release Date')); ?>

                            </label>
                            <input type="datetime-local" class="form-control <?php $__errorArgs = ['released_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="released_at" name="released_at" value="<?php echo e(old('released_at', now()->format('Y-m-d\TH:i'))); ?>">
                            <?php $__errorArgs = ['released_at'];
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

                        <div class="mb-3">
                            <label for="update_file" class="form-label fw-bold">
                                <i class="fas fa-file-archive text-info me-1"></i>
                                <?php echo e(trans('app.Update Package')); ?> (ZIP)
                            </label>
                            <input type="file" class="form-control <?php $__errorArgs = ['update_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="update_file" name="update_file" accept=".zip">
                            <div class="form-text"><?php echo e(trans('app.Upload a ZIP file containing the update package')); ?></div>
                            <?php $__errorArgs = ['update_file'];
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>
                                <?php echo e(trans('app.Create Update')); ?>

                            </button>
                            <a href="<?php echo e(route('admin.products.show', $product)); ?>" class="btn btn-outline-secondary">
                                <?php echo e(trans('app.Cancel')); ?>

                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e(trans('app.Product Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?php echo e(trans('app.Product Name')); ?></label>
                        <p class="form-control-plaintext"><?php echo e($product->name); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?php echo e(trans('app.Current Version')); ?></label>
                        <p class="form-control-plaintext"><?php echo e($product->current_version); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?php echo e(trans('app.Category')); ?></label>
                        <p class="form-control-plaintext"><?php echo e($product->category?->name ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\my-logos\resources\views/admin/product-updates/create.blade.php ENDPATH**/ ?>