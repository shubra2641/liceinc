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
    <link rel="stylesheet" href="<?php echo e(asset('assets/front/css/user-dashboard.css')); ?>">
    
    <!-- Laravel Mix Compiled Assets -->
    <link rel="stylesheet" href="<?php echo e(mix('assets/front/css/app.css')); ?>">
    <?php echo $__env->yieldContent('styles'); ?>

</head>
<body class="admin-page">
    
    <?php echo $__env->make('components.preloader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <!-- Header with Logo -->
    <header class="guest-header">
        <div class="guest-header-container">
            <a href="#" class="guest-logo">
                <?php if($siteLogo): ?>
                    <img src="<?php echo e(Storage::url($siteLogo)); ?>" alt="<?php echo e($siteName); ?>" class="guest-logo-icon" />
                <?php else: ?>
                    <div class="guest-logo-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                <?php endif; ?>
                <span class="guest-logo-text"><?php echo e($siteName ?? config('app.name')); ?></span>
            </a>
        </div>
    </header>
    
    <!-- Page Content -->
    <main class="admin-main-content px-4 py-6 lg:px-8 lg:py-8 max-w-full overflow-x-auto">
        <?php echo $__env->yieldContent('content'); ?>
    </main>


    <!-- Preloader JavaScript -->
    <!-- Security Utils Library -->
    <script src="<?php echo e(asset('assets/js/security-utils.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/front/js/preloader.js')); ?>"></script>
    <!-- jQuery (must be loaded first) -->
    <script src="<?php echo e(asset('assets/front/js/jquery-3.6.0.min.js')); ?>"></script>
    <!-- Bootstrap JS (required by Summernote BS5) -->
    <script src="<?php echo e(asset('vendor/assets/bootstrap/bootstrap.bundle.min.js')); ?>"></script>
    <!-- Select2 JS -->
    <script src="<?php echo e(asset('vendor/assets/select2/select2.min.js')); ?>"></script>
    <!-- Summernote BS5 JS -->
    <script src="<?php echo e(asset('vendor/assets/summernote/summernote-bs5.min.js')); ?>"></script>
    <!-- Chart.js -->
    <script src="<?php echo e(asset('vendor/assets/chartjs/chart.min.js')); ?>"></script>
    <!-- DataTables JS -->
    <script src="<?php echo e(asset('vendor/assets/datatables/jquery.dataTables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendor/assets/datatables/dataTables.bootstrap5.min.js')); ?>"></script>
    <!-- Laravel Mix Compiled JavaScript -->
    <script src="<?php echo e(mix('assets/front/js/app.js')); ?>"></script>

    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/layouts/guest.blade.php ENDPATH**/ ?>