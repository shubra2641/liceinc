



<?php if($preloaderSettings['preloaderEnabled']): ?>
<div class="preloader-container" id="preloader-container" data-enabled="1"
    data-type="<?php echo e($preloaderSettings['preloaderType']); ?>" data-color="<?php echo e($preloaderSettings['preloaderColor']); ?>"
    data-bg="<?php echo e($preloaderSettings['preloaderBgColor']); ?>" data-duration="<?php echo e($preloaderSettings['preloaderDuration']); ?>"
    data-min-duration="<?php echo e($preloaderSettings['preloaderMinDuration'] ?? 0); ?>"
    data-text="<?php echo e($preloaderSettings['preloaderText']); ?>" data-logo="<?php echo e($preloaderSettings['siteLogo']); ?>">
    <div class="preloader-content">
        
        <?php if($preloaderSettings['siteLogo'] || $preloaderSettings['logoShowText']): ?>
        <div class="preloader-logo">
            <?php if($preloaderSettings['siteLogo']): ?>
            <img src="<?php echo e(asset('storage/' . $preloaderSettings['siteLogo'])); ?>"
                alt="<?php echo e($preloaderSettings['logoText']); ?>" class="preloader-logo-img"
                class="max-w-[150px] max-h-[50px]">
            <?php elseif($preloaderSettings['logoShowText']): ?>
            <div class="preloader-logo-text" class="text-gray-800 text-2xl font-semibold">
                <?php echo e($preloaderSettings['logoText']); ?>

            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        
        <div class="preloader-animation">
            <?php switch($preloaderSettings['preloaderType']):
            case ('spinner'): ?>
            <div class="preloader-spinner"></div>
            <?php break; ?>
            <?php case ('dots'): ?>
            <div class="preloader-dots">
                <div class="preloader-dot"></div>
                <div class="preloader-dot"></div>
                <div class="preloader-dot"></div>
            </div>
            <?php break; ?>
            <?php case ('bars'): ?>
            <div class="preloader-bars">
                <div class="preloader-bar"></div>
                <div class="preloader-bar"></div>
                <div class="preloader-bar"></div>
                <div class="preloader-bar"></div>
                <div class="preloader-bar"></div>
            </div>
            <?php break; ?>
            <?php case ('pulse'): ?>
            <div class="preloader-pulse"></div>
            <?php break; ?>
            <?php case ('progress'): ?>
            <div class="preloader-progress">
                <div class="preloader-progress-bar"></div>
            </div>
            <?php break; ?>
            <?php case ('custom'): ?>
            <div class="preloader-custom">
                <?php echo e($settings->preloader_custom_css ?? '<div class="custom-loader"></div>'); ?>

            </div>
            <?php break; ?>
            <?php default: ?>
            <div class="preloader-spinner"></div>
            <?php endswitch; ?>
        </div>

        
        <div class="preloader-text"><?php echo e($preloaderSettings['preloaderText']); ?></div>
    </div>
</div>


<?php endif; ?>


<noscript>
    <link rel="stylesheet" href="<?php echo e(asset('assets/front/css/preloader-noscript.css')); ?>">
</noscript><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\components\preloader.blade.php ENDPATH**/ ?>