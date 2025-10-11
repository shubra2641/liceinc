<?php $__env->startSection('admin-content'); ?>
<div class="admin-programming-languages-edit admin-programming-languages-show" data-language-id="<?php echo e($programming_language->id); ?>" data-language-slug="<?php echo e($programming_language->slug); ?>">
    <!-- Page Header -->
    <div class="admin-page-header modern-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="flex-grow-1">
                <h1 class="gradient-text"><?php echo e(__('app.Edit_programming_language')); ?></h1>
                <p class="admin-page-subtitle"><?php echo e(__('app.modify_language_settings_and_templates')); ?></p>
            </div>
            <div class="d-flex align-items-center gap-2">
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
            <h2><i class="fas fa-info-circle me-2"></i><?php echo e(__('app.language_overview')); ?></h2>
            <div class="admin-section-actions">
                <?php if($programming_language->is_active): ?>
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
        <div class="admin-section-content">
            <div class="row g-4">
                <!-- Language Info Card -->
                <div class="col-md-4">
                    <div class="stats-card stats-card-neutral animate-slide-up">
                        <div class="stats-card-background">
                            <div class="stats-card-pattern"></div>
                        </div>
                        <div class="stats-card-content">
                            <div class="stats-card-header">
                                <div class="stats-card-icon language"></div>
                                <div class="stats-card-menu">
                                    <button class="stats-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="stats-card-body">
                                <div class="stats-card-value">
                                    <?php if($programming_language->icon): ?>
                                        <i class="<?php echo e($programming_language->icon); ?> text-primary"></i>
                                    <?php else: ?>
                                        <i class="fas fa-code text-primary"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="stats-card-label"><?php echo e($programming_language->name); ?></div>
                                <div class="stats-card-trend positive">
                                    <i class="stats-trend-icon positive"></i>
                                    <span><?php echo e($programming_language->slug); ?> • <?php echo e($programming_language->file_extension ?? 'N/A'); ?></span>
                                </div>
                                <?php if($programming_language->description): ?>
                                <div class="stats-card-subvalue mt-2">
                                    <small class="text-muted"><?php echo e(Str::limit($programming_language->description, 80)); ?></small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Statistics Card -->
                <div class="col-md-4">
                    <div class="stats-card stats-card-success animate-slide-up animate-delay-100">
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
                                <div class="stats-card-value"><?php echo e($programming_language->products()->count()); ?></div>
                                <div class="stats-card-label"><?php echo e(__('app.Total_products')); ?></div>
                                <div class="stats-card-trend positive">
                                    <i class="stats-trend-icon positive"></i>
                                    <span><?php echo e($programming_language->products()->where('is_active', true)->count()); ?> <?php echo e(__('app.Active')); ?></span>
                                </div>
                                <?php if($programming_language->products()->count() > 0): ?>
                                <div class="stats-card-subvalue mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-chart-line me-1"></i><?php echo e(__('app.products_using_language')); ?>

                                    </small>
                                </div>
                                <?php else: ?>
                                <div class="stats-card-subvalue mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i><?php echo e(__('app.no_products_yet')); ?>

                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Template Status Card -->
                <div class="col-md-4">
                    <div class="stats-card stats-card-warning animate-slide-up animate-delay-200">
                        <div class="stats-card-background">
                            <div class="stats-card-pattern"></div>
                        </div>
                        <div class="stats-card-content">
                            <div class="stats-card-header">
                                <div class="stats-card-icon template"></div>
                                <div class="stats-card-menu">
                                    <button class="stats-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="stats-card-body">
                                <div class="stats-card-value">
                                    <?php if($programming_language->hasTemplateFile()): ?>
                                        <i class="fas fa-check-circle text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-file-alt text-warning"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="stats-card-label"><?php echo e(__('app.template_status')); ?></div>
                                <div class="stats-card-trend <?php echo e($programming_language->hasTemplateFile() ? 'positive' : 'neutral'); ?>">
                                    <i class="stats-trend-icon <?php echo e($programming_language->hasTemplateFile() ? 'positive' : 'neutral'); ?>"></i>
                                    <span>
                                        <?php if($programming_language->hasTemplateFile()): ?>
                                            <?php echo e(__('app.custom_template')); ?>

                                        <?php else: ?>
                                            <?php echo e(__('app.default_template')); ?>

                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if($programming_language->hasTemplateFile()): ?>
                                <div class="stats-card-subvalue mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-file-code me-1"></i><?php echo e(__('app.template_configured')); ?>

                                    </small>
                                </div>
                                <?php else: ?>
                                <div class="stats-card-subvalue mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i><?php echo e(__('app.using_default_template')); ?>

                                    </small>
                                </div>
                                <?php endif; ?>
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
                <button type="button" data-action="show-tab" data-tab="basic-content" id="basic-tab"
                    class="admin-tab-btn admin-tab-btn-active"
                    role="tab" aria-selected="true" aria-controls="basic-content" tabindex="0">
                    <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                    <span><?php echo e(__('app.Basic_Information')); ?></span>
                </button>
                <button type="button" data-action="show-tab" data-tab="template-content" id="template-tab"
                    class="admin-tab-btn"
                    role="tab" aria-selected="false" aria-controls="template-content" tabindex="-1">
                    <i class="fas fa-file-contract me-2" aria-hidden="true"></i>
                    <span><?php echo e(__('app.license_template')); ?></span>
                </button>
                <button type="button" data-action="show-tab" data-tab="usage-content" id="usage-tab"
                    class="admin-tab-btn"
                    role="tab" aria-selected="false" aria-controls="usage-content" tabindex="-1">
                    <i class="fas fa-chart-bar me-2" aria-hidden="true"></i>
                    <span><?php echo e(__('app.usage_stats')); ?></span>
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

    <!-- Basic Information Tab -->
    <div id="basic-content" class="admin-tab-panel" role="tabpanel" aria-labelledby="basic-tab">
        <form method="post" action="<?php echo e(route('admin.programming-languages.update', $programming_language)); ?>"
            id="basic-form" class="needs-validation" novalidate>
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="row">
                <!-- Main Form Section -->
                <div class="col-lg-8">
                    <!-- Basic Details -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3><i class="fas fa-edit me-2"></i><?php echo e(__('app.basic_details')); ?></h3>
                            <span class="admin-badge admin-badge-danger"><?php echo e(__('app.Required')); ?></span>
                        </div>
                        <div class="admin-section-content">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="name">
                                            <i class="fas fa-code me-1"></i><?php echo e(__('app.language_name')); ?>

                                        </label>
                                        <input type="text" id="name" name="name" class="admin-form-input"
                                            value="<?php echo e(old('app.Name', $programming_language->name)); ?>" required
                                            placeholder="<?php echo e(__('app.enter_language_name')); ?>">
                                        <?php $__errorArgs = ['app.Name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="slug">
                                            <i class="fas fa-link me-1"></i><?php echo e(__('app.Slug')); ?>

                                        </label>
                                        <input type="text" id="slug" name="slug" class="admin-form-input"
                                            value="<?php echo e(old('app.Slug', $programming_language->slug)); ?>"
                                            placeholder="<?php echo e(__('app.auto_generated_from_name')); ?>">
                                        <small class="admin-form-help"><?php echo e(__('app.leave_empty_auto_generate')); ?></small>
                                        <?php $__errorArgs = ['app.Slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="file_extension">
                                            <i class="fas fa-file-code me-1"></i><?php echo e(__('app.file_extension')); ?>

                                        </label>
                                        <input type="text" id="file_extension" name="file_extension"
                                            class="admin-form-input"
                                            value="<?php echo e(old('app.file_extension', $programming_language->file_extension)); ?>"
                                            placeholder="php, js, py, java, cs, cpp">
                                        <?php $__errorArgs = ['app.file_extension'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="icon">
                                            <i class="fas fa-icons me-1"></i><?php echo e(__('app.icon_class')); ?>

                                        </label>
                                        <div class="input-group">
                                            <input type="text" id="icon" name="icon" class="admin-form-input"
                                                value="<?php echo e(old('icon', $programming_language->icon)); ?>"
                                                placeholder="fab fa-php, fas fa-code">
                                            <span class="input-group-text">
                                                <i id="icon-preview" class="<?php echo e($programming_language->icon ?: 'fas fa-code'); ?>"></i>
                                            </span>
                                        </div>
                                        <small class="admin-form-help"><?php echo e(__('app.fontawesome_icon_class')); ?></small>
                                        <?php $__errorArgs = ['icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="description">
                                            <i class="fas fa-align-left me-1"></i><?php echo e(__('app.Description')); ?>

                                        </label>
                                        <textarea id="description" name="description" class="admin-form-textarea" rows="4"
                                            placeholder="<?php echo e(__('app.brief_description_language')); ?>"><?php echo e(old('app.Description', $programming_language->description)); ?></textarea>
                                        <?php $__errorArgs = ['app.Description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Settings -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3><i class="fas fa-cogs me-2"></i><?php echo e(__('app.advanced_settings')); ?></h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="sort_order">
                                            <i class="fas fa-sort-numeric-up me-1"></i><?php echo e(__('app.sort_order')); ?>

                                        </label>
                                        <input type="number" id="sort_order" name="sort_order" class="admin-form-input"
                                            value="<?php echo e(old('app.sort_order', $programming_language->sort_order)); ?>" min="0">
                                        <?php $__errorArgs = ['app.sort_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                                <?php echo e(old('is_active', $programming_language->is_active) ? 'checked' : ''); ?>

                                                class="form-check-input">
                                            <label class="form-check-label" for="is_active">
                                                <i class="fas fa-toggle-on me-1"></i><?php echo e(__('app.Active_language')); ?>

                                            </label>
                                        </div>
                                        <small class="admin-form-help"><?php echo e(__('app.language_will_be_available')); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Information -->
                <div class="col-lg-4">
                    <!-- Quick Stats -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3><i class="fas fa-chart-pie me-2"></i><?php echo e(__('app.quick_stats')); ?></h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="admin-stats-grid">
                                <div class="admin-stat-item">
                                    <div class="admin-stat-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="admin-stat-content">
                                        <div class="admin-stat-value"><?php echo e($programming_language->products()->count()); ?></div>
                                        <div class="admin-stat-label"><?php echo e(__('app.Products')); ?></div>
                                    </div>
                                </div>

                                <div class="admin-stat-item">
                                    <div class="admin-stat-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="admin-stat-content">
                                        <div class="admin-stat-value"><?php echo e($programming_language->products()->where('is_active', true)->count()); ?></div>
                                        <div class="admin-stat-label"><?php echo e(__('app.Active_products')); ?></div>
                                    </div>
                                </div>

                                <div class="admin-stat-item">
                                    <div class="admin-stat-icon">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <div class="admin-stat-content">
                                        <div class="admin-stat-value"><?php echo e($programming_language->products()->withCount('licenses')->get()->sum('licenses_count')); ?></div>
                                        <div class="admin-stat-label"><?php echo e(__('app.Licenses')); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3><i class="fas fa-info me-2"></i><?php echo e(__('app.meta_information')); ?></h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="admin-info-list">
                                <div class="admin-info-item">
                                    <span class="admin-info-label"><?php echo e(__('app.Created_at')); ?>:</span>
                                    <span class="admin-info-value"><?php echo e($programming_language->created_at->format('M d, Y')); ?></span>
                                </div>
                                <div class="admin-info-item">
                                    <span class="admin-info-label"><?php echo e(__('app.updated_at')); ?>:</span>
                                    <span class="admin-info-value"><?php echo e($programming_language->updated_at->format('M d, Y')); ?></span>
                                </div>
                                <div class="admin-info-item">
                                    <span class="admin-info-label"><?php echo e(__('app.sort_order')); ?>:</span>
                                    <span class="admin-info-value">#<?php echo e($programming_language->sort_order); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="admin-section">
                <div class="admin-section-content">
                    <div class="d-flex justify-content-end gap-3">
                        <a href="<?php echo e(route('admin.programming-languages.index')); ?>"
                            class="admin-btn admin-btn-secondary admin-btn-m">
                            <i class="fas fa-times me-2"></i>
                            <?php echo e(__('app.Cancel')); ?>

                        </a>
                        <button type="submit" class="admin-btn admin-btn-primary admin-btn-m" id="submit-basic-btn">
                            <i class="fas fa-save me-2"></i>
                            <?php echo e(__('app.Update')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Template Tab -->
    <div id="template-content" class="admin-tab-panel admin-tab-panel-hidden" role="tabpanel" aria-labelledby="template-tab">
        <form method="post" action="<?php echo e(route('admin.programming-languages.update', $programming_language)); ?>"
            id="template-form" class="admin-form" novalidate>
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="row">
                <!-- Template Editor -->
                <div class="col-lg-8">
                    <div class="admin-section admin-section-warning">
                        <div class="admin-section-header">
                            <h3>
                                <i class="fas fa-file-contract admin-section-icon"></i>
                                <?php echo e(__('app.license_template_editor')); ?>

                            </h3>
                            <div class="admin-section-actions">
                                <button type="button" data-action="load-template"
                                    class="admin-btn admin-btn-sm admin-btn-primary">
                                    <i class="fas fa-upload me-2"></i><?php echo e(__('app.load_template')); ?>

                                </button>
                                <noscript>
                                    <a href="<?php echo e(route('admin.programming-languages.template-content', $programming_language)); ?>" 
                                       class="admin-btn admin-btn-sm admin-btn-primary">
                                        <i class="fas fa-upload me-2"></i><?php echo e(__('app.load_template')); ?>

                                    </a>
                                </noscript>
                                <button type="button" data-action="save-template"
                                    class="admin-btn admin-btn-sm admin-btn-success">
                                    <i class="fas fa-save me-2"></i><?php echo e(__('app.save_template')); ?>

                                </button>
                                <noscript>
                                    <button type="submit" form="template-form" 
                                            class="admin-btn admin-btn-sm admin-btn-success">
                                        <i class="fas fa-save me-2"></i><?php echo e(__('app.save_template')); ?>

                                    </button>
                                </noscript>
                                <button type="button" data-action="preview-template"
                                    class="admin-btn admin-btn-sm admin-btn-info">
                                    <i class="fas fa-eye me-2"></i><?php echo e(__('app.preview')); ?>

                                </button>
                                <noscript>
                                    <div class="admin-notification admin-notification-warning">
                                        <div class="admin-notification-content">
                                            <i class="fas fa-exclamation-triangle admin-notification-icon"></i>
                                            <div class="admin-notification-text">
                                                <h4><?php echo e(__('app.javascript_required')); ?></h4>
                                                <p><?php echo e(__('app.javascript_required_for_preview')); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </noscript>
                                <button type="button" data-action="validate-templates"
                                    data-url="<?php echo e(route('admin.programming-languages.validate-templates')); ?>"
                                    class="admin-btn admin-btn-sm admin-btn-warning">
                                    <i class="fas fa-check-circle me-2"></i><?php echo e(__('app.validate')); ?>

                                </button>
                            </div>
                        </div>
                        <div class="admin-section-content">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="license_template">
                                    <i class="fas fa-code admin-form-label-icon"></i><?php echo e(__('app.template_code')); ?>

                                </label>
                                <div class="admin-code-editor-container">
                                    <textarea id="license_template" name="license_template"
                                        class="admin-form-textarea admin-code-editor" rows="20"
                                        placeholder="<?php echo e(__('app.enter_license_verification_code')); ?>"
                                        data-help="<?php echo e(__('app.help_license_template')); ?>"><?php echo e(old('app.license_template', $programming_language->license_template)); ?></textarea>
                                    <div class="admin-code-editor-actions">
                                        <button type="button" data-action="toggle-code-view"
                                            class="admin-code-action-btn">
                                            <i class="fas fa-expand-arrows-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="admin-form-help admin-form-help-info">
                                    <i class="fas fa-info-circle admin-form-help-icon"></i>
                                    <?php echo e(__('app.available_placeholders')); ?>: {LICENSE_KEY}, {PRODUCT_NAME}, {CUSTOMER_EMAIL}
                                </div>
                                <?php $__errorArgs = ['app.license_template'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="admin-form-error-text"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template Sidebar -->
                <div class="col-lg-4">
                    <!-- Template Preview -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3>
                                <i class="fas fa-eye me-2"></i>
                                <?php echo e(__('app.live_preview')); ?>

                            </h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="admin-code-block" id="template-preview">
                                <?php if($programming_language->license_template): ?>
                                <?php echo e($programming_language->license_template); ?>

                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.No_custom_template')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Template Actions -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3>
                                <i class="fas fa-tools me-2"></i>
                                <?php echo e(__('app.template_actions')); ?>

                            </h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="d-grid gap-2">
                                <button type="button" data-action="refresh-templates"
                                    class="admin-btn admin-btn-info admin-btn-m w-100">
                                    <i class="fas fa-download me-2"></i><?php echo e(__('app.download_template')); ?>

                                </button>
                                <button type="button" data-action="view-template"
                                    class="admin-btn admin-btn-secondary admin-btn-m w-100">
                                    <i class="fas fa-eye me-2"></i><?php echo e(__('app.view_template_file')); ?>

                                </button>
                                <button type="button" data-action="create-template"
                                    class="admin-btn admin-btn-primary admin-btn-m w-100"
                                    data-label-not-implemented="<?php echo e(__('app.Not_implemented_yet')); ?>"
                                    data-label-prompt="<?php echo e(__('app.please_enter_template_name')); ?>">
                                    <i class="fas fa-plus me-2"></i><?php echo e(__('app.create_template_file')); ?>

                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Template Info -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3>
                                <i class="fas fa-info-circle me-2"></i>
                                <?php echo e(__('app.template_info')); ?>

                            </h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="admin-info-list">
                                <div class="admin-info-item">
                                    <span class="admin-info-label"><?php echo e(__('app.Status')); ?>:</span>
                                    <?php if($programming_language->hasTemplateFile()): ?>
                                    <span class="admin-badge admin-badge-success"><?php echo e(__('app.custom')); ?></span>
                                    <?php else: ?>
                                    <span class="admin-badge admin-badge-secondary"><?php echo e(__('app.default')); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if($programming_language->hasTemplateFile()): ?>
                                <div class="admin-info-item">
                                    <span class="admin-info-label"><?php echo e(__('app.file_size')); ?>:</span>
                                    <span class="admin-info-value"><?php echo e(number_format($programming_language->getTemplateInfo()['file_size'] / 1024, 1)); ?> KB</span>
                                </div>
                                <div class="admin-info-item">
                                    <span class="admin-info-label"><?php echo e(__('app.last_modified')); ?>:</span>
                                    <span class="admin-info-value"><?php echo e($programming_language->getTemplateInfo()['last_modified']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Form Actions -->
            <div class="admin-section">
                <div class="admin-section-content">
                    <div class="d-flex justify-content-end gap-3">
                        <a href="<?php echo e(route('admin.programming-languages.index')); ?>"
                            class="admin-btn admin-btn-secondary admin-btn-m">
                            <i class="fas fa-times me-2"></i>
                            <?php echo e(__('app.Cancel')); ?>

                        </a>
                        <button type="submit" class="admin-btn admin-btn-primary admin-btn-m">
                            <i class="fas fa-save me-2"></i>
                            <?php echo e(__('app.save_template')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <!-- Usage Stats Tab -->
    <div id="usage-content" class="admin-tab-panel admin-tab-panel-hidden">
        <!-- Usage Statistics -->
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-chart-bar me-2"></i><?php echo e(__('app.usage_statistics')); ?></h3>
            </div>
            <div class="admin-section-content">
                <div class="dashboard-grid dashboard-grid-3 stats-grid-enhanced">
                    <!-- Total Products Stats Card -->
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
                                <div class="stats-card-value"><?php echo e($programming_language->products()->count()); ?></div>
                                <div class="stats-card-label"><?php echo e(__('app.Total_products')); ?></div>
                                <div class="stats-card-trend positive">
                                    <i class="stats-trend-icon positive"></i>
                                    <span><?php echo e($programming_language->products()->where('is_active', true)->count()); ?> <?php echo e(__('app.Active')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Products Stats Card -->
                    <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
                        <div class="stats-card-background">
                            <div class="stats-card-pattern"></div>
                        </div>
                        <div class="stats-card-content">
                            <div class="stats-card-header">
                                <div class="stats-card-icon active"></div>
                                <div class="stats-card-menu">
                                    <button class="stats-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="stats-card-body">
                                <div class="stats-card-value"><?php echo e($programming_language->products()->where('is_active', true)->count()); ?></div>
                                <div class="stats-card-label"><?php echo e(__('app.Active_products')); ?></div>
                                <div class="stats-card-trend positive">
                                    <i class="stats-trend-icon positive"></i>
                                    <span><?php echo e(round(($programming_language->products()->where('is_active', true)->count() / max($programming_language->products()->count(), 1)) * 100)); ?>% <?php echo e(__('app.of_total')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Licenses Stats Card -->
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
                                <div class="stats-card-value"><?php echo e($programming_language->products()->withCount('licenses')->get()->sum('licenses_count')); ?></div>
                                <div class="stats-card-label"><?php echo e(__('app.Total_licenses_issued')); ?></div>
                                <div class="stats-card-trend positive">
                                    <i class="stats-trend-icon positive"></i>
                                    <span><?php echo e(__('app.Across all products')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Using This Language -->
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-list me-2"></i><?php echo e(__('app.products_using_language')); ?></h3>
            </div>
            <div class="admin-section-content">
                <?php if($programming_language->products()->count() > 0): ?>
                <div class="admin-table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo e(__('app.Product')); ?></th>
                                    <th><?php echo e(__('app.Licenses')); ?></th>
                                    <th><?php echo e(__('app.Status')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $programming_language->products()->take(10)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($product->image): ?>
                                            <img src="<?php echo e(Storage::url($product->image)); ?>" alt="<?php echo e($product->name); ?>"
                                                class="product-image me-3">
                                            <?php else: ?>
                                            <div class="product-avatar me-3">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold text-dark"><?php echo e($product->name); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-info"><?php echo e($product->licenses()->count()); ?></span>
                                    </td>
                                    <td>
                                        <?php if($product->is_active): ?>
                                        <span class="admin-badge admin-badge-success"><?php echo e(__('app.Active')); ?></span>
                                        <?php else: ?>
                                        <span class="admin-badge admin-badge-secondary"><?php echo e(__('app.inactive')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if($programming_language->products()->count() > 10): ?>
                    <div class="text-center p-3">
                        <span class="text-muted">
                            <?php echo e(__('app.and_more_products', ['count' => $programming_language->products()->count() - 10])); ?>

                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="admin-empty-state">
                    <div class="admin-empty-state-content">
                        <i class="fas fa-inbox admin-empty-state-icon"></i>
                        <h4 class="admin-empty-state-title"><?php echo e(__('app.No_products_yet')); ?></h4>
                        <p class="admin-empty-state-description"><?php echo e(__('app.No_products_using_language')); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\programming-languages\edit.blade.php ENDPATH**/ ?>