

<?php $__env->startSection('title', trans('app.Knowledgebase')); ?>
<?php $__env->startSection('page-title', trans('app.Knowledge Base')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Find answers to your questions and get help with our products')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-book"></i>
                <?php echo e(trans('app.Knowledge Base')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Find answers to your questions and get help with our products')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Hero Search Section -->
            <div class="user-kb-hero">
                <div class="user-kb-hero-content">
                    <div class="user-kb-hero-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h2 class="user-kb-hero-title">
                        <?php echo e(trans('app.Find Answers Instantly')); ?>

                    </h2>
                    <p class="user-kb-hero-subtitle">
                        <?php echo e(trans('app.Search through our comprehensive knowledge base to find solutions to your questions')); ?>

                    </p>
                    
                    <!-- Advanced Search Form -->
                    <div class="user-kb-search-container">
                        <form action="<?php echo e(route('kb.search')); ?>" method="get" class="user-kb-search-form">
                            <div class="user-kb-search-wrapper">
                                <div class="user-kb-search-input-container">
                                    <i class="fas fa-search user-kb-search-icon"></i>
                                    <input type="text" name="q" class="user-kb-search-input"
                                        placeholder="<?php echo e(trans('app.Search articles, guides, and tutorials...')); ?>"
                                        autocomplete="off">
                                    <div class="user-kb-search-suggestions" id="searchSuggestions"></div>
                                </div>
                                <button type="submit" class="user-kb-search-btn">
                                    <i class="fas fa-search"></i>
                                    <span><?php echo e(trans('app.Search')); ?></span>
                                </button>
                            </div>
                        </form>
                        
                        <!-- Quick Search Tags -->
                        <div class="user-kb-quick-tags">
                            <span class="user-kb-tag-label"><?php echo e(trans('app.Popular searches:')); ?></span>
                            <a href="<?php echo e(route('kb.search', ['q' => 'installation'])); ?>" class="user-kb-tag">
                                <i class="fas fa-download"></i>
                                <?php echo e(trans('app.Installation')); ?>

                            </a>
                            <a href="<?php echo e(route('kb.search', ['q' => 'configuration'])); ?>" class="user-kb-tag">
                                <i class="fas fa-cog"></i>
                                <?php echo e(trans('app.Configuration')); ?>

                            </a>
                            <a href="<?php echo e(route('kb.search', ['q' => 'troubleshooting'])); ?>" class="user-kb-tag">
                                <i class="fas fa-tools"></i>
                                <?php echo e(trans('app.Troubleshooting')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Section -->
            <div class="user-card user-kb-categories-card">
                <div class="user-card-header">
                    <div class="user-section-header">
                        <div class="user-section-title">
                            <div class="user-section-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div>
                                <h3 class="user-section-title-text"><?php echo e(trans('app.Browse by Category')); ?></h3>
                                <p class="user-section-subtitle"><?php echo e(trans('app.Explore articles organized by topic')); ?></p>
                            </div>
                        </div>
                        <div class="user-section-badge">
                            <i class="fas fa-folder"></i>
                            <span><?php echo e($categories->count()); ?> <?php echo e(trans('app.categories')); ?></span>
                        </div>
                    </div>
                </div>
                <div class="user-card-content">

                    <?php if($categories->isEmpty()): ?>
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            <?php echo e(trans('app.No categories available')); ?>

                        </h3>
                        <p class="user-empty-state-description">
                            <?php echo e(trans('app.Check back later for new categories')); ?>

                        </p>
                    </div>
                    <?php else: ?>
                    <div class="user-kb-categories-grid">
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="user-kb-category-card" data-category="<?php echo e($cat->slug); ?>">
                            <div class="user-kb-category-header">
                                <div class="user-kb-category-icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <div class="user-kb-category-info">
                                    <h3 class="user-kb-category-title">
                                        <a href="<?php echo e(route('kb.category', $cat->slug)); ?>">
                                            <?php echo e($cat->name); ?>

                                        </a>
                                    </h3>
                                    <div class="user-kb-category-badges">
                                        <?php if($cat->is_featured): ?>
                                        <span class="user-kb-badge user-kb-badge-premium">
                                            <i class="fas fa-crown"></i>
                                            <?php echo e(trans('app.Premium')); ?>

                                        </span>
                                        <?php endif; ?>
                                        <?php if($cat->requires_serial || $cat->product_id): ?>
                                        <?php if(auth()->check()): ?>
                                        <?php if($cat->hasAccess): ?>
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
                                    <?php echo e(Str::limit($cat->description, 120)); ?>

                                </p>
                                
                                <div class="user-kb-category-stats">
                                    <div class="user-kb-stat">
                                        <i class="fas fa-file-alt"></i>
                                        <span><?php echo e($cat->articles->count()); ?> <?php echo e(trans('app.articles')); ?></span>
                                    </div>
                                    <?php if($cat->articles->count() > 0): ?>
                                    <div class="user-kb-stat">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo e($cat->articles->sortByDesc('created_at')->first()->created_at->diffForHumans()); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="user-kb-category-footer">
                                <a href="<?php echo e(route('kb.category', $cat->slug)); ?>" class="user-kb-category-btn">
                                    <i class="fas fa-arrow-right"></i>
                                    <span><?php echo e(trans('app.Explore Category')); ?></span>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

            <!-- Latest Articles Section -->
            <?php if($latest->count() > 0): ?>
            <div class="user-card user-kb-articles-card">
                <div class="user-card-header">
                    <div class="user-section-header">
                        <div class="user-section-title">
                            <div class="user-section-icon">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div>
                                <h3 class="user-section-title-text"><?php echo e(trans('app.Latest Articles')); ?></h3>
                                <p class="user-section-subtitle"><?php echo e(trans('app.Recently published articles and guides')); ?></p>
                            </div>
                        </div>
                        <a href="<?php echo e(route('kb.search')); ?>" class="user-section-link">
                            <i class="fas fa-arrow-right"></i>
                            <span><?php echo e(trans('app.View All Articles')); ?></span>
                        </a>
                    </div>
                </div>
                <div class="user-card-content">

                    <div class="user-kb-articles-grid">
                        <?php $__currentLoopData = $latest; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                                        <?php if($article->is_featured): ?>
                                        <span class="user-kb-badge user-kb-badge-premium">
                                            <i class="fas fa-crown"></i>
                                            <?php echo e(trans('app.Premium')); ?>

                                        </span>
                                        <?php endif; ?>
                                        <?php if($article->allow_comments): ?>
                                        <span class="user-kb-badge user-kb-badge-comments">
                                            <i class="fas fa-comments"></i>
                                            <?php echo e(trans('app.Comments Enabled')); ?>

                                        </span>
                                        <?php endif; ?>
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
                                    <?php if($article->category): ?>
                                    <div class="user-kb-article-meta-item">
                                        <i class="fas fa-folder"></i>
                                        <span><?php echo e($article->category->name); ?></span>
                                    </div>
                                    <?php endif; ?>
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
            </div>
        </div>
        <?php endif; ?>


        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/kb/index.blade.php ENDPATH**/ ?>