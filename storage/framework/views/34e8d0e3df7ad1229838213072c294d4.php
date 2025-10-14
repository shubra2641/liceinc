

<?php $__env->startSection('title', trans('app.Products')); ?>

<?php $__env->startSection('admin-content'); ?>
<!-- Admin Products Page -->
<div class="admin-products-page">
    <div class="admin-page-header modern-header">
        <div class="admin-page-header-content">
            <div class="admin-page-title">
                <h1 class="gradient-text"><?php echo e(trans('app.Products')); ?></h1>
                <p class="admin-page-subtitle"><?php echo e(trans('app.Manage your products catalog')); ?></p>
            </div>
            <div class="admin-page-actions">
                <a href="<?php echo e(route('admin.products.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-plus me-2"></i>
                    <?php echo e(trans('app.Add Product')); ?>

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
                    <input type="text" class="admin-form-input" id="search" name="q" value="<?php echo e(request('q')); ?>" 
                           placeholder="<?php echo e(trans('app.Search products')); ?>">
                    <i class="fas fa-search admin-search-icon"></i>
                </div>
            </div>
        </div>
        <div class="admin-section-content">
            <form action="<?php echo e(route('admin.products.index')); ?>" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label class="admin-form-label" for="category_id">
                                <i class="fas fa-folder me-1"></i><?php echo e(trans('app.Category')); ?>

                            </label>
                            <select id="category_id" name="category_id" class="admin-form-input">
                                <option value=""><?php echo e(trans('app.All Categories')); ?></option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php if(request('category_id')==$category->id): echo 'selected'; endif; ?>>
                                    <?php echo e($category->name); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label class="admin-form-label" for="status-filter">
                                <i class="fas fa-toggle-on me-1"></i><?php echo e(trans('app.Status')); ?>

                            </label>
                            <select id="status-filter" class="admin-form-input">
                                <option value=""><?php echo e(trans('app.All Status')); ?></option>
                                <option value="active"><?php echo e(trans('app.Active')); ?></option>
                                <option value="inactive"><?php echo e(trans('app.Inactive')); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-cube me-2"></i><?php echo e(trans('app.All Products')); ?></h2>
            <span class="admin-badge admin-badge-info"><?php echo e($products->total()); ?> <?php echo e(trans('app.Products')); ?></span>
        </div>
        <div class="admin-section-content">
            <?php if($products->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 products-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center"><?php echo e(trans('app.Image')); ?></th>
                                <th><?php echo e(trans('app.Name')); ?></th>
                                <th><?php echo e(trans('app.Category')); ?></th>
                                <th><?php echo e(trans('app.Language')); ?></th>
                                <th class="text-end"><?php echo e(trans('app.Price')); ?></th>
                                <th class="text-center"><?php echo e(trans('app.Stock')); ?></th>
                                <th class="text-center"><?php echo e(trans('app.Status')); ?></th>
                                <th class="text-center"><?php echo e(trans('app.Flags')); ?></th>
                                <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="product-row" data-name="<?php echo e(strtolower($product->name)); ?>" data-category="<?php echo e($product->category_id ?? ''); ?>" data-status="<?php echo e($product->is_active ? 'active' : 'inactive'); ?>">
                                <td class="text-center">
                                    <?php if($product->image): ?>
                                    <img src="<?php echo e(asset('storage/' . $product->image)); ?>" alt="<?php echo e($product->name); ?>"
                                        class="rounded product-image">
                                    <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center product-avatar">
                                        <span class="text-muted small fw-bold"><?php echo e(substr($product->name, 0, 1)); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <a href="<?php echo e(route('admin.products.show', $product)); ?>" class="text-decoration-none">
                                            <?php echo e($product->name); ?>

                                        </a>
                                    </div>
                                    <small class="text-muted"><?php echo e($product->slug); ?></small>
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo e(optional($product->category)->name ?? '—'); ?></span>
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo e(optional($product->programmingLanguage)->name ?? '—'); ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="fw-semibold"><?php echo e($product->formatted_price); ?></div>
                                    <?php if($product->tax_rate): ?>
                                    <small class="text-muted"><?php echo e(trans('app.Tax')); ?>:
                                        <?php echo e(rtrim(rtrim(number_format($product->tax_rate, 2, '.', ''), '0'), '.')); ?>%</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo e($product->isInStock() ? 'bg-success' : 'bg-danger'); ?>">
                                        <?php echo e($product->stock_status); ?>

                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo e($product->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                                        <?php echo e($product->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        <?php if($product->is_featured): ?>
                                        <span class="badge bg-warning text-dark"><?php echo e(trans('app.Featured')); ?></span>
                                        <?php endif; ?>
                                        <?php if($product->is_popular): ?>
                                        <span class="badge bg-info"><?php echo e(trans('app.Popular')); ?></span>
                                        <?php endif; ?>
                                        <?php if($product->requires_domain): ?>
                                        <span class="badge bg-secondary"><?php echo e(trans('app.Requires Domain')); ?></span>
                                        <?php endif; ?>
                                        <?php if($product->is_downloadable): ?>
                                        <span class="badge bg-success"><?php echo e(trans('app.Downloadable')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        <a href="<?php echo e(route('admin.products.show', $product)); ?>" 
                                           class="btn btn-info btn-sm" title="View Product Details">
                                            <i class="fas fa-eye"></i>
                                            <span class="ms-1">View</span>
                                        </a>
                                        
                                        <a href="<?php echo e(route('admin.products.edit', $product)); ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit me-1"></i>
                                            <?php echo e(trans('app.Edit')); ?>

                                        </a>
                                        
                                        <a href="<?php echo e(route('admin.products.logs', $product)); ?>" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-list me-1"></i>
                                            <?php echo e(trans('app.Logs')); ?>

                                        </a>
                                        
                                        <a href="<?php echo e(route('admin.products.files.index', $product)); ?>" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-file-upload me-1"></i>
                                            <?php echo e(trans('app.Files')); ?>

                                        </a>

                                        <?php if($product->integration_file_path): ?>
                                        <a href="<?php echo e(route('admin.products.download-integration', $product)); ?>" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-download me-1"></i>
                                            <?php echo e(trans('app.Download')); ?>

                                        </a>
                                        <?php endif; ?>

                                        <form action="<?php echo e(route('admin.products.regenerate-integration', $product)); ?>" 
                                              method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                                                <i class="fas fa-sync me-1"></i>
                                                <?php echo e(trans('app.Regenerate')); ?>

                                            </button>
                                        </form>

                                        <form action="<?php echo e(route('admin.products.destroy', $product)); ?>" method="POST" 
                                              class="d-inline" data-confirm="delete-product">
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

            <?php if($products->hasPages()): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($products->links()); ?>

            </div>
            <?php endif; ?>
            <?php else: ?>
            <!-- Enhanced Empty State -->
            <div class="admin-empty-state products-empty-state">
                <div class="admin-empty-state-content">
                    <div class="admin-empty-state-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="admin-empty-state-text">
                        <h3 class="admin-empty-state-title"><?php echo e(trans('app.No Products Found')); ?></h3>
                        <p class="admin-empty-state-description">
                            <?php echo e(trans('app.Get started by adding your first product')); ?>

                        </p>
                    </div>
                    <div class="admin-empty-state-actions">
                        <a href="<?php echo e(route('admin.products.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                            <i class="fas fa-plus me-2"></i>
                            <?php echo e(trans('app.Add Your First Product')); ?>

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
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/admin/products/index.blade.php ENDPATH**/ ?>