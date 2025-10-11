<?php $__env->startSection('title', $category->name); ?>
<?php $__env->startSection('page-title', $category->name); ?>
<?php $__env->startSection('page-subtitle', trans('app.Knowledge Base')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Category Purchase Header -->
    <div class="category-purchase-header">
        <div class="category-purchase-header-content">


            <div class="category-purchase-hero">
                <div class="category-purchase-hero-content">
                    <div class="category-purchase-hero-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="category-purchase-hero-text">
                        <h1 class="category-purchase-hero-title"><?php echo e($category->name); ?></h1>
                        <?php if(!empty($category->description)): ?>
                        <p class="category-purchase-hero-subtitle"><?php echo e($category->description); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="category-purchase-hero-badge">
                        <span class="user-badge user-badge-warning">
                            <i class="fas fa-shield-alt"></i>
                            <?php echo e(trans('app.license_code_protection')); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Card -->
    <div class="license-verification-card">
        <div class="license-verification-header">
            <div class="license-verification-icon">
                <i class="fas fa-key"></i>
            </div>
            <div class="license-verification-title-section">
                <h2 class="license-verification-title"><?php echo e(trans('app.license_code_protection')); ?></h2>
                <p class="license-verification-subtitle">
                    <?php if(!empty($category->purchase_message)): ?>
                    <?php echo e($category->purchase_message); ?>

                    <?php else: ?>
                    <?php echo e(trans('app.users_must_enter_license_to_access_content')); ?>

                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="license-verification-content">
            <?php if(!empty($error) || session('error')): ?>
            <div class="license-error-message">
                <div class="license-error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="license-error-content">
                    <h4 class="license-error-title"><?php echo e(trans('app.Error')); ?></h4>
                    <p class="license-error-text"><?php echo e($error ?? session('error')); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <form method="GET" action="<?php echo e(route('kb.category', $category->slug)); ?>" class="license-verification-form">
                <div class="license-form-group">
                    <label for="raw_code" class="license-form-label">
                        <i class="fas fa-key"></i>
                        <?php echo e(trans('app.license_code')); ?>

                    </label>
                    <input 
                        id="raw_code" 
                        name="raw_code" 
                        type="text" 
                        required 
                        class="license-form-input"
                        placeholder="<?php echo e(trans('app.enter_license_code')); ?>"
                    >
                    <p class="license-form-help">
                        <i class="fas fa-info-circle"></i>
                        <?php echo e(trans('app.code_will_not_be_cleaned')); ?>

                    </p>
                </div>

                <button type="submit" class="license-verify-button">
                    <i class="fas fa-check"></i>
                    <?php echo e(trans('app.verify_license_code')); ?>

                </button>
            </form>
        </div>
    </div>

    <!-- Category Information -->
    <div class="user-card category-info-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text"><?php echo e(trans('app.Category Information')); ?></h3>
                        <p class="user-section-subtitle"><?php echo e(trans('app.About this category')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="category-info-grid">
                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Category Name')); ?></div>
                        <div class="info-value"><?php echo e($category->name); ?></div>
                    </div>
                </div>

                <?php if(!empty($category->description)): ?>
                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Description')); ?></div>
                        <div class="info-value"><?php echo e($category->description); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Access Level')); ?></div>
                        <div class="info-value">
                            <span class="user-badge user-badge-warning">
                                <i class="fas fa-lock"></i>
                                <?php echo e(trans('app.License Required')); ?>

                            </span>
                        </div>
                    </div>
                </div>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Created')); ?></div>
                        <div class="info-value"><?php echo e($category->created_at->format('M d, Y')); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\kb\category-purchase.blade.php ENDPATH**/ ?>