@if ($paginator->hasPages())
    <nav class="w-full" aria-label="Sayfalama">
        {{-- Keep the mobile controls compact so pagination never widens the page. --}}
        <div class="flex w-full items-center justify-between gap-3 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-10 items-center justify-center gap-1 rounded-lg border border-gray-200 px-3 text-sm font-medium text-gray-300 dark:border-gray-700 dark:text-gray-600">
                    <span class="material-icons-round text-lg" aria-hidden="true">chevron_left</span>
                    Önceki
                </span>
            @else
                <button type="button" wire:click="previousPage" wire:loading.attr="disabled" rel="prev"
                        class="inline-flex h-10 items-center justify-center gap-1 rounded-lg border border-gray-200 px-3 text-sm font-medium text-gray-600 transition-colors hover:border-primary hover:text-primary disabled:opacity-50 dark:border-gray-700 dark:text-gray-300">
                    <span class="material-icons-round text-lg" aria-hidden="true">chevron_left</span>
                    Önceki
                </button>
            @endif

            <span class="min-w-0 text-center text-sm font-medium text-gray-500 dark:text-gray-400" aria-current="page">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage" wire:loading.attr="disabled" rel="next"
                        class="inline-flex h-10 items-center justify-center gap-1 rounded-lg border border-gray-200 px-3 text-sm font-medium text-gray-600 transition-colors hover:border-primary hover:text-primary disabled:opacity-50 dark:border-gray-700 dark:text-gray-300">
                    Sonraki
                    <span class="material-icons-round text-lg" aria-hidden="true">chevron_right</span>
                </button>
            @else
                <span class="inline-flex h-10 items-center justify-center gap-1 rounded-lg border border-gray-200 px-3 text-sm font-medium text-gray-300 dark:border-gray-700 dark:text-gray-600">
                    Sonraki
                    <span class="material-icons-round text-lg" aria-hidden="true">chevron_right</span>
                </span>
            @endif
        </div>

        <div class="hidden items-center justify-center gap-2 sm:flex">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed">
                    <span class="material-icons-round text-lg" aria-hidden="true">chevron_left</span>
                </span>
            @else
                <button type="button" wire:click="previousPage" wire:loading.attr="disabled" rel="prev" aria-label="Önceki sayfa"
                        class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 hover:border-primary hover:text-primary transition-colors">
                    <span class="material-icons-round text-lg" aria-hidden="true">chevron_left</span>
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="text-gray-400">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary text-white font-medium shadow-md shadow-primary/20" aria-current="page">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }})" aria-label="{{ $page }}. sayfaya git"
                                    class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-primary hover:text-primary transition-colors">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage" wire:loading.attr="disabled" rel="next" aria-label="Sonraki sayfa"
                        class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 hover:border-primary hover:text-primary transition-colors">
                    <span class="material-icons-round text-lg" aria-hidden="true">chevron_right</span>
                </button>
            @else
                <span class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed">
                    <span class="material-icons-round text-lg" aria-hidden="true">chevron_right</span>
                </span>
            @endif
        </div>
    </nav>
@endif
