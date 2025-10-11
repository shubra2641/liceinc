
<?php $__env->startSection('title', 'Test Email Template'); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid email-template-test">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-paper-plane text-warning me-2"></i>
                                <?php echo e(trans('app.Test Email Template')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($email_template->name); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.email-templates.show', $email_template)); ?>"
                                class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Template')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Test Data -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-flask me-2"></i>
                        <?php echo e(trans('app.Test Data')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(trans('app.Template Type')); ?></label>
                        <div class="form-control-plaintext">
                            <span class="badge bg-primary"><?php echo e($email_template->type); ?></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo e(trans('app.Template Name')); ?></label>
                        <div class="form-control-plaintext"><?php echo e($email_template->name); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo e(trans('app.Template Subject')); ?></label>
                        <div class="form-control-plaintext"><?php echo e($email_template->subject); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo e(trans('app.Template Status')); ?></label>
                        <div class="form-control-plaintext">
                            <?php if($email_template->is_active): ?>
                            <span class="badge bg-success"><?php echo e(trans('app.Active')); ?></span>
                            <?php else: ?>
                            <span class="badge bg-secondary"><?php echo e(trans('app.Inactive')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo e(trans('app.Last Updated')); ?></label>
                        <div class="form-control-plaintext"><?php echo e($email_template->updated_at->format('Y-m-d H:i:s')); ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Actions -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        <?php echo e(trans('app.Test Actions')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.email-templates.send-test', $email_template)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="test_email" class="form-label"><?php echo e(trans('app.Test Email Address')); ?></label>
                            <input type="email" class="form-control" id="test_email" name="test_email"
                                value="<?php echo e(auth()->user()->email); ?>" required>
                            <div class="form-text"><?php echo e(trans('app.Enter email address to send test email')); ?></div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-paper-plane me-1"></i>
                            <?php echo e(trans('app.Send Test Email')); ?>

                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Rendered Preview -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye me-2"></i>
                        <?php echo e(trans('app.Rendered Preview')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <?php if(isset($rendered['subject'])): ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold"><?php echo e(trans('app.Subject')); ?></label>
                        <div class="alert alert-light border">
                            <?php echo e($rendered['subject']); ?>

                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($rendered['body'])): ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold"><?php echo e(trans('app.Body')); ?></label>
                        <div class="border rounded p-3 email-template-preview">
                            <?php echo e($rendered['body']); ?>

                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($rendered['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong><?php echo e(trans('app.Rendering Error')); ?>:</strong>
                        <?php echo e($rendered['error']); ?>

                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Template Variables -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i>
                        <?php echo e(trans('app.Template Variables')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted"><?php echo e(trans('app.Available Variables')); ?></h6>
                            <ul class="list-unstyled">
                                <li><code>{{ $user->name }}</code> - <?php echo e(trans('app.User Name')); ?></li>
                                <li><code>{{ $user->email }}</code> - <?php echo e(trans('app.User Email')); ?></li>
                                <li><code>{{ $site_name }}</code> - <?php echo e(trans('app.Site Name')); ?></li>
                                <li><code>{{ $site_url }}</code> - <?php echo e(trans('app.Site URL')); ?></li>
                                <li><code>{{ $current_date }}</code> - <?php echo e(trans('app.Current Date')); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted"><?php echo e(trans('app.Test Values')); ?></h6>
                            <ul class="list-unstyled">
                                <li><strong><?php echo e(trans('app.User Name')); ?>:</strong> <?php echo e($testData['user']['name'] ?? 'Test
                                    User'); ?></li>
                                <li><strong><?php echo e(trans('app.User Email')); ?>:</strong> <?php echo e($testData['user']['email'] ??
                                    'test@example.com'); ?></li>
                                <li><strong><?php echo e(trans('app.Site Name')); ?>:</strong> <?php echo e($testData['site_name'] ??
                                    config('app.name')); ?></li>
                                <li><strong><?php echo e(trans('app.Site URL')); ?>:</strong> <?php echo e($testData['site_url'] ?? url('/')); ?></li>
                                <li><strong><?php echo e(trans('app.Current Date')); ?>:</strong> <?php echo e($testData['current_date'] ??
                                    now()->format('Y-m-d')); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\email-templates\test.blade.php ENDPATH**/ ?>