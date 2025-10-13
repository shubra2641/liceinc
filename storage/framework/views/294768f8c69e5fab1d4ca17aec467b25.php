<?php $__env->startSection('title', $product->meta_title ?? $product->name); ?>
<?php $__env->startSection('meta_description', $product->meta_description ?? Str::limit(strip_tags($product->description), 160)); ?>
<?php $__env->startSection('page-title', $product->name); ?>
<?php $__env->startSection('page-subtitle', trans('app.Product Details')); ?>

<?php if(!empty($product->meta_title)): ?>
<?php $__env->startSection('og:title', $product->meta_title); ?>
<?php endif; ?>
<?php if(!empty($product->meta_description)): ?>
<?php $__env->startSection('og:description', $product->meta_description); ?>
<?php endif; ?>
<?php if(!empty($product->image)): ?>
<?php $__env->startSection('og:image', Storage::url($product->image)); ?>
<?php endif; ?>


<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Product Header -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-box"></i>
                <?php echo e($product->name); ?>

                <?php if($product->is_featured || $product->is_popular): ?>
                <span class="user-premium-badge">
                    <i class="fas fa-crown"></i>
                    <?php echo e(trans('app.Premium')); ?>

                </span>
                <?php endif; ?>
            </div>
            <p class="user-card-subtitle"><?php echo e(trans('app.Product Details and Purchase Information')); ?></p>
        </div>
        <div class="user-card-content">
            <!-- Product Overview -->
            <div class="product-overview">
                <div class="product-main-info">
                    <div class="product-image-section">
                        <?php if($product->image): ?>
                        <img src="<?php echo e(Storage::url($product->image)); ?>" alt="<?php echo e($product->name); ?>" class="product-image">
                        <?php else: ?>
                        <div class="product-image-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-details">
                        <div class="product-badges">
                            <?php if($product->category): ?>
                            <span class="product-category-badge">
                                <i class="fas fa-tag"></i>
                                <?php echo e($product->category->name); ?>

                            </span>
                            <?php endif; ?>
                            <?php if($product->programmingLanguage): ?>
                            <span class="product-language-badge">
                                <i class="fas fa-code"></i>
                                <?php echo e($product->programmingLanguage->name); ?>

                            </span>
                            <?php endif; ?>
                            <span class="product-version-badge">
                                <i class="fas fa-tag"></i>
                                v<?php echo e($product->latest_version); ?>

                            </span>
                        </div>

                        <h1 class="product-title"><?php echo e($product->name); ?></h1>

                        <div class="product-meta">
                            <div class="product-meta-item">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo e(trans('app.Updated')); ?>: <?php echo e($product->updated_at->format('M d, Y')); ?></span>
                            </div>
                            <div class="product-meta-item">
                                <i class="fas fa-download"></i>
                                <span><?php echo e(trans('app.Downloads')); ?>: <?php echo e($licenseCount ?? 0); ?></span>
                            </div>
                            <div class="product-meta-item">
                                <i class="fas fa-star"></i>
                                <span><?php echo e(trans('app.Rating')); ?>: <?php echo e($product->rating ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <div class="product-description">
                            <?php echo e($product->description); ?>

                        </div>
                    </div>
                </div>

                <!-- Purchase Section -->
                <div class="product-purchase-section">
                    <div class="purchase-card">
                        <div class="purchase-header">
                            <div class="product-price">
                                <span class="price-currency">$</span>
                                <span class="price-amount"><?php echo e(number_format($product->price, 2)); ?></span>
                                <?php if($product->price > 0): ?>
                                <span class="price-period"><?php echo e($product->renewalPeriodLabel()); ?></span>
                                <?php else: ?>
                                <span class="price-free"><?php echo e(trans('app.Free')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="purchase-features">
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span><?php echo e(trans('app.Lifetime License')); ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span><?php echo e(trans('app.Free Updates')); ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span><?php echo e(trans('app.Premium Support')); ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span><?php echo e(trans('app.Source Code Included')); ?></span>
                            </div>
                        </div>

                        <div class="purchase-actions">
                            <?php if($product->price > 0): ?>
                            <?php if(auth()->check()): ?>
                            <?php if($userHasPurchasedBefore): ?>
                            <!-- User has purchased before, show Buy Again -->
                            <a href="<?php echo e(route('payment.gateways', $product)); ?>" class="purchase-button primary">
                                <i class="fas fa-redo"></i>
                                <?php echo e(trans('app.Buy Again')); ?>

                            </a>
                            <?php else: ?>
                            <!-- First time buyer, show Buy Now -->
                            <a href="<?php echo e(route('payment.gateways', $product)); ?>" class="purchase-button primary">
                                <i class="fas fa-shopping-cart"></i>
                                <?php echo e(trans('app.Buy Now')); ?>

                            </a>
                            <?php endif; ?>

                            <?php if($userOwnsProduct): ?>
                            <!-- User owns this product - show license management -->
                            <a href="<?php echo e(route('user.licenses.index')); ?>" class="purchase-button secondary">
                                <i class="fas fa-key"></i>
                                <?php echo e(trans('app.View Licenses')); ?>

                            </a>
                            <?php endif; ?>
                            <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>" class="purchase-button primary">
                                <i class="fas fa-sign-in-alt"></i>
                                <?php echo e(trans('app.Login to Buy')); ?>

                            </a>
                            <?php endif; ?>
                            <?php else: ?>
                            <!-- Free product -->
                            <?php if($userOwnsProduct): ?>
                            <!-- User owns this product - show license management -->
                            <a href="<?php echo e(route('user.licenses.index')); ?>" class="purchase-button primary">
                                <i class="fas fa-key"></i>
                                <?php echo e(trans('app.View Licenses')); ?>

                            </a>
                            <?php endif; ?>

                            <button class="purchase-button secondary" onclick="downloadProduct()">
                                <i class="fas fa-download"></i>
                                <?php echo e(trans('app.Download Free')); ?>

                            </button>
                            <?php endif; ?>

                            <?php if($userOwnsProduct && $product->is_downloadable): ?>
                            <?php if(isset($userCanDownload) && $userCanDownload): ?>
                            <a href="<?php echo e(route('user.products.files.index', $product)); ?>"
                                class="purchase-button secondary">
                                <i class="fas fa-download"></i>
                                <?php echo e(trans('app.Download Files')); ?>

                            </a>
                            <?php else: ?>
                            <button class="purchase-button secondary" disabled
                                title="<?php echo e($downloadMessage ?? trans('app.You must pay the invoice first')); ?>">
                                <i class="fas fa-download"></i>
                                <?php echo e(trans('app.Download Files')); ?>

                            </button>
                            <?php if(isset($downloadMessage) && $downloadMessage): ?>
                            <small class="text-warning d-block mt-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php echo e($downloadMessage); ?>

                            </small>
                            <?php endif; ?>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="purchase-guarantee">
                            <i class="fas fa-shield-alt"></i>
                            <span><?php echo e(trans('app.30-Day Money Back Guarantee')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features and Requirements/Installation Layout -->
    <div class="user-dashboard-grid">
        <!-- Features Section (Main Content) -->
        <div class="user-dashboard-main">
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-star"></i>
                        <?php echo e(trans('app.Features')); ?>

                    </div>
                    <p class="user-card-subtitle"><?php echo e(trans('app.Key features and capabilities of this product')); ?></p>
                </div>
                <div class="user-card-content">
                    <?php if($product->features && !empty($product->features)): ?>
                    <?php if(is_string($product->features)): ?>
                    <div class="features-content">
                        <?php echo e($product->features); ?>

                    </div>
                    <?php elseif(is_array($product->features)): ?>
                    <div class="user-features-list">
                        <?php $__currentLoopData = $product->features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="user-feature-item">
                            <div class="user-feature-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="user-feature-content">
                                <span class="user-feature-text"><?php echo e($feature); ?></span>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php else: ?>
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            <?php echo e(trans('app.No features available')); ?>

                        </h3>
                        <p class="user-empty-state-description">
                            <?php echo e(trans('app.Product features will be added soon')); ?>

                        </p>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            <?php echo e(trans('app.No features available')); ?>

                        </h3>
                        <p class="user-empty-state-description">
                            <?php echo e(trans('app.Product features will be added soon')); ?>

                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Requirements & Installation Sidebar -->
        <?php if(($product->requirements && !empty($product->requirements)) || ($product->installation_guide &&
        !empty($product->installation_guide))): ?>
        <div class="user-dashboard-sidebar">
            <!-- Installation Guide Section (Top) -->
            <?php if($product->installation_guide && !empty($product->installation_guide)): ?>
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-tools"></i>
                        <?php echo e(trans('app.Installation Guide')); ?>

                    </div>
                    <p class="user-card-subtitle"><?php echo e(trans('app.Step-by-step installation instructions')); ?></p>
                </div>
                <div class="user-card-content">
                    <?php if(is_string($product->installation_guide)): ?>
                    <?php if($product->installation_guide_has_html): ?>
                    <div class="installation-content">
                        <?php echo e($product->installation_guide); ?>

                    </div>
                    <?php else: ?>
                    <div class="installation-content">
                        <?php echo e(nl2br(e($product->installation_guide))); ?>

                    </div>
                    <?php endif; ?>
                    <?php elseif(is_array($product->installation_guide)): ?>
                    <div class="user-installation-steps">
                        <?php $__currentLoopData = $product->installation_guide; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="user-installation-step">
                            <div class="user-step-number"><?php echo e($index + 1); ?></div>
                            <div class="user-step-content">
                                <?php if(is_string($step)): ?>
                                <?php if(strip_tags($step) !== $step): ?>
                                <?php echo e(Purify::clean($step)); ?>

                                <?php else: ?>
                                <p><?php echo e(nl2br(e($step))); ?></p>
                                <?php endif; ?>
                                <?php else: ?>
                                <p><?php echo e($step); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php else: ?>
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h4 class="user-empty-state-title">
                            <?php echo e(trans('app.No installation guide available')); ?>

                        </h4>
                        <p class="user-empty-state-description">
                            <?php echo e(trans('app.Installation guide will be added soon')); ?>

                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Requirements Section (Bottom) -->
            <?php if($product->requirements && !empty($product->requirements)): ?>
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-cogs"></i>
                        <?php echo e(trans('app.Requirements')); ?>

                    </div>
                    <p class="user-card-subtitle"><?php echo e(trans('app.System requirements and dependencies')); ?></p>
                </div>
                <div class="user-card-content">
                    <?php if(is_string($product->requirements)): ?>
                    <?php if($product->requirements_has_html): ?>
                    <div class="requirements-content">
                        <?php echo e($product->requirements); ?>

                    </div>
                    <?php else: ?>
                    <div class="requirements-content">
                        <?php echo e(nl2br(e($product->requirements))); ?>

                    </div>
                    <?php endif; ?>
                    <?php elseif(is_array($product->requirements)): ?>
                    <div class="user-requirements-grid">
                        <?php $__currentLoopData = $product->requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="user-requirement-item">
                            <div class="user-requirement-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="user-requirement-content">
                                <span class="user-requirement-text"><?php echo e($requirement); ?></span>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php else: ?>
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h4 class="user-empty-state-title">
                            <?php echo e(trans('app.No requirements specified')); ?>

                        </h4>
                        <p class="user-empty-state-description">
                            <?php echo e(trans('app.Product requirements will be added soon')); ?>

                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Product Screenshots -->
    <?php if($product->screenshots && !empty($product->screenshots)): ?>
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-images"></i>
                <?php echo e(trans('app.Screenshots')); ?>

            </div>
        </div>
        <div class="user-card-content">
            <div class="screenshots-grid">
                <?php if(is_array($screenshots) && count($screenshots) > 0): ?>
                <?php $__currentLoopData = $screenshots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $screenshot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="screenshot-item">
                    <img src="<?php echo e(Storage::url($screenshot)); ?>" alt="<?php echo e($product->name); ?> Screenshot"
                        class="screenshot-image">
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                <p><?php echo e(trans('app.No screenshots available')); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Product Documentation -->
    <?php if($product->documentation && !empty($product->documentation)): ?>
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-book"></i>
                <?php echo e(trans('app.Documentation')); ?>

            </div>
        </div>
        <div class="user-card-content">
            <div class="documentation-content">
                <?php if(is_string($product->documentation)): ?>
                <?php echo e($product->documentation); ?>

                <?php else: ?>
                <p><?php echo e(trans('app.No documentation available')); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Related Products -->
    <?php if($relatedProducts->count() > 0): ?>
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-th-large"></i>
                <?php echo e(trans('app.Related Products')); ?>

            </div>
        </div>
        <div class="user-card-content">
            <div class="related-products-grid">
                <?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="related-product-card">
                    <div class="related-product-image">
                        <?php if($relatedProduct->image): ?>
                        <img src="<?php echo e(Storage::url($relatedProduct->image)); ?>" alt="<?php echo e($relatedProduct->name); ?>">
                        <?php else: ?>
                        <div class="related-product-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="related-product-info">
                        <div class="related-product-title-row">
                            <h3 class="related-product-title"><?php echo e($relatedProduct->name); ?></h3>
                            <?php if($relatedProduct->is_featured || $relatedProduct->is_popular): ?>
                            <span class="user-premium-badge">
                                <i class="fas fa-crown"></i>
                                <?php echo e(trans('app.Premium')); ?>

                            </span>
                            <?php endif; ?>
                        </div>
                        <p class="related-product-description"><?php echo e(Str::limit($relatedProduct->description, 100)); ?></p>
                        <div class="related-product-price">
                            <?php if($relatedProduct->price > 0): ?>
                            $<?php echo e(number_format($relatedProduct->price, 2)); ?>

                            <?php else: ?>
                            <?php echo e(trans('app.Free')); ?>

                            <?php endif; ?>
                        </div>
                        <a href="<?php echo e(route('public.products.show', $relatedProduct->slug)); ?>"
                            class="related-product-link">
                            <i class="fas fa-eye"></i>
                            <?php echo e(trans('app.View Details')); ?>

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
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/user/products/show.blade.php ENDPATH**/ ?>