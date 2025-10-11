<?php $__env->startSection('title', trans('app.Product Updates')); ?>

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
                                <i class="fas fa-sync-alt text-success me-2"></i>
                                <?php echo e(trans('app.Product Updates')); ?>

                            </h1>
                            <?php if($product): ?>
                                <p class="text-muted mb-0"><?php echo e($product->name); ?></p>
                            <?php else: ?>
                                <p class="text-muted mb-0"><?php echo e(trans('app.All Products')); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if($product): ?>
                                <a href="<?php echo e(route('admin.product-updates.create', ['product_id' => $product->id])); ?>" class="btn btn-success">
                                    <i class="fas fa-plus me-1"></i>
                                    <?php echo e(trans('app.Add Update')); ?>

                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route('admin.product-updates.create')); ?>" class="btn btn-success">
                                    <i class="fas fa-plus me-1"></i>
                                    <?php echo e(trans('app.Add Update')); ?>

                                </a>
                            <?php endif; ?>
                            <?php if($product): ?>
                                <a href="<?php echo e(route('admin.products.show', $product)); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    <?php echo e(trans('app.Back to Product')); ?>

                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    <?php echo e(trans('app.Back to Products')); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-sync-alt me-3 text-success"></i>
                            <div>
                                <h5 class="card-title mb-0"><?php echo e(trans('app.All Updates')); ?></h5>
                                <small class="text-muted"><?php echo e(trans('app.Manage product updates')); ?></small>
                            </div>
                        </div>
                        <?php if($product): ?>
                            <div>
                                <span class="badge bg-info fs-6"><?php echo e($product->updates->count()); ?> <?php echo e(trans('app.Updates')); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body p-0">
                    <?php if($product && $product->updates->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center"><?php echo e(trans('app.Version')); ?></th>
                                    <th><?php echo e(trans('app.Title')); ?></th>
                                    <th class="text-center"><?php echo e(trans('app.Type')); ?></th>
                                    <th class="text-center"><?php echo e(trans('app.File Size')); ?></th>
                                    <th class="text-center"><?php echo e(trans('app.Released')); ?></th>
                                    <th class="text-center"><?php echo e(trans('app.Status')); ?></th>
                                    <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $product->updates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $update): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo e($update->version); ?></span>
                                        <?php if($update->is_major): ?>
                                            <span class="badge bg-warning ms-1"><?php echo e(trans('app.Major')); ?></span>
                                        <?php endif; ?>
                                        <?php if($update->is_required): ?>
                                            <span class="badge bg-danger ms-1"><?php echo e(trans('app.Required')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo e($update->title); ?></div>
                                        <?php if($update->description): ?>
                                        <small class="text-muted"><?php echo e(Str::limit($update->description, 50)); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($update->is_major): ?>
                                            <span class="text-warning"><?php echo e(trans('app.Major Update')); ?></span>
                                        <?php else: ?>
                                            <span class="text-info"><?php echo e(trans('app.Minor Update')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo e($update->formatted_file_size ?? 'N/A'); ?>

                                    </td>
                                    <td class="text-center">
                                        <?php echo e($update->released_at?->format('Y-m-d H:i') ?? 'N/A'); ?>

                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?php echo e($update->is_active ? 'success' : 'secondary'); ?>">
                                            <?php echo e($update->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo e(route('admin.product-updates.show', $update)); ?>" class="btn btn-outline-info" title="<?php echo e(trans('app.View')); ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('admin.product-updates.edit', $update)); ?>" class="btn btn-outline-primary" title="<?php echo e(trans('app.Edit')); ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-<?php echo e($update->is_active ? 'warning' : 'success'); ?>" 
                                                    onclick="toggleUpdateStatus(<?php echo e((int)$update->id); ?>)" 
                                                    title="<?php echo e($update->is_active ? trans('app.Deactivate') : trans('app.Activate')); ?>">
                                                <i class="fas fa-<?php echo e($update->is_active ? 'pause' : 'play'); ?>"></i>
                                            </button>
                                            <form method="POST" action="<?php echo e(route('admin.product-updates.destroy', $update)); ?>" 
                                                  class="inline-form" 
                                                  onsubmit="return confirm('<?php echo e(trans('app.Are you sure you want to delete this update?')); ?>')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-outline-danger" title="<?php echo e(trans('app.Delete')); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-sync-alt text-muted empty-state-icon"></i>
                        </div>
                        <h4 class="text-muted"><?php echo e(trans('app.No Updates Found')); ?></h4>
                        <p class="text-muted mb-4"><?php echo e(trans('app.Get started by adding your first update')); ?></p>
                        <?php if($product): ?>
                            <a href="<?php echo e(route('admin.product-updates.create', ['product_id' => $product->id])); ?>" class="btn btn-success btn-lg">
                        <?php else: ?>
                            <a href="<?php echo e(route('admin.product-updates.create')); ?>" class="btn btn-success btn-lg">
                        <?php endif; ?>
                            <i class="fas fa-plus me-2"></i>
                            <?php echo e(trans('app.Add Your First Update')); ?>

                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\product-updates\index.blade.php ENDPATH**/ ?>