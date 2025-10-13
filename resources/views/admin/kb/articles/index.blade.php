@extends('layouts.admin')
@section('title', 'KB Articles')

@section('admin-content')
<!-- Admin KB Articles Page -->
<div class="admin-kb-articles-page">
    <div class="admin-page-header modern-header">
        <div class="admin-page-header-content">
            <div class="admin-page-title">
                <h1 class="gradient-text">{{ trans('app.kb_articles_management') }}</h1>
                <p class="admin-page-subtitle">{{ trans('app.manage_kb_articles') }}</p>
            </div>
            <div class="admin-page-actions">
                <a href="{{ route('admin.kb-articles.create') }}" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-plus me-2"></i>
                    {{ trans('app.new_article') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-filter me-2"></i>{{ trans('app.Filters') }}</h2>
            <div class="admin-section-actions">
                <div class="admin-search-box">
                    <input type="text" class="admin-form-input" id="searchArticles" 
                           placeholder="{{ trans('app.search_articles') }}">
                    <i class="fas fa-search admin-search-icon"></i>
                </div>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="category-filter">
                            <i class="fas fa-folder me-1"></i>{{ trans('app.Category') }}
                        </label>
                        <select id="category-filter" class="admin-form-input">
                            <option value="">{{ trans('app.All Categories') }}</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="status-filter">
                            <i class="fas fa-toggle-on me-1"></i>{{ trans('app.Status') }}
                        </label>
                        <select id="status-filter" class="admin-form-input">
                            <option value="">{{ trans('app.All Statuses') }}</option>
                            <option value="published">{{ trans('app.published') }}</option>
                            <option value="draft">{{ trans('app.draft') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Section -->
    <div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
        <!-- Total Articles Stats Card -->
        <div class="stats-card stats-card-primary animate-slide-up">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon articles"></div>
                    <div class="stats-card-menu">
                        <button class="stats-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value">{{ $articles->total() }}</div>
                    <div class="stats-card-label">{{ trans('app.Total Articles') }}</div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span>{{ $articles->where('is_published', true)->count() }} {{ trans('app.published') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Published Articles Stats Card -->
        <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon licenses"></div>
                    <div class="stats-card-menu">
                        <button class="stats-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value">{{ $articles->where('is_published', true)->count() }}</div>
                    <div class="stats-card-label">{{ trans('app.Published Articles') }}</div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span>{{ number_format(($articles->where('is_published', true)->count() / max($articles->count(), 1)) * 100, 1) }}% {{ trans('app.of_total') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Draft Articles Stats Card -->
        <div class="stats-card stats-card-warning animate-slide-up animate-delay-300">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon tickets"></div>
                    <div class="stats-card-menu">
                        <button class="stats-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value">{{ $articles->where('is_published', false)->count() }}</div>
                    <div class="stats-card-label">{{ trans('app.Draft Articles') }}</div>
                    <div class="stats-card-trend negative">
                        <i class="stats-trend-icon negative"></i>
                        <span>{{ number_format(($articles->where('is_published', false)->count() / max($articles->count(), 1)) * 100, 1) }}% {{ trans('app.of_total') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Stats Card -->
        <div class="stats-card stats-card-info animate-slide-up animate-delay-400">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon products"></div>
                    <div class="stats-card-menu">
                        <button class="stats-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value">{{ $categories->count() }}</div>
                    <div class="stats-card-label">{{ trans('app.Categories') }}</div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span>{{ trans('app.available_categories') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KB Articles Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-file-alt me-2"></i>{{ trans('app.all_articles') }}</h2>
            <span class="admin-badge admin-badge-info">{{ $articles->total() }} {{ trans('app.Articles') }}</span>
        </div>
        <div class="admin-section-content">
            @if($articles->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 kb-articles-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">{{ trans('app.Avatar') }}</th>
                        <th class="text-center">{{ trans('app.Article') }}</th>
                        <th class="text-center">{{ trans('app.Category') }}</th>
                        <th class="text-center">{{ trans('app.Status') }}</th>
                        <th class="text-center">{{ trans('app.Views') }}</th>
                        <th class="text-center">{{ trans('app.Created') }}</th>
                        <th class="text-center">{{ trans('app.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                    <tr class="article-row" data-title="{{ strtolower($article->title) }}" data-category="{{ $article->category_id ?? '' }}" data-status="{{ $article->is_published ? 'published' : 'draft' }}">
                        <td class="text-center">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center article-avatar">
                                <span class="text-muted small fw-bold">{{ strtoupper(substr($article->title, 0, 1)) }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $article->title }}</div>
                            @if($article->excerpt)
                            <small class="text-muted">{{ Str::limit($article->excerpt, 60) }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($article->category)
                                <span class="text-muted">{{ $article->category->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $article->is_published ? 'bg-success' : 'bg-warning' }}">
                                @if($article->is_published)
                                    <i class="fas fa-check-circle me-1"></i>{{ trans('app.published') }}
                                @else
                                    <i class="fas fa-edit me-1"></i>{{ trans('app.draft') }}
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">
                                <i class="fas fa-eye me-1"></i>{{ $article->views_count ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark">{{ $article->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $article->created_at->diffForHumans() }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="{{ route('admin.kb-articles.edit', $article) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    {{ trans('app.Edit') }}
                                </a>

                                <form action="{{ route('admin.kb-articles.destroy', $article) }}" method="POST"
                                      class="d-inline" data-confirm="Are you sure you want to delete this article?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fas fa-trash me-1"></i>
                                        {{ trans('app.Delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

            @if($articles->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $articles->links() }}
            </div>
            @endif
            @else
            <!-- Enhanced Empty State -->
            <div class="admin-empty-state kb-articles-empty-state">
                <div class="admin-empty-state-content">
                    <div class="admin-empty-state-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="admin-empty-state-text">
                        <h3 class="admin-empty-state-title">{{ trans('app.No Articles Found') }}</h3>
                        <p class="admin-empty-state-description">
                            {{ trans('app.Create your first KB article to get started') }}
                        </p>
                    </div>
                    <div class="admin-empty-state-actions">
                        <a href="{{ route('admin.kb-articles.create') }}" class="admin-btn admin-btn-primary admin-btn-m">
                            <i class="fas fa-plus me-2"></i>
                            {{ trans('app.Create Your First Article') }}
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ trans('app.Back to Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
