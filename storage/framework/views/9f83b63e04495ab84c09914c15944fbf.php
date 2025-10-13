

<?php $__env->startSection('title', $category->name); ?>
<?php $__env->startSection('page-title', $category->name); ?>
<?php $__env->startSection('page-subtitle', $category->description); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-folder-open"></i>
                <?php echo e($category->name); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e($category->description); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Breadcrumbs -->
            <div class="user-breadcrumbs">
                <a href="<?php echo e(route('kb.index')); ?>" class="user-breadcrumb-link">
                    <i class="fas fa-home"></i>
                    <?php echo e(trans('app.Knowledge Base')); ?>

                </a>
                <i class="fas fa-chevron-right user-breadcrumb-separator"></i>
                <span class="user-breadcrumb-current"><?php echo e($category->name); ?></span>
            </div>

            <!-- Category Status -->
            <?php if($category->requires_serial || $category->product_id): ?>
            <div class="user-category-status">
                <?php if(auth()->check()): ?>
                    <?php if($category->hasAccess): ?>
                        <div class="user-status-badge user-status-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo e(trans('app.Full Access Available')); ?>

                        </div>
                    <?php else: ?>
                        <div class="user-status-badge user-status-warning">
                            <i class="fas fa-lock"></i>
                            <?php echo e(trans('app.Purchase Required for Access')); ?>

                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="user-status-badge user-status-info">
                        <i class="fas fa-user-lock"></i>
                        <?php echo e(trans('app.Login Required for Access')); ?>

                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Search Section -->

        </div>
    </div>

    <!-- Articles Section -->
    <div class="user-card user-kb-articles-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text"><?php echo e(trans('app.Articles')); ?></h3>
                        <p class="user-section-subtitle"><?php echo e(trans('app.Explore articles in this category')); ?></p>
                    </div>
                </div>
                <div class="user-section-badge">
                    <i class="fas fa-file"></i>
                    <span><?php echo e($articles->total()); ?> <?php echo e(trans('app.articles')); ?></span>
                </div>
            </div>
        </div>
        <div class="user-card-content">
            <?php if($articles->isEmpty()): ?>
            <div class="user-empty-state">
                <div class="user-empty-state-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 class="user-empty-state-title">
                    <?php echo e(trans('app.No articles found')); ?>

                </h3>
                <p class="user-empty-state-description">
                    <?php echo e(trans('app.This category doesn\'t have any articles yet')); ?>

                </p>
            </div>
            <?php else: ?>
            <div class="user-kb-articles-grid">
                <?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="user-kb-article-card" data-article="<?php echo e($article->slug); ?>">
                    <div class="user-kb-article-header">
                        <div class="user-kb-article-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="user-kb-article-info">
                            <h3 class="user-kb-article-title">
                                <a href="<?php echo e(route('kb.article', $article->slug)); ?>">
                                    <?php echo e($article->title); ?>

                                </a>
                            </h3>
                            <div class="user-kb-article-badges">
                                <?php if($article->requires_serial || ($article->category && $article->category->requires_serial) || $article->product_id): ?>
                                <?php if(auth()->check()): ?>
                                <?php if($article->hasAccess): ?>
                                <span class="user-kb-badge user-kb-badge-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo e(trans('app.Accessible')); ?>

                                </span>
                                <?php else: ?>
                                <span class="user-kb-badge user-kb-badge-warning">
                                    <i class="fas fa-lock"></i>
                                    <?php echo e(trans('app.Locked')); ?>

                                </span>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="user-kb-badge user-kb-badge-info">
                                    <i class="fas fa-user-lock"></i>
                                    <?php echo e(trans('app.Login Required')); ?>

                                </span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="user-kb-article-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>

                    <div class="user-kb-article-content">
                        <p class="user-kb-article-description">
                            <?php echo e(Str::limit($article->excerpt ?: strip_tags($article->content), 120)); ?>

                        </p>
                        
                        <div class="user-kb-article-meta">
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?php echo e($article->created_at->format('M d, Y')); ?></span>
                            </div>
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-eye"></i>
                                <span><?php echo e($article->views); ?> <?php echo e(trans('app.views')); ?></span>
                            </div>
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo e($article->created_at->diffForHumans()); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="user-kb-article-footer">
                        <a href="<?php echo e(route('kb.article', $article->slug)); ?>" class="user-kb-article-btn">
                            <i class="fas fa-arrow-right"></i>
                            <span><?php echo e(trans('app.Read Article')); ?></span>
                        </a>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Pagination -->
            <?php if($articles->hasPages()): ?>
            <div class="user-pagination">
                <?php echo e($articles->links()); ?>

            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Category Info Sidebar -->
    <div class="user-card user-kb-sidebar-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text"><?php echo e(trans('app.Category Info')); ?></h3>
                        <p class="user-section-subtitle"><?php echo e(trans('app.Details about this category')); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="user-card-content">
            <div class="user-kb-info-grid">
                <div class="user-kb-info-item">
                    <div class="user-kb-info-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="user-kb-info-content">
                        <h4 class="user-kb-info-label"><?php echo e(trans('app.Category Name')); ?></h4>
                        <p class="user-kb-info-value"><?php echo e($category->name); ?></p>
                    </div>
                </div>

                <div class="user-kb-info-item">
                    <div class="user-kb-info-icon">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <div class="user-kb-info-content">
                        <h4 class="user-kb-info-label"><?php echo e(trans('app.Description')); ?></h4>
                        <p class="user-kb-info-value"><?php echo e($category->description); ?></p>
                    </div>
                </div>

                <div class="user-kb-info-item">
                    <div class="user-kb-info-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="user-kb-info-content">
                        <h4 class="user-kb-info-label"><?php echo e(trans('app.Total Articles')); ?></h4>
                        <p class="user-kb-info-value"><?php echo e($articles->total()); ?></p>
                    </div>
                </div>

                <?php if($category->requires_serial || $category->product_id): ?>
                <div class="user-kb-info-item">
                    <div class="user-kb-info-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="user-kb-info-content">
                        <h4 class="user-kb-info-label"><?php echo e(trans('app.Access Status')); ?></h4>
                        <div class="user-kb-info-value">
                            <?php if(auth()->check()): ?>
                                <?php if($category->hasAccess): ?>
                                    <span class="user-kb-badge user-kb-badge-success">
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo e(trans('app.Full Access Available')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="user-kb-badge user-kb-badge-warning">
                                        <i class="fas fa-lock"></i>
                                        <?php echo e(trans('app.Purchase Required for Access')); ?>

                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="user-kb-badge user-kb-badge-info">
                                    <i class="fas fa-user-lock"></i>
                                    <?php echo e(trans('app.Login Required for Access')); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related Categories Section -->
    <?php if($relatedCategories->count() > 0): ?>
    <div class="user-card user-kb-categories-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text"><?php echo e(trans('app.Related Categories')); ?></h3>
                        <p class="user-section-subtitle"><?php echo e(trans('app.Explore similar categories')); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('kb.index')); ?>" class="user-section-link">
                    <i class="fas fa-arrow-right"></i>
                    <span><?php echo e(trans('app.View All Categories')); ?></span>
                </a>
            </div>
        </div>
        <div class="user-card-content">
            <div class="user-kb-categories-grid">
                <?php $__currentLoopData = $relatedCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedCat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="user-kb-category-card" data-category="<?php echo e($relatedCat->slug); ?>">
                    <div class="user-kb-category-header">
                        <div class="user-kb-category-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="user-kb-category-info">
                            <h3 class="user-kb-category-title">
                                <a href="<?php echo e(route('kb.category', $relatedCat->slug)); ?>">
                                    <?php echo e($relatedCat->name); ?>

                                </a>
                            </h3>
                            <div class="user-kb-category-badges">
                                <?php if($relatedCat->requires_serial || $relatedCat->product_id): ?>
                                <?php if(auth()->check()): ?>
                                <?php if($relatedCat->hasAccess): ?>
                                <span class="user-kb-badge user-kb-badge-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo e(trans('app.Accessible')); ?>

                                </span>
                                <?php else: ?>
                                <span class="user-kb-badge user-kb-badge-warning">
                                    <i class="fas fa-lock"></i>
                                    <?php echo e(trans('app.Locked')); ?>

                                </span>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="user-kb-badge user-kb-badge-info">
                                    <i class="fas fa-user-lock"></i>
                                    <?php echo e(trans('app.Login Required')); ?>

                                </span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="user-kb-category-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>

                    <div class="user-kb-category-content">
                        <p class="user-kb-category-description">
                            <?php echo e(Str::limit($relatedCat->description, 120)); ?>

                        </p>
                        
                        <div class="user-kb-category-stats">
                            <div class="user-kb-stat">
                                <i class="fas fa-file-alt"></i>
                                <span><?php echo e($relatedCat->articles->count()); ?> <?php echo e(trans('app.articles')); ?></span>
                            </div>
                            <?php if($relatedCat->articles->count() > 0): ?>
                            <div class="user-kb-stat">
                                <i class="fas fa-clock"></i>
                                <span><?php echo e($relatedCat->articles->sortByDesc('created_at')->first()->created_at->diffForHumans()); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="user-kb-category-footer">
                        <a href="<?php echo e(route('kb.category', $relatedCat->slug)); ?>" class="user-kb-category-btn">
                            <i class="fas fa-arrow-right"></i>
                            <span><?php echo e(trans('app.Explore Category')); ?></span>
                        </a>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/kb/category.blade.php ENDPATH**/ ?>