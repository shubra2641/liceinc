

<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid products-form">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-eye text-info me-2"></i>
                                <?php echo e(trans('app.View Ticket')); ?> #<?php echo e($ticket->id); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($ticket->subject); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.tickets.edit', $ticket)); ?>" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i>
                                <?php echo e(trans('app.Edit Ticket')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.tickets.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Tickets')); ?>

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
            <!-- Ticket Overview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-ticket-alt me-2"></i>
                        <?php echo e(trans('app.Ticket Overview')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="text-dark mb-3"><?php echo e($ticket->subject); ?></h4>
                            <div class="mb-3">
                                <h6 class="text-muted"><?php echo e(trans('app.Ticket Description')); ?></h6>
                                <div class="bg-light p-3 rounded">
                                    <?php echo e(nl2br(e($ticket->content))); ?>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="mb-3">
                                    <span
                                        class="badge bg-<?php echo e($ticket->priority == 'low' ? 'success' : ($ticket->priority == 'medium' ? 'warning' : 'danger')); ?> fs-6">
                                        <?php echo e(trans('app.' . ucfirst($ticket->priority))); ?>

                                    </span>
                                </div>
                                <div class="mb-3">
                                    <span
                                        class="badge bg-<?php echo e($ticket->status == 'open' ? 'success' : ($ticket->status == 'pending' ? 'warning' : ($ticket->status == 'resolved' ? 'info' : 'secondary'))); ?> fs-6">
                                        <?php echo e(trans('app.' . ucfirst($ticket->status))); ?>

                                    </span>
                                </div>
                                <?php if($ticket->category): ?>
                                <div class="mb-3">
                                    <span class="badge category-badge" data-color="<?php echo e($ticket->category->color); ?>">
                                        <?php echo e($ticket->category->name); ?>

                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Knowledge Base Integration -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-book me-2"></i>
                        <?php echo e(trans('app.Knowledge Base Integration')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo e(trans('app.Insert Article')); ?></label>
                            <select id="kb-article-select" class="form-select">
                                <option value=""><?php echo e(trans('app.Select an Article')); ?></option>
                                <?php $__currentLoopData = \App\Models\KbArticle::published()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($article->id); ?>" data-title="<?php echo e($article->title); ?>"
                                    data-content="<?php echo e($article->content); ?>">
                                    <?php echo e($article->title); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button type="button" data-action="insert-kb-article" class="btn btn-info btn-sm mt-2">
                                <i class="fas fa-file-alt me-1"></i><?php echo e(trans('app.Insert Article')); ?>

                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo e(trans('app.Insert Category Link')); ?></label>
                            <select id="kb-category-select" class="form-select">
                                <option value=""><?php echo e(trans('app.Select a Category')); ?></option>
                                <?php $__currentLoopData = \App\Models\KbCategory::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e(route('kb.category', $category->slug)); ?>">
                                    <?php echo e($category->name); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button type="button" data-action="insert-kb-category-link"
                                class="btn btn-outline-info btn-sm mt-2">
                                <i class="fas fa-link me-1"></i><?php echo e(trans('app.Insert Link')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Replies Section -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comments me-2"></i>
                        <?php echo e(trans('app.Replies')); ?> (<?php echo e($ticket->replies->count()); ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $ticket->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div
                                class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 user-avatar-small">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0"><?php echo e(optional($reply->user)->name ?? trans('app.Admin')); ?></h6>
                                <small class="text-muted"><?php echo e($reply->created_at->diffForHumans()); ?></small>
                            </div>
                        </div>
                        <div class="ms-5">
                            <?php echo e(nl2br(e($reply->message))); ?>

                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-comments text-muted fs-1 mb-3"></i>
                        <h5 class="text-muted"><?php echo e(trans('app.No Replies Yet')); ?></h5>
                        <p class="text-muted"><?php echo e(trans('app.Start the conversation by adding the first reply below')); ?>

                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Add Reply Form - Always Available for Admin -->
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-reply me-1"></i>
                            <?php echo e(trans('app.Add Reply')); ?>

                            <?php if($ticket->status === 'closed' || $ticket->status === 'resolved'): ?>
                            <span class="badge bg-warning ms-2"><?php echo e(trans('app.Ticket Closed')); ?></span>
                            <?php endif; ?>
                        </h6>
                        <form method="post" action="<?php echo e(route('admin.tickets.reply', $ticket)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label for="message" class="form-label">
                                    <i class="fas fa-comment me-1"></i>
                                    <?php echo e(trans('app.Your Reply')); ?> <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="message"
                                    name="message" rows="6" data-summernote="true" data-toolbar="standard"
                                    data-placeholder="<?php echo e(trans('app.Type your reply here')); ?>"
                                    placeholder="<?php echo e(trans('app.Type your reply here')); ?>" required></textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Use the rich text editor to format your reply')); ?>

                                    <?php if($ticket->status === 'closed' || $ticket->status === 'resolved'): ?>
                                    <br><i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                    <?php echo e(trans('app.Note: This ticket is closed, but you can still add a reply')); ?>

                                    <?php endif; ?>
                                </div>
                                <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-1"></i><?php echo e(trans('app.Send Reply')); ?>

                                </button>
                                <?php if($ticket->status === 'closed' || $ticket->status === 'resolved'): ?>
                                <button type="button" class="btn btn-outline-primary" onclick="reopenTicket()">
                                    <i class="fas fa-unlock me-1"></i><?php echo e(trans('app.Reopen Ticket')); ?>

                                </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        <?php echo e(trans('app.Quick Actions')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if($ticket->status !== 'resolved'): ?>
                        <form method="post" action="<?php echo e(route('admin.tickets.update-status', $ticket)); ?>"
                            class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <input type="hidden" name="status" value="resolved">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-1"></i><?php echo e(trans('app.Mark as Resolved')); ?>

                            </button>
                        </form>
                        <?php endif; ?>

                        <?php if($ticket->status !== 'pending'): ?>
                        <form method="post" action="<?php echo e(route('admin.tickets.update-status', $ticket)); ?>"
                            class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-clock me-1"></i><?php echo e(trans('app.Mark as Pending')); ?>

                            </button>
                        </form>
                        <?php endif; ?>

                        <?php if($ticket->status !== 'closed'): ?>
                        <form method="post" action="<?php echo e(route('admin.tickets.update-status', $ticket)); ?>"
                            class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <input type="hidden" name="status" value="closed">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-lock me-1"></i><?php echo e(trans('app.Mark as Closed')); ?>

                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ticket Details -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e(trans('app.Ticket Details')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-primary">#<?php echo e($ticket->id); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Ticket ID')); ?></p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-success"><?php echo e($ticket->replies->count()); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Replies')); ?></p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-info"><?php echo e($ticket->created_at->format('M Y')); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Created')); ?></p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-warning"><?php echo e($ticket->updated_at->format('M Y')); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Updated')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        <?php echo e(trans('app.Customer Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div
                            class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 user-avatar-large">
                            <i class="fas fa-user fs-4"></i>
                        </div>
                        <h6><?php echo e($ticket->user->name ?? trans('app.Unknown User')); ?></h6>
                        <p class="text-muted small mb-3"><?php echo e($ticket->user->email ?? trans('app.No Email')); ?></p>
                        <?php if($ticket->user): ?>
                        <a href="<?php echo e(route('admin.users.show', $ticket->user)); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i><?php echo e(trans('app.View User')); ?>

                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- License Information -->
            <?php if($ticket->user && $ticket->user->licenses->count() > 0): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        <?php echo e(trans('app.License Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $ticket->user->licenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $license): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><?php echo e($license->product->name ?? trans('app.Unknown Product')); ?></h6>
                            <span class="badge <?php echo e($license->support_active ? 'bg-success' : 'bg-danger'); ?>">
                                <?php echo e($license->support_active ? trans('app.Active') : trans('app.Expired')); ?>

                            </span>
                        </div>
                        <div class="small text-muted">
                            <div class="mb-1">
                                <strong><?php echo e(trans('app.Code')); ?>:</strong>
                                <code class="bg-light px-1 rounded"><?php echo e($license->purchase_code); ?></code>
                            </div>
                            <div class="mb-1">
                                <strong><?php echo e(trans('app.Type')); ?>:</strong> <?php echo e($license->license_type); ?>

                            </div>
                            <div>
                                <strong><?php echo e(trans('app.Support')); ?>:</strong>
                                <span class="<?php echo e($license->support_active ? 'text-success' : 'text-danger'); ?>">
                                    <?php echo e($license->support_expires_at ? $license->support_expires_at->format('M d, Y') :
                                    'N/A'); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Invoice Information -->
            <?php if($ticket->invoice): ?>
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        <?php echo e(trans('app.Invoice Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h6><?php echo e($ticket->invoice->invoice_number); ?></h6>
                        <p class="text-muted small mb-2">$<?php echo e(number_format($ticket->invoice->amount, 2)); ?> - <?php echo e(ucfirst($ticket->invoice->status)); ?></p>
                        <?php if($ticket->invoice->product): ?>
                        <p class="text-muted small mb-3"><?php echo e(trans('app.Product')); ?>: <?php echo e($ticket->invoice->product->name); ?></p>
                        <?php endif; ?>
                        <a href="<?php echo e(route('admin.invoices.show', $ticket->invoice)); ?>"
                            class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye me-1"></i><?php echo e(trans('app.View Invoice')); ?>

                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\tickets\show.blade.php ENDPATH**/ ?>