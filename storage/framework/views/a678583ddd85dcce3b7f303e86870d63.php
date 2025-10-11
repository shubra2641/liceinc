

<?php $__env->startSection('title', trans('install.completion_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1 class="install-card-title"><?php echo e(trans('install.completion_title')); ?></h1>
        <p class="install-card-subtitle"><?php echo e(trans('install.completion_subtitle')); ?></p>
    </div>

    <div class="install-card-body">
        <!-- Success Message -->
        <div class="install-alert install-alert-success">
            <i class="fas fa-check-circle"></i>
            <div>
                <h3><?php echo e(trans('install.installation_completed')); ?></h3>
                <p><?php echo e(trans('install.installation_success_message')); ?></p>
                <p class="completion-timestamp">
                    <i class="fas fa-clock"></i>
                    <?php echo e(trans('install.installation_completed_at')); ?>: <?php echo e(now()->format('Y-m-d H:i:s')); ?>

                </p>
            </div>
        </div>

        <!-- Admin Account Info -->
        <div class="completion-section">
            <h3 class="section-title">
                <i class="fas fa-user-shield"></i>
                <?php echo e(trans('install.admin_account_created')); ?>

            </h3>
            <div class="admin-info-card">
                <div class="admin-info-item">
                    <i class="fas fa-user"></i>
                    <span class="label"><?php echo e(trans('install.admin_name')); ?>:</span>
                    <span class="value"><?php echo e($adminConfig['name'] ?? session('install.admin.name')); ?></span>
                </div>
                <div class="admin-info-item">
                    <i class="fas fa-envelope"></i>
                    <span class="label"><?php echo e(trans('install.admin_email')); ?>:</span>
                    <span class="value"><?php echo e($adminConfig['email'] ?? session('install.admin.email')); ?></span>
                </div>
                <div class="admin-info-item">
                    <i class="fas fa-shield-alt"></i>
                    <span class="label"><?php echo e(trans('install.account_status')); ?>:</span>
                    <span class="value status-verified">
                        <i class="fas fa-check-circle"></i>
                        <?php echo e(trans('install.email_verified')); ?>

                    </span>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="completion-section">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i>
                <?php echo e(trans('install.system_information')); ?>

            </h3>
            <div class="system-info-grid">
                <div class="system-info-item">
                    <i class="fas fa-globe"></i>
                    <div>
                        <h4><?php echo e(trans('install.site_name')); ?></h4>
                        <p><?php echo e($settingsConfig['site_name'] ?? session('install.settings.site_name')); ?></p>
                    </div>
                </div>
                <div class="system-info-item">
                    <i class="fas fa-database"></i>
                    <div>
                        <h4><?php echo e(trans('install.database_connected')); ?></h4>
                        <p><?php echo e($databaseConfig['db_name'] ?? session('install.database.db_name')); ?></p>
                    </div>
                </div>
                <div class="system-info-item">
                    <i class="fas fa-language"></i>
                    <div>
                        <h4><?php echo e(trans('install.default_language')); ?></h4>
                        <p><?php echo e($settingsConfig['default_language'] ?? session('install.settings.default_language')); ?></p>
                    </div>
                </div>
                <div class="system-info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4><?php echo e(trans('install.timezone')); ?></h4>
                        <p><?php echo e($settingsConfig['timezone'] ?? session('install.settings.timezone')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notice -->
        <div class="completion-section">
            <div class="install-alert install-alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <h3><?php echo e(trans('install.important_notice')); ?></h3>
                    <p><?php echo e(trans('install.delete_install_folder_warning')); ?></p>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="completion-section">
            <h3 class="section-title">
                <i class="fas fa-arrow-right"></i>
                <?php echo e(trans('install.next_steps')); ?>

            </h3>
            <div class="next-steps-grid">
                <div class="next-step-item">
                    <div class="step-icon">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <h4><?php echo e(trans('install.delete_install_folder')); ?></h4>
                    <p><?php echo e(trans('install.delete_install_folder_description')); ?></p>
                </div>
                <div class="next-step-item">
                    <div class="step-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h4><?php echo e(trans('install.configure_system')); ?></h4>
                    <p><?php echo e(trans('install.configure_system_description')); ?></p>
                </div>
                <div class="next-step-item">
                    <div class="step-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4><?php echo e(trans('install.secure_system')); ?></h4>
                    <p><?php echo e(trans('install.secure_system_description')); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="install-actions">
        <div class="action-buttons">
            <a href="<?php echo e(route('login')); ?>" class="install-btn install-btn-primary">
                <i class="fas fa-tachometer-alt"></i>
                <?php echo e(trans('install.go_to_admin_panel')); ?>

            </a>
            <a href="<?php echo e(route('welcome')); ?>" class="install-btn install-btn-secondary">
                <i class="fas fa-home"></i>
                <?php echo e(trans('install.go_to_frontend')); ?>

            </a>
        </div>
        <div class="completion-footer">
            <p class="completion-note">
                <i class="fas fa-info-circle"></i>
                <?php echo e(trans('install.completion_note')); ?>

            </p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('install.layout', ['step' => 7], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\install\completion.blade.php ENDPATH**/ ?>