@props(['item', 'level' => 0])

@php
    $hasChildren = !$item->children->isEmpty();
@endphp

@if($hasChildren)
    <div x-data="{ open: false }" class="flex flex-col">
        <div class="flex items-center justify-between">
            @php
                $isHashLink = $item->link === '#' || str_ends_with($item->link, '#') || str_ends_with($item->link, '#/');
            @endphp
            @if($isHashLink)
                <button 
                    @click="open = !open"
                    class="flex-1 text-left text-text-dark dark:text-text-light hover:text-primary text-sm font-medium transition-colors py-2 {{ $item->link_class }}"
                >
                    {{ $item->name }}
                </button>
            @else
                <a 
                    href="{{ $item->link }}" 
                    target="{{ $item->target }}" 
                    @click.stop="open = !open"
                    class="flex-1 text-left text-text-dark dark:text-text-light hover:text-primary text-sm font-medium transition-colors py-2 {{ $item->link_class }}"
                >
                    {{ $item->name }}
                </a>
            @endif
            <button 
                @click.stop="open = !open"
                class="flex items-center justify-center text-text-dark dark:text-text-light hover:text-primary transition-colors p-2 ml-2"
                aria-label="Toggle submenu"
            >
                <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>
        </div>
        <div x-show="open" x-collapse class="ml-4 mt-2 space-y-2 border-l-2 border-primary/20 pl-4">
            @foreach($item->children as $child)
                @include('components.menu.mobile-nav-item', ['item' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    </div>
@else
    <a 
        href="{{ $item->link }}" 
        target="{{ $item->target }}"
        @click="$dispatch('close-mobile-menu')"
        class="block text-text-dark dark:text-text-light hover:text-primary text-sm font-medium transition-colors py-2 {{ $item->link_class }}"
    >
        {{ $item->name }}
    </a>
@endif
