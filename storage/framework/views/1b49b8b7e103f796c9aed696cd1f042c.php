<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['breadcrumbs' => []]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['breadcrumbs' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(count($breadcrumbs) > 1): ?>
<div class="bg-white px-4 py-3 border-b border-gray-200 sm:px-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <?php $__currentLoopData = $breadcrumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $breadcrumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($index > 0): ?>
                            <li>
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right flex-shrink-0 h-5 w-5 text-gray-400"></i>
                                    <?php if($breadcrumb['app.Active']): ?>
                                        <span class="ml-2 text-sm font-medium text-gray-500" aria-current="page"><?php echo e($breadcrumb['title']); ?></span>
                                    <?php else: ?>
                                        <a href="<?php echo e($breadcrumb['url']); ?>" class="ml-2 text-sm font-medium text-gray-700 hover:text-gray-900"><?php echo e($breadcrumb['title']); ?></a>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
            </nav>
        </div>
    </div>
</div>
<?php endif; ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\components\breadcrumb.blade.php ENDPATH**/ ?>