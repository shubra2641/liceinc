

<?php $__env->startSection('admin-content'); ?>
<div class="admin-page-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1><?php echo e(trans('app.License Management')); ?></h1>
            <p class="admin-page-subtitle"><?php echo e(trans('app.Manage and monitor license usage across your platform')); ?></p>
        </div>
        <div class="admin-page-actions">
            <a href="<?php echo e(route('admin.licenses.create')); ?>" class="admin-btn admin-btn-info admin-btn-m">
                <i class="fas fa-plus me-2"></i>
                <?php echo e(trans('app.Add New License')); ?>

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
                <small class="text-muted"><?php echo e(trans('app.Filter and search licenses')); ?></small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="searchLicenses" class="form-label"><?php echo e(trans('app.Search')); ?></label>
                <input type="text" id="searchLicenses" class="form-control" 
                       placeholder="<?php echo e(trans('app.Search by license key, customer or product')); ?>">
            </div>
            <div class="col-md-3">
                <label for="status-filter" class="form-label"><?php echo e(trans('app.Status')); ?></label>
                <select id="status-filter" class="form-select">
                    <option value=""><?php echo e(trans('app.All Statuses')); ?></option>
                    <option value="active"><?php echo e(trans('app.Active')); ?></option>
                    <option value="inactive"><?php echo e(trans('app.Inactive')); ?></option>
                    <option value="expired"><?php echo e(trans('app.Expired')); ?></option>
                    <option value="suspended"><?php echo e(trans('app.Suspended')); ?></option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="type-filter" class="form-label"><?php echo e(trans('app.Type')); ?></label>
                <select id="type-filter" class="form-select">
                    <option value=""><?php echo e(trans('app.All Types')); ?></option>
                    <option value="single"><?php echo e(trans('app.Single')); ?></option>
                    <option value="multi"><?php echo e(trans('app.Multi')); ?></option>
                    <option value="unlimited"><?php echo e(trans('app.Unlimited')); ?></option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="sort-filter" class="form-label"><?php echo e(trans('app.Sort By')); ?></label>
                <select id="sort-filter" class="form-select">
                    <option value="created_at"><?php echo e(trans('app.Created Date')); ?></option>
                    <option value="license_key"><?php echo e(trans('app.License Key')); ?></option>
                    <option value="expires_at"><?php echo e(trans('app.Expiry Date')); ?></option>
                    <option value="status"><?php echo e(trans('app.Status')); ?></option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Section -->
