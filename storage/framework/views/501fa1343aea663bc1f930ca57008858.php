<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
'title' => 'Need More Help?',
'description' => 'Can\'t find what you\'re looking for? Contact our support team for personalized assistance.',
'primaryButtonText' => 'Contact Support',
'primaryButtonUrl' => 'support.tickets.create',
'secondaryButtonText' => 'Search Knowledge Base',
'secondaryButtonUrl' => 'kb.search',
'icon' => 'help'
]));

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

foreach (array_filter(([
'title' => 'Need More Help?',
'description' => 'Can\'t find what you\'re looking for? Contact our support team for personalized assistance.',
'primaryButtonText' => 'Contact Support',
'primaryButtonUrl' => 'support.tickets.create',
'secondaryButtonText' => 'Search Knowledge Base',
'secondaryButtonUrl' => 'kb.search',
'icon' => 'help'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="kb-help-section">
    <div class="kb-help-content">
        <div class="kb-help-icon">
            <?php if($icon === 'help'): ?>
                <i class="fas fa-question-circle w-16 h-16"></i>
            <?php elseif($icon === 'search'): ?>
                <i class="fas fa-search w-16 h-16"></i>
            <?php elseif($icon === 'support'): ?>
                <i class="fas fa-headset w-16 h-16"></i>
            <?php elseif($icon === 'document'): ?>
                <i class="fas fa-file-alt w-16 h-16"></i>
            <?php endif; ?>
        </div>
        <h3 class="kb-help-title">
            <?php echo e(trans('app.' . $title)); ?>

        </h3>
        <p class="kb-help-description">
            <?php echo e(trans('app.' . $description)); ?>

        </p>
        <div class="kb-help-actions">
            <a href="<?php echo e(route($primaryButtonUrl)); ?>" class="kb-help-button kb-help-button-primary">
                <i class="fas fa-arrow-right w-5 h-5"></i>
                <?php echo e(trans('app.' . $primaryButtonText)); ?>

            </a>
            <a href="<?php echo e(route($secondaryButtonUrl)); ?>" class="kb-help-button kb-help-button-secondary">
                <i class="fas fa-search w-5 h-5"></i>
                <?php echo e(trans('app.' . $secondaryButtonText)); ?>

            </a>
        </div>
    </div>
</div><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\components\kb-help-section.blade.php ENDPATH**/ ?>