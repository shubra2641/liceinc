<?php $__env->startSection('admin-content'); ?>
<!-- Admin Product Categories Page -->
<div class="admin-product-categories-page">
    <div class="admin-page-header modern-header">
        <div class="admin-page-header-content">
            <div class="admin-page-title">
                <h1 class="gradient-text"><?php echo e(trans('app.product_categories')); ?></h1>
                <p class="admin-page-subtitle"><?php echo e(trans('app.manage_product_categories')); ?></p>
            </div>
            <div class="admin-page-actions">
                <a href="<?php echo e(route('admin.product-categories.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-plus me-2"></i>
                    <?php echo e(trans('app.new_category')); ?>

                </a>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-filter me-2"></i><?php echo e(trans('app.Filters')); ?></h2>
            <div class="admin-section-actions">
                <div class="admin-search-box">
                    <input type="text" class="admin-form-input" id="search" 
                           placeholder="<?php echo e(trans('app.search_categories')); ?>">
                    <i class="fas fa-search admin-search-icon"></i>
                </div>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="status">
                            <i class="fas fa-toggle-on me-1"></i><?php echo e(trans('app.Status')); ?>

                        </label>
                        <select id="status" class="admin-form-input">
                            <option value=""><?php echo e(trans('app.All Status')); ?></option>
                            <option value="active"><?php echo e(trans('app.Active')); ?></option>
                            <option value="inactive"><?php echo e(trans('app.Inactive')); ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="sort">
                            <i class="fas fa-sort me-1"></i><?php echo e(trans('app.Sort By')); ?>

                        </label>
                        <select id="sort" class="admin-form-input">
                            <option value="name"><?php echo e(trans('app.Name')); ?></option>
                            <option value="products"><?php echo e(trans('app.Products Count')); ?></option>
                            <option value="sort_order"><?php echo e(trans('app.sort_order')); ?></option>
                            <option value="created_at"><?php echo e(trans('app.Created Date')); ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label">&nbsp;</label>
                        <button type="button" class="admin-btn admin-btn-secondary admin-btn-m w-100" id="reset-filters-btn">
                            <i class="fas fa-refresh me-2"></i>
                            <?php echo e(trans('app.Reset')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Categories Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-folder me-2"></i><?php echo e(trans('app.all_categories')); ?></h2>
            <span class="admin-badge admin-badge-info"><?php echo e($categories->count()); ?> <?php echo e(trans('app.Categories')); ?></span>
        </div>
        <div class="admin-section-content">
            <?php if($categories->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 product-categories-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center"><?php echo e(trans('app.Image')); ?></th>
                                    <th><?php echo e(trans('app.Name')); ?></th>
                                    <th><?php echo e(trans('app.Slug')); ?></th>
                                    <th class="text-center"><?php echo e(trans('app.Products')); ?></th>
                                    <th class="text-center"><?php echo e(trans('app.Status')); ?></th>
                                    <th class="text-center"><?php echo e(trans('app.sort_order')); ?></th>
                                    <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="category-row" data-name="<?php echo e(strtolower($category->name)); ?>" data-status="<?php echo e($category->is_active ? 'active' : 'inactive'); ?>">
                                    <td class="text-center">
                                        <?php if($category->image): ?>
                                        <img src="<?php echo e(asset('storage/' . $category->image)); ?>" alt="<?php echo e($category->name); ?>"
                                            class="rounded category-image">
                                        <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center category-avatar">
                                            <span class="text-muted small fw-bold"><?php echo e(substr($category->name, 0, 1)); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo e($category->name); ?></div>
                                        <small class="text-muted"><?php echo e($category->slug); ?></small>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo e($category->slug); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?php echo e($category->products->count()); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            <span class="badge <?php echo e($category->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                                                <?php echo e($category->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                                            </span>
                                            <?php if($category->is_featured): ?>
                                            <span class="badge bg-warning text-dark"><?php echo e(trans('app.Featured')); ?></span>
                                            <?php endif; ?>
                                            <?php if($category->show_in_menu): ?>
                                            <span class="badge bg-info"><?php echo e(trans('app.In Menu')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-muted"><?php echo e($category->sort_order ?? '—'); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <a href="<?php echo e(route('admin.product-categories.edit', $category)); ?>"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit me-1"></i>
                                                <?php echo e(trans('app.Edit')); ?>

                                            </a>

                                            <form action="<?php echo e(route('admin.product-categories.destroy', $category)); ?>" method="POST"
                                                  class="d-inline" data-confirm="delete-category">
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
            <?php else: ?>
            <!-- Enhanced Empty State -->
            <div class="admin-empty-state product-categories-empty-state">
                <div class="admin-empty-state-content">
                    <div class="admin-empty-state-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="admin-empty-state-text">
                        <h3 class="admin-empty-state-title"><?php echo e(trans('app.No Categories Found')); ?></h3>
                        <p class="admin-empty-state-description">
                            <?php echo e(trans('app.Create your first product category to get started')); ?>

                        </p>
                    </div>
                    <div class="admin-empty-state-actions">
                        <a href="<?php echo e(route('admin.product-categories.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                            <i class="fas fa-plus me-2"></i>
                            <?php echo e(trans('app.Create Category')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-btn admin-btn-secondary admin-btn-m">
                            <i class="fas fa-arrow-left me-2"></i>
                            <?php echo e(trans('app.Back to Dashboard')); ?>

                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if($categories->hasPages()): ?>
    <div class="d-flex justify-content-center mt-4">
        <?php echo e($categories->links()); ?>

    </div>
    <?php endif; ?>
</div>

<!-- JavaScript is now handled by admin-categories.js -->
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\product-categories\index.blade.php ENDPATH**/ ?>