<?php $__env->startSection('admin-content'); ?>
<div class="admin-page-header modern-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1 class="gradient-text"><?php echo e(trans('app.ticket_categories')); ?></h1>
            <p class="admin-page-subtitle"><?php echo e(trans('app.manage_ticket_categories')); ?></p>
        </div>
        <div class="admin-page-actions">
            <a href="<?php echo e(route('admin.ticket-categories.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                <i class="fas fa-plus me-2"></i>
                <?php echo e(trans('app.add_category')); ?>

            </a>
        </div>
    </div>
</div>

<div class="admin-section">
    <!-- Filters Section -->
    <div class="admin-section-header">
        <h2><i class="fas fa-tag me-2"></i><?php echo e(trans('app.all_categories')); ?></h2>
        <div class="admin-section-actions">
            <div class="admin-search-box">
                <input type="text" class="admin-form-input" id="searchCategories" 
                       placeholder="<?php echo e(trans('app.search_categories')); ?>">
                <i class="fas fa-search admin-search-icon"></i>
            </div>
        </div>
    </div>

    <?php if($categories->count() > 0): ?>
        <div class="admin-section-content">
            <div class="table-responsive">
                <table class="table table-hover mb-0 ticket-categories-table">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center"><?php echo e(trans('app.Color')); ?></th>
                            <th><?php echo e(trans('app.Name')); ?></th>
                            <th><?php echo e(trans('app.Slug')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.Order')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.Status')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.requires_login')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.requires_purchase_code')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="category-row" data-name="<?php echo e(strtolower($category->name)); ?>" data-status="<?php echo e($category->is_active ? 'active' : 'inactive'); ?>">
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="rounded shadow-sm d-flex align-items-center justify-content-center category-color-avatar" data-color="<?php echo e($category->color); ?>">
                                        <span class="text-white fw-bold fs-5"><?php echo e(substr($category->name, 0, 1)); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo e($category->name); ?></div>
                                <small class="text-muted"><?php echo e($category->slug); ?></small>
                            </td>
                            <td>
                                <code class="text-muted small"><?php echo e($category->slug); ?></code>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark fs-6 px-2 py-1"><?php echo e($category->sort_order ?? '—'); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo e($category->is_active ? 'bg-success' : 'bg-secondary'); ?> fs-6 px-2 py-1">
                                    <i class="fas <?php echo e($category->is_active ? 'fa-check-circle' : 'fa-times-circle'); ?> me-1"></i>
                                    <?php echo e($category->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo e($category->requires_login ? 'bg-info' : 'bg-secondary'); ?> fs-6 px-2 py-1">
                                    <i class="fas <?php echo e($category->requires_login ? 'fa-lock' : 'fa-unlock'); ?> me-1"></i>
                                    <?php echo e($category->requires_login ? trans('app.Yes') : trans('app.No')); ?>

                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo e($category->requires_valid_purchase_code ? 'bg-warning' : 'bg-secondary'); ?> fs-6 px-2 py-1">
                                    <i class="fas fa-key me-1"></i>
                                    <?php echo e($category->requires_valid_purchase_code ? trans('app.Yes') : trans('app.No')); ?>

                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group-vertical btn-group-sm" role="group">
                                    <a href="<?php echo e(route('admin.ticket-categories.edit', $category)); ?>"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit me-1"></i>
                                        <?php echo e(trans('app.Edit')); ?>

                                    </a>

                                    <form action="<?php echo e(route('admin.ticket-categories.destroy', $category)); ?>" method="POST"
                                          class="d-inline delete-category-form" data-confirm="<?php echo e(trans('app.Are you sure?')); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                            <i class="fas fa-trash me-1"></i>
                                            <?php echo e(trans('app.Delete')); ?>

                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                <?php echo e($categories->links()); ?>

            </div>
        </div>
    <?php else: ?>
        <div class="admin-section-content">
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-ticket-alt text-muted empty-state-icon"></i>
                </div>
                <h4 class="text-muted"><?php echo e(trans('app.No Categories Found')); ?></h4>
                <p class="text-muted mb-4"><?php echo e(trans('app.Create your first ticket category to get started')); ?></p>
                <a href="<?php echo e(route('admin.ticket-categories.create')); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>
                    <?php echo e(trans('app.create_ticket_category')); ?>

                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\ticket-categories\index.blade.php ENDPATH**/ ?>