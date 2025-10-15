<?php $__env->startSection('title', 'Show Invoice'); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid invoice-show">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-eye text-info me-2"></i>
                                <?php echo e(trans('app.View Invoice')); ?>

                            </h1>
                            <p class="text-muted mb-0">#<?php echo e($invoice->invoice_number ?? $invoice->id); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.invoices.edit', $invoice)); ?>" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i>
                                <?php echo e(trans('app.Edit Invoice')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.invoices.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Invoices')); ?>

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
            <!-- Invoice Overview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        <?php echo e(trans('app.Invoice Overview')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-hashtag text-primary me-1"></i>
                                <?php echo e(trans('app.Invoice Number')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($invoice->invoice_number ?? '#' . $invoice->id); ?></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user text-success me-1"></i>
                                <?php echo e(trans('app.Customer')); ?>

                            </label>
                            <p class="text-muted">
                                <?php if($invoice->user): ?>
                                    <a href="<?php echo e(route('admin.users.show', $invoice->user)); ?>" class="text-decoration-none">
                                        <?php echo e($invoice->user->name); ?> (<?php echo e($invoice->user->email); ?>)
                                    </a>
                                <?php else: ?>
                                    <?php echo e(trans('app.No Customer')); ?>

                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tag text-warning me-1"></i>
                                <?php echo e(trans('app.Invoice Type')); ?>

                            </label>
                            <p class="text-muted">
                                <span class="badge bg-<?php echo e($invoice->type == 'custom' ? 'info' : 'primary'); ?>">
                                    <?php echo e(trans('app.' . ucfirst($invoice->type))); ?>

                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                <?php echo e(trans('app.Status')); ?>

                            </label>
                            <p class="text-muted">
                                <span class="badge bg-<?php echo e($invoice->status == 'paid' ? 'success' : ($invoice->status == 'overdue' ? 'danger' : ($invoice->status == 'cancelled' ? 'secondary' : 'warning'))); ?>">
                                    <?php echo e(trans('app.' . ucfirst($invoice->status))); ?>

                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-dollar-sign text-success me-1"></i>
                                <?php echo e(trans('app.Amount')); ?>

                            </label>
                            <p class="text-muted fs-5 fw-bold"><?php echo e($invoice->amount); ?> <?php echo e($invoice->currency); ?></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-danger me-1"></i>
                                <?php echo e(trans('app.Due Date')); ?>

                            </label>
                            <p class="text-muted">
                                <?php echo e($invoice->due_date ? $invoice->due_date->format('M d, Y') : trans('app.No Due Date')); ?>

                            </p>
                        </div>

                        <?php if($invoice->paid_at): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-check text-success me-1"></i>
                                <?php echo e(trans('app.Paid At')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($invoice->paid_at->format('M d, Y H:i')); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-info me-1"></i>
                                <?php echo e(trans('app.Created At')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($invoice->created_at->format('M d, Y H:i')); ?></p>
                        </div>

                        <?php if($invoice->license): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-key text-warning me-1"></i>
                                <?php echo e(trans('app.License')); ?>

                            </label>
                            <p class="text-muted">
                                <a href="<?php echo e(route('admin.licenses.show', $invoice->license)); ?>" class="text-decoration-none">
                                    <?php echo e($invoice->license->product->name); ?> - <?php echo e($invoice->license->license_type); ?>

                                </a>
                            </p>
                        </div>
                        <?php endif; ?>

                        <?php if($invoice->custom_product_name): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-shopping-cart text-primary me-1"></i>
                                <?php echo e(trans('app.Product/Service')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($invoice->custom_product_name); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($invoice->custom_invoice_type): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-cog text-info me-1"></i>
                                <?php echo e(trans('app.Custom Invoice Type')); ?>

                            </label>
                            <p class="text-muted"><?php echo e(trans('app.' . ucfirst(str_replace('_', ' ', $invoice->custom_invoice_type)))); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($invoice->expiration_date): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-times text-danger me-1"></i>
                                <?php echo e(trans('app.Expiration Date')); ?>

                            </label>
                            <p class="text-muted"><?php echo e($invoice->expiration_date->format('M d, Y')); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if($invoice->notes): ?>
                    <div class="mt-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-sticky-note text-warning me-1"></i>
                            <?php echo e(trans('app.Notes')); ?>

                        </label>
                        <div class="bg-light p-3 rounded">
                            <p class="text-muted mb-0"><?php echo e($invoice->notes); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Invoice Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        <?php echo e(trans('app.Invoice Statistics')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-primary"><?php echo e($invoice->amount); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Total Amount')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-success"><?php echo e($invoice->currency); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Currency')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-info"><?php echo e($invoice->user->invoices_count ?? 0); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Customer Invoices')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-warning"><?php echo e($invoice->days_remaining ?? 0); ?></h3>
                                <p class="text-muted mb-0"><?php echo e(trans('app.Days Remaining')); ?></p>
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
                                <h6 class="timeline-title"><?php echo e(trans('app.Invoice Created')); ?></h6>
                                <p class="timeline-text text-muted"><?php echo e($invoice->created_at->format('M d, Y H:i')); ?></p>
                            </div>
                        </div>
                        <?php if($invoice->updated_at != $invoice->created_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?php echo e(trans('app.Last Updated')); ?></h6>
                                <p class="timeline-text text-muted"><?php echo e($invoice->updated_at->format('M d, Y H:i')); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if($invoice->paid_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?php echo e(trans('app.Invoice Paid')); ?></h6>
                                <p class="timeline-text text-muted"><?php echo e($invoice->paid_at->format('M d, Y H:i')); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if($invoice->status == 'overdue'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?php echo e(trans('app.Invoice Overdue')); ?></h6>
                                <p class="timeline-text text-muted"><?php echo e($invoice->due_date ? $invoice->due_date->format('M d, Y') : trans('app.Due Date Passed')); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Invoice Status -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        <?php echo e(trans('app.Invoice Status')); ?>

                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="invoice-status-display mb-3">
                        <i class="fas fa-file-invoice-dollar fs-1 text-primary mb-2"></i>
                        <h5>#<?php echo e($invoice->invoice_number ?? $invoice->id); ?></h5>
                        <span class="badge bg-<?php echo e($invoice->status == 'paid' ? 'success' : ($invoice->status == 'overdue' ? 'danger' : ($invoice->status == 'cancelled' ? 'secondary' : 'warning'))); ?> fs-6">
                            <?php echo e(trans('app.' . ucfirst($invoice->status))); ?>

                        </span>
                    </div>
                    <div class="invoice-amount-display">
                        <h3 class="text-primary"><?php echo e($invoice->amount); ?> <?php echo e($invoice->currency); ?></h3>
                        <p class="text-muted small mb-0"><?php echo e(trans('app.Total Amount')); ?></p>
                    </div>
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
                        <a href="<?php echo e(route('admin.invoices.edit', $invoice)); ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            <?php echo e(trans('app.Edit Invoice')); ?>

                        </a>
                        <?php if($invoice->user): ?>
                        <a href="<?php echo e(route('admin.users.show', $invoice->user)); ?>" class="btn btn-outline-success">
                            <i class="fas fa-user me-1"></i>
                            <?php echo e(trans('app.View Customer')); ?>

                        </a>
                        <?php endif; ?>
                        <?php if($invoice->license): ?>
                        <a href="<?php echo e(route('admin.licenses.show', $invoice->license)); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-key me-1"></i>
                            <?php echo e(trans('app.View License')); ?>

                        </a>
                        <?php endif; ?>
                        <button class="btn btn-outline-info" id="print-invoice-btn">
                            <i class="fas fa-print me-1"></i>
                            <?php echo e(trans('app.Print Invoice')); ?>

                        </button>
                    </div>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e(trans('app.Invoice Details')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-info"><?php echo e($invoice->created_at->format('M Y')); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Created')); ?></p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-warning"><?php echo e($invoice->updated_at->format('M Y')); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Updated')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        <?php echo e(trans('app.Payment Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-dollar-sign text-success me-1"></i>
                            <?php echo e(trans('app.Amount')); ?>

                        </label>
                        <p class="text-muted fs-5 fw-bold"><?php echo e($invoice->amount); ?> <?php echo e($invoice->currency); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar text-danger me-1"></i>
                            <?php echo e(trans('app.Due Date')); ?>

                        </label>
                        <p class="text-muted">
                            <?php echo e($invoice->due_date ? $invoice->due_date->format('M d, Y') : trans('app.No Due Date')); ?>

                        </p>
                    </div>
                    <?php if($invoice->paid_at): ?>
                    <div class="mb-0">
                        <label class="form-label fw-bold">
                            <i class="fas fa-check text-success me-1"></i>
                            <?php echo e(trans('app.Paid At')); ?>

                        </label>
                        <p class="text-muted"><?php echo e($invoice->paid_at->format('M d, Y H:i')); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/admin/invoices/show.blade.php ENDPATH**/ ?>