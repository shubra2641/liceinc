@if ($paginator->hasPages() || $paginator->total() > 0)
    <div class="user-pagination-wrapper">
        {{-- Left: Statistics --}}
        <div class="user-pagination-info">
            <div class="user-pagination-stats">
                <i class="fas fa-chart-bar text-primary me-2"></i>
                <span class="user-pagination-text">
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
        <div class="user-pagination-center">
            <ul class="user-pagination-numbers">
                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="user-page-item disabled">
                            <span class="user-page-link user-page-dots">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="user-page-item active">
                                    <span class="user-page-link user-page-number">{{ $page }}</span>
                                </li>
                            @else
                                <li class="user-page-item">
                                    <a class="user-page-link user-page-number" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </ul>
        </div>
        @else
        <div class="user-pagination-center">
            <div class="user-single-page">
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="user-single-page-text">{{ __('Page 1 of 1') }}</span>
            </div>
        </div>
        @endif

        {{-- Right: Previous/Next Buttons --}}
        @if ($paginator->hasPages())
        <div class="user-pagination-nav">
            <ul class="user-pagination-controls">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="user-page-item disabled">
                        <span class="user-page-link user-page-prev">
                            <i class="fas fa-chevron-left"></i>
                            <span class="user-page-text">{{ __('Previous') }}</span>
                        </span>
                    </li>
                @else
                    <li class="user-page-item">
                        <a class="user-page-link user-page-prev" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="fas fa-chevron-left"></i>
                            <span class="user-page-text">{{ __('Previous') }}</span>
                        </a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="user-page-item">
                        <a class="user-page-link user-page-next" href="{{ $paginator->nextPageUrl() }}" rel="next">
                            <span class="user-page-text">{{ __('Next') }}</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="user-page-item disabled">
                        <span class="user-page-link user-page-next">
                            <span class="user-page-text">{{ __('Next') }}</span>
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>
        @endif
    </div>
@endif
