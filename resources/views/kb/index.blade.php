@extends('layouts.user')

@section('title', trans('app.Knowledgebase'))
@section('page-title', trans('app.Knowledge Base'))
@section('page-subtitle', trans('app.Find answers to your questions and get help with our products'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-book"></i>
                {{ trans('app.Knowledge Base') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Find answers to your questions and get help with our products') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Hero Search Section -->
            <div class="user-kb-hero">
                <div class="user-kb-hero-content">
                    <div class="user-kb-hero-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h2 class="user-kb-hero-title">
                        {{ trans('app.Find Answers Instantly') }}
                    </h2>
                    <p class="user-kb-hero-subtitle">
                        {{ trans('app.Search through our comprehensive knowledge base to find solutions to your questions') }}
                    </p>
                    
                    <!-- Advanced Search Form -->
                    <div class="user-kb-search-container">
                        <form action="{{ route('kb.search') }}" method="get" class="user-kb-search-form">
                            <div class="user-kb-search-wrapper">
                                <div class="user-kb-search-input-container">
                                    <i class="fas fa-search user-kb-search-icon"></i>
                                    <input type="text" name="q" class="user-kb-search-input"
                                        placeholder="{{ trans('app.Search articles, guides, and tutorials...') }}"
                                        autocomplete="off">
                                    <div class="user-kb-search-suggestions" id="searchSuggestions"></div>
                                </div>
                                <button type="submit" class="user-kb-search-btn">
                                    <i class="fas fa-search"></i>
                                    <span>{{ trans('app.Search') }}</span>
                                </button>
                            </div>
                        </form>
                        
                        <!-- Quick Search Tags -->
                        <div class="user-kb-quick-tags">
                            <span class="user-kb-tag-label">{{ trans('app.Popular searches:') }}</span>
                            <a href="{{ route('kb.search', ['q' => 'installation']) }}" class="user-kb-tag">
                                <i class="fas fa-download"></i>
                                {{ trans('app.Installation') }}
                            </a>
                            <a href="{{ route('kb.search', ['q' => 'configuration']) }}" class="user-kb-tag">
                                <i class="fas fa-cog"></i>
                                {{ trans('app.Configuration') }}
                            </a>
                            <a href="{{ route('kb.search', ['q' => 'troubleshooting']) }}" class="user-kb-tag">
                                <i class="fas fa-tools"></i>
                                {{ trans('app.Troubleshooting') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Section -->
            <div class="user-card user-kb-categories-card">
                <div class="user-card-header">
                    <div class="user-section-header">
                        <div class="user-section-title">
                            <div class="user-section-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div>
                                <h3 class="user-section-title-text">{{ trans('app.Browse by Category') }}</h3>
                                <p class="user-section-subtitle">{{ trans('app.Explore articles organized by topic') }}</p>
                            </div>
                        </div>
                        <div class="user-section-badge">
                            <i class="fas fa-folder"></i>
                            <span>{{ $categories->count() }} {{ trans('app.categories') }}</span>
                        </div>
                    </div>
                </div>
                <div class="user-card-content">

                    @if($categories->isEmpty())
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            {{ trans('app.No categories available') }}
                        </h3>
                        <p class="user-empty-state-description">
                            {{ trans('app.Check back later for new categories') }}
                        </p>
                    </div>
                    @else
                    <div class="user-kb-categories-grid">
                        @foreach($categories as $cat)
                        <div class="user-kb-category-card" data-category="{{ $cat->slug }}">
                            <div class="user-kb-category-header">
                                <div class="user-kb-category-icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <div class="user-kb-category-info">
                                    <h3 class="user-kb-category-title">
                                        <a href="{{ route('kb.category', $cat->slug) }}">
                                            {{ $cat->name }}
                                        </a>
                                    </h3>
                                    <div class="user-kb-category-badges">
                                        @if($cat->is_featured)
                                        <span class="user-kb-badge user-kb-badge-premium">
                                            <i class="fas fa-crown"></i>
                                            {{ trans('app.Premium') }}
                                        </span>
                                        @endif
                                        @if($cat->requires_serial || $cat->product_id)
                                        @if(auth()->check())
                                        @if($cat->hasAccess)
                                        <span class="user-kb-badge user-kb-badge-success">
                                            <i class="fas fa-check-circle"></i>
                                            {{ trans('app.Accessible') }}
                                        </span>
                                        @else
                                        <span class="user-kb-badge user-kb-badge-warning">
                                            <i class="fas fa-lock"></i>
                                            {{ trans('app.Locked') }}
                                        </span>
                                        @endif
                                        @else
                                        <span class="user-kb-badge user-kb-badge-info">
                                            <i class="fas fa-user-lock"></i>
                                            {{ trans('app.Login Required') }}
                                        </span>
                                        @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="user-kb-category-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>

                            <div class="user-kb-category-content">
                                <p class="user-kb-category-description">
                                    {{ Str::limit($cat->description, 120) }}
                                </p>
                                
                                <div class="user-kb-category-stats">
                                    <div class="user-kb-stat">
                                        <i class="fas fa-file-alt"></i>
                                        <span>{{ $cat->articles->count() }} {{ trans('app.articles') }}</span>
                                    </div>
                                    @if($cat->articles->count() > 0)
                                    <div class="user-kb-stat">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ $cat->articles->sortByDesc('created_at')->first()->created_at->diffForHumans() }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="user-kb-category-footer">
                                <a href="{{ route('kb.category', $cat->slug) }}" class="user-kb-category-btn">
                                    <i class="fas fa-arrow-right"></i>
                                    <span>{{ trans('app.Explore Category') }}</span>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

            <!-- Latest Articles Section -->
            @if($latest->count() > 0)
            <div class="user-card user-kb-articles-card">
                <div class="user-card-header">
                    <div class="user-section-header">
                        <div class="user-section-title">
                            <div class="user-section-icon">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div>
                                <h3 class="user-section-title-text">{{ trans('app.Latest Articles') }}</h3>
                                <p class="user-section-subtitle">{{ trans('app.Recently published articles and guides') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('kb.search') }}" class="user-section-link">
                            <i class="fas fa-arrow-right"></i>
                            <span>{{ trans('app.View All Articles') }}</span>
                        </a>
                    </div>
                </div>
                <div class="user-card-content">

                    <div class="user-kb-articles-grid">
                        @foreach($latest as $article)
                        <div class="user-kb-article-card" data-article="{{ $article->slug }}">
                            <div class="user-kb-article-header">
                                <div class="user-kb-article-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="user-kb-article-info">
                                    <h3 class="user-kb-article-title">
                                        <a href="{{ route('kb.article', $article->slug) }}">
                                            {{ $article->title }}
                                        </a>
                                    </h3>
                                    <div class="user-kb-article-badges">
                                        @if($article->is_featured)
                                        <span class="user-kb-badge user-kb-badge-premium">
                                            <i class="fas fa-crown"></i>
                                            {{ trans('app.Premium') }}
                                        </span>
                                        @endif
                                        @if($article->allow_comments)
                                        <span class="user-kb-badge user-kb-badge-comments">
                                            <i class="fas fa-comments"></i>
                                            {{ trans('app.Comments Enabled') }}
                                        </span>
                                        @endif
                                        @if($article->requires_serial || ($article->category && $article->category->requires_serial) || $article->product_id)
                                        @if(auth()->check())
                                        @if($article->hasAccess)
                                        <span class="user-kb-badge user-kb-badge-success">
                                            <i class="fas fa-check-circle"></i>
                                            {{ trans('app.Accessible') }}
                                        </span>
                                        @else
                                        <span class="user-kb-badge user-kb-badge-warning">
                                            <i class="fas fa-lock"></i>
                                            {{ trans('app.Locked') }}
                                        </span>
                                        @endif
                                        @else
                                        <span class="user-kb-badge user-kb-badge-info">
                                            <i class="fas fa-user-lock"></i>
                                            {{ trans('app.Login Required') }}
                                        </span>
                                        @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="user-kb-article-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>

                            <div class="user-kb-article-content">
                                <p class="user-kb-article-description">
                                    {{ Str::limit($article->excerpt ?: strip_tags($article->content), 120) }}
                                </p>
                                
                                <div class="user-kb-article-meta">
                                    <div class="user-kb-article-meta-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>{{ $article->created_at->format('M d, Y') }}</span>
                                    </div>
                                    @if($article->category)
                                    <div class="user-kb-article-meta-item">
                                        <i class="fas fa-folder"></i>
                                        <span>{{ $article->category->name }}</span>
                                    </div>
                                    @endif
                                    <div class="user-kb-article-meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ $article->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="user-kb-article-footer">
                                <a href="{{ route('kb.article', $article->slug) }}" class="user-kb-article-btn">
                                    <i class="fas fa-arrow-right"></i>
                                    <span>{{ trans('app.Read Article') }}</span>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
            </div>
        </div>
        @endif


        </div>
    </div>

</div>
@endsection