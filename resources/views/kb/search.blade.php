@extends('layouts.user')

@section('title', trans('app.Search Knowledge Base'))
@section('page-title', trans('app.Search Knowledge Base'))
@section('page-subtitle', trans('app.Find answers to your questions'))


@section('content')
<div class="user-dashboard-container">
    <!-- Search Header -->
    <div class="user-card kb-search-header">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-search" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h1 class="user-section-title-text">{{ trans('app.Search Knowledge Base') }}</h1>
                        <p class="user-section-subtitle">{{ trans('app.Find answers to your questions and get help with
                            our products') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="user-kb-search-container">
                <form action="{{ route('kb.search') }}" method="get" class="user-kb-search-form" role="search">
                    <div class="user-kb-search-wrapper">
                        <div class="user-kb-search-input-container">
                            <label for="search-input" class="sr-only">{{ trans('app.Search articles...') }}</label>
                            <div class="user-kb-search-icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </div>
                            <input type="text" name="q" id="search-input" value="{{ $q }}" class="user-kb-search-input"
                                placeholder="{{ trans('app.Search articles...') }}" autocomplete="off"
                                aria-describedby="search-help">
                        </div>
                        <button type="submit" class="user-kb-search-btn" aria-label="{{ trans('app.Search') }}">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <span class="button-text">{{ trans('app.Search') }}</span>
                        </button>
                    </div>
                    <div id="search-help" class="user-kb-search-help">
                        {{ trans('app.Search in articles, categories, and content') }}
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($q === '')
    <!-- Empty Search State -->
    <div class="user-card">
        <div class="user-card-content">
            <div class="user-empty-state">
                <div class="user-empty-state-icon">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </div>
                <h2 class="user-empty-state-title">{{ trans('app.Start your search') }}</h2>
                <p class="user-empty-state-description">
                    {{ trans('app.Type something in the search box above to find articles and answers to your
                    questions') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Popular Categories -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-folder" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="user-section-title-text">{{ trans('app.Browse by Category') }}</h2>
                        <p class="user-section-subtitle">{{ trans('app.Explore our knowledge base categories') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="user-kb-categories-grid">
                @foreach($categoriesWithAccess as $cat)
                <article class="user-kb-category-card" data-category="{{ $cat->slug }}">
                    <div class="user-kb-category-header">
                        <div class="user-kb-category-icon">
                            <i class="fas fa-folder" aria-hidden="true"></i>
                        </div>
                        <div class="user-kb-category-info">
                            <h3 class="user-kb-category-title">
                                <a href="{{ route('kb.category', $cat->slug) }}"
                                    aria-label="{{ trans('app.View category') }}: {{ $cat->name }}">
                                    {{ $cat->name }}
                                </a>
                            </h3>
                            <div class="user-kb-category-badges">
                                @if($cat->is_featured)
                                <span class="user-kb-badge user-kb-badge-premium"
                                    aria-label="{{ trans('app.Premium') }}">
                                    <i class="fas fa-crown" aria-hidden="true"></i>
                                    {{ trans('app.Premium') }}
                                </span>
                                @endif
                                @if($cat->requires_serial || $cat->product_id)
                                @if($cat->hasAccess)
                                <span class="user-kb-badge user-kb-badge-success"
                                    aria-label="{{ trans('app.Accessible') }}">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                                    {{ trans('app.Accessible') }}
                                </span>
                                @else
                                <span class="user-kb-badge user-kb-badge-warning"
                                    aria-label="{{ trans('app.Locked') }}">
                                    <i class="fas fa-lock" aria-hidden="true"></i>
                                    {{ trans('app.Locked') }}
                                </span>
                                @endif
                                @endif
                            </div>
                        </div>
                        <div class="user-kb-category-arrow">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="user-kb-category-content">
                        <p class="user-kb-category-description">
                            {{ Str::limit($cat->description, 120) }}
                        </p>

                        <div class="user-kb-category-meta">
                            <div class="user-kb-category-meta-item">
                                <i class="fas fa-file-alt" aria-hidden="true"></i>
                                <span>{{ $cat->articles->count() }} {{ trans('app.articles') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="user-kb-category-footer">
                        <a href="{{ route('kb.category', $cat->slug) }}" class="user-kb-category-btn"
                            aria-label="{{ trans('app.View articles in') }} {{ $cat->name }}">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            <span>{{ trans('app.View Articles') }}</span>
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <!-- Search Results -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-search" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="user-section-title-text">{{ trans('app.Search results') }}</h2>
                        <p class="user-section-subtitle">
                            @if($results->count() > 0)
                            {{ $results->count() }} {{ trans('app.results for') }} "<strong>{{ $q }}</strong>"
                            @else
                            {{ trans('app.No results for') }} "<strong>{{ $q }}</strong>"
                            @endif
                        </p>
                    </div>
                </div>
                <div class="user-form-actions">
                    <label class="user-form-label" for="sortSelect">{{ trans('app.Sort by') }}</label>
                    <select name="sort" id="sortSelect" class="user-form-select"
                        aria-label="{{ trans('app.Sort results by') }}">
                        <option value="relevance" {{ request('sort')=='relevance' ? 'selected' : '' }}>{{
                            trans('app.Relevance') }}</option>
                        <option value="newest" {{ request('sort')=='newest' ? 'selected' : '' }}>{{ trans('app.Newest')
                            }}</option>
                        <option value="oldest" {{ request('sort')=='oldest' ? 'selected' : '' }}>{{ trans('app.Oldest')
                            }}</option>
                        <option value="popular" {{ request('sort')=='popular' ? 'selected' : '' }}>{{ trans('app.Most
                            Popular') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            @if($results->count() > 0)
            <div class="user-kb-articles-grid" role="list" aria-label="{{ trans('app.Search results') }}">
                @foreach($resultsWithAccess as $item)
                <article class="user-kb-article-card {{ !$item->hasAccess ? 'kb-result-locked' : '' }}" role="listitem"
                    data-search-type="{{ $item->search_type }}" data-slug="{{ $item->slug }}">
                    <div class="user-kb-article-header">
                        <div class="user-kb-article-icon">
                            @if($item->search_type === 'article')
                            <i class="fas fa-file-alt" aria-hidden="true"></i>
                            @else
                            <i class="fas fa-folder" aria-hidden="true"></i>
                            @endif
                        </div>
                        <div class="user-kb-article-info">
                            <h3 class="user-kb-article-title">
                                @if($item->search_type === 'article')
                                @if($item->hasAccess)
                                <a href="{{ route('kb.article', $item->slug) }}" class="user-kb-article-link"
                                    aria-label="{{ trans('app.Read article') }}: {{ $item->title }}">
                                    {!! \App\Http\Controllers\KbPublicController::highlightSearchTerm($item->title,
                                    $highlightQuery) !!}
                                </a>
                                @else
                                <span class="kb-result-locked-text"
                                    aria-label="{{ trans('app.Locked article') }}: {{ $item->title }}">
                                    {!! \App\Http\Controllers\KbPublicController::highlightSearchTerm($item->title,
                                    $highlightQuery) !!}
                                </span>
                                @endif
                                @else
                                @if($item->hasAccess)
                                <a href="{{ route('kb.category', $item->slug) }}" class="user-kb-article-link"
                                    aria-label="{{ trans('app.View category') }}: {{ $item->name }}">
                                    {!! \App\Http\Controllers\KbPublicController::highlightSearchTerm($item->name,
                                    $highlightQuery) !!}
                                </a>
                                @else
                                <span class="kb-result-locked-text"
                                    aria-label="{{ trans('app.Locked category') }}: {{ $item->name }}">
                                    {!! \App\Http\Controllers\KbPublicController::highlightSearchTerm($item->name,
                                    $highlightQuery) !!}
                                </span>
                                @endif
                                @endif
                            </h3>
                            <div class="user-kb-article-badges">
                                <span
                                    class="user-kb-badge {{ $item->search_type === 'article' ? 'user-kb-badge-info' : 'user-kb-badge-success' }}"
                                    aria-label="{{ $item->search_type === 'article' ? trans('app.Article') : trans('app.Category') }}">
                                    @if($item->search_type === 'article')
                                    <i class="fas fa-file-alt" aria-hidden="true"></i>
                                    {{ trans('app.Article') }}
                                    @else
                                    <i class="fas fa-folder" aria-hidden="true"></i>
                                    {{ trans('app.Category') }}
                                    @endif
                                </span>

                                @if($item->is_featured)
                                <span class="user-kb-badge user-kb-badge-premium"
                                    aria-label="{{ trans('app.Premium') }}">
                                    <i class="fas fa-crown" aria-hidden="true"></i>
                                    {{ trans('app.Premium') }}
                                </span>
                                @endif

                                @if($item->search_type === 'article' && $item->allow_comments)
                                <span class="user-kb-badge user-kb-badge-comments"
                                    aria-label="{{ trans('app.Comments Enabled') }}">
                                    <i class="fas fa-comments" aria-hidden="true"></i>
                                    {{ trans('app.Comments Enabled') }}
                                </span>
                                @endif

                                @if(!$item->hasAccess)
                                <span class="user-kb-badge user-kb-badge-warning"
                                    aria-label="{{ auth()->check() ? trans('app.Locked') : trans('app.Login Required') }}">
                                    <i class="fas fa-lock" aria-hidden="true"></i>
                                    @if(auth()->check())
                                    {{ trans('app.Locked') }}
                                    @else
                                    {{ trans('app.Login Required') }}
                                    @endif
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="user-kb-article-arrow">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="user-kb-article-content">
                        <p class="user-kb-article-description {{ !$item->hasAccess ? 'kb-result-locked-text' : '' }}">
                            @if($item->search_type === 'article')
                            {!! \App\Http\Controllers\KbPublicController::highlightSearchTerm(Str::limit($item->excerpt
                            ?: strip_tags($item->content), 200),
                            $highlightQuery) !!}
                            @else
                            {!!
                            \App\Http\Controllers\KbPublicController::highlightSearchTerm(Str::limit($item->description,
                            200), $highlightQuery) !!}
                            @endif
                        </p>

                        @if(!$item->hasAccess)
                        <p class="kb-result-access-message">
                            @if(auth()->check())
                            {{ trans('app.This content requires a valid license to access') }}
                            @else
                            {{ trans('app.Please login to access this content') }}
                            @endif
                        </p>
                        @endif

                        <div class="user-kb-article-meta">
                            @if($item->search_type === 'article')
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-calendar" aria-hidden="true"></i>
                                <span>{{ $item->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                                <span>{{ $item->views ?? 0 }} {{ trans('app.views') }}</span>
                            </div>
                            @if($item->category)
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-folder" aria-hidden="true"></i>
                                <span>{{ $item->category->name }}</span>
                            </div>
                            @endif
                            @else
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-file-alt" aria-hidden="true"></i>
                                <span>{{ $item->articles_count ?? 0 }} {{ trans('app.articles') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="user-kb-article-footer">
                        @if($item->search_type === 'article')
                        @if($item->hasAccess)
                        <a href="{{ route('kb.article', $item->slug) }}" class="user-kb-article-btn"
                            aria-label="{{ trans('app.Read article') }}: {{ $item->title }}"
                            title="{{ trans('app.Read article') }}: {{ $item->title }}">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            {{ trans('app.Read Article') }}
                        </a>
                        @else
                        <span class="kb-result-locked-action">
                            @if(auth()->check())
                            {{ trans('app.License Required') }}
                            @else
                            <a href="{{ route('login') }}" class="user-action-button secondary"
                                aria-label="{{ trans('app.Login to access') }}"
                                title="{{ trans('app.Login to access') }}">
                                {{ trans('app.Login to Access') }}
                            </a>
                            @endif
                        </span>
                        @endif
                        @else
                        @if($item->hasAccess)
                        <a href="{{ route('kb.category', $item->slug) }}" class="user-kb-article-btn"
                            aria-label="{{ trans('app.View category') }}: {{ $item->name }}"
                            title="{{ trans('app.View category') }}: {{ $item->name }}">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            {{ trans('app.View Category') }}
                        </a>
                        @else
                        <span class="kb-result-locked-action">
                            @if(auth()->check())
                            {{ trans('app.License Required') }}
                            @else
                            <a href="{{ route('login') }}" class="user-action-button secondary"
                                aria-label="{{ trans('app.Login to access') }}"
                                title="{{ trans('app.Login to access') }}">
                                {{ trans('app.Login to Access') }}
                            </a>
                            @endif
                        </span>
                        @endif
                        @endif
                    </div>
                </article>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($results->count() > 10)
            <nav class="kb-search-pagination" aria-label="{{ trans('app.Search results pagination') }}">
                <div class="pagination-info">
                    <span>{{ trans('app.Showing') }} {{ (request('page', 1) - 1) * 10 + 1 }}-{{ min(request('page', 1) * 10, $results->count()) }} {{ trans('app.of') }} {{ $results->count() }} {{ trans('app.results') }}</span>
                </div>
                <div class="pagination-links">
                    @if(request('page', 1) > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => request('page', 1) - 1]) }}"
                        class="pagination-link pagination-prev" aria-label="{{ trans('app.Previous page') }}">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        {{ trans('app.Previous') }}
                    </a>
                    @endif

                    @for($i = 1; $i <= ceil($results->count() / 10); $i++)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                            class="pagination-link {{ request('page', 1) == $i ? 'pagination-active' : '' }}"
                            aria-label="{{ trans('app.Go to page') }} {{ $i }}" @if(request('page', 1)==$i)
                            aria-current="page" @endif>
                            {{ $i }}
                        </a>
                        @endfor

                        @if(request('page', 1) < ceil($results->count() / 10))
                            <a href="{{ request()->fullUrlWithQuery(['page' => request('page', 1) + 1]) }}"
                                class="pagination-link pagination-next" aria-label="{{ trans('app.Next page') }}">
                                {{ trans('app.Next') }}
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                            @endif
                </div>
            </nav>
            @endif
            @else
            <!-- No Results -->
            <div class="user-card">
                <div class="user-card-content">
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-search-minus" aria-hidden="true"></i>
                        </div>
                        <h2 class="user-empty-state-title">{{ trans('app.No results found') }}</h2>
                        <p class="user-empty-state-description">
                            {{ trans('app.We couldn\'t find any articles matching your search. Try different keywords or
                            browse our categories below.') }}
                        </p>

                        <!-- Search Suggestions -->
                        <div class="user-form-actions">
                            <h3 class="user-form-label">{{ trans('app.Search Tips') }}</h3>
                            <ul class="user-features-list">
                                <li class="user-feature-item">
                                    <i class="fas fa-lightbulb" aria-hidden="true"></i>
                                    <span class="user-feature-text">{{ trans('app.Try different keywords') }}</span>
                                </li>
                                <li class="user-feature-item">
                                    <i class="fas fa-lightbulb" aria-hidden="true"></i>
                                    <span class="user-feature-text">{{ trans('app.Check your spelling') }}</span>
                                </li>
                                <li class="user-feature-item">
                                    <i class="fas fa-lightbulb" aria-hidden="true"></i>
                                    <span class="user-feature-text">{{ trans('app.Use more general terms') }}</span>
                                </li>
                                <li class="user-feature-item">
                                    <i class="fas fa-lightbulb" aria-hidden="true"></i>
                                    <span class="user-feature-text">{{ trans('app.Try fewer keywords') }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="user-form-actions">
                            <a href="{{ route('kb.index') }}" class="user-action-button"
                                aria-label="{{ trans('app.Browse all categories') }}">
                                <i class="fas fa-folder" aria-hidden="true"></i>
                                {{ trans('app.Browse Categories') }}
                            </a>
                            <a href="{{ route('support.tickets.create') }}" class="user-action-button secondary"
                                aria-label="{{ trans('app.Contact support team') }}">
                                <i class="fas fa-headset" aria-hidden="true"></i>
                                {{ trans('app.Contact Support') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Popular Categories (when there are results) -->
    @if($q !== '' && $results->count() > 0)
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-star" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="user-section-title-text">{{ trans('app.Popular Categories') }}</h2>
                        <p class="user-section-subtitle">{{ trans('app.Explore more content') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="user-kb-categories-grid">
                @foreach($categoriesWithAccess->take(6) as $cat)
                <article class="user-kb-category-card" data-category="{{ $cat->slug }}">
                    <div class="user-kb-category-header">
                        <div class="user-kb-category-icon">
                            <i class="fas fa-folder" aria-hidden="true"></i>
                        </div>
                        <div class="user-kb-category-info">
                            <h3 class="user-kb-category-title">
                                <a href="{{ route('kb.category', $cat->slug) }}"
                                    aria-label="{{ trans('app.View category') }}: {{ $cat->name }}">
                                    {{ $cat->name }}
                                </a>
                            </h3>
                            <div class="user-kb-category-badges">
                                @if($cat->is_featured)
                                <span class="user-kb-badge user-kb-badge-premium"
                                    aria-label="{{ trans('app.Premium') }}">
                                    <i class="fas fa-crown" aria-hidden="true"></i>
                                    {{ trans('app.Premium') }}
                                </span>
                                @endif
                                @if($cat->requires_serial || $cat->product_id)
                                @if($cat->hasAccess)
                                <span class="user-kb-badge user-kb-badge-success"
                                    aria-label="{{ trans('app.Accessible') }}">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                                    {{ trans('app.Accessible') }}
                                </span>
                                @else
                                <span class="user-kb-badge user-kb-badge-warning"
                                    aria-label="{{ trans('app.Locked') }}">
                                    <i class="fas fa-lock" aria-hidden="true"></i>
                                    {{ trans('app.Locked') }}
                                </span>
                                @endif
                                @endif
                            </div>
                        </div>
                        <div class="user-kb-category-arrow">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="user-kb-category-content">
                        <p class="user-kb-category-description">
                            {{ Str::limit($cat->description, 120) }}
                        </p>

                        <div class="user-kb-category-meta">
                            <div class="user-kb-category-meta-item">
                                <i class="fas fa-file-alt" aria-hidden="true"></i>
                                <span>{{ $cat->articles->count() }} {{ trans('app.articles') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="user-kb-category-footer">
                        <a href="{{ route('kb.category', $cat->slug) }}" class="user-kb-category-btn"
                            aria-label="{{ trans('app.View articles in') }} {{ $cat->name }}">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            <span>{{ trans('app.View Articles') }}</span>
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endif
</div>

@endsection