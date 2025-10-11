

<?php $__env->startSection('admin-content'); ?>
<!-- Admin Tickets Page -->
<div class="admin-tickets-page">
    <div class="admin-page-header modern-header">
        <div class="admin-page-header-content">
            <div class="admin-page-title">
                <h1 class="gradient-text"><?php echo e(trans('app.Tickets Management')); ?></h1>
                <p class="admin-page-subtitle"><?php echo e(trans('app.Handle Customer Support Tickets')); ?></p>
            </div>
            <div class="admin-page-actions">
                <a href="<?php echo e(route('admin.tickets.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-plus me-2"></i>
                    <?php echo e(trans('app.Create Ticket for User')); ?>

                </a>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-filter me-2"></i><?php echo e(trans('app.Filters')); ?></h2>
            <div class="admin-section-actions">
                <div class="admin-search-box">
                    <input type="text" class="admin-form-input" id="searchTickets" 
                           placeholder="<?php echo e(trans('app.Search Tickets')); ?>">
                    <i class="fas fa-search admin-search-icon"></i>
                </div>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="category-filter">
                            <i class="fas fa-tag me-1"></i><?php echo e(trans('app.Category')); ?>

                        </label>
                        <select id="category-filter" class="admin-form-input">
                            <option value=""><?php echo e(trans('app.All Categories')); ?></option>
                            <?php $__currentLoopData = \App\Models\TicketCategory::active()->ordered()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="status-filter">
                            <i class="fas fa-info-circle me-1"></i><?php echo e(trans('app.Status')); ?>

                        </label>
                        <select id="status-filter" class="admin-form-input">
                            <option value=""><?php echo e(trans('app.All Status')); ?></option>
                            <option value="open"><?php echo e(trans('app.Open')); ?></option>
                            <option value="pending"><?php echo e(trans('app.Pending')); ?></option>
                            <option value="resolved"><?php echo e(trans('app.Resolved')); ?></option>
                            <option value="closed"><?php echo e(trans('app.Closed')); ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="priority-filter">
                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e(trans('app.Priority')); ?>

                        </label>
                        <select id="priority-filter" class="admin-form-input">
                            <option value=""><?php echo e(trans('app.All Priorities')); ?></option>
                            <option value="high"><?php echo e(trans('app.High')); ?></option>
                            <option value="medium"><?php echo e(trans('app.Medium')); ?></option>
                            <option value="low"><?php echo e(trans('app.Low')); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-ticket-alt me-2"></i><?php echo e(trans('app.All Tickets')); ?></h2>
            <span class="admin-badge admin-badge-info"><?php echo e($tickets->total()); ?> <?php echo e(trans('app.Tickets')); ?></span>
        </div>
        <div class="admin-section-content">
            <?php if($tickets->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 tickets-table">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center"><?php echo e(trans('app.Subject')); ?></th>
                            <th><?php echo e(trans('app.User')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.Category')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.Priority')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.Status')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.Created')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.Invoice')); ?></th>
                            <th class="text-center"><?php echo e(trans('app.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="ticket-row <?php echo e($ticket->replies->count() === 0 ? 'new-ticket' : ''); ?>" 
                            data-subject="<?php echo e(strtolower($ticket->subject)); ?>" 
                            data-category="<?php echo e($ticket->category_id ?? ''); ?>" 
                            data-status="<?php echo e($ticket->status); ?>" 
                            data-priority="<?php echo e($ticket->priority); ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="fw-semibold text-dark"><?php echo e($ticket->subject); ?></div>
                                    <?php if($ticket->replies->count() === 0): ?>
                                    <span class="badge bg-primary ms-2"><?php echo e(trans('app.New')); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo e(optional($ticket->user)->name); ?></div>
                                <small class="text-muted"><?php echo e(optional($ticket->user)->email); ?></small>
                            </td>
                            <td class="text-center">
                                <?php if($ticket->category): ?>
                                <span class="badge category-badge" data-category-color="<?php echo e($ticket->category->color); ?>">
                                    <?php echo e($ticket->category->name); ?>

                                </span>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.No Category')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($ticket->priority == 'high'): ?>
                                <span class="badge bg-danger"><?php echo e(trans('app.High')); ?></span>
                                <?php elseif($ticket->priority == 'medium'): ?>
                                <span class="badge bg-warning"><?php echo e(trans('app.Medium')); ?></span>
                                <?php else: ?>
                                <span class="badge bg-secondary"><?php echo e(trans('app.Low')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($ticket->status == 'open'): ?>
                                <span class="badge bg-success"><?php echo e(trans('app.Open')); ?></span>
                                <?php elseif($ticket->status == 'pending'): ?>
                                <span class="badge bg-warning"><?php echo e(trans('app.Pending')); ?></span>
                                <?php elseif($ticket->status == 'resolved'): ?>
                                <span class="badge bg-info"><?php echo e(trans('app.Resolved')); ?></span>
                                <?php else: ?>
                                <span class="badge bg-secondary"><?php echo e(trans('app.Closed')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="fw-semibold text-dark"><?php echo e($ticket->created_at->format('M d, Y')); ?></div>
                                <small class="text-muted"><?php echo e($ticket->created_at->diffForHumans()); ?></small>
                            </td>
                            <td class="text-center">
                                <?php if($ticket->invoice): ?>
                                <div class="fw-semibold">
                                    <a href="<?php echo e(route('admin.invoices.show', $ticket->invoice)); ?>" class="text-primary">
                                        <?php echo e($ticket->invoice->invoice_number); ?>

                                    </a>
                                </div>
                                <small class="text-muted"><?php echo e($ticket->invoice->product->name ?? 'N/A'); ?></small>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group-vertical btn-group-sm" role="group">
                                    <a href="<?php echo e(route('admin.tickets.show', $ticket)); ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>
                                        <?php echo e(trans('app.View')); ?>

                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($tickets->links()); ?>

            </div>
        <?php else: ?>
        <!-- Enhanced Empty State -->
        <div class="admin-empty-state tickets-empty-state">
            <div class="admin-empty-state-content">
                <div class="admin-empty-state-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="admin-empty-state-text">
                    <h3 class="admin-empty-state-title"><?php echo e(trans('app.No Tickets Found')); ?></h3>
                    <p class="admin-empty-state-description">
                        <?php echo e(trans('app.There are currently no support tickets. When customers submit tickets, they will appear here for you to manage.')); ?>

                    </p>
                </div>
                <div class="admin-empty-state-actions">
                    <a href="<?php echo e(route('admin.tickets.create')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
                        <i class="fas fa-plus me-2"></i>
                        <?php echo e(trans('app.Create First Ticket')); ?>

                    </a>
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-btn admin-btn-secondary admin-btn-m">
                        <i class="fas fa-arrow-left me-2"></i>
                        <?php echo e(trans('app.Back to Dashboard')); ?>

                    </a>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="admin-empty-state-stats">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="admin-stat-card">
                            <div class="admin-stat-icon">
                                <i class="fas fa-ticket-alt text-primary"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value">0</div>
                                <div class="admin-stat-label"><?php echo e(trans('app.Total Tickets')); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-stat-card">
                            <div class="admin-stat-icon">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value">0</div>
                                <div class="admin-stat-label"><?php echo e(trans('app.Pending Tickets')); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-stat-card">
                            <div class="admin-stat-icon">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value">0</div>
                                <div class="admin-stat-label"><?php echo e(trans('app.Resolved Tickets')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Helpful Tips -->
            <div class="admin-empty-state-tips">
                <h4 class="admin-tips-title">
                    <i class="fas fa-lightbulb me-2"></i>
                    <?php echo e(trans('app.Getting Started Tips')); ?>

                </h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="admin-tip-card">
                            <div class="admin-tip-icon">
                                <i class="fas fa-users text-info"></i>
                            </div>
                            <div class="admin-tip-content">
                                <h5><?php echo e(trans('app.Encourage Customer Support')); ?></h5>
                                <p><?php echo e(trans('app.Make it easy for customers to submit tickets by adding support links to your website.')); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-tip-card">
                            <div class="admin-tip-icon">
                                <i class="fas fa-cog text-warning"></i>
                            </div>
                            <div class="admin-tip-content">
                                <h5><?php echo e(trans('app.Set Up Categories')); ?></h5>
                                <p><?php echo e(trans('app.Organize tickets by creating categories for different types of support requests.')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\tickets\index.blade.php ENDPATH**/ ?>