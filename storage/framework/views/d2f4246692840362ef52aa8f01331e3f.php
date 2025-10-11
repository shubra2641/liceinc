<?php $__env->startSection('title', trans('app.Product Files') . ' - ' . $product->name); ?>
<?php $__env->startSection('page-title', trans('app.Product Files')); ?>
<?php $__env->startSection('page-subtitle', $product->name); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-download"></i>
                <?php echo e(trans('app.Product Files')); ?>: <?php echo e($product->name); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Download and manage your product files')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Action Buttons -->
            <div class="user-action-buttons">
                <a href="<?php echo e(route('user.products.show', $product)); ?>" class="user-action-button secondary">
                    <i class="fas fa-arrow-left"></i>
                    <?php echo e(trans('app.Back to Product')); ?>

                </a>
                <?php if(isset($latestFile) && $latestFile): ?>
                    <a href="<?php echo e(route('user.products.files.download-latest', $product)); ?>" class="user-action-button">
                        <i class="fas fa-download"></i>
                        <?php if(isset($latestUpdate) && $latestUpdate): ?>
                            <?php echo e(trans('app.Download Latest Update')); ?> (v<?php echo e($latestUpdate->version); ?>)
                        <?php else: ?>
                            <?php echo e(trans('app.Download Product')); ?>

                        <?php endif; ?>
                    </a>
                <?php endif; ?>
                <?php if(isset($allVersions) && count($allVersions) > 1): ?>
                    <a href="<?php echo e(route('user.products.files.download-all', $product)); ?>" class="user-action-button">
                        <i class="fas fa-download"></i>
                        <?php echo e(trans('app.Download All as ZIP')); ?>

                    </a>
                <?php endif; ?>
            </div>
            <?php if(isset($permissions) && !$permissions['can_download']): ?>
                <div class="user-alert user-alert-warning">
                    <div class="user-alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="user-alert-content">
                        <h4 class="user-alert-title"><?php echo e(trans('app.Cannot Download Files')); ?></h4>
                        <p class="user-alert-message"><?php echo e($permissions['message']); ?></p>
                        
                        <?php if(!$permissions['has_license']): ?>
                            <div class="user-alert-actions">
                                <a href="<?php echo e(route('user.products.show', $product)); ?>" class="user-btn user-btn-primary">
                                    <i class="fas fa-shopping-cart"></i>
                                    <?php echo e(trans('app.Purchase Product')); ?>

                                </a>
                            </div>
                        <?php elseif(!$permissions['has_paid_invoice']): ?>
                            <div class="user-alert-actions">
                                <a href="<?php echo e(route('user.invoices.index')); ?>" class="user-btn user-btn-warning">
                                    <i class="fas fa-credit-card"></i>
                                    <?php echo e(trans('app.Pay Invoice')); ?>

                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif(isset($allVersions) && count($allVersions) > 0): ?>
                <!-- Update Information -->
                <?php if(isset($latestUpdate) && $latestUpdate): ?>
                    <div class="user-alert user-alert-info">
                        <div class="user-alert-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="user-alert-content">
                            <h4 class="user-alert-title"><?php echo e(trans('app.Update Available')); ?></h4>
                            <p class="user-alert-message">
                                <?php echo e(trans('app.A new update is available')); ?>: <strong><?php echo e($latestUpdate->title); ?> v<?php echo e($latestUpdate->version); ?></strong>
                                <?php if($latestUpdate->description): ?>
                                    <br><small class="text-muted"><?php echo e(Str::limit($latestUpdate->description, 100)); ?></small>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="user-alert user-alert-success">
                        <div class="user-alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="user-alert-content">
                            <h4 class="user-alert-title"><?php echo e(trans('app.Files Available')); ?></h4>
                            <p class="user-alert-message"><?php echo e(trans('app.You can download the following files because you have a valid license and paid invoice for this product.')); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- All Versions Grid -->
                <div class="user-stats-grid">
                    <?php $__currentLoopData = $allVersions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="user-stat-card flex-column h-100">
                            <!-- File Header -->
                            <div class="user-stat-header">
                                <div class="user-stat-title">
                                    <?php echo e($file->original_name); ?>

                                    <?php if(isset($file->is_update) && $file->is_update): ?>
                                        <span class="inline-flex items-center p-2-8 bg-orange-500 rounded-12 fs-11 fw-500 ml-8">
                                            <i class="fas fa-sync-alt mr-4"></i>
                                            <?php echo e(trans('app.Update')); ?>

                                        </span>
                                        <?php if(isset($file->update_info) && $file->update_info->is_required): ?>
                                            <span class="inline-flex items-center p-2-8 bg-red-500 rounded-12 fs-11 fw-500 ml-4">
                                                <i class="fas fa-exclamation-triangle mr-4"></i>
                                                <?php echo e(trans('app.Required')); ?>

                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="user-stat-icon <?php echo e(isset($file->is_update) && $file->is_update ? 'orange' : 'blue'); ?>">
                                    <?php if($file->file_extension == 'zip'): ?>
                                        <i class="fas fa-file-archive"></i>
                                    <?php elseif(in_array($file->file_extension, ['pdf'])): ?>
                                        <i class="fas fa-file-pdf"></i>
                                    <?php elseif(in_array($file->file_extension, ['doc', 'docx'])): ?>
                                        <i class="fas fa-file-word"></i>
                                    <?php elseif(in_array($file->file_extension, ['xls', 'xlsx'])): ?>
                                        <i class="fas fa-file-excel"></i>
                                    <?php elseif(in_array($file->file_extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                        <i class="fas fa-file-image"></i>
                                    <?php else: ?>
                                        <i class="fas fa-file"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Content Area (flexible) -->
                            <div class="flex-1 flex-column">
                                <!-- File Info -->
                                <div class="user-stat-value"><?php echo e($file->formatted_size ?? number_format($file->file_size / 1024 / 1024, 2) . ' MB'); ?></div>
                                <p class="fs-14 text-gray-500 margin-0">
                                    <?php echo e(strtoupper($file->file_extension)); ?> <?php echo e(trans('app.File')); ?>

                                    <?php if(isset($file->is_update) && $file->is_update && isset($file->update_info)): ?>
                                        <br><span class="text-orange-500">v<?php echo e($file->update_info->version); ?></span>
                                    <?php endif; ?>
                                </p>
                                
                                <!-- File Description -->
                                <?php if($file->description): ?>
                                    <div class="mt-12">
                                        <p class="fs-12 text-gray-400 margin-0"><?php echo e(Str::limit($file->description, 80)); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Bottom Section (always at bottom) -->
                            <div class="mt-auto">
                                <!-- File Stats -->
                                <div class="mt-12 d-flex justify-content-between fs-12 text-gray-500">
                                    <span><i class="fas fa-download mr-4"></i><?php echo e($file->download_count ?? 0); ?> <?php echo e(trans('app.Downloads')); ?></span>
                                    <span><i class="fas fa-calendar mr-4"></i><?php echo e($file->created_at->format('M d, Y')); ?></span>
                                </div>
                                
                                <!-- Download Button -->
                                <div class="mt-3">
                                    <?php if(isset($file->is_update) && $file->is_update): ?>
                                        <?php if(isset($file->update_info) && $file->update_info->file_path): ?>
                                            <a href="<?php echo e(route('user.products.files.download-update', [$product, $file->update_info->id])); ?>" 
                                               class="user-action-button"
                                               title="<?php echo e(trans('app.Download Update')); ?> <?php echo e($file->original_name); ?>">
                                                <i class="fas fa-sync-alt"></i>
                                                <?php echo e(trans('app.Download Update')); ?>

                                            </a>
                                        <?php else: ?>
                                            <button class="user-action-button secondary" disabled
                                                    title="<?php echo e(trans('app.Update file not available')); ?>">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?php echo e(trans('app.File Not Available')); ?>

                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="<?php echo e(route('user.product-files.download', $file)); ?>" 
                                           class="user-action-button"
                                           title="<?php echo e(trans('app.Download')); ?> <?php echo e($file->original_name); ?>">
                                            <i class="fas fa-download"></i>
                                            <?php echo e(trans('app.Download File')); ?>

                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- Download Statistics -->
                <div class="user-stats-grid">
                    <div class="user-stat-card">
                        <div class="user-stat-header">
                            <div class="user-stat-title"><?php echo e(trans('app.Total Versions')); ?></div>
                            <div class="user-stat-icon blue">
                                <i class="fas fa-file"></i>
                            </div>
                        </div>
                        <div class="user-stat-value"><?php echo e(count($allVersions)); ?></div>
                        <p class="fs-14 text-gray-500 margin-0"><?php echo e(trans('app.Available versions')); ?></p>
                    </div>

                    <div class="user-stat-card">
                        <div class="user-stat-header">
                            <div class="user-stat-title"><?php echo e(trans('app.Updates Available')); ?></div>
                            <div class="user-stat-icon orange">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                        </div>
                        <div class="user-stat-value"><?php echo e(collect($allVersions)->where('is_update', true)->count()); ?></div>
                        <p class="fs-14 text-gray-500 margin-0"><?php echo e(trans('app.Update files')); ?></p>
                    </div>

                    <div class="user-stat-card">
                        <div class="user-stat-header">
                            <div class="user-stat-title"><?php echo e(trans('app.Base Files')); ?></div>
                            <div class="user-stat-icon green">
                                <i class="fas fa-file-archive"></i>
                            </div>
                        </div>
                        <div class="user-stat-value"><?php echo e(collect($allVersions)->where('is_update', false)->count()); ?></div>
                        <p class="fs-14 text-gray-500 margin-0"><?php echo e(trans('app.Original files')); ?></p>
                    </div>

                    <?php if(isset($latestUpdate) && $latestUpdate): ?>
                        <div class="user-stat-card">
                            <div class="user-stat-header">
                                <div class="user-stat-title"><?php echo e(trans('app.Latest Version')); ?></div>
                                <div class="user-stat-icon orange">
                                    <i class="fas fa-sync-alt"></i>
                                </div>
                            </div>
                            <div class="user-stat-value">v<?php echo e($latestUpdate->version); ?></div>
                            <p class="fs-14 text-gray-500 margin-0"><?php echo e($latestUpdate->title); ?></p>
                        </div>

                        <div class="user-stat-card">
                            <div class="user-stat-header">
                                <div class="user-stat-title"><?php echo e(trans('app.Update Size')); ?></div>
                                <div class="user-stat-icon purple">
                                    <i class="fas fa-hdd"></i>
                                </div>
                            </div>
                            <div class="user-stat-value"><?php echo e($latestUpdate->file_size ? number_format($latestUpdate->file_size / 1024 / 1024, 2) . ' MB' : 'N/A'); ?></div>
                            <p class="fs-14 text-gray-500 margin-0"><?php echo e(trans('app.Latest update file')); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="user-stat-card">
                            <div class="user-stat-header">
                                <div class="user-stat-title"><?php echo e(trans('app.Total Size')); ?></div>
                                <div class="user-stat-icon purple">
                                    <i class="fas fa-hdd"></i>
                                </div>
                            </div>
                            <div class="user-stat-value"><?php echo e(collect($allVersions)->sum('file_size') > 0 ? number_format(collect($allVersions)->sum('file_size') / 1024 / 1024, 2) . ' MB' : '0 MB'); ?></div>
                            <p class="fs-14 text-gray-500 margin-0"><?php echo e(trans('app.Combined size')); ?></p>
                        </div>

                        <div class="user-stat-card">
                            <div class="user-stat-header">
                                <div class="user-stat-title"><?php echo e(trans('app.Last Updated')); ?></div>
                                <div class="user-stat-icon orange">
                                    <i class="fas fa-calendar"></i>
                                </div>
                            </div>
                            <div class="user-stat-value"><?php echo e(collect($allVersions)->sortByDesc('created_at')->first() ? collect($allVersions)->sortByDesc('created_at')->first()->created_at->format('Y-m-d') : 'N/A'); ?></div>
                            <p class="fs-14 text-gray-500 margin-0"><?php echo e(trans('app.Most recent file')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="user-empty-state">
                    <div class="user-empty-state-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h4 class="user-empty-state-title"><?php echo e(trans('app.No files available')); ?></h4>
                    <p class="user-empty-state-message"><?php echo e(trans('app.No files have been uploaded for this product yet.')); ?></p>
                    <div class="user-empty-state-actions">
                        <a href="<?php echo e(route('user.products.show', $product)); ?>" class="user-btn user-btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            <?php echo e(trans('app.Back to Product')); ?>

                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\user\products\files\index.blade.php ENDPATH**/ ?>