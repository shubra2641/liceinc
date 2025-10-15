<?php $__env->startSection('admin-content'); ?>
<!-- Programming Languages Index Page -->
<div class="admin-programming-languages-index">
    <!-- Page Header -->
    <div class="admin-page-header modern-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="flex-grow-1">
                <h1 class="gradient-text"><?php echo e(__('app.programming_languages')); ?></h1>
                    <p class="admin-page-subtitle"><?php echo e(__('app.manage_programming_languages')); ?></p>
                </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo e(route('admin.programming-languages.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-plus me-2"></i>
                            <?php echo e(__('app.new_programming_language')); ?>

                        </a>
                        <button type="button" class="admin-btn admin-btn-success admin-btn-m" data-action="reload-page">
                    <i class="fas fa-sync-alt me-2"></i>
                            <?php echo e(__('app.refresh')); ?>

                        </button>
                        <noscript>
                    <a href="<?php echo e(route('admin.programming-languages.index')); ?>" class="admin-btn admin-btn-success admin-btn-m">
                        <i class="fas fa-sync-alt me-2"></i>
                                <?php echo e(__('app.refresh')); ?>

                            </a>
                        </noscript>
                </div>
            </div>
        </div>

    <!-- Status Messages -->

        <?php if(session('error')): ?>
    <div class="admin-alert admin-alert-error">
        <div class="admin-alert-content">
            <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
            <div class="admin-alert-text">
                <h4><?php echo e(__('app.error')); ?></h4>
                <p><?php echo e(session('error')); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

    <!-- Enhanced Statistics Section -->
    <div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
        <!-- Total Languages Stats Card -->
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
                    <div class="stats-card-value"><?php echo e($languages->count()); ?></div>
                    <div class="stats-card-label"><?php echo e(__('app.Total_languages')); ?></div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span><?php echo e($languages->where('is_active', true)->count()); ?> <?php echo e(__('app.active')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Languages Stats Card -->
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
                    <div class="stats-card-value"><?php echo e($languages->where('is_active', true)->count()); ?></div>
                    <div class="stats-card-label"><?php echo e(__('app.Active_languages')); ?></div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span><?php echo e(number_format(($languages->where('is_active', true)->count() / max($languages->count(), 1)) * 100, 1)); ?>% <?php echo e(__('app.of_total')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inactive Languages Stats Card -->
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
                    <div class="stats-card-value"><?php echo e($languages->where('is_active', false)->count()); ?></div>
                    <div class="stats-card-label"><?php echo e(__('app.inactive_languages')); ?></div>
                    <div class="stats-card-trend negative">
                        <i class="stats-trend-icon negative"></i>
                        <span><?php echo e(number_format(($languages->where('is_active', false)->count() / max($languages->count(), 1)) * 100, 1)); ?>% <?php echo e(__('app.of_total')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Products Stats Card -->
        <div class="stats-card stats-card-info animate-slide-up animate-delay-400">
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
                    <div class="stats-card-value"><?php echo e($languages->sum(function($language) { return $language->products()->count(); })); ?></div>
                    <div class="stats-card-label"><?php echo e(__('app.Total_products')); ?></div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span><?php echo e(__('app.across_all_languages')); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="admin-section">
        <div class="admin-section-content">
            <nav class="admin-tabs-nav" role="tablist" aria-label="<?php echo e(__('app.navigation_tabs')); ?>">
                <button type="button" data-action="show-tab" data-tab="languages-content" id="languages-tab"
                    class="admin-tab-btn admin-tab-btn-active"
                    role="tab" aria-selected="true" aria-controls="languages-content" tabindex="0">
                    <i class="fas fa-code me-2" aria-hidden="true"></i>
                    <span><?php echo e(__('app.languages')); ?></span>
                </button>
                <button type="button" data-action="show-tab" data-tab="templates-content" id="templates-tab"
                    class="admin-tab-btn"
                    role="tab" aria-selected="false" aria-controls="templates-content" tabindex="-1">
                    <i class="fas fa-file-contract me-2" aria-hidden="true"></i>
                    <span><?php echo e(__('app.license_templates')); ?></span>
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

    <!-- Languages Tab Content -->
    <div id="languages-content" class="admin-tab-panel" role="tabpanel" aria-labelledby="languages-tab" aria-hidden="false">
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-list me-2"></i><?php echo e(__('app.language_details')); ?></h3>
                <div class="admin-section-actions">
                    <button type="button" class="admin-btn admin-btn-success admin-btn-m" data-action="export-languages">
                        <i class="fas fa-download me-2"></i>
                        <?php echo e(__('app.export')); ?>

                    </button>
                    <noscript>
                        <a href="<?php echo e(route('admin.programming-languages.export')); ?>" class="admin-btn admin-btn-success admin-btn-m">
                            <i class="fas fa-download me-2"></i>
                            <?php echo e(__('app.export')); ?>

                        </a>
                    </noscript>
                </div>
            </div>
            <div class="admin-section-content">
                <!-- Search and Filters -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="admin-form-group">
                                        <label class="admin-form-label" for="search-input">
                                <i class="fas fa-search me-1"></i><?php echo e(__('app.search_languages')); ?>

                                        </label>
                                        <input type="text" id="search-input" class="admin-form-input"
                                            placeholder="<?php echo e(__('app.search_by_name_or_extension')); ?>">
                        </div>
                                    </div>

                    <div class="col-md-4">
                        <div class="admin-form-group">
                                        <label class="admin-form-label" for="status-filter">
                                <i class="fas fa-filter me-1"></i><?php echo e(__('app.Status')); ?>

                                        </label>
                                        <select id="status-filter" class="admin-form-input">
                                            <option value=""><?php echo e(__('app.all_languages')); ?></option>
                                            <option value="active"><?php echo e(__('app.Active_only')); ?></option>
                                            <option value="inactive"><?php echo e(__('app.inactive_only')); ?></option>
                                        </select>
                        </div>
                                    </div>

                    <div class="col-md-4">
                        <div class="admin-form-group">
                                        <label class="admin-form-label" for="sort-filter">
                                <i class="fas fa-sort me-1"></i><?php echo e(__('app.sort_by')); ?>

                                        </label>
                                        <select id="sort-filter" class="admin-form-input">
                                            <option value="name"><?php echo e(__('app.Name')); ?></option>
                                            <option value="sort_order"><?php echo e(__('app.sort_order')); ?></option>
                                            <option value="products_count"><?php echo e(__('app.Products_count')); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                <!-- Languages Grid -->
                <div class="row g-4">
                                <?php $__empty_1 = true; $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="admin-card">
                            <div class="admin-card-content">
                                <div class="d-flex align-items-center mb-3">
                                            <?php if($language->icon): ?>
                                    <i class="<?php echo e($language->icon); ?> admin-card-icon me-3"></i>
                                            <?php else: ?>
                                    <i class="fas fa-code admin-card-icon me-3"></i>
                                            <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h4 class="admin-card-title"><?php echo e($language->name); ?></h4>
                                        <p class="admin-card-subtitle"><?php echo e($language->slug); ?></p>
                                        </div>
                                    <div>
                                            <?php if($language->is_active): ?>
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

                                        <?php if($language->description): ?>
                                <p class="admin-card-text"><?php echo e(Str::limit($language->description, 100)); ?></p>
                                        <?php endif; ?>

                                <div class="admin-info-list">
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.extension')); ?>:</span>
                                        <span class="admin-info-value"><?php echo e($language->file_extension ?: __('not_specified')); ?></span>
                                            </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.Products')); ?>:</span>
                                        <span class="admin-info-value"><?php echo e($language->products()->count()); ?></span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.sort_order')); ?>:</span>
                                        <span class="admin-info-value">#<?php echo e($language->sort_order); ?></span>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mt-3">
                                    <a href="<?php echo e(route('admin.programming-languages.edit', $language)); ?>" 
                                       class="admin-btn admin-btn-primary admin-btn-sm flex-grow-1">
                                        <i class="fas fa-edit me-1"></i><?php echo e(__('app.Edit')); ?>

                                    </a>
                                    <a href="<?php echo e(route('admin.programming-languages.show', $language)); ?>" 
                                       class="admin-btn admin-btn-secondary admin-btn-sm">
                                        <i class="fas fa-eye me-1"></i><?php echo e(__('app.View')); ?>

                                    </a>
                                </div>
                            </div>
                        </div>
                            </div>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12">
                        <div class="admin-empty-state">
                            <div class="admin-empty-state-content">
                                <i class="fas fa-code admin-empty-state-icon"></i>
                                <h4 class="admin-empty-state-title"><?php echo e(__('app.No_programming_languages')); ?></h4>
                                <p class="admin-empty-state-description"><?php echo e(__('app.get_started_create_first_language')); ?></p>
                                <a href="<?php echo e(route('admin.programming-languages.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                                    <i class="fas fa-plus me-2"></i>
                                    <?php echo e(__('app.create_language')); ?>

                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                                    </div>

                <!-- Pagination -->
                <div class="admin-pagination">
                    <?php echo e($languages->links()); ?>

                                    </div>
                                </div>
                            </div>
                        </div>

    <!-- Templates Tab Content -->
    <div id="templates-content" class="admin-tab-panel admin-tab-panel-hidden" role="tabpanel" aria-labelledby="templates-tab">
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-file-contract me-2"></i><?php echo e(__('app.license_templates')); ?></h3>
                <div class="admin-section-actions">
                    <button type="button" class="admin-btn admin-btn-success admin-btn-m" data-action="refresh-templates">
                        <i class="fas fa-sync-alt me-2"></i>
                        <?php echo e(__('app.refresh')); ?>

                    </button>
                    <noscript>
                        <a href="<?php echo e(route('admin.programming-languages.index')); ?>" class="admin-btn admin-btn-success admin-btn-m">
                            <i class="fas fa-sync-alt me-2"></i>
                            <?php echo e(__('app.refresh')); ?>

                        </a>
                    </noscript>
                </div>
            </div>
            <div class="admin-section-content">
                <!-- Templates Grid -->
                <div class="row g-4">
                    <?php $__empty_1 = true; $__currentLoopData = $availableTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $templateName => $templateInfo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="admin-card">
                            <div class="admin-card-content">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-file-contract admin-card-icon me-3"></i>
                                    <div class="flex-grow-1">
                                        <h4 class="admin-card-title"><?php echo e(ucfirst($templateName)); ?></h4>
                                        <p class="admin-card-subtitle"><?php echo e($templateName); ?>.blade.php</p>
                                            </div>
                                    <span class="admin-badge admin-badge-success">
                                        <i class="fas fa-check-circle me-1"></i><?php echo e(__('app.exists')); ?>

                                    </span>
                                            </div>

                                <div class="admin-info-list">
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.file_size')); ?>:</span>
                                        <span class="admin-info-value"><?php echo e(number_format($templateInfo['file_size'] / 1024, 1)); ?> KB</span>
                                            </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.last_modified')); ?>:</span>
                                        <span class="admin-info-value"><?php echo e(\Carbon\Carbon::parse($templateInfo['last_modified'])->format('M d, Y')); ?></span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label"><?php echo e(__('app.template_type')); ?>:</span>
                                        <span class="admin-info-value"><?php echo e(trans('app.Blade Template')); ?></span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" data-action="view-template" data-template="<?php echo e($templateName); ?>"
                                        class="admin-btn admin-btn-primary admin-btn-sm flex-grow-1">
                                        <i class="fas fa-eye me-1"></i><?php echo e(__('app.view')); ?>

                                        </button>
                                    <button type="button" data-action="edit-template" data-template="<?php echo e($templateName); ?>"
                                        class="admin-btn admin-btn-secondary admin-btn-sm">
                                        <i class="fas fa-edit me-1"></i><?php echo e(__('app.Edit')); ?>

                                        </button>
                                </div>
                                        </div>
                                    </div>
                                </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12">
                        <div class="admin-empty-state">
                            <div class="admin-empty-state-content">
                                <i class="fas fa-file-contract admin-empty-state-icon"></i>
                                <h4 class="admin-empty-state-title"><?php echo e(__('app.No_templates_found')); ?></h4>
                                <p class="admin-empty-state-description"><?php echo e(__('app.create_first_template_to_get_started')); ?></p>
                                <button type="button" data-action="create-template" class="admin-btn admin-btn-primary admin-btn-m">
                                    <i class="fas fa-plus me-2"></i>
                                    <?php echo e(__('app.create_new_template')); ?>

                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
                <?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/admin/programming-languages/index.blade.php ENDPATH**/ ?>