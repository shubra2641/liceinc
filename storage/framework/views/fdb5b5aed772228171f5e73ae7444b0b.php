

<?php $__env->startSection('title', trans('app.Confirm Password')); ?>
<?php $__env->startSection('page-title', trans('app.Confirm your password')); ?>
<?php $__env->startSection('page-subtitle', trans('app.This is a secure area of the application. Please confirm your password before continuing.')); ?>
<?php $__env->startSection('app.Description', trans('app.Confirm your password to proceed to secure areas')); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">
    <div class="admin-card">
    <!-- Header Section -->
    <div class="admin-card-header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
                <h2 class="admin-card-title text-2xl font-bold text-slate-900 dark:text-white">
                    <?php echo e(trans('app.Confirm your password')); ?>

                </h2>
                <p class="text-slate-600 dark:text-slate-400 mt-2">
                    <?php echo e(trans('app.This is a secure area of the application. Please confirm your password before continuing.')); ?>

                </p>
            </div>
        </div>
    </div>

    <div class="admin-card-content">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <form method="POST" action="<?php echo e(route('password.confirm')); ?>" class="space-y-6 auth-form" novalidate>
                    <?php echo csrf_field(); ?>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            <?php echo e(trans('app.Password')); ?>

                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock h-5 w-5 text-slate-400"></i>
                            </div>
                            <input id="password" name="password" type="password"
                                class="block w-full pl-10 pr-3 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                required autofocus autocomplete="current-password"
                                placeholder="<?php echo e(trans('app.Enter your password')); ?>" />
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                <i class="fas fa-exclamation-circle w-4 h-4 mr-1"></i>
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <span class="button-text"><?php echo e(trans('app.Confirm')); ?></span>
                        <i class="fas fa-spinner fa-spin button-loading hidden ml-2 -mr-1 w-4 h-4"></i>
                    </button>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Security Info -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-shield-check w-5 h-5 text-indigo-600"></i>
                            <?php echo e(trans('app.Security Notice')); ?>

                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-exclamation-triangle w-6 h-6 text-amber-500 mt-0.5 flex-shrink-0"></i>
                            <div>
                                <h4 class="text-sm font-medium text-slate-900 dark:text-white"><?php echo e(trans('app.Secure Area')); ?></h4>
                                <p class="text-sm text-slate-600 dark:text-slate-400"><?php echo e(trans('app.This area requires password confirmation for security')); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-check-circle w-6 h-6 text-green-500 mt-0.5 flex-shrink-0"></i>
                            <div>
                                <h4 class="text-sm font-medium text-slate-900 dark:text-white"><?php echo e(trans('app.Your Data is Safe')); ?></h4>
                                <p class="text-sm text-slate-600 dark:text-slate-400"><?php echo e(trans('app.We never store your password in plain text')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-6">
                    <div class="text-center">
                        <i class="fas fa-question-circle w-8 h-8 text-blue-600 dark:text-blue-400 mx-auto mb-3"></i>
                        <h4 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                            <?php echo e(trans('app.Need Help?')); ?>

                        </h4>
                        <p class="text-slate-600 dark:text-slate-300 text-sm mb-4">
                            <?php echo e(trans('app.Forgot your password?')); ?>

                        </p>
                        <a href="<?php echo e(route('password.request')); ?>" class="admin-btn admin-btn-primary w-full flex items-center justify-center gap-2">
                            <i class="fas fa-key w-4 h-4"></i>
                            <?php echo e(trans('app.Reset Password')); ?>

                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\auth\confirm-password.blade.php ENDPATH**/ ?>