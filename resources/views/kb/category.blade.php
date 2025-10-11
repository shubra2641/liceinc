@extends('layouts.user')

@section('title', $category->name)
@section('page-title', $category->name)
@section('page-subtitle', $category->description)

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-folder-open"></i>
                {{ $category->name }}
            </div>
            <p class="user-card-subtitle">
                {{ $category->description }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Breadcrumbs -->
            <div class="user-breadcrumbs">
                <a href="{{ route('kb.index') }}" class="user-breadcrumb-link">
                    <i class="fas fa-home"></i>
                    {{ trans('app.Knowledge Base') }}
                </a>
                <i class="fas fa-chevron-right user-breadcrumb-separator"></i>
                <span class="user-breadcrumb-current">{{ $category->name }}</span>
            </div>

            <!-- Category Status -->
            @if($category->requires_serial || $category->product_id)
            <div class="user-category-status">
                @if(auth()->check())
                    @if($category->hasAccess)
                        <div class="user-status-badge user-status-success">
                            <i class="fas fa-check-circle"></i>
                            {{ trans('app.Full Access Available') }}
                        </div>
                    @else
                        <div class="user-status-badge user-status-warning">
                            <i class="fas fa-lock"></i>
                            {{ trans('app.Purchase Required for Access') }}
                        </div>
                    @endif
                @else
                    <div class="user-status-badge user-status-info">
                        <i class="fas fa-user-lock"></i>
                        {{ trans('app.Login Required for Access') }}
                    </div>
                @endif
            </div>
            @endif

            <!-- Search Section -->

        </div>
    </div>

    <!-- Articles Section -->
    <div class="user-card user-kb-articles-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text">{{ trans('app.Articles') }}</h3>
                        <p class="user-section-subtitle">{{ trans('app.Explore articles in this category') }}</p>
                    </div>
                </div>
                <div class="user-section-badge">
                    <i class="fas fa-file"></i>
                    <span>{{ $articles->total() }} {{ trans('app.articles') }}</span>
                </div>
            </div>
        </div>
        <div class="user-card-content">
            @if($articles->isEmpty())
            <div class="user-empty-state">
                <div class="user-empty-state-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 class="user-empty-state-title">
                    {{ trans('app.No articles found') }}
                </h3>
                <p class="user-empty-state-description">
                    {{ trans('app.This category doesn\'t have any articles yet') }}
                </p>
            </div>
            @else
            <div class="user-kb-articles-grid">
                @foreach($articles as $article)
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
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-eye"></i>
                                <span>{{ $article->views }} {{ trans('app.views') }}</span>
                            </div>
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

            <!-- Pagination -->
            @if($articles->hasPages())
            <div class="user-pagination">
                {{ $articles->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>

    <!-- Category Info Sidebar -->
    <div class="user-card user-kb-sidebar-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text">{{ trans('app.Category Info') }}</h3>
                        <p class="user-section-subtitle">{{ trans('app.Details about this category') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="user-card-content">
            <div class="user-kb-info-grid">
                <div class="user-kb-info-item">
                    <div class="user-kb-info-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="user-kb-info-content">
                        <h4 class="user-kb-info-label">{{ trans('app.Category Name') }}</h4>
                        <p class="user-kb-info-value">{{ $category->name }}</p>
                    </div>
                </div>

                <div class="user-kb-info-item">
                    <div class="user-kb-info-icon">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <div class="user-kb-info-content">
                        <h4 class="user-kb-info-label">{{ trans('app.Description') }}</h4>
                        <p class="user-kb-info-value">{{ $category->description }}</p>
                    </div>
                </div>

                <div class="user-kb-info-item">
                    <div class="user-kb-info-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="user-kb-info-content">
                        <h4 class="user-kb-info-label">{{ trans('app.Total Articles') }}</h4>
                        <p class="user-kb-info-value">{{ $articles->total() }}</p>
                    </div>
                </div>

                @if($category->requires_serial || $category->product_id)
                <div class="user-kb-info-item">
                    <div class="user-kb-info-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="user-kb-info-content">
                        <h4 class="user-kb-info-label">{{ trans('app.Access Status') }}</h4>
                        <div class="user-kb-info-value">
                            @if(auth()->check())
                                @if($category->hasAccess)
                                    <span class="user-kb-badge user-kb-badge-success">
                                        <i class="fas fa-check-circle"></i>
                                        {{ trans('app.Full Access Available') }}
                                    </span>
                                @else
                                    <span class="user-kb-badge user-kb-badge-warning">
                                        <i class="fas fa-lock"></i>
                                        {{ trans('app.Purchase Required for Access') }}
                                    </span>
                                @endif
                            @else
                                <span class="user-kb-badge user-kb-badge-info">
                                    <i class="fas fa-user-lock"></i>
                                    {{ trans('app.Login Required for Access') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Related Categories Section -->
    @if($relatedCategories->count() > 0)
    <div class="user-card user-kb-categories-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text">{{ trans('app.Related Categories') }}</h3>
                        <p class="user-section-subtitle">{{ trans('app.Explore similar categories') }}</p>
                    </div>
                </div>
                <a href="{{ route('kb.index') }}" class="user-section-link">
                    <i class="fas fa-arrow-right"></i>
                    <span>{{ trans('app.View All Categories') }}</span>
                </a>
            </div>
        </div>
        <div class="user-card-content">
            <div class="user-kb-categories-grid">
                @foreach($relatedCategories as $relatedCat)
                <div class="user-kb-category-card" data-category="{{ $relatedCat->slug }}">
                    <div class="user-kb-category-header">
                        <div class="user-kb-category-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="user-kb-category-info">
                            <h3 class="user-kb-category-title">
                                <a href="{{ route('kb.category', $relatedCat->slug) }}">
                                    {{ $relatedCat->name }}
                                </a>
                            </h3>
                            <div class="user-kb-category-badges">
                                @if($relatedCat->requires_serial || $relatedCat->product_id)
                                @if(auth()->check())
                                @if($relatedCat->hasAccess)
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
                            {{ Str::limit($relatedCat->description, 120) }}
                        </p>
                        
                        <div class="user-kb-category-stats">
                            <div class="user-kb-stat">
                                <i class="fas fa-file-alt"></i>
                                <span>{{ $relatedCat->articles->count() }} {{ trans('app.articles') }}</span>
                            </div>
                            @if($relatedCat->articles->count() > 0)
                            <div class="user-kb-stat">
                                <i class="fas fa-clock"></i>
                                <span>{{ $relatedCat->articles->sortByDesc('created_at')->first()->created_at->diffForHumans() }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="user-kb-category-footer">
                        <a href="{{ route('kb.category', $relatedCat->slug) }}" class="user-kb-category-btn">
                            <i class="fas fa-arrow-right"></i>
                            <span>{{ trans('app.Explore Category') }}</span>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection


