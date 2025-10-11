

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
                                <i class="fas fa-edit text-primary me-2"></i>
                                <?php echo e(trans('app.Edit Ticket')); ?>

                            </h1>
                            <p class="text-muted mb-0">#<?php echo e($ticket->id); ?> - <?php echo e($ticket->subject); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.tickets.show', $ticket)); ?>" class="btn btn-info me-2">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo e(trans('app.View Ticket')); ?>

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

    

    

    <form method="post" action="<?php echo e(route('admin.tickets.update', $ticket)); ?>" class="needs-validation" novalidate>
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Ticket Details -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-ticket-alt me-2"></i>
                            <?php echo e(trans('app.Ticket Details')); ?>

                            <span class="badge bg-light text-warning ms-2"><?php echo e(trans('app.Required')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-tag text-purple me-1"></i>
                                    <?php echo e(trans('app.Category')); ?>

                                </label>
                                <select class="form-select <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="category_id" name="category_id">
                                    <option value=""><?php echo e(trans('app.No Category')); ?></option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>" 
                                        <?php echo e(old('category_id', $ticket->category_id) == $category->id ? 'selected' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['category_id'];
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

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">
                                    <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                                    <?php echo e(trans('app.Priority')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="priority" name="priority" required>
                                    <option value="low" <?php echo e(old('priority', $ticket->priority) == 'low' ? 'selected' : ''); ?>>
                                        <i class="fas fa-arrow-down text-success me-1"></i><?php echo e(trans('app.Low')); ?>

                                    </option>
                                    <option value="medium" <?php echo e(old('priority', $ticket->priority) == 'medium' ? 'selected' : ''); ?>>
                                        <i class="fas fa-minus text-warning me-1"></i><?php echo e(trans('app.Medium')); ?>

                                    </option>
                                    <option value="high" <?php echo e(old('priority', $ticket->priority) == 'high' ? 'selected' : ''); ?>>
                                        <i class="fas fa-arrow-up text-danger me-1"></i><?php echo e(trans('app.High')); ?>

                                    </option>
                                    <option value="urgent" <?php echo e(old('priority', $ticket->priority) == 'urgent' ? 'selected' : ''); ?>>
                                        <i class="fas fa-exclamation text-danger me-1"></i><?php echo e(trans('app.Urgent')); ?>

                                    </option>
                                </select>
                                <?php $__errorArgs = ['priority'];
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
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">
                                <i class="fas fa-heading text-indigo me-1"></i>
                                <?php echo e(trans('app.Subject')); ?> <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="subject" name="subject" value="<?php echo e(old('subject', $ticket->subject)); ?>" 
                                   placeholder="<?php echo e(trans('app.Enter ticket subject')); ?>" required>
                            <?php $__errorArgs = ['subject'];
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

                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-info-circle text-success me-1"></i>
                                <?php echo e(trans('app.Status')); ?> <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                    id="status" name="status" required>
                                <option value="open" <?php echo e(old('status', $ticket->status) == 'open' ? 'selected' : ''); ?>>
                                    <i class="fas fa-circle text-success me-1"></i><?php echo e(trans('app.Open')); ?>

                                </option>
                                <option value="pending" <?php echo e(old('status', $ticket->status) == 'pending' ? 'selected' : ''); ?>>
                                    <i class="fas fa-clock text-warning me-1"></i><?php echo e(trans('app.Pending')); ?>

                                </option>
                                <option value="resolved" <?php echo e(old('status', $ticket->status) == 'resolved' ? 'selected' : ''); ?>>
                                    <i class="fas fa-check-circle text-info me-1"></i><?php echo e(trans('app.Resolved')); ?>

                                </option>
                                <option value="closed" <?php echo e(old('status', $ticket->status) == 'closed' ? 'selected' : ''); ?>>
                                    <i class="fas fa-times-circle text-danger me-1"></i><?php echo e(trans('app.Closed')); ?>

                                </option>
                            </select>
                            <?php $__errorArgs = ['status'];
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

                        <div class="mb-3">
                            <label for="content" class="form-label">
                                <i class="fas fa-align-left text-success me-1"></i>
                                <?php echo e(trans('app.Content')); ?> <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="content" name="content" rows="8"
                                      data-summernote="true" data-toolbar="standard"
                                      data-placeholder="<?php echo e(trans('app.Enter ticket content')); ?>"
                                      placeholder="<?php echo e(trans('app.Enter ticket content')); ?>" required><?php echo e(old('content', $ticket->content)); ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                <?php echo e(trans('app.Use the rich text editor to format your content')); ?>

                            </div>
                            <?php $__errorArgs = ['content'];
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
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Ticket Preview -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>
                            <?php echo e(trans('app.Ticket Preview')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div id="ticket-preview" class="p-3 rounded border">
                                <i class="fas fa-ticket-alt fs-1 text-primary mb-2"></i>
                                <h5 id="preview-subject"><?php echo e($ticket->subject); ?></h5>
                                <p id="preview-priority" class="text-muted small mb-0">
                                    <span class="badge bg-<?php echo e($ticket->priority == 'low' ? 'success' : ($ticket->priority == 'medium' ? 'warning' : 'danger')); ?>">
                                        <?php echo e(trans('app.' . ucfirst($ticket->priority))); ?>

                                    </span>
                                </p>
                            </div>
                            <p class="text-muted small mt-2"><?php echo e(trans('app.Live Preview')); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Ticket Information -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo e(trans('app.Ticket Information')); ?>

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
                            <?php echo e(trans('app.User Information')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fs-1 text-primary mb-2"></i>
                                <h6><?php echo e($ticket->user->name ?? trans('app.Unknown User')); ?></h6>
                                <p class="text-muted small mb-0"><?php echo e($ticket->user->email ?? trans('app.No Email')); ?></p>
                            </div>
                            <?php if($ticket->user): ?>
                            <a href="<?php echo e(route('admin.users.show', $ticket->user)); ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-1"></i><?php echo e(trans('app.View User')); ?>

                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Priority Guide -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo e(trans('app.Priority Guide')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-success me-2">Low</span>
                            <small class="text-muted"><?php echo e(trans('app.General questions and minor issues')); ?></small>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-warning me-2">Medium</span>
                            <small class="text-muted"><?php echo e(trans('app.Standard support requests')); ?></small>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-danger me-2">High</span>
                            <small class="text-muted"><?php echo e(trans('app.Urgent issues affecting functionality')); ?></small>
                        </div>
                        <div class="mb-0">
                            <span class="badge bg-dark me-2">Urgent</span>
                            <small class="text-muted"><?php echo e(trans('app.Critical issues requiring immediate attention')); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('admin.tickets.show', $ticket)); ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye me-1"></i><?php echo e(trans('app.View Ticket')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.tickets.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i><?php echo e(trans('app.Cancel')); ?>

                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?php echo e(trans('app.Save Changes')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\tickets\edit.blade.php ENDPATH**/ ?>