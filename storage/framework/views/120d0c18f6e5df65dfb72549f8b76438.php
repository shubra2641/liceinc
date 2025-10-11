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
                                <i class="fas fa-plus-circle text-primary me-2"></i>
                                <?php echo e(trans('app.Create Ticket for User')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e(trans('app.Create New Support Ticket')); ?></p>
                        </div>
                        <div>
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

    

    <form method="post" action="<?php echo e(route('admin.tickets.store')); ?>" class="needs-validation" novalidate>
        <?php echo csrf_field(); ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- User Selection -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>
                            <?php echo e(trans('app.User Selection')); ?>

                            <span class="badge bg-light text-primary ms-2"><?php echo e(trans('app.Required')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">
                                <i class="fas fa-user-circle text-primary me-1"></i>
                                <?php echo e(trans('app.Select User')); ?> <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                    id="user_id" name="user_id" required data-action="update-user-licenses">
                                <option value=""><?php echo e(trans('app.select_a_user')); ?></option>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>" 
                                        data-licenses='<?php echo e($user->licenses->toJson()); ?>'
                                        data-name="<?php echo e($user->name); ?>"
                                        data-email="<?php echo e($user->email); ?>"
                                        <?php echo e(old('user_id') == $user->id ? 'selected' : ''); ?>>
                                    <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)
                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['user_id'];
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

                        <!-- License Information Section -->
                        <div id="license-info" class="mb-3 hidden-field">
                            <label class="form-label">
                                <i class="fas fa-key text-success me-1"></i>
                                <?php echo e(trans('app.License Information')); ?>

                            </label>
                            <div id="license-details" class="bg-light p-3 rounded border">
                                <!-- License details will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

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
                                    <?php echo e(trans('app.Category')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="category_id" name="category_id" required>
                                    <option value=""><?php echo e(trans('app.Select a Category')); ?></option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>" 
                                        <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>>
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
                                    <option value=""><?php echo e(trans('app.Select Priority')); ?></option>
                                    <option value="low" <?php echo e(old('priority') == 'low' ? 'selected' : ''); ?>>
                                        <i class="fas fa-arrow-down text-success me-1"></i><?php echo e(trans('app.Low')); ?>

                                    </option>
                                    <option value="medium" <?php echo e(old('priority') == 'medium' ? 'selected' : ''); ?>>
                                        <i class="fas fa-minus text-warning me-1"></i><?php echo e(trans('app.Medium')); ?>

                                    </option>
                                    <option value="high" <?php echo e(old('priority') == 'high' ? 'selected' : ''); ?>>
                                        <i class="fas fa-arrow-up text-danger me-1"></i><?php echo e(trans('app.High')); ?>

                                    </option>
                                    <option value="urgent" <?php echo e(old('priority') == 'urgent' ? 'selected' : ''); ?>>
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
                                   id="subject" name="subject" value="<?php echo e(old('subject')); ?>" 
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
                            <label for="content" class="form-label">
                                <i class="fas fa-align-left text-success me-1"></i>
                                <?php echo e(trans('app.Message')); ?> <span class="text-danger">*</span>
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
                                      data-placeholder="<?php echo e(trans('app.Enter ticket message')); ?>"
                                      placeholder="<?php echo e(trans('app.Enter ticket message')); ?>" required><?php echo e(old('content')); ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                <?php echo e(trans('app.use_the_rich_text_editor_to_format_your_message_with_headings_lists_links_and_more.')); ?>

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

                <!-- Optional: Create Invoice for User -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            <?php echo e(trans('app.Create Invoice (optional)')); ?>

                            <span class="badge bg-light text-info ms-2"><?php echo e(trans('app.Optional')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="create_invoice" name="create_invoice" value="1"
                                   <?php echo e(old('create_invoice') ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="create_invoice">
                                <i class="fas fa-file-invoice text-info me-1"></i>
                                <?php echo e(trans('app.Create invoice for this user')); ?>

                            </label>
                        </div>

                        <div id="invoice-section" class="hidden-field invoice-section">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="invoice_product_id" class="form-label">
                                        <i class="fas fa-box text-primary me-1"></i>
                                        <?php echo e(trans('app.Product')); ?>

                                    </label>
                                    <select class="form-select" id="invoice_product_id" name="invoice_product_id">
                                        <option value=""><?php echo e(trans('app.Select Product')); ?></option>
                                        <option value="custom" <?php echo e(old('invoice_product_id') == 'custom' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.Custom Invoice')); ?>

                                        </option>
                                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($product->id); ?>" 
                                                data-price="<?php echo e($product->price); ?>" 
                                                data-duration="<?php echo e($product->duration_days); ?>"
                                                <?php echo e(old('invoice_product_id') == $product->id ? 'selected' : ''); ?>>
                                            <?php echo e($product->name); ?>

                                        </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="billing_type" class="form-label">
                                        <i class="fas fa-credit-card text-warning me-1"></i>
                                        <?php echo e(trans('app.Billing Type')); ?>

                                    </label>
                                    <select class="form-select" id="billing_type" name="billing_type">
                                        <option value="one_time" <?php echo e(old('billing_type') == 'one_time' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.One-time (no renewal)')); ?>

                                        </option>
                                        <option value="monthly" <?php echo e(old('billing_type') == 'monthly' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.Monthly')); ?>

                                        </option>
                                        <option value="quarterly" <?php echo e(old('billing_type') == 'quarterly' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.Quarterly')); ?>

                                        </option>
                                        <option value="semi_annual" <?php echo e(old('billing_type') == 'semi_annual' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.Semi-annual')); ?>

                                        </option>
                                        <option value="annual" <?php echo e(old('billing_type') == 'annual' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.Annual')); ?>

                                        </option>
                                        <option value="custom_recurring" <?php echo e(old('billing_type') == 'custom_recurring' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.Custom (recurring)')); ?>

                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="invoice_amount" class="form-label">
                                        <i class="fas fa-dollar-sign text-success me-1"></i>
                                        <?php echo e(trans('app.Amount')); ?>

                                    </label>
                                    <input type="number" class="form-control" id="invoice_amount" name="invoice_amount" 
                                           value="<?php echo e(old('invoice_amount')); ?>" placeholder="0.00" step="0.01" min="0.01" max="999999.99">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="invoice_due_date" class="form-label">
                                        <i class="fas fa-calendar text-info me-1"></i>
                                        <?php echo e(trans('app.Due Date')); ?>

                                    </label>
                                    <input type="date" class="form-control" id="invoice_due_date" name="invoice_due_date" 
                                           value="<?php echo e(old('invoice_due_date')); ?>">
                                </div>

                                <div class="col-md-6 mb-3" id="invoice-duration-group">
                                    <label for="invoice_duration_days" class="form-label">
                                        <i class="fas fa-clock text-purple me-1"></i>
                                        <?php echo e(trans('app.Duration (days)')); ?>

                                    </label>
                                    <input type="number" class="form-control" id="invoice_duration_days" name="invoice_duration_days" 
                                           value="<?php echo e(old('invoice_duration_days')); ?>" min="0" placeholder="e.g. 365">
                                </div>

                                <div class="col-md-6 mb-3" id="invoice-renewal-group" class="hidden-field">
                                    <label for="invoice_renewal_price" class="form-label">
                                        <i class="fas fa-redo text-warning me-1"></i>
                                        <?php echo e(trans('app.Renewal Price')); ?>

                                    </label>
                                    <input type="text" class="form-control" id="invoice_renewal_price" name="invoice_renewal_price" 
                                           value="<?php echo e(old('invoice_renewal_price')); ?>" placeholder="0.00">
                                </div>

                                <div class="col-md-6 mb-3" id="invoice-renewal-period-group" class="hidden-field">
                                    <label for="invoice_renewal_period_days" class="form-label">
                                        <i class="fas fa-calendar-alt text-danger me-1"></i>
                                        <?php echo e(trans('app.Renewal Period (days)')); ?>

                                    </label>
                                    <input type="number" class="form-control" id="invoice_renewal_period_days" name="invoice_renewal_period_days" 
                                           value="<?php echo e(old('invoice_renewal_period_days')); ?>" min="1" placeholder="e.g. 30">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="invoice_status" class="form-label">
                                        <i class="fas fa-flag text-primary me-1"></i>
                                        <?php echo e(trans('app.Status')); ?>

                                    </label>
                                    <select class="form-select" id="invoice_status" name="invoice_status">
                                        <option value="pending" <?php echo e(old('invoice_status') == 'pending' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.Pending')); ?>

                                        </option>
                                        <option value="paid" <?php echo e(old('invoice_status') == 'paid' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.Paid')); ?>

                                        </option>
                                        <option value="overdue" <?php echo e(old('invoice_status') == 'overdue' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.Overdue')); ?>

                                        </option>
                                        <option value="cancelled" <?php echo e(old('invoice_status') == 'cancelled' ? 'selected' : ''); ?>>
                                            <?php echo e(trans('app.Cancelled')); ?>

                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="invoice_notes" class="form-label">
                                    <i class="fas fa-sticky-note text-secondary me-1"></i>
                                    <?php echo e(trans('app.Notes')); ?>

                                </label>
                                <textarea class="form-control" id="invoice_notes" name="invoice_notes" rows="3"
                                          placeholder="<?php echo e(trans('app.Enter invoice notes')); ?>"><?php echo e(old('invoice_notes')); ?></textarea>
                            </div>
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
                                <h5 id="preview-subject"><?php echo e(trans('app.Ticket Subject')); ?></h5>
                                <p id="preview-priority" class="text-muted small mb-0"><?php echo e(trans('app.Priority')); ?></p>
                            </div>
                            <p class="text-muted small mt-2"><?php echo e(trans('app.Live Preview')); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            <?php echo e(trans('app.Quick Stats')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-primary"><?php echo e($users->count()); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Users')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success"><?php echo e($categories->count()); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Categories')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-info"><?php echo e($products->count()); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Products')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-warning">0</h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Tickets')); ?></p>
                                </div>
                            </div>
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
                            <a href="<?php echo e(route('admin.tickets.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i><?php echo e(trans('app.Cancel')); ?>

                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?php echo e(trans('app.Create Ticket')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\tickets\create.blade.php ENDPATH**/ ?>