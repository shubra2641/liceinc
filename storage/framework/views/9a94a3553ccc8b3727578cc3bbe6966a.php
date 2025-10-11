<?php if($paginator->hasPages() || $paginator->total() > 0): ?>
<div class="admin-pagination-wrapper">
    
    <div class="admin-pagination-info">
        <div class="admin-pagination-stats">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <span class="admin-pagination-text">
                <?php echo e(__('Showing')); ?>

                <strong class="text-primary"><?php echo e($paginator->firstItem() ?? 0); ?></strong>
                <?php echo e(__('to')); ?>

                <strong class="text-primary"><?php echo e($paginator->lastItem() ?? 0); ?></strong>
                <?php echo e(__('of')); ?>

                <strong class="text-primary"><?php echo e($paginator->total()); ?></strong>
                <?php echo e(__('results')); ?>

            </span>
        </div>
    </div>

    
    <?php if($paginator->hasPages()): ?>
    <div class="admin-pagination-center">
        <ul class="admin-pagination-numbers">
            
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            
            <?php if(is_string($element)): ?>
            <li class="admin-page-item disabled">
                <span class="admin-page-link admin-page-dots"><?php echo e($element); ?></span>
            </li>
            <?php endif; ?>

            
            <?php if(is_array($element)): ?>
            <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($page == $paginator->currentPage()): ?>
            <li class="admin-page-item active">
                <span class="admin-page-link admin-page-number"><?php echo e($page); ?></span>
            </li>
            <?php else: ?>
            <li class="admin-page-item">
                <a class="admin-page-link admin-page-number" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
            </li>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php else: ?>
    <div class="admin-pagination-center">
        <div class="admin-single-page">
            <i class="fas fa-check-circle text-success me-2"></i>
            <span class="admin-single-page-text"><?php echo e(__('Page 1 of 1')); ?></span>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($paginator->hasPages()): ?>
    <div class="admin-pagination-nav">
        <ul class="admin-pagination-controls">
            
            <?php if($paginator->onFirstPage()): ?>
            <li class="admin-page-item disabled">
                <span class="admin-page-link admin-page-prev">
                    <i class="fas fa-chevron-left"></i>
                    <span class="admin-page-text"><?php echo e(__('Previous')); ?></span>
                </span>
            </li>
            <?php else: ?>
            <li class="admin-page-item">
                <a class="admin-page-link admin-page-prev" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">
                    <i class="fas fa-chevron-left"></i>
                    <span class="admin-page-text"><?php echo e(__('Previous')); ?></span>
                </a>
            </li>
            <?php endif; ?>

            
            <?php if($paginator->hasMorePages()): ?>
            <li class="admin-page-item">
                <a class="admin-page-link admin-page-next" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">
                    <span class="admin-page-text"><?php echo e(__('Next')); ?></span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
            <?php else: ?>
            <li class="admin-page-item disabled">
                <span class="admin-page-link admin-page-next">
                    <span class="admin-page-text"><?php echo e(__('Next')); ?></span>
                    <i class="fas fa-chevron-right"></i>
                </span>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\vendor\pagination\admin-custom.blade.php ENDPATH**/ ?>