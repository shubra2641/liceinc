

<?php $__env->startSection('admin-content'); ?>
<!-- Admin KB Categories Page -->
<div class="admin-kb-categories-page">
    <div class="admin-page-header modern-header">
        <div class="admin-page-header-content">
            <div class="admin-page-title">
                <h1 class="gradient-text"><?php echo e(trans('app.kb_categories_management')); ?></h1>
                <p class="admin-page-subtitle"><?php echo e(trans('app.organize_kb_categories')); ?></p>
            </div>
            <div class="admin-page-actions">
                <a href="<?php echo e(route('admin.kb-categories.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
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
                    <input type="text" class="admin-form-input" id="searchCategories" 
                           placeholder="<?php echo e(trans('app.search_categories')); ?>">
                    <i class="fas fa-search admin-search-icon"></i>
                </div>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="protection-filter">
                            <i class="fas fa-shield-alt me-1"></i><?php echo e(trans('app.Protection')); ?>

                        </label>
                        <select id="protection-filter" class="admin-form-input">
                            <option value=""><?php echo e(trans('app.All Protection Levels')); ?></option>
                            <option value="protected"><?php echo e(trans('app.serial_protected')); ?></option>
                            <option value="public"><?php echo e(trans('app.public')); ?></option>
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
        </div>
    </div>

    <!-- Enhanced Statistics Section -->
    <div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
        <!-- Total Categories Stats Card -->
        <div class="stats-card stats-card-primary animate-slide-up">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon products"></div>
                    <div class="stats-card-menu">
                        <button class="stats-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value"><?php echo e($categories->total()); ?></div>
                    <div class="stats-card-label"><?php echo e(trans('app.Total Categories')); ?></div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span><?php echo e($categories->where('is_active', true)->count()); ?> <?php echo e(trans('app.active')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Articles Stats Card -->
        <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon articles"></div>
                    <div class="stats-card-menu">
                        <button class="stats-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value"><?php echo e($categories->sum('articles_count') ?? 0); ?></div>
                    <div class="stats-card-label"><?php echo e(trans('app.Total Articles')); ?></div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span><?php echo e(trans('app.across_all_categories')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Protected Categories Stats Card -->
        <div class="stats-card stats-card-warning animate-slide-up animate-delay-300">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon tickets"></div>
                    <div class="stats-card-menu">
                        <button class="stats-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value"><?php echo e($categories->where('requires_serial', true)->count()); ?></div>
                    <div class="stats-card-label"><?php echo e(trans('app.Protected Categories')); ?></div>
                    <div class="stats-card-trend negative">
                        <i class="stats-trend-icon negative"></i>
                        <span><?php echo e(number_format(($categories->where('requires_serial', true)->count() / max($categories->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Public Categories Stats Card -->
        <div class="stats-card stats-card-info animate-slide-up animate-delay-400">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon licenses"></div>
                    <div class="stats-card-menu">
                        <button class="stats-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value"><?php echo e($categories->where('requires_serial', false)->count()); ?></div>
                    <div class="stats-card-label"><?php echo e(trans('app.Public Categories')); ?></div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span><?php echo e(number_format(($categories->where('requires_serial', false)->count() / max($categories->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KB Categories Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-folder me-2"></i><?php echo e(trans('app.all_categories')); ?></h2>
            <span class="admin-badge admin-badge-info"><?php echo e($categories->total()); ?> <?php echo e(trans('app.Categories')); ?></span>
        </div>
        <div class="admin-section-content">
            <?php if($categories->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 kb-categories-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center"><?php echo e(trans('app.Avatar')); ?></th>
                        <th><?php echo e(trans('app.Category')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Parent')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Articles')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Protection')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Created')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="category-row" data-name="<?php echo e(strtolower($cat->name)); ?>" data-protection="<?php echo e($cat->requires_serial ? 'protected' : 'public'); ?>" data-status="<?php echo e($cat->is_active ? 'active' : 'inactive'); ?>">
                        <td class="text-center">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center category-avatar">
                                <?php if($cat->icon): ?>
                                    <i class="<?php echo e($cat->icon); ?> text-primary"></i>
                                <?php else: ?>
                                    <span class="text-muted small fw-bold"><?php echo e(strtoupper(substr($cat->name, 0, 1))); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">
                                <?php echo e($cat->name); ?>

                                <?php if($cat->is_featured): ?>
                                    <i class="fas fa-star text-warning ms-1" title="<?php echo e(trans('app.Featured Category')); ?>"></i>
                                <?php endif; ?>
                                <?php if(!$cat->is_active): ?>
                                    <i class="fas fa-eye-slash text-muted ms-1" title="<?php echo e(trans('app.Inactive')); ?>"></i>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted"><?php echo e($cat->slug); ?></small>
                        </td>
                        <td class="text-center">
                            <?php if($cat->parent): ?>
                                <span class="text-muted"><?php echo e($cat->parent->name); ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">
                                <i class="fas fa-file-alt me-1"></i><?php echo e($cat->articles_count ?? 0); ?>

                            </span>
                        </td>
                        <td class="text-center">
                            <?php if($cat->requires_serial): ?>
                                <span class="badge bg-warning">
                                    <i class="fas fa-lock me-1"></i><?php echo e(trans('app.serial_protected')); ?>

                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-unlock me-1"></i><?php echo e(trans('app.public')); ?>

                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark"><?php echo e($cat->created_at->format('M d, Y')); ?></div>
                            <small class="text-muted"><?php echo e($cat->created_at->diffForHumans()); ?></small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="<?php echo e(route('admin.kb-categories.edit', $cat)); ?>"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    <?php echo e(trans('app.Edit')); ?>

                                </a>

                                <form action="<?php echo e(route('admin.kb-categories.destroy', $cat)); ?>" method="POST"
                                      class="d-inline" data-confirm="delete-category">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <input type="hidden" name="delete_mode" value="keep_articles">
                                    <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                        <i class="fas fa-archive me-1"></i>
                                        <?php echo e(trans('app.delete_keep_articles')); ?>

                                    </button>
                                </form>

                                <form action="<?php echo e(route('admin.kb-categories.destroy', $cat)); ?>" method="POST"
                                      class="d-inline" data-confirm="delete-category-articles">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <input type="hidden" name="delete_mode" value="with_articles">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fas fa-trash me-1"></i>
                                        <?php echo e(trans('app.delete_with_articles')); ?>

                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

            <?php if($categories->hasPages()): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($categories->links()); ?>

            </div>
            <?php endif; ?>
            <?php else: ?>
            <!-- Enhanced Empty State -->
            <div class="admin-empty-state kb-categories-empty-state">
                <div class="admin-empty-state-content">
                    <div class="admin-empty-state-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="admin-empty-state-text">
                        <h3 class="admin-empty-state-title"><?php echo e(trans('app.No Categories Found')); ?></h3>
                        <p class="admin-empty-state-description">
                            <?php echo e(trans('app.Create your first KB category to get started')); ?>

                        </p>
                    </div>
                    <div class="admin-empty-state-actions">
                        <a href="<?php echo e(route('admin.kb-categories.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                            <i class="fas fa-plus me-2"></i>
                            <?php echo e(trans('app.Create Your First Category')); ?>

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
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\kb\categories\index.blade.php ENDPATH**/ ?>