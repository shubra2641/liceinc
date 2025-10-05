

<?php $__env->startSection('title', trans('app.Search Knowledge Base')); ?>
<?php $__env->startSection('page-title', trans('app.Search Knowledge Base')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Find answers to your questions')); ?>


<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Search Header -->
    <div class="user-card kb-search-header">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-search" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h1 class="user-section-title-text"><?php echo e(trans('app.Search Knowledge Base')); ?></h1>
                        <p class="user-section-subtitle"><?php echo e(trans('app.Find answers to your questions and get help with
                            our products')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="user-kb-search-container">
                <form action="<?php echo e(route('kb.search')); ?>" method="get" class="user-kb-search-form" role="search">
                    <div class="user-kb-search-wrapper">
                        <div class="user-kb-search-input-container">
                            <label for="search-input" class="sr-only"><?php echo e(trans('app.Search articles...')); ?></label>
                            <div class="user-kb-search-icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </div>
                            <input type="text" name="q" id="search-input" value="<?php echo e($q); ?>" class="user-kb-search-input"
                                placeholder="<?php echo e(trans('app.Search articles...')); ?>" autocomplete="off"
                                aria-describedby="search-help">
                        </div>
                        <button type="submit" class="user-kb-search-btn" aria-label="<?php echo e(trans('app.Search')); ?>">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <span class="button-text"><?php echo e(trans('app.Search')); ?></span>
                        </button>
                    </div>
                    <div id="search-help" class="user-kb-search-help">
                        <?php echo e(trans('app.Search in articles, categories, and content')); ?>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if($q === ''): ?>
    <!-- Empty Search State -->
    <div class="user-card">
        <div class="user-card-content">
            <div class="user-empty-state">
                <div class="user-empty-state-icon">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </div>
                <h2 class="user-empty-state-title"><?php echo e(trans('app.Start your search')); ?></h2>
                <p class="user-empty-state-description">
                    <?php echo e(trans('app.Type something in the search box above to find articles and answers to your
                    questions')); ?>

                </p>
            </div>
        </div>
    </div>

    <!-- Popular Categories -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-folder" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="user-section-title-text"><?php echo e(trans('app.Browse by Category')); ?></h2>
                        <p class="user-section-subtitle"><?php echo e(trans('app.Explore our knowledge base categories')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="user-kb-categories-grid">
                <?php $__currentLoopData = $categoriesWithAccess; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="user-kb-category-card" data-category="<?php echo e($cat->slug); ?>">
                    <div class="user-kb-category-header">
                        <div class="user-kb-category-icon">
                            <i class="fas fa-folder" aria-hidden="true"></i>
                        </div>
                        <div class="user-kb-category-info">
                            <h3 class="user-kb-category-title">
                                <a href="<?php echo e(route('kb.category', $cat->slug)); ?>"
                                    aria-label="<?php echo e(trans('app.View category')); ?>: <?php echo e($cat->name); ?>">
                                    <?php echo e($cat->name); ?>

                                </a>
                            </h3>
                            <div class="user-kb-category-badges">
                                <?php if($cat->is_featured): ?>
                                <span class="user-kb-badge user-kb-badge-premium"
                                    aria-label="<?php echo e(trans('app.Premium')); ?>">
                                    <i class="fas fa-crown" aria-hidden="true"></i>
                                    <?php echo e(trans('app.Premium')); ?>

                                </span>
                                <?php endif; ?>
                                <?php if($cat->requires_serial || $cat->product_id): ?>
                                <?php if($cat->hasAccess): ?>
                                <span class="user-kb-badge user-kb-badge-success"
                                    aria-label="<?php echo e(trans('app.Accessible')); ?>">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                                    <?php echo e(trans('app.Accessible')); ?>

                                </span>
                                <?php else: ?>
                                <span class="user-kb-badge user-kb-badge-warning"
                                    aria-label="<?php echo e(trans('app.Locked')); ?>">
                                    <i class="fas fa-lock" aria-hidden="true"></i>
                                    <?php echo e(trans('app.Locked')); ?>

                                </span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="user-kb-category-arrow">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="user-kb-category-content">
                        <p class="user-kb-category-description">
                            <?php echo e(Str::limit($cat->description, 120)); ?>

                        </p>

                        <div class="user-kb-category-meta">
                            <div class="user-kb-category-meta-item">
                                <i class="fas fa-file-alt" aria-hidden="true"></i>
                                <span><?php echo e($cat->articles->count()); ?> <?php echo e(trans('app.articles')); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="user-kb-category-footer">
                        <a href="<?php echo e(route('kb.category', $cat->slug)); ?>" class="user-kb-category-btn"
                            aria-label="<?php echo e(trans('app.View articles in')); ?> <?php echo e($cat->name); ?>">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            <span><?php echo e(trans('app.View Articles')); ?></span>
                        </a>
                    </div>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Search Results -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-search" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="user-section-title-text"><?php echo e(trans('app.Search results')); ?></h2>
                        <p class="user-section-subtitle">
                            <?php if($results->count() > 0): ?>
                            <?php echo e($results->count()); ?> <?php echo e(trans('app.results for')); ?> "<strong><?php echo e($q); ?></strong>"
                            <?php else: ?>
                            <?php echo e(trans('app.No results for')); ?> "<strong><?php echo e($q); ?></strong>"
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="user-form-actions">
                    <label class="user-form-label" for="sortSelect"><?php echo e(trans('app.Sort by')); ?></label>
                    <select name="sort" id="sortSelect" class="user-form-select"
                        aria-label="<?php echo e(trans('app.Sort results by')); ?>">
                        <option value="relevance" <?php echo e(request('sort')=='relevance' ? 'selected' : ''); ?>><?php echo e(trans('app.Relevance')); ?></option>
                        <option value="newest" <?php echo e(request('sort')=='newest' ? 'selected' : ''); ?>><?php echo e(trans('app.Newest')); ?></option>
                        <option value="oldest" <?php echo e(request('sort')=='oldest' ? 'selected' : ''); ?>><?php echo e(trans('app.Oldest')); ?></option>
                        <option value="popular" <?php echo e(request('sort')=='popular' ? 'selected' : ''); ?>><?php echo e(trans('app.Most
                            Popular')); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <?php if($results->count() > 0): ?>
            <div class="user-kb-articles-grid" role="list" aria-label="<?php echo e(trans('app.Search results')); ?>">
                <?php $__currentLoopData = $resultsWithAccess; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="user-kb-article-card <?php echo e(!$item->hasAccess ? 'kb-result-locked' : ''); ?>" role="listitem"
                    data-search-type="<?php echo e($item->search_type); ?>" data-slug="<?php echo e($item->slug); ?>">
                    <div class="user-kb-article-header">
                        <div class="user-kb-article-icon">
                            <?php if($item->search_type === 'article'): ?>
                            <i class="fas fa-file-alt" aria-hidden="true"></i>
                            <?php else: ?>
                            <i class="fas fa-folder" aria-hidden="true"></i>
                            <?php endif; ?>
                        </div>
                        <div class="user-kb-article-info">
                            <h3 class="user-kb-article-title">
                                <?php if($item->search_type === 'article'): ?>
                                <?php if($item->hasAccess): ?>
                                <a href="<?php echo e(route('kb.article', $item->slug)); ?>" class="user-kb-article-link"
                                    aria-label="<?php echo e(trans('app.Read article')); ?>: <?php echo e($item->title); ?>">
                                    <?php echo \App\Http\Controllers\KbPublicController::highlightSearchTerm($item->title,
                                    $highlightQuery); ?>

                                </a>
                                <?php else: ?>
                                <span class="kb-result-locked-text"
                                    aria-label="<?php echo e(trans('app.Locked article')); ?>: <?php echo e($item->title); ?>">
                                    <?php echo \App\Http\Controllers\KbPublicController::highlightSearchTerm($item->title,
                                    $highlightQuery); ?>

                                </span>
                                <?php endif; ?>
                                <?php else: ?>
                                <?php if($item->hasAccess): ?>
                                <a href="<?php echo e(route('kb.category', $item->slug)); ?>" class="user-kb-article-link"
                                    aria-label="<?php echo e(trans('app.View category')); ?>: <?php echo e($item->name); ?>">
                                    <?php echo \App\Http\Controllers\KbPublicController::highlightSearchTerm($item->name,
                                    $highlightQuery); ?>

                                </a>
                                <?php else: ?>
                                <span class="kb-result-locked-text"
                                    aria-label="<?php echo e(trans('app.Locked category')); ?>: <?php echo e($item->name); ?>">
                                    <?php echo \App\Http\Controllers\KbPublicController::highlightSearchTerm($item->name,
                                    $highlightQuery); ?>

                                </span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </h3>
                            <div class="user-kb-article-badges">
                                <span
                                    class="user-kb-badge <?php echo e($item->search_type === 'article' ? 'user-kb-badge-info' : 'user-kb-badge-success'); ?>"
                                    aria-label="<?php echo e($item->search_type === 'article' ? trans('app.Article') : trans('app.Category')); ?>">
                                    <?php if($item->search_type === 'article'): ?>
                                    <i class="fas fa-file-alt" aria-hidden="true"></i>
                                    <?php echo e(trans('app.Article')); ?>

                                    <?php else: ?>
                                    <i class="fas fa-folder" aria-hidden="true"></i>
                                    <?php echo e(trans('app.Category')); ?>

                                    <?php endif; ?>
                                </span>

                                <?php if($item->is_featured): ?>
                                <span class="user-kb-badge user-kb-badge-premium"
                                    aria-label="<?php echo e(trans('app.Premium')); ?>">
                                    <i class="fas fa-crown" aria-hidden="true"></i>
                                    <?php echo e(trans('app.Premium')); ?>

                                </span>
                                <?php endif; ?>

                                <?php if($item->search_type === 'article' && $item->allow_comments): ?>
                                <span class="user-kb-badge user-kb-badge-comments"
                                    aria-label="<?php echo e(trans('app.Comments Enabled')); ?>">
                                    <i class="fas fa-comments" aria-hidden="true"></i>
                                    <?php echo e(trans('app.Comments Enabled')); ?>

                                </span>
                                <?php endif; ?>

                                <?php if(!$item->hasAccess): ?>
                                <span class="user-kb-badge user-kb-badge-warning"
                                    aria-label="<?php echo e(auth()->check() ? trans('app.Locked') : trans('app.Login Required')); ?>">
                                    <i class="fas fa-lock" aria-hidden="true"></i>
                                    <?php if(auth()->check()): ?>
                                    <?php echo e(trans('app.Locked')); ?>

                                    <?php else: ?>
                                    <?php echo e(trans('app.Login Required')); ?>

                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="user-kb-article-arrow">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="user-kb-article-content">
                        <p class="user-kb-article-description <?php echo e(!$item->hasAccess ? 'kb-result-locked-text' : ''); ?>">
                            <?php if($item->search_type === 'article'): ?>
                            <?php echo \App\Http\Controllers\KbPublicController::highlightSearchTerm(Str::limit($item->excerpt
                            ?: strip_tags($item->content), 200),
                            $highlightQuery); ?>

                            <?php else: ?>
                            <?php echo \App\Http\Controllers\KbPublicController::highlightSearchTerm(Str::limit($item->description,
                            200), $highlightQuery); ?>

                            <?php endif; ?>
                        </p>

                        <?php if(!$item->hasAccess): ?>
                        <p class="kb-result-access-message">
                            <?php if(auth()->check()): ?>
                            <?php echo e(trans('app.This content requires a valid license to access')); ?>

                            <?php else: ?>
                            <?php echo e(trans('app.Please login to access this content')); ?>

                            <?php endif; ?>
                        </p>
                        <?php endif; ?>

                        <div class="user-kb-article-meta">
                            <?php if($item->search_type === 'article'): ?>
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-calendar" aria-hidden="true"></i>
                                <span><?php echo e($item->created_at->format('M d, Y')); ?></span>
                            </div>
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                                <span><?php echo e($item->views ?? 0); ?> <?php echo e(trans('app.views')); ?></span>
                            </div>
                            <?php if($item->category): ?>
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-folder" aria-hidden="true"></i>
                                <span><?php echo e($item->category->name); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php else: ?>
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-file-alt" aria-hidden="true"></i>
                                <span><?php echo e($item->articles_count ?? 0); ?> <?php echo e(trans('app.articles')); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="user-kb-article-footer">
                        <?php if($item->search_type === 'article'): ?>
                        <?php if($item->hasAccess): ?>
                        <a href="<?php echo e(route('kb.article', $item->slug)); ?>" class="user-kb-article-btn"
                            aria-label="<?php echo e(trans('app.Read article')); ?>: <?php echo e($item->title); ?>"
                            title="<?php echo e(trans('app.Read article')); ?>: <?php echo e($item->title); ?>">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            <?php echo e(trans('app.Read Article')); ?>

                        </a>
                        <?php else: ?>
                        <span class="kb-result-locked-action">
                            <?php if(auth()->check()): ?>
                            <?php echo e(trans('app.License Required')); ?>

                            <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>" class="user-action-button secondary"
                                aria-label="<?php echo e(trans('app.Login to access')); ?>"
                                title="<?php echo e(trans('app.Login to access')); ?>">
                                <?php echo e(trans('app.Login to Access')); ?>

                            </a>
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                        <?php else: ?>
                        <?php if($item->hasAccess): ?>
                        <a href="<?php echo e(route('kb.category', $item->slug)); ?>" class="user-kb-article-btn"
                            aria-label="<?php echo e(trans('app.View category')); ?>: <?php echo e($item->name); ?>"
                            title="<?php echo e(trans('app.View category')); ?>: <?php echo e($item->name); ?>">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            <?php echo e(trans('app.View Category')); ?>

                        </a>
                        <?php else: ?>
                        <span class="kb-result-locked-action">
                            <?php if(auth()->check()): ?>
                            <?php echo e(trans('app.License Required')); ?>

                            <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>" class="user-action-button secondary"
                                aria-label="<?php echo e(trans('app.Login to access')); ?>"
                                title="<?php echo e(trans('app.Login to access')); ?>">
                                <?php echo e(trans('app.Login to Access')); ?>

                            </a>
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Pagination -->
            <?php if($results->count() > 10): ?>
            <nav class="kb-search-pagination" aria-label="<?php echo e(trans('app.Search results pagination')); ?>">
                <div class="pagination-info">
                    <span><?php echo e(trans('app.Showing')); ?> <?php echo e((request('page', 1) - 1) * 10 + 1); ?>-<?php echo e(min(request('page', 1) *
                        10, $results->count())); ?> <?php echo e(trans('app.of')); ?> <?php echo e($results->count()); ?> <?php echo e(trans('app.results')); ?></span>
                </div>
                <div class="pagination-links">
                    <?php if(request('page', 1) > 1): ?>
                    <a href="<?php echo e(request()->fullUrlWithQuery(['page' => request('page', 1) - 1])); ?>"
                        class="pagination-link pagination-prev" aria-label="<?php echo e(trans('app.Previous page')); ?>">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        <?php echo e(trans('app.Previous')); ?>

                    </a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= ceil($results->count() / 10); $i++): ?>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $i])); ?>"
                            class="pagination-link <?php echo e(request('page', 1) == $i ? 'pagination-active' : ''); ?>"
                            aria-label="<?php echo e(trans('app.Go to page')); ?> <?php echo e($i); ?>" <?php if(request('page', 1)==$i): ?>
                            aria-current="page" <?php endif; ?>>
                            <?php echo e($i); ?>

                        </a>
                        <?php endfor; ?>

                        <?php if(request('page', 1) < ceil($results->count() / 10)): ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['page' => request('page', 1) + 1])); ?>"
                                class="pagination-link pagination-next" aria-label="<?php echo e(trans('app.Next page')); ?>">
                                <?php echo e(trans('app.Next')); ?>

                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                </div>
            </nav>
            <?php endif; ?>
            <?php else: ?>
            <!-- No Results -->
            <div class="user-card">
                <div class="user-card-content">
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-search-minus" aria-hidden="true"></i>
                        </div>
                        <h2 class="user-empty-state-title"><?php echo e(trans('app.No results found')); ?></h2>
                        <p class="user-empty-state-description">
                            <?php echo e(trans('app.We couldn\'t find any articles matching your search. Try different keywords or
                            browse our categories below.')); ?>

                        </p>

                        <!-- Search Suggestions -->
                        <div class="user-form-actions">
                            <h3 class="user-form-label"><?php echo e(trans('app.Search Tips')); ?></h3>
                            <ul class="user-features-list">
                                <li class="user-feature-item">
                                    <i class="fas fa-lightbulb" aria-hidden="true"></i>
                                    <span class="user-feature-text"><?php echo e(trans('app.Try different keywords')); ?></span>
                                </li>
                                <li class="user-feature-item">
                                    <i class="fas fa-lightbulb" aria-hidden="true"></i>
                                    <span class="user-feature-text"><?php echo e(trans('app.Check your spelling')); ?></span>
                                </li>
                                <li class="user-feature-item">
                                    <i class="fas fa-lightbulb" aria-hidden="true"></i>
                                    <span class="user-feature-text"><?php echo e(trans('app.Use more general terms')); ?></span>
                                </li>
                                <li class="user-feature-item">
                                    <i class="fas fa-lightbulb" aria-hidden="true"></i>
                                    <span class="user-feature-text"><?php echo e(trans('app.Try fewer keywords')); ?></span>
                                </li>
                            </ul>
                        </div>

                        <div class="user-form-actions">
                            <a href="<?php echo e(route('kb.index')); ?>" class="user-action-button"
                                aria-label="<?php echo e(trans('app.Browse all categories')); ?>">
                                <i class="fas fa-folder" aria-hidden="true"></i>
                                <?php echo e(trans('app.Browse Categories')); ?>

                            </a>
                            <a href="<?php echo e(route('support.tickets.create')); ?>" class="user-action-button secondary"
                                aria-label="<?php echo e(trans('app.Contact support team')); ?>">
                                <i class="fas fa-headset" aria-hidden="true"></i>
                                <?php echo e(trans('app.Contact Support')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Popular Categories (when there are results) -->
    <?php if($q !== '' && $results->count() > 0): ?>
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-star" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="user-section-title-text"><?php echo e(trans('app.Popular Categories')); ?></h2>
                        <p class="user-section-subtitle"><?php echo e(trans('app.Explore more content')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="user-kb-categories-grid">
                <?php $__currentLoopData = $categoriesWithAccess->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="user-kb-category-card" data-category="<?php echo e($cat->slug); ?>">
                    <div class="user-kb-category-header">
                        <div class="user-kb-category-icon">
                            <i class="fas fa-folder" aria-hidden="true"></i>
                        </div>
                        <div class="user-kb-category-info">
                            <h3 class="user-kb-category-title">
                                <a href="<?php echo e(route('kb.category', $cat->slug)); ?>"
                                    aria-label="<?php echo e(trans('app.View category')); ?>: <?php echo e($cat->name); ?>">
                                    <?php echo e($cat->name); ?>

                                </a>
                            </h3>
                            <div class="user-kb-category-badges">
                                <?php if($cat->is_featured): ?>
                                <span class="user-kb-badge user-kb-badge-premium"
                                    aria-label="<?php echo e(trans('app.Premium')); ?>">
                                    <i class="fas fa-crown" aria-hidden="true"></i>
                                    <?php echo e(trans('app.Premium')); ?>

                                </span>
                                <?php endif; ?>
                                <?php if($cat->requires_serial || $cat->product_id): ?>
                                <?php if($cat->hasAccess): ?>
                                <span class="user-kb-badge user-kb-badge-success"
                                    aria-label="<?php echo e(trans('app.Accessible')); ?>">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                                    <?php echo e(trans('app.Accessible')); ?>

                                </span>
                                <?php else: ?>
                                <span class="user-kb-badge user-kb-badge-warning"
                                    aria-label="<?php echo e(trans('app.Locked')); ?>">
                                    <i class="fas fa-lock" aria-hidden="true"></i>
                                    <?php echo e(trans('app.Locked')); ?>

                                </span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="user-kb-category-arrow">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="user-kb-category-content">
                        <p class="user-kb-category-description">
                            <?php echo e(Str::limit($cat->description, 120)); ?>

                        </p>

                        <div class="user-kb-category-meta">
                            <div class="user-kb-category-meta-item">
                                <i class="fas fa-file-alt" aria-hidden="true"></i>
                                <span><?php echo e($cat->articles->count()); ?> <?php echo e(trans('app.articles')); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="user-kb-category-footer">
                        <a href="<?php echo e(route('kb.category', $cat->slug)); ?>" class="user-kb-category-btn"
                            aria-label="<?php echo e(trans('app.View articles in')); ?> <?php echo e($cat->name); ?>">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            <span><?php echo e(trans('app.View Articles')); ?></span>
                        </a>
                    </div>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\my-logos\resources\views/kb/search.blade.php ENDPATH**/ ?>