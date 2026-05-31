@if ($paginator->hasPages())
    <nav aria-label="Pagination">
        <div class="khqr-pagination">
            <ul class="pagination mb-0">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span class="page-link page-link-nav"><i class="bi bi-chevron-left"></i><span>Prev</span></span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link page-link-nav" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><i class="bi bi-chevron-left"></i><span>Prev</span></a>
                    </li>
                @endif

                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link page-link-nav" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"><span>Next</span><i class="bi bi-chevron-right"></i></a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span class="page-link page-link-nav"><span>Next</span><i class="bi bi-chevron-right"></i></span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
