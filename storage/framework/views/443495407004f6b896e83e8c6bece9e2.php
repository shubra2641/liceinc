

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
                                <i class="fas fa-eye text-primary me-2"></i>
                                <?php echo e(trans('app.View Product')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($product->name); ?></p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                <?php echo e(trans('app.Edit Product')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Products')); ?>

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
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e(trans('app.Basic Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tag text-primary me-1"></i>
                                <?php echo e(trans('app.Product Name')); ?>

                            </label>
                            <p class="form-control-plaintext"><?php echo e($product->name); ?></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-folder text-success me-1"></i>
                                <?php echo e(trans('app.Category')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php if($product->category): ?>
                                <span class="badge bg-success"><?php echo e($product->category->name); ?></span>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.No Category')); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-code text-purple me-1"></i>
                                <?php echo e(trans('app.Programming Language')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php if($product->programmingLanguage): ?>
                                <i class="<?php echo e($product->programmingLanguage->icon ?? 'fas fa-code'); ?> me-1"></i>
                                <?php echo e($product->programmingLanguage->name); ?>

                                <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.No Language')); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-globe text-warning me-1"></i>
                                <?php echo e(trans('app.Requires Domain')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php if($product->requires_domain): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i><?php echo e(trans('app.Yes')); ?>

                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times me-1"></i><?php echo e(trans('app.No')); ?>

                                </span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-book text-info me-1"></i>
                                <?php echo e(trans('app.Knowledge Base Section')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php if($product->kbCategory): ?>
                                <span class="badge bg-info"><?php echo e($product->kbCategory->name); ?></span>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.No KB Section')); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-align-left text-secondary me-1"></i>
                                <?php echo e(trans('app.Product Description')); ?>

                            </label>
                            <div class="form-control-plaintext">
                                <?php echo e($product->description ?: '<span class="text-muted">' . trans('app.No Description') .
                                    '</span>'); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        <?php echo e(trans('app.Additional Information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-dollar-sign text-success me-1"></i>
                                <?php echo e(trans('app.Price')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php if($product->price): ?>
                                $<?php echo e(number_format($product->price, 2)); ?>

                                <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.Free')); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-code-branch text-primary me-1"></i>
                                <?php echo e(trans('app.Version')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php echo e($product->latest_version ?: '<span class="text-muted">' . trans('app.No Version') .
                                    '</span>'); ?>

                            </p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fab fa-envato text-warning me-1"></i>
                                <?php echo e(trans('app.Envato Item ID')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php echo e($product->envato_item_id ?: '<span class="text-muted">' . trans('app.No Envato ID') .
                                    '</span>'); ?>

                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-headset text-danger me-1"></i>
                                <?php echo e(trans('app.Support Days')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php echo e($product->support_days ? $product->support_days . ' ' . trans('app.days') : '<span
                                    class="text-muted">' . trans('app.No Support') . '</span>'); ?>

                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-boxes text-warning me-1"></i>
                                <?php echo e(trans('app.Stock Quantity')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php if($product->stock_quantity == -1): ?>
                                <span class="badge bg-success"><?php echo e(trans('app.Unlimited Stock')); ?></span>
                                <?php else: ?>
                                <?php echo e($product->stock_quantity ?: 0); ?>

                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features and Requirements -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star me-2"></i>
                        <?php echo e(trans('app.Features Requirements')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list-check text-success me-1"></i>
                                <?php echo e(trans('app.Features')); ?>

                            </label>
                            <div class="form-control-plaintext">
                                <?php if($product->features && is_array($product->features) && count($product->features) > 0): ?>
                                <ul class="list-unstyled mb-0">
                                    <?php $__currentLoopData = $product->features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="mb-1">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <?php echo e($feature); ?>

                                    </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.No Features')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-clipboard-check text-primary me-1"></i>
                                <?php echo e(trans('app.Requirements')); ?>

                            </label>
                            <div class="form-control-plaintext">
                                <?php if($product->requirements && is_array($product->requirements) &&
                                count($product->requirements) > 0): ?>
                                <ul class="list-unstyled mb-0">
                                    <?php $__currentLoopData = $product->requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="mb-1">
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                        <?php echo e($requirement); ?>

                                    </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.No Requirements')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-book text-purple me-1"></i>
                            <?php echo e(trans('app.Installation Guide')); ?>

                        </label>
                        <div class="form-control-plaintext">
                            <?php if($product->installation_guide && is_array($product->installation_guide) &&
                            count($product->installation_guide) > 0): ?>
                            <ol class="list-unstyled mb-0">
                                <?php $__currentLoopData = $product->installation_guide; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    <?php echo e($step); ?>

                                </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ol>
                            <?php else: ?>
                            <span class="text-muted"><?php echo e(trans('app.No Installation Guide')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media and Assets -->
            <div class="card mb-4">
                <div class="card-header bg-pink text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-images me-2"></i>
                        <?php echo e(trans('app.Media and Assets')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-image text-primary me-1"></i>
                                <?php echo e(trans('app.Main Image')); ?>

                            </label>
                            <div class="form-control-plaintext">
                                <?php if($product->image): ?>
                                <img src="<?php echo e(Storage::url($product->image)); ?>" alt="<?php echo e(trans('app.Product Image')); ?>"
                                    class="img-thumbnail product-image">
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.No Image')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-images text-success me-1"></i>
                                <?php echo e(trans('app.Gallery Images')); ?>

                            </label>
                            <div class="form-control-plaintext">
                                <?php if($product->gallery_images && count($product->gallery_images) > 0): ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $product->gallery_images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $galleryImage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <img src="<?php echo e(Storage::url($galleryImage)); ?>" alt="<?php echo e(trans('app.Gallery Image')); ?>"
                                        class="img-thumbnail product-gallery-image">
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.No Gallery Images')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Optimization -->
            <div class="card mb-4">
                <div class="card-header bg-indigo text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-search me-2"></i>
                        <?php echo e(trans('app.SEO')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-heading text-primary me-1"></i>
                                <?php echo e(trans('app.Meta Title')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php echo e($product->meta_title ?: '<span class="text-muted">' . trans('app.No Meta Title') .
                                    '</span>'); ?>

                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tags text-warning me-1"></i>
                                <?php echo e(trans('app.Tags')); ?>

                            </label>
                            <p class="form-control-plaintext">
                                <?php if($product->tags && is_array($product->tags) && count($product->tags) > 0): ?>
                                <?php $__currentLoopData = $product->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge bg-secondary me-1"><?php echo e($tag); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(trans('app.No Tags')); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-file-alt text-success me-1"></i>
                            <?php echo e(trans('app.Meta Description')); ?>

                        </label>
                        <p class="form-control-plaintext">
                            <?php echo e($product->meta_description ?: '<span class="text-muted">' . trans('app.No Meta
                                Description') . '</span>'); ?>

                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Product Settings -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>
                        <?php echo e(trans('app.Product Settings')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">
                            <i class="fas fa-toggle-on text-success me-1"></i>
                            <?php echo e(trans('app.Active')); ?>

                        </span>
                        <?php if($product->is_active): ?>
                        <span class="badge bg-success"><?php echo e(trans('app.Yes')); ?></span>
                        <?php else: ?>
                        <span class="badge bg-secondary"><?php echo e(trans('app.No')); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">
                            <i class="fas fa-star text-warning me-1"></i>
                            <?php echo e(trans('app.Featured')); ?>

                        </span>
                        <?php if($product->is_featured): ?>
                        <span class="badge bg-warning"><?php echo e(trans('app.Yes')); ?></span>
                        <?php else: ?>
                        <span class="badge bg-secondary"><?php echo e(trans('app.No')); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">
                            <i class="fas fa-download text-info me-1"></i>
                            <?php echo e(trans('app.Downloadable')); ?>

                        </span>
                        <?php if($product->is_downloadable): ?>
                        <span class="badge bg-info"><?php echo e(trans('app.Yes')); ?></span>
                        <?php else: ?>
                        <span class="badge bg-secondary"><?php echo e(trans('app.No')); ?></span>
                        <?php endif; ?>
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
                                <h4 class="text-primary"><?php echo e($product->licenses()->count()); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Licenses')); ?></p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-success"><?php echo e($product->invoices()->count()); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Invoices')); ?></p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-info"><?php echo e($product->created_at->format('M Y')); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Created')); ?></p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-warning"><?php echo e($product->updated_at->format('M Y')); ?></h4>
                                <p class="text-muted small mb-0"><?php echo e(trans('app.Updated')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- License Integration -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i>
                        <?php echo e(trans('app.License Integration')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <?php if($product->programmingLanguage): ?>
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-check-circle me-1"></i>
                            <?php echo e(trans('app.Integration File Generated')); ?>

                        </h6>
                        <p class="mb-2">
                            <strong><?php echo e(trans('app.Language')); ?>:</strong> <?php echo e($product->programmingLanguage->name); ?><br>
                            <strong><?php echo e(trans('app.File')); ?>:</strong> <?php echo e(basename($product->integration_file_path ??
                            trans('app.Not generated'))); ?>

                        </p>
                        <div class="d-grid gap-2">
                            <?php if($product->integration_file_path &&
                            \Illuminate\Support\Facades\Storage::disk('public')->exists($product->integration_file_path)): ?>
                            <a href="<?php echo e(route('admin.products.download-integration', $product)); ?>"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i><?php echo e(trans('app.Download')); ?>

                            </a>
                            <?php endif; ?>
                            <form method="post" action="<?php echo e(route('admin.products.regenerate-integration', $product)); ?>"
                                class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="fas fa-sync me-1"></i><?php echo e(trans('app.Regenerate')); ?>

                                </button>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <?php echo e(trans('app.Programming Language Required')); ?>

                        </h6>
                        <p class="mb-0"><?php echo e(trans('app.Set Programming Language Message')); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Create Test License -->
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        <?php echo e(trans('app.Create Test License')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3"><?php echo e(trans('app.Create Test License Description')); ?></p>
                    <form method="post" action="<?php echo e(route('admin.products.generate-license', $product)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="domain" class="form-label">
                                <i class="fas fa-globe text-primary me-1"></i>
                                <?php echo e(trans('app.Domain')); ?> <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="domain" name="domain" placeholder="example.com"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope text-success me-1"></i>
                                <?php echo e(trans('app.Customer Email')); ?> <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="customer@example.com" required>
                        </div>
                        <button type="submit" class="btn btn-purple w-100">
                            <i class="fas fa-plus me-1"></i><?php echo e(trans('app.Create Test License')); ?>

                        </button>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo e(trans('app.Danger Zone')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3"><?php echo e(trans('app.Delete Product Warning')); ?></p>
                    <form method="post" action="<?php echo e(route('admin.products.destroy', $product)); ?>"
                        data-confirm="delete-product">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-1"></i><?php echo e(trans('app.Delete Product')); ?>

                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Updates Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sync-alt me-2"></i>
                            <?php echo e(trans('app.Product Updates')); ?>

                        </h5>
                        <a href="<?php echo e(route('admin.product-updates.create', ['product_id' => $product->id])); ?>"
                            class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            <?php echo e(trans('app.Add Update')); ?>

                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="productUpdatesTable">
                            <thead>
                                <tr>
                                    <th><?php echo e(trans('app.Version')); ?></th>
                                    <th><?php echo e(trans('app.Title')); ?></th>
                                    <th><?php echo e(trans('app.Type')); ?></th>
                                    <th><?php echo e(trans('app.File Size')); ?></th>
                                    <th><?php echo e(trans('app.Released')); ?></th>
                                    <th><?php echo e(trans('app.Status')); ?></th>
                                    <th><?php echo e(trans('app.Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $product->updates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $update): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary"><?php echo e($update->version); ?></span>
                                        <?php if($update->is_major): ?>
                                        <span class="badge bg-warning ms-1"><?php echo e(trans('app.Major')); ?></span>
                                        <?php endif; ?>
                                        <?php if($update->is_required): ?>
                                        <span class="badge bg-danger ms-1"><?php echo e(trans('app.Required')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($update->title); ?></td>
                                    <td>
                                        <?php if($update->is_major): ?>
                                        <span class="text-warning"><?php echo e(trans('app.Major Update')); ?></span>
                                        <?php else: ?>
                                        <span class="text-info"><?php echo e(trans('app.Minor Update')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($update->formatted_file_size); ?></td>
                                    <td><?php echo e($update->released_at?->format('Y-m-d H:i') ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($update->is_active ? 'success' : 'secondary'); ?>">
                                            <?php echo e($update->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo e(route('admin.product-updates.show', $update)); ?>"
                                                class="btn btn-outline-info" title="<?php echo e(trans('app.View')); ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('admin.product-updates.edit', $update)); ?>"
                                                class="btn btn-outline-primary" title="<?php echo e(trans('app.Edit')); ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-outline-<?php echo e($update->is_active ? 'warning' : 'success'); ?>"
                                                onclick="toggleUpdateStatus(<?php echo e((int)$update->id); ?>)"
                                                title="<?php echo e($update->is_active ? trans('app.Deactivate') : trans('app.Activate')); ?>">
                                                <i class="fas fa-<?php echo e($update->is_active ? 'pause' : 'play'); ?>"></i>
                                            </button>
                                            <form method="POST"
                                                action="<?php echo e(route('admin.product-updates.destroy', $update)); ?>"
                                                class="inline-form"
                                                onsubmit="return confirm('<?php echo e(trans('app.Are you sure you want to delete this update?')); ?>')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-outline-danger"
                                                    title="<?php echo e(trans('app.Delete')); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                                        <?php echo e(trans('app.No updates available for this product')); ?>

                                        <br>
                                        <a href="<?php echo e(route('admin.product-updates.create', ['product_id' => $product->id])); ?>"
                                            class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-plus me-1"></i>
                                            <?php echo e(trans('app.Add First Update')); ?>

                                        </a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\my-logos\resources\views/admin/products/show.blade.php ENDPATH**/ ?>