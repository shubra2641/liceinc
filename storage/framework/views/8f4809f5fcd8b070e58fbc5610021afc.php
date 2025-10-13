<?php $__env->startSection('admin-content'); ?>
<div class="admin-page-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1><?php echo e(trans('app.System Updates')); ?></h1>
            <p class="admin-page-subtitle"><?php echo e(trans('app.Manage system updates and version control')); ?></p>
        </div>
        <div class="admin-page-actions">
            <form method="POST" action="<?php echo e(route('admin.updates.check')); ?>" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="admin-btn admin-btn-info admin-btn-m">
                    <i class="fas fa-sync me-2"></i>
                    <?php echo e(trans('app.Check for Updates')); ?>

                </button>
            </form>
            <button type="button" class="admin-btn admin-btn-primary admin-btn-m" data-bs-toggle="modal" data-bs-target="#autoUpdateModal">
                <i class="fas fa-magic me-2"></i>
                <?php echo e(trans('app.Auto Update')); ?>

            </button>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <?php echo e(session('success')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?php echo e(session('error')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if(session('info')): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fas fa-info-circle me-2"></i>
    <?php echo e(session('info')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if(session('warning')): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?php echo e(session('warning')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>





<!-- System Status Card -->
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stats-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-server text-primary fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 text-dark"><?php echo e(trans('app.System Status')); ?></h4>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Current version')); ?>: 
                                    <span class="badge bg-primary"><?php echo e($versionStatus['current_version']); ?></span>
                                </p>
                            </div>
                        </div>
                        
                        <?php if(isset($updateInfo) && $updateInfo && $updateInfo['is_update_available']): ?>
                            <div class="alert alert-warning mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <div>
                                        <strong><?php echo e(trans('app.Next Update Available')); ?>:</strong> 
                                        <span class="badge bg-warning"><?php echo e($updateInfo['next_version']); ?></span>
                                        <br>
                                        <small class="text-muted"><?php echo e(trans('app.Updates must be installed sequentially')); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <div>
                                        <strong><?php echo e(trans('app.System is up to date')); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo e(trans('app.No updates available')); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-end">
                        <form method="POST" action="<?php echo e(route('admin.updates.check')); ?>" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sync me-2"></i>
                                <?php echo e(trans('app.Check for Updates')); ?>

                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Information -->
<?php if(isset($updateInfo) && $updateInfo && $updateInfo['is_update_available']): ?>
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-download me-2"></i>
                    <?php echo e(trans('app.Next Update Available')); ?> - <?php echo e($updateInfo['next_version']); ?>

                </h5>
            </div>
            <div class="card-body p-4">

                <!-- Update Details -->
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="text-dark mb-3"><?php echo e($updateInfo['update_info']['title'] ?? 'Update'); ?></h5>
                        
                        <?php if(isset($updateInfo['update_info']['description']) && $updateInfo['update_info']['description']): ?>
                            <p class="text-muted mb-3"><?php echo e($updateInfo['update_info']['description']); ?></p>
                        <?php endif; ?>
                        
                        <?php if(isset($updateInfo['update_info']['changelog']) && is_array($updateInfo['update_info']['changelog'])): ?>
                            <div class="mb-3">
                                <h6 class="text-dark"><?php echo e(trans('app.Changelog')); ?>:</h6>
                                <ul class="list-unstyled">
                                    <?php $__currentLoopData = $updateInfo['update_info']['changelog']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <?php echo e($item); ?>

                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo e(trans('app.Update Details')); ?></h6>
                                
                                <div class="mb-2">
                                    <small class="text-muted"><?php echo e(trans('app.Version')); ?>:</small>
                                    <div class="fw-bold"><?php echo e($updateInfo['next_version']); ?></div>
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-muted"><?php echo e(trans('app.Type')); ?>:</small>
                                    <div>
                                        <?php if($updateInfo['update_info']['is_major'] ?? false): ?>
                                            <span class="badge bg-warning"><?php echo e(trans('app.Major')); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-info"><?php echo e(trans('app.Minor')); ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if($updateInfo['update_info']['is_required'] ?? false): ?>
                                            <span class="badge bg-danger ms-1"><?php echo e(trans('app.Required')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if(isset($updateInfo['update_info']['file_size'])): ?>
                                <div class="mb-2">
                                    <small class="text-muted"><?php echo e(trans('app.File Size')); ?>:</small>
                                    <div class="fw-bold"><?php echo e(number_format($updateInfo['update_info']['file_size'] / 1024 / 1024, 2)); ?> MB</div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if(isset($updateInfo['update_info']['release_date'])): ?>
                                <div class="mb-3">
                                    <small class="text-muted"><?php echo e(trans('app.Release Date')); ?>:</small>
                                    <div class="fw-bold"><?php echo e($updateInfo['update_info']['release_date']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 flex-wrap mt-4">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#autoUpdateModal">
                        <i class="fas fa-magic me-2"></i>
                        <?php echo e(trans('app.Auto Update')); ?>

                    </button>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#uploadUpdateModal">
                        <i class="fas fa-upload me-2"></i>
                        <?php echo e(trans('app.Upload Update')); ?>

                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Update Actions -->
<?php if($versionStatus['is_update_available']): ?>
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0">
            <i class="fas fa-download me-2"></i>
            <?php echo e(trans('app.Update Available')); ?>

        </h5>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="mb-2"><?php echo e(trans('app.New Version Available')); ?></h6>
                <p class="text-muted mb-0">
                    <?php echo e(trans('app.A new version')); ?> <strong><?php echo e($versionStatus['latest_version']); ?></strong> 
                    <?php echo e(trans('app.is available. Current version is')); ?> <strong><?php echo e($versionStatus['current_version']); ?></strong>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?php echo e(route('admin.updates.confirm', ['version' => $versionStatus['latest_version']])); ?>" class="btn btn-warning btn-lg">
                    <i class="fas fa-download me-2"></i>
                    <?php echo e(trans('app.Update Now')); ?>

                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Version History -->
<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center">
            <i class="fas fa-history me-3 text-primary"></i>
            <div>
                <h5 class="card-title mb-0"><?php echo e(trans('app.Version History')); ?></h5>
                <small class="text-muted"><?php echo e(trans('app.Release notes and changelog')); ?></small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="version-history-content">
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                <p class="text-muted"><?php echo e(trans('app.Loading version history...')); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Update Confirmation Modal -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo e(trans('app.Confirm System Update')); ?>

                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong><?php echo e(trans('app.Warning')); ?>:</strong> <?php echo e(trans('app.System update will perform the following actions')); ?>:
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-database text-primary me-2"></i>
                        <?php echo e(trans('app.Run database migrations')); ?>

                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-broom text-info me-2"></i>
                        <?php echo e(trans('app.Clear all caches')); ?>

                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-sync text-success me-2"></i>
                        <?php echo e(trans('app.Optimize application')); ?>

                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-tag text-warning me-2"></i>
                        <?php echo e(trans('app.Update version number')); ?>

                    </li>
                </ul>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmUpdate">
                    <label class="form-check-label" for="confirmUpdate">
                        <?php echo e(trans('app.I understand the risks and want to proceed with the update')); ?>

                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?php echo e(trans('app.Cancel')); ?>

                </button>
                <button type="button" class="btn btn-warning" id="confirm-update-btn" disabled>
                    <i class="fas fa-download me-2"></i>
                    <?php echo e(trans('app.Proceed with Update')); ?>

                </button>
            </div>
        </div>
    </div>
</div>

<!-- Version Details Modal -->
<div class="modal fade" id="versionDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="versionDetailsTitle">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo e(trans('app.Version Details')); ?>

                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="versionDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?php echo e(trans('app.Close')); ?>

                </button>
            </div>
        </div>
    </div>
</div>



<!-- Upload Update Modal -->
<div class="modal fade" id="uploadUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-upload me-2"></i>
                    <?php echo e(trans('app.Upload Update Package')); ?>

                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong><?php echo e(trans('app.Note')); ?>:</strong> <?php echo e(trans('app.Upload a ZIP file containing the update package')); ?>

                </div>

                <form method="POST" action="<?php echo e(route('admin.updates.upload-package')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label for="update_package" class="form-label">
                            <i class="fas fa-file-archive text-primary me-1"></i>
                            <?php echo e(trans('app.Update Package')); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="update_package" name="update_package"
                               accept=".zip" required>
                        <div class="form-text"><?php echo e(trans('app.Select a ZIP file containing the update files')); ?></div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <?php echo e(trans('app.Cancel')); ?>

                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-upload me-2"></i>
                            <?php echo e(trans('app.Upload & Process')); ?>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Auto Update Modal -->
<div class="modal fade" id="autoUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-magic me-2"></i>
                    <?php echo e(trans('app.Auto Update')); ?>

                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong><?php echo e(trans('app.Note')); ?>:</strong> <?php echo e(trans('app.Enter your license information to check for and install updates automatically')); ?>

                </div>
                
                <form id="autoUpdateForm" method="POST" action="<?php echo e(route('admin.updates.auto-check')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="auto-license-key" class="form-label">
                                    <i class="fas fa-key text-primary me-1"></i>
                                    <?php echo e(trans('app.License Key')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="auto-license-key" name="license_key" 
                                       placeholder="XXXX-XXXX-XXXX-XXXX" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="auto-product-slug" class="form-label">
                                    <i class="fas fa-tag text-success me-1"></i>
                                    <?php echo e(trans('app.Product Slug')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="auto-product-slug" name="product_slug" 
                                       value="the-ultimate-license-management-system" readonly required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="auto-domain" class="form-label">
                                    <i class="fas fa-globe text-info me-1"></i>
                                    <?php echo e(trans('app.Domain')); ?>

                                </label>
                                <input type="text" class="form-control" id="auto-domain" name="domain" 
                                       value="<?php echo e(parse_url(config('app.url'), PHP_URL_HOST)); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="auto-current-version" class="form-label">
                                    <i class="fas fa-code-branch text-warning me-1"></i>
                                    <?php echo e(trans('app.Current Version')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="auto-current-version" name="current_version" 
                                       value="<?php echo e(\App\Helpers\VersionHelper::getCurrentVersion()); ?>" readonly required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="check-auto-updates-btn">
                            <i class="fas fa-search me-2"></i>
                            <?php echo e(trans('app.Check for Updates')); ?>

                        </button>
                    </div>
                </form>
                
                <div id="auto-update-info" class="auto-update-info mt-4"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?php echo e(trans('app.Close')); ?>

                </button>
            </div>
        </div>
    </div>
</div>

<!-- Rollback Confirmation Modal -->
<div class="modal fade" id="rollbackModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-undo me-2"></i>
                    <?php echo e(trans('app.Confirm System Rollback')); ?>

                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong><?php echo e(trans('app.Warning')); ?>:</strong> <?php echo e(trans('app.System rollback will restore the system to a previous version')); ?>

                </div>
                <p><?php echo e(trans('app.This action will')); ?>:</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-undo text-danger me-2"></i>
                        <?php echo e(trans('app.Restore system files from backup')); ?>

                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-database text-warning me-2"></i>
                        <?php echo e(trans('app.Rollback database changes')); ?>

                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-broom text-info me-2"></i>
                        <?php echo e(trans('app.Clear all caches')); ?>

                    </li>
                </ul>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmRollback">
                    <label class="form-check-label" for="confirmRollback">
                        <?php echo e(trans('app.I understand the risks and want to proceed with the rollback')); ?>

                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?php echo e(trans('app.Cancel')); ?>

                </button>
                <button type="button" class="btn btn-danger" id="confirm-rollback-btn" disabled>
                    <i class="fas fa-undo me-2"></i>
                    <?php echo e(trans('app.Proceed with Rollback')); ?>

                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/admin/updates/index.blade.php ENDPATH**/ ?>