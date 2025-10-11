

<?php $__env->startSection('title', trans('app.Support Tickets')); ?>
<?php $__env->startSection('page-title', trans('app.Support Tickets')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Get help and support for your products')); ?>

<?php $__env->startSection('seo_title', $ticketsSeoTitle ?? $siteSeoTitle ?? trans('app.Support Tickets')); ?>
<?php $__env->startSection('meta_description', $ticketsSeoDescription ?? $siteSeoDescription ?? trans('app.Get help and support for your products')); ?>


<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-headset"></i>
                <?php echo e(trans('app.Support Tickets')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Get help and support for your products and licenses')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Ticket Statistics -->
            <div class="invoice-stats-grid">
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Total Tickets')); ?></div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($tickets->total()); ?></div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Open Tickets')); ?></div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($tickets->where('status', 'open')->count()); ?></div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Closed Tickets')); ?></div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e($tickets->where('status', 'closed')->count()); ?></div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.Avg Response Time')); ?></div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-stopwatch"></i>
                        </div>
                    </div>
                    <div class="user-stat-value"><?php echo e(\App\Models\Setting::get('avg_response_time', 24)); ?>h</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="ticket-quick-actions">
                <a href="<?php echo e(route('user.tickets.create')); ?>" class="user-action-button">
                    <i class="fas fa-plus"></i>
                    <?php echo e(trans('app.Create New Ticket')); ?>

                </a>
                
                <a href="<?php echo e(route('kb.index')); ?>" class="user-action-button">
                    <i class="fas fa-book"></i>
                    <?php echo e(trans('app.Knowledge Base')); ?>

                </a>
            </div>

            <!-- Filters and Search -->
            <div class="license-filters">
                <div class="filter-group">
                    <label for="status-filter"><?php echo e(trans('app.Filter by Status')); ?>:</label>
                    <select id="status-filter" class="filter-select">
                        <option value=""><?php echo e(trans('app.All Statuses')); ?></option>
                        <option value="open"><?php echo e(trans('app.Open')); ?></option>
                        <option value="closed"><?php echo e(trans('app.Closed')); ?></option>
                        <option value="pending"><?php echo e(trans('app.Pending')); ?></option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="priority-filter"><?php echo e(trans('app.Filter by Priority')); ?>:</label>
                    <select id="priority-filter" class="filter-select">
                        <option value=""><?php echo e(trans('app.All Priorities')); ?></option>
                        <option value="low"><?php echo e(trans('app.Low')); ?></option>
                        <option value="medium"><?php echo e(trans('app.Medium')); ?></option>
                        <option value="high"><?php echo e(trans('app.High')); ?></option>
                        <option value="urgent"><?php echo e(trans('app.Urgent')); ?></option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search-input"><?php echo e(trans('app.Search')); ?>:</label>
                    <input type="text" id="search-input" class="filter-input" placeholder="<?php echo e(trans('app.Search by subject...')); ?>">
                </div>
            </div>

            <?php if($tickets->isEmpty()): ?>
            <div class="user-empty-state">
                <div class="user-empty-state-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="user-empty-state-title">
                    <?php echo e(trans('app.No tickets found')); ?>

                </h3>
                <p class="user-empty-state-description">
                    <?php echo e(trans('app.You haven\'t created any support tickets yet. Need help? Create your first ticket!')); ?>

                </p>
                <a href="<?php echo e(route('user.tickets.create')); ?>" class="user-action-button">
                    <i class="fas fa-plus"></i>
                    <?php echo e(trans('app.Create First Ticket')); ?>

                </a>
            </div>
            <?php else: ?>
            <!-- Tickets Table -->
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th><?php echo e(trans('app.Ticket')); ?></th>
                            <th><?php echo e(trans('app.Subject')); ?></th>
                            <th><?php echo e(trans('app.Category')); ?></th>
                            <th><?php echo e(trans('app.Priority')); ?></th>
                            <th><?php echo e(trans('app.Status')); ?></th>
                            <th><?php echo e(trans('app.Created')); ?></th>
                            <th><?php echo e(trans('app.Last Reply')); ?></th>
                            <th><?php echo e(trans('app.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div class="ticket-number">#<?php echo e($ticket->id); ?></div>
                            </td>
                            <td>
                                <div class="ticket-subject"><?php echo e($ticket->subject); ?></div>
                                <?php if($ticket->description): ?>
                                <div class="ticket-description"><?php echo e(Str::limit($ticket->description, 50)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="ticket-category-badge">
                                    <?php echo e($ticket->category?->name ?? '-'); ?>

                                </span>
                            </td>
                            <td>
                                <span class="ticket-priority-badge ticket-priority-<?php echo e($ticket->priority); ?>">
                                    <?php echo e(ucfirst($ticket->priority)); ?>

                                </span>
                            </td>
                            <td>
                                <span class="ticket-status-badge ticket-status-<?php echo e($ticket->status); ?>">
                                    <?php echo e(ucfirst($ticket->status)); ?>

                                </span>
                            </td>
                            <td><?php echo e($ticket->created_at->format('M d, Y')); ?></td>
                            <td><?php echo e(optional($ticket->updated_at)->format('M d, Y')); ?></td>
                            <td>
                                <div class="license-actions-cell">
                                    <a href="<?php echo e(route('user.tickets.show', $ticket)); ?>" class="license-action-link">
                                        <i class="fas fa-eye"></i>
                                        <?php echo e(trans('app.View')); ?>

                                    </a>
                                    <?php if($ticket->status === 'open'): ?>
                                    <a href="<?php echo e(route('user.tickets.show', $ticket)); ?>#reply" class="license-action-link">
                                        <i class="fas fa-reply"></i>
                                        <?php echo e(trans('app.Reply')); ?>

                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="license-pagination">
                <?php echo e($tickets->links()); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\user\tickets\index.blade.php ENDPATH**/ ?>