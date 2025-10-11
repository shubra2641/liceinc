<?php $__env->startSection('admin-content'); ?>
<!-- Programming Language Show Page -->
<div class="admin-programming-languages-show">
    <!-- Page Header -->
    <div class="admin-page-header modern-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="flex-grow-1">
                <h1 class="gradient-text"><?php echo e($programmingLanguage->name); ?></h1>
                <p class="admin-page-subtitle"><?php echo e(__('app.programming_language_details')); ?></p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo e(route('admin.programming-languages.edit', $programmingLanguage)); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-edit me-2"></i>
                    <?php echo e(__('app.Edit')); ?>

                </a>
                <a href="<?php echo e(route('admin.programming-languages.index')); ?>" class="admin-btn admin-btn-secondary admin-btn-m">
                    <i class="fas fa-arrow-left me-2"></i>
                    <?php echo e(__('app.back_to_languages')); ?>

                </a>
            </div>
        </div>
    </div>

    <!-- Language Overview -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-code me-2"></i><?php echo e(__('app.language_overview')); ?></h2>
            <div class="admin-section-actions">
                <span class="admin-badge admin-badge-info"><?php echo e($programmingLanguage->name); ?></span>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-4">
                <!-- Quick Stats -->
                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-content">
                            <div class="d-flex align-items-center mb-3">
                                <?php if($programmingLanguage->icon): ?>
                                <i class="<?php echo e($programmingLanguage->icon); ?> admin-card-icon me-3"></i>
                                <?php else: ?>
                                <i class="fas fa-code admin-card-icon me-3"></i>
                                <?php endif; ?>
                                <div>
                                    <h4 class="admin-card-title"><?php echo e($programmingLanguage->name); ?></h4>
                                    <p class="admin-card-subtitle"><?php echo e(__('app.language_name')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-content">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-box admin-card-icon me-3"></i>
                                <div>
                                    <h4 class="admin-card-title"><?php echo e($programmingLanguage->products()->count()); ?></h4>
                                    <p class="admin-card-subtitle"><?php echo e(__('app.total_products')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-content">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle admin-card-icon me-3"></i>
                                <div>
                                    <h4 class="admin-card-title"><?php echo e($programmingLanguage->products()->where('is_active', true)->count()); ?></h4>
                                    <p class="admin-card-subtitle"><?php echo e(__('app.active_products')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-content">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-file-contract admin-card-icon me-3"></i>
                                <div>
                                    <h4 class="admin-card-title">
                                        <?php if($programmingLanguage->hasTemplateFile()): ?>
                                        <span class="admin-badge admin-badge-success"><?php echo e(__('app.custom')); ?></span>
                                        <?php else: ?>
                                        <span class="admin-badge admin-badge-info"><?php echo e(__('app.default')); ?></span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="admin-card-subtitle"><?php echo e(__('app.template_status')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="admin-section">
        <div class="admin-section-content">
            <nav class="admin-tabs-nav" role="tablist" aria-label="<?php echo e(__('app.navigation_tabs')); ?>">
                <button type="button" data-action="show-tab" data-tab="details-content" id="details-tab"
                    class="admin-tab-btn admin-tab-btn-active"
                    role="tab" aria-selected="true" aria-controls="details-content" tabindex="0">
                    <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                    <span><?php echo e(__('app.details')); ?></span>
                </button>
                <button type="button" data-action="show-tab" data-tab="template-content" id="template-tab"
                    class="admin-tab-btn"
                    role="tab" aria-selected="false" aria-controls="template-content" tabindex="-1">
                    <i class="fas fa-file-contract me-2" aria-hidden="true"></i>
                    <span><?php echo e(__('app.license_template')); ?></span>
                </button>
                <button type="button" data-action="show-tab" data-tab="products-content" id="products-tab"
                    class="admin-tab-btn"
                    role="tab" aria-selected="false" aria-controls="products-content" tabindex="-1">
                    <i class="fas fa-box me-2" aria-hidden="true"></i>
                    <span><?php echo e(__('app.related_products')); ?></span>
                </button>
            </nav>
            <noscript>
                <div class="admin-alert admin-alert-info mt-4">
                    <div class="admin-alert-content">
                        <i class="fas fa-info-circle admin-alert-icon"></i>
                        <div>
                            <h4 class="admin-alert-title"><?php echo e(trans('app.javascript_required')); ?></h4>
                            <p class="admin-alert-message"><?php echo e(trans('app.tab_navigation_requires_javascript')); ?></p>
                        </div>
                    </div>
                </div>
            </noscript>
        </div>
    </div>

    <!-- Details Tab Content -->
    <div id="details-content" class="admin-tab-panel" role="tabpanel" aria-labelledby="details-tab" aria-hidden="false">
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-info-circle me-2"></i><?php echo e(__('app.language_details')); ?></h3>
                <div class="admin-section-actions">
                    <a href="<?php echo e(route('admin.programming-languages.edit', $programmingLanguage)); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                        <i class="fas fa-edit me-2"></i>
                        <?php echo e(__('app.Edit')); ?>

                    </a>
                </div>
            </div>
            <div class="admin-section-content">
                <!-- Language Information Grid -->
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="admin-card">
                            <div class="admin-card-content">
                                <div class="d-flex align-items-center mb-3">
                                    <?php if($programmingLanguage->icon): ?>
                                    <i class="<?php echo e($programmingLanguage->icon); ?> admin-card-icon me-3"></i>
                                    <?php else: ?>
                                    <i class="fas fa-code admin-card-icon me-3"></i>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h4 class="admin-card-title"><?php echo e($programmingLanguage->name); ?></h4>
                                        <p class="admin-card-subtitle"><?php echo e($programmingLanguage->slug); ?></p>
                                    </div>
                                    <div>
                                        <?php if($programmingLanguage->is_active): ?>
                                        <span class="admin-badge admin-badge-success">
                                            <i class="fas fa-check-circle me-1"></i><?php echo e(__('app.Active')); ?>

                                        </span>
                                        <?php else: ?>
                                        <span class="admin-badge admin-badge-secondary">
                                            <i class="fas fa-pause-circle me-1"></i><?php echo e(__('app.inactive')); ?>

                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if($programmingLanguage->description): ?>
                                <p class="admin-card-text"><?php echo e(Str::limit($programmingLanguage->description, 200)); ?></p>
                                <?php endif; ?>

                                <div class="admin-info-list">
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.extension')); ?>:</span>
                                        <span class="admin-info-value"><?php echo e($programmingLanguage->file_extension ?: __('not_specified')); ?></span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.Products')); ?>:</span>
                                        <span class="admin-info-value"><?php echo e($programmingLanguage->products()->count()); ?></span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.sort_order')); ?>:</span>
                                        <span class="admin-info-value">#<?php echo e($programmingLanguage->sort_order); ?></span>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mt-3">
                                    <a href="<?php echo e(route('admin.programming-languages.edit', $programmingLanguage)); ?>" 
                                       class="admin-btn admin-btn-primary admin-btn-sm flex-grow-1">
                                        <i class="fas fa-edit me-1"></i><?php echo e(__('app.Edit')); ?>

                                    </a>
                                    <a href="<?php echo e(route('admin.programming-languages.index')); ?>" 
                                       class="admin-btn admin-btn-secondary admin-btn-sm">
                                        <i class="fas fa-arrow-left me-1"></i><?php echo e(__('app.back_to_languages')); ?>

                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Language Statistics -->
                        <div class="admin-card">
                            <div class="admin-card-content">
                                <h5 class="admin-card-title mb-3"><?php echo e(__('app.statistics')); ?></h5>
                                <div class="admin-info-list">
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.total_products')); ?>:</span>
                                        <span class="admin-info-value"><?php echo e($programmingLanguage->products()->count()); ?></span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.active_products')); ?>:</span>
                                        <span class="admin-info-value"><?php echo e($programmingLanguage->products()->where('is_active', true)->count()); ?></span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.template_status')); ?>:</span>
                                        <span class="admin-info-value">
                                            <?php if($programmingLanguage->hasTemplateFile()): ?>
                                            <span class="admin-badge admin-badge-success"><?php echo e(__('app.custom')); ?></span>
                                            <?php else: ?>
                                            <span class="admin-badge admin-badge-info"><?php echo e(__('app.default')); ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.Created_at')); ?>:</span>
                                        <span class="admin-info-value"><?php echo e($programmingLanguage->created_at->format('M d, Y')); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="admin-card">
                            <div class="admin-card-content">
                                <h5 class="admin-card-title mb-3"><?php echo e(__('app.quick_actions')); ?></h5>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo e(route('admin.programming-languages.edit', $programmingLanguage)); ?>" class="admin-btn admin-btn-primary admin-btn-sm">
                                        <i class="fas fa-edit me-2"></i><?php echo e(__('app.Edit')); ?>

                                    </a>
                                    <a href="<?php echo e(route('admin.programming-languages.create')); ?>" class="admin-btn admin-btn-success admin-btn-sm">
                                        <i class="fas fa-plus me-2"></i><?php echo e(__('app.add_new_language')); ?>

                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Tab Content -->
    <div id="template-content" class="admin-tab-panel admin-tab-panel-hidden" role="tabpanel" aria-labelledby="template-tab" aria-hidden="true">
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-file-contract me-2"></i><?php echo e(__('app.license_template')); ?></h3>
                <div class="admin-section-actions">
                    <a href="<?php echo e(route('admin.programming-languages.edit', $programmingLanguage)); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                        <i class="fas fa-edit me-2"></i>
                        <?php echo e(__('app.Edit')); ?>

                    </a>
                </div>
            </div>
            <div class="admin-section-content">
                <?php if($programmingLanguage->license_template): ?>
                <div class="admin-card">
                    <div class="admin-card-content">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-file-contract admin-card-icon me-3"></i>
                            <div class="flex-grow-1">
                                <h4 class="admin-card-title"><?php echo e(__('app.license_template')); ?></h4>
                                <p class="admin-card-subtitle"><?php echo e(__('app.custom_license_verification_template')); ?></p>
                            </div>
                            <span class="admin-badge admin-badge-success">
                                <i class="fas fa-check-circle me-1"></i><?php echo e(__('app.custom')); ?>

                            </span>
                        </div>

                        <div class="admin-code-block">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-white">
                                    <i class="fas fa-code me-2"></i><?php echo e(__('app.template_code')); ?>

                                </h5>
                                <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" data-copy-target="template-code">
                                    <i class="fas fa-copy me-1"></i><?php echo e(__('app.copy')); ?>

                                </button>
                            </div>
                            <pre class="admin-code-pre" id="template-code"><?php echo e($programmingLanguage->license_template); ?></pre>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="admin-card">
                    <div class="admin-card-content">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-file-contract admin-card-icon me-3"></i>
                            <div class="flex-grow-1">
                                <h4 class="admin-card-title"><?php echo e(__('app.license_template')); ?></h4>
                                <p class="admin-card-subtitle"><?php echo e(__('app.no_custom_template_available')); ?></p>
                            </div>
                            <span class="admin-badge admin-badge-info">
                                <i class="fas fa-info-circle me-1"></i><?php echo e(__('app.default')); ?>

                            </span>
                        </div>

                        <div class="admin-empty-state">
                            <div class="admin-empty-state-content">
                                <i class="fas fa-file-contract admin-empty-state-icon"></i>
                                <h4 class="admin-empty-state-title"><?php echo e(__('app.No_template_available')); ?></h4>
                                <p class="admin-empty-state-description"><?php echo e(__('app.create_template_to_get_started')); ?></p>
                                <a href="<?php echo e(route('admin.programming-languages.edit', $programmingLanguage)); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                                    <i class="fas fa-plus me-2"></i>
                                    <?php echo e(__('app.create_template')); ?>

                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Products Tab Content -->
    <div id="products-content" class="admin-tab-panel admin-tab-panel-hidden" role="tabpanel" aria-labelledby="products-tab" aria-hidden="true">
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-box me-2"></i><?php echo e(__('app.related_products')); ?></h3>
                <div class="admin-section-actions">
                    <span class="admin-badge admin-badge-info"><?php echo e($programmingLanguage->products()->count()); ?> <?php echo e(__('app.products')); ?></span>
                </div>
            </div>
            <div class="admin-section-content">
                <?php if($programmingLanguage->products()->count() > 0): ?>
                <div class="row g-4">
                    <?php $__currentLoopData = $programmingLanguage->products()->take(6)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="admin-card">
                            <div class="admin-card-content">
                                <div class="d-flex align-items-center mb-3">
                                    <?php if($product->image): ?>
                                    <img src="<?php echo e(Storage::url($product->image)); ?>" alt="<?php echo e($product->name); ?>" class="product-image me-3">
                                    <?php else: ?>
                                    <div class="product-avatar me-3">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h4 class="admin-card-title"><?php echo e($product->name); ?></h4>
                                        <p class="admin-card-subtitle"><?php echo e(Str::limit($product->description, 50)); ?></p>
                                    </div>
                                    <div>
                                        <?php if($product->is_active): ?>
                                        <span class="admin-badge admin-badge-success">
                                            <i class="fas fa-check-circle me-1"></i><?php echo e(__('app.Active')); ?>

                                        </span>
                                        <?php else: ?>
                                        <span class="admin-badge admin-badge-secondary">
                                            <i class="fas fa-pause-circle me-1"></i><?php echo e(__('app.inactive')); ?>

                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo e(route('admin.products.show', $product)); ?>" class="admin-btn admin-btn-primary admin-btn-sm flex-grow-1">
                                        <i class="fas fa-eye me-1"></i><?php echo e(__('app.View')); ?>

                                    </a>
                                    <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
                                        <i class="fas fa-edit me-1"></i><?php echo e(__('app.Edit')); ?>

                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                
                <?php if($programmingLanguage->products()->count() > 6): ?>
                <div class="text-center mt-4">
                    <a href="<?php echo e(route('admin.products.index', ['programming_language' => $programmingLanguage->id])); ?>" class="admin-btn admin-btn-outline-primary admin-btn-m">
                        <i class="fas fa-eye me-2"></i>
                        <?php echo e(__('app.view_all_products')); ?>

                    </a>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="admin-card">
                    <div class="admin-card-content">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-box admin-card-icon me-3"></i>
                            <div class="flex-grow-1">
                                <h4 class="admin-card-title"><?php echo e(__('app.related_products')); ?></h4>
                                <p class="admin-card-subtitle"><?php echo e(__('app.no_products_using_language')); ?></p>
                            </div>
                            <span class="admin-badge admin-badge-secondary">
                                <i class="fas fa-info-circle me-1"></i><?php echo e(__('app.empty')); ?>

                            </span>
                        </div>

                        <div class="admin-empty-state">
                            <div class="admin-empty-state-content">
                                <i class="fas fa-box admin-empty-state-icon"></i>
                                <h4 class="admin-empty-state-title"><?php echo e(__('app.No_products_yet')); ?></h4>
                                <p class="admin-empty-state-description"><?php echo e(__('app.No_products_using_language')); ?></p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="<?php echo e(route('admin.products.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                                        <i class="fas fa-plus me-2"></i>
                                        <?php echo e(__('app.create_product')); ?>

                                    </a>
                                    <a href="<?php echo e(route('admin.products.index')); ?>" class="admin-btn admin-btn-secondary admin-btn-m">
                                        <i class="fas fa-list me-2"></i>
                                        <?php echo e(__('app.view_all_products')); ?>

                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\programming-languages\show.blade.php ENDPATH**/ ?>