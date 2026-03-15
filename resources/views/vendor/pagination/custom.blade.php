@if ($paginator->hasPages())
<nav class="bf-pagination" role="navigation" aria-label="Pagination">
    <div class="bf-pagination__info">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / {{ $paginator->total() }}
    </div>
    <div class="bf-pagination__links">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="bf-page bf-page--disabled" aria-disabled="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="bf-page" rel="prev">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="bf-page bf-page--dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="bf-page bf-page--active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="bf-page">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="bf-page" rel="next">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        @else
            <span class="bf-page bf-page--disabled" aria-disabled="true">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </span>
        @endif
    </div>
</nav>
@endif
