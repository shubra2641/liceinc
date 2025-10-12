<?php $__env->startSection('page-title', trans('app.Browse Products')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Discover and purchase new products')); ?>

<?php $__env->startSection('seo_title', $siteSeoTitle ?? trans('app.Browse Products')); ?>
<?php $__env->startSection('meta_description', $siteSeoDescription ?? trans('app.Discover and purchase new products')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-box"></i>
                <?php echo e(trans('app.Available Products')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Discover and purchase new products to enhance your projects')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Stats Cards -->
            <div class="user-stats-grid">
                <!-- Total Products -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Products')); ?></div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($products->total()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Available products')); ?></p>
                </div>

                <!-- Categories -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Categories')); ?></div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-folder"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($categories->count()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Product categories')); ?></p>
                </div>

                <!-- Free Products -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Free Products')); ?></div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-gift"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($products->where('price', 0)->count()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.No cost products')); ?></p>
                </div>

                <!-- Paid Products -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Paid Products')); ?></div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($products->where('price', '>', 0)->count()); ?></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.Premium products')); ?></p>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-search"></i>
                        <?php echo e(trans('app.Search & Filter')); ?>

                    </div>
                    <p class="user-card-subtitle"><?php echo e(trans('app.Find the perfect product for your needs')); ?></p>
                </div>
                <div class="user-card-content">
                    <!-- Search Form -->
                    <form action="<?php echo e(route('public.products.index')); ?>" method="get" class="user-search-form">
                        <div class="user-search-input-group">
                            <i class="fas fa-search user-search-icon"></i>
                            <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                                class="user-search-input"
                                placeholder="<?php echo e(trans('app.Search products...')); ?>">
                            <button type="submit" class="user-search-button">
                                <i class="fas fa-search"></i>
                                <?php echo e(trans('app.Search')); ?>

                            </button>
                        </div>
                    </form>

                    <!-- Filters -->
                    <div class="user-filters-row">
                        <div class="user-filters-group">
                            <!-- Category Filter -->
                            <div class="user-form-group">
                                <label class="user-form-label"><?php echo e(trans('app.Category')); ?></label>
                                <select name="category" data-action="submit-on-change" form="filterForm"
                                    class="user-form-select">
                                    <option value=""><?php echo e(trans('app.All Categories')); ?></option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>"
                                        <?php echo e(request('category') == $category->id ? 'selected' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <!-- Language Filter -->
                            <div class="user-form-group">
                                <label class="user-form-label"><?php echo e(trans('app.Language')); ?></label>
                                <select name="language" data-action="submit-on-change" form="filterForm"
                                    class="user-form-select">
                                    <option value=""><?php echo e(trans('app.All Languages')); ?></option>
                                    <?php $__currentLoopData = $programmingLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($language->id); ?>"
                                        <?php echo e(request('language') == $language->id ? 'selected' : ''); ?>>
                                        <?php echo e($language->name); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <!-- Price Filter -->
                            <div class="user-form-group">
                                <label class="user-form-label"><?php echo e(trans('app.Price')); ?></label>
                                <select name="price_filter" data-action="submit-on-change" form="filterForm"
                                    class="user-form-select">
                                    <option value=""><?php echo e(trans('app.All Prices')); ?></option>
                                    <option value="free" <?php echo e(request('price_filter') == 'free' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Free Only')); ?></option>
                                    <option value="paid" <?php echo e(request('price_filter') == 'paid' ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.Paid Only')); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Sort Options -->
                        <div class="user-sort-group">
                            <label class="user-form-label"><?php echo e(trans('app.Sort by')); ?></label>
                            <select name="sort" data-action="submit-on-change" form="filterForm"
                                class="user-form-select">
                                <option value="name" <?php echo e(request('sort', 'name') == 'name' ? 'selected' : ''); ?>>
                                    <?php echo e(trans('app.Name')); ?></option>
                                <option value="price_low" <?php echo e(request('sort') == 'price_low' ? 'selected' : ''); ?>>
                                    <?php echo e(trans('app.Price: Low to High')); ?></option>
                                <option value="price_high" <?php echo e(request('sort') == 'price_high' ? 'selected' : ''); ?>>
                                    <?php echo e(trans('app.Price: High to Low')); ?></option>
                                <option value="newest" <?php echo e(request('sort') == 'newest' ? 'selected' : ''); ?>>
                                    <?php echo e(trans('app.Newest')); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Hidden form for filters -->
                    <form id="filterForm" method="get" class="hidden">
                        <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                    </form>
                    <noscript>
                        <div class="user-alert user-alert-warning">
                            <div class="user-alert-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="user-alert-content">
                                <h4 class="user-alert-title"><?php echo e(trans('app.JavaScript Required')); ?></h4>
                                <p class="user-alert-text">
                                    <?php echo e(trans('app.Filtering and sorting functionality requires JavaScript to be enabled. Please enable JavaScript or use the search form above.')); ?>

                                </p>
                            </div>
                        </div>
                    </noscript>
                </div>
            </div>

            <!-- Products Section -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-shopping-cart"></i>
                        <?php echo e(trans('app.Products')); ?>

                    </div>
                    <p class="user-card-subtitle"><?php echo e(trans('app.Browse and purchase products')); ?></p>
                </div>
                <div class="user-card-content">
                    <?php if($products->count() > 0): ?>
                    <div class="user-products-grid">
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="user-product-card">
                            <div class="user-product-header">
                                <div>
                                    <div class="user-product-title-row">
                                        <h3 class="user-product-title"><?php echo e($product->name); ?></h3>
                                        <?php if($product->is_featured || $product->is_popular): ?>
                                        <span class="user-premium-badge">
                                            <i class="fas fa-crown"></i>
                                            <?php echo e(trans('app.Premium')); ?>

                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="user-product-version">v<?php echo e($product->latest_version ?? '-'); ?></p>
                                </div>
                                <div class="user-product-price">
                                    <?php if($product->price > 0): ?>
                                    <div class="user-product-price-value">$<?php echo e(number_format($product->price, 2)); ?></div>
                                    <?php else: ?>
                                    <div class="user-product-price-free">
                                        <i class="fas fa-gift"></i>
                                        <?php echo e(trans('app.Free')); ?>

                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if($product->description): ?>
                            <p class="user-product-description">
                                <?php echo e(Str::limit($product->description, 100)); ?>

                            </p>
                            <?php endif; ?>

                            <div class="user-product-badges">
                                <?php if($product->is_featured || $product->is_popular): ?>
                                <span class="user-badge user-badge-premium">
                                    <i class="fas fa-crown"></i>
                                    <?php echo e(trans('app.Premium')); ?>

                                </span>
                                <?php endif; ?>
                                <?php if($product->is_downloadable): ?>
                                <span class="user-badge user-badge-success">
                                    <i class="fas fa-download"></i>
                                    <?php echo e(trans('app.Downloadable')); ?>

                                </span>
                                <?php endif; ?>
                                <?php if($product->category): ?>
                                <span class="user-badge user-badge-primary">
                                    <i class="fas fa-folder"></i>
                                    <?php echo e($product->category->name); ?>

                                </span>
                                <?php endif; ?>
                                <?php if($product->programmingLanguage): ?>
                                <span class="user-badge user-badge-secondary">
                                    <i class="fas fa-code"></i>
                                    <?php echo e($product->programmingLanguage->name); ?>

                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="user-product-meta">
                                <?php if($product->updated_at): ?>
                                <div class="user-meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo e($product->updated_at->format('M d, Y')); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="user-product-actions">
                                <a href="<?php echo e(route('public.products.show', $product->slug)); ?>" class="user-product-button">
                                    <i class="fas fa-eye"></i>
                                    <?php echo e(trans('app.View Details')); ?>

                                </a>
                                <?php if($product->price > 0 && auth()->check()): ?>
                                    <a href="<?php echo e(route('payment.gateways', $product)); ?>" class="user-product-button primary">
                                        <i class="fas fa-shopping-cart"></i>
                                        <?php echo e(trans('app.Buy Now')); ?>

                                    </a>
                                <?php elseif($product->price > 0 && !auth()->check()): ?>
                                    <a href="<?php echo e(route('login')); ?>" class="user-product-button primary">
                                        <i class="fas fa-sign-in-alt"></i>
                                        <?php echo e(trans('app.Login to Buy')); ?>

                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <!-- Pagination -->
                    <?php if($products->hasPages()): ?>
                    <div class="user-pagination">
                        <?php echo e($products->links()); ?>

                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <!-- Empty State -->
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            <?php echo e(trans('app.No products found')); ?>

                        </h3>
                        <p class="user-empty-state-description">
                            <?php if(request('search') || request('category') || request('language') || request('price_filter')): ?>
                            <?php echo e(trans('app.No products match your current filters. Try adjusting your search criteria.')); ?>

                            <?php else: ?>
                            <?php echo e(trans('app.No products are currently available. Check back later for new products.')); ?>

                            <?php endif; ?>
                        </p>
                        <?php if(request('search') || request('category') || request('language') || request('price_filter')): ?>
                        <a href="<?php echo e(route('public.products.index')); ?>" class="user-btn user-btn-info">
                            <i class="fas fa-refresh"></i>
                            <?php echo e(trans('app.Clear Filters')); ?>

                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>


        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/user/products/index.blade.php ENDPATH**/ ?>