

<?php $__env->startSection('title', 'Unauthorized - Error 403'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-24 w-24 text-red-500">
                <i class="fas fa-exclamation-triangle text-6xl text-red-500"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Error 403 - Unauthorized
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                <?php echo e($exception->getMessage() ?: 'You do not have permission to access this page'); ?>

            </p>
        </div>
        
        <div class="mt-8 space-y-4">
            <div class="text-center">
                <a href="<?php echo e(url()->previous()); ?>" 
                   class="error-btn-primary">
                    <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
                    Go Back
                </a>
            </div>
            
            <div class="text-center">
                <a href="<?php echo e(route('home')); ?>" 
                   class="error-btn-secondary">
                    <i class="fas fa-home w-4 h-4 mr-2"></i>
                    Home Page
                </a>
            </div>
            
            <?php if(auth()->guard()->check()): ?>
            <div class="text-center">
                <a href="<?php echo e(route('dashboard')); ?>" 
                   class="error-btn-secondary">
                    <i class="fas fa-chart-bar w-4 h-4 mr-2"></i>
                    Dashboard
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-8 text-center">
            <p class="text-xs text-gray-500">
                If you believe this is an error, please contact technical support
            </p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/errors/403.blade.php ENDPATH**/ ?>