

<?php $__env->startSection('admin-content'); ?>
<div class="admin-page-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1><?php echo e(trans('app.Email Templates')); ?></h1>
            <p class="admin-page-subtitle"><?php echo e(trans('app.Manage email templates for the system')); ?></p>
        </div>
        <div class="admin-page-actions">
            <a href="<?php echo e(route('admin.email-templates.create')); ?>" class="admin-btn admin-btn-info admin-btn-m">
                <i class="fas fa-plus me-2"></i>
                <?php echo e(trans('app.Create New Template')); ?>

            </a>
        </div>
    </div>
</div>





<!-- Enhanced Filters Section -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center">
            <i class="fas fa-filter me-3 text-primary"></i>
            <div>
                <h5 class="card-title mb-0"><?php echo e(trans('app.Filters')); ?></h5>
                <small class="text-muted"><?php echo e(trans('app.Filter and search email templates')); ?></small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="searchTemplates" class="form-label"><?php echo e(trans('app.Search')); ?></label>
                <input type="text" id="searchTemplates" class="form-control" 
                       placeholder="<?php echo e(trans('app.Search by name or subject')); ?>">
            </div>
            <div class="col-md-4">
                <label for="type-filter" class="form-label"><?php echo e(trans('app.Type')); ?></label>
                <select id="type-filter" class="form-select">
                    <option value=""><?php echo e(trans('app.All Types')); ?></option>
                    <option value="user"><?php echo e(trans('app.User')); ?></option>
                    <option value="admin"><?php echo e(trans('app.Admin')); ?></option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="category-filter" class="form-label"><?php echo e(trans('app.Category')); ?></label>
                <select id="category-filter" class="form-select">
                    <option value=""><?php echo e(trans('app.All Categories')); ?></option>
                    <option value="registration"><?php echo e(trans('app.Registration')); ?></option>
                    <option value="authentication"><?php echo e(trans('app.Authentication')); ?></option>
                    <option value="license"><?php echo e(trans('app.License')); ?></option>
                    <option value="ticket"><?php echo e(trans('app.Ticket')); ?></option>
                    <option value="invoice"><?php echo e(trans('app.Invoice')); ?></option>
                    <option value="product"><?php echo e(trans('app.Product')); ?></option>
                    <option value="other"><?php echo e(trans('app.Other')); ?></option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Section -->
<div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
    <!-- Total Templates Stats Card -->
    <div class="stats-card stats-card-primary animate-slide-up">
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
                <div class="stats-card-value"><?php echo e($templates->total()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Total Templates')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(trans('app.all_email_templates')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Templates Stats Card -->
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
                <div class="stats-card-value"><?php echo e($templates->where('is_active', true)->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Active Templates')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(number_format(($templates->where('is_active', true)->count() / max($templates->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- User Templates Stats Card -->
    <div class="stats-card stats-card-info animate-slide-up animate-delay-300">
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
                <div class="stats-card-value"><?php echo e($templates->where('type', 'user')->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.User Templates')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(number_format(($templates->where('type', 'user')->count() / max($templates->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Templates Stats Card -->
    <div class="stats-card stats-card-warning animate-slide-up animate-delay-400">
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
                <div class="stats-card-value"><?php echo e($templates->where('type', 'admin')->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Admin Templates')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(number_format(($templates->where('type', 'admin')->count() / max($templates->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Templates Table -->
<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-envelope me-3 text-primary"></i>
                <div>
                    <h5 class="card-title mb-0"><?php echo e(trans('app.All Email Templates')); ?></h5>
                    <small class="text-muted"><?php echo e(trans('app.Manage and customize email templates')); ?></small>
                </div>
            </div>
            <div>
                <span class="badge bg-info fs-6"><?php echo e($templates->total()); ?> <?php echo e(trans('app.Templates')); ?></span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <?php if($templates->count() > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0 email-templates-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center"><?php echo e(trans('app.Avatar')); ?></th>
                        <th><?php echo e(trans('app.Template')); ?></th>
                        <th><?php echo e(trans('app.Subject')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Type')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Category')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Status')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Created')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="template-row" data-name="<?php echo e(strtolower($template->name)); ?>" data-subject="<?php echo e(strtolower($template->subject)); ?>" data-type="<?php echo e($template->type); ?>" data-category="<?php echo e($template->category); ?>">
                        <td class="text-center">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center template-avatar">
                                <span class="text-muted small fw-bold"><?php echo e(strtoupper(substr($template->name, 0, 1))); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?php echo e($template->name); ?></div>
                            <?php if($template->description): ?>
                            <small class="text-muted"><?php echo e(Str::limit($template->description, 50)); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?php echo e(Str::limit($template->subject, 40)); ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge <?php echo e($template->type === 'user' ? 'bg-info' : 'bg-warning'); ?>">
                                <?php if($template->type === 'user'): ?>
                                    <i class="fas fa-user me-1"></i><?php echo e(trans('app.User')); ?>

                                <?php else: ?>
                                    <i class="fas fa-user-shield me-1"></i><?php echo e(trans('app.Admin')); ?>

                                <?php endif; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">
                                <i class="fas fa-tag me-1"></i><?php echo e(trans('app.' . ucfirst($template->category))); ?>

                            </span>
                        </td>
                        <td class="text-center">
                            <form method="POST" action="<?php echo e(route('admin.email-templates.toggle', $template)); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm <?php echo e($template->is_active ? 'btn-success' : 'btn-outline-secondary'); ?>">
                                    <?php if($template->is_active): ?>
                                        <i class="fas fa-toggle-on me-1"></i><?php echo e(trans('app.Active')); ?>

                                    <?php else: ?>
                                        <i class="fas fa-toggle-off me-1"></i><?php echo e(trans('app.Inactive')); ?>

                                    <?php endif; ?>
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark"><?php echo e($template->created_at->format('M d, Y')); ?></div>
                            <small class="text-muted"><?php echo e($template->created_at->format('g:i A')); ?></small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="<?php echo e(route('admin.email-templates.show', $template)); ?>"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo e(trans('app.View')); ?>

                                </a>

                                <a href="<?php echo e(route('admin.email-templates.edit', $template)); ?>"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    <?php echo e(trans('app.Edit')); ?>

                                </a>

                                <a href="<?php echo e(route('admin.email-templates.test', $template)); ?>"
                                   class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    <?php echo e(trans('app.Test')); ?>

                                </a>

                                <form method="POST" action="<?php echo e(route('admin.email-templates.destroy', $template)); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 delete-template-btn"
                                            data-confirm="<?php echo e(trans('app.Are you sure you want to delete this template?')); ?>">
                                        <i class="fas fa-trash me-1"></i>
                                        <?php echo e(trans('app.Delete')); ?>

                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <?php if($templates->hasPages()): ?>
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                <?php echo e($templates->links()); ?>

            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-envelope text-muted empty-state-icon"></i>
            </div>
            <h4 class="text-muted"><?php echo e(trans('app.No Email Templates Found')); ?></h4>
            <p class="text-muted mb-4"><?php echo e(trans('app.Create your first email template to get started')); ?></p>
            <a href="<?php echo e(route('admin.email-templates.create')); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>
                <?php echo e(trans('app.Create Your First Template')); ?>

            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript is now handled by admin-categories.js -->

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\email-templates\index.blade.php ENDPATH**/ ?>