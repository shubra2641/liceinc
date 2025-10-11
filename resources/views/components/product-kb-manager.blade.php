@props(['product' => null, 'categories' => [], 'articles' => []])

<div class="product-kb-manager">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fas fa-book w-5 h-5 mr-2"></i>
                {{ trans('app.Knowledge Base Access') }}
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
                           {{ old('kb_access_required', $product?->kb_access_required) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 rounded">
                    <label for="kb_access_required" class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ trans('app.Require KB Access for this Product') }}
                    </label>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ trans('app.When enabled, users must have a valid license for this product to access linked KB content') }}
                </p>
            </div>

            <!-- KB Access Message -->
            <div class="mb-6" id="kb-access-message-section" class="hidden">
                <label for="kb_access_message" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    {{ trans('app.Custom Access Message') }}
                </label>
                <textarea id="kb_access_message" 
                          name="kb_access_message" 
                          rows="3"
                          class="admin-form-input"
                          placeholder="{{ trans('app.Enter a custom message to show when KB access is required...') }}">{{ old('kb_access_message', $product?->kb_access_message) }}</textarea>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ trans('app.This message will be displayed to users who need to verify their purchase') }}
                </p>
            </div>

            <!-- KB Categories Selection -->
            <div class="mb-6" id="kb-categories-section" class="hidden">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    {{ trans('app.Link KB Categories') }}
                </label>
                <div class="kb-selection-container">
                    <div class="flex items-center mb-2">
                        <input type="text" 
                               id="category-search" 
                               placeholder="{{ trans('app.Search categories...') }}"
                               class="admin-form-input flex-1">
                        <button type="button" 
                                id="select-all-categories"
                                class="ml-2 admin-btn admin-btn-secondary admin-btn-sm">
                            {{ trans('app.Select All') }}
                        </button>
                        <button type="button" 
                                id="clear-categories"
                                class="ml-2 admin-btn admin-btn-secondary admin-btn-sm">
                            {{ trans('app.Clear') }}
                        </button>
                    </div>
                    <div class="kb-categories-list max-h-48 overflow-y-auto border border-slate-200 dark:border-slate-600 rounded-md p-3">
                        @foreach($categories as $category)
                        <label class="flex items-center mb-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 p-2 rounded">
                            <input type="checkbox" 
                                   name="kb_categories[]" 
                                   value="{{ $category->id }}"
                                   {{ in_array($category->id, old('kb_categories', $product?->kb_categories ?? [])) ? 'checked' : '' }}
                                   class="category-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 rounded">
                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-300">
                                {{ $category->name }}
                                <span class="text-slate-500 dark:text-slate-400">({{ $category->slug }})</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- KB Articles Selection -->
            <div class="mb-6" id="kb-articles-section" class="hidden">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    {{ trans('app.Link KB Articles') }}
                </label>
                <div class="kb-selection-container">
                    <div class="flex items-center mb-2">
                        <input type="text" 
                               id="article-search" 
                               placeholder="{{ trans('app.Search articles...') }}"
                               class="admin-form-input flex-1">
                        <button type="button" 
                                id="select-all-articles"
                                class="ml-2 admin-btn admin-btn-secondary admin-btn-sm">
                            {{ trans('app.Select All') }}
                        </button>
                        <button type="button" 
                                id="clear-articles"
                                class="ml-2 admin-btn admin-btn-secondary admin-btn-sm">
                            {{ trans('app.Clear') }}
                        </button>
                    </div>
                    <div class="kb-articles-list max-h-48 overflow-y-auto border border-slate-200 dark:border-slate-600 rounded-md p-3">
                        @foreach($articles as $article)
                        <label class="flex items-center mb-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 p-2 rounded">
                            <input type="checkbox" 
                                   name="kb_articles[]" 
                                   value="{{ $article->id }}"
                                   {{ in_array($article->id, old('kb_articles', $product?->kb_articles ?? [])) ? 'checked' : '' }}
                                   class="article-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 rounded">
                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-300">
                                {{ $article->title }}
                                <span class="text-slate-500 dark:text-slate-400">
                                    ({{ $article->category?->name ?? 'No Category' }})
                                </span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Selected Items Summary -->
            <div id="selected-summary" class="mt-4 p-3 bg-slate-50 dark:bg-slate-700 rounded-md" class="hidden">
                <h4 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    {{ trans('app.Selected Items') }}
                </h4>
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    <span id="selected-categories-count">0</span> {{ trans('app.categories') }}, 
                    <span id="selected-articles-count">0</span> {{ trans('app.articles') }}
                </div>
            </div>
        </div>
    </div>
</div>

