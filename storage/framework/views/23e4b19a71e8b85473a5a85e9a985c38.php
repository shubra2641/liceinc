

<?php $__env->startSection('title', trans('install.database_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-database"></i>
        </div>
        <h1 class="install-card-title"><?php echo e(trans('install.database_title')); ?></h1>
        <p class="install-card-subtitle"><?php echo e(trans('install.database_subtitle')); ?></p>
    </div>

    <form method="POST" action="<?php echo e(route('install.database.store')); ?>" class="install-form" id="database-form">
        <?php echo csrf_field(); ?>
        <?php echo method_field('POST'); ?>
        
        <div class="install-card-body">
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="db_host" class="form-label">
                    <i class="fas fa-server"></i>
                    <?php echo e(trans('install.database_host')); ?>

                </label>
                <input type="text" 
                       id="db_host" 
                       name="db_host" 
                       class="form-input" 
                       value="<?php echo e(old('db_host', '127.0.0.1')); ?>" 
                       required>
                <?php $__errorArgs = ['db_host'];
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
                <label for="db_port" class="form-label">
                    <i class="fas fa-plug"></i>
                    <?php echo e(trans('install.database_port')); ?>

                </label>
                <input type="text" 
                       id="db_port" 
                       name="db_port" 
                       class="form-input" 
                       value="<?php echo e(old('db_port', '3306')); ?>" 
                       required>
                <?php $__errorArgs = ['db_port'];
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
                <label for="db_name" class="form-label">
                    <i class="fas fa-database"></i>
                    <?php echo e(trans('install.database_name')); ?>

                </label>
                <input type="text" 
                       id="db_name" 
                       name="db_name" 
                       class="form-input" 
                       value="<?php echo e(old('db_name')); ?>" 
                       required>
                <?php $__errorArgs = ['db_name'];
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
                <label for="db_username" class="form-label">
                    <i class="fas fa-user"></i>
                    <?php echo e(trans('install.database_username')); ?>

                </label>
                <input type="text" 
                       id="db_username" 
                       name="db_username" 
                       class="form-input" 
                       value="<?php echo e(old('db_username')); ?>" 
                       required>
                <?php $__errorArgs = ['db_username'];
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
                <label for="db_password" class="form-label">
                    <i class="fas fa-lock"></i>
                    <?php echo e(trans('install.database_password')); ?>

                </label>
                <input type="password" 
                       id="db_password" 
                       name="db_password" 
                       class="form-input" 
                       value="<?php echo e(old('db_password')); ?>">
                <?php $__errorArgs = ['db_password'];
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

            <div class="test-connection-section">
                <button type="button" 
                        id="test-connection-btn" 
                        class="install-btn install-btn-outline">
                    <i class="fas fa-plug"></i>
                    <span><?php echo e(trans('install.test_connection')); ?></span>
                </button>
                <div id="connection-result" class="connection-result"></div>
            </div>
        </div>

        <div class="install-actions">
            <a href="<?php echo e(route('install.requirements')); ?>" class="install-btn install-btn-secondary">
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



<?php echo $__env->make('install.layout', ['step' => 4], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\install\database.blade.php ENDPATH**/ ?>