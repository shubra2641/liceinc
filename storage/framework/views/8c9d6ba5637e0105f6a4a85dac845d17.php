<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(app()->getLocale() == 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', trans('install.install_title')); ?> - <?php echo e(config('app.name', 'License Management System')); ?></title>
    
    <!-- Fonts -->
    <!-- Fonts (Local) -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fonts/cairo.css')); ?>">
    
    <!-- FontAwesome (Local) -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fontawesome/css/all.min.css')); ?>">
    
    <!-- Installation Styles -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/install/css/install.css')); ?>">
    
    <!-- License Verification Styles -->
    <?php if(request()->routeIs('install.license*')): ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/install/css/license.css')); ?>">
    <?php endif; ?>
    
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body class="install-body">
    <!-- Installation Header -->
    <div class="install-header">
        <div class="install-header-content">
            <div class="install-logo">
                <i class="fas fa-shield-alt"></i>
                <span><?php echo e(config('app.name', 'License Management System')); ?></span>
            </div>
            <div class="install-version">
                <span>Installation Wizard</span>
            </div>
        </div>
    </div>

    <!-- Installation Progress -->
    <div class="install-progress">
        <div class="install-progress-line"></div>
        
        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stepData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="install-step <?php echo e($stepData['status']); ?>">
                <div class="install-step-number">
                    <?php if($stepData['isCompleted']): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        <?php echo e($stepData['number']); ?>

                    <?php endif; ?>
                </div>
                <div class="install-step-title"><?php echo e($stepData['name']); ?></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Main Content -->
    <div class="install-container">
        <div class="install-content">
            <?php if(session('success')): ?>
                <div class="install-alert install-alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="install-alert install-alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('info')): ?>
                <div class="install-alert install-alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php echo e(session('info')); ?>

                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>

    <!-- Installation Footer -->
    <div class="install-footer">
        <div class="install-footer-content">
            <div class="install-footer-text">
                <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name', 'License Management System')); ?>. All rights reserved.</p>
            </div>
            <div class="install-footer-links">
                <a href="https://my-logos.com/tickets" class="install-footer-link">
                    <i class="fas fa-question-circle"></i>
                    <span>Help</span>
                </a>
                <a href="Https://www.my-logos.com/kb" class="install-footer-link">
                    <i class="fas fa-book"></i>
                    <span>Documentation</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Installation Scripts -->
    <script src="<?php echo e(asset('assets/install/js/install.js')); ?>"></script>
    
    <!-- License Verification Scripts -->
    <?php if(request()->routeIs('install.license*')): ?>
    <script src="<?php echo e(asset('assets/install/js/license.js')); ?>"></script>
    <?php endif; ?>
    
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\install\layout.blade.php ENDPATH**/ ?>