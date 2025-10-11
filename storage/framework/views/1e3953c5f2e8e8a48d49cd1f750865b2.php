

<?php $__env->startSection('title', trans('install.admin_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <h1 class="install-card-title"><?php echo e(trans('install.admin_title')); ?></h1>
        <p class="install-card-subtitle"><?php echo e(trans('install.admin_subtitle')); ?></p>
    </div>

    <form method="POST" action="<?php echo e(route('install.admin.store')); ?>" class="install-form" id="admin-form">
        <?php echo csrf_field(); ?>
        
        <div class="install-card-body">
            <div class="form-group">
                <label for="name" class="form-label">
                    <i class="fas fa-user"></i>
                    <?php echo e(trans('install.admin_name')); ?>

                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-input" 
                       value="<?php echo e(old('name')); ?>" 
                       required>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="form-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i>
                    <?php echo e(trans('install.admin_email')); ?>

                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input" 
                       value="<?php echo e(old('email')); ?>" 
                       required>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="form-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i>
                    <?php echo e(trans('install.admin_password')); ?>

                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-input" 
                       required
                       minlength="8">
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="form-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <div class="form-hint"><?php echo e(trans('install.password_hint')); ?></div>
                <noscript>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <?php echo e(trans('install.javascript_required_for_password_validation')); ?>

                    </div>
                </noscript>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">
                    <i class="fas fa-lock"></i>
                    <?php echo e(trans('install.admin_password_confirmation')); ?>

                </label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       class="form-input" 
                       required>
            </div>
        </div>

        <div class="install-actions">
            <a href="<?php echo e(route('install.database')); ?>" class="install-btn install-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span><?php echo e(trans('install.back')); ?></span>
            </a>
            
            <button type="submit" class="install-btn install-btn-primary">
                <i class="fas fa-arrow-right"></i>
                <span><?php echo e(trans('install.continue')); ?></span>
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('install.layout', ['step' => 5], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\install\admin.blade.php ENDPATH**/ ?>