@extends('layouts.user')

@section('title', $article->title)
@section('page-title', $article->title)
@section('page-subtitle', trans('app.Knowledge Base Article'))

@section('content')
<div class="user-dashboard-container">
    <!-- Article Header -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h1 class="user-card-title">{{ $article->title }}</h1>
                        <p class="user-card-subtitle">{{ trans('app.Knowledge Base Article') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Article Content -->
    <div class="user-card">
        <div class="user-card-content">
            <!-- Article Meta Info -->
            <div class="user-kb-article-meta">
                <div class="user-kb-article-meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>{{ trans('app.Updated') }} {{ $article->updated_at->format('M d, Y') }}</span>
                </div>
                <div class="user-kb-article-meta-item">
                    <i class="fas fa-eye"></i>
                    <span>{{ trans('app.Views') }}: {{ $article->views }}</span>
                </div>
                @if($article->category)
                <div class="user-kb-article-meta-item">
                    <i class="fas fa-folder"></i>
                    <span>{{ $article->category->name }}</span>
                </div>
                @endif
            </div>

            <!-- Article Content -->
            <div class="article-content">
                {{ $article->content }}
            </div>
        </div>
    </div>

    <!-- Article Information Sidebar -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text">{{ trans('app.Article Information') }}</h3>
                        <p class="user-section-subtitle">{{ trans('app.About this article') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="category-info-grid">
                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{ trans('app.Category') }}</div>
                        <div class="info-value">{{ optional($article->category)->name ?? trans('app.Uncategorized') }}
                        </div>
                    </div>
                </div>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{ trans('app.Created') }}</div>
                        <div class="info-value">{{ $article->created_at->format('M d, Y') }}</div>
                    </div>
                </div>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{ trans('app.Last Updated') }}</div>
                        <div class="info-value">{{ $article->updated_at->format('M d, Y') }}</div>
                    </div>
                </div>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{ trans('app.Views') }}</div>
                        <div class="info-value">{{ $article->views }}</div>
                    </div>
                </div>
            </div>

            <div class="user-form-actions">
                <button class="user-action-button" data-action="print">
                    <i class="fas fa-print"></i>
                    {{ trans('app.Print Article') }}
                </button>

                <button class="user-action-button secondary" data-action="share">
                    <i class="fas fa-share-alt"></i>
                    {{ trans('app.Share') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Related Articles Section -->
    @if(isset($relatedArticles) && $relatedArticles->count() > 0)
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text">{{ trans('app.Related Articles') }}</h3>
                        <p class="user-section-subtitle">{{ trans('app.Similar articles you might find helpful') }}</p>
                    </div>
                </div>
                <a href="{{ route('kb.index') }}" class="user-section-link">
                    <i class="fas fa-arrow-right"></i>
                    <span>{{ trans('app.View All Articles') }}</span>
                </a>
            </div>
        </div>
        <div class="user-card-content">
            <div class="user-kb-articles-grid">
                @foreach($relatedArticles as $relatedArticle)
                <div class="user-kb-article-card" data-article="{{ $relatedArticle->slug }}">
                    <div class="user-kb-article-header">
                        <div class="user-kb-article-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="user-kb-article-info">
                            <h3 class="user-kb-article-title">
                                <a href="{{ route('kb.article', $relatedArticle->slug) }}">
                                    {{ $relatedArticle->title }}
                                </a>
                            </h3>
                            <div class="user-kb-article-badges">
                                @if($relatedArticle->requires_serial || ($relatedArticle->category &&
                                $relatedArticle->category->requires_serial) || $relatedArticle->product_id)
                                @if(auth()->check())
                                @if($relatedArticle->hasAccess)
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
                            {{ Str::limit($relatedArticle->excerpt ?: strip_tags($relatedArticle->content), 120) }}
                        </p>

                        <div class="user-kb-article-meta">
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>{{ $relatedArticle->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($relatedArticle->category)
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-folder"></i>
                                <span>{{ $relatedArticle->category->name }}</span>
                            </div>
                            @endif
                            <div class="user-kb-article-meta-item">
                                <i class="fas fa-clock"></i>
                                <span>{{ $relatedArticle->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="user-kb-article-footer">
                        <a href="{{ route('kb.article', $relatedArticle->slug) }}" class="user-kb-article-btn">
                            <i class="fas fa-arrow-right"></i>
                            <span>{{ trans('app.Read More') }}</span>
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