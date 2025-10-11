<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid user-show">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-eye text-info me-2"></i>
                                <?php echo e(trans('app.View User')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($user->name); ?> (<?php echo e($user->email); ?>)</p>
        </div>
                        <div>
                            <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i>
                    <?php echo e(trans('app.Edit User')); ?>

                </a>
                            <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                    <?php echo e(trans('app.Back to Users')); ?>

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
            <!-- User Overview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        <?php echo e(trans('app.User Overview')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user text-primary me-1"></i>
                                <?php echo e(trans('app.Full Name')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($user->name); ?></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-envelope text-success me-1"></i>
                                <?php echo e(trans('app.Email Address')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($user->email); ?></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user-shield text-purple me-1"></i>
                                <?php echo e(trans('app.User Role')); ?>

                            </label>
                            <p class="text-muted">
                                <span class="badge bg-<?php echo e($user->role == 'admin' ? 'danger' : 'secondary'); ?>">
                                    <?php echo e($user->role == 'admin' ? trans('app.Administrator') : trans('app.Regular User')); ?>

                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-toggle-on text-success me-1"></i>
                                <?php echo e(trans('app.Status')); ?>

                            </label>
                            <p class="text-muted">
                                <span class="badge bg-<?php echo e($user->is_active ? 'success' : 'danger'); ?>">
                                    <?php echo e($user->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                <?php echo e(trans('app.Email Verification')); ?>

                            </label>
                            <p class="text-muted">
                                <span class="badge bg-<?php echo e($user->email_verified_at ? 'success' : 'warning'); ?>">
                                    <?php echo e($user->email_verified_at ? trans('app.Verified') : trans('app.Not Verified')); ?>

                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-info me-1"></i>
                                <?php echo e(trans('app.Created At')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($user->created_at->format('M d, Y H:i')); ?></p>
                </div>
            </div>
        </div>
    </div>

            <!-- Client Information -->
            <?php if($user->firstname || $user->lastname || $user->companyname || $user->phonenumber): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-address-card me-2"></i>
                        <?php echo e(trans('app.Client Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if($user->firstname): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user text-primary me-1"></i>
                                <?php echo e(trans('app.First Name')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($user->firstname); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($user->lastname): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user text-primary me-1"></i>
                                <?php echo e(trans('app.Last Name')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($user->lastname); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($user->companyname): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-building text-purple me-1"></i>
                                <?php echo e(trans('app.Company Name')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($user->companyname); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($user->phonenumber): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-phone text-success me-1"></i>
                                <?php echo e(trans('app.Phone Number')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($user->phonenumber); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($user->address1): ?>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-map-marker-alt text-warning me-1"></i>
                                <?php echo e(trans('app.Address')); ?>

                            </label>
                            <p class="text-muted">
                                <?php echo e($user->address1); ?>

                                <?php if($user->address2): ?>
                                <br><?php echo e($user->address2); ?>

                                <?php endif; ?>
                                <?php if($user->city): ?>
                                <br><?php echo e($user->city); ?>

                                <?php endif; ?>
                                <?php if($user->state): ?>
                                , <?php echo e($user->state); ?>

                                <?php endif; ?>
                                <?php if($user->postcode): ?>
                                <?php echo e($user->postcode); ?>

                                <?php endif; ?>
                                <?php if($user->country): ?>
                                <br><?php echo e($user->country); ?>

                                <?php endif; ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- User Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        <?php echo e(trans('app.User Statistics')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-primary"><?php echo e($user->licenses_count ?? 0); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Licenses')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-success"><?php echo e($user->invoices_count ?? 0); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Invoices')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-info"><?php echo e($user->tickets_count ?? 0); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Tickets')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-warning"><?php echo e($user->orders_count ?? 0); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Orders')); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Recent Activity -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        <?php echo e(trans('app.Recent Activity')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?php echo e(trans('app.Account Created')); ?></h6>
                                <p class="timeline-text text-muted"><?php echo e($user->created_at->format('M d, Y H:i')); ?></p>
                            </div>
                        </div>
                        <?php if($user->updated_at != $user->created_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?php echo e(trans('app.Last Updated')); ?></h6>
                                <p class="timeline-text text-muted"><?php echo e($user->updated_at->format('M d, Y H:i')); ?></p>
                            </div>
                        </div>
            <?php endif; ?>
            <?php if($user->email_verified_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?php echo e(trans('app.Email Verified')); ?></h6>
                                <p class="timeline-text text-muted"><?php echo e($user->email_verified_at->format('M d, Y H:i')); ?></p>
        </div>
    </div>
                        <?php endif; ?>
                    </div>
                </div>
                    </div>
                </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- User Avatar -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        <?php echo e(trans('app.User Avatar')); ?>

                    </h5>
                    </div>
                <div class="card-body text-center">
                    <div class="user-avatar mb-3">
                        <i class="fas fa-user-circle fs-1 text-primary"></i>
                    </div>
                    <h5><?php echo e($user->name); ?></h5>
                    <p class="text-muted"><?php echo e($user->email); ?></p>
                    <span class="badge bg-<?php echo e($user->role == 'admin' ? 'danger' : 'secondary'); ?>">
                        <?php echo e($user->role == 'admin' ? trans('app.Administrator') : trans('app.User')); ?>

                    </span>
                    </div>
                </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        <?php echo e(trans('app.Quick Actions')); ?>

                    </h5>
                    </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            <?php echo e(trans('app.Edit User')); ?>

                        </a>
                        <a href="mailto:<?php echo e($user->email); ?>" class="btn btn-outline-success">
                            <i class="fas fa-envelope me-1"></i>
                            <?php echo e(trans('app.Send Email')); ?>

                        </a>
                        <?php if($user->licenses_count > 0): ?>
                        <a href="<?php echo e(route('admin.licenses.index', ['user_id' => $user->id])); ?>" class="btn btn-outline-info">
                            <i class="fas fa-key me-1"></i>
                            <?php echo e(trans('app.View Licenses')); ?>

                        </a>
                        <?php endif; ?>
                        <?php if($user->invoices_count > 0): ?>
                        <a href="<?php echo e(route('admin.invoices.index', ['user_id' => $user->id])); ?>" class="btn btn-outline-warning">
                            <i class="fas fa-file-invoice me-1"></i>
                            <?php echo e(trans('app.View Invoices')); ?>

                        </a>
                            <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e(trans('app.Account Details')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-info"><?php echo e($user->created_at->format('M Y')); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Created')); ?></p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-warning"><?php echo e($user->updated_at->format('M Y')); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Updated')); ?></p>
                            </div>
                    </div>
                    </div>
                    </div>
                </div>

            <!-- Security Information -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        <?php echo e(trans('app.Security Information')); ?>

                    </h5>
                    </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-lock text-success me-1"></i>
                            <?php echo e(trans('app.Password Status')); ?>

                        </label>
                        <p class="text-muted">
                            <span class="badge bg-success"><?php echo e(trans('app.Set')); ?></span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-envelope text-info me-1"></i>
                            <?php echo e(trans('app.Email Status')); ?>

                        </label>
                        <p class="text-muted">
                            <span class="badge bg-<?php echo e($user->email_verified_at ? 'success' : 'warning'); ?>">
                                <?php echo e($user->email_verified_at ? trans('app.Verified') : trans('app.Not Verified')); ?>

                            </span>
                        </p>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">
                            <i class="fas fa-toggle-on text-primary me-1"></i>
                            <?php echo e(trans('app.Account Status')); ?>

                        </label>
                        <p class="text-muted">
                            <span class="badge bg-<?php echo e($user->is_active ? 'success' : 'danger'); ?>">
                                <?php echo e($user->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\users\show.blade.php ENDPATH**/ ?>