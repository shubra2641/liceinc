<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid products-form">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-edit text-primary me-2"></i>
                                <?php echo e(trans('app.Edit Email Template')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($email_template->name); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.email-templates.show', $email_template)); ?>"
                                class="btn btn-info me-2">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo e(trans('app.View Template')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.email-templates.test', $email_template)); ?>"
                                class="btn btn-warning me-2">
                                <i class="fas fa-paper-plane me-1"></i>
                                <?php echo e(trans('app.Test Template')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.email-templates.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Templates')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($errors->any()): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger border-0 shadow-sm">
                <div class="d-flex">
                    <i class="fas fa-exclamation-triangle text-danger mt-1 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-2"><?php echo e(trans('app.Validation Errors')); ?></h5>
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('admin.email-templates.update', $email_template)); ?>" class="needs-validation"
        novalidate>
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            <?php echo e(trans('app.Basic Information')); ?>

                            <span class="badge bg-danger ms-2"><?php echo e(trans('app.Required')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo e(trans('app.Template Name')); ?>

                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="name"
                                    name="name" value="<?php echo e(old('name', $email_template->name)); ?>" required
                                    placeholder="e.g., user_welcome_template">
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="form-text"><?php echo e(trans('app.Unique identifier for the template')); ?></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    <i class="fas fa-list me-1"></i>
                                    <?php echo e(trans('app.Template Type')); ?>

                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="type" name="type"
                                    required>
                                    <option value=""><?php echo e(trans('app.Select Type')); ?></option>
                                    <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type); ?>" <?php echo e(old('type', $email_template->type) === $type ?
                                        'selected' : ''); ?>>
                                        <?php echo e(trans('app.' . ucfirst($type))); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">
                                    <i class="fas fa-folder me-1"></i>
                                    <?php echo e(trans('app.Template Category')); ?>

                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="category"
                                    name="category" required>
                                    <option value=""><?php echo e(trans('app.Select Category')); ?></option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category); ?>" <?php echo e(old('category', $email_template->category) ===
                                        $category ? 'selected' : ''); ?>>
                                        <?php echo e(trans('app.' . ucfirst($category))); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    <?php echo e(trans('app.Template Status')); ?>

                                </label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_active" value="1"
                                            id="is_active_yes" <?php echo e(old('is_active', $email_template->is_active) ?
                                        'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_active_yes">
                                            <?php echo e(trans('app.Active')); ?>

                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_active" value="0"
                                            id="is_active_no" <?php echo e(old('is_active', $email_template->is_active) == 0 ?
                                        'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_active_no">
                                            <?php echo e(trans('app.Inactive')); ?>

                                        </label>
                                    </div>
                                </div>
                                <?php $__errorArgs = ['is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Content -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope text-info me-2"></i>
                            <?php echo e(trans('app.Email Content')); ?>

                            <span class="badge bg-danger ms-2"><?php echo e(trans('app.Required')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-heading me-1"></i>
                                    <?php echo e(trans('app.Email Subject')); ?>

                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="subject" name="subject" value="<?php echo e(old('subject', $email_template->subject)); ?>"
                                    required placeholder="e.g., Welcome to {{app_name}}!">
                                <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="form-text"><?php echo e(trans('app.Use variables like {{app_name); ?>, <?php echo e(user_name); ?>,
                                    etc.') }}</div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="body" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>
                                    <?php echo e(trans('app.Email Body')); ?>

                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="body" name="body"
                                    rows="15" required data-summernote="true" data-toolbar="standard"
                                    data-placeholder="<?php echo e(trans('app.Enter your email content here...')); ?>"
                                    placeholder="<?php echo e(trans('app.Enter your email content here...')); ?>"><?php echo e(old('body', $email_template->body)); ?></textarea>
                                <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="form-text"><?php echo e(trans('app.HTML is supported. Use variables like {{app_name); ?>,
                                    <?php echo e(user_name); ?>, etc.') }}</div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>
                                    <?php echo e(trans('app.Template Description')); ?>

                                </label>
                                <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="description" name="description" rows="3"
                                    placeholder="<?php echo e(trans('app.Brief description of this template...')); ?>"><?php echo e(old('description', $email_template->description)); ?></textarea>
                                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Variables -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-code text-warning me-2"></i>
                            <?php echo e(trans('app.Available Variables')); ?>

                            <span class="badge bg-info ms-2"><?php echo e(trans('app.Help')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3"><?php echo e(trans('app.Click on any variable to copy it to your clipboard')); ?>

                        </p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="{{app_name}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-building text-primary"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">{{app_name}}</code>
                                            <div class="text-muted small"><?php echo e(trans('app.Application name')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="{{app_url}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-link text-info"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">{{app_url}}</code>
                                            <div class="text-muted small"><?php echo e(trans('app.Application URL')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="{{user_name}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-user text-success"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">{{user_name}}</code>
                                            <div class="text-muted small"><?php echo e(trans('app.User name')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="{{user_email}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-envelope text-warning"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">{{user_email}}</code>
                                            <div class="text-muted small"><?php echo e(trans('app.User email')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="{{license_code}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-key text-danger"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">{{license_code}}</code>
                                            <div class="text-muted small"><?php echo e(trans('app.License code')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="{{product_name}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-box text-purple"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">{{product_name}}</code>
                                            <div class="text-muted small"><?php echo e(trans('app.Product name')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="{{ticket_id}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-ticket-alt text-secondary"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">{{ticket_id}}</code>
                                            <div class="text-muted small"><?php echo e(trans('app.Ticket ID')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="{{invoice_id}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-file-invoice text-dark"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">{{invoice_id}}</code>
                                            <div class="text-muted small"><?php echo e(trans('app.Invoice ID')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Template Preview -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye text-info me-2"></i>
                            <?php echo e(trans('app.Template Preview')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="preview-container">
                            <div class="preview-header">
                                <div class="preview-subject">
                                    <strong><?php echo e(trans('app.Subject')); ?>:</strong>
                                    <span id="preview-subject">-</span>
                                </div>
                            </div>
                            <div class="preview-content-wrapper">
                                <div id="preview-content" class="preview-content"></div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" id="refresh-preview" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-sync-alt me-1"></i>
                                <?php echo e(trans('app.Refresh Preview')); ?>

                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>
                            <?php echo e(trans('app.Actions')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                <?php echo e(trans('app.Update Template')); ?>

                            </button>
                            <a href="<?php echo e(route('admin.email-templates.show', $email_template)); ?>" class="btn btn-info">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo e(trans('app.View Template')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.email-templates.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                <?php echo e(trans('app.Cancel')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\email-templates\edit.blade.php ENDPATH**/ ?>