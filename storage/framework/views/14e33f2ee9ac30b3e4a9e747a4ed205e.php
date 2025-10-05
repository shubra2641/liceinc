

<?php $__env->startSection('title', $article->title); ?>
<?php $__env->startSection('page-title', $article->title); ?>
<?php $__env->startSection('page-subtitle', trans('app.Knowledge Base Article')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Article Header -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h1 class="user-card-title"><?php echo e($article->title); ?></h1>
                        <p class="user-card-subtitle"><?php echo e(trans('app.Knowledge Base Article')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Article Content -->
    <div class="user-card">
        <div class="user-card-content">
            <!-- Article Meta Info -->
            <div class="user-kb-article-meta">
                <div class="user-kb-article-meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo e(trans('app.Updated')); ?> <?php echo e($article->updated_at->format('M d, Y')); ?></span>
                </div>
                <div class="user-kb-article-meta-item">
                    <i class="fas fa-eye"></i>
                    <span><?php echo e(trans('app.Views')); ?>: <?php echo e($article->views); ?></span>
                </div>
                <?php if($article->category): ?>
                <div class="user-kb-article-meta-item">
                    <i class="fas fa-folder"></i>
                    <span><?php echo e($article->category->name); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Article Content -->
            <div class="article-content">
                <?php echo $article->content; ?>

            </div>
        </div>
    </div>

    <!-- Article Information Sidebar -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text"><?php echo e(trans('app.Article Information')); ?></h3>
                        <p class="user-section-subtitle"><?php echo e(trans('app.About this article')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="category-info-grid">
                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Category')); ?></div>
                        <div class="info-value"><?php echo e(optional($article->category)->name ?? trans('app.Uncategorized')); ?>

                        </div>
                    </div>
                </div>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Created')); ?></div>
                        <div class="info-value"><?php echo e($article->created_at->format('M d, Y')); ?></div>
                    </div>
                </div>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Last Updated')); ?></div>
                        <div class="info-value"><?php echo e($article->updated_at->format('M d, Y')); ?></div>
                    </div>
                </div>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Views')); ?></div>
                        <div class="info-value"><?php echo e($article->views); ?></div>
                    </div>
                </div>
            </div>

            <div class="user-form-actions">
                <button class="user-action-button" data-action="print">
                    <i class="fas fa-print"></i>
                    <?php echo e(trans('app.Print Article')); ?>

                </button>

                <button class="user-action-button secondary" data-action="share">
                    <i class="fas fa-share-alt"></i>
                    <?php echo e(trans('app.Share')); ?>

                </button>
            </div>
        </div>
    </div>

    <!-- Related Articles Section -->
    <?php if(isset($relatedArticles) && $relatedArticles->count() > 0): ?>
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text"><?php echo e(trans('app.Related Articles')); ?></h3>
                        <p class="user-section-subtitle"><?php echo e(trans('app.Similar articles you might find helpful')); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('kb.index')); ?>" class="user-section-link">
                    <i class="fas fa-arrow-right"></i>
                    <span><?php echo e(trans('app.View All Articles')); ?></span>
                </a>
            </div>
        </div>
        <div class="user-card-content">
            <div class="user-kb-articles-grid">
                <?php $__currentLoopData = $relatedArticles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedArticle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="user-kb-article-card" data-article="<?php echo e($relatedArticle->slug); ?>">
                    <div class="user-kb-article-header">
                        <div class="user-kb-article-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="user-kb-article-info">
                            <h3 class="user-kb-article-title">
                                <a href="<?php echo e(route('kb.article', $relatedArticle->slug)); ?>">
                                    <?php echo e($relatedArticle->title); ?>

                                </a>
                            </h3>
                            <div class="user-kb-article-badges">
                                <?php if($relatedArticle->requires_serial || ($relatedArticle->category &&
                                $relatedArticle->category->requires_serial) || $relatedArticle->product_id): ?>
                                <?php if(auth()->check()): ?>
                                <?php if($relatedArticle->hasAccess): ?>
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
                            <?php echo e(Str::limit($relatedArticle->excerpt ?: strip_tags($relatedArticle->content), 120)); ?>

                        </p>

                        <div class="user-kb-article-meta">
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?php echo e($relatedArticle->created_at->format('M d, Y')); ?></span>
                            </div>
                            <?php if($relatedArticle->category): ?>
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-folder"></i>
                                <span><?php echo e($relatedArticle->category->name); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo e($relatedArticle->created_at->diffForHumans()); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="user-kb-article-footer">
                        <a href="<?php echo e(route('kb.article', $relatedArticle->slug)); ?>" class="user-kb-article-btn">
                            <i class="fas fa-arrow-right"></i>
                            <span><?php echo e(trans('app.Read More')); ?></span>
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
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\my-logos\resources\views/kb/article.blade.php ENDPATH**/ ?>