

<?php $__env->startSection('title', trans('license-logs.details_title')); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                <?php echo e(trans('license-logs.details_title')); ?>

                            </h1>
                            <p class="text-muted mb-0">ID: <?php echo e($log->id); ?> - <?php echo e($log->created_at->format('M d, Y H:i:s')); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.license-verification-logs.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('license-logs.back_to_logs')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Basic Information -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e(trans('license-logs.basic_information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?php echo e(trans('license-logs.id')); ?>:</span>
                                <strong><?php echo e($log->id); ?></strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?php echo e(trans('license-logs.purchase_code_hash')); ?>:</span>
                                <code class="bg-light px-2 py-1 rounded"><?php echo e($log->purchase_code_hash); ?></code>
                            </div>
                            <small class="text-muted d-block mt-1">(<?php echo e(trans('license-logs.masked_purchase_code')); ?>: <?php echo e($log->masked_purchase_code); ?>)</small>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?php echo e(trans('license-logs.domain')); ?>:</span>
                                <span class="badge bg-info"><?php echo e($log->domain); ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?php echo e(trans('license-logs.ip_address')); ?>:</span>
                                <span class="badge bg-secondary"><?php echo e($log->ip_address); ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?php echo e(trans('license-logs.status')); ?>:</span>
                                <span class="badge <?php echo e($log->status_badge_class); ?>">
                                    <?php echo e(trans('license-logs.status_' . $log->status)); ?>

                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?php echo e(trans('license-logs.is_valid')); ?>:</span>
                                <?php if($log->is_valid): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i> <?php echo e(trans('license-logs.valid')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i> <?php echo e(trans('license-logs.invalid')); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?php echo e(trans('license-logs.source')); ?>:</span>
                                <span class="badge <?php echo e($log->source_badge_class); ?>">
                                    <?php echo e(trans('license-logs.source_' . $log->verification_source)); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Response Information -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-reply me-2"></i>
                        <?php echo e(trans('license-logs.response_information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="text-muted"><?php echo e(trans('license-logs.response_message')); ?>:</span>
                                <div class="text-end">
                                    <span class="text-dark"><?php echo e($log->response_message); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php if($log->error_details): ?>
                        <div class="col-12">
                            <div class="alert alert-danger border-0">
                                <strong><?php echo e(trans('license-logs.error_details')); ?>:</strong>
                                <pre class="mb-0 mt-2"><?php echo e($log->error_details); ?></pre>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if($log->verified_at): ?>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?php echo e(trans('license-logs.verified_at')); ?>:</span>
                                <div class="text-end">
                                    <i class="fas fa-clock text-muted me-1"></i>
                                    <span class="text-dark"><?php echo e($log->verified_at->format('M d, Y H:i:s')); ?></span>
                                    <small class="text-muted d-block"><?php echo e($log->verified_at->diffForHumans()); ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><?php echo e(trans('license-logs.created_at')); ?>:</span>
                                <div class="text-end">
                                    <i class="fas fa-clock text-muted me-1"></i>
                                    <span class="text-dark"><?php echo e($log->created_at->format('M d, Y H:i:s')); ?></span>
                                    <small class="text-muted d-block"><?php echo e($log->created_at->diffForHumans()); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Agent Information -->
    <?php if($log->user_agent): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-desktop me-2"></i>
                        <?php echo e(trans('license-logs.user_agent_information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0">
                        <strong><?php echo e(trans('license-logs.user_agent')); ?>:</strong>
                        <pre class="mb-0 mt-2"><?php echo e($log->user_agent); ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Response Data -->
    <?php if($log->response_data): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-database me-2"></i>
                        <?php echo e(trans('license-logs.response_data')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded border"><code><?php echo e(json_encode($log->response_data, JSON_PRETTY_PRINT)); ?></code></pre>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Security Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        <?php echo e(trans('license-logs.security_information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-search me-2"></i>
                                    <?php echo e(trans('license-logs.ip_address_analysis')); ?>

                                </h6>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">IP:</span>
                                    <strong><?php echo e($log->ip_address); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted"><?php echo e(trans('license-logs.ip_type')); ?>:</span>
                                    <?php if(filter_var($log->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)): ?>
                                        <span class="badge bg-primary"><?php echo e(trans('license-logs.ipv4')); ?></span>
                                    <?php elseif(filter_var($log->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)): ?>
                                        <span class="badge bg-info"><?php echo e(trans('license-logs.ipv6')); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo e(trans('license-logs.unknown')); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <h6 class="text-success mb-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo e(trans('license-logs.verification_context')); ?>

                                </h6>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted"><?php echo e(trans('license-logs.source')); ?>:</span>
                                    <strong><?php echo e(trans('license-logs.source_' . $log->verification_source)); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted"><?php echo e(trans('license-logs.result')); ?>:</span>
                                    <?php if($log->is_valid): ?>
                                        <span class="badge bg-success"><?php echo e(trans('license-logs.successful')); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?php echo e(trans('license-logs.failed')); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\license-verification-logs\show.blade.php ENDPATH**/ ?>