
<?php $__env->startSection('title', 'KB Articles'); ?>

<?php $__env->startSection('admin-content'); ?>
<!-- Admin KB Articles Page -->
<div class="admin-kb-articles-page">
    <div class="admin-page-header modern-header">
        <div class="admin-page-header-content">
            <div class="admin-page-title">
                <h1 class="gradient-text"><?php echo e(trans('app.kb_articles_management')); ?></h1>
                <p class="admin-page-subtitle"><?php echo e(trans('app.manage_kb_articles')); ?></p>
            </div>
            <div class="admin-page-actions">
                <a href="<?php echo e(route('admin.kb-articles.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-plus me-2"></i>
                    <?php echo e(trans('app.new_article')); ?>

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
                    <input type="text" class="admin-form-input" id="searchArticles" 
                           placeholder="<?php echo e(trans('app.search_articles')); ?>">
                    <i class="fas fa-search admin-search-icon"></i>
                </div>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="category-filter">
                            <i class="fas fa-folder me-1"></i><?php echo e(trans('app.Category')); ?>

                        </label>
                        <select id="category-filter" class="admin-form-input">
                            <option value=""><?php echo e(trans('app.All Categories')); ?></option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
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
                            <option value=""><?php echo e(trans('app.All Statuses')); ?></option>
                            <option value="published"><?php echo e(trans('app.published')); ?></option>
                            <option value="draft"><?php echo e(trans('app.draft')); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Section -->
    <div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
        <!-- Total Articles Stats Card -->
        <div class="stats-card stats-card-primary animate-slide-up">
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
                    <div class="stats-card-value"><?php echo e($articles->total()); ?></div>
                    <div class="stats-card-label"><?php echo e(trans('app.Total Articles')); ?></div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span><?php echo e($articles->where('is_published', true)->count()); ?> <?php echo e(trans('app.published')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Published Articles Stats Card -->
        <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
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
                    <div class="stats-card-value"><?php echo e($articles->where('is_published', true)->count()); ?></div>
                    <div class="stats-card-label"><?php echo e(trans('app.Published Articles')); ?></div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span><?php echo e(number_format(($articles->where('is_published', true)->count() / max($articles->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Draft Articles Stats Card -->
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
                    <div class="stats-card-value"><?php echo e($articles->where('is_published', false)->count()); ?></div>
                    <div class="stats-card-label"><?php echo e(trans('app.Draft Articles')); ?></div>
                    <div class="stats-card-trend negative">
                        <i class="stats-trend-icon negative"></i>
                        <span><?php echo e(number_format(($articles->where('is_published', false)->count() / max($articles->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Stats Card -->
        <div class="stats-card stats-card-info animate-slide-up animate-delay-400">
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
                    <div class="stats-card-value"><?php echo e($categories->count()); ?></div>
                    <div class="stats-card-label"><?php echo e(trans('app.Categories')); ?></div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span><?php echo e(trans('app.available_categories')); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KB Articles Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-file-alt me-2"></i><?php echo e(trans('app.all_articles')); ?></h2>
            <span class="admin-badge admin-badge-info"><?php echo e($articles->total()); ?> <?php echo e(trans('app.Articles')); ?></span>
        </div>
        <div class="admin-section-content">
            <?php if($articles->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 kb-articles-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center"><?php echo e(trans('app.Avatar')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Article')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Category')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Status')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Views')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Created')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="article-row" data-title="<?php echo e(strtolower($article->title)); ?>" data-category="<?php echo e($article->category_id ?? ''); ?>" data-status="<?php echo e($article->is_published ? 'published' : 'draft'); ?>">
                        <td class="text-center">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center article-avatar">
                                <span class="text-muted small fw-bold"><?php echo e(strtoupper(substr($article->title, 0, 1))); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?php echo e($article->title); ?></div>
                            <?php if($article->excerpt): ?>
                            <small class="text-muted"><?php echo e(Str::limit($article->excerpt, 60)); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if($article->category): ?>
                                <span class="text-muted"><?php echo e($article->category->name); ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge <?php echo e($article->is_published ? 'bg-success' : 'bg-warning'); ?>">
                                <?php if($article->is_published): ?>
                                    <i class="fas fa-check-circle me-1"></i><?php echo e(trans('app.published')); ?>

                                <?php else: ?>
                                    <i class="fas fa-edit me-1"></i><?php echo e(trans('app.draft')); ?>

                                <?php endif; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">
                                <i class="fas fa-eye me-1"></i><?php echo e($article->views_count ?? 0); ?>

                            </span>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark"><?php echo e($article->created_at->format('M d, Y')); ?></div>
                            <small class="text-muted"><?php echo e($article->created_at->diffForHumans()); ?></small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="<?php echo e(route('admin.kb-articles.show', $article)); ?>"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo e(trans('app.View')); ?>

                                </a>

                                <a href="<?php echo e(route('admin.kb-articles.edit', $article)); ?>"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    <?php echo e(trans('app.Edit')); ?>

                                </a>

                                <form action="<?php echo e(route('admin.kb-articles.destroy', $article)); ?>" method="POST"
                                      class="d-inline" data-confirm="delete-article">
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

            <?php if($articles->hasPages()): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($articles->links()); ?>

            </div>
            <?php endif; ?>
            <?php else: ?>
            <!-- Enhanced Empty State -->
            <div class="admin-empty-state kb-articles-empty-state">
                <div class="admin-empty-state-content">
                    <div class="admin-empty-state-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="admin-empty-state-text">
                        <h3 class="admin-empty-state-title"><?php echo e(trans('app.No Articles Found')); ?></h3>
                        <p class="admin-empty-state-description">
                            <?php echo e(trans('app.Create your first KB article to get started')); ?>

                        </p>
                    </div>
                    <div class="admin-empty-state-actions">
                        <a href="<?php echo e(route('admin.kb-articles.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                            <i class="fas fa-plus me-2"></i>
                            <?php echo e(trans('app.Create Your First Article')); ?>

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

<!-- JavaScript is now handled by admin-categories.js -->


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\kb\articles\index.blade.php ENDPATH**/ ?>