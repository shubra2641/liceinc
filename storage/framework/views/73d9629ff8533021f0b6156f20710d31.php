<?php $__env->startSection('admin-content'); ?>
<div class="admin-programming-languages-create">
    <!-- Page Header -->
    <div class="admin-page-header modern-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="flex-grow-1">
                <h1 class="gradient-text"><?php echo e(__('app.create_new_programming_language')); ?></h1>
                <p class="admin-page-subtitle"><?php echo e(__('app.add_new_programming_language')); ?></p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo e(route('admin.programming-languages.index')); ?>" class="admin-btn admin-btn-secondary admin-btn-m">
                    <i class="fas fa-arrow-left me-2"></i>
                    <?php echo e(__('app.back_to_languages')); ?>

                </a>
            </div>
        </div>
    </div>

    <?php if($errors->any()): ?>
    <div class="admin-alert admin-alert-error">
        <div class="admin-alert-content">
            <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
            <div>
                <h4 class="admin-alert-title"><?php echo e(__('validation_errors')); ?></h4>
                <ul class="admin-alert-message">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="admin-section">
        <div class="admin-section-content">
            <form method="post" action="<?php echo e(route('admin.programming-languages.store')); ?>" class="needs-validation" novalidate>
                <?php echo csrf_field(); ?>

                <!-- Basic Information -->
                <div class="admin-card mb-4">
                    <div class="admin-section-content">
                        <div class="admin-card-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="admin-card-title">
                            <h3><?php echo e(__('app.Basic_Information')); ?></h3>
                            <p class="admin-card-subtitle"><?php echo e(__('app.enter_language_basic_details')); ?></p>
                        </div>
                        <span class="admin-badge admin-badge-required"><?php echo e(__('app.Required')); ?></span>
                    </div>
                    <div class="admin-card-content">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label required" for="name">
                                        <i class="fas fa-code me-2"></i><?php echo e(__('app.language_name')); ?>

                                    </label>
                                    <input type="text" id="name" name="name" class="admin-form-input"
                                           value="<?php echo e(old('name')); ?>" required placeholder="<?php echo e(__('app.enter_language_name')); ?>">
                                    <?php $__errorArgs = ['name'];
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
                                        <i class="fas fa-link me-2"></i><?php echo e(__('slug')); ?>

                                    </label>
                                    <input type="text" id="slug" name="slug" class="admin-form-input"
                                           value="<?php echo e(old('slug')); ?>" placeholder="<?php echo e(__('app.auto_generated_from_name')); ?>">
                                    <small class="admin-form-help"><?php echo e(__('app.leave_empty_auto_generate')); ?></small>
                                    <?php $__errorArgs = ['slug'];
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
                                        <i class="fas fa-file-code me-2"></i><?php echo e(__('app.file_extension')); ?>

                                    </label>
                                    <input type="text" id="file_extension" name="file_extension" class="admin-form-input"
                                           value="<?php echo e(old('file_extension')); ?>" placeholder="php, js, py, etc.">
                                    <?php $__errorArgs = ['file_extension'];
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
                                        <i class="fas fa-icons me-2"></i><?php echo e(__('app.icon_class')); ?>

                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="icon" name="icon" class="admin-form-input"
                                               value="<?php echo e(old('icon')); ?>" placeholder="fab fa-php, fas fa-code">
                                        <div class="input-group-text">
                                            <i id="icon-preview" class="fas fa-code"></i>
                                        </div>
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

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="sort_order">
                                        <i class="fas fa-sort-numeric-up me-2"></i><?php echo e(__('app.sort_order')); ?>

                                    </label>
                                    <input type="number" id="sort_order" name="sort_order" class="admin-form-input"
                                           value="<?php echo e(old('sort_order', 0)); ?>" min="0">
                                    <?php $__errorArgs = ['sort_order'];
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
                                    <div class="admin-checkbox-group">
                                        <input type="checkbox" id="is_active" name="is_active" value="1"
                                               <?php echo e(old('is_active', true) ? 'checked' : ''); ?> class="admin-checkbox">
                                        <label for="is_active" class="admin-checkbox-label">
                                            <span class="admin-checkbox-text"><?php echo e(__('app.Active')); ?></span>
                                            <small class="admin-checkbox-description"><?php echo e(__('app.language_will_be_available')); ?></small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="description">
                                    <i class="fas fa-align-left me-2"></i><?php echo e(__('app.Description')); ?>

                                </label>
                                <textarea id="description" name="description" class="admin-form-textarea" rows="3"
                                          placeholder="<?php echo e(__('app.brief_description_language')); ?>"><?php echo e(old('description')); ?></textarea>
                                <?php $__errorArgs = ['description'];
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

                <!-- License Template -->
                <div class="admin-card mb-4">
                    <div class="admin-section-content">
                        <div class="admin-card-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div class="admin-card-title">
                            <h3><?php echo e(__('app.license_template')); ?></h3>
                            <p class="admin-card-subtitle"><?php echo e(__('app.custom_license_verification_template')); ?></p>
                        </div>
                    </div>
                    <div class="admin-card-content">
                        <div class="admin-form-group">
                            <label class="admin-form-label" for="license_template">
                                <i class="fas fa-code me-2"></i><?php echo e(__('app.license_template')); ?>

                            </label>
                            <textarea id="license_template" name="license_template" class="admin-form-textarea" rows="15"
                                      placeholder="<?php echo e(__('app.enter_license_verification_code')); ?>"><?php echo e(old('license_template')); ?></textarea>
                            <small class="admin-form-help">
                                <?php echo e(__('app.available_placeholders')); ?><br>
                                <?php echo e(__('app.leave_empty_use_default')); ?>

                            </small>
                            <?php $__errorArgs = ['license_template'];
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

                        <div class="admin-card admin-card-info">
                            <div class="admin-section-content">
                                <div class="admin-card-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="admin-card-title">
                                    <h4><?php echo e(__('app.template_preview')); ?></h4>
                                </div>
                            </div>
                            <div class="admin-card-content">
                                <div class="admin-code-block">
                                    <h4><?php echo e(__('app.template_preview')); ?></h4>
                                    <pre class="admin-code-pre" id="template-preview"><?php echo e(__('app.template_generated_based_language')); ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="admin-card">
                    <div class="admin-card-content">
                        <div class="d-flex gap-3 justify-content-end">
                            <a href="<?php echo e(route('admin.programming-languages.index')); ?>" class="admin-btn admin-btn-secondary admin-btn-lg">
                                <i class="fas fa-times me-2"></i>
                                <?php echo e(__('app.Cancel')); ?>

                            </a>
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-lg">
                                <i class="fas fa-save me-2"></i>
                                <?php echo e(__('app.create_language')); ?>

                                <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\programming-languages\create.blade.php ENDPATH**/ ?>