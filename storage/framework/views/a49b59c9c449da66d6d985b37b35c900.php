

<?php $__env->startSection('title', trans('app.Updater Backups')); ?>

<?php $__env->startSection('admin-content'); ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><?php echo e(trans('app.Updater Backups')); ?></h3>
        </div>
        <div class="admin-card-body">
            <p><?php echo e(trans('app.backups_list_help')); ?></p>
            <?php if(empty($files)): ?>
                <div class="text-muted"><?php echo e(trans('app.no_backups_found')); ?></div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php echo e(trans('app.File')); ?></th>
                            <th><?php echo e(trans('app.Size')); ?></th>
                            <th><?php echo e(trans('app.Modified')); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($file['name']); ?></td>
                            <td><?php echo e(number_format($file['size'] / 1024, 2)); ?> KB</td>
                            <td><?php echo e($file['mtime']); ?></td>
                            <td>
                                <form method="POST" action="<?php echo e(route('backups.restore')); ?>" class="restore-form" data-confirm="<?php echo e(e(trans('app.confirm_restore_backup'))); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="backup" value="<?php echo e($file['name']); ?>" />
                                    <button class="btn btn-danger"><?php echo e(trans('app.Restore')); ?></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\update\backups.blade.php ENDPATH**/ ?>