@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-1">
        {{-- First Page / Previous Page Link (Double Left Arrow) --}}
        @if ($paginator->onFirstPage())
            <span class="px-2.5 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 rounded cursor-not-allowed">
                &laquo;
            </span>
        @else
            <a href="{{ $paginator->url(1) }}" 
               rel="first" 
               class="px-2.5 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:border-blue-500 transition-colors">
                &laquo;
            </a>
        @endif

        {{-- Previous Page Link (Single Left Arrow) --}}
        @if ($paginator->onFirstPage())
            <span class="px-2.5 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 rounded cursor-not-allowed">
                &lsaquo;
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" 
               rel="prev" 
               class="px-2.5 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:border-blue-500 transition-colors">
                &lsaquo;
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="px-2.5 py-1.5 text-sm font-medium text-gray-500">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1.5 text-sm font-semibold text-white bg-blue-600 rounded cursor-default min-w-[36px] text-center">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" 
                           class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:border-blue-500 transition-colors min-w-[36px] text-center">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link (Single Right Arrow) --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" 
               rel="next" 
               class="px-2.5 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:border-blue-500 transition-colors">
                &rsaquo;
            </a>
        @else
            <span class="px-2.5 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 rounded cursor-not-allowed">
                &rsaquo;
            </span>
        @endif

        {{-- Last Page Link (Double Right Arrow) --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->url($paginator->lastPage()) }}" 
               rel="last" 
               class="px-2.5 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:border-blue-500 transition-colors">
                &raquo;
            </a>
        @else
            <span class="px-2.5 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 rounded cursor-not-allowed">
                &raquo;
            </span>
        @endif
    </nav>
@endif
