

<?php $__env->startSection('title', trans('install.welcome_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-rocket"></i>
        </div>
        <h1 class="install-card-title"><?php echo e(trans('install.welcome_title')); ?></h1>
        <p class="install-card-subtitle"><?php echo e(trans('install.welcome_subtitle')); ?></p>
    </div>

    <div class="install-card-body">
        <div class="install-description">
            <p><?php echo e(trans('install.welcome_description')); ?></p>
        </div>

        <!-- Language Selector -->
        <div class="language-selector">
            <label for="language-select" class="language-label">
                <i class="fas fa-globe"></i>
                <?php echo e(trans('install.select_language')); ?>

            </label>
            <select id="language-select" class="language-select">
                <option value="en" <?php echo e(app()->getLocale() == 'en' ? 'selected' : ''); ?>>English</option>
                <option value="ar" <?php echo e(app()->getLocale() == 'ar' ? 'selected' : ''); ?>>العربية</option>
            </select>
            <noscript>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php echo e(trans('install.javascript_required_for_language_switching')); ?>

                </div>
            </noscript>
        </div>

        <!-- Installation Steps Overview -->
        <div class="install-steps-overview">
            <h3><?php echo e(trans('install.what_we_setup')); ?></h3>
            <div class="steps-grid">
                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="step-text">
                        <h4>License Verification</h4>
                        <p>Verify your purchase code to ensure you have a valid license</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="step-text">
                        <h4><?php echo e(trans('install.step_requirements')); ?></h4>
                        <p><?php echo e(trans('install.step_requirements_desc')); ?></p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="step-text">
                        <h4><?php echo e(trans('install.step_database')); ?></h4>
                        <p><?php echo e(trans('install.step_database_desc')); ?></p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="step-text">
                        <h4><?php echo e(trans('install.step_admin')); ?></h4>
                        <p><?php echo e(trans('install.step_admin_desc')); ?></p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-icon">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <div class="step-text">
                        <h4><?php echo e(trans('install.step_settings')); ?></h4>
                        <p><?php echo e(trans('install.step_settings_desc')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="install-actions">
        <a href="<?php echo e(route('install.license')); ?>" class="install-btn install-btn-primary">
            <i class="fas fa-arrow-right"></i>
            <span><?php echo e(trans('install.get_started')); ?></span>
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('install.layout', ['step' => 1], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\install\welcome.blade.php ENDPATH**/ ?>