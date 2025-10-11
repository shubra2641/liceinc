<!DOCTYPE html>
<html>
<head>
    <title>Mix Test</title>
    <link rel="stylesheet" href="<?php echo e(mix('assets/admin/css/app.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(mix('assets/front/css/app.css')); ?>">
</head>
<body>
    <div class="container">
        <h1>Laravel Mix Test</h1>
        <p>This page tests Mix asset loading.</p>
        
        <div class="admin-test text-blue">Admin CSS Test</div>
        <div class="front-test text-green">Front CSS Test</div>
    </div>
    
    <script src="<?php echo e(mix('assets/admin/js/app.js')); ?>"></script>
    <script src="<?php echo e(mix('assets/front/js/app.js')); ?>"></script>
</body>
</html><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\test-mix.blade.php ENDPATH**/ ?>