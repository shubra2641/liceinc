<?php $__env->startSection('title', trans('app.Ticket Details')); ?>
<?php $__env->startSection('page-title', trans('app.Ticket') . ' #' . $ticket->id); ?>
<?php $__env->startSection('page-subtitle', trans('app.View ticket details and replies')); ?>

<?php $__env->startSection('seo_title', $ticketsSeoTitle ?? $siteSeoTitle ?? trans('app.Ticket Details')); ?>
<?php $__env->startSection('meta_description', $ticketsSeoDescription ?? $siteSeoDescription ?? trans('app.View ticket details and
replies')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-ticket-alt"></i>
                <?php echo e(trans('app.Ticket')); ?> #<?php echo e($ticket->id); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e($ticket->subject); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Ticket Status Banner -->
            <div class="quick-help-section">
                <div class="quick-help-header">
                    <h3><?php echo e(trans('app.Ticket Status')); ?></h3>
                    <p>
                        <?php if($ticket->status === 'open'): ?>
                        <?php echo e(trans('app.This ticket is open and awaiting response')); ?>

                        <?php elseif($ticket->status === 'closed'): ?>
                        <?php echo e(trans('app.This ticket has been closed')); ?>

                        <?php else: ?>
                        <?php echo e(trans('app.This ticket is pending')); ?>

                        <?php endif; ?>
                    </p>
                </div>

                <div class="quick-help-actions">
                    <div class="ticket-status-badge ticket-status-<?php echo e($ticket->status); ?>">
                        <i class="fas fa-<?php echo e($ticket->status === 'open' ? 'clock' : 'check-circle'); ?>"></i>
                        <?php echo e(ucfirst($ticket->status)); ?>

                    </div>

                    <div class="ticket-priority-badge ticket-priority-<?php echo e($ticket->priority); ?>">
                        <i class="fas fa-flag"></i>
                        <?php echo e(ucfirst($ticket->priority)); ?>

                    </div>
                </div>
            </div>

            <!-- Ticket Information -->
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3><?php echo e(trans('app.Ticket Information')); ?></h3>

                    <div class="form-group">
                        <label><?php echo e(trans('app.Ticket ID')); ?></label>
                        <div class="form-input bg-light-gray">#<?php echo e($ticket->id); ?></div>
                    </div>

                    <div class="form-group">
                        <label><?php echo e(trans('app.Status')); ?></label>
                        <div class="form-input bg-light-gray">
                            <span class="ticket-status-badge ticket-status-<?php echo e($ticket->status); ?>">
                                <?php echo e(ucfirst($ticket->status)); ?>

                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?php echo e(trans('app.Priority')); ?></label>
                        <div class="form-input bg-light-gray">
                            <span class="ticket-priority-badge ticket-priority-<?php echo e($ticket->priority); ?>">
                                <?php echo e(ucfirst($ticket->priority)); ?>

                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?php echo e(trans('app.Category')); ?></label>
                        <div class="form-input bg-light-gray"><?php echo e($ticket->category?->name
                            ?? '-'); ?></div>
                    </div>
                </div>

                <!-- Related Information -->
                <div class="form-section">
                    <h3><?php echo e(trans('app.Related Information')); ?></h3>

                    <div class="form-group">
                        <label><?php echo e(trans('app.Created')); ?></label>
                        <div class="form-input bg-light-gray"><?php echo e($ticket->created_at->format('M d, Y H:i')); ?></div>
                    </div>

                    <div class="form-group">
                        <label><?php echo e(trans('app.Last Updated')); ?></label>
                        <div class="form-input bg-light-gray"><?php echo e($ticket->updated_at->format('M d, Y H:i')); ?></div>
                    </div>

                    <?php if($ticket->license): ?>
                    <div class="form-group">
                        <label><?php echo e(trans('app.Related License')); ?></label>
                        <a href="<?php echo e(route('user.licenses.show', $ticket->license)); ?>" class="user-action-button">
                            <i class="fas fa-key"></i>
                            <?php echo e(trans('app.View License')); ?>

                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if($ticket->license && $ticket->license->product): ?>
                    <div class="form-group">
                        <label><?php echo e(trans('app.Product')); ?></label>
                        <a href="<?php echo e(route('public.products.show', $ticket->license->product->slug)); ?>"
                            class="user-action-button">
                            <i class="fas fa-box"></i>
                            <?php echo e($ticket->license->product->name); ?>

                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label><?php echo e(trans('app.Replies')); ?></label>
                        <div class="form-input bg-light-gray"><?php echo e($ticket->replies->count()); ?></div>
                    </div>
                </div>
            </div>

            <!-- Ticket Description -->
            <div class="form-section">
                <h3><?php echo e(trans('app.Ticket Description')); ?></h3>

                <div class="form-group">
                    <label><?php echo e(trans('app.Initial Message')); ?></label>
                    <div class="ticket-message">
                        <div class="message-header">
                            <div class="message-author">
                                <i class="fas fa-user"></i>
                                <span><?php echo e($ticket->user->name); ?></span>
                            </div>
                            <div class="message-date">
                                <?php echo e($ticket->created_at->format('M d, Y H:i')); ?>

                            </div>
                        </div>
                        <div class="message-content">
                            <?php echo e(nl2br(e($ticket->content))); ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Replies -->
            <?php if($ticket->replies->isNotEmpty()): ?>
            <div class="form-section">
                <h3><?php echo e(trans('app.Replies')); ?> (<?php echo e($ticket->replies->count()); ?>)</h3>

                <div class="ticket-replies">
                    <?php $__currentLoopData = $ticket->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        class="ticket-message <?php echo e($reply->user_id === $ticket->user_id ? 'user-message' : 'admin-message'); ?>">
                        <div class="message-header">
                            <div class="message-author">
                                <i class="fas fa-<?php echo e($reply->user_id === $ticket->user_id ? 'user' : 'headset'); ?>"></i>
                                <span><?php echo e($reply->user->name); ?></span>
                                <?php if($reply->user_id !== $ticket->user_id): ?>
                                <span class="admin-badge"><?php echo e(trans('app.Support')); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="message-date">
                                <?php echo e($reply->created_at->format('M d, Y H:i')); ?>

                            </div>
                        </div>
                        <div class="message-content">
                            <?php echo e(nl2br(e($reply->message))); ?>

                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Reply Form -->
            <?php if(in_array($ticket->status, ['open', 'pending'])): ?>
            <div class="form-section">
                <h3><?php echo e(trans('app.Add Reply')); ?></h3>

                <form action="<?php echo e(route('user.tickets.reply', $ticket)); ?>" method="POST" class="ticket-form">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="message"><?php echo e(trans('app.Message')); ?> <span class="required">*</span></label>
                        <textarea id="message" name="message" rows="6" class="form-textarea"
                            placeholder="<?php echo e(trans('app.Type your reply here...')); ?>" required></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="user-action-button">
                            <i class="fas fa-paper-plane"></i>
                            <?php echo e(trans('app.Send Reply')); ?>

                        </button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <!-- Closed Ticket Message -->
            <div class="quick-help-section">
                <div class="quick-help-header">
                    <h3><?php echo e(trans('app.Ticket Closed')); ?></h3>
                    <p><?php echo e(trans('app.This ticket has been closed and no further replies can be added')); ?></p>
                    <?php if($ticket->status === 'resolved'): ?>
                    <p class="resolved-note"><?php echo e(trans('app.This ticket has been resolved')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ticket Actions -->
            <div class="form-actions">
                <a href="<?php echo e(route('user.tickets.index')); ?>" class="user-action-button secondary">
                    <i class="fas fa-arrow-left"></i>
                    <?php echo e(trans('app.Back to Tickets')); ?>

                </a>

                <a href="<?php echo e(route('kb.index')); ?>" class="user-action-button">
                    <i class="fas fa-book"></i>
                    <?php echo e(trans('app.Knowledge Base')); ?>

                </a>
            </div>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\user\tickets\show.blade.php ENDPATH**/ ?>