<div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
    <!-- Total Licenses Stats Card -->
    <div class="stats-card stats-card-primary animate-slide-up">
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
                <div class="stats-card-value"><?php echo e($licenses->total()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Total Licenses')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(trans('app.all_issued_licenses')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Licenses Stats Card -->
    <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
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
                <div class="stats-card-value"><?php echo e($licenses->where('status', 'active')->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Active Licenses')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(number_format(($licenses->where('status', 'active')->count() / max($licenses->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiring Soon Stats Card -->
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
                <div class="stats-card-value"><?php echo e($licenses->filter(function($license) { return $license->license_expires_at && $license->license_expires_at->diffInDays() <= 30; })->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Expiring Soon')); ?></div>
                <div class="stats-card-trend negative">
                    <i class="stats-trend-icon negative"></i>
                    <span><?php echo e(trans('app.within_30_days')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Expired Licenses Stats Card -->
    <div class="stats-card stats-card-danger animate-slide-up animate-delay-400">
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
                <div class="stats-card-value"><?php echo e($licenses->filter(function($license) { return $license->license_expires_at && $license->license_expires_at->isPast(); })->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Expired Licenses')); ?></div>
                <div class="stats-card-trend negative">
                    <i class="stats-trend-icon negative"></i>
                    <span><?php echo e(number_format(($licenses->filter(function($license) { return $license->license_expires_at && $license->license_expires_at->isPast(); })->count() / max($licenses->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Domain Statistics -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm stats-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-globe text-info fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1 fw-medium"><?php echo e(trans('app.Total Domains')); ?></p>
                        <h3 class="fw-bold mb-1 text-dark"><?php echo e($licenses->sum(function($license) { return $license->active_domains_count ?? 0; })); ?></h3>
                        <small class="text-info fw-medium"><?php echo e(trans('app.Active domains')); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm stats-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-exclamation-triangle text-warning fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1 fw-medium"><?php echo e(trans('app.Domain Limit Reached')); ?></p>
                        <h3 class="fw-bold mb-1 text-dark"><?php echo e($licenses->filter(function($license) { return $license->hasReachedDomainLimit(); })->count()); ?></h3>
                        <small class="text-warning fw-medium"><?php echo e(trans('app.Licenses at limit')); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm stats-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-check-circle text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1 fw-medium"><?php echo e(trans('app.Single Domain Licenses')); ?></p>
                        <h3 class="fw-bold mb-1 text-dark"><?php echo e($licenses->where('license_type', 'single')->count()); ?></h3>
                        <small class="text-success fw-medium"><?php echo e(trans('app.One domain only')); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm stats-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-users text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1 fw-medium"><?php echo e(trans('app.Multi Domain Licenses')); ?></p>
                        <h3 class="fw-bold mb-1 text-dark"><?php echo e($licenses->whereIn('license_type', ['multi', 'developer', 'extended'])->count()); ?></h3>
                        <small class="text-primary fw-medium"><?php echo e(trans('app.Multiple domains')); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Licenses Table -->
<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-key me-3 text-primary"></i>
                <div>
                    <h5 class="card-title mb-0"><?php echo e(trans('app.All Licenses')); ?></h5>
                    <small class="text-muted"><?php echo e(trans('app.Manage and monitor license usage')); ?></small>
                </div>
            </div>
            <div>
                <span class="badge bg-info fs-6"><?php echo e($licenses->total()); ?> <?php echo e(trans('app.Licenses')); ?></span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <?php if($licenses->count() > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center"><?php echo e(trans('app.Avatar')); ?></th>
                        <th><?php echo e(trans('app.License')); ?></th>
                        <th><?php echo e(trans('app.Customer')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Product')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Status')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Type')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Expires')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Usage')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $licenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $license): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="license-row" data-key="<?php echo e(strtolower($license->license_key)); ?>" data-customer="<?php echo e(strtolower($license->user->name ?? $license->customer->name ?? '')); ?>" data-product="<?php echo e(strtolower($license->product->name ?? '')); ?>" data-status="<?php echo e($license->status); ?>" data-type="<?php echo e($license->license_type); ?>">
                        <td class="text-center">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center license-avatar">
                                <span class="text-muted small fw-bold"><?php echo e(strtoupper(substr($license->license_key, 0, 1))); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?php echo e($license->license_key); ?></div>
                            <small class="text-muted">ID: <?php echo e($license->id); ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?php echo e($license->user->name ?? $license->customer->name ?? 'N/A'); ?></div>
                            <small class="text-muted"><?php echo e($license->user->email ?? $license->customer->email ?? ''); ?></small>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark"><?php echo e($license->product->name ?? 'N/A'); ?></div>
                            <?php if($license->product): ?>
                            <small class="text-muted"><?php echo e($license->product->category->name ?? ''); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge <?php echo e($license->status === 'active' ? 'bg-success' : ($license->status === 'expired' ? 'bg-danger' : ($license->status === 'inactive' ? 'bg-secondary' : 'bg-warning'))); ?>">
                                <?php if($license->status === 'active'): ?>
                                    <i class="fas fa-check-circle me-1"></i><?php echo e(trans('app.Active')); ?>

                                <?php elseif($license->status === 'inactive'): ?>
                                    <i class="fas fa-pause-circle me-1"></i><?php echo e(trans('app.Inactive')); ?>

                                <?php elseif($license->status === 'expired'): ?>
                                    <i class="fas fa-times-circle me-1"></i><?php echo e(trans('app.Expired')); ?>

                                <?php else: ?>
                                    <i class="fas fa-exclamation-triangle me-1"></i><?php echo e(ucfirst($license->status)); ?>

                                <?php endif; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge <?php echo e($license->license_type === 'single' ? 'bg-info' : ($license->license_type === 'multi' ? 'bg-primary' : 'bg-success')); ?>">
                                <?php if($license->license_type === 'single'): ?>
                                    <i class="fas fa-user me-1"></i><?php echo e(trans('app.Single')); ?>

                                <?php elseif($license->license_type === 'multi'): ?>
                                    <i class="fas fa-users me-1"></i><?php echo e(trans('app.Multi')); ?>

                                <?php else: ?>
                                    <i class="fas fa-infinity me-1"></i><?php echo e(trans('app.Unlimited')); ?>

                                <?php endif; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if($license->license_expires_at): ?>
                                <div class="fw-semibold text-dark"><?php echo e($license->license_expires_at->format('M d, Y')); ?></div>
                                <?php if($license->license_expires_at->isPast()): ?>
                                    <small class="text-danger">
                                        <i class="fas fa-times-circle me-1"></i><?php echo e(trans('app.Expired')); ?>

                                    </small>
                                <?php elseif($license->license_expires_at->diffInDays() <= 30): ?>
                                    <small class="text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i><?php echo e(trans('app.Expiring Soon')); ?>

                                    </small>
                                <?php else: ?>
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i><?php echo e(trans('app.Valid')); ?>

                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <span class="badge bg-info mb-1">
                                    <i class="fas fa-globe me-1"></i><?php echo e($license->active_domains_count ?? 0); ?>/<?php echo e($license->max_domains ?? 1); ?>

                                </span>
                                <small class="text-muted"><?php echo e(trans('app.Domains')); ?></small>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="<?php echo e(route('admin.licenses.show', $license)); ?>"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo e(trans('app.View')); ?>

                                </a>

                                <a href="<?php echo e(route('admin.licenses.edit', $license)); ?>"
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
                <?php echo e($licenses->links()); ?>

            </div>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-key text-muted empty-state-icon"></i>
            </div>
            <h4 class="text-muted"><?php echo e(trans('app.No Licenses Found')); ?></h4>
            <p class="text-muted mb-4"><?php echo e(trans('app.Create your first license to get started')); ?></p>
            <a href="<?php echo e(route('admin.licenses.create')); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>
                <?php echo e(trans('app.Add Your First License')); ?>

            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript is now handled by admin-categories.js -->

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\licenses\index.blade.php ENDPATH**/ ?>