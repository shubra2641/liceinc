<?php $__env->startSection('title', $article->title); ?>
<?php $__env->startSection('page-title', $article->title); ?>
<?php $__env->startSection('page-subtitle', trans('app.Knowledge Base')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Article Purchase Header -->
    <div class="category-purchase-header">
        <div class="category-purchase-header-content">


            <div class="category-purchase-hero">
                <div class="category-purchase-hero-content">
                    <div class="category-purchase-hero-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="category-purchase-hero-text">
                        <h1 class="category-purchase-hero-title"><?php echo e($article->title); ?></h1>
                        <?php if($article->excerpt): ?>
                        <p class="category-purchase-hero-subtitle"><?php echo e($article->excerpt); ?></p>
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
                    <?php if($article->requires_purchase_code && $article->purchase_message): ?>
                    <?php echo e($article->purchase_message); ?>

                    <?php elseif($article->category && $article->category->requires_purchase_code && $article->category->purchase_message): ?>
                    <?php echo e($article->category->purchase_message); ?>

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

            <form method="GET" action="<?php echo e(route('kb.article', $article->slug)); ?>" class="license-verification-form">
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


    <!-- Article Information -->
    <div class="user-card category-info-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text"><?php echo e(trans('app.Article Information')); ?></h3>
                        <p class="user-section-subtitle"><?php echo e(trans('app.About this article')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="category-info-grid">
                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Article Title')); ?></div>
                        <div class="info-value"><?php echo e($article->title); ?></div>
                    </div>
                </div>

                <?php if($article->category): ?>
                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Category')); ?></div>
                        <div class="info-value"><?php echo e($article->category->name); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Created')); ?></div>
                        <div class="info-value"><?php echo e($article->created_at->format('M d, Y')); ?></div>
                    </div>
                </div>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label"><?php echo e(trans('app.Views')); ?></div>
                        <div class="info-value"><?php echo e($article->views); ?></div>
                    </div>
                </div>

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
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\kb\article-purchase.blade.php ENDPATH**/ ?>