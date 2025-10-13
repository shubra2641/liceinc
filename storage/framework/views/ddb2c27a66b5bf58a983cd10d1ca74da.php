<?php if(auth()->check() && auth()->user()->is_admin): ?>
<div id="update-notification" class="update-notification">
    <div class="update-notification-content">
        <div class="update-notification-icon">
            <i class="fas fa-download"></i>
        </div>
        <div class="update-notification-text">
            <h6><?php echo e(trans('app.Update Available')); ?></h6>
            <p><?php echo e(trans('app.A new version is available for your system')); ?></p>
        </div>
        <div class="update-notification-actions">
            <a href="<?php echo e(route('admin.updates.index')); ?>" class="btn btn-sm btn-warning">
                <?php echo e(trans('app.Update Now')); ?>

            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="dismissUpdateNotification()">
                <?php echo e(trans('app.Later')); ?>

            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="dismissUpdateNotificationPermanently()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<?php endif; ?>
<?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/components/UpdateNotification.blade.php ENDPATH**/ ?>