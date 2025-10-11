<?php $__env->startSection('title', 'Show Email Template'); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid email-template-show">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-eye text-info me-2"></i>
                                <?php echo e(trans('app.View Email Template')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($email_template->name); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.email-templates.test', $email_template)); ?>"
                                class="btn btn-warning me-2">
                                <i class="fas fa-paper-plane me-1"></i>
                                <?php echo e(trans('app.Test Template')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.email-templates.edit', $email_template)); ?>"
                                class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i>
                                <?php echo e(trans('app.Edit Template')); ?>

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

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Template Overview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo e(trans('app.Template Overview')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tag text-primary me-1"></i>
                                <?php echo e(trans('app.Template Name')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($email_template->name); ?></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list text-success me-1"></i>
                                <?php echo e(trans('app.Template Type')); ?>

                            </label>
                            <p class="text-muted">
                                <span class="badge bg-<?php echo e($email_template->type === 'user' ? 'primary' : 'secondary'); ?>">
                                    <?php echo e(trans('app.' . ucfirst($email_template->type))); ?>

                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-folder text-warning me-1"></i>
                                <?php echo e(trans('app.Template Category')); ?>

                            </label>
                            <p class="text-muted">
                                <span class="badge bg-info">
                                    <?php echo e(trans('app.' . ucfirst($email_template->category))); ?>

                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-toggle-on text-info me-1"></i>
                                <?php echo e(trans('app.Status')); ?>

                            </label>
                            <p class="text-muted">
                                <span class="badge bg-<?php echo e($email_template->is_active ? 'success' : 'danger'); ?>">
                                    <?php echo e($email_template->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-plus text-success me-1"></i>
                                <?php echo e(trans('app.Created At')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($email_template->created_at->format('M d, Y \a\t g:i A')); ?></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-edit text-warning me-1"></i>
                                <?php echo e(trans('app.Updated At')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($email_template->updated_at->format('M d, Y \a\t g:i A')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Description -->
            <?php if($email_template->description): ?>
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        <?php echo e(trans('app.Template Description')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo e($email_template->description); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Email Subject -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-heading me-2"></i>
                        <?php echo e(trans('app.Email Subject')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        <code class="text-dark"><?php echo e($email_template->subject); ?></code>
                    </div>
                </div>
            </div>

            <!-- Email Body -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-align-left me-2"></i>
                        <?php echo e(trans('app.Email Body')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="email-preview border rounded p-3">
                        <?php echo e($email_template->body); ?>

                    </div>
                </div>
            </div>

            <!-- Template Variables -->
            <?php if($email_template->variables && count($email_template->variables) > 0): ?>
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i>
                        <?php echo e(trans('app.Variables Used')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php $__currentLoopData = $email_template->variables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-2 border rounded">
                                <div class="me-3">
                                    <i class="fas fa-code text-primary"></i>
                                </div>
                                <div>
                                    <code class="text-primary"><?php echo e($variable); ?></code>
                                    <div class="text-muted small">
                                        <?php switch($variable):
                                        case ('app_name'): ?>
                                        <?php echo e(trans('app.Application name')); ?>

                                        <?php break; ?>
                                        <?php case ('app_url'): ?>
                                        <?php echo e(trans('app.Application URL')); ?>

                                        <?php break; ?>
                                        <?php case ('user_name'): ?>
                                        <?php echo e(trans('app.User name')); ?>

                                        <?php break; ?>
                                        <?php case ('user_email'): ?>
                                        <?php echo e(trans('app.User email')); ?>

                                        <?php break; ?>
                                        <?php case ('license_code'): ?>
                                        <?php echo e(trans('app.License code')); ?>

                                        <?php break; ?>
                                        <?php case ('product_name'): ?>
                                        <?php echo e(trans('app.Product name')); ?>

                                        <?php break; ?>
                                        <?php case ('ticket_id'): ?>
                                        <?php echo e(trans('app.Ticket ID')); ?>

                                        <?php break; ?>
                                        <?php case ('invoice_id'): ?>
                                        <?php echo e(trans('app.Invoice ID')); ?>

                                        <?php break; ?>
                                        <?php default: ?>
                                        <?php echo e(ucfirst(str_replace('_', ' ', $variable))); ?>

                                        <?php endswitch; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Template Actions -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>
                        <?php echo e(trans('app.Template Actions')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('admin.email-templates.test', $email_template)); ?>" class="btn btn-warning">
                            <i class="fas fa-paper-plane me-1"></i>
                            <?php echo e(trans('app.Test Template')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.email-templates.edit', $email_template)); ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            <?php echo e(trans('app.Edit Template')); ?>

                        </a>
                        <form method="POST" action="<?php echo e(route('admin.email-templates.toggle', $email_template)); ?>"
                            class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="btn btn-<?php echo e($email_template->is_active ? 'warning' : 'success'); ?> w-100">
                                <i class="fas fa-toggle-<?php echo e($email_template->is_active ? 'off' : 'on'); ?> me-1"></i>
                                <?php echo e($email_template->is_active ? trans('app.Deactivate') : trans('app.Activate')); ?>

                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.email-templates.destroy', $email_template)); ?>"
                            class="d-inline" data-confirm="delete-template">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-1"></i>
                                <?php echo e(trans('app.Delete Template')); ?>

                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Template Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        <?php echo e(trans('app.Template Statistics')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">0</h4>
                                <small class="text-muted"><?php echo e(trans('app.Times Used')); ?></small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">0</h4>
                                <small class="text-muted"><?php echo e(trans('app.Test Emails')); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        <?php echo e(trans('app.Quick Actions')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary copy-btn"
                            data-text="<?php echo e($email_template->name); ?>">
                            <i class="fas fa-copy me-1"></i>
                            <?php echo e(trans('app.Copy Template Name')); ?>

                        </button>
                        <button type="button" class="btn btn-outline-info copy-btn"
                            data-text="<?php echo e($email_template->subject); ?>">
                            <i class="fas fa-copy me-1"></i>
                            <?php echo e(trans('app.Copy Subject')); ?>

                        </button>
                        <a href="<?php echo e(route('admin.email-templates.index')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>
                            <?php echo e(trans('app.All Templates')); ?>

                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\email-templates\show.blade.php ENDPATH**/ ?>