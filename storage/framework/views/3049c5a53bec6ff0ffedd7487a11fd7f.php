<?php $__env->startSection('admin-content'); ?>
<div class="admin-page-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1><?php echo e(trans('app.User Management')); ?></h1>
            <p class="admin-page-subtitle"><?php echo e(trans('app.Manage system users and their permissions')); ?></p>
        </div>
        <div class="admin-page-actions">
            <a href="<?php echo e(route('admin.users.create')); ?>" class="admin-btn admin-btn-info admin-btn-m">
                <i class="fas fa-plus me-2"></i>
                <?php echo e(trans('app.Add New User')); ?>

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
                <small class="text-muted"><?php echo e(trans('app.Filter and search users')); ?></small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="searchUsers" class="form-label"><?php echo e(trans('app.Search')); ?></label>
                <input type="text" id="searchUsers" class="form-control" 
                       placeholder="<?php echo e(trans('app.Search by name, email or role')); ?>">
            </div>
            <div class="col-md-4">
                <label for="role-filter" class="form-label"><?php echo e(trans('app.Role')); ?></label>
                <select id="role-filter" class="form-select">
                    <option value=""><?php echo e(trans('app.All Roles')); ?></option>
                    <option value="admin"><?php echo e(trans('app.Admin')); ?></option>
                    <option value="user"><?php echo e(trans('app.User')); ?></option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="status-filter" class="form-label"><?php echo e(trans('app.Status')); ?></label>
                <select id="status-filter" class="form-select">
                    <option value=""><?php echo e(trans('app.All Statuses')); ?></option>
                    <option value="verified"><?php echo e(trans('app.Verified')); ?></option>
                    <option value="unverified"><?php echo e(trans('app.Unverified')); ?></option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Section -->
<div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
    <!-- Total Users Stats Card -->
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
                <div class="stats-card-value"><?php echo e($users->total()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Total Users')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(trans('app.all_registered_users')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Administrators Stats Card -->
    <div class="stats-card stats-card-danger animate-slide-up animate-delay-200">
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
                <div class="stats-card-value"><?php echo e($users->where('is_admin', '1')->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Administrators')); ?></div>
                <div class="stats-card-trend negative">
                    <i class="stats-trend-icon negative"></i>
                    <span><?php echo e(number_format(($users->where('is_admin', '1')->count() / max($users->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Regular Users Stats Card -->
    <div class="stats-card stats-card-info animate-slide-up animate-delay-300">
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
                <div class="stats-card-value"><?php echo e($users->where('role', '!=', 'admin')->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Regular Users')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(number_format(($users->where('role', '!=', 'admin')->count() / max($users->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Licenses Stats Card -->
    <div class="stats-card stats-card-success animate-slide-up animate-delay-400">
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
                <div class="stats-card-value"><?php echo e($users->sum('licenses_count')); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Total Licenses')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(trans('app.active_licenses')); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-users me-3 text-primary"></i>
                <div>
                    <h5 class="card-title mb-0"><?php echo e(trans('app.All Users')); ?></h5>
                    <small class="text-muted"><?php echo e(trans('app.Manage system users and their permissions')); ?></small>
                </div>
            </div>
            <div>
                <span class="badge bg-info fs-6"><?php echo e($users->total()); ?> <?php echo e(trans('app.Users')); ?></span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <?php if($users->count() > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center"><?php echo e(trans('app.Avatar')); ?></th>
                        <th><?php echo e(trans('app.User')); ?></th>
                        <th><?php echo e(trans('app.Email')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Company')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Location')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Role')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Joined')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Licenses')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="user-row" data-name="<?php echo e(strtolower($user->name)); ?>" data-email="<?php echo e(strtolower($user->email)); ?>" data-role="<?php echo e($user->hasRole('admin') ? 'admin' : 'user'); ?>" data-status="<?php echo e($user->email_verified_at ? 'verified' : 'unverified'); ?>">
                        <td class="text-center">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center user-avatar">
                                <span class="text-muted small fw-bold"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?php echo e($user->name); ?></div>
                            <small class="text-muted">ID: <?php echo e($user->id); ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?php echo e($user->email); ?></div>
                            <?php if($user->email_verified_at): ?>
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i><?php echo e(trans('app.Verified')); ?>

                            </small>
                            <?php else: ?>
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i><?php echo e(trans('app.Unverified')); ?>

                            </small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if($user->companyname): ?>
                                <span class="text-muted"><?php echo e($user->companyname); ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if($user->city || $user->country): ?>
                                <span class="text-muted">
                                <?php if($user->city && $user->country): ?>
                                <?php echo e($user->city); ?>, <?php echo e($user->country); ?>

                                <?php elseif($user->city): ?>
                                <?php echo e($user->city); ?>

                                <?php elseif($user->country): ?>
                                <?php echo e($user->country); ?>

                                <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge <?php echo e($user->hasRole('admin') ? 'bg-danger' : 'bg-info'); ?>">
                                <?php if($user->hasRole('admin')): ?>
                                    <i class="fas fa-user-shield me-1"></i><?php echo e(trans('app.Admin')); ?>

                                <?php else: ?>
                                    <i class="fas fa-user me-1"></i><?php echo e(trans('app.User')); ?>

                                <?php endif; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark"><?php echo e($user->created_at->format('M d, Y')); ?></div>
                            <small class="text-muted"><?php echo e($user->created_at->diffForHumans()); ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">
                                <i class="fas fa-key me-1"></i><?php echo e($user->licenses_count ?? 0); ?>

                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="<?php echo e(route('admin.users.show', $user)); ?>"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo e(trans('app.View')); ?>

                                </a>

                                <a href="<?php echo e(route('admin.users.edit', $user)); ?>"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    <?php echo e(trans('app.Edit')); ?>

                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-center">
                <?php echo e($users->links()); ?>

            </div>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-users text-muted empty-state-icon"></i>
            </div>
            <h4 class="text-muted"><?php echo e(trans('app.No Users Found')); ?></h4>
            <p class="text-muted mb-4"><?php echo e(trans('app.Create your first user to get started')); ?></p>
            <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>
                <?php echo e(trans('app.Add New User')); ?>

            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript is now handled by admin-categories.js -->

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\my-logos\resources\views/admin/users/index.blade.php ENDPATH**/ ?>