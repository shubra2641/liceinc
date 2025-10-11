<?php $__env->startSection('title', trans('app.View Product Update')); ?>

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
                                <i class="fas fa-eye text-info me-2"></i>
                                <?php echo e(trans('app.View Product Update')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($productUpdate->title); ?> - <?php echo e($productUpdate->version); ?></p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo e(route('admin.product-updates.edit', $productUpdate)); ?>" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                <?php echo e(trans('app.Edit Update')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.product-updates.index', ['product_id' => $productUpdate->product_id])); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Updates')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Update Information -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e(trans('app.Update Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tag text-primary me-1"></i>
                                <?php echo e(trans('app.Version')); ?>

                            </label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-primary fs-6"><?php echo e($productUpdate->version); ?></span>
                                <?php if($productUpdate->is_major): ?>
                                    <span class="badge bg-warning ms-2"><?php echo e(trans('app.Major')); ?></span>
                                <?php endif; ?>
                                <?php if($productUpdate->is_required): ?>
                                    <span class="badge bg-danger ms-2"><?php echo e(trans('app.Required')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-heading text-info me-1"></i>
                                <?php echo e(trans('app.Title')); ?>

                            </label>
                            <div class="form-control-plaintext"><?php echo e($productUpdate->title); ?></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-align-left text-secondary me-1"></i>
                            <?php echo e(trans('app.Description')); ?>

                        </label>
                        <div class="form-control-plaintext">
                            <?php echo e($productUpdate->description ?: trans('app.No description provided')); ?>

                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-list-check text-success me-1"></i>
                            <?php echo e(trans('app.Changelog')); ?>

                        </label>
                        <div class="form-control-plaintext">
                            <?php if($productUpdate->changelog && is_array($productUpdate->changelog) && count($productUpdate->changelog) > 0): ?>
                                <ul class="list-unstyled mb-0">
                                    <?php $__currentLoopData = $productUpdate->changelog; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <?php echo e($item); ?>

                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.No changelog provided')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-star text-warning me-1"></i>
                                <?php echo e(trans('app.Major Update')); ?>

                            </label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-<?php echo e($productUpdate->is_major ? 'warning' : 'secondary'); ?>">
                                    <?php echo e($productUpdate->is_major ? trans('app.Yes') : trans('app.No')); ?>

                                </span>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                                <?php echo e(trans('app.Required Update')); ?>

                            </label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-<?php echo e($productUpdate->is_required ? 'danger' : 'secondary'); ?>">
                                    <?php echo e($productUpdate->is_required ? trans('app.Yes') : trans('app.No')); ?>

                                </span>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-toggle-on text-success me-1"></i>
                                <?php echo e(trans('app.Status')); ?>

                            </label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-<?php echo e($productUpdate->is_active ? 'success' : 'secondary'); ?>">
                                    <?php echo e($productUpdate->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar text-primary me-1"></i>
                            <?php echo e(trans('app.Release Date')); ?>

                        </label>
                        <div class="form-control-plaintext">
                            <?php echo e($productUpdate->released_at?->format('Y-m-d H:i:s') ?? trans('app.Not set')); ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- File Information -->
            <?php if($productUpdate->file_path): ?>
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-archive me-2"></i>
                        <?php echo e(trans('app.Update Package')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-file me-1"></i>
                                <?php echo e(trans('app.File Name')); ?>

                            </label>
                            <div class="form-control-plaintext"><?php echo e($productUpdate->file_name ?? 'N/A'); ?></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-weight-hanging me-1"></i>
                                <?php echo e(trans('app.File Size')); ?>

                            </label>
                            <div class="form-control-plaintext"><?php echo e($productUpdate->formatted_file_size ?? 'N/A'); ?></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-fingerprint me-1"></i>
                            <?php echo e(trans('app.File Hash')); ?>

                        </label>
                        <div class="form-control-plaintext">
                            <code><?php echo e($productUpdate->file_hash ?? 'N/A'); ?></code>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?php echo e(route('admin.product-updates.download', $productUpdate)); ?>" class="btn btn-success">
                            <i class="fas fa-download me-1"></i>
                            <?php echo e(trans('app.Download Package')); ?>

                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- Product Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cube me-2"></i>
                        <?php echo e(trans('app.Product Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?php echo e(trans('app.Product Name')); ?></label>
                        <p class="form-control-plaintext"><?php echo e($productUpdate->product->name); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?php echo e(trans('app.Category')); ?></label>
                        <p class="form-control-plaintext"><?php echo e($productUpdate->product->category?->name ?? 'N/A'); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?php echo e(trans('app.Current Version')); ?></label>
                        <p class="form-control-plaintext"><?php echo e($productUpdate->product->current_version); ?></p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        <?php echo e(trans('app.Quick Actions')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('admin.product-updates.edit', $productUpdate)); ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            <?php echo e(trans('app.Edit Update')); ?>

                        </a>
                        
                        <button type="button" class="btn btn-<?php echo e($productUpdate->is_active ? 'warning' : 'success'); ?>" 
                                onclick="toggleUpdateStatus(<?php echo e((int)$productUpdate->id); ?>)">
                            <i class="fas fa-<?php echo e($productUpdate->is_active ? 'pause' : 'play'); ?> me-1"></i>
                            <?php echo e($productUpdate->is_active ? trans('app.Deactivate') : trans('app.Activate')); ?>

                        </button>

                        <?php if($productUpdate->file_path): ?>
                        <a href="<?php echo e(route('admin.product-updates.download', $productUpdate)); ?>" class="btn btn-info">
                            <i class="fas fa-download me-1"></i>
                            <?php echo e(trans('app.Download Package')); ?>

                        </a>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('admin.product-updates.destroy', $productUpdate)); ?>" 
                              onsubmit="return confirm('<?php echo e(trans('app.Are you sure you want to delete this update?')); ?>')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-1"></i>
                                <?php echo e(trans('app.Delete Update')); ?>

                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\product-updates\show.blade.php ENDPATH**/ ?>