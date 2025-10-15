<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="base-url" content="<?php echo e(url('/')); ?>">
    <meta charset="UTF-8">
    <meta name="description"
        content="<?php echo $__env->yieldContent('meta_description', $siteSeoDescription ?? trans('app.User dashboard for managing licenses and products')); ?>">

    <!-- Responsive Viewport Meta Tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>
        <?php if (! empty(trim($__env->yieldContent('title')))): ?> 
        <?php echo $__env->yieldContent('title'); ?> - <?php echo e($siteName); ?>

        <?php elseif(View::hasSection('page-title')): ?>
        <?php echo $__env->yieldContent('page-title'); ?> - <?php echo e($siteName); ?>

        <?php elseif(View::hasSection('seo_title')): ?>
        <?php echo $__env->yieldContent('seo_title'); ?> - <?php echo e($siteName); ?>

        <?php elseif($siteSeoTitle): ?>
        <?php echo e($siteSeoTitle); ?> - <?php echo e($siteName); ?>

        <?php else: ?>
        <?php echo e($siteName); ?> - <?php echo e(trans('app.Dashboard')); ?>

        <?php endif; ?>
    </title>
    
    <?php if(View::hasSection('meta_keywords')): ?>
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords'); ?>">
    <?php endif; ?>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">

    <!-- Fonts (Local) -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fonts/cairo.css')); ?>">

    <!-- Bootstrap CSS (Local) -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/bootstrap/css/bootstrap.min.css')); ?>">
    
    <!-- Font Awesome (Local) -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fontawesome/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/assets/fontawesome/css/local-fonts.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/front/css/preloader.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/front/css/maintenance.css')); ?>">
    
    <?php echo $__env->yieldContent('styles'); ?>

</head>
<body class="admin-page">
    
    <?php echo $__env->make('components.preloader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    

    
    <!-- Page Content -->
    <main class="admin-main-content px-4 py-6 lg:px-8 lg:py-8 max-w-full overflow-x-auto">
        <?php echo $__env->yieldContent('content'); ?>
    </main>


    <!-- Preloader JavaScript -->
    <!-- Security Utils Library -->
    <script src="<?php echo e(asset('assets/js/security-utils.js')); ?>"></script>
    
    <!-- Preloader Settings -->
    <script>
        window.preloaderSettings = {
            enabled: <?php echo e($preloaderSettings['preloaderEnabled'] ? 'true' : 'false'); ?>,
            type: '<?php echo e($preloaderSettings['preloaderType']); ?>',
            color: '<?php echo e($preloaderSettings['preloaderColor']); ?>',
            backgroundColor: '<?php echo e($preloaderSettings['preloaderBgColor']); ?>',
            duration: <?php echo e($preloaderSettings['preloaderDuration']); ?>,
            minDuration: <?php echo e($preloaderSettings['preloaderMinDuration'] ?? 0); ?>,
            text: '<?php echo e($preloaderSettings['preloaderText']); ?>',
            logo: '<?php echo e($preloaderSettings['siteLogo']); ?>',
            logoText: '<?php echo e($preloaderSettings['logoText']); ?>',
            logoShowText: <?php echo e($preloaderSettings['logoShowText'] ? 'true' : 'false'); ?>

        };
    </script>

        <!-- Preloader JavaScript -->
        <script src="<?php echo e(asset('assets/admin/js/preloader.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/layouts/guest.blade.php ENDPATH**/ ?>