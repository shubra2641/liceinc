<?php $__env->startSection('title', 'Invoices'); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="admin-page-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1><?php echo e(trans('app.Invoice Management')); ?></h1>
            <p class="admin-page-subtitle"><?php echo e(trans('app.Manage system invoices and payments')); ?></p>
        </div>
        <div class="admin-page-actions">
            <a href="<?php echo e(route('admin.invoices.create')); ?>" class="admin-btn admin-btn-info admin-btn-m">
                <i class="fas fa-plus me-2"></i>
                <?php echo e(trans('app.Create Invoice')); ?>

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
                <small class="text-muted"><?php echo e(trans('app.Filter and search invoices')); ?></small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="searchInvoices" class="form-label"><?php echo e(trans('app.Search')); ?></label>
                <input type="text" id="searchInvoices" class="form-control" 
                       placeholder="<?php echo e(trans('app.Search by invoice number or user')); ?>">
            </div>
            <div class="col-md-3">
                <label for="status-filter" class="form-label"><?php echo e(trans('app.Status')); ?></label>
                <select id="status-filter" class="form-select">
                    <option value=""><?php echo e(trans('app.All Statuses')); ?></option>
                    <option value="pending"><?php echo e(trans('app.Pending')); ?></option>
                    <option value="paid"><?php echo e(trans('app.Paid')); ?></option>
                    <option value="overdue"><?php echo e(trans('app.Overdue')); ?></option>
                    <option value="cancelled"><?php echo e(trans('app.Cancelled')); ?></option>
                    <option value="suspended"><?php echo e(trans('app.Suspended')); ?></option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date-from" class="form-label"><?php echo e(trans('app.Date From')); ?></label>
                <input type="date" id="date-from" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="date-to" class="form-label"><?php echo e(trans('app.Date To')); ?></label>
                <input type="date" id="date-to" class="form-control">
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Section -->
<div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
    <!-- Total Invoices Stats Card -->
    <div class="stats-card stats-card-primary animate-slide-up">
        <div class="stats-card-background">
            <div class="stats-card-pattern"></div>
        </div>
        <div class="stats-card-content">
            <div class="stats-card-header">
                <div class="stats-card-icon invoices"></div>
                <div class="stats-card-menu">
                    <button class="stats-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
            <div class="stats-card-body">
                <div class="stats-card-value"><?php echo e($invoices->total()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Total Invoices')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(trans('app.all_invoices')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Paid Invoices Stats Card -->
    <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
        <div class="stats-card-background">
            <div class="stats-card-pattern"></div>
        </div>
        <div class="stats-card-content">
            <div class="stats-card-header">
                <div class="stats-card-icon paid"></div>
                <div class="stats-card-menu">
                    <button class="stats-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
            <div class="stats-card-body">
                <div class="stats-card-value"><?php echo e($invoices->where('status', 'paid')->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Paid Invoices')); ?></div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span><?php echo e(number_format(($invoices->where('status', 'paid')->count() / max($invoices->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Invoices Stats Card -->
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
                <div class="stats-card-value"><?php echo e($invoices->where('status', 'pending')->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Pending Invoices')); ?></div>
                <div class="stats-card-trend negative">
                    <i class="stats-trend-icon negative"></i>
                    <span><?php echo e(number_format(($invoices->where('status', 'pending')->count() / max($invoices->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Invoices Stats Card -->
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
                <div class="stats-card-value"><?php echo e($invoices->where('status', 'overdue')->count()); ?></div>
                <div class="stats-card-label"><?php echo e(trans('app.Overdue Invoices')); ?></div>
                <div class="stats-card-trend negative">
                    <i class="stats-trend-icon negative"></i>
                    <span><?php echo e(number_format(($invoices->where('status', 'overdue')->count() / max($invoices->count(), 1)) * 100, 1)); ?>% <?php echo e(trans('app.of_total')); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-file-invoice-dollar me-3 text-primary"></i>
                <div>
                    <h5 class="card-title mb-0"><?php echo e(trans('app.All Invoices')); ?></h5>
                    <small class="text-muted"><?php echo e(trans('app.Manage and monitor all system invoices')); ?></small>
                </div>
            </div>
            <div>
                <span class="badge bg-info fs-6"><?php echo e($invoices->total()); ?> <?php echo e(trans('app.Invoices')); ?></span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <?php if($invoices->count() > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center"><?php echo e(trans('app.Avatar')); ?></th>
                        <th><?php echo e(trans('app.Invoice')); ?></th>
                        <th><?php echo e(trans('app.User')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Product')); ?></th>
                        <th class="text-end"><?php echo e(trans('app.Amount')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Status')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Due Date')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Created')); ?></th>
                        <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="invoice-row" data-number="<?php echo e(strtolower($invoice->invoice_number)); ?>" data-user="<?php echo e(strtolower($invoice->user->name ?? '')); ?>" data-status="<?php echo e($invoice->status); ?>">
                        <td class="text-center">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center invoice-avatar">
                                <span class="text-muted small fw-bold"><?php echo e(strtoupper(substr($invoice->invoice_number, 0, 1))); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?php echo e($invoice->invoice_number); ?></div>
                            <small class="text-muted">ID: <?php echo e($invoice->id); ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?php echo e($invoice->user->name ?? 'N/A'); ?></div>
                            <small class="text-muted"><?php echo e($invoice->user->email ?? ''); ?></small>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark"><?php echo e($invoice->license->product->name ?? 'N/A'); ?></div>
                            <?php if($invoice->license): ?>
                            <small class="text-muted"><?php echo e($invoice->license->license_type ?? ''); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="fw-semibold text-dark">$<?php echo e(number_format($invoice->amount, 2)); ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge <?php echo e($invoice->status === 'paid' ? 'bg-success' : ($invoice->status === 'overdue' ? 'bg-danger' : ($invoice->status === 'pending' ? 'bg-warning' : ($invoice->status === 'cancelled' ? 'bg-secondary' : 'bg-info')))); ?>">
                                <?php if($invoice->status === 'paid'): ?>
                                    <i class="fas fa-check-circle me-1"></i><?php echo e(trans('app.Paid')); ?>

                                <?php elseif($invoice->status === 'pending'): ?>
                                    <i class="fas fa-clock me-1"></i><?php echo e(trans('app.Pending')); ?>

                                <?php elseif($invoice->status === 'overdue'): ?>
                                    <i class="fas fa-exclamation-triangle me-1"></i><?php echo e(trans('app.Overdue')); ?>

                                <?php elseif($invoice->status === 'cancelled'): ?>
                                    <i class="fas fa-times-circle me-1"></i><?php echo e(trans('app.Cancelled')); ?>

                                <?php else: ?>
                                    <i class="fas fa-pause-circle me-1"></i><?php echo e(ucfirst($invoice->status)); ?>

                                <?php endif; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if($invoice->due_date): ?>
                                <div class="fw-semibold text-dark"><?php echo e($invoice->due_date->format('M d, Y')); ?></div>
                                <?php if($invoice->due_date->isPast() && $invoice->status === 'pending'): ?>
                                    <small class="text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i><?php echo e(trans('app.Overdue')); ?>

                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark"><?php echo e($invoice->created_at->format('M d, Y')); ?></div>
                            <small class="text-muted"><?php echo e($invoice->created_at->diffForHumans()); ?></small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="<?php echo e(route('admin.invoices.show', $invoice)); ?>"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo e(trans('app.View')); ?>

                                </a>

                                <?php if($invoice->status === 'pending'): ?>
                                <form method="POST" action="<?php echo e(route('admin.invoices.mark-paid', $invoice)); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="btn btn-outline-success btn-sm w-100"
                                            data-confirm="<?php echo e(trans('app.Are you sure you want to mark this invoice as paid?')); ?>">
                                        <i class="fas fa-check me-1"></i>
                                        <?php echo e(trans('app.Paid')); ?>

                                    </button>
                                </form>

                                <form method="POST" action="<?php echo e(route('admin.invoices.cancel', $invoice)); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                            data-confirm="<?php echo e(trans('app.Are you sure you want to cancel this invoice?')); ?>">
                                        <i class="fas fa-times me-1"></i>
                                        <?php echo e(trans('app.Cancel')); ?>

                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <?php if($invoices->hasPages()): ?>
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                <?php echo e($invoices->links()); ?>

            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-file-invoice-dollar text-muted empty-state-icon"></i>
            </div>
            <h4 class="text-muted"><?php echo e(trans('app.No Invoices Found')); ?></h4>
            <p class="text-muted mb-4"><?php echo e(trans('app.Create your first invoice to get started')); ?></p>
            <a href="<?php echo e(route('admin.invoices.create')); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>
                <?php echo e(trans('app.Create Your First Invoice')); ?>

            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/admin/invoices/index.blade.php ENDPATH**/ ?>