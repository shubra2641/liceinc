@if ($paginator->hasPages() || $paginator->total() > 0)
<div class="admin-pagination-wrapper">
    {{-- Left: Statistics --}}
    <div class="admin-pagination-info">
        <div class="admin-pagination-stats">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <span class="admin-pagination-text">
                {{ __('Showing') }}
                <strong class="text-primary">{{ $paginator->firstItem() ?? 0 }}</strong>
                {{ __('to') }}
                <strong class="text-primary">{{ $paginator->lastItem() ?? 0 }}</strong>
                {{ __('of') }}
                <strong class="text-primary">{{ $paginator->total() }}</strong>
                {{ __('results') }}
            </span>
        </div>
    </div>

    {{-- Center: Page Numbers --}}
    @if ($paginator->hasPages())
    <div class="admin-pagination-center">
        <ul class="admin-pagination-numbers">
            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
            <li class="admin-page-item disabled">
                <span class="admin-page-link admin-page-dots">{{ $element }}</span>
            </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
            @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
            <li class="admin-page-item active">
                <span class="admin-page-link admin-page-number">{{ $page }}</span>
            </li>
            @else
            <li class="admin-page-item">
                <a class="admin-page-link admin-page-number" href="{{ $url }}">{{ $page }}</a>
            </li>
            @endif
            @endforeach
            @endif
            @endforeach
        </ul>
    </div>
    @else
    <div class="admin-pagination-center">
        <div class="admin-single-page">
            <i class="fas fa-check-circle text-success me-2"></i>
            <span class="admin-single-page-text">{{ __('Page 1 of 1') }}</span>
        </div>
    </div>
    @endif

    {{-- Right: Previous/Next Buttons --}}
    @if ($paginator->hasPages())
    <div class="admin-pagination-nav">
        <ul class="admin-pagination-controls">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
            <li class="admin-page-item disabled">
                <span class="admin-page-link admin-page-prev">
                    <i class="fas fa-chevron-left"></i>
                    <span class="admin-page-text">{{ __('Previous') }}</span>
                </span>
            </li>
            @else
            <li class="admin-page-item">
                <a class="admin-page-link admin-page-prev" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <i class="fas fa-chevron-left"></i>
                    <span class="admin-page-text">{{ __('Previous') }}</span>
                </a>
            </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
            <li class="admin-page-item">
                <a class="admin-page-link admin-page-next" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    <span class="admin-page-text">{{ __('Next') }}</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
            @else
            <li class="admin-page-item disabled">
                <span class="admin-page-link admin-page-next">
                    <span class="admin-page-text">{{ __('Next') }}</span>
                    <i class="fas fa-chevron-right"></i>
                </span>
            </li>
            @endif
        </ul>
    </div>
    @endif
</div>
@endif