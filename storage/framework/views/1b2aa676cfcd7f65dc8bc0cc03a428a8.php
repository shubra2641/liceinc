<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['siteName' => config('app.name')]));

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

foreach (array_filter((['siteName' => config('app.name')]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<!-- Enhanced User Footer with Need More Help Section -->
<footer class="user-footer-enhanced">
    <!-- Need More Help Section -->
    <div class="user-kb-premium-help">
        <div class="user-kb-help-background">
            <div class="user-kb-help-pattern"></div>
        </div>
        
        <div class="user-kb-help-container">
            <div class="user-kb-help-header">
                <div class="user-kb-help-icon-wrapper">
                    <div class="user-kb-help-icon">
                        <i class="fas fa-life-ring"></i>
                    </div>
                    <div class="user-kb-help-icon-glow"></div>
                </div>
                <div class="user-kb-help-content">
                    <h3 class="user-kb-help-title">
                        <?php echo e(trans('app.Need More Help?')); ?>

                    </h3>
                    <p class="user-kb-help-subtitle">
                        <?php echo e(trans('app.We\'re here to help you succeed')); ?>

                    </p>
                    <p class="user-kb-help-description">
                        <?php echo e(trans('app.Can\'t find what you\'re looking for? Our expert support team is standing by to provide personalized assistance and help you get the most out of our platform.')); ?>

                    </p>
                </div>
            </div>
            
            <div class="user-kb-help-features">
                <div class="user-kb-help-feature">
                    <div class="user-kb-feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="user-kb-feature-content">
                        <h4 class="user-kb-feature-title"><?php echo e(trans('app.24/7 Support')); ?></h4>
                        <p class="user-kb-feature-description"><?php echo e(trans('app.Round-the-clock assistance')); ?></p>
                    </div>
                </div>
                
                <div class="user-kb-help-feature">
                    <div class="user-kb-feature-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="user-kb-feature-content">
                        <h4 class="user-kb-feature-title"><?php echo e(trans('app.Expert Team')); ?></h4>
                        <p class="user-kb-feature-description"><?php echo e(trans('app.Professional assistance')); ?></p>
                    </div>
                </div>
                
                <div class="user-kb-help-feature">
                    <div class="user-kb-feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div class="user-kb-feature-content">
                        <h4 class="user-kb-feature-title"><?php echo e(trans('app.Fast Response')); ?></h4>
                        <p class="user-kb-feature-description"><?php echo e(trans('app.Quick resolution times')); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="user-kb-help-actions">
                <a href="<?php echo e(route('support.tickets.create')); ?>" class="user-kb-help-btn user-kb-help-btn-primary">
                    <div class="user-kb-btn-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="user-kb-btn-content">
                        <span class="user-kb-btn-title"><?php echo e(trans('app.Contact Support')); ?></span>
                        <span class="user-kb-btn-subtitle"><?php echo e(trans('app.Get personalized help')); ?></span>
                    </div>
                    <div class="user-kb-btn-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                
                <a href="<?php echo e(route('kb.search')); ?>" class="user-kb-help-btn user-kb-help-btn-secondary">
                    <div class="user-kb-btn-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="user-kb-btn-content">
                        <span class="user-kb-btn-title"><?php echo e(trans('app.Search Again')); ?></span>
                        <span class="user-kb-btn-subtitle"><?php echo e(trans('app.Find more articles')); ?></span>
                    </div>
                    <div class="user-kb-btn-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                
                <a href="<?php echo e(route('kb.index')); ?>" class="user-kb-help-btn user-kb-help-btn-tertiary">
                    <div class="user-kb-btn-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="user-kb-btn-content">
                        <span class="user-kb-btn-title"><?php echo e(trans('app.Browse All')); ?></span>
                        <span class="user-kb-btn-subtitle"><?php echo e(trans('app.Explore knowledge base')); ?></span>
                    </div>
                    <div class="user-kb-btn-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </div>
            
            <div class="user-kb-help-footer">
                <div class="user-kb-help-stats">
                    <div class="user-kb-stat-item">
                        <span class="user-kb-stat-number">99%</span>
                        <span class="user-kb-stat-label"><?php echo e(trans('app.Satisfaction Rate')); ?></span>
                    </div>
                    <div class="user-kb-stat-divider"></div>
                    <div class="user-kb-stat-item">
                        <span class="user-kb-stat-number">&lt;2h</span>
                        <span class="user-kb-stat-label"><?php echo e(trans('app.Avg Response')); ?></span>
                    </div>
                    <div class="user-kb-stat-divider"></div>
                    <div class="user-kb-stat-item">
                        <span class="user-kb-stat-number">24/7</span>
                        <span class="user-kb-stat-label"><?php echo e(trans('app.Availability')); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>


</footer>
<?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\components\user-footer.blade.php ENDPATH**/ ?>