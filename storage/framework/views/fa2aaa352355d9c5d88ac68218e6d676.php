<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($rendered['subject']); ?></title>

</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1><?php echo e(config('app.name')); ?></h1>
            <p><?php echo e($rendered['subject']); ?></p>
        </div>

        <!-- Content -->
        <div class="email-content">
            <?php echo e($rendered['body']); ?>

        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>
                <strong><?php echo e(config('app.name')); ?></strong><br>
                <?php echo e(config('app.url')); ?>

            </p>

            <div class="social-links">
                <a href="<?php echo e(config('app.url')); ?>/support">Support</a>
                <a href="<?php echo e(config('app.url')); ?>/privacy">Privacy Policy</a>
                <a href="<?php echo e(config('app.url')); ?>/terms">Terms of Service</a>
            </div>

            <p class="text-xs text-gray-400">
                This email was sent to <?php echo e($data['recipient_email'] ?? 'you'); ?>.
                If you no longer wish to receive these emails, you can
                <a href="<?php echo e(config('app.url')); ?>/unsubscribe">unsubscribe here</a>.
            </p>

            <p class="text-xs text-gray-400">
                © <?php echo e($data['current_year'] ?? date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/emails/dynamic.blade.php ENDPATH**/ ?>