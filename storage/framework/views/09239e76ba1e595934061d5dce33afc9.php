

<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid license-show">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-eye text-info me-2"></i>
                                <?php echo e(trans('app.View License')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($license->license_key); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.licenses.edit', $license)); ?>" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i>
                                <?php echo e(trans('app.Edit License')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.licenses.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Licenses')); ?>

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
            <!-- License Overview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        <?php echo e(trans('app.License Overview')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-key text-primary me-1"></i>
                                <?php echo e(trans('app.License Key')); ?>

                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo e($license->license_key); ?>" readonly>
                                <button class="btn btn-outline-secondary copy-btn" type="button" data-text="<?php echo e($license->license_key); ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-box text-success me-1"></i>
                                <?php echo e(trans('app.Product')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($license->product->name ?? trans('app.No Product')); ?></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user text-primary me-1"></i>
                                <?php echo e(trans('app.Owner')); ?>

                            </label>
                            <p class="text-muted">
                                <?php if($license->user): ?>
                                    <a href="<?php echo e(route('admin.users.show', $license->user)); ?>" class="text-decoration-none">
                                        <?php echo e($license->user->name); ?> (<?php echo e($license->user->email); ?>)
                                    </a>
                                <?php else: ?>
                                    <?php echo e(trans('app.No Owner')); ?>

                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tag text-warning me-1"></i>
                                <?php echo e(trans('app.License Type')); ?>

                            </label>
                            <p class="text-muted">
                                <span class="badge bg-<?php echo e($license->license_type == 'extended' ? 'success' : 'primary'); ?>">
                                    <?php echo e(trans('app.' . ucfirst($license->license_type))); ?>

                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-toggle-on text-info me-1"></i>
                                <?php echo e(trans('app.Status')); ?>

                            </label>
                            <p class="text-muted">
                                <span class="badge bg-<?php echo e($license->status == 'active' ? 'success' : ($license->status == 'expired' ? 'danger' : 'warning')); ?>">
                                    <?php echo e(trans('app.' . ucfirst($license->status))); ?>

                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-globe text-success me-1"></i>
                                <?php echo e(trans('app.Domains')); ?>

                            </label>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-<?php echo e($license->hasReachedDomainLimit() ? 'warning' : 'success'); ?> me-2">
                                    <?php echo e($license->active_domains_count); ?> / <?php echo e($license->max_domains ?? 1); ?>

                                </span>
                                <?php if($license->hasReachedDomainLimit()): ?>
                                    <small class="text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i><?php echo e(trans('app.Limit Reached')); ?>

                                    </small>
                                <?php else: ?>
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i><?php echo e($license->remaining_domains); ?> <?php echo e(trans('app.remaining')); ?>

                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if($license->expires_at): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-danger me-1"></i>
                                <?php echo e(trans('app.Expires At')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($license->expires_at->format('M d, Y H:i')); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($license->support_expires_at): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-headset text-success me-1"></i>
                                <?php echo e(trans('app.Support Expires At')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($license->support_expires_at->format('M d, Y H:i')); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-info me-1"></i>
                                <?php echo e(trans('app.Created At')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($license->created_at->format('M d, Y H:i')); ?></p>
                        </div>
                    </div>

                    <?php if($license->notes): ?>
                    <div class="mt-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-sticky-note text-warning me-1"></i>
                            <?php echo e(trans('app.Notes')); ?>

                        </label>
                        <div class="bg-light p-3 rounded">
                            <p class="text-muted mb-0"><?php echo e($license->notes); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- License Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        <?php echo e(trans('app.License Statistics')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-primary"><?php echo e($license->active_domains_count); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Used Domains')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-success"><?php echo e($license->max_domains ?? 1); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Max Domains')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-info"><?php echo e($license->remaining_domains); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Remaining Domains')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-warning"><?php echo e($license->logs_count ?? 0); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Activity Logs')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- License Domains -->
            <?php if($license->domains && $license->domains->count() > 0): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-globe me-2"></i>
                        <?php echo e(trans('app.License Domains')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo e(trans('app.Domain')); ?></th>
                                    <th><?php echo e(trans('app.Status')); ?></th>
                                    <th><?php echo e(trans('app.Verified At')); ?></th>
                                    <th><?php echo e(trans('app.Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $license->domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($domain->domain); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($domain->is_verified ? 'success' : 'warning'); ?>">
                                            <?php echo e($domain->is_verified ? trans('app.Verified') : trans('app.Pending')); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($domain->verified_at ? $domain->verified_at->format('M d, Y H:i') : '-'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger remove-domain-btn" data-domain-id="<?php echo e($domain->id); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

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
                                <h6 class="timeline-title"><?php echo e(trans('app.License Created')); ?></h6>
                                <p class="timeline-text text-muted"><?php echo e($license->created_at->format('M d, Y H:i')); ?></p>
                            </div>
                        </div>
                        <?php if($license->updated_at != $license->created_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?php echo e(trans('app.Last Updated')); ?></h6>
                                <p class="timeline-text text-muted"><?php echo e($license->updated_at->format('M d, Y H:i')); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if($license->domains_count > 0): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?php echo e(trans('app.Domains Added')); ?></h6>
                                <p class="timeline-text text-muted"><?php echo e($license->domains_count); ?> <?php echo e(trans('app.domains')); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- License Key -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        <?php echo e(trans('app.License Key')); ?>

                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="license-key-display mb-3">
                        <code class="fs-6"><?php echo e($license->license_key); ?></code>
                    </div>
                    <button class="btn btn-primary copy-btn" data-text="<?php echo e($license->license_key); ?>">
                        <i class="fas fa-copy me-1"></i>
                        <?php echo e(trans('app.Copy License Key')); ?>

                    </button>
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
                        <a href="<?php echo e(route('admin.licenses.edit', $license)); ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            <?php echo e(trans('app.Edit License')); ?>

                        </a>
                        <?php if($license->user): ?>
                        <a href="<?php echo e(route('admin.users.show', $license->user)); ?>" class="btn btn-outline-success">
                            <i class="fas fa-user me-1"></i>
                            <?php echo e(trans('app.View User')); ?>

                        </a>
                        <?php endif; ?>
                        <?php if($license->product): ?>
                        <a href="<?php echo e(route('admin.products.show', $license->product)); ?>" class="btn btn-outline-info">
                            <i class="fas fa-box me-1"></i>
                            <?php echo e(trans('app.View Product')); ?>

                        </a>
                        <?php endif; ?>
                        <button class="btn btn-outline-warning" id="regenerate-license-key-btn">
                            <i class="fas fa-sync me-1"></i>
                            <?php echo e(trans('app.Regenerate Key')); ?>

                        </button>
                    </div>
                </div>
            </div>

            <!-- License Details -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e(trans('app.License Details')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-info"><?php echo e($license->created_at->format('M Y')); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Created')); ?></p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-warning"><?php echo e($license->updated_at->format('M Y')); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Updated')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- License Status -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        <?php echo e(trans('app.License Status')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-toggle-on text-success me-1"></i>
                            <?php echo e(trans('app.Status')); ?>

                        </label>
                        <p class="text-muted">
                            <span class="badge bg-<?php echo e($license->status == 'active' ? 'success' : ($license->status == 'expired' ? 'danger' : 'warning')); ?>">
                                <?php echo e(trans('app.' . ucfirst($license->status))); ?>

                            </span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-globe text-info me-1"></i>
                            <?php echo e(trans('app.Domain Usage')); ?>

                        </label>
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" 
                                 data-width="<?php echo e($license->max_domains > 0 ? ($license->active_domains_count / $license->max_domains) * 100 : 0); ?>">
                            </div>
                        </div>
                        <p class="text-muted small mb-0">
                            <?php echo e($license->active_domains_count); ?> / <?php echo e($license->max_domains ?? 1); ?> <?php echo e(trans('app.domains used')); ?>

                            <?php if($license->remaining_domains > 0): ?>
                                (<?php echo e($license->remaining_domains); ?> <?php echo e(trans('app.remaining')); ?>)
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if($license->expires_at): ?>
                    <div class="mb-0">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar text-danger me-1"></i>
                            <?php echo e(trans('app.Expiration')); ?>

                        </label>
                        <p class="text-muted small mb-0">
                            <?php echo e($license->expires_at->format('M d, Y')); ?>

                            <?php if($license->expires_at->isFuture()): ?>
                                (<?php echo e($license->expires_at->diffForHumans()); ?>)
                            <?php else: ?>
                                (<?php echo e(trans('app.Expired')); ?>)
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/admin/licenses/show.blade.php ENDPATH**/ ?>