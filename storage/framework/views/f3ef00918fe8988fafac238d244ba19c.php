<?php if($paginator->hasPages() || $paginator->total() > 0): ?>
    <div class="user-pagination-wrapper">
        
        <div class="user-pagination-info">
            <div class="user-pagination-stats">
                <i class="fas fa-chart-bar text-primary me-2"></i>
                <span class="user-pagination-text">
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
        <div class="user-pagination-center">
            <ul class="user-pagination-numbers">
                
                <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    
                    <?php if(is_string($element)): ?>
                        <li class="user-page-item disabled">
                            <span class="user-page-link user-page-dots"><?php echo e($element); ?></span>
                        </li>
                    <?php endif; ?>

                    
                    <?php if(is_array($element)): ?>
                        <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page == $paginator->currentPage()): ?>
                                <li class="user-page-item active">
                                    <span class="user-page-link user-page-number"><?php echo e($page); ?></span>
                                </li>
                            <?php else: ?>
                                <li class="user-page-item">
                                    <a class="user-page-link user-page-number" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <?php else: ?>
        <div class="user-pagination-center">
            <div class="user-single-page">
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="user-single-page-text"><?php echo e(__('Page 1 of 1')); ?></span>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if($paginator->hasPages()): ?>
        <div class="user-pagination-nav">
            <ul class="user-pagination-controls">
                
                <?php if($paginator->onFirstPage()): ?>
                    <li class="user-page-item disabled">
                        <span class="user-page-link user-page-prev">
                            <i class="fas fa-chevron-left"></i>
                            <span class="user-page-text"><?php echo e(__('Previous')); ?></span>
                        </span>
                    </li>
                <?php else: ?>
                    <li class="user-page-item">
                        <a class="user-page-link user-page-prev" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">
                            <i class="fas fa-chevron-left"></i>
                            <span class="user-page-text"><?php echo e(__('Previous')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                
                <?php if($paginator->hasMorePages()): ?>
                    <li class="user-page-item">
                        <a class="user-page-link user-page-next" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">
                            <span class="user-page-text"><?php echo e(__('Next')); ?></span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="user-page-item disabled">
                        <span class="user-page-link user-page-next">
                            <span class="user-page-text"><?php echo e(__('Next')); ?></span>
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\vendor\pagination\user-custom.blade.php ENDPATH**/ ?>