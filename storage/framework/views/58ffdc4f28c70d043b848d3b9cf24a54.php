<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product' => null, 'categories' => [], 'articles' => []]));

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

foreach (array_filter((['product' => null, 'categories' => [], 'articles' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="product-kb-manager">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fas fa-book w-5 h-5 mr-2"></i>
                <?php echo e(trans('app.Knowledge Base Access')); ?>

            </h3>
        </div>
        
        <div class="admin-card-content">
            <!-- KB Access Required Toggle -->
            <div class="mb-6">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="kb_access_required" 
                           name="kb_access_required" 
                           value="1"
                           <?php echo e(old('kb_access_required', $product?->kb_access_required) ? 'checked' : ''); ?>

                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 rounded">
                    <label for="kb_access_required" class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                        <?php echo e(trans('app.Require KB Access for this Product')); ?>

                    </label>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    <?php echo e(trans('app.When enabled, users must have a valid license for this product to access linked KB content')); ?>

                </p>
            </div>

            <!-- KB Access Message -->
            <div class="mb-6" id="kb-access-message-section" class="hidden">
                <label for="kb_access_message" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <?php echo e(trans('app.Custom Access Message')); ?>

                </label>
                <textarea id="kb_access_message" 
                          name="kb_access_message" 
                          rows="3"
                          class="admin-form-input"
                          placeholder="<?php echo e(trans('app.Enter a custom message to show when KB access is required...')); ?>"><?php echo e(old('kb_access_message', $product?->kb_access_message)); ?></textarea>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    <?php echo e(trans('app.This message will be displayed to users who need to verify their purchase')); ?>

                </p>
            </div>

            <!-- KB Categories Selection -->
            <div class="mb-6" id="kb-categories-section" class="hidden">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <?php echo e(trans('app.Link KB Categories')); ?>

                </label>
                <div class="kb-selection-container">
                    <div class="flex items-center mb-2">
                        <input type="text" 
                               id="category-search" 
                               placeholder="<?php echo e(trans('app.Search categories...')); ?>"
                               class="admin-form-input flex-1">
                        <button type="button" 
                                id="select-all-categories"
                                class="ml-2 admin-btn admin-btn-secondary admin-btn-sm">
                            <?php echo e(trans('app.Select All')); ?>

                        </button>
                        <button type="button" 
                                id="clear-categories"
                                class="ml-2 admin-btn admin-btn-secondary admin-btn-sm">
                            <?php echo e(trans('app.Clear')); ?>

                        </button>
                    </div>
                    <div class="kb-categories-list max-h-48 overflow-y-auto border border-slate-200 dark:border-slate-600 rounded-md p-3">
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center mb-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 p-2 rounded">
                            <input type="checkbox" 
                                   name="kb_categories[]" 
                                   value="<?php echo e($category->id); ?>"
                                   <?php echo e(in_array($category->id, old('kb_categories', $product?->kb_categories ?? [])) ? 'checked' : ''); ?>

                                   class="category-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 rounded">
                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-300">
                                <?php echo e($category->name); ?>

                                <span class="text-slate-500 dark:text-slate-400">(<?php echo e($category->slug); ?>)</span>
                            </span>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <!-- KB Articles Selection -->
            <div class="mb-6" id="kb-articles-section" class="hidden">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <?php echo e(trans('app.Link KB Articles')); ?>

                </label>
                <div class="kb-selection-container">
                    <div class="flex items-center mb-2">
                        <input type="text" 
                               id="article-search" 
                               placeholder="<?php echo e(trans('app.Search articles...')); ?>"
                               class="admin-form-input flex-1">
                        <button type="button" 
                                id="select-all-articles"
                                class="ml-2 admin-btn admin-btn-secondary admin-btn-sm">
                            <?php echo e(trans('app.Select All')); ?>

                        </button>
                        <button type="button" 
                                id="clear-articles"
                                class="ml-2 admin-btn admin-btn-secondary admin-btn-sm">
                            <?php echo e(trans('app.Clear')); ?>

                        </button>
                    </div>
                    <div class="kb-articles-list max-h-48 overflow-y-auto border border-slate-200 dark:border-slate-600 rounded-md p-3">
                        <?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center mb-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 p-2 rounded">
                            <input type="checkbox" 
                                   name="kb_articles[]" 
                                   value="<?php echo e($article->id); ?>"
                                   <?php echo e(in_array($article->id, old('kb_articles', $product?->kb_articles ?? [])) ? 'checked' : ''); ?>

                                   class="article-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 rounded">
                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-300">
                                <?php echo e($article->title); ?>

                                <span class="text-slate-500 dark:text-slate-400">
                                    (<?php echo e($article->category?->name ?? 'No Category'); ?>)
                                </span>
                            </span>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <!-- Selected Items Summary -->
            <div id="selected-summary" class="mt-4 p-3 bg-slate-50 dark:bg-slate-700 rounded-md" class="hidden">
                <h4 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <?php echo e(trans('app.Selected Items')); ?>

                </h4>
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    <span id="selected-categories-count">0</span> <?php echo e(trans('app.categories')); ?>, 
                    <span id="selected-articles-count">0</span> <?php echo e(trans('app.articles')); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\components\product-kb-manager.blade.php ENDPATH**/ ?>