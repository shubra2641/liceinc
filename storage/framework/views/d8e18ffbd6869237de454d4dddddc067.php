

<?php $__env->startSection('title', trans('install.requirements_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <h1 class="install-card-title"><?php echo e(trans('install.requirements_title')); ?></h1>
        <p class="install-card-subtitle"><?php echo e(trans('install.requirements_subtitle')); ?></p>
    </div>

    <div class="install-card-body">

        <div class="requirements-list">
            <?php $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="requirement-item <?php echo e($requirement['passed'] ? 'passed' : 'failed'); ?>">
                    <div class="requirement-status">
                        <i class="fas <?php echo e($requirement['passed'] ? 'fa-check-circle' : 'fa-times-circle'); ?>"></i>
                    </div>
                    <div class="requirement-details">
                        <div class="requirement-name"><?php echo e($requirement['name']); ?></div>
                        <div class="requirement-info">
                            <span class="requirement-required"><?php echo e(trans('install.required')); ?>: <?php echo e($requirement['required']); ?></span>
                            <span class="requirement-current"><?php echo e(trans('install.current')); ?>: <?php echo e($requirement['current']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="requirements-summary <?php echo e($allPassed ? 'success' : 'error'); ?>">
            <div class="summary-icon">
                <i class="fas <?php echo e($allPassed ? 'fa-check-circle' : 'fa-exclamation-triangle'); ?>"></i>
            </div>
            <div class="summary-text">
                <?php if($allPassed): ?>
                    <h3><?php echo e(trans('install.requirements_all_passed')); ?></h3>
                    <p><?php echo e(trans('install.requirements_success_message')); ?></p>
                <?php else: ?>
                    <h3><?php echo e(trans('install.requirements_failed')); ?></h3>
                    <p><?php echo e(trans('install.requirements_failed_message')); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="install-actions">
        <a href="<?php echo e(route('install.welcome')); ?>" class="install-btn install-btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span><?php echo e(trans('install.back')); ?></span>
        </a>
        
        <?php if($allPassed): ?>
            <a href="<?php echo e(route('install.database')); ?>" class="install-btn install-btn-primary">
                <i class="fas fa-arrow-right"></i>
                <span><?php echo e(trans('install.continue')); ?></span>
            </a>
        <?php else: ?>
            <button class="install-btn install-btn-primary" disabled>
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo e(trans('install.fix_requirements')); ?></span>
            </button>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('install.layout', ['step' => 3], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\install\requirements.blade.php ENDPATH**/ ?>